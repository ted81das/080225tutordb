<?php
/**
 * TutorPro Exporter
 *
 * @package TutorPro\Tools
 * @author  Themeum<support@themeum.com>
 * @link    https://themeum.com
 * @since   3.6.0
 */

namespace TutorPro\Tools;

use AllowDynamicProperties;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Options_V2;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handling export functionality.
 *
 * @since 3.6.0
 */
#[AllowDynamicProperties]
class AjaxHandler {

	use JsonResponse;

	/**
	 * Directory to keep the export file
	 *
	 * @since 3.6.0
	 *
	 * @var string file path
	 */
	private $upload_dir;

	/**
	 * Register hooks
	 *
	 * @since 3.6.0
	 *
	 * @param Exporter    $exporter Export object.
	 * @param Importer    $importer Import object.
	 * @param CourseModel $course_model Course model object.
	 */
	public function __construct( Exporter $exporter, Importer $importer, CourseModel $course_model ) {
		$this->exporter     = $exporter;
		$this->course_model = $course_model;
		$this->importer     = $importer;

		add_action( 'wp_ajax_tutor_pro_exportable_contents', array( $this, 'ajax_get_exportable_contents' ) );
		add_action( 'wp_ajax_tutor_pro_export', array( $this, 'ajax_export_handler' ) );
		add_action( 'wp_ajax_tutor_pro_export_import_history', array( $this, 'ajax_fetch_history' ) );
		add_action( 'wp_ajax_tutor_pro_import', array( $this, 'ajax_import_handler' ) );
		add_action( 'wp_ajax_tutor_pro_delete_export_import_history', array( $this, 'ajax_delete_export_import_history' ) );

		add_action(
			'init',
			function () {
				$this->upload_dir = wp_upload_dir()['basedir'] . '/tutor-pro/';
			}
		);
		add_action( 'tutor_pro_export_completed', array( $this, 'update_settings_log' ), 10, 2 );
	}

	/**
	 * Ajax handler for exportable contents API
	 *
	 * @since 3.6.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_get_exportable_contents() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$contents = $this->get_exportable_content_with_count();

		$this->json_response( __( 'Exportable contents fetched successfully!', 'tutor-pro' ), $contents, HttpHelper::STATUS_OK );
	}

	/**
	 * Get exportable contents with count
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public function get_exportable_content_with_count() {
		$args = array(
			'post_type'              => tutor()->course_post_type,
			'posts_per_page'         => -1,
			'no_found_rows'          => true,     // Skip pagination counting.
			'update_post_term_cache' => false,   // Skip taxonomy term caching.
			'update_post_meta_cache' => false,   // Skip post meta caching.
			'fields'                 => 'ids',
			'post_status'            => 'any',
		);

		$contents = $this->exporter->get_exportable_content();

		foreach ( $contents as $key => $content ) {
			switch ( $content['key'] ) {
				case tutor()->course_post_type:
					$query = $this->course_model::get_courses_by_args( $args );

					$post_count = is_a( $query, 'WP_Query' ) ? $query->post_count : 0;
					$ids        = is_a( $query, 'WP_Query' ) ? $query->posts : array();

					$label = $content['label'];

					$contents[ $key ]['label'] = $label;
					$contents[ $key ]['ids']   = $ids;
					$contents[ $key ]['count'] = $post_count;

					$sub_contents = isset( $content['contents'] ) ? $content['contents'] : array();
					foreach ( $sub_contents as $k => $sub_content ) {
						$sub_content_count    = $this->course_model::count_course_content( $sub_content['key'] );
						$sub_content['count'] = $sub_content_count;

						$sub_contents[ $k ] = $sub_content;
					}

					$contents[ $key ]['contents'] = $sub_contents;
					break;
				case tutor()->bundle_post_type:
					$args['post_type'] = tutor()->bundle_post_type;

					$query = $this->course_model::get_courses_by_args( $args );

					$post_count = is_a( $query, 'WP_Query' ) ? $query->post_count : 0;
					$ids        = is_a( $query, 'WP_Query' ) ? $query->posts : array();

					$label = $content['label'];

					$contents[ $key ]['label'] = $label;
					$contents[ $key ]['ids']   = $ids;
					$contents[ $key ]['count'] = $post_count;
					break;

				default:
					// code...
					break;
			}
		}

		return $contents;
	}

	/**
	 * Handle export
	 *
	 * @since 3.6.0
	 *
	 * @return void wp_json response
	 */
	public function ajax_export_handler() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$job_id           = Input::post( 'job_id', 0, Input::TYPE_INT );
		$export_contents  = $_POST['export_contents'] ?? array();
		$keep_media_files = Input::post( 'keep_media_files', 1, Input::TYPE_INT );

