<?php
/**
 * Application Name: Webhook Outgoing
 * Description: Add Webhook Outgoing integration to FlowMattic.
 * Version: 1.0
 * Author: InfiWebs
 * Author URI: https://www.infiwebs.com
 * Textdomain: flowmattic
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook Outgoing integration class.
 *
 * @since 1.0
 */
class FlowMattic_Webhook_Outgoing {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 3.1.1
	 * @var array|string
	 */
	public $request_body;

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
			'webhook_outgoing',
			array(
				'name'         => esc_attr__( 'Webhook Outgoing', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook-outgoing/icon.svg',
				'instructions' => __( 'Copy and enter the above webhook URL to your apps webhook setting', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'type'         => 'action',
				'base'         => 'core',
				'version'      => '1.0',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-webhook-outgoing', FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook-outgoing/view-webhook-outgoing.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'send_data' => array(
				'title'       => esc_attr__( 'Submit Data to Webhook', 'flowmattic' ),
				'description' => esc_attr__( 'Send data to a webhook URL using POST method in JSON format.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$blocking       = isset( $fields['request_blocking'] ) && 'no' === $fields['request_blocking'] ? true : false;
		$response_array = array();

		$post_fields = isset( $step['webhook_outgoing_parameters'] ) ? $step['webhook_outgoing_parameters'] : array();

		if ( empty( $post_fields ) ) {
			if ( isset( $step['actionAppArgs']['webhook_outgoing_parameters'] ) ) {
				$post_fields = $step['actionAppArgs']['webhook_outgoing_parameters'];
			} elseif ( isset( $step['settings']['webhook_outgoing_parameters'] ) ) {
				$post_fields = $step['settings']['webhook_outgoing_parameters'];
			}
		}

		// Fix the JSON data.
		foreach ( $post_fields as $key => $value ) {
			if ( is_array( $value ) ) {
				$post_fields[ $key ] = $value;
			} elseif ( is_array( json_decode( $value, true ) ) ) {
				$post_fields[ $key ] = json_decode( $value, true );
			} elseif ( $value && is_array( json_decode( stripslashes( $value ), true ) ) ) {
				$post_fields[ $key ] = json_decode( stripslashes( $value ), true );
			}
		}

		$args = array(
			'headers'   => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
				'X-User-Agent' => 'FlowMattic',
			),
			'sslverify' => false,
			'body'      => wp_json_encode( $post_fields ),
		);

		if ( $blocking ) {
			$args['blocking'] = false;
		}

		// Set the request body.
		$this->request_body = $post_fields;

		// Get the webhook URL.
		$webhook_url = isset( $fields['webhook_outgoing_url'] ) ? $fields['webhook_outgoing_url'] : ( isset( $fields['actionAppArgs']['webhook_outgoing_url'] ) ? $fields['actionAppArgs']['webhook_outgoing_url'] : '' );

		if ( empty( $webhook_url ) ) {
			$response_array = array(
				'status'  => esc_attr__( 'Error', 'flowmattic' ),
				'message' => esc_attr__( 'Webhook URL is required.', 'flowmattic' ),
			);

			return wp_json_encode( $response_array );
		}

		// Create a new contact.
		$request       = wp_remote_post( $webhook_url, $args );
		$response_code = wp_remote_retrieve_response_code( $request );

		if ( $blocking ) {
			return wp_json_encode(
				array(
					'no_response' => esc_attr__( 'Request response not available', 'flowmattic' ),
				)
			);
		} else {
			$response = wp_remote_retrieve_body( $request );
		}

		if ( 200 !== $response_code ) {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					// translators: error code.
					'message' => ( 404 === $response_code ) ? sprintf( __( 'Error: %s. Webhook URL not found. Make sure your you enter the publicly accessible webhook url.', 'flowmattic' ), $response_code ) : $response_code,
				)
			);
		}

		// If there's no response, return default message.
		if ( '' === $response ) {
			$response = wp_json_encode(
				array(
					'no_reply' => esc_attr__( 'Request has no response data', 'flowmattic' ),
				)
			);
		}

		if ( 'ok' === strtolower( $response ) ) {
			$response = wp_json_encode( array( $response ) );
		}

		return $response;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 3.1.1
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event          = $event_data['event'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $event_data['actionAppArgs'] ) ? $event_data['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$event_data['fields'] = $fields;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Webhook_Outgoing();
