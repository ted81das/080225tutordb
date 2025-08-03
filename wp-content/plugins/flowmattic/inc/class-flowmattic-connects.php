<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Connects main class.
 *
 * @since 3.0
 */
class FlowMattic_Connects {

	/**
	 * Stores the connects used by external apps.
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected static $external_connects = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function __construct() {
		// Action to register the refresh token cron.
		add_action( 'flowmattic_connect_register_cron', array( $this, 'register_cron' ), 10, 2 );

		// Action to profess the refresh token cron.
		add_action( 'flowmattic_connect_refresh_token', array( $this, 'refresh_token' ), 1, 2 );

		// Ajax to save connect.
		add_action( 'wp_ajax_flowmattic_connect_save_auth_data', array( $this, 'connect_save_auth_data' ) );

		// Ajax to get the captured connect auth data.
		add_action( 'wp_ajax_flowmattic_connect_capture_data', array( $this, 'capture_auth_data' ) );

		// Ajax to get the connect settings.
		add_action( 'wp_ajax_flowmattic_connect_edit_settings', array( $this, 'get_connect_settings' ) );

		// Ajax to delete the connect settings.
		add_action( 'wp_ajax_flowmattic_connect_delete', array( $this, 'delete_connect' ) );
	}

	/**
	 * Add connects for external integrations.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $slug     Application slug.
	 * @param array  $settings Connect settings.
	 * @return void
	 */
	public static function add_connect( $slug, $settings ) {
		// Add the connect to array.
		self::$external_connects[ $slug ] = $settings;
	}

	/**
	 * Retrieve the list of all registered or single connect.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $slug Application slug.
	 * @return array
	 */
	public static function get_connect( $slug = 'all' ) {
		// Return all connects if not single requested.
		return ( 'all' === $slug ) ? self::$external_connects : ( isset( self::$external_connects[ $slug ] ) ? self::$external_connects[ $slug ] : array() );
	}

	/**
	 * Register the cron for refreshing token.
	 *
	 * @access public
	 * @since 3.0
	 * @param array $credentials Auth credentials.
	 * @param int   $connect_id  Connect DB ID.
	 * @return void
	 */
	public function register_cron( $credentials, $connect_id ) {
		$expires_in      = $credentials['expires_in'];
		$expiration_time = time() + $expires_in;
		$run_time        = $expiration_time - ( 10 * 60 ); // 10 minutes before expiration.
		$connect_id      = intval( $connect_id );
		$cron_args       = array(
			'connect_id' => $connect_id,
		);

		// Check if the cron is already scheduled.
		$next_scheduled = wp_next_scheduled(
			'flowmattic_connect_refresh_token',
			$cron_args
		);

		// Unschedule the existing cron.
		if ( $next_scheduled ) {
			wp_unschedule_event(
				$next_scheduled,
				'flowmattic_connect_refresh_token',
				$cron_args
			);
		}

		// Schedule new cron.
		wp_schedule_single_event(
			$run_time,
			'flowmattic_connect_refresh_token',
			$cron_args
		);

		// Verify the cron is scheduled, if not, re-schedule.
		$new_scheduled = wp_next_scheduled(
			'flowmattic_connect_refresh_token',
			$cron_args
		);

		// Retry scheduling the cron.
		if ( ! $new_scheduled ) {
			$this->register_cron( $credentials, $connect_id );
		}
	}