		$contents = array();
		foreach ( $export_contents as $key => $value ) {
			$contents[] = json_decode( stripslashes( $value ), true );
		}

		if ( $job_id ) {
			$existing_job = $this->get_export_job( $job_id );
			$contents     = $existing_job['job_requirements'] ?? array();
		}

		if ( ! $job_id && ! $contents ) {
			$this->response_bad_request( __( 'Invalid request!', 'tutor-pro' ) );
		}

		$contents = array_map(
			function ( $content ) {
				$type = Input::sanitize( $content['type'] );
				$ids  = array_map( 'intval', $content['ids'] ?? array() );

				$content['ids']  = $ids;
				$content['type'] = $type;

				return $content;
			},
			$contents
		);

		$exporter = $this->exporter;
		if ( $keep_media_files ) {
			$exporter->add_media_files();
		}

		// Get or create job data.
		$job_data = $this->get_export_job( $job_id ) ?? $this->get_default_job_data( $job_id, $contents );

		$this->job_id = $job_data['job_id'];

		// Process contents in batches.
		foreach ( $contents as $content ) {
			$type = $content['type'] ?? false;
			$ids  = $content['ids'] ?? array();

			// Skip already processed items.
			if ( $exporter::TYPE_SETTINGS === $type && true === $job_data['completed_contents']['settings'] ) {
				continue;
			}

			// Process one item at a time.
			if ( tutor()->course_post_type === $type && ! empty( $ids ) ) {
				$completed_ids = array_merge( $job_data['completed_contents'][ tutor()->course_post_type ]['success'], $job_data['completed_contents'][ tutor()->course_post_type ]['failed'] );

				$remaining_ids = array_diff( $ids, $completed_ids );
				if ( ! empty( $remaining_ids ) ) {
					$is_failed     = false;
					$id_to_process = array_shift( $remaining_ids );
					$sub_contents  = $content['sub_contents'] ?? array();

					$export_data = null;
					try {
						$export_data = $exporter->add_courses( $id_to_process, $sub_contents )->export();
					} catch ( \Throwable $th ) {
						$is_failed = true;
					} finally {
						if ( $is_failed ) {
							$job_data['completed_contents'][ tutor()->course_post_type ]['failed'][] = $id_to_process;
						} else {
							$job_data['completed_contents'][ tutor()->course_post_type ]['success'][] = $id_to_process;
						}
					}

					if ( is_null( $export_data ) ) {
						$export_data = $this->exporter->get_schema();
					}

					try {
						$this->update_export_job( $job_data, tutor()->course_post_type, $export_data );
					} catch ( \Throwable $th ) {
						$this->send_export_response( __( 'Course export failed', 'tutor-pro' ), HttpHelper::STATUS_BAD_REQUEST );
					}

					$this->send_export_response( __( 'Export in progress', 'tutor-pro' ) );
				}
			} elseif ( tutor()->bundle_post_type === $type && ! empty( $ids ) ) {
				$completed_ids = array_merge( $job_data['completed_contents'][ tutor()->bundle_post_type ]['success'], $job_data['completed_contents'][ tutor()->bundle_post_type ]['failed'] );

				$remaining_ids = array_diff( $ids, $completed_ids );
				if ( ! empty( $remaining_ids ) ) {
					$is_failed     = false;
					$id_to_process = array_shift( $remaining_ids );

					$export_data = null;
					try {
						$export_data = $exporter->add_bundles( $id_to_process )->export();
					} catch ( \Throwable $th ) {
						$is_failed = true;
					} finally {
						if ( $is_failed ) {
							$job_data['completed_contents'][ tutor()->bundle_post_type ]['failed'][] = $id_to_process;
						} else {
							$job_data['completed_contents'][ tutor()->bundle_post_type ]['success'][] = $id_to_process;
						}
					}

					if ( is_null( $export_data ) ) {
						$export_data = $this->exporter->get_schema();
					}

					try {
						$this->update_export_job( $job_data, tutor()->bundle_post_type, $export_data );
					} catch ( \Throwable $th ) {
						$this->send_export_response( __( 'Bundle export failed', 'tutor-pro' ), HttpHelper::STATUS_BAD_REQUEST );
					}

					$this->send_export_response( __( 'Export in progress', 'tutor-pro' ) );
				}
			} elseif ( $type === $exporter::TYPE_SETTINGS ) {
				$export_data = $exporter->add_settings()->export();

				$job_data['completed_contents']['settings'] = true;
				try {
					$this->update_export_job( $job_data, $exporter::TYPE_SETTINGS, $export_data );
				} catch ( \Throwable $th ) {
					$this->send_export_response( __( 'Settings export failed', 'tutor-pro' ), HttpHelper::STATUS_BAD_REQUEST );
				}

				$this->send_export_response( __( 'Export in progress', 'tutor-pro' ) );
			}
		}

