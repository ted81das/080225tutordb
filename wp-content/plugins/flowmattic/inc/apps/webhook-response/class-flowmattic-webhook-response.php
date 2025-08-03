<?php
/**
 * Application Name: Webhook Response
 * Description: Add Webhook Response integration to FlowMattic.
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
 * Webhook Response integration class.
 *
 * @since 2.1.0
 */
class FlowMattic_Webhook_Response {
	/**
	 * Webhook response.
	 *
	 * @access public
	 * @since 5.2.0
	 * @var array
	 */
	public $webhook_response = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Webhook Response.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'webhook_response',
			array(
				'name'         => esc_attr__( 'Webhook Response', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook-response/icon.svg',
				'instructions' => __( 'Configure the response to webhook trigger', 'flowmattic' ),
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
	 * @since 2.1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-webhook-response', FLOWMATTIC_PLUGIN_URL . 'inc/apps/webhook-response/view-webhook-response.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'set_webhook_response' => array(
				'title'       => esc_attr__( 'Set Webhook Response', 'flowmattic' ),
				'description' => esc_attr__( 'Set the custom webhook response from the data in action steps', 'flowmattic' ),
			),
			'set_webhook_redirect' => array(
				'title'       => esc_attr__( 'Set Webhook Redirect URL', 'flowmattic' ),
				'description' => esc_attr__( 'Set the custom url to redirect the user after workflow completion', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action         = $step['action'];
		$response_array = array();
		$fields         = isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array();

		if ( 'set_webhook_response' === $action ) {
			$response_type = $fields['webhook_response_type'];
			$response_data = ( isset( $step['settings']['webhook_response_parameters'] ) ) ? $step['settings']['webhook_response_parameters'] : ( isset( $step['webhook_response_parameters'] ) ? $step['webhook_response_parameters'] : '' );

			if ( 'string' === $response_type ) {
				$response_data = stripslashes( $fields['response_string'] );
			} elseif ( 'custom_json' === $response_type ) {
				$response_data = json_decode( stripslashes( $step['custom_json'] ), true );
				$response_data = $response_data ? $response_data : array();
			}

			$response = $response_data;

			$this->webhook_response = $response;

			add_filter( 'flowmattic_webhook_response', array( $this, 'set_webhook_response' ), 10, 3 );

			if ( 'string' === $response_type ) {
				return wp_json_encode(
					array(
						'response' => $response,
					),
				);
			} else {
				return wp_json_encode( $response );
			}
		} elseif ( 'set_webhook_redirect' === $action ) {
			$redirect_url = $fields['redirect_url'];

			$this->webhook_redirect = $redirect_url;

			add_filter( 'flowmattic_webhook_redirect', array( $this, 'set_webhook_redirect' ), 10, 3 );

			return wp_json_encode(
				array(
					'redirect_url' => $redirect_url,
				),
			);
		}
	}

	/**
	 * Return the custom response to webhook.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param string $response     Workflow response.
	 * @param object $webhook_id   Workflow ID.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function set_webhook_response( $response, $webhook_id, $capture_data ) {
		return $this->webhook_response;
	}

	/**
	 * Return the custom redirect URL to webhook.
	 *
	 * @access public
	 * @since 4.1.3
	 * @param string $response     Workflow response.
	 * @param object $webhook_id   Workflow ID.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function set_webhook_redirect( $response, $webhook_id, $capture_data ) {
		return $this->webhook_redirect;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event          = $event_data['event'];
		$settings       = $event_data['settings'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		// Replace action for testing.
		$event_data['action'] = $event;

		// Set action app args.
		$event_data = $settings;

		// If action is set_webhook_redirect, return the default response.
		if ( 'set_webhook_redirect' === $event ) {
			$redirect_url = $fields['redirect_url'];
			return wp_json_encode(
				array(
					'status'       => esc_attr__( 'Success', 'flowmattic' ),
					'redirect_url' => $redirect_url,
				),
			);
		}

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Webhook_Response();
