<?php
namespace Admin;

/**
 * Class Assets
 * @package BBPCorePro\Admin
 */
class Assets {
	/**
	 * Assets constructor.
	 */
	public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 999);
	}
	
	public function admin_scripts() {
		wp_register_style( 'bbpc-admin', BBPC_ASSETS . 'css/bbpc-admin.css' );		
		wp_register_style( 'sweetalert', BBPC_ASSETS . '/css/sweetalert.css' );
		
		wp_register_script( 'sweetalert', BBPC_ASSETS . 'js/sweetalert.min.js', [ 'jquery' ], '1.0', true );
        wp_register_script( 'bbpc-notify-review', BBPC_ASSETS . 'admin/js/review.js', array( 'jquery' ), BBPC_VERSION, true );
		
		if ( bbpc_admin_pages('admin') ) {
			wp_enqueue_style( 'bbpc-admin' );
			wp_enqueue_style( 'normalize', BBPC_ASSETS . 'css/normalize.css' );
			wp_enqueue_style( 'nice-select', BBPC_ASSETS . 'css/nice-select.css' );
			wp_enqueue_style( 'bbpc-admin-ui', BBPC_ASSETS . 'css/admin-ui-style.css' );
			wp_enqueue_style( 'sweetalert' );

			// Scripts.
			wp_enqueue_script( 'modernizr', BBPC_ASSETS . 'js/modernizr-3.11.2.min.js', [ 'jquery' ], '3.11.2', true );
			wp_enqueue_script( 'jquery-ui', BBPC_ASSETS . 'js/jquery-ui.js', [ 'jquery' ], '1.12.1', true );
			wp_enqueue_script( 'mixitup', BBPC_ASSETS . 'js/mixitup.min.js', [ 'jquery' ], '3.3.1', true );
			wp_enqueue_script( 'mixitup-multifilter', BBPC_ASSETS . 'js/mixitup-multifilter.js', [ 'jquery' ], '3.3.1', true );
			wp_enqueue_script( 'jquery-nice-select', BBPC_ASSETS . 'js/jquery.nice-select.min.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_script( 'tabby-polyfills', BBPC_ASSETS . 'js/tabby.polyfills.min.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_script( 'sortable', BBPC_ASSETS . 'js/Sortable.min.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_script( 'accordion', BBPC_ASSETS . 'js/accordion.min.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_script( 'bbpc-admin-main', BBPC_ASSETS . 'js/admin-main.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_script( 'sweetalert' );
		}

		if ( bbpc_admin_pages('settings') ) {
            wp_enqueue_style( 'bbpc-admin' );
            wp_enqueue_style( 'sweetalert' );
			wp_enqueue_script( 'sweetalert' );
			wp_enqueue_script( 'bbpc-admin-global', BBPC_ASSETS . 'js/admin-global.js', BBPC_VERSION );
		}

		if ( bbpc_admin_pages('settings') ) {
			wp_deregister_style('csf-fa5');
			wp_deregister_style('csf-fa5-v4-shims');
		}
        
        // Localize the script with new data
        $ajax_url 				= admin_url('admin-ajax.php');
        $wpml_current_language 	= apply_filters('wpml_current_language', null);

        if ( !empty($wpml_current_language) ) {
            $ajax_url 			= add_query_arg('wpml_lang', $wpml_current_language, $ajax_url);
        }	

		wp_localize_script( 'jquery', 'bbp_core_local_object', [
                'ajaxurl' 			 => esc_url( $ajax_url ),
				'create_forum_title' => esc_html__( 'Enter Forum Title', 'bbp-core' ),
				'create_topic_title' => esc_html__( 'Enter Topic Title', 'bbp-core' ),
				'forum_delete_title' => esc_html__( 'Are you sure to delete?', 'bbp-core' ),
				'forum_delete_desc'  => esc_html__( "This forum will be deleted with all the topics and you won't be able to revert!", 'bbp-core' ),
				'topic_delete_desc'  => esc_html__( "This topic will be deleted and you won't be able to revert!", 'bbp-core' ),
				'BBPC_ASSETS'        => BBPC_ASSETS,
                'nonce' 			 => wp_create_nonce('bbpc-admin-nonce')  
			]
		);
	}
}