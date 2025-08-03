<?php
spl_autoload_register( 'bbp_core_autoloader' );

/**
 * Autoload files for the plugin
 *
 * @param string $class
 * @return void
 */
function bbp_core_autoloader( $class ) {
	$path = __DIR__ . '/includes/' . str_replace( '\\', '/', $class ) . '.php';

	if ( file_exists( $path ) ) {
		include_once $path;
	}
}
