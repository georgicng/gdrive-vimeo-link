<?php

// Register Vimeo and gDrive config settings in customiser
add_action(
    'customize_register',
    function ($wp_customize) {
        //Vimeo sections, settings, and controls
        $wp_customize->add_section(
            'gai_vimeo_settings',
            [
                'title'      => __('Vimeo', 'gdrive-vimeo-link'),
                'priority'   => 30,
            ]
        );

        $wp_customize->add_setting(
            'gai_vimeo_client_id',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_vimeo_client_id_control',
            [
                'label'      => __('Client ID', 'gdrive-vimeo-link'),
                'section'    => 'gai_vimeo_settings',
                'settings'   => 'gai_vimeo_client_id',
                'type'     => 'text',
            ]
        );

        $wp_customize->add_setting(
            'gai_vimeo_client_secret',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_vimeo_client_secret_control',
            [
                'label'      => __('Client Secret', 'gdrive-vimeo-link'),
                'section'    => 'gai_vimeo_settings',
                'settings'   => 'gai_vimeo_client_secret',
                'type'     => 'text',
            ]
        );

        $wp_customize->add_setting(
            'gai_vimeo_client_token',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_vimeo_client_token_control',
            [
                'label'      => __('Token', 'gdrive-vimeo-link'),
                'section'    => 'gai_vimeo_settings',
                'settings'   => 'gai_vimeo_client_token',
                'type'     => 'text',
            ]
        );

        //gDrive sections, settings, and controls
        $wp_customize->add_section(
            'gai_gdrive_settings',
            [
                'title'      => __('Google Drive Settings', 'gdrive-vimeo-link'),
                'priority'   => 30,
            ]
        );

        $wp_customize->add_setting(
            'gai_gdrive_app_id',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_gdrive_app_id_control',
            [
                'label'      => __('App ID', 'gdrive-vimeo-link'),
                'section'    => 'gai_gdrive_settings',
                'settings'   => 'gai_gdrive_app_id',
                'type'     => 'text',
            ]
        );

        $wp_customize->add_setting(
            'gai_gdrive_client_id',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_gdrive_client_id_control',
            [
                'label'      => __('Client ID', 'gdrive-vimeo-link'),
                'section'    => 'gai_gdrive_settings',
                'settings'   => 'gai_gdrive_client_id',
                'type'     => 'text',
            ]
        );

        $wp_customize->add_setting(
            'gai_gdrive_dev_key',
            [
                'type' => 'option',
                'transport'   => 'refresh',
            ]
        );

        $wp_customize->add_control(
            'gai_gdrive_dev_key_control',
            [
                'label'      => __('Dev Key', 'gdrive-vimeo-link'),
                'section'    => 'gai_gdrive_settings',
                'settings'   => 'gai_gdrive_dev_key',
                'type'     => 'text',
            ]
        );
    }
);