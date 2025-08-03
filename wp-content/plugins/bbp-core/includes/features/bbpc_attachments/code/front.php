<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GDATTFront {
	private $edit_mode = false;
	private $icons     = [
		'code'       => 'c|cc|h|js|class',
		'xml'        => 'xml',
		'excel'      => 'xla|xls|xlsx|xlt|xlw|xlam|xlsb|xlsm|xltm',
		'word'       => 'docx|dotx|docm|dotm',
		'image'      => 'png|gif|jpg|jpeg|jpe|jp|bmp|tif|tiff|svg',
		'psd'        => 'psd',
		'ai'         => 'ai',
		'archive'    => 'zip|rar|gz|gzip|tar',
		'text'       => 'txt|asc|nfo',
		'powerpoint' => 'pot|pps|ppt|pptx|ppam|pptm|sldm|ppsm|potm',
		'pdf'        => 'pdf',
		'html'       => 'htm|html|css',
		'video'      => 'avi|asf|asx|wax|wmv|wmx|divx|flv|mov|qt|mpeg|mpg|mpe|mp4|m4v|ogv|mkv',
		'documents'  => 'odt|odp|ods|odg|odc|odb|odf|wp|wpd|rtf',
		'audio'      => 'mp3|m4a|m4b|mp4|m4v|wav|ra|ram|ogg|oga|mid|midi|wma|mka',
		'icon'       => 'ico',
	];

	function __construct() {
		add_action( 'bbp_init', [ $this, 'load' ] );
	}

	public static function instance() {
		static $instance = false;

		if ( $instance === false ) {
			$instance = new GDATTFront();
		}

		return $instance;
	}

	public function load() {
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );

		add_action( 'bbp_theme_before_reply_form_submit_wrapper', [ $this, 'embed_form' ] );
		add_action( 'bbp_theme_before_topic_form_submit_wrapper', [ $this, 'embed_form' ] );

		add_action( 'bbp_edit_reply', [ $this, 'edit_reply' ], 10, 5 );
		add_action( 'bbp_edit_topic', [ $this, 'edit_topic' ], 10, 4 );
		add_action( 'bbp_new_reply', [ $this, 'save_reply' ], 10, 5 );
		add_action( 'bbp_new_topic', [ $this, 'save_topic' ], 10, 4 );

		add_filter( 'bbp_get_reply_content', [ $this, 'embed_attachments' ], 100, 2 );
		add_filter( 'bbp_get_topic_content', [ $this, 'embed_attachments' ], 100, 2 );

		if ( bbpc_bba_o( 'is_attachment_icon' ) == 1 ) {
			add_action( 'bbp_theme_before_topic_title', [ $this, 'show_attachments_icon' ] );
		}

		$this->register_scripts_and_styles();
	}

	private function icon( $ext ) {
		foreach ( $this->icons as $icon => $list ) {
			$list = explode( '|', $list );

			if ( in_array( $ext, $list ) ) {
				return $icon;
			}
		}

		return 'generic';
	}

	public function register_scripts_and_styles() {
		//TODO: Fix the constant for version
		wp_register_style( 'bbpc-attachments', BBPCATTACHMENT_URL . 'css/front.css', [], BBPC_VERSION );
		wp_register_script( 'bbpc-attachments', BBPCATTACHMENT_URL . 'js/front.js', [ 'jquery' ], BBPC_VERSION, true );
	}

	public function include_scripts_and_styles() {
		wp_enqueue_style( 'bbpc-attachments' );
		wp_enqueue_script( 'bbpc-attachments' );

		wp_localize_script(
			'bbpc-attachments',
			'bbpcAttachmentsInit',
			[
				'max_files'    => apply_filters( 'bbpc_bbpressattchment_allow_upload', BBPCATTCore::instance()->get_max_files(), bbp_get_forum_id() ),
				'are_you_sure' => __( 'This operation is not reversible. Are you sure?', 'bbp-core' ),
			]
		);
	}

	public function wp_enqueue_scripts() {
		if ( bbpc_is_bbpress() ) {
			$this->include_scripts_and_styles();
		}
	}

	public function edit_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		$this->edit_mode = true;
		$this->process_attachments( 0, $topic_id, $forum_id, $anonymous_data, $topic_author );
	}

	public function edit_reply( $reply_id, $topic_id, $forum_id, $anonymous_data = null, $reply_author = null ) {
		$this->edit_mode = true;
		$this->process_attachments( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author );
	}

	public function save_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		$this->edit_mode = false;
		$this->process_attachments( 0, $topic_id, $forum_id, $anonymous_data, $topic_author );
	}

	public function save_reply( $reply_id, $topic_id, $forum_id, $anonymous_data = null, $reply_author = null ) {
		$this->edit_mode = false;
		$this->process_attachments( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author );
	}

	public function process_attachments( $reply_id, $topic_id, $forum_id, $anonymous_data = null, $reply_author = null ) {
		$uploads  = [];
		$revision = false;

		if ( ! empty( $_FILES ) && ! empty( $_FILES['bbpc_attachment'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$errors    = new gdbbp_Error();
			$overrides = [
				'test_form'            => false,
				'upload_error_handler' => 'bbpc_bbattachment_handle_upload_error',
			];

			foreach ( $_FILES['bbpc_attachment']['error'] as $key => $error ) {
				$file_name = $_FILES['bbpc_attachment']['name'][ $key ];

				if ( $error == UPLOAD_ERR_OK ) {
					$file = [
						'name'     => $file_name,
						'type'     => $_FILES['bbpc_attachment']['type'][ $key ],
						'size'     => $_FILES['bbpc_attachment']['size'][ $key ],
						'tmp_name' => $_FILES['bbpc_attachment']['tmp_name'][ $key ],
						'error'    => $_FILES['bbpc_attachment']['error'][ $key ],
					];

					$file_name = sanitize_file_name( $file_name );

					if ( BBPCATTCore::instance()->is_right_size( $file, $forum_id ) ) {
						$upload = wp_handle_upload( $file, $overrides );

						if ( ! is_wp_error( $upload ) ) {
							$uploads[] = $upload;
						} else {
							$errors->add( 'wp_upload', $upload->errors['wp_upload_error'][0], $file_name );
						}
					} else {
						$errors->add( 'bbpc_upload', 'File exceeds allowed file size.', $file_name );
					}
				} else {
					switch ( $error ) {
						default:
						case 'UPLOAD_ERR_NO_FILE':
							$errors->add( 'php_upload', 'File not uploaded.', $file_name );
							break;
						case 'UPLOAD_ERR_INI_SIZE':
							$errors->add( 'php_upload', 'Upload file size exceeds PHP maximum file size allowed.', $file_name );
							break;
						case 'UPLOAD_ERR_FORM_SIZE':
							$errors->add( 'php_upload', 'Upload file size exceeds FORM specified file size.', $file_name );
							break;
						case 'UPLOAD_ERR_PARTIAL':
							$errors->add( 'php_upload', 'Upload file only partially uploaded.', $file_name );
							break;
						case 'UPLOAD_ERR_CANT_WRITE':
							$errors->add( 'php_upload', 'Can\'t write file to the disk.', $file_name );
							break;
						case 'UPLOAD_ERR_NO_TMP_DIR':
							$errors->add( 'php_upload', 'Temporary folder for upload is missing.', $file_name );
							break;
						case 'UPLOAD_ERR_EXTENSION':
							$errors->add( 'php_upload', 'Server extension restriction stopped upload.', $file_name );
							break;
					}
				}
			}
		}

		$post_id = $reply_id == 0 ? $topic_id : $reply_id;

		if ( ! empty( $errors->errors ) ) {
			foreach ( $errors->errors as $code => $errs ) {
				foreach ( $errs as $error ) {
					if ( $error[0] != '' && $error[1] != '' ) {
						add_post_meta(
							$post_id,
							'_bbp_attachment_upload_error',
							[
								'code'    => $code,
								'file'    => $error[1],
								'message' => $error[0],
							]
						);
					}
				}
			}

			$revision = true;
		}

		if ( ! empty( $uploads ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			foreach ( $uploads as $upload ) {
				$wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );
				$attachment  = [
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload['file'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				];

				$attach_id   = wp_insert_attachment( $attachment, $upload['file'], $post_id );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );

				wp_update_attachment_metadata( $attach_id, $attach_data );
				update_post_meta( $attach_id, '_bbp_attachment', '1' );
			}

			$revision = true;
		}

		if ( $this->edit_mode && $revision ) {
			add_filter( 'wp_save_post_revision_post_has_changed', [ $this, 'post_has_changed' ] );
		}
	}

	public function post_has_changed() {
		remove_filter( 'wp_save_post_revision_post_has_changed', [ $this, 'post_has_changed' ] );

		return true;
	}

	public function show_attachments_icon() {
		$topic_id = bbp_get_topic_id();
		$count    = bbpc_topic_attachments_count( $topic_id, true );

		if ( $count > 0 ) {
			echo '<span class="bbp-attachments-count" title="' . $count . ' ' . _n( 'attachment', 'attachments', $count, 'bbp-core' ) . '"></span>';
		}
	}

	public function embed_attachments( $content, $id ) {
		global $user_ID;

		$attachments = bbpc_get_post_attachments( $id );

		$post      = get_post( $id );
		$author_id = $post->post_author;

		$opt = get_option( 'bbp_core_settings' );

		if ( ! empty( $attachments ) ) {
			$content .= '<div class="bbp-attachments">';
			$content .= '<h6>' . __( 'Attachments', 'bbp-core' ) . ':</h6>';

			$_download 	= ' download';			

			if ( ! is_user_logged_in() && BBPCATTCore::instance()->is_hidden_from_visitors() ) {
				$content .= sprintf( __( "You must be <a href='%s'>logged in</a> to view attached files.", 'bbp-core' ), wp_login_url( get_permalink() ) );
			} else {
				$listing    = '<ol';
				$thumbnails = '<ol';

				$listing    .= ' class="with-icons d4p-bbp-listing"';
				$thumbnails .= ' class="with-icons d4p-bba-thumbnails"';			

				$listing    .= '>';
				$thumbnails .= '>';
				$images      = $files = 0;

				foreach ( $attachments as $attachment ) {
					$actions = [];

					$url = add_query_arg( '_wpnonce', wp_create_nonce( 'd4p-bbpress-attachments' ) );
					$url = add_query_arg( 'att_id', $attachment->ID, $url );
					$url = add_query_arg( 'bbp_id', $id, $url );

					$allow = 'no';
					if ( bbpc_is_user_admin() ) {
						$allow = 'delete';
					} elseif ( bbpc_is_user_moderator() ) {
						$allow = 'delete';
					} elseif ( $author_id == $user_ID ) {
						$allow = false;
					}

					if ( $allow == 'delete' || $allow == 'both' ) {
						$actions[] = '<a class="d4p-bba-action-delete" href="' . esc_url( add_query_arg( 'd4pbbaction', 'delete', $url ) ) . '">' . __( 'delete', 'bbp-core' ) . '</a>';
					}
					
					if ( $allow == 'detach' || $allow == 'both' ) {
						$actions[] = '<a class="d4p-bba-action-detach" href="' . esc_url( add_query_arg( 'd4pbbaction', 'detach', $url ) ) . '">' . __( 'detach', 'bbp-core' ) . '</a>';
					}

					if ( count( $actions ) > 0 ) {
						$actions = ' <span class="d4p-bba-actions">[' . join( ' | ', $actions ) . ']</span>';
					} else {
						$actions = '';
					}

					$file     = get_attached_file( $attachment->ID );
					$ext      = pathinfo( $file, PATHINFO_EXTENSION );
					$filename = pathinfo( $file, PATHINFO_BASENAME );
					$file_url = wp_get_attachment_url( $attachment->ID );

					$html    = $class_li = $class_a = $rel_a = '';
					$a_title = $filename;
					$caption = false;

					$is_lighbox = '';
					if ( bbpc_is_premium() ) {
						$is_lighbox = $opt['image_link_type'] == 'lightbox' ? ' bbpc-lightbox' : '';
						$_download 	= $opt['image_link_type'] == 'lightbox' ? '' : $_download;
					}

					$img = false;
					if ( ( $opt['attachment_image_x'] ?? false ) && ( $opt['attachment_image_y'] ?? false ) ) {
						$html = wp_get_attachment_image( $attachment->ID, 'bbpc-attachment-thumb' );

						if ( $html != '' ) {
							$img = true;

							$class_li = 'bbp-atthumb';

							$caption = bbpc_bba_o( 'image_thumbnail_caption' ) == 1;
						}
					}

					$item = '<li id="bbpcore-attach_' . $attachment->ID . '" class="bbpcore-attach bbpcore-attachment-' . $ext . ' ' . $class_li . '">';

					if ( $html == '' ) {
						$html = $filename;
                        $class_li = 'bbp-atticon bbp-atticon-' . $this->icon( $ext );
					}

					if ( $img ) {
						
						if ( $caption ) {
							$item .= '<div style="width: ' . bbpc_bba_o( 'attachment_image_x' ) . 'px" class="wp-caption">';
						}

						$item .= '<a class="' . $class_a . $is_lighbox . '" href="' . $file_url . '" title="' . $a_title .'" '.  $_download .'>' . $html . '</a>';

						if ( $caption ) {
							$a_title = '<a class="' . $is_lighbox . '" href="' . $file_url . '"' . $_download . '>' . $a_title . '</a>';

							$item .= '<p class="wp-caption-text">' . $a_title . '<br/>' . $actions . '</p></div>';
						}
					} else {
						$item .= '<span role="presentation" class="' . $class_li . '"></span> ';
						$item .= '<div class="d4p-bbp-att-wrapper"><a class="' . $class_a . $is_lighbox . '"' . $rel_a . $_download . ' href="' . $file_url . '" title="' . $a_title . '">' . $html . '</a>' . $actions . '</div>';
					}

					$item .= '</li>';

					if ( $img ) {
						$thumbnails .= $item;
						$images++;
					} else {
						$listing .= $item;
						$files++;
					}
				}

				$thumbnails .= '</ol>';
				$listing    .= '</ol>';

				if ( $images > 0 ) {
					$content .= $thumbnails;
				}

				if ( $files > 0 ) {
					$content .= $listing;
				}
			}

			$content .= '</div>';
		}

		if ( bbpc_is_user_admin() || bbpc_is_user_moderator() ) {
			$errors = get_post_meta( $id, '_bbp_attachment_upload_error' );

			if ( ! empty( $errors ) ) {
				$content .= '<div class="bbp-attachments-errors">';
				$content .= '<h6>' . __( 'Upload Errors', 'bbp-core' ) . ':</h6>';
				$content .= '<ol';

				$class_li = 'bbp-file-error';

				$content  .= ' class="with-icons"';
				$class_li .= ' bbp-atticon bbp-atticon-error';

				$content .= '>';

				foreach ( $errors as $error ) {
					$content .= '<li class="' . $class_li . '"><span role="presentation" class="' . $class_li . '"></span> ';
					$content .= '<div class="d4p-bbp-att-wrapper"><strong>' . esc_html( $error['file'] ) . '</strong>: ' . __( $error['message'], 'bbp-core' ) . '</div></li>';
				}

				$content .= '</ol></div>';
			}
		}

		return $content;
	}

	public function embed_form() {
		$can_upload = apply_filters( 'bbpc_bbpressattchment_allow_upload', BBPCATTCore::instance()->is_user_allowed(), bbpc_get_forum_id() );
		if ( ! $can_upload ) {
			return;
		}

		$is_enabled = apply_filters( 'bbpc_bbpressattchment_forum_enabled', BBPCATTCore::instance()->enabled_for_forum(), bbpc_get_forum_id() );
		if ( ! $is_enabled ) {
			return;
		}

		$file_size = apply_filters( 'bbpc_bbpressattchment_max_file_size', BBPCATTCore::instance()->get_file_size(), bbpc_get_forum_id() );

		include BBPCATTACHMENTS_PATH . 'forms/uploader.php';
	}
}
