<?php
/**
 * Handle database queries for apps.
 *
 * @package flowmattic
 * @since 3.0
 */

if ( ! class_exists( 'FlowMattic_Database_Custom_Apps' ) ) {
	/**
	 * Handle database queries for apps.
	 *
	 * @since 3.0
	 */
	class FlowMattic_Database_Custom_Apps {

		/**
		 * The table name.
		 *
		 * @access protected
		 * @since 3.0
		 * @var string
		 */
		protected $table_name = 'flowmattic_custom_apps';

		/**
		 * The Constructor.
		 *
		 * @since 3.0
		 * @access public
		 */
		public function __construct() {
		}

		/**
		 * Insert app to database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return integer|boolean The last insert id or false if query failed.
		 */
		public function insert( $args ) {
			global $wpdb;

			if ( isset( $args['app_id'] ) ) {
				$status = $wpdb->insert(
					$wpdb->prefix . $this->table_name,
					array(
						'app_id'       => $args['app_id'],
						'app_name'     => $args['app_name'],
						'app_actions'  => $args['app_actions'],
						'app_triggers' => $args['app_triggers'],
						'app_settings' => $args['app_settings'],
						'app_time'     => date_i18n( 'Y-m-d H:i:s' ),
					)
				);

				// Delete the transient.
				delete_transient( 'flowmattic_custom_apps' );

				return $wpdb->insert_id;
			} else {
				return wp_json_encode(
					array(
						'status' => 'error',
						'message' => esc_html__( 'Invalid App ID', 'flowmattic' ),
					)
				);
			}
		}

		/**
		 * Update the app in database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function update( $args ) {
			global $wpdb;

			// Check if app ID is provided, else skip.
			if ( isset( $args['app_id'] ) ) {
				$app_id = $args['app_id'];

				$data_args = array();

				if ( isset( $args['app_name'] ) && '' !== $args['app_name'] ) {
					$data_args['app_name'] = $args['app_name'];
				}

				if ( isset( $args['app_triggers'] ) && '' !== $args['app_triggers'] ) {
					$data_args['app_triggers'] = $args['app_triggers'];
				}

				if ( isset( $args['app_actions'] ) && '' !== $args['app_actions'] ) {
					$data_args['app_actions'] = $args['app_actions'];
				}

				if ( isset( $args['app_settings'] ) && '' !== $args['app_settings'] ) {
					$data_args['app_settings'] = $args['app_settings'];
				}

				// Update the entry to database.
				$wpdb->update(
					$wpdb->prefix . $this->table_name,
					$data_args,
					array(
						'app_id' => esc_attr( $app_id ),
					)
				);

				// Delete the transient.
				delete_transient( 'flowmattic_custom_apps' );
			}
		}

		/**
		 * Delete the app from database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function delete( $args ) {
			global $wpdb;

			// Check if app ID is provided, else skip.
			if ( isset( $args['app_id'] ) ) {
				$wpdb->delete(
					$wpdb->prefix . $this->table_name,
					array(
						'app_id' => esc_attr( $args['app_id'] ),
					)
				);

				// Delete the transient.
				delete_transient( 'flowmattic_custom_apps' );
			}
		}

		/**
		 * Get the app from database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return array app data from database.
		 */
		public function get( $args ) {
			global $wpdb;

			// Check if app ID is provided, else skip.
			if ( isset( $args['app_id'] ) ) {
				$app = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `app_id` = %s', $args['app_id'] )
				);
			} else {
				return wp_json_encode(
					array(
						'status' => 'error',
						'message' => esc_html__( 'Invalid App ID', 'flowmattic' ),
					)
				);
			}

			$app = ( ! empty( $app ) && isset( $app[0] ) ) ? $app[0] : false;

			return $app;
		}

		/**
		 * Get all apps from database.
		 *
		 * @since 3.0
		 * @access public
		 * @return object app data from database.
		 */
		public function get_all() {
			global $wpdb;

			// Get all apps from transients.
			$apps = get_transient( 'flowmattic_custom_apps' );

			if ( false === $apps ) {
				$apps = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 )
				);

				// Set the transient.
				set_transient( 'flowmattic_custom_apps', $apps, 60 * 60 );
			}

			return $apps;
		}

		/**
		 * Get all apps from database for the specified user.
		 *
		 * @since 3.0
		 * @access public
		 * @return object app data from database.
		 */
		public function get_user_apps( $user_email ) {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `app_settings` LIKE %s', '%"user_email":"' . $user_email . '"%' )
			);
		}
	}
}