	/**
	 * Process the refresh token cron.
	 *
	 * @access public
	 * @since 3.0
	 * @param int $connect_id Connect DB ID.
	 * @return void
	 */
	public function refresh_token( $connect_id ) {
		$connect_id = intval( $connect_id );

		// Get the connect data.
		$args = array(
			'connect_id' => $connect_id,
		);

		$connect          = wp_flowmattic()->connects_db->get( $args );
		$connect_data     = is_array( $connect ) ? $connect['connect_data'] : $connect->connect_data;
		$connect_settings = is_array( $connect ) ? $connect['connect_settings'] : $connect->connect_settings;

		if ( isset( $connect_data['refresh_token'] ) ) {
			$refresh_token = stripslashes( $connect_data['refresh_token'] );
			if ( ! isset( $connect_settings['is_external'] ) ) {
				$auth_token_url = $connect_settings['tokenUrl'];
				$args           = array(
					'body'    => array(
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
						'client_id'     => $connect_settings['client_id'],
						'client_secret' => $connect_settings['client_secret'],
					),
					'headers' => array(
						'Accept' => 'application/json',
					),
					'timeout' => 60,
				);
			} else {
				$auth_token_url = isset( $connect_settings['endpoint_url'] ) ? $connect_settings['endpoint_url'] : $connect_settings['callback_url'];
				$args           = array(
					'body'    => array(
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
					),
					'headers' => array(
						'Accept' => 'application/json',
					),
					'timeout' => 60,
				);

				// Get external connects.
				$external_connects = flowmattic_get_connects();

				$external_slug = $connect_settings['external_slug'];
				$integration   = $external_connects[ $external_slug ];

				// If the connect is for custom app, use the client ID and secret.
				if ( isset( $integration['custom'] ) && isset( $connect_settings['client_id'] ) ) {
					$args['body']['client_id']     = $connect_settings['client_id'];
					$args['body']['client_secret'] = $connect_settings['client_secret'];
				}

				if ( isset( $connect_settings['auth_api_base'] ) && isset( $connect_settings['token_url'] ) ) {
					$auth_base_url = $connect_settings['auth_api_base'];
					$auth_token_url = $connect_settings['token_url'];
					$auth_token_url = str_replace( '{base_url}', $auth_base_url, $auth_token_url );
				}
			}

			$request       = wp_remote_post( $auth_token_url, $args );
			$request_body  = wp_remote_retrieve_body( $request );
			$response_code = wp_remote_retrieve_response_code( $request );
			$response      = json_decode( $request_body, true );

			// If is slack connect, make sure to refresh the user tokens as well.
			$user_response = array();
			if ( isset( $connect_settings['external_slug'] ) && 'slack' === $connect_settings['external_slug'] && isset( $connect_data['authed_user'] ) ) {
				$user_token_data    = $connect_data['authed_user'];
				$user_refresh_token = $user_token_data['refresh_token'];

				$args = array(
					'body'    => array(
						'grant_type'    => 'refresh_token',
						'refresh_token' => $user_refresh_token,
					),
					'headers' => array(
						'Accept' => 'application/json',
					),
					'timeout' => 20,
				);

				$request       = wp_remote_post( $auth_token_url, $args );
				$request_body  = wp_remote_retrieve_body( $request );
				$user_response = json_decode( $request_body, true );
			}

			// Assign the response to connect data.
			$connect_data = $response;

			// Set the expires in.
			$expires_in = isset( $response['expires_in'] ) ? $response['expires_in'] : 0;

			// Add the slack user access token.
			if ( ! empty( $user_response ) ) {
				$connect_data['authed_user'] = $user_response;

				// If the expires for the user is earlier, try to renew it first.
				if ( $expires_in > $user_response['expires_in'] ) {
					$expires_in = $user_response['expires_in'];
				}
			}

			// If refresh token is not received in response, use the previous one.
			if ( ! isset( $response['refresh_token'] ) ) {
				$connect_data['refresh_token'] = stripslashes( $refresh_token );
			}

			// Generate the array data to store in database.
			$args = array(
				'connect_id'   => intval( $connect_id ),
				'connect_data' => base64_encode( wp_json_encode( $connect_data ) ), // @codingStandardsIgnoreLine
			);

			$connects_db = wp_flowmattic()->connects_db;
			$connects_db->update( $args );

			$credentials = array(
				'expires_in' => $expires_in,
			);

			// Register the cron to renew the token.
			do_action( 'flowmattic_connect_register_cron', $credentials, $connect_id );
		}
	}

