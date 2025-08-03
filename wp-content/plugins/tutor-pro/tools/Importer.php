<?php
/**
 * TutorPro Importer
 *
 * @package TutorPro\Tools
 * @author  Themeum<support@themeum.com>
 * @link    https://themeum.com
 * @since   3.6.0
 */

namespace TutorPro\Tools;

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Options_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importer class
 */
class Importer {
	/**
	 * Course Importer Class Instance.
	 *
	 * @since 3.6.0
	 */
	private $course_importer;


	/**
	 * Quiz Importer Class Instance.
	 *
	 * @since 3.6.0
	 */
	private $quiz_importer;

	/**
	 * Assignment Importer Class Instance.
	 *
	 * @since 3.6.0
	 */
	private $assignment_importer;

	/**
	 * Bundle Importer Class Instance.
	 *
	 * @since 3.6.0
	 */
	private $bundle_importer;

	/**
	 * Import option name
	 *
	 * Each job id will be concat with this option name
	 *
	 * @since 3.6.0
	 */
	const OPT_NAME = 'tutor_pro_import_';


	/**
	 * Error log key for importer.
	 *
	 * @since 3.6.0
	 */
	const TUTOR_IMPORTER_ERROR_LOG = 'TUTOR_IMPORTER_ERROR';

	/**
	 * Importer class constructor.
	 */
	public function __construct() {
		$this->course_importer     = new CourseImporter();
		$this->quiz_importer       = new QuizImporter();
		$this->assignment_importer = new AssignmentImporter();
		$this->bundle_importer     = new BundleImporter();
	}

	/**
	 * Import tutor settings.
	 *
	 * @since 3.6.0
	 *
	 * @param array $data array of settings data.
	 *
	 * @return bool|\WP_Error
	 */
	public function import_settings( $data ) {
		if ( is_array( $data ) && count( $data ) ) {
			$update_option = tutor_utils()->sanitize_recursively( $data );

			$tutor_option = get_option( 'tutor_option' );

			if ( $update_option === $tutor_option || maybe_serialize( $tutor_option ) === maybe_serialize( $update_option ) ) {
				return true;
			}

			$response = update_option( 'tutor_option', $update_option );

			( new Options_V2( false ) )->update_settings_log( $update_option, 'Imported' );
			return $response;
		}
	}

	/**
	 * Prepare tutor settings.
	 *
	 * @since 3.6.0
	 *
	 * @param array $data array of settings data.
	 *
	 * @return void
	 */
	public function prepare_tutor_settings( $settings ) {
		$data = $settings;

		$skip_options = array(
			'tutor_dashboard_page_id',
			'tutor_toc_page_id',
			'tutor_cart_page_id',
			'tutor_checkout_page_id',
			'course_permalink_base',
			'lesson_permalink_base',
			'membership_pricing_page_id',
			'quiz_permalink_base',
			'assignment_permalink_base',
			'student_register_page',
			'instructor_register_page',
			'course_archive_page',
			'tutor_certificate_page',
		);

		foreach ( $skip_options as $option_key ) {
			if ( isset( $data[ $option_key ] ) ) {
				unset( $data[ $option_key ] );
			}
		}

		return $data;
	}