		// If we get here, all items are processed.
		$progress = (int) $this->calculate_progress( $job_data );
		if ( 100 === $progress ) {
			$job_data['job_status'] = 'completed';
		}

		$this->send_export_response( 100 === $progress ? __( 'Export completed', 'tutor-pro' ) : __( 'Export in progress', 'tutor-pro' ) );
	}

	/**
	 * Handle ajax import.
	 *
	 * @since 3.6.0
	 *
	 * @return void wp_json response
	 */
	public function ajax_import_handler() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$job_id   = Input::post( 'job_id', 0 );
		$job_data = $this->get_import_job( $job_id );
		$contents = '';

		$file = $_FILES['data'] ?? '';

		if ( ! $file && ! $job_data ) {
			$this->response_bad_request( __( 'Invalid or empty file provided', 'tutor-pro' ) );
		}

		if ( $file ) {
			if ( $file['error'] !== UPLOAD_ERR_OK ) {
				if ( WP_DEBUG_LOG ) {
					error_log( $this->importer::TUTOR_IMPORTER_ERROR_LOG . ':' . 'File upload error code, ' . $file['error'] );
				}
				$this->response_bad_request( __( 'File upload error', 'tutor-pro' ) );
			}

			try {
				$contents = file_get_contents( $file['tmp_name'] );

				if ( ! $contents ) {
					$this->response_bad_request( __( 'Error reading from file', 'tutor-pro' ) );
				}
			} catch ( \Throwable $th ) {
				$this->response_bad_request( $th->getMessage() );
			} finally {
				unlink( $file['tmp_name'] );
			}
		}

		$contents = json_decode( $contents, true );

		$importer = $this->importer;

		$this->job_id = $job_data['job_id'] ?? 0;

		if ( $contents ) {
			$keep_media_files = false;

			$job_requirements = array();

			if ( isset( $contents['keep_media_files'] ) ) {
				$keep_media_files = $contents['keep_media_files'];
			}

			$rules = array(
				'schema_version' => 'required',
				'data'           => 'required|is_array',
			);

			$validate_json = ValidationHelper::validate( $rules, $contents );

			if ( ! $validate_json->success ) {
				$this->json_response( __( 'Invalid json', 'tutor-pro' ), $validate_json->errors, HttpHelper::STATUS_BAD_REQUEST );
			}

			$data = $contents['data'];

			$importable_contents = array();

			foreach ( $this->exporter->get_exportable_content() as $value ) {
				$importable_contents[] = $value['key'];
			}

			foreach ( $data as $content ) {
				$requirement    = array();
				$bundle_courses = array();
				$rules          = array(
					'content_type' => 'required|match_string:' . implode( ',', $importable_contents ),
					'data'         => 'required|is_array',
				);

				$validate_content = ValidationHelper::validate( $rules, $content );

				if ( ! $validate_content->success ) {
					$this->json_response( __( 'Invalid data', 'tutor-pro' ), $validate_content->errors, HttpHelper::STATUS_BAD_REQUEST );
				}

				if ( get_tutor_post_types( 'bundle' ) === $content['content_type'] ) {
					$course_ids  = $job_requirements[ get_tutor_post_types( 'course' ) ]['ids'] ?? array();
					$course_data = array(
						'content_type' => get_tutor_post_types( 'course' ),
						'data'         => array(),
					);

					if ( $course_ids ) {
						$course_data = array_shift( $data );
					}
					foreach ( $content['data'] as $bundle ) {
						$bundle_courses = $bundle['courses'];

						if ( ! count( $bundle_courses ) ) {
							continue;
						}

						$bundle_course_ids = array_column( $bundle_courses, 'ID' );

						if ( count( $course_ids ) ) {
							$unique_ids = array_diff( $bundle_course_ids, $course_ids );
							array_push( $job_requirements[ get_tutor_post_types( 'course' ) ]['ids'], ...$unique_ids );

							$course_data['data'] = array_merge( $course_data['data'], $bundle_courses );

						} else {
							if ( $job_requirements[ get_tutor_post_types( 'course' ) ] ) {
								$unique_ids = array_diff( $bundle_course_ids, $job_requirements[ get_tutor_post_types( 'course' ) ]['ids'] );
								array_push( $job_requirements[ get_tutor_post_types( 'course' ) ]['ids'], ...$unique_ids );
							} else {
								$requirement['type']                                  = get_tutor_post_types( 'course' );
								$requirement['ids']                                   = $bundle_course_ids;
								$job_requirements[ get_tutor_post_types( 'course' ) ] = $requirement;
							}

							$course_data['data'] = array_merge( $course_data['data'], $bundle_courses );
						}
					}

					array_unshift( $data, $course_data );
				}

				$requirement['type'] = $content['content_type'];

				if ( $this->exporter::TYPE_SETTINGS !== $content['content_type'] ) {
					$requirement['ids'] = array_column( $content['data'], 'ID' );
				}

				$job_requirements[ $content['content_type'] ] = $requirement;
			}

			$job_data = $this->get_default_job_data( $this->job_id, $job_requirements );

			$this->job_id = $job_data['job_id'];

			$job_data['course_ids_map']   = array();
			$job_data['import_data']      = '';
			$job_data['imported_data']    = array();
			$job_data['keep_media_files'] = $keep_media_files;

			try {
				$this->save_import_json_content( $data );
			} catch ( \Throwable $th ) {
				$this->json_response( __( 'Error saving import file', 'tutor-pro' ), $th->getMessage(), HttpHelper::STATUS_BAD_REQUEST );
			}
		}

		$data = $this->get_json_data( 'import' );

		if ( $data ) {
			foreach ( $data as $content ) {
				switch ( $content['content_type'] ) {
					case get_tutor_post_types( 'course' ):
						$content_type = $content['content_type'];
						if ( $content_type === $job_data['import_data'] ) {
							$completed_contents = array_merge( $job_data['completed_contents'][ $content_type ]['success'], $job_data['completed_contents'][ $content_type ]['failed'] );
							$ids                = array_diff( $job_data['job_requirements'][ $content_type ]['ids'], $completed_contents );

							if ( $ids ) {
								$id = array_shift( $ids );
								foreach ( $content['data'] as $data ) {
									if ( $data['ID'] !== $id ) {
										continue;
									}

									$import_id = $this->importer->import_content( array( $data ), $job_data['keep_media_files'] );

									if ( is_wp_error( $import_id ) ) {
										$job_data['completed_contents'][ $content_type ]['failed'][] = $id;
										if ( WP_DEBUG_LOG ) {
											error_log( $this->importer::TUTOR_IMPORTER_ERROR_LOG . ':' . $import_id->get_error_message() );
										}
									} else {
										$job_data['course_ids_map'][ $id ]                            = $import_id;
										$job_data['completed_contents'][ $content_type ]['success'][] = $id;
									}

									$this->update_import_job( $job_data );

									$this->send_import_response( __( 'Importing Courses', 'tutor-pro' ) );
								}
							}

							$job_data['import_data']     = '';
							$job_data['imported_data'][] = $content_type;
							$this->update_import_job( $job_data );

						} else {
							if ( ! in_array( $content_type, $job_data['imported_data'] ) ) {
								$job_data['import_data'] = $content_type;
								$this->update_import_job( $job_data );
								$this->send_import_response( __( 'Importing Courses', 'tutor-pro' ), HttpHelper::STATUS_OK, get_tutor_post_types( 'course' ) );
							}
						}
						break;

					case get_tutor_post_types( 'bundle' ):
						$content_type = $content['content_type'];
						if ( $content_type === $job_data['import_data'] ) {
							$completed_contents = array_merge( $job_data['completed_contents'][ $content_type ]['success'], $job_data['completed_contents'][ $content_type ]['failed'] );
							$ids                = array_diff( $job_data['job_requirements'][ $content_type ]['ids'], $completed_contents );
							if ( $ids ) {
								$id = array_shift( $ids );
								foreach ( $content['data'] as $data ) {
									if ( $data['ID'] !== $id ) {
										continue;
									}

									$import_data = $this->importer->import_bundle( $data, $job_data['keep_media_files'], $job_data['course_ids_map'] );

									if ( is_wp_error( $import_data ) ) {
										$job_data['completed_contents'][ $content_type ]['failed'][] = $id;
										if ( WP_DEBUG_LOG ) {
											error_log( $this->importer::TUTOR_IMPORTER_ERROR_LOG . ':' . $import_data->get_error_message() );
										}
									} else {
										$job_data['completed_contents'][ $content_type ]['success'][] = $id;
									}

									$this->update_import_job( $job_data );

									$this->send_import_response( __( 'Importing Bundles', 'tutor-pro' ) );
								}
							}
							$job_data['import_data']     = '';
							$job_data['imported_data'][] = $content_type;
							$this->update_import_job( $job_data );

						} else {
							if ( ! in_array( $content_type, $job_data['imported_data'] ) ) {
								$job_data['import_data'] = $content_type;
								$this->update_import_job( $job_data );
								$this->send_import_response( __( 'Importing Bundles', 'tutor-pro' ), HttpHelper::STATUS_OK, get_tutor_post_types( 'bundle' ) );
							}
						}
						break;

					case $this->exporter::TYPE_SETTINGS:
						$content_type = $content['content_type'];
						if ( $content_type === $job_data['import_data'] ) {
							$response = $importer->import_settings( $content['data'] );

							if ( is_wp_error( $response ) ) {
								$this->response_bad_request( $response->get_error_message() );
							}

							$job_data['import_data']                         = '';
							$job_data['imported_data'][]                     = $content_type;
							$job_data['completed_contents'][ $content_type ] = $response;
							$this->update_import_job( $job_data );
							$this->send_import_response( __( 'Importing Settings', 'tutor-pro' ) );

						} else {
							if ( ! in_array( $content_type, $job_data['imported_data'] ) ) {
								$job_data['import_data'] = $content_type;
								$this->update_import_job( $job_data );
								$this->send_import_response( __( 'Importing Settings', 'tutor-pro' ), HttpHelper::STATUS_OK, $this->exporter::TYPE_SETTINGS );
							}
						}
						break;

					default:
						break;
				}
			}

			$progress = (int) $this->calculate_progress( $job_data );

			if ( 100 === $progress ) {
				$this->send_import_response( __( 'Import completed', 'tutor-pro' ) );
			}
		} else {
			$this->response_bad_request( __( 'Invalid or empty data provided', 'tutor-pro' ) );
		}
	}

	/**
	 * Save import content in json file.
	 *
	 * @param array $content the content to store.
	 *
	 * @throws \Exception if error writing import content to file.
	 *
	 * @return int|bool
	 */
	private function save_import_json_content( $content ) {
		$json_file = $this->get_json_file( 'import' );

		$data = file_put_contents( $json_file, wp_json_encode( $content, JSON_PRETTY_PRINT ) );

		if ( ! $data ) {
			throw new \Exception( __( 'Error writing to file', 'tutor-pro' ) );
		}

		return $data;
	}


	/**
	 * Get job data
	 *
	 * @since 3.6.0
	 *
	 * @param mixed $job_id Job id to get job data.
	 *
	 * @return array
	 */
	private function get_export_job( $job_id ) {
		return get_option( $this->exporter::OPT_NAME . $job_id, null );
	}

	/**
	 * Get import job data
	 *
	 * @since 3.6.0
	 *
	 * @param mixed $job_id Job id to get job data.
	 *
	 * @return array
	 */
	private function get_import_job( $job_id ) {
		return get_option( $this->importer::OPT_NAME . $job_id, null );
	}

	/**
	 * Update export job data
	 *
	 * @since 3.6.0
	 *
	 * @throws \Exception If invalid export data is provided.
	 * @throws \Throwable If failed to update the json file.
	 *
	 * @param array  $job_data Job data.
	 * @param string $job_type New done job type.
	 * @param mixed  $new_export_data Exported data to merge with the job data.
	 *
	 * @return void
	 */
	private function update_export_job( array $job_data, string $job_type, $new_export_data = null ) {
		if ( $new_export_data ) {
			$exported_data    = $job_data['exported_data'] ?? null;
			$new_content_type = $new_export_data['data'][0]['content_type'] ?? $job_type;
			$new_data         = $new_export_data['data'][0]['data'] ?? array();

			// if ( ! $new_content_type || ! $new_data ) {
			// 	throw new \Exception( __( 'Invalid export data', 'tutor-pro' ) );
			// }

			if ( empty( $exported_data ) ) {
				$exported_data = $new_export_data;
			} else {
				$exported_data = $this->get_json_data();
				foreach ( $exported_data['data'] as $k => $data ) {
					if ( $new_content_type === $data['content_type'] ) {
						array_push( $exported_data['data'][ $k ]['data'], ...$new_data );
						break;
					}
				}

				$is_new_type_exists = in_array( $new_content_type, array_column( $exported_data['data'], 'content_type' ) );
				if ( ! $is_new_type_exists ) {
					array_push( $exported_data['data'], $new_export_data['data'][0] );
				}
			}

			try {
				$this->update_json_file_data( $exported_data );
			} catch ( \Throwable $th ) {
				throw $th;
			}
		}

		$job_data['exported_data'] = $this->get_json_file();
		$job_data['job_progress']  = $this->calculate_progress( $job_data );

		update_option( $this->exporter::OPT_NAME . $this->job_id, $job_data, false );
	}

	/**
	 * Update import job data
	 *
	 * @since 3.6.0
	 *
	 * @param array $job_data the job data to update.
	 *
	 * @return void
	 */
	private function update_import_job( array $job_data ) {
		update_option( $this->importer::OPT_NAME . $this->job_id, $job_data, false );
	}

	/**
	 * Prepare and send job response
	 *
	 * @since 3.6.0
	 *
	 * @param string $message Response message.
	 * @param int    $status_code Status code.
	 *
	 * @return void
	 */
	private function send_export_response( string $message, $status_code = HttpHelper::STATUS_OK ) {
		$job_data = $this->get_export_job( $this->job_id );
		$progress = (int) $this->calculate_progress( $job_data );

		$response_to_client = null;
		if ( 100 === $progress ) {
			$job_data['job_progress'] = $progress;
			$job_data['job_status']   = 'completed';

			// Send response to the client.
			$job_data['exported_data'] = $this->get_json_data();

			do_action( 'tutor_pro_export_completed', $this->job_id, $job_data['exported_data'] );

			$response_to_client        = $job_data;
			$job_data['exported_data'] = null;

			
			// Unlink the json file.
			if ( file_exists( $this->get_json_file() ) ) {
				unlink( $this->get_json_file() );
			}
		} else {
			$job_data['job_progress'] = $progress;
		}

		update_option( $this->exporter::OPT_NAME . $this->job_id, $job_data, false );

		$this->json_response( $message, $response_to_client ? $response_to_client : $job_data, $status_code );
	}

	/**
	 * Prepare and send job response
	 *
	 * @since 3.6.0
	 *
	 * @param string $message Response message.
	 * @param int    $status_code Status code.
	 *
	 * @return void
	 */
	private function send_import_response( string $message, $status_code = HttpHelper::STATUS_OK ) {
		$job_data = $this->get_import_job( $this->job_id );
		$progress = (int) $this->calculate_progress( $job_data );

		$response_to_client = null;
		if ( 100 === $progress ) {
			$job_data['job_progress'] = $progress;
			$job_data['job_status']   = 'completed';

			// Unlink the json file.
			if ( file_exists( $this->get_json_file( 'import' ) ) ) {
				unlink( $this->get_json_file( 'import' ) );
			}
		} else {
			$job_data['job_progress'] = $progress;
		}

		$this->update_import_job( $job_data );

		$this->json_response( $message, $response_to_client ? $response_to_client : $job_data, $status_code );
	}

	/**
	 * Calculate the job progress based on the data
	 *
	 * @since 3.6.0
	 *
	 * @param array $job_data Job data.
	 *
	 * @return number Job progress
	 */
	private function calculate_progress( array $job_data ) {
		$total     = 0;
		$completed = 0;

		foreach ( $job_data['job_requirements'] as $content ) {
			if ( $this->exporter::TYPE_SETTINGS === $content['type'] ) {
				++$total;
				if ( $job_data['completed_contents']['settings'] ) {
					++$completed;
				}
			} else {
				$total += count( $content['ids'] );

				if ( is_array( $job_data['completed_contents'][ $content['type'] ] ) ) {
					$completed += count( $job_data['completed_contents'][ $content['type'] ]['success'] );
					$completed += count( $job_data['completed_contents'][ $content['type'] ]['failed'] );
				}
			}
		}

		return $total > 0 ? round( ( $completed / $total ) * 100 ) : 100;
	}

	/**
	 * Get the JSON file
	 *
	 * @since 3.6.0
	 *
	 * @param string $type whether import or export.
	 *
	 * @return string JSON file path
	 */
	private function get_json_file( string $type = 'export' ) {
		$export_file = $this->upload_dir . "{$type}-" . $this->job_id . '.json';

		// Ensure directory exists with proper permissions.
		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );

			// Set directory permissions (755 for security).
			if ( file_exists( $this->upload_dir ) ) {
				chmod( $this->upload_dir, 0755 );
			}
		}

		return $export_file;
	}

	/**
	 * Get the JSON file
	 *
	 * @since 3.6.0
	 *
	 * @param string $type whether import or export.
	 *
	 * @return array JSON file path
	 */
	private function get_json_data( string $type = 'export' ) {
		$export_file = $this->get_json_file( $type );
		$data        = file_get_contents( $export_file );

		return json_decode( $data, true );
	}

	/**
	 * Update json file update
	 *
	 * @since 3.6.0
	 *
	 * @throws \Exception If failed to write the JSON file.
	 *
	 * @param array $data Exported data.
	 */
	private function update_json_file_data( $data ) {
		$data = $this->deep_maybe_unserialize( $data );

		$store = file_put_contents(
			$this->get_json_file(),
			wp_json_encode( $data, JSON_PRETTY_PRINT )
		);

		if ( false === $store ) {
			throw new \Exception( 'Failed to write the JSON file', 'tutor-pro' );
		}

		return $store;
	}

	/**
	 * Recursively unserialize all serialized values in an array
	 *
	 * @since 3.6.0
	 *
	 * @param mixed $data The input data (array or string).
	 *
	 * @return mixed The processed data with unserialized values
	 */
	public function deep_maybe_unserialize( $data ) {
		// Handle arrays recursively.
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->deep_maybe_unserialize( $value );
			}
			return $data;
		}

		// Handle objects recursively (convert to array first).
		if ( is_object( $data ) ) {
			$data = (array) $data;
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->deep_maybe_unserialize( $value );
			}
			return (object) $data;
		}

		// Handle strings (check if serialized).
		if ( is_string( $data ) ) {
			// Skip if empty or doesn't look serialized.
			if ( empty( $data ) || ! preg_match( '/^[aOs]:[\d]+:/', $data ) ) {
				return $data;
			}

			$unserialized = maybe_unserialize( $data );
			if ( $data !== $unserialized ) {
				return $this->deep_maybe_unserialize( $unserialized );
			}
		}

		return $data;
	}

	/**
	 * Get default job data
	 *
	 * @since 3.6.0
	 *
	 * @param mixed  $job_id Job id.
	 * @param string $job_requirements Job requirements.
	 *
	 * @return array
	 */
	private function get_default_job_data( $job_id, $job_requirements ) {
		$user_id = get_current_user_id();

		$data = array(
			'created_at'         => gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), tutor_time() ),
			'user_name'          => tutor_utils()->display_name( $user_id ),
			'job_id'             => $job_id ? $job_id : wp_rand(),
			'job_progress'       => '0',
			'job_status'         => 'in-progress',
			'job_requirements'   => $job_requirements,
			'exported_data'      => array(),
			'completed_contents' => array(
				tutor()->course_post_type => array(
					'success' => array(),
					'failed'  => array(),
				),
				tutor()->bundle_post_type => array(
					'success' => array(),
					'failed'  => array(),
				),
				'settings'                => false,
			),
		);

		return apply_filters( 'tutor_pro_export_job_data', $data );
	}

	/**
	 * Fetch export/import history
	 *
	 * @since 3.6.0
	 *
	 * @return void send wp_json_response
	 */
	public function ajax_fetch_history() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$data = $this->get_export_import_history();

		$this->json_response( __( 'History fetched successfully!', 'tutor-pro' ), $data );
	}

	/**
	 * Get export/import history
	 *
	 * Unserialize the data before sending response
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public function get_export_import_history(): array {
		global $wpdb;
		$data = array();

		$fetch = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					*
				FROM $wpdb->options
				WHERE option_name LIKE %s
				OR option_name LIKE %s
				ORDER BY option_id DESC
				LIMIT 10
				",
				'tutor_pro_export_%',
				'tutor_pro_import_%',
			)
		);

		if ( $fetch ) {
			foreach ( $fetch as $item ) {
				$data[] = $this->deep_maybe_unserialize( $item );
			}
		}

		return $data;
	}

	/**
	 * Delete export/import history
	 *
	 * @since 3.6.0
	 *
	 * @return void send wp_json_response
	 */
	public function ajax_delete_export_import_history() {
		tutor_utils()->check_nonce();
		tutor_utils()->check_current_user_capability();

		$option_id = Input::post( 'option_id', 0, Input::TYPE_INT );

		if ( ! $option_id ) {
			$this->response_bad_request( __( 'Option ID is required to delete history', 'tutor-pro' ) );
		}

		try {
			$this->delete_export_import_history( $option_id );
		} catch ( \InvalidArgumentException $e ) {
			$this->response_bad_request( $e->getMessage() );
		} catch ( \WP_Error $e ) {
			$this->response_bad_request( $e->get_error_message() );
		}

		$this->json_response( __( 'History deleted successfully!', 'tutor-pro' ) );
	}

	/**
	 * Delete export/import history
	 *
	 * @since 3.6.0
	 *
	 * @param int $option_id Option ID to delete.
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_export_import_history( $option_id = 0 ) {
		global $wpdb;

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options
				WHERE option_id = %d
				AND (option_name LIKE %s OR option_name LIKE %s)",
				$option_id,
				'tutor_pro_export_%',
				'tutor_pro_import_%'
			)
		);

		if ( false === $deleted ) {
			return new \WP_Error( 'db_error', __( 'Database error occurred while deleting history', 'tutor-pro' ) );
		}

		return true;
	}

	/**
	 * Delete export/import history
	 *
	 * @since 3.6.0
	 *
	 * @param mixed $job_id Job ID.
	 * @param array $job_data Exported data.
	 *
	 * @return void
	 */
	public function update_settings_log( $job_id, $job_data ) {
		if ( is_array( $job_data ) && ! empty( $job_data ) ) {
			$exported_data = $job_data['data'];
			if ( ! empty( $exported_data ) ) {
				foreach ( $exported_data as $data ) {
					if ( $this->exporter::TYPE_SETTINGS === $data['content_type'] ) {
						( new Options_V2( false ) )->update_settings_log( $data['data'], 'Exported' );
					}
				}
			} else {
				global $wpdb;
				$option_name = 'tutor_pro_export_' . $job_id;

				$delete_clause = array(
					'option_name' => $option_name,
				);

				QueryHelper::delete( $wpdb->options, $delete_clause );
			}
		}
	}
}
