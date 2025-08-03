<?php
/**
 * Get the value of a settings field.
 *
 * @param string $option  settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function bbpc_get_opt( $option, $default = '' ) {
	$options = get_option( 'bbp_core_settings' );

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}

/**
 * Check if a plugin has been installed for specific number of days
 *
 * @param string $plugin_path The plugin path (e.g. 'woocommerce/woocommerce.php')
 * @param int    $days        Number of days to check against
 * @return bool  True if plugin is installed for specified days, false otherwise
 */
function bbpc_is_plugin_installed_for_days( $days, $plugin_slug='eazydocs' ) {
	// Get the installation timestamp of the plugin
	$installed_time = get_option( $plugin_slug . '_installed' );

	// Ensure it's a valid timestamp
	if ( ! is_numeric( $installed_time ) || $installed_time <= 0 ) {
		return false;
	}

	// Convert days to seconds
	$required_time = (int) $days * DAY_IN_SECONDS;

	// Get the current UTC time
	$current_time = time();

	// Check if the plugin has been installed for the required duration
	return ( $current_time - $installed_time ) >= $required_time;
}

/**
 * Check If the Page is Forum page
 */
function bbpc_is_forum_page() {
	if ( in_array( 'bbpress', get_body_class() ) ) {
		return true;
	}
}

/**
 * Check if the pro plugin and plan is active
 *
 * @return bool|void
 */
function bbpc_is_premium() {
	if ( class_exists('BBPCorePro') && bc_fs()->can_use_premium_code() ) {
		return true;
	}
}

/**
 * BBP Core Admin pages
 * If any of the admin pages match the current page, return true.
 *
 * @return bool|void
 */
function bbpc_admin_pages($admin) {
	$current_url 	= ! empty( $_GET['page'] ) ? admin_url( 'admin.php?page=' ) . sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	
	if ( $admin == 'admin' ){
		if ( $current_url == admin_url('admin.php?page=bbp-core') ) {
			return true;
		}
	} elseif ( $admin == 'settings' ) {
		if ( $current_url == admin_url('admin.php?page=bbp-core-settings') ) {
			return true;
		}
	}
}

/**
 * BBP Forum Assets
 * Checks if the current page is a single forum or a single topic.
 *
 * @return bool True if the current page is a single forum or topic, false otherwise.
 */
function bbpc_forum_assets(){
	if ( bbp_is_single_forum() ||  bbp_is_single_topic() ) {
		return true;
	}
}


/**
 * Posts Arraty
 * @param object Post Type
 */
function bbp_core_get_posts( $post_type = 'forum' ) {
	$posts = get_pages(
		[
			'post_type' => $post_type,
			'parent'    => 0,
		]
	);

	$posts_array = [];

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$posts_array[ $post->ID ] = $post->post_title;
		}
	}

	return $posts_array;
}

/**
 * Limit letter
 * @param $string
 * @param $limit_length
 * @param string $suffix
 */
function bbp_core_limit_letter( $string, $limit_length, $suffix = '...' ) {
	if ( strlen( $string ) > $limit_length ) {
		echo strip_shortcodes( substr( $string, 0, $limit_length ) . $suffix );
	} else {
		echo strip_shortcodes( esc_html( $string ) );
	}
}

/**
 * Return the topic view count.
 *
 * @param int $topic_id Optional. Topic id
 *
 * @return int The view count
 * @uses get_post_meta() To get the view count meta
 * @uses bbp_get_topic_id() To get the topic id
 */
function bbp_get_topic_view_count( $topic_id = 0 ) {
	$topic_id = bbp_get_topic_id( $topic_id );

	if ( empty( $topic_id ) ) {
		return 0;
	}

	$views = (int) get_post_meta( $topic_id, '_btv_view_count', true );

	return $views;
}

/**
 * Output the topic view count.
 *
 * @param int $topic_id Optional. Topic id
 *
 * @uses bbp_get_topic_id() To get the topic id
 * @uses btv_get_topic_view_count() To get the view count for the topic
 */
function bbp_topic_view_count( $topic_id = 0 ) {
	$topic_id   = bbp_get_topic_id( $topic_id );
	$view_count = bbp_get_topic_view_count( $topic_id );
	return $view_count;
}


/**
 * Get forum title
 * @return string
 */
function bbpc_forum_title(){
    $forum_id       = bbp_get_forum_id();
    $forum_title    = get_the_title( $forum_id );
    return $forum_title;
}

/**
 * Customizer section hide from customizer
 */
add_action( 'customize_register', function( $wp_customize ) {
    // Unset the section you want to hide
    $wp_customize->remove_section( 'design_fields' );
}, 20 );