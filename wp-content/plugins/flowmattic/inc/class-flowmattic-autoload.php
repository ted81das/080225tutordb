<?php
/**
 * Autoloader for FlowMattic classes.
 *
 * @author     InfiWebs
 * @copyright  (c) Copyright by InfiWebs
 * @link       https://flowmattic.com
 * @package    FlowMattic
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The Autoloader class for Flowmattic.
 */
class FlowMattic_Autoload {

	/**
	 * The transient name.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private static $transient_name = '';

	/**
	 * Stored paths.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private static $cached_paths = array();

	/**
	 * Whether the cache needs updating or not.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var bool
	 */
	private static $update_cache = false;

	/**
	 * The path to the "inc" folder inside the theme.
	 *
	 * @access protected
	 * @since 1.0
	 * @var string
	 */
	protected $flowmattic_includes_path;

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		// Set the transient name.
		if ( empty( self::$transient_name ) ) {
			self::$transient_name = 'flowmattic_autoloader_paths_' . md5( __FILE__ );
		}

		$this->flowmattic_includes_path = FLOWMATTIC_PLUGIN_DIR . 'inc/';

		// Get the cached paths array.
		$this->get_cached_paths();

		// Register our autoloader.
		spl_autoload_register( array( $this, 'include_class_file' ) );

		// Update caches.
		add_action( 'shutdown', array( $this, 'update_cached_paths' ) );

		// Reset caches on theme switch.
		add_action( 'after_switch_theme', array( $this, 'reset_cached_paths' ) );
		add_action( 'switch_theme', array( $this, 'reset_cached_paths' ) );

	}

	/**
	 * Gets the cached paths.
	 *
	 * @access protected
	 * @since 1.0
	 * @return void
	 */
	protected function get_cached_paths() {

		self::$cached_paths = get_site_transient( self::$transient_name );

	}

	/**
	 * Gets the path for a specific class-name.
	 *
	 * @access protected
	 * @since 1.0
	 * @param string $class_name The class-name we're looking for.
	 * @return false|string      The full path to the class, or false if not found.
	 */
	protected function get_path( $class_name ) {

		$paths = array();
		if ( false !== strpos( $class_name, 'FlowMattic' ) ) {

			$filename = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			$paths[] = $this->flowmattic_includes_path . $filename;

			foreach ( $paths as $path ) {
				$path = wp_normalize_path( $path );
				if ( file_exists( $path ) ) {
					return $path;
				}
			}
		}

		return false;
	}

	/**
	 * Get the path & include the file for the class.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $class_name The class-name we're looking for.
	 * @return void
	 */
	public function include_class_file( $class_name ) {

		// If the path is cached, use it & early exit.
		if ( isset( self::$cached_paths[ $class_name ] ) ) {
			require_once self::$cached_paths[ $class_name ];
			return;
		}

		// If we got this far, the path is not cached.
		// We'll need to get it, and add it to the cache.
		$path = $this->get_path( $class_name );

		// Include the path.
		if ( $path ) {
			include $path;

			if ( ! is_array( self::$cached_paths ) ) {
				self::$cached_paths = array();
			}
			
			// Add path to the array of paths to cache.
			self::$cached_paths[ $class_name ] = $path;

			// Make sure we update the caches.
			self::$update_cache = true;

			return;
		}
	}

	/**
	 * Update caches if needed.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function update_cached_paths() {

		// If we don't need to update the caches, early exit.
		if ( false === self::$update_cache ) {
			return;
		}

		// Cache for 30 seconds using transients.
		set_site_transient( self::$transient_name, self::$cached_paths, 30 );

	}

	/**
	 * Reset caches.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function reset_cached_paths() {

		delete_site_transient( self::$transient_name );

	}
}
