<?php

namespace BitApps\BTCBI_PRO;

/**
 * Main class for the plugin.
 *
 * @since 1.0.0-alpha
 */

use BitApps\BTCBI_PRO\Admin\Admin_Bar;
use BitApps\BTCBI_PRO\Core\Database\DB;
use BitApps\BTCBI_PRO\Core\Hooks\HookService;
use BitApps\BTCBI_PRO\Core\Update\API;
use BitApps\BTCBI_PRO\Core\Update\Updater;
use BitApps\BTCBI_PRO\Core\Util\Activation;
use BitApps\BTCBI_PRO\Core\Util\Capabilities;
use BitApps\BTCBI_PRO\Core\Util\Deactivation;
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Core\Util\Installer as FreeInstaller;
use BitApps\BTCBI_PRO\Core\Util\Request;
use BitApps\BTCBI_PRO\Core\Util\UnInstallation;

final class Plugin
{
    /**
     * Main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @var Plugin|null
     */
    private static $_instance;

    private $isLicActive;

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function initialize()
    {
        Hooks::add('plugins_loaded', [$this, 'init_plugin'], 12);
        (new Activation())->activate();
        (new Deactivation())->register();
        (new UnInstallation())->register();
    }

    public function init_plugin()
    {
        Hooks::add('init', [$this, 'init_classes'], 8);
        Hooks::add('init', [$this, 'localization_setup']);

        new HookService();
        $freeInstaller = new FreeInstaller();
        Hooks::add('init', [$freeInstaller, 'init']);
        Hooks::filter('plugin_action_links_' . plugin_basename(BTCBI_PRO_PLUGIN_MAIN_FILE), [$this, 'plugin_action_links']);
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        static::update_tables();
        $isBitFiLicActive = Plugin::instance()->isLicenseActive();
        if (Request::Check('admin')) {
            (new Admin_Bar())->register();
            new Updater();
        }

        global $wp_rewrite;
        \define('BTCBI_PRO_API_MAIN', get_site_url() . ($wp_rewrite->permalink_structure ? '/wp-json' : '/?rest_route='));
    }

    /**
     * Initially load the plugin text domain
     *
     * @return void
     */
    public function localization_setup()
    {
        load_plugin_textdomain('bit-integrations-pro', false, basename(BTCBI_PRO_PLUGIN_BASEDIR) . '/languages');
    }

    /**
     * Plugin action links
     *
     * @param array $links
     *
     * @return array
     */
    public function plugin_action_links($links)
    {
        $links[] = '<a href="https://docs.bit-integrations.bitapps.pro" target="_blank">' . __('Docs', 'bit-integrations-pro') . '</a>';

        return $links;
    }

    /**
     * Retrieves the main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @return Plugin main instance.
     */
    public static function instance()
    {
        return static::$_instance;
    }

    public static function update_tables()
    {
        if (!Capabilities::Check('manage_options')) {
            return;
        }
        global $btcbi_pro_db_version;
        $installed_db_version = get_site_option('btcbi_pro_db_version');
        if ($installed_db_version != $btcbi_pro_db_version) {
            DB::migrate();
        }
    }

    public function isLicenseActive()
    {
        if (!isset($this->isLicActive)) {
            $this->isLicActive = API::isLicenseActive();
        }

        return $this->isLicActive;
    }

    /**
     * Loads the plugin main instance and initializes it.
     *
     * @since 1.0.0-alpha
     *
     * @param string $main_file Absolute path to the plugin main file.
     *
     * @return bool True if the plugin main instance could be loaded, false otherwise./
     */
    public static function load()
    {
        if (null !== static::$_instance) {
            return false;
        }
        static::$_instance = new static();
        static::$_instance->initialize();

        return true;
    }
}
