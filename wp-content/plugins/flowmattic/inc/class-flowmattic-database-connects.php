<?php
/**
 * Handle database queries for connects.
 *
 * @package flowmattic
 * @since 3.0
 */

if ( ! class_exists( 'FlowMattic_Database_Connects' ) ) {
	/**
	 * Handle database queries for connects.
	 *
	 * @since 3.0
	 */
	class FlowMattic_Database_Connects {

		/**
		 * The table name.
		 *
		 * @access protected
		 * @since 3.0
		 * @var string
		 */
		protected $table_name = 'flowmattic_connects';

		/**
		 * The Constructor.
		 *
		 * @since 3.0
		 * @access public
		 */
		public function __construct() {
		}

		/**
		 * Insert connect to database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return integer|boolean The last insert id or false if query failed.
		 */
		public function insert( $args ) {
			global $wpdb;

			// Check if connect ID is not provided, else update.
			if ( ! isset( $args['connect_id'] ) || '' === trim( $args['connect_id'] ) ) {
				$status = $wpdb->insert( // phpcs:ignore
					$wpdb->prefix . $this->table_name,
					array(
						'connect_id'       => flowmattic_random_string( 8 ),
						'connect_name'     => esc_attr( $args['connect_name'] ),
						'connect_data'     => '',
						'connect_settings' => base64_encode( wp_json_encode( $args['connect_settings'] ) ), // phpcs:ignore
						'connect_time'     => date_i18n( 'Y-m-d H:i:s' ),
					)
				);

				// Delete transient.
				delete_transient( 'flowmattic_all_connects' );

				return $wpdb->insert_id;
			} else {
				$exists = $this->get( $args );

				// If not exists, add new record, else update.
				if ( $exists ) {
					return $this->update( $args );
				}
			}
		}

		/**
		 * Update the connect in database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function update( $args ) {
			global $wpdb;

			// Check if connect ID is provided, else skip.
			if ( isset( $args['connect_id'] ) ) {
				$connect_id = (int) $args['connect_id'];

				$data_args = array();

				if ( isset( $args['connect_name'] ) && '' !== $args['connect_name'] ) {
					$data_args['connect_name'] = $args['connect_name'];
				}

				if ( isset( $args['connect_data'] ) && '' !== $args['connect_data'] ) {
					$data_args['connect_data'] = $args['connect_data'];
				}

				if ( isset( $args['connect_settings'] ) && '' !== $args['connect_settings'] ) {
					$data_args['connect_settings'] = $args['connect_settings'];
				}

				// Update the entry to database.
				$wpdb->update( // phpcs:ignore
					$wpdb->prefix . $this->table_name,
					$data_args,
					array(
						'id' => esc_attr( $connect_id ),
					)
				);

				// Delete transient.
				delete_transient( 'flowmattic_all_connects' );
			}
		}

		/**
		 * Delete the connect from database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function delete( $args ) {
			global $wpdb;

			// Check if connect ID is provided, else skip.
			if ( isset( $args['connect_id'] ) ) {
				$wpdb->delete( // phpcs:ignore
					$wpdb->prefix . $this->table_name,
					array(
						'id' => esc_attr( $args['connect_id'] ),
					)
				);

				// Delete transient.
				delete_transient( 'flowmattic_all_connects' );
			}
		}

		/**
		 * Get the connect from database.
		 *
		 * @since 3.0
		 * @access public
		 * @param array $args The arguments.
		 * @return array connect data from database.
		 */
		public function get( $args ) {
			global $wpdb;

			// Check if connect ID is provided, else skip.
			if ( isset( $args['connect_id'] ) ) {
				$connect = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `id` = %d', (int) $args['connect_id'] ) ); // phpcs:ignore
			}

			$connect = ( ! empty( $connect ) && isset( $connect[0] ) ) ? $connect[0] : array();

			if ( ! empty( $connect ) ) {
				$connect->connect_data     = json_decode( base64_decode( $connect->connect_data ), true ); // phpcs:ignore
				$connect->connect_settings = json_decode( base64_decode( $connect->connect_settings ), true ); // phpcs:ignore

				// Check if token type is set, and is Bearer.
				if ( isset( $connect->connect_data['token_type'] ) && 'Bearer' === $connect->connect_data['token_type'] ) {
					// Check if the token expiry is set.
					if ( isset( $connect->connect_data['expires_in'] ) ) {
						// Check if refresh token is available, else, send an email to the admin.
						if ( ! isset( $connect->connect_data['refresh_token'] ) ) {
							// Get the settings.
							$settings = get_option( 'flowmattic_settings', array() );

							// Check if the notification email is already sent.
							$notification_email_sent = get_transient( 'flowmattic_notification_email_sent_' . $connect->id );

							// If notification email is not sent, send an email.
							if ( false === $notification_email_sent ) {
								// If notifications are turned on, and the task is failed, notify the user.
								if ( ( isset( $settings['enable_notifications_connect'] ) && 'yes' === $settings['enable_notifications_connect'] ) ) {
									if ( '' !== $settings['notification_email_connect'] ) {
										$notification_email = $settings['notification_email_connect'];

										$email_body  = '<p>' . esc_html__( 'Hello,', 'flowmattic' ) . '</p>' . PHP_EOL;
										$email_body .= '<p>We found that the authentication or access token for the following Connect seems expired on site <strong>' . wp_parse_url( get_site_url(), PHP_URL_HOST ) . '</strong>.</p>' . PHP_EOL;
										$email_body .= '<ul><li>Connect Name: <strong>' . $connect->connect_name . '</strong></li><li>Connect ID: <strong>' . $connect->id . '</strong></li></ul>' . PHP_EOL;
										$email_body .= '<p>' . esc_html__( 'Connect Page Link', 'flowmattic' ) . " - <a href='" . admin_url( '/admin.php?page=flowmattic-connects' ) . "'>" . admin_url( '/admin.php?page=flowmattic-connects' ) . '</a></p>' . PHP_EOL;
										$email_body .= '<p>' . esc_html__( 'Please check and re-authenticate the connect to avoid failures in the workflows it is used in.', 'flowmattic' ) . '</p>' . PHP_EOL;
										$email_body .= '<p>' . esc_html__( 'To avoid this issue in the future, make sure the crons are running properly on your site.', 'flowmattic' ) . '</p>' . PHP_EOL;
										$email_body .= '<p><i>' . esc_html__( 'This is an automated email, please do not reply to this email.', 'flowmattic' ) . '</i></p>' . PHP_EOL . PHP_EOL;
										$email_body .= '<p>' . esc_html__( 'Regards,', 'flowmattic' ) . PHP_EOL;
										$email_body .= esc_html__( 'FlowMattic Team', 'flowmattic' ) . '</p>' . PHP_EOL;

										$email_body = wpautop( $email_body );

										$to = $notification_email;

										// Translators: %s: Connect name.
										$subject = sprintf( esc_html__( 'FlowMattic: Authentication for %s seems expired on site - ', 'flowmattic' ), $connect->connect_name ) . wp_parse_url( get_site_url(), PHP_URL_HOST );
										$body    = $email_body;
										$headers = array( 'Content-Type: text/html; charset=UTF-8' );

										wp_mail( $to, $subject, $body, $headers );

										// Set transient for 1 day.
										set_transient( 'flowmattic_notification_email_sent_' . $connect->id, true, DAY_IN_SECONDS );
									}
								}
							}
						} else {
							// Check if the token refresh cron is registered.
							$cron_args = array(
								'connect_id' => $connect->id,
							);

							if ( ! wp_next_scheduled( 'flowmattic_connect_refresh_token', $cron_args ) ) {
								// Schedule the token refresh cron 5 minutes from now.
								$schedule_time = 300;
								$credentials   = $connect->connect_data;

								// Update the token expiry time.
								$credentials['expires_in'] = $schedule_time;

								// Register the cron.
								do_action( 'flowmattic_connect_register_cron', $credentials, $connect->id );
							}
						}
					}
				}
			}

			return $connect;
		}

		/**
		 * Get all connects from database.
		 *
		 * @since 3.0
		 * @access public
		 * @return object Connect data from database.
		 */
		public function get_all() {
			global $wpdb;

			// Get records from transient.
			$all_connects = get_transient( 'flowmattic_all_connects' );

			// If transient is not set, get records from database.
			if ( false === $all_connects ) {
				$all_connects = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 ) ); // phpcs:ignore

				// Set transient for 1 hour.
				set_transient( 'flowmattic_all_connects', $all_connects, HOUR_IN_SECONDS );
			}

			return $all_connects;
		}

		/**
		 * Get all connects from database for the specified user.
		 *
		 * @since 3.0
		 * @access public
		 * @param string $user_email The user email.
		 * @return object Connect data from database.
		 */
		public function get_user_connects( $user_email ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `connect_settings` LIKE %s', '%"user_email":"' . $user_email . '"%' ) ); // phpcs:ignore
		}
	}
}
