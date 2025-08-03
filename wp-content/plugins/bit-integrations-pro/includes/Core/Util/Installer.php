<?php

namespace BitApps\BTCBI_PRO\Core\Util;

use BTCBI_Quiet_Installer_Skin;
use BTCBI_Quiet_Upgrader_Skin;
use Plugin_Upgrader;

final class Installer
{
    private $slug = '';

    private $file_slug = '';

    public function __construct($slug = 'bit-integrations', $file_slug = 'bit-integrations/bitwpfi.php')
    {
        $this->slug = $slug;
        $this->file_slug = $file_slug;
    }

    public function init()
    {
        if (!\function_exists('install')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        if (!\function_exists('upgrade')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!$this->is_plugin_installed()) {
            $installed = $this->install_plugin();
        } else {
            $this->upgrade_plugin();
            $installed = true;
        }
        if (!is_wp_error($installed) && $installed) {
            activate_plugin($this->file_slug);
        } else {
            echo 'Could not install the new plugin.';
        }
    }

    public function is_plugin_installed()
    {
        if (!\function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $callback = function ($key) {
            $key = explode('/', $key);

            return $key[0];
        };
        $plugins_keys = array_keys(get_plugins());
        $plugin_slugs = array_map($callback, $plugins_keys);

        return (bool) (\in_array($this->slug, $plugin_slugs))

        ;
    }

    public function install_plugin()
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once BTCBI_PRO_PLUGIN_BASEDIR . 'includes/Core/Util/Quiet_Installer_Skin.php';
        wp_cache_flush();

        $api = plugins_api(
            'plugin_information',
            [
                'slug'   => $this->slug,
                'fields' => [
                    'short_description' => false,
                    'requires'          => false,
                    'sections'          => false,
                    'rating'            => false,
                    'ratings'           => false,
                    'downloaded'        => false,
                    'last_updated'      => false,
                    'added'             => false,
                    'tags'              => false,
                    'compatibility'     => false,
                    'homepage'          => false,
                    'donate_link'       => false,
                ],
            ]
        );

        $skin = new BTCBI_Quiet_Installer_Skin(['api' => $api]);

        $upgrader = new Plugin_Upgrader($skin);
        $installed = $upgrader->install($api->download_link);

        return $installed;
    }

    public function upgrade_plugin()
    {
        $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->file_slug);
        $installed_version = $plugin_info['Version'];
        $latest_version = '1.4.4';

        if (version_compare($installed_version, $latest_version, '<')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            include_once BTCBI_PRO_PLUGIN_BASEDIR . 'includes/Core/Util/Quiet_Upgrader_Skin.php';
            wp_cache_flush();

            $api = plugins_api(
                'plugin_information',
                [
                    'slug'   => $this->slug,
                    'fields' => [
                        'short_description' => false,
                        'requires'          => false,
                        'sections'          => false,
                        'rating'            => false,
                        'ratings'           => false,
                        'downloaded'        => false,
                        'last_updated'      => false,
                        'added'             => false,
                        'tags'              => false,
                        'compatibility'     => false,
                        'homepage'          => false,
                        'donate_link'       => false,
                    ],
                ]
            );
            // Replace new \Plugin_Installer_Skin with new Quiet_Upgrader_Skin when output needs to be suppressed.
            $skin = new BTCBI_Quiet_Upgrader_Skin(['api' => $api]);

            $upgrader = new Plugin_Upgrader($skin);
            $upgraded = $upgrader->upgrade($this->file_slug);

            return $upgraded;
        }

        return false;
    }
}
