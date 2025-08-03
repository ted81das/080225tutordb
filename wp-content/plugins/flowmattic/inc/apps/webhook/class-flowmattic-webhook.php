<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook by FlowMattic.
 */
class FlowMattic_Webhook {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Webhook Outgoing.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'webhook',
			array(
				'name'         => esc_attr__( 'Webhooks by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook/icon.svg',
				'instructions' => 'Copy the Webhook URL and send your request to this url from your application or website.',
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		// Register REST API route for webhook capture as fallback method.
		add_action( 'rest_api_init', array( $this, 'register_webhook_route' ) );
	}

	/**
	 * Enqueue custom view for Webhook Outgoing.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		// Enqueue the custom view.
		wp_enqueue_script( 'flowmattic-webhook', FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook/view-webhook.js', array( 'jquery' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'capture' => array(
				'title' => esc_attr__( 'Capture Webhook Response', 'flowmattic' ),
			),
		);
	}

	/**
	 * Get the webhook url.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id Workflow ID.
	 * @return string
	 */
	public static function get_url( $workflow_id ) {
		$webhook_url = apply_filters( 'flowmattic_webhook_url', home_url( '/webhook/capture/' . $workflow_id ) );
		return $webhook_url;
	}

	/**
	 * Get the webhook url based on REST API.
	 *
	 * @access public
	 * @since 4.0
	 * @param string $workflow_id Workflow ID.
	 * @return string
	 */
	public static function get_rest_url( $workflow_id ) {
		$webhook_url = apply_filters( 'flowmattic_webhook_url', rest_url( 'webhook/capture/' . $workflow_id ) );
		return $webhook_url;
	}

	/**
	 * Register webhook route.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function register_webhook_route() {
		register_rest_route(
			'webhook',
			'/capture/(?P<workflow_id>[a-zA-Z0-9]+)/',
			array(
				'methods'             => 'GET, POST',
				'callback'            => array( $this, 'capture_webhook' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Capture webhook.
	 *
	 * @access public
	 * @since 4.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function capture_webhook( WP_REST_Request $request ) {
		$workflow_id      = $request->get_param( 'workflow_id' );
		$is_capturing     = ( get_option( 'webhook-capture-live', false ) === $workflow_id );
		$webhook_redirect = false;
		$webhook_response = '';

		// Get all the data.
		$parameters = $request->get_params();

		// Capture all data as JSON.
		$json_data = wp_json_encode( $parameters );

		// Response.
		$response = array(
			'status'  => 'success',
			'message' => 'Response Captured',
		);

		// Check if request is for OAuth Connect.
		if ( 'fm_oauth' === $workflow_id ) {
			if ( ! empty( $parameters ) ) {
				$auth_data = $parameters;
			} else {
				$auth_data = isset( $parameters['authData'] ) ? $parameters['authData'] : '';
				if ( '' !== $auth_data ) {
					$auth_data = json_decode( base64_decode( $auth_data ), true );
				}
			}

			if ( ! empty( $auth_data ) ) {
				$connect_id   = isset( $auth_data['connect_id'] ) ? $auth_data['connect_id'] : '';
				$credentials  = isset( $auth_data['credentials'] ) ? json_decode( stripslashes( $auth_data['credentials'] ), true ) : '';
				$connect_data = isset( $auth_data['credentials'] ) ? base64_encode( stripslashes( $auth_data['credentials'] ) ) : '';

				// If token is set to be expired, register a cron to renew it later.
				if ( isset( $credentials['expires_in'] ) ) {
					do_action( 'flowmattic_connect_register_cron', $credentials, $connect_id );
				}

				// Update the data for temp. use in options table.
				update_option( 'fm_auth_data_' . $connect_id, $connect_data );
			}

			// Return default response.
			$response = wp_json_encode(
				array(
					'status'  => 'success',
					'message' => 'Response Captured',
				)
			);

			// Set the request content type.
			header( 'Content-Type: application/json' );

			// Set Access-Control-Allow-Origin header.
			header( 'Access-Control-Allow-Origin: *' );

			// Set User-Agent header.
			header( 'User-Agent: FlowMattic/' . FLOWMATTIC_VERSION );

			die( $response );
		}

		// Remove the workflow_id from the parameters.
		unset( $parameters['workflow_id'] );

		// Get the workflow settings.
		$args     = array(
			'workflow_id' => $workflow_id,
		);
		$workflow = wp_flowmattic()->workflows_db->get( $args );

		if ( $workflow ) {
			$steps    = json_decode( $workflow->workflow_steps, true );
			$settings = json_decode( $workflow->workflow_settings, true );

			// Get the trigger step data.
			$workflow_data = $steps[0];
		}

		// Get the custom response.
		if ( isset( $workflow_data['webhook_response'] ) && 'Yes' === $workflow_data['webhook_response'] ) {
			$webhook_response = $workflow_data['webhook_custom_responce'];
		}

		if ( '' !== $webhook_response ) {
			$response = stripslashes( $webhook_response );
			$response = ( ! is_array( json_decode( $response, true ) ) ) ? $response : json_decode( $response, true );
		}

		// If webhook redirect is set.
		if ( isset( $workflow_data['workflowEndAction'] ) && 'redirect' === $workflow_data['workflowEndAction'] ) {
			$webhook_redirect = true;
		}

		// Check the simple response option.
		$simple_response = ( ! isset( $workflow_data['simple_response'] ) || 'Yes' === $workflow_data['simple_response'] ) ? true : false;

		// Create simple array.
		$processed_array = array();

		// Process the data.
		foreach ( $parameters as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! $simple_response ) {
					$processed_array[ $key ] = wp_json_encode( $value );
				} else {
					$processed_array = flowmattic_recursive_array( $processed_array, $key, $value );
				}
			} else {
				$processed_array[ $key ] = $value;
			}
		}

		// Assign the JSON data to the processed array.
		$processed_array['fm_webhook_data'] = $json_data;

		// Add the webhook capture time.
		$processed_array['webhook_captured_at'] = date_i18n( 'd-m-Y H:i:s' );

		// Fire an action to perform additional data check etc.
		do_action( 'flowmattic_webhook_response_captured', $workflow_id, $processed_array );

		// Check if the webhook is in capturing mode.
		if ( $is_capturing ) {
			update_option( 'webhook-capture-' . $workflow_id, $processed_array, false );
			delete_option( 'webhook-capture-live' );
			delete_option( 'webhook-capture-application' );
		} else {
			// If webhook is not in capturing mode, but the response is not yet captured, set it to be captured.
			if ( ! empty( $workflow_data ) && ! isset( $workflow_data['capturedData'] ) ) {
				update_option( 'webhook-capture-' . $workflow_id, $processed_array, false );
			}

			// Let the server breathe a little.
			// wait for 25 milliseconds.
			usleep( 25000 );

			// Trigger the workflow.
			do_action(
				'flowmattic_trigger_workflow',
				$workflow_id,
				$processed_array,
			);
		}

		// If webhook redirect, redirect to the URL.
		if ( $webhook_redirect ) {
			$redirect_url = isset( $workflow_data['workflowEndActionURL'] ) ? $workflow_data['workflowEndActionURL'] : '';

			// Filter for custom redirect.
			$redirect_url = apply_filters( 'flowmattic_webhook_redirect', $redirect_url, $workflow_id, $processed_array );

			if ( '' !== $redirect_url ) {
				// Redirect to the URL.
				wp_redirect( $redirect_url );
				exit;
			}
		}

		// Filter for custom response.
		$response = apply_filters( 'flowmattic_webhook_response', $response, $workflow_id, $processed_array );

		if ( isset( $processed_array['hub_challenge'] ) ) {
			$response = $processed_array['hub_challenge'];
			header( 'Content-Type: text/plain' );
			die( $response );
		} elseif ( isset( $processed_array['challenge'] ) ) {
			header( 'Content-Type: text/plain' );
			$response = $processed_array['challenge'];
			die( $response );
		}

		// Set Access-Control-Allow-Origin header.
		header( 'Access-Control-Allow-Origin: *' );

		// Set User-Agent header.
		header( 'User-Agent: FlowMattic/' . FLOWMATTIC_VERSION );

		// Return the response.
		return new WP_REST_Response( $response, 200 );
	}
}

new FlowMattic_Webhook();
