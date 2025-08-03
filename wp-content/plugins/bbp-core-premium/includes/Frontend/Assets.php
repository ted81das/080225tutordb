<?php
namespace BBPCorePro\Frontend;

/**
 * Class Assets
 * @package EazyDocs\Admin
 */
class Assets {

	/**
	 * Assets constructor.
	 */
	public function __construct() {
		add_action('wp_enqueue_scripts', [$this, 'frontend_scripts'], 999);
	}
	
	/**
	 * Register scripts and styles [ FRONTEND ]
	 */
	public function frontend_scripts() {		
		wp_register_style( 'bbpc-pro-frontend', BBPCOREPRO_ASSETS . '/frontend/css/frontend.css', array(), BBPCOREPRO_VERSION );
		wp_register_script( 'bbpc-pro-frontend', BBPCOREPRO_ASSETS . '/frontend/js/frontend.js', array('jquery'), BBPCOREPRO_VERSION, true );
		
		// localize
		if ( function_exists('is_bbpress') && class_exists('BBP_Core') ){

			wp_enqueue_script( 'bbpc-custom', BBPCOREPRO_ASSETS . '/js/custom.js', [ 'jquery' ], BBPC_VERSION, true );

            $opt 					= get_option('bbp_core_settings');
            $avatar 				= $opt['bbpc_notification_avatar'] ?? '';
            $img_url    			= ! empty ( $avatar['url'] ) ? $avatar['url'] : BBPCOREPRO_IMG . '/notification.svg';
			 
            $profile_menu_wrapper   	= $opt['bbpc_profile_location']['location_selector'] ?? '';
            $notification_menu_wrapper  = $opt['bbpc_notification_location']['location_selector'] ?? '';

			wp_localize_script( 'bbpc-pro-frontend', 'bbpc_localize_script', array(
				'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
				'nonce'   						=> wp_create_nonce( 'bbpc-nonce' ),
				'bbpc_subscribed_link' 			=> wp_kses_post(bbp_get_forum_subscription_link()),
				'bbpc_subscribed_forum_title'	=> bbpc_forum_title(),
				'bbpc_subscribed_forum_id'		=> bbp_get_forum_id(),
				'bbpc_current_topic_id'			=> get_the_ID(),

				'bbpc_mini_profile_avatar' 		=> get_avatar( get_current_user_id(), 32 ),
				'bbpc_mini_profile_content'		=> bbpc_mini_profile_content(),

				'bbpc_notification_unread_count' 	=> bbpc_notification_unread_count(),
				'bbpc_notification_render'			=> bbpc_notification_render(),

				'bbpc_notification_avatar' 		=> $img_url,
				'profile_menu_wrapper'			=> $profile_menu_wrapper,
				'notification_menu_wrapper' 	=> $notification_menu_wrapper				
			) );
		}
	}
}