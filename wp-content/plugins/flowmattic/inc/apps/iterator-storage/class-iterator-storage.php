<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Iterator_Storage {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for iterator storage.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'iterator_storage',
			array(
				'name'         => esc_attr__( 'Iterator Storage', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator-storage/icon.svg',
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
		wp_enqueue_script( 'flowmattic-app-view-iterator-storage', FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator-storage/view-iterator-storage.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'store_as_variable' => array(
				'title'       => esc_attr__( 'Store as String', 'flowmattic' ),
				'description' => esc_attr__( 'Store the data as a string.', 'flowmattic' ),
			),
			'store_as_array'    => array(
				'title'       => esc_attr__( 'Store as Array', 'flowmattic' ),
				'description' => esc_attr__( 'Store the data as an array.', 'flowmattic' ),
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
		// Assign the step ID.
		$step_id = isset( $step['stepID'] ) ? $step['stepID'] : $workflow_id;

		// Assign the task ID.
		$task_id = isset( $step['task_history_id'] ) ? $step['task_history_id'] : $step_id;

		switch ( $action ) {
			case 'store_as_variable':
				$response = $this->store_as_variable( $fields, $workflow_id, $task_id );
				break;

			case 'store_as_array':
				$fields['array'] = isset( $step['iterator_storage_parameters'] ) ? $step['iterator_storage_parameters'] : array();
				$response        = $this->store_as_array( $fields, $workflow_id, $task_id );
				break;
		}

		return $response;
	}

	/**
	 * Storage as string.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param array  $data        Request data.
	 * @param string $workflow_id Workflow ID.
	 * @param string $task_id     Task ID.
	 * @return string
	 */
	public function store_as_variable( $data, $workflow_id, $task_id ) {
		$storage_key     = 'flowmattic_iterator_storage_' . $task_id;
		$storage_content = $data['iterator_storage_content'];

		// Get previously stored data from a transient
		$previous_data = get_transient( $storage_key );
		if ( false === $previous_data ) {
			$previous_data = ''; // Start with an empty string if no data is found
		}

		// Append the new content to the previously stored data
		$new_data = $previous_data . $storage_content;

		// Update the transient (valid for an hour, adjust as needed)
		set_transient( $storage_key, $new_data, HOUR_IN_SECONDS );

		$response = array(
			'status' => 'success',
			'data'   => $new_data,
		);

		return wp_json_encode( $response );
	}

	/**
	 * Storage as array.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param array  $data        Request data.
	 * @param string $workflow_id Workflow ID.
	 * @param string $task_id     Task ID.
	 * @return array
	 */
	public function store_as_array( $data, $workflow_id, $task_id ) {
		$storage_key = 'flowmattic_iterator_storage_' . $task_id;

		// Get previously stored data from a transient
		$previous_data = get_transient( $storage_key );
		if ( false === $previous_data ) {
			$previous_data = array( 'array' => array() );
		}

		$storage_content = array();
		foreach ( $data['array'] as $key => $value ) {
			$new_value = stripslashes( $value );
			$decoded   = json_decode( $new_value, true );
			if ( is_array( $decoded ) ) {
				$storage_content[ $key ] = $decoded;
			} else {
				$storage_content[ $key ] = $new_value;
			}
		}

		// Append the new data
		$previous_data['array'][] = $storage_content;

		// Update the transient (valid for an hour, adjust as needed)
		set_transient( $storage_key, $previous_data, HOUR_IN_SECONDS );

		$response = array(
			'status' => 'success',
			'data'   => wp_json_encode( $previous_data['array'] ),
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
		$settings    = $event_data['settings'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		$event_data['iterator_storage_parameters'] = isset( $settings['iterator_storage_parameters'] ) ? $settings['iterator_storage_parameters'] : array();

		// Replace action for testing.
		$event_data['action'] = $event;

		// Delete previous storage.
		delete_option( 'flowmattic_iterator_storage_' . $workflow_id );

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Iterator_Storage();
