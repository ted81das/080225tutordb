<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Iterator_End {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for iterator end.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'iterator_end',
			array(
				'name'         => esc_attr__( 'Iterator End', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator-end/icon.svg',
				'instructions' => __( 'Store data for iterator.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
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
		wp_enqueue_script( 'flowmattic-app-view-iterator-end', FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator-end/view-iterator-end.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'end_iterator' => array(
				'title'       => esc_attr__( 'End the Iterator', 'flowmattic' ),
				'description' => esc_attr__( 'Iterator will stop at this action step.', 'flowmattic' ),
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

		$step    = (array) $step;
		$action  = $step['action'];
		$fields  = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$task_id = isset( $step['task_history_id'] ) ? $step['task_history_id'] : $workflow_id;

		$response = array(
			'status'  => 'success',
			'message' => esc_html__( 'Iterator will stop at this action step. Next steps will be executed after the iterator is fully executed', 'flowmattic' ),
		);

		return wp_json_encode( $response );
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
		$event       = $event_data['event'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Iterator_End();
