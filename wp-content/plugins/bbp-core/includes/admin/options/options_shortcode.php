<?php
// Create a section.
CSF::createSection(
    $prefix,
    [
        'title'  => __( 'Shortcodes', 'bbp-core' ),
		'icon'   => 'dashicons dashicons-editor-code',
        'fields' => [
            [
                'id'         => 'bbpc_shortcode',
                'type'       => 'text',
                'title'      => esc_html__( 'My Profile URL', 'bbp-core' ),
                'subtitle'   => esc_html__( 'Use this shortcode to get the user\'s own profile URL', 'bbp-core' ),
                'default'    => '[bbpc_profile_link]',
                'attributes' => array(
                    'readonly' => 'readonly',
                ),
                'class'   => 'st-pro-notice'
            ]
        ],
    ]
);