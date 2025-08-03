<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BBPCATTCore {
	private $wp_version;
	private $plugin_path;
	private $plugin_url;

	public $l;
	public $o;

	function __construct() {
		global $wp_version;

		$this->wp_version = substr( str_replace( '.', '', $wp_version ), 0, 2 );

		$this->o = get_option( 'bbp_core_settings' );

		$this->plugin_path = dirname( dirname( __FILE__ ) ) . '/';
		$this->plugin_url  = plugins_url( '/', __DIR__ );

		define( 'BBPCATTACHMENT_URL', $this->plugin_url );
		define( 'BBPCATTACHMENTS_PATH', $this->plugin_path );

		add_action( 'after_setup_theme', [ $this, 'load' ], 5 );
		add_action( 'before_delete_post', [ $this, 'topic_attachment_deletion' ]);
		add_action( 'before_delete_post', [ $this, 'reply_attachment_deletion' ]);
	}

	public static function instance() {
		static $instance = false;

		if ( $instance === false ) {
			$instance = new BBPCATTCore();
		}

		return $instance;
	}

	private function _upgrade( $old, $new ) {
		foreach ( $new as $key => $value ) {
			if ( ! isset( $old[ $key ] ) ) {
				$old[ $key ] = $value;
			}
		}

		$unset = [];
		foreach ( $old as $key => $value ) {
			if ( ! isset( $new[ $key ] ) ) {
				$unset[] = $key;
			}
		}

		foreach ( $unset as $key ) {
			unset( $old[ $key ] );
		}

		return $old;
	}

	public function load() {
		add_action( 'init', [ $this, 'init_thumbnail_size' ], 1 );
		add_action( 'init', [ $this, 'delete_attachments' ] );

		add_action( 'before_delete_post', [ $this, 'delete_post' ] );

		if ( is_admin() ) {
			require_once BBPCATTACHMENTS_PATH . 'code/admin.php';
			require_once BBPCATTACHMENTS_PATH . 'code/meta.php';

			GDATTAdmin::instance();
			GDATTAdminMeta::instance();
		} else {
			require_once BBPCATTACHMENTS_PATH . 'code/front.php';

			GDATTFront::instance();
		}
	}

	public function init_thumbnail_size() {
		add_image_size( 'bbpc-attachment-thumb', $this->o['attachment_image_x'] ?? 300, $this->o['attachment_image_y'] ?? 300, true );
	}

	public function delete_attachments() {
		if ( isset( $_GET['d4pbbaction'] ) ) {
			$nonce = wp_verify_nonce( $_GET['_wpnonce'], 'd4p-bbpress-attachments' );

			if ( $nonce ) {
				global $user_ID;

				$action = $_GET['d4pbbaction'];
				$att_id = intval( $_GET['att_id'] );
				$bbp_id = intval( $_GET['bbp_id'] );

				$post      = get_post( $bbp_id );
				$author_ID = $post->post_author;

				$file = get_attached_file( $att_id );
				$file = pathinfo( $file, PATHINFO_BASENAME );

				$allow = 'no';
				if ( bbpc_is_user_admin() ) {
					$allow = 'delete';
				} elseif ( bbpc_is_user_moderator() ) {
					$allow =  'delete';
				} elseif ( $author_ID == $user_ID ) {
					$allow = false;
				}

				if ( $action == 'delete' && ( $allow == 'delete' || $allow == 'both' ) ) {
					wp_delete_attachment( $att_id );

					add_post_meta(
						$bbp_id,
						'_bbp_attachment_log',
						[
							'code' => 'delete_attachment',
							'user' => $user_ID,
							'file' => $file,
						]
					);
				}

				if ( $action == 'detach' && ( $allow == 'detach' || $allow == 'both' ) ) {
					global $wpdb;
					$wpdb->update( $wpdb->posts, [ 'post_parent' => 0 ], [ 'ID' => $att_id ] );

					add_post_meta(
						$bbp_id,
						'_bbp_attachment_log',
						[
							'code' => 'detach_attachment',
							'user' => $user_ID,
							'file' => $file,
						]
					);
				}
			}

			$url = remove_query_arg( [ '_wpnonce', 'd4pbbaction', 'att_id', 'bbp_id' ] );
			wp_redirect( $url );
			exit;
		}
	}

	public function delete_post( $id ) {
		if ( bbpc_has_bbpress() ) {
			if ( bbp_is_reply( $id ) || bbp_is_topic( $id ) ) {
				//TODO: Add conditions in pro
				// if ( $this->o['delete_attachments'] == 'delete' ) {
				// 	$files = bbpc_get_post_attachments( $id );

				// 	if ( is_array( $files ) && ! empty( $files ) ) {
				// 		foreach ( $files as $file ) {
				// 			wp_delete_attachment( $file->ID );
				// 		}
				// 	}
				// } elseif ( $this->o['delete_attachments'] == 'detach' ) {
					global $wpdb;

					$wpdb->update(
						$wpdb->posts,
						[ 'post_parent' => 0 ],
						[
							'post_parent' => $id,
							'post_type'   => 'attachment',
						]
					);
				//}
			}
		}
	}

	public function enabled_for_forum( $id = 0 ) {
		$meta = get_post_meta( bbp_get_forum_id( $id ), '_gdbbatt_settings', true );
		return ! isset( $meta['disable'] ) || ( isset( $meta['disable'] ) && $meta['disable'] == 0 );
	}

	public function get_file_size( $global_only = false, $forum_id = 0 ) {
		$forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;
		
		if ( ! class_exists( 'BBPCorePro' ) ){
			$value    		= 512;
			// $value    	= (int) $this->o['max_file_size'] ?? 512;
		}else{
			$value    	= $this->o['max_file_size'] ?? 512;
		}

		if ( ! $global_only ) {
			$meta = get_post_meta( $forum_id, '_gdbbatt_settings', true );

			if ( is_array( $meta ) && $meta['to_override'] == 1 ) {
				$value = $meta['max_file_size'];
			}
		}

		return $value;
	}

	public function get_max_files( $global_only = false, $forum_id = 0 ) {
		$forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;
		if( ! class_exists( 'BBPCorePro' ) ){
			$value    		= 4;
		}else{
			$value    = $this->o['max_file_uploads'] ?? 4;
		}

		if ( ! $global_only ) {
			$meta = get_post_meta( $forum_id, '_gdbbatt_settings', true );

			if ( is_array( $meta ) && $meta['to_override'] == 1 ) {
				$value = $meta['max_to_upload'] ?? 4;
			}
		}

		return $value;
	}

	public function is_right_size( $file, $forum_id = 0 ) {
		$forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;

		$file_size = apply_filters( 'bbpc_bbpressattchment_max_file_size', $this->get_file_size( false, $forum_id ), $forum_id );

		return $file['size'] < $file_size * 1024;
	}

	public function is_user_allowed() {
		$allowed = false;

		if ( is_user_logged_in() ) {
			if ( ! isset( $this->o['roles_to_upload'] ) ) {
				$allowed = true;
			} else {
				$value = $this->o['roles_to_upload'];
				if ( ! is_array( $value ) ) {
					$allowed = true;
				}

				global $current_user;
				if ( is_array( $current_user->roles ) ) {
					$matched = array_intersect( $current_user->roles, $value );
					$allowed = ! empty( $matched );
				}
			}
		}

		return apply_filters( 'bbpc_bbpressattchment_is_user_allowed', $allowed );
	}

	public function is_hidden_from_visitors( $forum_id = 0 ) {
		$forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;

		$value = $this->o['is_hide_attachment'] ?? 1;
		$meta  = get_post_meta( $forum_id, '_gdbbatt_settings', true );

		if ( is_array( $meta ) && $meta['to_override'] == 1 ) {
			$value = $meta['hide_from_visitors'];
		}

		return apply_filters( 'bbpc_bbpressattchment_is_hidden_from_visitors', $value == 1 );
	}

	public function topic_attachment_deletion($topic_id){
		if( class_exists( 'BBPCorePro' ) ){
			$topic_deletion = $this->o['is_attachment_deletion'] ?? '';
			
			if( $topic_deletion == 1 && get_post_type($topic_id) == bbp_get_topic_post_type() ) {
				$topic_attachments = get_attached_media( '', $topic_id );			
				foreach ($topic_attachments as $attachment) {
				wp_delete_attachment( $attachment->ID, 'true' );
				}
			}
		}
	}

	public function reply_attachment_deletion($reply_id){

		if( class_exists( 'BBPCorePro' ) ){
			$reply_deletion = $this->o['is_attachment_deletion'] ?? '';

			if( $reply_deletion == 1 && get_post_type($reply_id) == bbp_get_reply_post_type() ) {
				$replies_attachments = get_attached_media( '', $reply_id );
			
				foreach ($replies_attachments as $attachment) {
				wp_delete_attachment( $attachment->ID, 'true' );
				}
			}
		}
	}
}
