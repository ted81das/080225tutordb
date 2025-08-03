<?php
/**
 * Application Name: FlowMattic Router
 * Description: Add Router module to FlowMattic.
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
 * Router module integration class.
 *
 * @since 2.0
 */
class FlowMattic_Router {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for filter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'router',
			array(
				'name'         => esc_attr__( 'Router by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/router/icon.svg',
				'instructions' => 'Loop through the array items and execute following steps.',
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
	 * @since 2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-router', FLOWMATTIC_PLUGIN_URL . 'inc/apps/router/view-router.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'conditionally_run' => array(
				'title' => esc_attr__( 'Conditionally run...', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the router delay step.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $delay_data   Workflow ID.
	 * @return void
	 */
	public function execute_router_delay( $delay_data ) {
		$task_history_id = $delay_data['task_history_id'];
		$next_step_id    = $delay_data['next_step_id'];
		$workflow_id     = $delay_data['workflow_id'];
		$router_id       = $delay_data['router_id'];
		$response_data   = array();

		$args = array(
			'workflow_id' => $workflow_id,
		);

		$tasks_db = wp_flowmattic()->tasks_db;
		$workflow = wp_flowmattic()->workflows_db->get( $args );
		$steps    = $workflow->workflow_steps;
		$steps    = json_decode( $steps, true );

		$skip_to_step = $router_id;
		$step_number  = 1;
		$route_letter = '';
		$route_number = 0;

		// Get the task data by ID.
		$task = $tasks_db->get_task_by_id( $task_history_id );
		$task = (array) $task[0];

		// Set the captured data from trigger step.
		$task_data         = json_decode( $task['task_data'], true );
		$task['task_data'] = $task_data;
		$task_captured     = $task_data[0]['captured_data'];
		$capture_data      = $task_captured;

		foreach ( $task['task_data'] as $key => $step ) {
			$application = $step['application'];

			if ( isset( $step['step_id'] ) && $next_step_id === $step['step_id'] ) {
				break;
			}

			// If steps before delay are already executed, get response data.
			$task_captured = $step['captured_data'];

			// Set the response data for the step.
			if ( isset( $step['route_letter'] ) ) {
				if ( '' === $route_letter ) {
					$route_number = $step_number;
				}

				if ( $route_letter !== $step['route_letter'] ) {
					$step_number = $route_number;
				}

				$route_letter = $step['route_letter'];

				// If application is router, record as filter for dynamic tag replacement.
				$application = ( 'router' === $application ) ? 'filter' : $application;

				// Set the dynamic tag for router step.
				$response_data[ 'route' . $step['route_letter'] . '.' . $application . $step_number ] = $task_captured;
			} else {
				$response_data[ $application . $step_number ] = $task_captured;
			}

			// Increase the step number accordingly to avoid conflicts.
			$step_number++; // @codingStandardsIgnoreLine Incrementing the step number.
		}

		foreach ( $steps as $i => $step ) {
			$application = $step['application'];

			// Router.
			if ( 'router' === $application ) {
				$router_steps = $step['routerSteps'];

				if ( ! empty( $router_steps ) ) {
					foreach ( $router_steps as $route_letter => $route_actions ) {
						foreach ( $route_actions as $key => $route ) {
							$route_application = $route['application'];

							if ( $next_step_id === $route['stepID'] ) {
								$this->execute_router( $workflow_id, $route_actions, $response_data, $task, $step_number, $router_id, $next_step_id );
								break 2;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Run the workflow step.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id   Workflow ID.
	 * @param array  $route_actions Router array.
	 * @param array  $response_data Response data still current step.
	 * @param array  $task          Current task execution.
	 * @param int    $step_number   Current step number.
	 * @param string $router_id     Router step ID.
	 * @param string $next_route_id  Next step ID.
	 * @return array
	 */
	public function execute_router( $workflow_id, $route_actions, $response_data, $task, $step_number, $router_id, $next_route_id = '' ) {
		$tasks_db        = wp_flowmattic()->tasks_db;
		$app_response    = '';
		$next_step_flag  = true;
		$cur_step_number = $step_number;
		$route_letter    = '';
		$task_history_id = $task['task_id'];

		foreach ( $route_actions as $key => $step ) {
			$response_array = array();
			$application    = $step['application'];
			$route_letter   = $step['routerRoute'];

			// If next route is set, check if the step ID matches.
			if ( '' !== $next_route_id && $next_route_id !== $step['stepID'] ) {
				continue;
			}

			foreach ( $response_data as $app_id => $app_data ) {

				// If app data is not array, do not process.
				if ( ! is_array( $app_data ) ) {
					continue;
				}

				foreach ( $app_data as $app_key => $value ) {
					$replace_tag = '{' . $app_id . '.' . $app_key . '}';

					if ( is_array( $value ) ) {
						continue;
					}

					// If is null, assign empty string.
					$value = ( null === $value ) ? '' : str_replace( array( "\r", "\n" ), '', $value );

					// Do the dynamic tag replacement with their values.
					$updated_value = flowmattic_dynamic_tag_values( $step, $replace_tag, $value );

					// Assign the updated data back to step object.
					$step = $updated_value;
				}
			}

			// Find and update the variables with their values in the request data.
			$step = wp_flowmattic()->variables->find_and_replace( $step );

			// Set the pattern to find dynamic tags that are not replaced due to missing data in request.
			$pattern = '/\{(?:(?!\')(?!\")[a-zA-Z0-9_-]+\d+\.[a-zA-Z0-9-_\s]+)}/';

			// Convert the step data to json, for easy replacement.
			$data_string = wp_json_encode( $step );

			// Replace the matched dynamic tags with a blank string.
			$modified_string = preg_replace( $pattern, '', $data_string );

			// Assign the modified data back to step variable.
			if ( json_decode( $modified_string, true ) ) {
				$step = json_decode( $modified_string, true );
			}

			// Delay.
			if ( 'delay' === $application ) {
				$event  = $step['action'];
				$fields = $step['actionAppArgs'];

				$next_step_id = isset( $route_actions[ $key + 1 ] ) ? $route_actions[ $key + 1 ]['stepID'] : false;

				if ( 'delay_for' === $event ) {
					$delay_unit  = $fields['delayUnit'];
					$delay_value = $fields['delayValue'];
					$time_value  = '';

					// translators: Delay unit.
					$response = sprintf( __( 'Execution delayed for %1$s %2$s', 'flowmattic' ), $delay_value, $delay_unit );

					switch ( $delay_unit ) {
						case 'seconds':
							$time_value = (int) $delay_value;
							break;
						case 'minutes':
							$time_value = (int) $delay_value * 60;
							break;
						case 'hours':
							$time_value = (int) $delay_value * 3600;
							break;
						case 'days':
							$time_value = (int) $delay_value * 24 * 3600;
							break;
						case 'weeks':
							$time_value = (int) $delay_value * 7 * 24 * 3600;
							break;
					}

					// If seconds delay, execute immediately.
					if ( 'seconds' === $delay_unit ) {
						flowmattic_delay( $time_value );
					} else {
						wp_schedule_single_event( time() + $time_value, 'flowmattic_delay_workflow_route', array( $task_history_id, $next_step_id, $workflow_id, $router_id ) );
						$next_step_flag = false;
					}
				} elseif ( 'delay_until' === $event ) {
					$delay_time          = $fields['delayTime'];
					$time_difference     = ( strtotime( get_date_from_gmt( $delay_time ) ) - strtotime( date_i18n( 'Y-m-d H:i:s', strtotime( $delay_time ) ) ) );
					$timezone_delay_time = ( date_i18n( 'Y-m-d H:i:s', ( strtotime( date_i18n( 'Y-m-d H:i:s', strtotime( $delay_time ) ) ) - $time_difference ) ) );

					// translators: Delay time.
					$response   = sprintf( __( 'Execution paused until %s', 'flowmattic' ), $delay_time );
					$time_value = strtotime( $timezone_delay_time );

					// Breath a little.
					flowmattic_delay( 1 );

					wp_schedule_single_event( $time_value, 'flowmattic_delay_workflow_route', array( $task_history_id, $next_step_id, $workflow_id, $router_id ) );

					$next_step_flag = false;
				}

				$task['task_data'][] = array(
					'application'   => $application,
					'step_id'       => $step['stepID'],
					'route_letter'  => $route_letter,
					'captured_data' => array(
						'status'  => esc_attr__( 'Success', 'flowmattic' ),
						'message' => $response,
					),
				);
			}

			// If iterator.
			if ( 'iterator' === $application ) {
				$iterator_json  = $step['actionAppArgs']['iteratorArray'];
				$iterator_array = json_decode( $iterator_json, true );

				$is_valid_array = false;

				if ( is_array( $iterator_array ) ) {
					$is_valid_array = true;
				} elseif ( json_decode( $iterator_json, true ) ) {
					$iterator_array = json_decode( $iterator_json, true );
					$is_valid_array = true;
				} elseif ( json_decode( addslashes( $iterator_json ), true ) ) {
					$iterator_array = json_decode( addslashes( $iterator_json ), true );
					$is_valid_array = true;
				} elseif ( json_decode( stripslashes( $iterator_json ), true ) ) {
					$iterator_array = json_decode( stripslashes( $iterator_json ), true );
					$is_valid_array = true;
				}

				$task['task_data'][] = array(
					'application'   => $application,
					'step_id'       => $step['stepID'],
					'route_letter'  => $route_letter,
					'captured_data' => array(
						'array'          => $iterator_json,
						'is_valid_array' => $is_valid_array,
					),
				);

				// Insert task into database.
				$tasks_db->update( $task );

				// Start the count from 1.
				$count = 1;

				// Get the remaining steps.
				$remain_route_actions = array_slice( $route_actions, $key );

				$iterator_task = array(
					'task_id'   => $task_history_id,
					'task_data' => array(),
				);

				// Loop through the array items.
				foreach ( $iterator_array as $index => $iteration_array ) {
					$response_array = array();

					$response_array['array_item_number'] = $count;

					if ( is_numeric( $index ) ) {
						$index = 0;
					}

					if ( is_array( $iteration_array ) ) {
						foreach ( $iteration_array as $array_key => $array_item ) {

							if ( is_array( $array_item ) ) {
								if ( $simple_response ) {
									$response_array = flowmattic_recursive_array( $response_array, $array_key, $array_item );
								} else {
									$response_array[ $array_key ] = wp_json_encode( $array_item );
								}
							} else {
								$response_array[ $array_key ] = $array_item;
							}
						}
					} else {
						$response_array[ $index ] = $iteration_array;
					}

					$response_data[ 'route' . $route_letter . '.iterator' . $cur_step_number ] = $response_array;

					$next_step_id     = isset( $route_actions[ $key + 1 ] ) ? $route_actions[ $key + 1 ]['stepID'] : false;
					$router_id        = $step['stepID'];
					$next_step_number = $cur_step_number + 1;

					// Execute the iterator.
					foreach ( $remain_route_actions as $iterator_key => $iterator_step ) {
						$next_step_id = isset( $remain_route_actions[ $iterator_key + 1 ] ) ? $remain_route_actions[ $iterator_key + 1 ]['stepID'] : false;

						$iterator_tasks_item = $this->execute_router( $workflow_id, array( $iterator_step ), $response_data, $iterator_task, $next_step_number, $router_id );
						$iterator_tasks[]    = $iterator_tasks_item;

						$iterator_response = $iterator_tasks_item['task_data'][0]['captured_data'];
						$response_data[ 'route' . $route_letter . '.' . $iterator_step['application'] . $next_step_number ] = $iterator_response;

						$next_step_number = $next_step_number + 1; // @codingStandardsIgnoreLine Incrementing the step number.
					}

					++$count;
				}

				if ( ! empty( $iterator_tasks ) ) {
					foreach ( $iterator_tasks as $i_key => $iterator_task ) {
						$task['task_data'][] = $iterator_task['task_data'][0];
					}

					// Insert task into database.
					$tasks_db->update( $iterator_task );
				}

				$next_step_flag = false;
			}

			if ( ! $next_step_flag ) {
				break;
			}

			// Check if it is a custom app.
			if ( preg_match( '/^(custom_app|app_)/', $application ) ) {
				$app_class = 'FlowMattic_Custom_User_App';
			} else {
				$app_class = ucwords( str_replace( array( '-', '_' ), ' ', $application ) );
				$app_class = str_replace( ' ', '_', $app_class );
				$app_class = 'FlowMattic_' . $app_class;
			}

			if ( class_exists( $app_class ) ) {
				// Delete the workflow captured data.
				unset( $step['capturedData'] );

				$app_class_instance = new $app_class();

				// Check if app has method to validate workflow.
				if ( method_exists( $app_class_instance, 'validate_workflow_step' ) ) {
					$is_workflow_executable   = true;
					$workflow_step_validation = $app_class_instance->validate_workflow_step( $workflow_id, $step, $response_data );

					if ( is_object( $workflow_step_validation ) ) {
						if ( isset( $workflow_step_validation->flag ) && 'continue' === $workflow_step_validation->flag ) {
							$is_workflow_executable = true;
						} else {
							$is_workflow_executable = false;
						}

						$app_response = $workflow_step_validation;
					} elseif ( ! $workflow_step_validation ) {
						$is_workflow_executable = false;
						$app_response           = array(
							'status'  => 'error',
							'message' => esc_html__( 'Workflow step validation not passed', 'flowmattic' ),
						);
					}

					if ( ! $is_workflow_executable ) {

						$response_data[ 'route' . $route_letter . '.' . $application . $cur_step_number ] = $app_response;

						// Check if the request data method exists.
						$request_data = '';
						if ( method_exists( $app_class_instance, 'get_request_data' ) ) {
							$request_data = $app_class_instance->get_request_data();
						}

						$task['task_data'][] = array(
							'application'   => $application,
							'captured_data' => $app_response,
							'step_id'       => $step['stepID'],
							'route_letter'  => $route_letter,
							'request_data'  => is_array( $request_data ) ? wp_json_encode( $request_data ) : $request_data,
						);

						$next_step_flag = false;
						break;
					}
				}

				$continue_execution = true;

				// Check if conditional execution is turned ON.
				if ( isset( $step['conditional_execution'] ) && 'Yes' === $step['conditional_execution'] ) {
					$filter_conditions = ( isset( $step['filterConditions'] ) && ! empty( $step['filterConditions'] ) ) ? $step['filterConditions'] : array();
					if ( ! empty( $filter_conditions ) ) {
						if ( class_exists( 'FlowMattic_Filter' ) ) {
							$fields = array(
								'filter_conditions' => $filter_conditions,
							);

							$filter    = new FlowMattic_Filter();
							$condition = $filter->continue_if( $fields );
							$condition = json_decode( $condition );

							if ( 'continue' === $condition->flag ) {
								$continue_execution = true;
							} else {
								$continue_execution = false;
								$app_response       = array(
									'status'  => 'error',
									'message' => esc_html__( 'Conditions does not meet. This step will not be executed.', 'flowmattic' ),
								);
							}
						}
					}
				}

				if ( $continue_execution ) {
					// If application has both - trigger and action, run the action step.
					if ( method_exists( $app_class_instance, 'run_action_step' ) ) {
						// Make sure actionAppArgs is array and not JSON.
						if ( isset( $step['actionAppArgs'] ) && ! is_array( $step['actionAppArgs'] ) ) {
							$step['actionAppArgs'] = json_decode( $step['actionAppArgs'], true );
						}

						$app_response = $app_class_instance->run_action_step( $workflow_id, $step, $response_data );
						$app_response = json_decode( $app_response, true );
					}
				}

				$response_data[ 'route' . $route_letter . '.' . $application . $cur_step_number ] = $app_response;

				// Check if the request data method exists.
				$request_data = '';
				if ( method_exists( $app_class_instance, 'get_request_data' ) ) {
					$request_data = $app_class_instance->get_request_data();
				}

				$current_task_data = array(
					array(
						'application'   => $application,
						'captured_data' => $app_response,
						'step_id'       => $step['stepID'],
						'route_letter'  => $route_letter,
						'request_data'  => is_array( $request_data ) ? wp_json_encode( $request_data ) : $request_data,
					),
				);

				if ( '' !== $next_route_id && $next_route_id === $step['stepID'] ) {
					// Get the delay step ID.
					$prev_route_id = isset( $route_actions[ $key - 1 ] ) ? $route_actions[ $key - 1 ]['stepID'] : false;

					// Assign the task data.
					$all_task_data = $task['task_data'];

					// Get the delay step key.
					$router_key = array_search( $prev_route_id, array_column( $all_task_data, 'step_id' ), true );

					// Insert the current task data after delay. +2 requies as +1 for delay task, as router index starting with 0, +1 for the current task.
					array_splice( $all_task_data, $router_key + 2, 0, $current_task_data );

					// Re-assign the task data back to task.
					$task['task_data'] = $all_task_data;
				} else {
					$task['task_data'][] = $current_task_data[0];
				}
			}

			++$cur_step_number;

			if ( ! $next_step_flag ) {
				break;
			}
		}

		return $task;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event  = isset( $event_data['event'] ) ? $event_data['event'] : '';
		$fields = $event_data['fields'];
		$arrays = json_decode( $fields['routerArray'], true );
		$count  = 0;

		$response_array = array();

		foreach ( $arrays as $array ) {
			if ( 1 === $count ) {
				break;
			}

			foreach ( $array as $key => $array_item ) {
				if ( is_array( $array_item ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $array_item );
				} else {
					$response_array[ $key ] = $array_item;
				}
			}

			++$count;
		}

		return wp_json_encode( $response_array );
	}
}

new FlowMattic_Router();
