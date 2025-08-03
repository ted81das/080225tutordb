<?php
/**
 * Handle database queries for workflows.
 *
 * @package flowmattic
 * @since 1.0
 */

if ( ! class_exists( 'FlowMattic_Database_Workflows' ) ) {
	/**
	 * Handle database queries for workflows.
	 *
	 * @since 1.0
	 */
	class FlowMattic_Database_Workflows {

		/**
		 * The table name.
		 *
		 * @access protected
		 * @since 1.0
		 * @var string
		 */
		protected $table_name = 'flowmattic_workflows';

		/**
		 * The Constructor.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct() {
		}

		/**
		 * Insert workflow to database.
		 *
		 * @since 1.0
		 * @access public
		 * @param array $args The arguments.
		 * @param boolean $is_clone If the workflow is cloned.
		 * @return integer|boolean The last insert id or false if query failed.
		 */
		public function insert( $args, $is_clone = '' ) {
			global $wpdb;

			if ( $is_clone ) {
				$workflow_id = $is_clone;
				$workflow = $this->get_raw( $workflow_id );

				// Update the workflow steps.
				$workflow_steps = json_decode( $workflow->workflow_steps );

				$args['workflow_steps'] = $workflow_steps;
			}

			// Check if workflow ID is provided, else skip.
			if ( isset( $args['workflow_id'] ) ) {
				$exists = $this->get( $args, true );

				// If not exists, add new record, else update.
				if ( ! $exists ) {
					return $wpdb->insert(
						$wpdb->prefix . $this->table_name,
						array(
							'workflow_id'       => esc_attr( $args['workflow_id'] ),
							'workflow_name'     => esc_attr( $args['workflow_name'] ),
							'workflow_steps'    => wp_json_encode( $args['workflow_steps'] ),
							'workflow_settings' => wp_json_encode( $args['workflow_settings'] ),
						)
					);
				} else {
					return $this->update( $args );
				}
			}
		}

		/**
		 * Update the workflow in database.
		 *
		 * @since 1.0
		 * @access public
		 * @param array $args The arguments.
		 * @param integer $workflow_id The workflow ID.
		 * @return void
		 */
		public function update( $args, $workflow_id = '' ) {
			global $wpdb;

			// If workflow_steps are set, and steps inside are empty, skip.
			if ( isset( $args['workflow_steps'] ) && empty( $args['workflow_steps'] ) ) {
				return;
			}

			// If workflow_steps are set, and steps inside are empty, skip to avoid empty steps.
			if ( isset( $args['workflow_steps'] ) && isset( $args['workflow_steps']['steps'] ) && empty( $args['workflow_steps']['steps'] ) ) {
				return;				
			}

			// If workflow_id is not blank, get the steps from raw data and update.
			if ( '' !== $workflow_id ) {
				$workflow = $this->get_raw( $workflow_id );

				// Update the workflow steps.
				$workflow_steps = json_decode( $workflow->workflow_steps );

				$args['workflow_steps'] = $workflow_steps;
			}

			// Check if workflow ID is provided, else skip.
			if ( isset( $args['workflow_id'] ) ) {
				// Delete the transient.
				delete_transient( 'flowmattic_workflow_' . $args['workflow_id'] );

				// Delete the cache.
				$query_id = md5( 'flowmattic_workflow_' . $args['workflow_id'] );
				wp_cache_delete( $query_id, 'flowmattic_workflows' );

				return $wpdb->update(
					$wpdb->prefix . $this->table_name,
					array(
						'workflow_name'     => esc_attr( $args['workflow_name'] ),
						'workflow_steps'    => wp_json_encode( $args['workflow_steps'] ),
						'workflow_settings' => wp_json_encode( $args['workflow_settings'] ),
					),
					array(
						'workflow_id' => esc_attr( $args['workflow_id'] ),
					)
				);
			}
		}

		/**
		 * Update the workflow settings.
		 *
		 * @since 4.1.0
		 * @access public
		 * @param integer $workflow_id The workflow ID.
		 * @param array   $args        The arguments.
		 * @return integer|boolean The last insert id or false if query failed.
		 */
		public function update_settings( $workflow_id, $args ) {
			global $wpdb;

			// Check if workflow ID is provided, else skip.
			if ( isset( $workflow_id ) && '' !== $workflow_id ) {
				// Delete the transient.
				delete_transient( 'flowmattic_workflow_' . $workflow_id );

				// Delete the cache.
				$query_id = md5( 'flowmattic_workflow_' . $workflow_id );
				wp_cache_delete( $query_id, 'flowmattic_workflows' );

				return $wpdb->update(
					$wpdb->prefix . $this->table_name,
					array(
						'workflow_settings' => wp_json_encode( $args['workflow_settings'] ),
					),
					array(
						'workflow_id' => esc_attr( $workflow_id ),
					)
				);
			}
		}

		/**
		 * Delete the workflow from database.
		 *
		 * @since 1.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function delete( $args ) {
			global $wpdb;

			// Check if workflow ID is provided, else skip.
			if ( isset( $args['workflow_id'] ) ) {
				$wpdb->delete(
					$wpdb->prefix . $this->table_name,
					array(
						'workflow_id' => esc_attr( $args['workflow_id'] ),
					)
				);
			}

			// Delete the cache.
			$workflow_id = $args['workflow_id'];
			$query_id = md5( 'flowmattic_workflow_' . $workflow_id );
			wp_cache_delete( $query_id, 'flowmattic_workflows' );

			// Delete the transient.
			delete_transient( 'flowmattic_workflow_' . $args['workflow_id'] );
		}

		/**
		 * Get the workflow from database.
		 *
		 * @since 1.0
		 * @access public
		 * @param array   $args        The arguments.
		 * @param boolean $force_check If to force check the database.
		 * @param boolean $raw_export If to export raw data.
		 * @return array Workflow data from database.
		 */
		public function get( $args, $force_check = false, $raw_export = false ) {
			global $wpdb;

			// Check if the workflow is there in transient.
			// $workflow = get_transient( 'flowmattic_workflow_' . $args['workflow_id'] );

			$workflow_id = $args['workflow_id'];
			$query_id = md5( 'flowmattic_workflow_' . $workflow_id );
			$workflow = wp_cache_get( $query_id, 'flowmattic_workflows' );

			// If not, get it from database.
			if ( false === $workflow || $force_check ) {
				$workflow = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_id` = %s', $args['workflow_id'] )
				);

				$workflow = ( ! empty( $workflow ) && isset( $workflow[0] ) ) ? $workflow[0] : array();

				// Export raw data.
				if ( $raw_export ) {
					return $workflow;
				}

				// Update the workflow steps.
				$workflow = $this->update_workflow_steps( $workflow );

				wp_cache_set( $query_id, $workflow, 'flowmattic_workflows' );

				// Set the transient.
				// set_transient( 'flowmattic_workflow_' . $args['workflow_id'], $workflow, 60 * 60 );
			}

			// // Check if workflow ID is provided, else skip.
			// if ( isset( $args['workflow_id'] ) ) {
			// 	$workflow = $wpdb->get_results(
			// 		$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_id` = %s', $args['workflow_id'] )
			// 	);
			// }

			return $workflow;
		}

		/**
		 * Get the raw workflow from database.
		 *
		 * @since 5.0
		 * @access public
		 * @param string $workflow_id The workflow ID.
		 * @return array Workflow data from database.
		 */
		public function get_raw( $workflow_id ) {
			global $wpdb;

			// Check if workflow ID is provided, else skip.
			if ( isset( $workflow_id ) ) {
				$workflow = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_id` = %s', $workflow_id )
				);
			}

			$workflow = ( ! empty( $workflow ) && isset( $workflow[0] ) ) ? $workflow[0] : array();

			return $workflow;
		}

		/**
		 * Get the workflow from database.
		 *
		 * @since 1.0
		 * @access public
		 * @param array $application_name The application name.
		 * @return array Workflow data from database.
		 */
		public function get_workflow_by_trigger_application( $application_name ) {
			global $wpdb;

			// Check if trigger application is provided, else skip.
			if ( '' !== $application_name ) {
				$workflows = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_steps` LIKE %s', '%"application":"' . $application_name . '"%' )
				);
			}

			if ( ! empty( $workflows ) ) {
				foreach ( $workflows as $key => $workflow ) {
					$workflow = $this->update_workflow_steps( $workflow );
					$workflow_steps = json_decode( $workflow->workflow_steps );

					// Loop through each workflow to check the trigger step application.
					if ( ! empty( $workflow_steps ) ) {
						foreach ( $workflow_steps as $step ) {
							if ( 'trigger' !== $step->type ) {
								continue;
							}

							// Remove the workflow if the trigger application is not the same as the provided application.
							if ( $application_name !== $step->application ) {
								unset( $workflows[ $key ] );
							}
						}
					}
				}
			}

			return $workflows;
		}

		/**
		 * Get all workflows from database.
		 *
		 * @since 1.0
		 * @access public
		 * @return object Workflow data from database.
		 */
		public function get_all() {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 )
			);
		}

		/**
		 * Get all workflows with pagination, order by id desc.
		 *
		 * @since 5.2.0
		 * @access public
		 * @param integer $offset Offset for pagination.
		 * @param integer $limit  Limit for pagination.
		 * @return object Workflow data from database.
		 */
		public function get_all_with_pagination( $offset = 0, $limit = 20 ) {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s ORDER BY `id` DESC LIMIT %d OFFSET %d', 1, $limit, $offset )
			);
		}

		/**
		 * Get all workflows count from database.
		 *
		 * @since 5.2.0
		 * @access public
		 * @return int Workflow count from database.
		 */
		public function get_all_count() {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare( 'SELECT COUNT(*) FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 )
			);
		}

		/**
		 * Get all workflows from database for the specified user.
		 *
		 * @since 1.3.0
		 * @access public
		 * @return object Workflow data from database.
		 */
		public function get_user_workflows( $user_email ) {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_settings` LIKE %s', '%"user_email":"' . $user_email . '"%' )
			);
		}

		/**
		 * Update workflow steps with the new mapping.
		 *
		 * @since 5.0
		 * @access public
		 * @return object Workflow data from database.
		 */
		public function update_workflow_steps( $workflow ) {
			if ( empty( $workflow ) ) {
				return $workflow;
			}

			$workflow_steps = json_decode( $workflow->workflow_steps );

			// For visual workflow builder.
			if ( isset( $workflow_steps->steps ) ) {
				$workflow_steps = $workflow_steps->steps;

				$updated_steps = array();

				foreach ( $workflow_steps as $key => $step ) {
					// Remove the step if type is placeholder.
					if ( 'placeholder' === $step->type ) {
						unset( $workflow_steps[ $key ] );
					}

					// Merge settings with steps.
					if ( isset( $step->settings ) ) {
						$step = array_replace_recursive( (array) $step, (array) $step->settings );
					}

					$updated_steps[ $key ] = $step;
				}

				$workflow_steps = $updated_steps;

				// Update the workflow steps.
				$workflow->workflow_steps = wp_json_encode( $workflow_steps );
			}

			return $workflow;
		}
	}
}
