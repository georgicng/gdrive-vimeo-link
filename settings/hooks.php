<?php
// Add settings page
add_action(
    'admin_menu',
    function () {
        global $settings_page;
        $settings_page = add_options_page(
            'Google Drive Authorization',
            __('gDrive Access', 'gdrive-vimeo-link'),
            'manage_options',
            'gai_gdrive_token',
            function () {
                $token;
                $expired = false;
                $alert = false;
                $saved_token = false;
                $message = '';
                $client;
                $redirect_uri;
                $auth_url;
                $alert;
            
                if ($_GET['page'] !== "gai_gdrive_token") {
                    return;
                }
            
                if (!isset($_GET['tab']) || $_GET['tab'] == "tab-1") {
                    // gdrive setup
                    $token = getStoredToken();
                    $client = getGoogleClient();
            
                    //Process auth redirect
                    if (isset($_GET['code'])) {
                        try {
                            update_option('gai_gdrive_client_code', $_GET['code']);
                            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                            if (isset($token['error'])) {
                                throw new Exception();
                            } else {
                                update_option('gai_gdrive_client_token', json_encode($token));
                                $refresh = $client->getRefreshToken();
                                if ($refresh) {
                                    update_option('gai_gdrive_client_refresh_token', $refresh);
                                }
                                $saved_token = true;
                            }
                        } catch (Exception $e) {
                            $alert = true;
                        }
                    }
            
                    if ($saved_token == false && renewToken($token, 'gdrive', $client)) {
                        $auth_url = $client->createAuthUrl();
                        $message = __('You need to authorise App', 'gdrive-vimeo-link');
                    } else {
                        $message = __('App authorised, everything good', 'gdrive-vimeo-link');
                    }
                }
            
                if (isset($_GET['tab']) && $_GET['tab'] == "tab-2") {
                    //Vimeo Setup/processing
                    $client_id = get_option('gai_vimeo_client_id');
                    $client_secret = get_option('gai_vimeo_client_secret');
                    $token = get_option('gai_vimeo_client_token');
                    $redirect_uri = admin_url('options-general.php?page=gai_gdrive_token&tab=tab-2');
                    //add_query_arg($wp->query_string, '', admin_url($wp->request));
                    $client = new \Vimeo\Vimeo($client_id, $client_secret);
            
                    //Process auth redirect
                    if (isset($_GET['code']) && isset($_GET['state'])) {
                        $state = get_option('gai_vimeo_state');
                        if ($state && $_GET['state'] == $state) {
                            try {
                                $token = $client->accessToken($_GET['code'], $redirect_uri);
            
                                // Save access token
                                if ($token && $token['body']['access_token']) {
                                    update_option('gai_vimeo_client_token', $token['body']['access_token']);
                                    update_option('gai_vimeo_client_scope', $token['body']['scope']);
                                    $saved_token = true;
                                    $message = __('App Authorised', 'gdrive-vimeo-link');
                                }
                            } catch (Exception $e) {
                                $alert = true;
                            }
                        }
                    }
            
                    if ($saved_token == false  && renewToken($token, 'vimeo', $client)) {
                        $message = __('You need to authorise App', 'gdrive-vimeo-link');
                        $scopes = ['create', 'edit', 'delete', 'upload'];
                        $state = uniqid();
                        update_option('gai_vimeo_state', $state);
                        $auth_url = $client->buildAuthorizationEndpoint($redirect_uri, $scopes, $state);
                    } else {
                        $message = __('App Authorised', 'gdrive-vimeo-link');;
                    }
                }
            
                include 'page.php';
            }
        );
    }
);