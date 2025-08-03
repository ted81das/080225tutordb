<?php
class Admin {
	/**
	 * Admin class construct
	 */
	public function __construct() {
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
		new admin\Menu();
	}
	
	/**
	 * Add body class to admin pages.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		// if current page is ?page=bbp-core in admin.
		if ( isset( $_GET['page'] ) && 'bbp-core' === $_GET['page'] ) {
			$classes .= ' bbpc-forum-ui';
		}

		// if has no pro plan.
		if ( bbpc_is_premium() !== true ) {
			$classes .= ' bbpc-no-pro';
		}

		if ( class_exists( 'BBPC_GEO_ROLES' ) ) {
			$classes .= ' bbpc-geo-roles';
		}
		
		return $classes;
	}
}
