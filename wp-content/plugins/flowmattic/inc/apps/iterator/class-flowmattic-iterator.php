<?php
/**
 * Application Name: FlowMattic Iterator
 * Description: Add Iterator module to FlowMattic.
 * Version: 1.1
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
 * Iterator module integration class.
 *
 * @since 1.1
 */
class FlowMattic_Iterator {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for filter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'iterator',
			array(
				'name'         => esc_attr__( 'Iterator by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator/icon.svg',
				'instructions' => 'Loop through the array items and execute following steps.',
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);

		add_action( 'flowmattic_execute_iterator', array( $this, 'execute_iterations' ), 10, 8 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-iterator', FLOWMATTIC_PLUGIN_URL . 'inc/apps/iterator/view-iterator.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.1
	 * @return array
	 */
	public function get_actions() {
		return array(
			'loop_through_array' => array(
				'title'       => esc_attr__( 'Loop Through Array', 'flowmattic' ),
				'description' => esc_attr__( 'Loop through the array items and execute following steps for each item.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the workflow step.
	 *
	 * @access public
	 * @since 1.1
	 * @param string $workflow_id     Workflow ID.
	 * @param array  $iterator_array  Iterator array.
	 * @param array  $response_data   Response data still current step.
	 * @param array  $next_step_id    Next step ID to continue execution.
	 * @param array  $task_history_id Task history ID.
	 * @param bool   $simple_response Whether to display iterator response for nested item in simple format or JSON.
	 * @param int    $step_number     Step number.
	 * @param array  $additional_options Additional options.
	 * @return void
	 */
	public function execute_iterations( $workflow_id, $iterator_array, $response_data, $next_step_id, $task_history_id, $simple_response, $step_number, $additional_options ) {
		$arrays      = is_array( $iterator_array ) ? $iterator_array : json_decode( stripslashes( $iterator_array ), true );
		$count       = 1;
		$array_count = is_array( $arrays ) ? count( $arrays ) : 0;

		// Get the iteration method and batch size.
		$iteration_method = isset( $additional_options['iteratorMethod'] ) ? $additional_options['iteratorMethod'] : 'direct';
		$batch_size       = isset( $additional_options['batchSize'] ) ? $additional_options['batchSize'] : 10;

		// If arrays is empty, decode without stripslashes.
		if ( empty( $arrays ) ) {
			$arrays = json_decode( $iterator_array, true );
		}

		$iterator_data = array(
			'next_step_id'    => $next_step_id,
			'task_history_id' => $task_history_id,
		);

		if ( '' === $simple_response ) {
			$simple_response = false;
		}

		if ( 'batch' === $iteration_method ) {
			flowmattic_process_iterator_in_batches( $workflow_id, $iterator_data, $simple_response, $arrays, $batch_size );
		} else {
			foreach ( $arrays as $index => $array ) {
				$response_array = array();

				$response_array['array_item_number'] = $count;

				if ( is_numeric( $index ) ) {
					$index = 0;
				}

				if ( is_array( $array ) ) {
					foreach ( $array as $key => $array_item ) {
						if ( is_array( $array_item ) ) {
							if ( $simple_response ) {
								$response_array = flowmattic_recursive_array( $response_array, $key, $array_item );
							} else {
								$response_array[ $key ] = wp_json_encode( $array_item );
							}
						} else {
							$response_array[ $key ] = $array_item;
						}
					}
				} else {
					$response_array[ $index ] = $array;
				}

				// Assign response array to iterator.
				$response_data[ 'iterator' . $step_number ] = $response_array;

				// Assign response data to iterator.
				$iterator_data['response_data'] = $response_data;

				if ( $array_count === $count ) {
					$iterator_data['last_step'] = true;
				}

				$flowmattic_workflow = new FlowMattic_Workflow();
				$flowmattic_workflow->run( $workflow_id, $response_array, array(), $iterator_data, true );

				$count = $count + 1;
			}
		}
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.1
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event           = isset( $event_data['event'] ) ? $event_data['event'] : '';
		$fields          = $event_data['fields'];
		$arrays          = is_array( $fields['iteratorArray'] ) ? $fields['iteratorArray'] : json_decode( stripslashes( $fields['iteratorArray'] ), true );
		$simple_response = isset( $fields['iterator_simple_response'] ) && 'No' === $fields['iterator_simple_response'] ? false : true;
		$count           = 1;

		// If arrays is empty, decode without stripslashes.
		if ( empty( $arrays ) ) {
			$arrays = json_decode( $fields['iteratorArray'], true );
		}

		$response_array = array();

		foreach ( $arrays as $index => $array ) {
			if ( 1 !== $count && ( isset( $arrays[0] ) && is_array( $arrays[0] ) ) ) {
				break;
			}

			$response_array['array_item_number'] = $count;

			if ( is_array( $array ) ) {
				foreach ( $array as $key => $array_item ) {
					if ( is_array( $array_item ) ) {
						if ( $simple_response ) {
							$response_array = flowmattic_recursive_array( $response_array, $key, $array_item );
						} else {
							$response_array[ $key ] = wp_json_encode( $array_item );
						}
					} else {
						$response_array[ $key ] = $array_item;
					}
				}
			} else {
				$response_array[ $index ] = $array;
			}

			$count = $count + 1;
		}

		return wp_json_encode( $response_array );
	}
}

new FlowMattic_Iterator();
