<?php
namespace BBPCorePro\inc\template_library;

/**
 * Class Template_Library
 * @package BBPCorePro
 * @since 3.3.0
 */
class Template_Library {

	public function __construct() {
		$this->core_includes();
	}

	public function core_includes() {

		// templates
		include( __DIR__ . '/templates/Import.php');
		include( __DIR__ . '/templates/Init.php');
		include( __DIR__ . '/templates/Load.php');
		include( __DIR__ . '/templates/Api.php');

        \BBPCorePro\inc\template_library\templates\Import::instance()->load();
        \BBPCorePro\inc\template_library\templates\Load::instance()->load();
        \BBPCorePro\inc\template_library\templates\Init::instance()->init();

		if (!defined('BBPC_TEMPLATE_LOGO_SRC')){
			define('BBPC_TEMPLATE_LOGO_SRC', plugin_dir_url( __FILE__ ) . 'templates/assets/img/bbpc_logo.svg');
		}

	}

}