	/**
	 * Import bundle using importer.
	 *
	 * @since 3.6.0
	 *
	 * @param array $post the bundle data.
	 * @param bool  $keep_media_files whether to download media files or not.
	 * @param array $course_ids_map array of new course ids map to previous.
	 *
	 * @return bool|\WP_Error
	 */
	public function import_bundle( array $post, bool $keep_media_files = false, $course_ids_map = array() ) {
		if ( is_array( $post ) && count( $post ) ) {
			$courses           = $post['courses'] ?? null;
			$meta              = $post['meta'] ?? null;
			$course_ids        = array();
			$failed_course_ids = array();
			$thumbnail_url     = $post['thumbnail_url'] ?? null;
			$attachment_links  = $post['attachment_links'] ?? null;
			$attachment_ids    = array();

			if ( $meta ) {
				$this->bundle_importer->prepare_bundle_meta( $meta );
			}

			$post = $this->prepare_post_data( $post );

			if ( is_wp_error( $post ) ) {
				return $post;
			}

			$post['post_status'] = 'draft';

			$id = wp_insert_post( $post, true );

			if ( is_wp_error( $id ) ) {
				return $id;
			}

			if ( $thumbnail_url && $keep_media_files ) {
				$this->save_post_thumbnail( $thumbnail_url, $id );
			}

			if ( $attachment_links && $keep_media_files ) {
				$attachment_ids = $this->get_post_attachments_id( $attachment_links );
			}

			if ( $attachment_ids ) {
				update_post_meta( $id, '_tutor_attachments', maybe_serialize( $attachment_ids ) );
			}

			if ( $courses ) {
				foreach ( $courses as $post ) {
					$course_id = 0;
					if ( $course_ids_map && in_array( $post['ID'], array_keys( $course_ids_map ) ) ) {
						$course_id = $course_ids_map[ $post['ID'] ];
					}

					if ( $course_id ) {
						$course_ids[] = $course_id;
					}
				}
			}

			if ( $course_ids ) {
				$this->bundle_importer->update_course_bundle_ids( $id, $course_ids );
			}

			if ( $id ) {
				if ( $meta ) {
					$this->bundle_importer->prepare_bundle_meta( $meta );
				}
				$id = wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'publish',
					),
					true
				);
				if ( is_wp_error( $id ) ) {
					return $id;
				}
			}

