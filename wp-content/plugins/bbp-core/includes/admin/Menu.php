<?php
namespace admin;

class Menu {
	function __construct() {
		add_action( 'admin_menu', [ $this, 'bbpc_admin_menu' ] );
	}

	/**
	 * Create Admin menu
	 *
	 * @return void
	 */
	public function bbpc_admin_menu() {
		$capability = 'manage_options';

		add_menu_page( __( 'BBP Core', 'bbp-core' ), __( 'BBP Core', 'bbp-core' ), $capability, 'bbp-core', [ $this, 'bbpc_plugin_page' ], 'dashicons-buddicons-bbpress-logo', 20 );
		add_submenu_page( 'bbp-core', __( 'Forum Builder', 'bbp-core' ), __( 'Forum Builder', 'bbp-core' ), $capability, 'bbp-core' );
		// add_submenu_page( 'bbp-core', __( 'BBP Core Dashboard', 'bbp-core' ), __( 'Dashboard', 'bbp-core' ), $capability, 'admin.php?page=bbp-core-dashboard', [ $this, 'bbpc_statistics_dashboard' ] );

		// Remove menu items.
		$opt = get_option( 'bbp_core_settings' );

		if ( isset($opt['is_bbp_post_types_hidden']) && ! $opt['is_bbp_post_types_hidden'] ) {
			remove_menu_page( 'edit.php?post_type=forum' );
			remove_menu_page( 'edit.php?post_type=topic' );
			remove_menu_page( 'edit.php?post_type=reply' );
		}
	}

	/**
	 * Plugin page callback function.
	 *
	 * @return void
	 */
	public function bbpc_plugin_page() {
		$opt = get_option( 'bbp_core_settings' );
		include plugin_dir_path( __FILE__ ) . '/menu/admin_ui.php';
	}

	/**
	 * Dashboard Statistics.
	 *
	 * @return void
	 */
	public function bbpc_statistics_dashboard() {
		include plugin_dir_path( __FILE__ ) . '/menu/statistics.php';
	}
}
