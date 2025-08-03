<?php
/**
 * Plugin Name: FlowMattic
 * Plugin URI: https://flowmattic.com/
 * Description: Workflow automation plugin for WordPress.
 * Version: 5.2.0
 * Requires at least: 5.0
 * Requires PHP:      8.0
 * Author: InfiWebs
 * Author URI: https://www.infiwebs.com
 * Textdomain: flowmattic
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'FLOWMATTIC_VERSION' ) ) {
	define( 'FLOWMATTIC_VERSION', '5.2.0' );
}

// Plugin Root File.
if ( ! defined( 'FLOWMATTIC_PLUGIN_FILE' ) ) {
	define( 'FLOWMATTIC_PLUGIN_FILE', __FILE__ );
}

// Plugin Folder Path.
if ( ! defined( 'FLOWMATTIC_PLUGIN_DIR' ) ) {
	define( 'FLOWMATTIC_PLUGIN_DIR', wp_normalize_path( plugin_dir_path( FLOWMATTIC_PLUGIN_FILE ) ) );
}

// Plugin Folder URL.
if ( ! defined( 'FLOWMATTIC_PLUGIN_URL' ) ) {
	define( 'FLOWMATTIC_PLUGIN_URL', plugin_dir_url( FLOWMATTIC_PLUGIN_FILE ) );
}

// Plugin Root File.
if ( ! defined( 'FLOWMATTIC_APP_URL' ) ) {
	define( 'FLOWMATTIC_APP_URL', WP_CONTENT_URL . '/flowmattic-apps' );
}

// FlowMattic Updates Server.
if ( ! defined( 'FLOWMATTIC_UPDATES_SERVER' ) ) {
	define( 'FLOWMATTIC_UPDATES_SERVER', 'https://updates.flowmattic.com' );
}

// Include FlowMattic Main File.
require_once wp_normalize_path( FLOWMATTIC_PLUGIN_DIR . '/inc/class-flowmattic.php' );

/**
 * Include the Flowmattic autoloader class.
 */
require_once wp_normalize_path( FLOWMATTIC_PLUGIN_DIR . '/inc/class-flowmattic-autoload.php' );

/**
 * Instantiate the autoloader.
 */
new FlowMattic_Autoload();

/**
 * Instantiates the FlowMattic class.
 * Make sure the class is properly set-up.
 * The FlowMattic class is a singleton
 * so we can directly access the one true FlowMattic object using this function.
 *
 * @return object FlowMattic
 */
function wp_flowmattic() {
	return FlowMattic::get_instance();
}

/**
 * Instantiate FlowMattic class.
 *
 * @since 1.0
 * @return void
 */
function infi_activate__flowmattic() {
	// Include the helpers.
	require_once wp_normalize_path( FLOWMATTIC_PLUGIN_DIR . '/inc/helpers.php' );

	// Initiate the FlowMattic.
	wp_flowmattic();

	// Check if version is different, reset the option to create database.
	if ( FLOWMATTIC_VERSION !== get_option( 'flowmattic_version' ) ) {
		delete_option( 'flowmattic_data_tables_created' );
		update_option( 'flowmattic_version', FLOWMATTIC_VERSION );

		// Clear the cache.
		delete_transient( 'flowmattic_license_response_check' );
		delete_transient( 'flowmattic_workflow_templates' );
		delete_transient( 'flowmattic_integrations' );

		// Set the default settings.
		$settings = get_option( 'flowmattic_settings', array() );

		$settings['enable_notifications_connect'] = isset( $settings['enable_notifications_connect'] ) ? $settings['enable_notifications_connect'] : 'yes';
		$settings['notification_email_connect']   = isset( $settings['notification_email_connect'] ) ? $settings['notification_email_connect'] : $settings['notification_email'];

		update_option( 'flowmattic_settings', $settings );
	}

	// Check the license status.
	wp_flowmattic()->check_license();

	// Create database tables if not exist.
	$flowmattic_data_tables_created = get_option( 'flowmattic_data_tables_created', false );
	if ( ! $flowmattic_data_tables_created ) {
		wp_flowmattic()->create_tables();
	}
}
add_action( 'after_setup_theme', 'infi_activate__flowmattic', 11 );

/*
 * This class handles the MCP server functionality for FlowMattic.
 * It allows for the creation and management of MCP tools.
 *
 * @package FlowMattic
 * @since 5.2.0
 */
require_once wp_normalize_path( FLOWMATTIC_PLUGIN_DIR . '/inc/class-flowmattic-mcp-server.php' );
if ( class_exists( 'FlowMattic_MCP_Server' ) ) {
	new FlowMattic_MCP_Server();
}

/**
 * Add MCP tools.
 *
 * @access public
 * @since 5.2.0
 * @param string $plugin_name Name of the plugin.
 * @param array  $tools       Array of tools to add.
 * @return void
 */
function flowmattic_add_mcp_tools( $plugin_name, $tools ) {
	if ( ! empty( $tools ) ) {
		FlowMattic_MCP_Server::add_plugin_tools( $plugin_name, $tools );
	}
}