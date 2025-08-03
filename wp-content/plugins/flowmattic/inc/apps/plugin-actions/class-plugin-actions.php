<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Plugin_Actions {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for plugin actions.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'plugin_actions',
			array(
				'name'         => esc_attr__( 'Plugin Actions', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/plugin-actions/icon.svg',
				'instructions' => __( 'Enter WordPress or your plugin action to trigger this workflow.', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		add_action( 'wp_ajax_flowmattic_save_plugin_action_data', array( $this, 'save_plugin_action_data_ajax' ) );
		add_action( 'wp_ajax_flowmattic_capture_plugin_action_data', array( $this, 'capture_plugin_action_data_ajax' ) );
	}

	/**
	 * Check plugin action to receive data.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function save_plugin_action_data_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['actionHook'] ) && '' !== $_POST['actionHook'] ) {
			$action_hook = $_POST['actionHook'];
			$webhook_id  = isset( $_POST['workflow_id'] ) ? $_POST['workflow_id'] : $_POST['webhook-id'];

			// Get the workflow hooks registered.
			$workflow_hooks = get_option( 'flowmattic_workflow_hooks', array() );

			// Assign the current action hook to the current workflow.
			$workflow_hooks[ $webhook_id ] = $action_hook;

			// Update the workflow hooks to database.
			update_option( 'flowmattic_workflow_hooks', $workflow_hooks, false );
		}

		die();
	}

	/**
	 * Check plugin action to receive data.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function capture_plugin_action_data_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['webhook-id'] ) && isset( $_POST['capture'] ) && isset( $_POST['actionHook'] ) ) {
			$webhook_id       = $_POST['webhook-id'];
			$capture_response = $_POST['capture'];

			update_option( 'webhook-capture-live', $webhook_id, false );

			if ( 1 === (int) $capture_response ) {
				delete_option( 'webhook-capture-' . $webhook_id );
			}

			$webhook_capture = get_option( 'webhook-capture-' . $webhook_id, array() );

			if ( $webhook_capture ) {

				delete_option( 'webhook-capture-' . $webhook_id );
				delete_option( 'webhook-capture-live' );

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

			die();
		}
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-plugin-actions', FLOWMATTIC_PLUGIN_URL . 'inc/apps/plugin-actions/view-plugin-actions.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'action_trigger' => array(
				'title'       => esc_attr__( 'Plugin or WP action triggered', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when the action provided is triggered by your plugin or WP', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Plugin_Actions();
