<?php
// Check if token exists and has expired and return state
// valid, expired, none or false
function renewToken($token, $type, $ref)
{
    if (empty($token)) {
        //check for vimeo
        return true;
    }

    if ($type == 'gdrive') {
        $ref->setAccessToken($token);
        if ($ref->isAccessTokenExpired()) {
            $refresh = $ref->getRefreshToken();
            if ($refresh) {            
                update_option('gai_gdrive_client_refresh_token', $refresh);
                $new_token = $ref->fetchAccessTokenWithRefreshToken($refresh);
                if (!isset($new_token['error'])) {
                    update_option('gai_gdrive_client_token', json_encode($new_token));
                    return false;
                }
                
            } elseif ($refreshTokenSaved = get_option('gai_gdrive_client_refresh_token')) {
                $new_token = $ref->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
                if (!$new_token['error']) {
                    update_option('gai_gdrive_client_token', json_encode($new_token));
                    return false;
                }
            }
            return true;
        }
    }

    return false;
}

function getStoredToken() {
    $setToken = get_option('gai_gdrive_client_token');
    return $setToken ? json_decode($setToken, true) : null;
}

/*
* function return token to js script for filepicker
*/
function getToken()
{
    try {
        $client = getGoogleClient();
        $token = getStoredToken();

        $client->setAccessToken($token);
        // check if token has expired and renew if refreshtoken present/saved
        if ($client->isAccessTokenExpired()) {
            $refresh = $client->getRefreshToken();
            if ($refresh) {            
                update_option('gai_gdrive_client_refresh_token', $refresh);
                $new_token = $client->fetchAccessTokenWithRefreshToken($refresh);
                update_option('gai_gdrive_client_token', $token);
                return $new_token['access_token'];
            } elseif ($refreshTokenSaved = get_option('gai_gdrive_client_refresh_token')) {
                $new_token = $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
                update_option('gai_gdrive_client_token', $token);
                return $new_token['access_token'];
            }
            return false;   
        }
        return $token['access_token'];
    } catch(Exception $e) {
        return $e;
    }
}

function getVimeo()
{
    $client_id = get_option('gai_vimeo_client_id');
    $client_secret = get_option('gai_vimeo_client_secret');
    $token = get_option('gai_vimeo_client_token');
    $vimeo = new \Vimeo\Vimeo($client_id, $client_secret);
    $vimeo->setToken($token);
    return $vimeo;
}

function getGoogleClient($token = null)
{
    $client = new Google_Client();
    $client->setAuthConfig(plugin_dir_path(__DIR__).'config/client_credentials.json');
    $redirect_uri = admin_url('options-general.php?page=gai_gdrive_token&tab=tab-1');
    $client->setRedirectUri($redirect_uri);
    $client->setAccessType('offline');
    $client->setApprovalPrompt("force");
    $client->setIncludeGrantedScopes(true);
    $client->addScope("https://www.googleapis.com/auth/drive");
    $client->addScope("https://www.googleapis.com/auth/drive.metadata");

    if ($token && renewToken($token, 'gdrive', $client)) {
        return false;
    }

    return $client;
}

function getKey($data)
{
    return md5(serialize($data));
}

function cacheResponse($payload, $data)
{
    $hash = getKey($payload);
    set_transient($hash, $data, 60 * 60 * 12);
}

function fetchFromCache($payload)
{
    $hash = getKey($payload);;
    $return = get_transient($hash);
    return $return;
}

function embed($html, $to)
{
    update_post_meta(
        $to,
        '_lp_lesson_video_intro',
        $html
    );
}