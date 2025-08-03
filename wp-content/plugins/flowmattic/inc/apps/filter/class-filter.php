<?php
/**
 * Application Name: FlowMattic Filters
 * Description: Add FlowMattic Filters integration to FlowMattic.
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

class FlowMattic_Filter {
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
		// Enqueue custom view for filter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'filter',
			array(
				'name'         => esc_attr__( 'Filter by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/filter/icon.svg',
				'instructions' => esc_attr__( 'Connect your Filter account. Your credentials are stored securely in your site.', 'flowmattic' ),
				'connect_note' => esc_attr__( 'Your Filter account is already connected. To re-connect Filter, click the button above. Your credentials are stored securely in your site.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'type'         => 'action',
				'base'         => 'core',
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
		wp_enqueue_script( 'flowmattic-app-view-filter', FLOWMATTIC_PLUGIN_URL . 'inc/apps/filter/view-filter.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Validate if the workflow should execute or not.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id   Workflow ID for the workflow being executed.
	 * @param array  $workflow_step Current step in the workflow being executed.
	 * @param array  $capture_data  Data received in the webhook.
	 * @return bool  Whether the workflow can be executed or not.
	 */
	public function validate_workflow_step( $workflow_id, $workflow_step, $capture_data ) {
		$filter_results = $this->run_action_step( $workflow_id, $workflow_step, $capture_data );

		return $filter_results ? json_decode( $filter_results ) : true;
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
		$step   = (array) $step;
		$action = $step['action'];

		// CS.
		$capture_data;

		if ( ! isset( $step['filterConditions'] ) ) {
			return;
		}

		$fields                      = array();
		$fields['filter_conditions'] = $step['filterConditions'];

		// Set the request body.
		$this->request_body = $fields;

		switch ( $action ) {
			case 'continue_if':
				$response = $this->continue_if( $fields );

				break;

			case 'exit_if':
				$response = $this->exit_if( $fields );

				break;
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
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'continue_if' => array(
				'title' => esc_attr__( 'Continue If Condition Meet', 'flowmattic' ),
				'description' => esc_attr__( 'Continue the workflow if the conditions meet.', 'flowmattic' ),
			),
			'exit_if'     => array(
				'title' => esc_attr__( 'Exit If Condition Meet', 'flowmattic' ),
				'description' => esc_attr__( 'Exit the workflow if the conditions meet.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Continue if the conditions meet.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Options to check condition against.
	 * @return array
	 */
	public function continue_if( $fields ) {
		// Set to continue.
		$continue = false;

		// Get the conditions.
		$filter_conditions = $fields['filter_conditions'];

		// If the conditions contain filterConditions, use that.
		if ( isset( $filter_conditions['filterConditions'] ) ) {
			$filter_conditions = $filter_conditions['filterConditions'];
		}

		// If filter conditions is not an array, try json decode.
		if ( ! is_array( $filter_conditions ) ) {
			$filter_conditions = json_decode( $filter_conditions, true );
		}

		foreach ( $filter_conditions as $key => $conditions ) {
			$meet_conditions   = false;
			$condition_matches = array();

			if ( empty( $conditions ) ) {
				continue;
			}

			$condition_check_type = 1 === count( $conditions ) ? 'OR' : 'AND';

			foreach ( $conditions as $index => $condition ) {
				if ( ! is_array( $condition ) ) {
					$condition = str_replace( array( "\r", "\n" ), '', $condition );
					$condition = ( is_array( json_decode( $condition, true ) ) ) ? json_decode( $condition, true ) : $condition;
				}

				$filter_key       = $condition['key'];
				$filter_condition = $condition['condition'];
				$filter_value     = $condition['value'];

				if ( false !== strpos( $filter_key, 'JSON:' ) ) {
					$filter_key_text = str_replace( 'JSON:', '', $filter_key );
					$filter_key      = base64_decode( $filter_key_text ); // @codingStandardsIgnoreLine
				}

				if ( false !== strpos( $filter_value, 'JSON:' ) ) {
					$filter_value_text = str_replace( 'JSON:', '', $filter_value );
					$filter_value      = base64_decode( $filter_value_text ); // @codingStandardsIgnoreLine
				}

				// Convert to lowercase, including array values.
				$filter_key       = is_array( $filter_key ) ? array_map( 'strtolower', $filter_key ) : strtolower( $filter_key );
				$filter_value     = is_array( $filter_value ) ? array_map( 'strtolower', $filter_value ) : strtolower( $filter_value );

				// Decode the filter value back to normal from urlencoded.
				if ( is_array( $filter_value ) ) {
					$filter_value = array_map( 'urldecode', $filter_value );
				} else {
					$filter_value = urldecode( $filter_value );
				}

				// Encode the filter key back to normal from urlencoded.
				$filter_key = urldecode( $filter_key );

				switch ( $filter_condition ) {
					case 'contain_text':
						if ( false !== strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_contain_text':
						if ( false === strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'exactly_match':
						if ( $filter_key === $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_exactly_match':
						if ( $filter_key !== $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_in':
						$value_array = explode( ',', $filter_value );
						$value_array = array_map( 'trim', $value_array );

						foreach ( $value_array as $key => $value ) {
							if ( false !== strpos( $filter_value, $filter_key ) ) {
								$meet_conditions = true;
								break;
							} else {
								$meet_conditions = false;
							}
						}
						break;

					case 'is_not_in':
						$value_array = explode( ',', $filter_value );
						$value_array = array_map( 'trim', $value_array );

						foreach ( $value_array as $key => $value ) {
							if ( false === strpos( $filter_value, $filter_key ) ) {
								$meet_conditions = true;
								break;
							} else {
								$meet_conditions = false;
							}
						}
						break;

					case 'starts_with':
						if ( 0 === strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_start_with':
						if ( 0 !== strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'ends_with':
						$count = strlen( $filter_value );
						if ( substr( $filter_key, -$count ) === $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_end_with':
						$count = strlen( $filter_value );
						if ( substr( $filter_key, -$count ) !== $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'greater_than':
						if ( (int) $filter_key > (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'less_than':
						if ( (int) $filter_key < (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'equal_to':
						if ( (int) $filter_key === (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'after_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date > $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'before_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date < $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'equal_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date === $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'is_true':
						if ( ( is_bool( $filter_key ) && $filter_key ) || 'true' === $filter_key || '1' === $filter_key || 1 === $filter_key ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_false':
						if ( ( is_bool( $filter_key ) && ! $filter_key ) || 'false' === $filter_key || '' === $filter_key || '0' === $filter_key || 0 === $filter_key ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}

						break;

					case 'exists':
						$key_exists = '' === $filter_key ? false : true;
						if ( $key_exists ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_exists':
						$key_exists = '' === $filter_key ? false : true;
						if ( ! $key_exists ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_empty':
						$is_empty = ( empty( $filter_key ) || '[]' === $filter_key );
						if ( $is_empty ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_not_empty':
						$is_empty = ( empty( $filter_key ) || '[]' === $filter_key );
						if ( ! $is_empty ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_blank':
						$blank = '' === $filter_key ? true : false;
						if ( $blank ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_not_blank':
						$blank = '' === $filter_key ? true : false;
						if ( ! $blank ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_email_valid':
						$valid_email = is_email( $filter_key );
						if ( $valid_email ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_valid_json':
						$valid_json = is_array( json_decode( $filter_key, true ) ) || is_array( json_decode( stripslashes( $filter_key ), true ) );
						if ( $valid_json ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;
				}

				if ( $meet_conditions ) {
					$condition_matches[ $key ] = true;

					if ( 'OR' === $condition_check_type ) {
						$continue = true;
						break 2;
					}
				} else {
					$condition_matches[ $key ] = false;

					if ( 'AND' === $condition_check_type ) {
						$continue = false;
						break 2;
					}
				}
			}

			foreach ( $condition_matches as $index => $match ) {
				if ( $match ) {
					$continue = true;
				}
			}
		}

		if ( $continue ) {
			// If all the conditions meet, let the workflow run.
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Success', 'flowmattic' ),
					'message' => esc_attr__( 'Conditions met! Execution will be continued.', 'flowmattic' ),
					'flag'    => 'continue',
				)
			);
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Success', 'flowmattic' ),
					'message' => esc_attr__( 'Conditions does not meet. Execution will stop.', 'flowmattic' ),
					'flag'    => 'abort',
				)
			);
		}
	}

	/**
	 * Exit if the conditions meet.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Options to check condition against.
	 * @return array
	 */
	public function exit_if( $fields ) {

		// Set to continue.
		$continue = true;

		// Get the conditions.
		$filter_conditions = $fields['filter_conditions'];

		foreach ( $filter_conditions as $key => $conditions ) {
			$meet_conditions   = false;
			$condition_matches = array();

			foreach ( $conditions as $index => $condition ) {
				if ( ! is_array( $condition ) ) {
					$condition = str_replace( array( "\r", "\n" ), '', $condition );
					$condition = ( is_array( json_decode( $condition, true ) ) ) ? json_decode( $condition, true ) : $condition;
				}

				$filter_key       = $condition['key'];
				$filter_condition = $condition['condition'];
				$filter_value     = $condition['value'];

				if ( false !== strpos( $filter_key, 'JSON:' ) ) {
					$filter_key_text = str_replace( 'JSON:', '', $filter_key );
					$filter_key      = base64_decode( $filter_key_text ); // @codingStandardsIgnoreLine
				}

				if ( false !== strpos( $filter_value, 'JSON:' ) ) {
					$filter_value_text = str_replace( 'JSON:', '', $filter_value );
					$filter_value      = base64_decode( $filter_value_text ); // @codingStandardsIgnoreLine
				}

				// Convert to lowercase, including array values.
				$filter_key       = is_array( $filter_key ) ? array_map( 'strtolower', $filter_key ) : strtolower( $filter_key );
				$filter_value     = is_array( $filter_value ) ? array_map( 'strtolower', $filter_value ) : strtolower( $filter_value );

				// Decode the filter value back to normal from urlencoded.
				if ( is_array( $filter_value ) ) {
					$filter_value = array_map( 'urldecode', $filter_value );
				} else {
					$filter_value = urldecode( $filter_value );
				}

				// Encode the filter key back to normal from urlencoded.
				$filter_key = urldecode( $filter_key );

				switch ( $filter_condition ) {
					case 'contain_text':
						if ( false !== strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_contain_text':
						if ( false === strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'exactly_match':
						if ( $filter_key === $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_exactly_match':
						if ( $filter_key !== $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_in':
						$value_array = explode( ',', $filter_value );
						$value_array = array_map( 'trim', $value_array );

						foreach ( $value_array as $key => $value ) {
							if ( false !== strpos( $filter_value, $filter_key ) ) {
								$meet_conditions = true;
								break;
							} else {
								$meet_conditions = false;
							}
						}
						break;

					case 'is_not_in':
						$value_array = explode( ',', $filter_value );
						$value_array = array_map( 'trim', $value_array );

						foreach ( $value_array as $key => $value ) {
							if ( false === strpos( $filter_value, $filter_key ) ) {
								$meet_conditions = true;
								break;
							} else {
								$meet_conditions = false;
							}
						}
						break;

					case 'starts_with':
						if ( 0 === strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_start_with':
						if ( 0 !== strpos( $filter_key, $filter_value ) ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'ends_with':
						$count = strlen( $filter_value );
						if ( substr( $filter_key, -$count ) === $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_end_with':
						$count = strlen( $filter_value );
						if ( substr( $filter_key, -$count ) !== $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'greater_than':
						if ( (int) $filter_key > (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'less_than':
						if ( (int) $filter_key < (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'equal_to':
						if ( (int) $filter_key === (int) $filter_value ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'after_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date > $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'before_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date < $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'equal_date':
						try {
							$value_date = new DateTime( $filter_value );
							$key_date   = new DateTime( $filter_key );

							if ( $key_date === $value_date ) {
								$meet_conditions = true;
							} else {
								$meet_conditions = false;
							}
						} catch ( Exception $e ) {
							$error_message   = $e->getMessage();
							$meet_conditions = false;
						}

						break;

					case 'is_true':
						if ( ( is_bool( $filter_key ) && $filter_key ) || 'true' === $filter_key || '1' === $filter_key || 1 === $filter_key ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_false':
						if ( ( is_bool( $filter_key ) && ! $filter_key ) || 'false' === $filter_key || '' === $filter_key || '0' === $filter_key || 0 === $filter_key ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'exists':
						$key_exists = '' === $filter_key ? false : true;
						if ( $key_exists ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'does_not_exists':
						$key_exists = '' === $filter_key ? false : true;
						if ( ! $key_exists ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_empty':
						$is_empty = ( empty( $filter_key ) || '[]' === $filter_key );
						if ( $is_empty ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_not_empty':
						$is_empty = ( empty( $filter_key ) || '[]' === $filter_key );
						if ( ! $is_empty ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_blank':
						$blank = '' === $filter_key ? true : false;
						if ( $blank ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_not_blank':
						$blank = '' === $filter_key ? true : false;
						if ( ! $blank ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_email_valid':
						$valid_email = is_email( $filter_key );
						if ( $valid_email ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;

					case 'is_valid_json':
						$valid_json = is_array( json_decode( $filter_key, true ) ) || is_array( json_decode( stripslashes( $filter_key ), true ) );
						if ( $valid_json ) {
							$meet_conditions = true;
						} else {
							$meet_conditions = false;
						}
						break;
				}

				if ( $meet_conditions ) {
					$condition_matches[ $key ] = true;
				} else {
					$condition_matches[ $key ] = false;
					break;
				}
			}

			foreach ( $condition_matches as $index => $match ) {
				if ( $match ) {
					$continue = false;
				}
			}
		}

		if ( $continue ) {
			// If all the conditions meet, let the workflow run.
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Success', 'flowmattic' ),
					'message' => esc_attr__( 'Conditions does not meet. Execution will be continued.', 'flowmattic' ),
					'flag'    => 'continue',
				)
			);
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Success', 'flowmattic' ),
					'message' => esc_attr__( 'Conditions met!  Execution will stop.', 'flowmattic' ),
					'flag'    => 'abort',
				)
			);
		}
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
		$event          = isset( $event_data['event'] ) ? $event_data['event'] : '';
		$workflow_id    = isset( $event_data['workflow_id'] ) ? $event_data['workflow_id'] : '';
		$step_ids       = isset( $event_data['stepIDs'] ) ? $event_data['stepIDs'] : '';
		$response_array = array();
		$fields         = $event_data['fields'];

		// Add conditions to fields array.
		$fields['filter_conditions'] = isset( $event_data['settings']['filterConditions'] ) ? $event_data['settings']['filterConditions'] : '';

		switch ( $event ) {
			case 'continue_if':
				$filter_results = $this->continue_if( $fields );

				break;

			case 'exit_if':
				$filter_results = $this->exit_if( $fields );

				break;

		}

		return $filter_results;
	}
}

// Initialize the integration.
new FlowMattic_Filter();