	/**
	 * Process the ajax to save the connect authentications.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function connect_save_auth_data() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$connects_db  = wp_flowmattic()->connects_db;
			$data         = $_POST;
			$connect_name = $data['connect_name'];

			// Delete the data not required for connect data.
			unset( $data['action'] );
			unset( $data['workflow_nonce'] );
			unset( $data['connect_name'] );

			// Generate the array data to store in database.
			$args = array(
				'connect_name'     => esc_attr( $connect_name ),
				'connect_data'     => '',
				'connect_settings' => $data,
			);

			if ( ! isset( $data['connect_id'] ) || '' === $data['connect_id'] ) {
				$connect_id =  intval( $connects_db->insert( $args ) );
			} else {
				// If request is for rename connect, remove the settings and data params.
				if ( isset( $data['connect_rename'] ) ) {
					unset( $args['connect_data'] );
					unset( $args['connect_settings'] );
				} else {
					$args['connect_settings'] = base64_encode( wp_json_encode( $args['connect_settings'] ) ); // @codingStandardsIgnoreLine
				}

				$connect_id         = intval( $data['connect_id'] );
				$args['connect_id'] = $connect_id;

				// If connect ID present, update.
				$connects_db->update( $args );
			}

			echo wp_json_encode(
				array(
					'status'     => 'success',
					'message'    => esc_html__( 'Connect added successfully!', 'flowmattic' ),
					'connect_id' => $connect_id,
				)
			);
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Check if connect has received any data.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function capture_auth_data() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['connect_id'] ) ) {
			$connect_id       = $_POST['connect_id'];
			$capture_response = isset( $_POST['capture'] ) ? $_POST['capture'] : '';

			$auth_capture = get_option( 'fm_auth_data_' . $connect_id, false );

			if ( $auth_capture ) {
				$reply = array(
					'status'  => 'success',
					'capture' => $capture_response,
				);

				// Generate the array data to store in database.
				$args = array(
					'connect_id'   => intval( $connect_id ),
					'connect_data' => $auth_capture,
				);

				$connects_db = wp_flowmattic()->connects_db;
				$connects_db->update( $args );

				// Delete the temp. connect data.
				delete_option( 'fm_auth_data_' . $connect_id );

				// Print the response.
				echo wp_json_encode( $reply );
			} else {
				$reply = array(
					'status'  => 'pending',
					'capture' => $capture_response,
				);

				// Print the response.
				echo wp_json_encode( $reply );
			}
		}

		wp_die();
	}

	/**
	 * Get connect settings for editing.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function get_connect_settings() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['connect_id'] ) ) {
			$connect_id = $_POST['connect_id'];

			// Get the connect data.
			$args = array(
				'connect_id' => intval( $connect_id ),
			);

			$connect       = wp_flowmattic()->connects_db->get( $args );
			$response_data = wp_json_encode( $connect );
		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid connect ID. Please try refreshing the page, or contact support', 'flowmattic' ),
				)
			);
		}

		// Print the JSON for Ajax.
		echo $response_data;

		wp_die();
	}

	/**
	 * Delete connect from database.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function delete_connect() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['connect_id'] ) ) {
			$connect_id = $_POST['connect_id'];

			// Get the connect data.
			$args = array(
				'connect_id' => $connect_id,
			);

			// Process deleting connect.
			wp_flowmattic()->connects_db->delete( $args );

			// Check if the cron is scheduled.
			$next_scheduled = wp_next_scheduled(
				'flowmattic_connect_refresh_token',
				$args
			);

			// Unschedule the existing cron, since the connect is no longer available.
			if ( $next_scheduled ) {
				wp_unschedule_event(
					$next_scheduled,
					'flowmattic_connect_refresh_token',
					$args
				);
			}
		}

		$response_data = wp_json_encode( $args );

		// Print the JSON for Ajax.
		echo $response_data;

		wp_die();
	}
}
