<?php
add_action(
    'admin_enqueue_scripts',
    function ($hook) {
        if ($hook != 'post-new.php' && $hook != 'post.php') {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'gp-picker',
            "https://apis.google.com/js/api.js",
            ['jquery']
        );
        wp_enqueue_script(
            'gp-browser',
            plugins_url('metabox/js/jquery.filebrowser.min.js', dirname(__FILE__)),
            ['jquery']
        );
        wp_enqueue_style(
            'gp-browser-css',
            plugins_url('metabox/css/jquery.filebrowser.min.css', dirname(__FILE__))
        );
        wp_enqueue_script(
            'my-custom-script',
            plugins_url('metabox/js/script.js', dirname(__FILE__)),
            ['gp-picker']
        );
        global $post;
        // Localize the custom script with keys needed for js calls
        $gdrive_settings = array(
            "appId" => get_option('gai_gdrive_app_id'),
            "developerKey" => get_option('gai_gdrive_dev_key'),
            "clientToken" => getToken(),
            "ajaxUrl" => admin_url('admin-ajax.php'),
            "uploadAction" => 'vimeo_upload',
            "browseAction" => 'vimeo_files',
            "selectAction" => "vimeo_select",
            'postId' => $post->ID
        );
        wp_localize_script('my-custom-script', 'gdrive', $gdrive_settings);

        // Enqueued script with localized data.
        //wp_enqueue_script('my-custom-script');
    }
);

// Add button to select files from gDrive for upload to vimeo
add_action(
    'add_meta_boxes',
    function () {
        add_meta_box(
            'file-picker',
            __('Upload from gDrive', 'gdrive-vimeo-link'),
            function ($post) {
                if (getToken()) {
                    echo '<div class="uploader"><button type="button" onclick="loadPicker()">'.__('Upload Video', 'gdrive-vimeo-link').'</button>';
                    echo '<div id="result"></div></div>';
                } else {
                    $link = 'options-general.php?page=gai_gdrive_token&tab=tab-1';
                    echo '<div class="uploader">'.__('Please', 'gdrive-vimeo-link').'<a href="'.admin_url($link).'">'.__('authorize app', 'gdrive-vimeo-link').'</a>'.__('to load videos', 'gdrive-vimeo-link').'</div>';
                }
                add_thickbox();
                echo '<div class="surfer">'
                    .'<div id="my-content-id" style="display:none;">'
                    .'<div id="browser" class="browser"></div>'
                    .'</div>';
                echo '<style>'
                    .'.browser { width:475px; height:450px; border:1px solid gray; float:left;  margin:20px; }'
                    .'.browser.selected { border-color: blue; }'
                    .'.browser-widget li.file, .browser-widget li.directory { width: 60px;  }'
                    .'</style>';
                echo '<a href="#TB_inline?&width=800&height=600&inlineId=my-content-id" '
                    .'class="button thickbox" onclick="loadBrowser()">'.__('Select Video', 'gdrive-vimeo-link').'</a></div>';
            },
            'lp_lesson'
        );
    }
);