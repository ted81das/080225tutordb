<?php
use TutorLMSDroip\Backend;
use TutorLMSDroip\Frontend;
use TutorLMSDroip\Helper;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '../../../classes/Input.php'; // Input helper class not loaded, thats why we need to include it manually.

/**
 * Droip element source and filter hook will use using this prefix.
 * this prefix value is same as javascript APP_PREFIX variable.
 */
define('TDE_APP_PREFIX', 'tde');
define('TDE_APP_VERSION', '1.0.2');
define('TDE_BASE', home_url());
define('TDE_PLUGIN_ROOT_BASE', plugin_dir_url(__FILE__));

/**
 * Tutor Droip Elements
 *
 * @package     tutor-droip-elements
 */
class TutorDroipElements
{

    /**
     * Initializes a singleton instance
     *
     * @return \Droip
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance) {
            $instance = new self();
        }

        register_activation_hook(__FILE__, [new Helper(), 't_d_e_activate']);

        new Backend();
        new Frontend();

        return $instance;
    }
}

TutorDroipElements::init();
