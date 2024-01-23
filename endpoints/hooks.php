<?php

// Admin endpoint to upload selected gDrive video to Vimeo and embed it to lesson
add_action(
    'wp_ajax_vimeo_upload',
    function () {
        //Extract request data
        $param = $_REQUEST;
        $fileId = $param['fileId'];

        //Check if required parameters are passed
        if (!$fileId) {
            die('Missing values');
        }

        $oAuthToken = getToken();
        $token = get_option('gai_gdrive_client_token');

        //Check if tokens are set
        if (!$oAuthToken || !$token) {
            die('Missing tokens');
        }

        try {
            $client = getGoogleClient($token);
            $driveService = new Google_Service_Drive($client);
            $file = $driveService->files->get($fileId, ["fields" => "*"]);

            $getUrl = 'https://www.googleapis.com/drive/v3/files/' . $fileId . '?alt=media';
            //$authHeader = "Bearer {$oAuthToken}";

            //init vimeo
            $vimeo = getVimeo();
            // Used by vimeo.com
            $payload = [
                "headers" => [
                    "authorization" => "Bearer {$oAuthToken}"
                ],
                "link" => $getUrl,
                "name" => $file->getName(),
                "size" => $file->getSize(), //  write logic to get vide size
                "type" => "pull",
                "picture_link" => $file->getThumbnailLink()
            ];

            //submit request
            $response = $vimeo->request(
                '/me/videos',
                $payload,
                'POST',
                true,
                ['Accept' => 'application/vnd.vimeo.video;version=3.2']
            );

            if ($response['body'] && $response['body']['embed']) {
                embed(                    
                    $response['body']['embed']['html'],
                    $param['postId']
                );
                $uri = $response['body']['uri'];
                $privacy =  [
                    'privacy' => [
                        "view" => "unlisted",
                        "download" => false,
                        "embed" => "whitelist",
                        "add" => false
                    ]
                ];
                $response = $vimeo->request($uri, $privacy, 'PATCH');
                $response = $vimeo->request($uri . '/privacy/domains/liciousdesserts.com', [], 'PUT');
                $response = $vimeo->request($uri . '/privacy/domains/localhost', [], 'PUT');
            }
            wp_send_json_success($response);
        } catch (Google_Service_Exception $e) {
            wp_send_json_error($e);
        } catch (VimeoUploadException $e) {
            wp_send_json_error($e);
        } catch (VimeoRequestException $e) {
            wp_send_json_error($e);
        } catch (Exception $e) {
            wp_send_json_error($e);
        }
    }
);

// Admin endpoint to list available vimeo files in the file browser
add_action(
    'wp_ajax_vimeo_files',
    function () {
        try {
            //init vimeo
            $vimeo = getVimeo();
            $payload = [];
            //Extract request data
            $param = $_REQUEST;
            if (isset($param['page'])) {
                $payload['page'] = $param['page'];
            }

            if ($cache = fetchFromCache($payload)) {
                wp_send_json_success($cache);
                return;
            }

            $response = $vimeo->request('/me/videos', $payload, 'GET');
            cacheResponse($payload, $response['body'], 60 * 60 * 12);
            wp_send_json_success($response['body']);
        } catch (VimeoUploadException $e) {
            wp_send_json_error($e);
        } catch (VimeoRequestException $e) {
            wp_send_json_error($e);
        }
    }
);

// Admin endpoint to embed selected vimeo file to lesson
add_action(
    'wp_ajax_vimeo_select',
    function () {
        //Extract request data
        $param = $_REQUEST;
        $uri = $param['uri'];
        $payload = [];
        if (isset($param['page'])) {
            $payload['page'] = $param['page'];
        }

        if ($cache = fetchFromCache($payload)) {
            foreach ($cache['data'] as $record) {
                if ($record['uri'] === $uri) {
                    embed($record['embed']['html'], $param['postId']);
                    break;
                }
            }
            return;
        }

        $item = $vimeo->request($uri, [], 'GET');
        if ($item) {
            embed($item['embed']['html'], $param['postId']);
        }

        //TODO: return friendly message to show to user
    }
);

add_filter(
    'query_vars',
    function ($vars) {
        $vars[] = 'webhook';
        return $vars;
    }
);

add_action(
    'init',
    function () {
        add_rewrite_rule(
            '^webhook/(.+?)/?$',
            'index.php?webhook=$matches[1]',
            'top'
        );
    }
);

add_action(
    'parse_request',
    function ($wp) {
        if (!empty($wp->query_vars['webhook'])) {
            do_action($wp->query_vars['webhook']);
            die();
        }
    }
);
