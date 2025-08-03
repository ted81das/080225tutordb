<?php
namespace admin;
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if ( !function_exists('is_plugin_active') ) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

class Pro_Widget_Service {

    public static function get_widget_settings($callable)
    {

        $settings_fields = [
            'bbpc_active_modules' => [
                [
                    'name' => 'ama_ajax_forum',
                    'label' => esc_html__('BBPC Ajax Forums', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
                [
                    'name' => 'ama_forum_posts',
                    'label' => esc_html__('BBPC Forum Topics', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
                [
                    'name' => 'ama_forum_tab',
                    'label' => esc_html__('BBPC Forum Tabs', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
                [
                    'name' => 'ama_forums',
                    'label' => esc_html__('BBPC Forums', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
                [
                    'name' => 'ama_search',
                    'label' => esc_html__('BBPC Search', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
                [
                    'name' => 'ama_single_forum',
                    'label' => esc_html__('BBPC Single Forum', 'bbp-core-pro'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'widget_type' => 'pro',
                    'demo_url' => 'https://spider-themes.net/bbp-core/',
                    //'video_url'    => 'https://youtu.be/Lq_st2IWZiE',
                ],
            ]
        ];

        $settings = [];
        $settings['settings_fields'] = $settings_fields;

        return $callable($settings);
    }

}

