<?php
/**
 * Application Name: FlowMattic PHP Array
 * Description: Add PHP Array module to FlowMattic.
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
 * PHP Array module integration class.
 *
 * @since 1.1
 */
class FlowMattic_Php_Array {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 4.3.0
	 * @var array|string
	 */
	public $request_body;

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
			'php_array',
			array(
				'name'         => esc_attr__( 'PHP Array by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/php-array/icon.svg',
				'instructions' => 'Perform array functions on the array data in your workflow',
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
	 * @since 1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-php-array', FLOWMATTIC_PLUGIN_URL . 'inc/apps/php-array/view-php-array.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'get_value_by_index'    => array(
				'title'       => esc_attr__( 'Get Value by Index', 'flowmattic' ),
				'description' => esc_attr__( 'Get value of a specified index of an array', 'flowmattic' ),
			),
			'get_array_count'       => array(
				'title'       => esc_attr__( 'Get Array Count', 'flowmattic' ),
				'description' => esc_attr__( 'Get count of total number of items in array', 'flowmattic' ),
			),
			'array_search'          => array(
				'title'       => esc_attr__( 'Array Search', 'flowmattic' ),
				'description' => esc_attr__( 'Searches an array for a given value and returns the key', 'flowmattic' ),
			),
			'convert_list_to_array' => array(
				'title'       => esc_attr__( 'Convert List to Array', 'flowmattic' ),
				'description' => esc_attr__( 'Converts a list to an array', 'flowmattic' ),
			),
			'convert_array_to_list' => array(
				'title'       => esc_attr__( 'Convert Array to List', 'flowmattic' ),
				'description' => esc_attr__( 'Converts an array to a list', 'flowmattic' ),
			),
			'insert_value_at_index' => array(
				'title'       => esc_attr__( 'Insert Value at Index', 'flowmattic' ),
				'description' => esc_attr__( 'Inserts a value at a specified index in an array', 'flowmattic' ),
			),
			'new_line_to_array'     => array(
				'title'       => esc_attr__( 'New Line to Array', 'flowmattic' ),
				'description' => esc_attr__( 'Converts a new line separated list to an array', 'flowmattic' ),
			),
			'extract_json'          => array(
				'title'       => esc_attr__( 'Extract JSON Data', 'flowmattic' ),
				'description' => esc_attr__( 'Extracts JSON data to individual items', 'flowmattic' ),
			),
			'itemize_array'         => array(
				'title'       => esc_attr__( 'Itemize Array', 'flowmattic' ),
				'description' => esc_attr__( 'Converts an array to individual items', 'flowmattic' ),
			),
			'create_itemized_array' => array(
				'title'       => esc_attr__( 'Create Line Items', 'flowmattic' ),
				'description' => esc_attr__( 'Creates a list of line items from multiple fields', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.1
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action    = $step['action'];
		$fields    = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$json_data = array();

		if ( isset( $fields['json_array'] ) && ! empty( $fields['json_array'] ) ) {
			$json_data = is_array( $fields['json_array'] ) ? $fields['json_array'] : ( is_array( json_decode( $fields['json_array'], true ) ) ? json_decode( $fields['json_array'], true ) : stripslashes( $fields['json_array'] ) );
		}

		$json_array     = is_array( $json_data ) ? $json_data : json_decode( $json_data, true );
		$response_array = array();

		// Set the request body.
		$this->request_body = array(
			'json_array' => wp_json_encode( $json_array ),
		);

		if ( ! is_array( $json_array ) ) {
			$response = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid JSON format.', 'flowmattic' ),
				)
			);
		}

		switch ( $action ) {
			case 'get_value_by_index':
				$this->request_body['array_index'] = $fields['array_index'];

				$array_index = explode( ',', $fields['array_index'] );
				$array_index = array_map( 'trim', $array_index );
				foreach ( $array_index as $key => $index ) {
					$index        = trim( $index );
					$array_value  = flowmattic_get_value_by_index( $json_array, $index );
					$simple_array = ( $array_value ) ? $array_value : array();

					if ( empty( $simple_array ) ) {
						$simple_array = array(
							'status'   => 'error',
							'response' => esc_html__( 'Array index not exits', 'flowmattic' ),
						);
					}

					if ( 1 === count( $array_index ) ) {
						if ( is_array( $simple_array ) ) {
							foreach ( $simple_array as $s_key => $s_value ) {
								$response_array[ $s_key ] = is_array( $s_value ) ? wp_json_encode( $s_value ) : $s_value;
							}
						} else {
							$response_array[ $index ] = $simple_array;
						}
					} else {
						$response_array[ $index ] = ( is_array( $simple_array ) ) ? wp_json_encode( $simple_array ) : $simple_array;
					}
				}

				$response = wp_json_encode( $response_array );

				break;

			case 'get_array_count':
				$count    = count( $json_array );
				$response = wp_json_encode(
					array(
						'status' => 'success',
						'count'  => $count,
					)
				);

				break;

			case 'array_search':
				$array_search_term = $fields['array_search_term'];

				$this->request_body['array_search_term'] = $array_search_term;

				$key = array_search( $array_search_term, $json_array, true );
				$key = ( 0 === $key ) ? '0' : $key;

				if ( ! $key ) {
					foreach ( $json_array as $k => $array_item ) {
						if ( is_array( $array_item ) ) {
							if ( 1 !== count( $array_item ) ) {
								foreach ( $array_item as $ar_index => $ar_item ) {
									if ( is_array( $ar_item ) ) {
										$key_2 = array_search( $array_search_term, $ar_item, true );
										$key_2 = ( 0 === $key_2 ) ? '0' : $key_2;

										if ( $key_2 ) {
											$key = ( 0 === $k ) ? '0/' . $ar_index . '/' . $key_2 : $k . '/' . $ar_index . '/' . $key_2;
											break;
										}
									} else {
										$key_2 = ( $array_search_term === $ar_item ) ? $ar_index : false;

										if ( $key_2 ) {
											$key = $k;
											break;
										}
									}
								}
							} else {
								$key_2 = array_search( $array_search_term, $array_item, true );
								$key_2 = ( 0 === $key_2 ) ? '0' : $key_2;
								if ( $key_2 ) {
									$key = ( 0 === $k ) ? '0' : $k;
									break;
								}
							}
						}
					}
				}

				if ( false !== $key ) {
					$response = wp_json_encode(
						array(
							'status' => 'success',
							'key'    => $key,
						)
					);
				} else {
					$response = wp_json_encode(
						array(
							'status'   => 'error',
							'response' => esc_html__( 'Nothing found for the given value', 'flowmattic' ),
						)
					);
				}

				break;

			case 'convert_list_to_array':
				$json_array = $fields['list_of_values'];
				$separator  = ( isset( $fields['separator'] ) && ! empty( $fields['separator'] ) ) ? $fields['separator'] : ',';
				$list_array = explode( $separator, $json_array );
				$list_array = array_map( 'trim', $list_array );
				$response   = wp_json_encode(
					array(
						'status' => 'success',
						'array'  => wp_json_encode( $list_array ),
					)
				);

				$this->request_body['list_of_values'] = $json_array;
				$this->request_body['separator']      = $separator;

				break;

			case 'convert_array_to_list':
				$separator  = ( isset( $fields['separator'] ) && ! empty( $fields['separator'] ) ) ? $fields['separator'] : ',';
				$list_array = $json_array;
				$list_array = implode( $separator, $list_array );
				$response   = wp_json_encode(
					array(
						'status' => 'success',
						'list'   => $list_array,
					)
				);

				$this->request_body['separator'] = $separator;

				break;

			case 'insert_value_at_index':
				$index = $fields['array_index'];
				$value = stripslashes( $fields['value_to_insert'] );

				$this->request_body['array_index']     = $index;
				$this->request_body['value_to_insert'] = $value;

				// See if value is a json.
				if ( is_array( json_decode( $value, true ) ) ) {
					$value = json_decode( $value, true );
				}

				if ( isset( $json_array[ $index ] ) ) {
					$json_array = array_merge(
						array_slice( $json_array, 0, $index, true ),
						array( $value ),
						array_slice( $json_array, $index, count( $json_array ), true )
					);

					$response = wp_json_encode(
						array(
							'status'        => 'success',
							'updated_array' => wp_json_encode( $json_array ),
						)
					);
				} else {
					$response = wp_json_encode(
						array(
							'status'   => 'error',
							'response' => esc_html__( 'Index not found', 'flowmattic' ),
						)
					);
				}

				break;

			case 'new_line_to_array':
				$json_array = $fields['new_line_separated_list'];
				$list_array = explode( "\n", $json_array );
				$list_array = array_map( 'trim', $list_array );

				$this->request_body['new_line_separated_list'] = $fields['new_line_separated_list'];

				// Make the list array unique.
				$list_array = array_unique( $list_array );

				// Initiate the new array for Iterator.
				$new_array = array();

				// Loop through the list array and add to new array.
				foreach ( $list_array as $key => $value ) {
					$new_array[] = array(
						'list_item' => $value,
					);
				}

				// Encode the response.
				$response = wp_json_encode(
					array(
						'status'         => 'success',
						'original_array' => wp_json_encode( $list_array ),
						'iterator_array' => wp_json_encode( $new_array ),
					)
				);

				break;

			case 'extract_json':
				$json_array = $fields['json_data'];

				if ( ! is_array( $json_array ) ) {
					$json_array = is_array( json_decode( $json_array, true ) ) ? json_decode( $json_array, true ) : json_decode( stripslashes( $json_array ), true );
				}

				$this->request_body['json_data'] = $fields['json_data'];

				// Check if the json is valid.
				if ( ! is_array( $json_array ) ) {
					$response = wp_json_encode(
						array(
							'status'  => 'error',
							'message' => esc_html__( 'Invalid JSON format.', 'flowmattic' ),
						)
					);
				} else {
					// Simplify the array.
					$simple_array = array();

					// Add status to the response.
					$simple_array['status'] = 'success';

					// Loop through the array and add to simple array.
					foreach ( $json_array as $key => $value ) {
						// Check if the value is JSON, then decode it.
						if ( ! is_array( $value ) && is_array( json_decode( $value, true ) ) ) {
							$value = json_decode( $value, true );
						}

						if ( is_array( $value ) ) {
							$simple_array = flowmattic_recursive_array( $simple_array, $key, $value );
						} else {
							$simple_array[ $key ] = $value;
						}
					}

					// Encode the response.
					$response = wp_json_encode( $simple_array );
				}

				break;

			case 'itemize_array':
				$json_array = $fields['json_data'];
				$json_array = is_array( json_decode( $json_array, true ) ) ? json_decode( $json_array, true ) : json_decode( stripslashes( $json_array ), true );

				$this->request_body['json_data'] = $fields['json_data'];

				// Check if the json is valid.
				if ( ! is_array( $json_array ) ) {
					$response = wp_json_encode(
						array(
							'status'  => 'error',
							'message' => esc_html__( 'Invalid JSON format.', 'flowmattic' ),
						)
					);
				} else {
					// Simplify the array.
					$simple_array = array();

					// Add status to the response.
					$simple_array['status'] = 'success';

					// Loop through the array and add to simple array.
					foreach ( $json_array as $key => $value ) {
						// Check if the value is JSON, then decode it.
						if ( ! is_array( $value ) && is_array( json_decode( $value, true ) ) ) {
							$value = json_decode( $value, true );
						}

						if ( is_array( $value ) ) {
							$simple_array['items'][] = array(
								'item' => wp_json_encode( $value ),
							);
						} else {
							$simple_array['items'][] = array(
								'item' => $value,
							);
						}
					}

					$simple_array['items'] = wp_json_encode( $simple_array['items'] );

					// Encode the response.
					$response = wp_json_encode( $simple_array );
				}

				break;

			case 'create_itemized_array':
				$line_items_data = $fields['line_items'];

				$this->request_body['line_items'] = $fields['line_items'];

				$data = $line_items_data;
				if ( null === $data || ! is_array( $data ) ) {
					return json_encode( array() );
				}

				// Split each field by commas and trim whitespace.
				$split_data = array();
				foreach ( $data as $key => $value ) {
					$split_data[ $key ] = array_map( 'trim', explode( ',', $value ) );
				}

				// Find the maximum length among all arrays.
				$max_length = 0;
				foreach ( $split_data as $arr ) {
					$arr_count = count( $arr );
					if ( $max_length < $arr_count ) {
						$max_length = $arr_count;
					}
				}

				// Build the resulting array of objects.
				$result = array();
				for ( $i = 0; $i < $max_length; $i++ ) {
					$item = array();
					foreach ( $split_data as $key => $arr ) {
						$item[ $key ] = isset( $arr[ $i ] ) ? $arr[ $i ] : null;

					}
					// Add item index.
					$item['fm_item_index']          = $i + 1;
					$result[ 'item_' . ( $i + 1 ) ] = $item;
				}

				$response = wp_json_encode(
					array(
						'line_items' => $result,
					),
				);

				break;
		}

		return $response;
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
		$event          = $event_data['event'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 4.3.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Php_Array();