			return array(
				'bundle_id'         => $id,
				'failed_course_ids' => $failed_course_ids,
			);
		}
	}

	/**
	 * Import tutor posts recursively.
	 *
	 * @since 3.6.0
	 *
	 * @param array $posts the array of data to import.
	 * @param bool  $keep_media_files whether to download media files or not.
	 *
	 * @return array|bool|\WP_Error
	 */
	public function import_content( array $posts, bool $keep_media_files = false ) {
		if ( is_array( $posts ) && count( $posts ) ) {
			$parent_id = 0;
			foreach ( $posts as $post ) {
				$contents         = $post['contents'] ?? null;
				$children         = $post['children'] ?? null;
				$taxonomies       = $post['taxonomies'] ?? null;
				$meta             = $post['meta'] ?? null;
				$thumbnail_url    = $post['thumbnail_url'] ?? null;
				$attachment_links = $post['attachment_links'] ?? null;
				$question_answer  = $post['question_answer'] ?? null;
				$attachment_ids   = array();

				if ( $meta ) {
					$this->course_importer->prepare_course_meta( $meta, $keep_media_files );
				}

				if ( $meta && get_tutor_post_types( 'quiz' ) === $post['post_type'] ) {
					$this->quiz_importer->prepare_quiz_meta( $meta );
				}

				if ( $taxonomies ) {
					$this->set_tutor_course_taxonomies( $taxonomies );
				}
				// Prepare post data before insert.
				$post = $this->prepare_post_data( $post );

				if ( is_wp_error( $post ) ) {
					if ( WP_DEBUG_LOG ) {
						error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $post->get_error_message() );
					}
					return $post;
				}

				$parent_id = wp_insert_post( $post, true );

				if ( is_wp_error( $parent_id ) ) {
					if ( WP_DEBUG_LOG ) {
						error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $parent_id->get_error_message() );
					}
					return $parent_id;
				}

				if ( $thumbnail_url && $keep_media_files ) {
					$this->save_post_thumbnail( $thumbnail_url, $parent_id );
				}

				if ( $attachment_links && $keep_media_files ) {
					$attachment_ids = $this->get_post_attachments_id( $attachment_links );
				}

				if ( $attachment_ids && get_tutor_post_types( 'assignment' ) !== get_post_type( $parent_id ) ) {
					update_post_meta( $parent_id, '_tutor_attachments', maybe_serialize( $attachment_ids ) );
				}

				if ( $meta && get_tutor_post_types( 'assignment' ) === get_post_type( $parent_id ) ) {
					$this->assignment_importer->set_assignment_meta( $meta, $parent_id, $attachment_ids );
				}

				if ( $taxonomies ) {
					$result = $this->course_importer->course_importer_set_categories_tags( array( $parent_id => $taxonomies ) );
					if ( is_wp_error( $result ) ) {
						if ( WP_DEBUG_LOG ) {
							error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $result->get_error_message() );
						}
						return $result;
					}
				}

				if ( get_tutor_post_types( 'quiz' ) === get_post_type( $parent_id ) && $question_answer ) {
					$quiz_question_answer = $this->quiz_importer->flatten_quiz_question_answer( array( $parent_id => $question_answer ) );
					$response             = $this->quiz_importer->save_quiz_questions_answers( $quiz_question_answer );

					if ( is_wp_error( $response ) && WP_DEBUG_LOG ) {
						error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $response->get_error_message() );
					}
				}

				if ( $contents ) {
					$contents = $this->add_post_parent( $contents, $parent_id );
					$this->import_content( $contents, $keep_media_files );
				}

				if ( $children ) {
					$children = $this->add_post_parent( $children, $parent_id );
					$this->import_content( $children, $keep_media_files );
				}
			}

			return $parent_id;
		}
	}

	/**
	 * Get attachment ids from attachment url.
	 *
	 * @since 3.6.0
	 *
	 * @param array $attachment_urls the attachment url list.
	 *
	 * @return array
	 */
	public function get_post_attachments_id( array $attachment_urls ) {
		$attachment_ids = array();
		foreach ( $attachment_urls as $url ) {
			if ( $url ) {
				$upload_data = $this->url_upload_file( $url );

				if ( is_wp_error( $upload_data ) ) {
					if ( WP_DEBUG_LOG ) {
						error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $upload_data->get_error_message() );
					}
					continue;
				}
				$attachment_ids[] = $upload_data['id'];
			}
		}

		return $attachment_ids;
	}

	/**
	 * Upload and save post thumbnail meta.
	 *
	 * @since 3.6.0
	 *
	 * @param array   $thumbnail_url the thumbnail urls array.
	 * @param integer $post_id the post id to save meta.
	 *
	 * @return bool|\WP_Error
	 */
	public function save_post_thumbnail( string $thumbnail_url, int $post_id ) {

		$upload_data = $this->url_upload_file( $thumbnail_url );
		$response    = true;

		if ( ! is_wp_error( $upload_data ) ) {
			$response = set_post_thumbnail( $post_id, $upload_data['id'] );
		} else {
			if ( WP_DEBUG_LOG ) {
				error_log( self::TUTOR_IMPORTER_ERROR_LOG . ' : ' . $upload_data->get_error_message() );
			}
		}

		return $response;
	}

	/**
	 * Replace old parent ids with new parent ids after insertion.
	 *
	 * @since 3.6.0
	 *
	 * @param array $contents the array of contents to replace parent id.
	 * @param array $parent_ids the array of parent ids to replace.
	 *
	 * @return array
	 */
	public function replace_parent_ids( $contents, $parent_ids ) {
		$data = array();

		foreach ( array_keys( $contents ) as $key => $id ) {
			$data[ $parent_ids[ $key ] ] = $contents[ $id ];
		}

		return $data;
	}

	/**
	 * Flatten an array with child content as value and key as parent id,
	 * replace old parent id with parent id from key.
	 *
	 * @since 3.6.0
	 *
	 * @param array $contents the array of contents to flatten.
	 * @param array $parent_ids the array of parent ids to replace.
	 *
	 * @return array
	 */
	public function add_post_parent( $contents, $parent_id ) {
		$posts = array();
		foreach ( $contents as $content ) {
			$content['post_parent'] = $parent_id;
			$posts[]                = $content;
		}
		return $posts;
	}

	/**
	 * Inserts categories and tags if not exist in new site.
	 *
	 * @since 3.6.0
	 *
	 * @param array $taxonomies
	 *
	 * @return bool|\WP_Error
	 */
	public function set_tutor_course_taxonomies( $taxonomies ) {
		$categories = array_merge( ...array_column( $taxonomies, 'categories' ) );
		$tags       = array_merge( ...array_column( $taxonomies, 'tags' ) );

		if ( $categories ) {
			foreach ( $categories as $category ) {
				if ( ! term_exists( $category['name'] ) ) {

					if ( $category['parent'] ) {
						$category_list      = array_column( $categories, 'name', 'term_id' );
						$parent_term_name   = $category_list[ $category['parent'] ];
						$term               = get_term_by( 'name', $parent_term_name, CourseModel::COURSE_CATEGORY );
						$category['parent'] = $term ? $term->term_id : 0;
					}

					$response = wp_insert_term(
						$category['name'],
						CourseModel::COURSE_CATEGORY,
						array(
							'parent'      => $category['parent'],
							'description' => $category['description'],
							'slug'        => $category['slug'],
						)
					);

					if ( is_wp_error( $response ) ) {
						return $response;
					}
				}
			}
		}

		if ( $tags ) {
			foreach ( $tags as $tag ) {
				if ( ! term_exists( $tag['name'] ) ) {
					$response = wp_insert_term(
						$tag['name'],
						CourseModel::COURSE_TAG,
						array(
							'parent'      => $tag['parent'],
							'description' => $tag['description'],
							'slug'        => $tag['slug'],
						)
					);

					if ( is_wp_error( $response ) ) {
						return $response;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Prepare post data before insertion.
	 *
	 * @since 3.6.0
	 *
	 * @param array $post the post data to prepare.
	 *
	 * @return array|\WP_Error
	 */
	public function prepare_post_data( $post ) {

		if ( isset( $post['ID'] ) ) {
			unset( $post['ID'] );
		}

		$post = sanitize_post( $post, 'db' );

		$rules = array(
			'post_title'  => 'required',
			'post_type'   => 'required|match_string:' . implode( ',', get_tutor_post_types() ),
			'post_status' => 'required|match_string:' . implode( ',', CourseModel::get_status_list() ),
		);

		$validate_content = ValidationHelper::validate( $rules, $post );

		if ( ! $validate_content->success ) {
			return new \WP_Error( 'invalid_post_data', __( 'Post data is invalid', 'tutor-pro' ), $validate_content->errors );
		}

		$post['post_author'] = get_current_user_id();
		return $post;
	}


	/**
	 * Handle file upload from given url.
	 *
	 * @since 3.6.0
	 *
	 * @param string $file_url the file url.
	 *
	 * @return int|\WP_Error
	 */
	public static function url_upload_file( $file_url ) {

		if ( empty( $file_url ) || ! filter_var( $file_url, FILTER_VALIDATE_URL ) ) {
			return new \WP_Error( 'invalid_file_url', 'Invalid file URL provided.' );
		}

		$upload_dir = wp_upload_dir()['basedir'];

		$parse_url = parse_url( $file_url );

		$base_url = $parse_url['scheme'] . '://' . $parse_url['host'];

		if ( isset( $parse_url['port'] ) ) {
			$base_url .= ':' . $parse_url['port'];
		}

		$file_name       = basename( $file_url );
		$source_dir_url  = str_replace( $file_name, '', $file_url );
		$source_dir_part = str_replace( $base_url . '/wp-content/uploads/', '', $source_dir_url );

		$file_path = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part ) . $file_name;

		$upload_dir = trailingslashit( $upload_dir ) . trailingslashit( $source_dir_part );

		try {
			if ( ! file_exists( $file_path ) ) {

				if ( ! file_exists( $upload_dir ) ) {
					mkdir( $upload_dir, 0777, true );
				}

				$file_data = file_get_contents( $file_url );
				if ( false !== $file_data ) {
					// Save the image to the uploads directory.
					file_put_contents( $file_path, $file_data );
				} else {
					return new \WP_Error( 'download_failed', 'Failed to download content ' . $file_url );
				}
			}
		} catch ( \Throwable $th ) {
			return new \WP_Error( 'download_failed', 'Failed to download content ' . $file_url, $th->getMessage() );
		}

		$file_type = wp_check_filetype( $file_name );

		$file_url = str_replace( $source_dir_url, site_url( '/wp-content/uploads/' . $source_dir_part ), $file_url );

		$attachment_args = array(
			'guid'           => $file_url,
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment_args, $file_path, 0, true );

		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		if ( wp_attachment_is_image( $attach_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}

		return array(
			'url' => $file_url,
			'id'  => $attach_id,
		);
	}

	/**
	 * Reset global $_POST and $_REQUEST data.
	 *
	 * @since 3.6.0
	 *
	 * @param string $key the key to look for the data.
	 *
	 * @return void
	 */
	public static function reset_post_data( string $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			unset( $_POST[ $key ] );
		}

		if ( isset( $_REQUEST[ $key ] ) ) {
			unset( $_REQUEST[ $key ] );
		}
	}
}
