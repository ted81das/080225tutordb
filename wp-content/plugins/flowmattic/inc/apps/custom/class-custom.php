<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Custom {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for custom.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'custom',
			array(
				'name'         => esc_attr__( 'Custom Action', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/custom/icon.svg',
				'instructions' => __( 'Run your custom action to pass data to this workflow using the above code. Do not replace or remove the <strong>Workflow ID</strong> from the code.', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		add_action( 'wp_ajax_flowmattic_capture_custom_action_data', array( $this, 'capture_custom_action_data' ) );
	}

	/**
	 * Check if webhook has received any data.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function capture_custom_action_data() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['webhook-id'] ) && isset( $_POST['capture'] ) ) {
			$webhook_id       = $_POST['webhook-id'];
			$capture_response = $_POST['capture'];

			update_option( 'webhook-capture-live', $webhook_id, false );
			if ( 1 === (int) $capture_response ) {
				delete_option( 'webhook-capture-' . $webhook_id );
			}

			$webhook_capture = get_option( 'webhook-capture-' . $webhook_id, array() );

			if ( $webhook_capture ) {
				$reply = array(
					'status'          => 'success',
					'capture'         => $capture_response,
					'webhook_capture' => $webhook_capture,
				);
				echo wp_json_encode( $reply );
			} else {
				$reply = array(
					'status'  => 'pending',
					'capture' => $capture_response,
				);
				echo wp_json_encode( $reply );
			}
		}

		die();
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-custom', FLOWMATTIC_PLUGIN_URL . 'inc/apps/custom/view-custom.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'action_trigger' => array(
				'title'       => esc_attr__( 'Custom action triggered', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when the action is triggered with the code below', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Custom();
