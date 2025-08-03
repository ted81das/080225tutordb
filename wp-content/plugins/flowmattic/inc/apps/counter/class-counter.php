<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Counter {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for counter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'counter',
			array(
				'name'         => esc_attr__( 'Counter', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/counter/icon.svg',
				'instructions' => __( 'Store workflow execution count.', 'flowmattic' ),
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
	 * @since 2.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-counter', FLOWMATTIC_PLUGIN_URL . 'inc/apps/counter/view-counter.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'incremental_counter' => array(
				'title'       => esc_attr__( 'Incremental Counter', 'flowmattic' ),
				'description' => esc_attr__( 'Increase the number every time workflow executes.', 'flowmattic' ),
			),
			'decremental_counter' => array(
				'title'       => esc_attr__( 'Decremental Counter', 'flowmattic' ),
				'description' => esc_attr__( 'Decrement the number every time workflow executes.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step   = (array) $step;
		$action = $step['action'];
		$fields = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );

		switch ( $action ) {
			case 'incremental_counter':
				$response = $this->incremental_counter( $fields, $workflow_id );
				break;

			case 'decremental_counter':
				$response = $this->decremental_counter( $fields, $workflow_id );
				break;
		}

		return $response;
	}

	/**
	 * Storage as string.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array  $data        Request data.
	 * @param string $workflow_id Workflow ID.
	 * @return string
	 */
	public function incremental_counter( $data, $workflow_id ) {
		$initial_value   = (int) $data['initial_value'];
		$change_value    = (int) $data['change_value'];
		$reset_execution = ( isset( $data['reset_execution'] ) ) ? $data['reset_execution'] : 'no';
		$reset_value     = (int) $data['reset_value'];

		// Get the old value.
		$old_value = (int) get_option( 'flowmattic_counter_' . $workflow_id, $initial_value );

		if ( 'yes' === $reset_execution && $old_value >= $reset_value ) {
			// Reset counter.
			$new_value = $initial_value;
		} else {
			// Increase the value.
			$new_value = $old_value + $change_value;
		}

		// Update the value back to database.
		update_option( 'flowmattic_counter_' . $workflow_id, $new_value, false );

		$response = array(
			'status'          => 'success',
			'initial_value'   => $initial_value,
			'change_by_value' => $change_value,
			'final_value'     => $new_value,
		);

		if ( 'yes' === $reset_execution && '' !== $reset_value ) {
			$response['reset_counter_after'] = $reset_value;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Storage as array.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array  $data        Request data.
	 * @param string $workflow_id Workflow ID.
	 * @return array
	 */
	public function decremental_counter( $data, $workflow_id ) {
		$initial_value   = (int) $data['initial_value'];
		$change_value    = (int) $data['change_value'];
		$reset_execution = ( isset( $data['reset_execution'] ) ) ? $data['reset_execution'] : 'no';
		$reset_value     = (int) $data['reset_value'];

		// Get the old value.
		$old_value = (int) get_option( 'flowmattic_counter_' . $workflow_id, $initial_value );

		if ( 'yes' === $reset_execution && $reset_value >= $old_value ) {
			// Reset counter.
			$new_value = $initial_value;
		} else {
			// Decrease the value.
			$new_value = $old_value - $change_value;
		}

		// Update the value back to database.
		update_option( 'flowmattic_counter_' . $workflow_id, $new_value, false );

		$response = array(
			'status'          => 'success',
			'initial_value'   => $initial_value,
			'change_by_value' => $change_value,
			'final_value'     => $new_value,
		);

		if ( 'yes' === $reset_execution && '' !== $reset_value ) {
			$response['reset_counter_after'] = $reset_value;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event       = $event_data['event'];
		$settings    = $event_data['settings'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Counter();
