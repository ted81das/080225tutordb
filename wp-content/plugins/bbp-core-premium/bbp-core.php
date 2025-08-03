<?php

/**
 * Plugin Name: BBP Core Pro
 * Description: Power-up the BBP Core plugin with advanced controls and features
 * Plugin URI: #
 * Author: spider-themes
 * Author URI: http://spider-themes.net/
 * Version: 1.1.7
 * Update URI: https://api.freemius.com
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'bc_fs' ) ) {
    // Create a helper function for easy SDK access.
    function bc_fs() {
        global $bc_fs;
        if ( !isset( $bc_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/fs/start.php';
            $bc_fs = fs_dynamic_init( array(
                'id'              => '10864',
                'slug'            => 'bbp-core',
                'type'            => 'plugin',
                'public_key'      => 'pk_41277ad11125f6e2a1b4e66f40164',
                'is_premium'      => true,
                'is_premium_only' => false,
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'menu'            => array(
                    'slug'       => 'bbp-core',
                    'first-path' => 'plugins.php',
                    'contact'    => false,
                    'support'    => false,
                ),
                'is_live'         => true,
            ) );
        }
        return $bc_fs;
    }

    // Init Freemius.
    bc_fs()->add_filter( 'deactivate_on_activation', '__return_false' );
    // Signal that SDK was initiated.
    do_action( 'bc_fs_loaded' );
}
if ( !class_exists( 'BBPCorePro' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
    /**
     * Class BBPCOREPRO
     */
    class BBPCorePro {
        /**
         * BbpcorePro Version
         *
         * Holds the version of the plugin.
         *
         * @var string The plugin version.
         */
        const version = '1.1.7';

        /**
         * Constructor.
         *
         * Initialize the Bbpcore plugin
         *
         * @access public
         */
        public function __construct() {
            $this->define_constants();
            $this->core_includes();
            register_activation_hook( __FILE__, [$this, 'activate'] );
            add_action( 'init', [$this, 'i18n'] );
            add_action( 'plugins_loaded', [$this, 'init_plugin'] );
            add_action( 'elementor/editor/before_enqueue_scripts', [$this, 'enqueue_elementor_editor_styles'] );
        }

        public function enqueue_elementor_editor_styles() {
            wp_enqueue_style( 'bbp_single_widgets', BBPCOREPRO_ASSETS . '/css/elementor-editor.css' );
        }

        /**
         * Load Textdomain
         *
         * Load plugin localization files.
         *
         * @access public
         */
        public function i18n() {
            load_plugin_textdomain( 'bbp-core-pro', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        }

        /**
         * Include Files
         *
         * Load core files required to run the plugin.
         *
         * @access public
         */
        public function core_includes() {
            require_once __DIR__ . '/includes/functions.php';
            require_once __DIR__ . '/includes/shortcodes/shortcodes.php';
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once __DIR__ . '/includes/notices.php';
        }

        /**
         * Define constants
         */
        public function define_constants() {
            define( 'BBPCOREPRO_VERSION', self::version );
            define( 'BBPCOREPRO_FILE', __FILE__ );
            define( 'BBPCOREPRO_PATH', __DIR__ );
            define( 'BBPCOREPRO_URL', plugins_url( '', BBPCOREPRO_FILE ) );
            define( 'BBPCOREPRO_ASSETS', BBPCOREPRO_URL . '/assets' );
            define( 'BBPCOREPRO_CSS', BBPCOREPRO_URL . '/assets/css' );
            define( 'BBPCOREPRO_JS', BBPCOREPRO_URL . '/assets/js' );
            define( 'BBPCOREPRO_FRONT_CSS', BBPCOREPRO_URL . '/assets/css/frontend' );
            define( 'BBPCOREPRO_IMG', BBPCOREPRO_URL . '/assets/images' );
            define( 'BBPCOREPRO_VEND', BBPCOREPRO_URL . '/assets/vendors' );
        }

        /**
         * Initializes a singleton instances
         *
         * @return void
         */
        public static function init() {
            static $instance = false;
            if ( !$instance ) {
                $instance = new self();
            }
            return $instance;
        }

        /**
         * Initializes the plugin
         *
         * @return void
         */
        public function init_plugin() {
            if ( is_admin() ) {
                new BBPCorePro\Admin\Admin_Actions();
                new BBPCorePro\Admin\Notifications();
            } else {
                new BBPCorePro\Frontend\Frontend_Actions();
                new BBPCorePro\Frontend\Assets();
            }
            // Elementor Block & Page Template Library
            if ( did_action( 'elementor/loaded' ) ) {
                require_once __DIR__ . '/includes/template_library/Template_Library.php';
                new BBPCorePro\inc\template_library\Template_Library();
            }
        }

        /**
         * Do stuff upon plugin activation
         */
        public function activate() {
            //Insert the installation time into the database
            $installed = get_option( 'BbpCorePro_installed' );
            if ( !$installed ) {
                update_option( 'BbpCorePro_installed', time() );
            }
            update_option( 'BbpCorePro_version', 'BBPCOREPRO_VERSION' );
        }

    }

}
/**
 * @return BBPCore|false
 */
if ( !function_exists( 'bbpcorepro' ) ) {
    /**
     * Load bbpcore
     *
     * Main instance of bbpcore
     *
     */
    function bbpcorepro() {
        return BBPCorePro::init();
    }

    /**
     * Kick of the plugin
     */
    bbpcorepro();
}