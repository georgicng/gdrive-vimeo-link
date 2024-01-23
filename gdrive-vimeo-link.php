<?php
/*
Plugin Name: Google Drive to Vimeo Link
Description: Custom plugin to enable video upload from Google Drive to Vimeo and embed Vimeo videos to learnpress lessons
*/

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/common/lib.php';

require __DIR__ . '/endpoints/hooks.php';

require __DIR__ . '/customizer/hooks.php';

require __DIR__ . '/settings/hooks.php';

require __DIR__ . '/metabox/hooks.php';

register_activation_hook( 
    __FILE__, 
    function () {
        add_filter(
            'init',
            function () { 
                global $wp_rewrite; 
                $wp_rewrite->flush_rules(); 
            }
        ); 
    }
);

add_action(
    'after_setup_theme',
    function () {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }
);
