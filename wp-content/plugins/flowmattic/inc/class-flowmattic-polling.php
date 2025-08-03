<?php
/**
 * Application Name: FlowMattic API Polling
 * Description: Add FlowMattic API Polling integration to FlowMattic.
 * Version: 4.1.0
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
 * FlowMattic API Polling.
 *
 * @since 4.1.0
 */
class FlowMattic_Polling {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return void
	 */
	public function __construct() {
		// Add ajax action for API Polling test.
		add_action( 'wp_ajax_flowmattic_poll_api', array( $this, 'ajax_poll_api' ) );

		// Run the scheduled polling.
		add_action( 'flowmattic_workflow_polling_cron', array( $this, 'poll_scheduled_workflow' ), 10, 2 );
	}

	/**
	 * Run the workflow.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param String $workflow_id Workflow ID.
	 * @param Array  $response    Response.
	 * @return void
	 */
	public function run_workflow( $workflow_id, $response ) {
		// Run the workflow.
		$flowmattic_workflow = new FlowMattic_Workflow();
		$flowmattic_workflow->run( $workflow_id, $response );
	}

	/**
	 * Poll API.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_poll_api() {
		// Check if nonce is valid.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the workflow ID.
		$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

		// Get the application.
		$application = isset( $_POST['application'] ) ? sanitize_text_field( wp_unslash( $_POST['application'] ) ) : '';

		// Get the app action.
		$app_action = isset( $_POST['appAction'] ) ? sanitize_text_field( wp_unslash( $_POST['appAction'] ) ) : '';

		// Get the options.
		$options = isset( $_POST['options'] ) ? wp_unslash( $_POST['options'] ) : array();

		// Set default response.
		$response = array(
			'success' => true,
			'message' => esc_html__( 'Polling successful', 'flowmattic' ),
		);

		// Get the workflow.
		$args = array(
			'workflow_id' => $workflow_id,
		);

		$workflow_data     = array();
		$workflow          = wp_flowmattic()->workflows_db->get( $args );
		$workflow_settings = json_decode( $workflow->workflow_settings );
		$steps             = $workflow->workflow_steps;
		$steps             = json_decode( $steps, true );

		// Loop through the steps, and get the trigger.
		foreach ( $steps as $key => $workflow_step ) {
			if ( 'trigger' === $workflow_step['type'] ) {
				$workflow_data = $workflow_step;
				break;
			}
		}

		// Merge the options with the workflow data.
		$workflow_data = array_merge( $workflow_data, $options );

		// Get the response.
		$polling_response = apply_filters( 'flowmattic_poll_api_' . $application, $response, $workflow_id, $workflow_data, $workflow_settings, true );

		// Get the API Polling Frequency.
		$api_polling_frequency = isset( $workflow_data['apiPollingFrequency'] ) ? (int) $workflow_data['apiPollingFrequency'] : 10;

		// Create the data for scheduling the polling.
		$schedule_polling = array(
			'workflow_id' => $workflow_id,
			'frequency'   => $this->get_readable_frequency( $api_polling_frequency ),
		);

		// Unschedule the previous workflow event, if trigger is changed, remove the existing scheduled event.
		foreach ( _get_cron_array() as $timestamp => $cron_event ) {
			if ( isset( $cron_event['flowmattic_workflow_polling_cron'] ) ) {
				foreach ( $cron_event['flowmattic_workflow_polling_cron'] as $key => $cron_settings ) {
					if ( $workflow_id === $cron_settings['args']['workflow_id'] ) {
						wp_unschedule_event( $timestamp, 'flowmattic_workflow_polling_cron', $cron_settings['args'] );
					}
				}
			}
		}

		$interval = $this->get_cron_interval( $api_polling_frequency );

		// Schedule the new workflow event with updated time. Start the event after 1 minute.
		$register_cron = wp_schedule_event( time() + 60, $interval, 'flowmattic_workflow_polling_cron', $schedule_polling, true );

		// If is WP_Error, try again, or return the error.
		if ( is_wp_error( $register_cron ) ) {
			// Try again after 2 minutes.
			$register_cron = wp_schedule_event( time() + 120, $interval, 'flowmattic_workflow_polling_cron', $schedule_polling, true );

			// If is WP_Error, return the error.
			if ( is_wp_error( $register_cron ) ) {
				$response = array(
					'status'  => 'error',
					'message' => $register_cron->get_error_message(),
				);
			}
		}

		// Return the response.
		wp_send_json( $polling_response );

		// Always exit.
		exit;
	}

	/**
	 * Poll the scheduled workflow.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param String $workflow_id Workflow ID.
	 * @param String $frequency   Readable frequency.
	 * @return Bool
	 */
	public function poll_scheduled_workflow( $workflow_id, $frequency ) {
		// Set default response.
		$response = array(
			'success'   => true,
			'message'   => esc_html__( 'Polling successful', 'flowmattic' ),
			'frequency' => $frequency,
		);

		// Get the workflow.
		$args = array(
			'workflow_id' => $workflow_id,
		);

		$workflow_data     = array();
		$workflow          = wp_flowmattic()->workflows_db->get( $args );
		$workflow_settings = json_decode( $workflow->workflow_settings );

		// Check if workflow status is ON. Else, stop!
		if ( 'on' !== $workflow_settings->status ) {
			return false;
		}

		// Get the workflow steps.
		$steps = $workflow->workflow_steps;
		$steps = json_decode( $steps, true );

		// Loop through the steps, and get the trigger.
		foreach ( $steps as $key => $workflow_step ) {
			if ( 'trigger' === $workflow_step['type'] ) {
				$workflow_data = $workflow_step;
				break;
			}
		}

		// Get the application.
		$application = $workflow_data['application'];

		// Get the response.
		$polling_response = apply_filters( 'flowmattic_poll_api_' . $application, $response, $workflow_id, $workflow_data, $workflow_settings, false );
	}

	/**
	 * Get readable frequency.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Int $frequency Integer frequency.
	 * @return String
	 */
	public function get_readable_frequency( $frequency ) {
		$frequencies = array(
			'1'    => esc_attr__( '1 Minute', 'flowmattic' ),
			'2'    => esc_attr__( '2 Minutes', 'flowmattic' ),
			'5'    => esc_attr__( '5 Minutes', 'flowmattic' ),
			'10'   => esc_attr__( '10 Minutes', 'flowmattic' ),
			'15'   => esc_attr__( '15 Minutes', 'flowmattic' ),
			'30'   => esc_attr__( '30 Minutes', 'flowmattic' ),
			'60'   => esc_attr__( '1 Hour', 'flowmattic' ),
			'120'  => esc_attr__( '2 Hours', 'flowmattic' ),
			'180'  => esc_attr__( '3 Hours', 'flowmattic' ),
			'360'  => esc_attr__( '6 Hours', 'flowmattic' ),
			'720'  => esc_attr__( '12 Hours', 'flowmattic' ),
			'1440' => esc_attr__( '1 Day', 'flowmattic' ),
		);

		return $frequencies[ $frequency ];
	}

	/**
	 * Get cron event schedule interval.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Int $frequency Integer frequency.
	 * @return String
	 */
	public function get_cron_interval( $frequency ) {
		$interval = 'flowmattic_every_' . $frequency . '_minutes';

		// If frequency is 60 minutes, set it as hourly.
		if ( 60 === $frequency ) {
			$interval = 'hourly';
		}

		// If frequency is 720 minutes, set it as twicedaily.
		if ( 720 === $frequency ) {
			$interval = 'twicedaily';
		}

		// If frequency is 1440 minutes, set it as daily.
		if ( 1440 === $frequency ) {
			$interval = 'daily';
		}

		return $interval;
	}

	/**
	 * Check if the stored data matches the response.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array  $workflow_settings Workflow settings.
	 * @param Array  $response          Response.
	 * @param String $needle            Needle.
	 * @return String
	 */
	public function compare_stored_data( $workflow_settings, $response, $needle = '' ) {
		// Get the stored data.
		$stored_response = isset( $workflow_settings->stored_response ) ? $workflow_settings->stored_response : '';

		// Decode the stored response.
		$stored_response_decoded = base64_decode( $stored_response ); // phpcs:ignore
		$stored_response_array   = json_decode( $stored_response_decoded, true );

		// If the response contains certain keys, remove them.
		$response = $this->remove_frequently_updated_keys_from_response( $response );

		// Set the data changed flag.
		$data_changed = false;

		// If the needle is provided, check if the response contains the needle.
		if ( '' !== $needle ) {
			// Check if both the arrays have the same needle.
			if ( isset( $stored_response_array[ $needle ] ) && isset( $response[ $needle ] ) ) {
				// Compare the stored data with the response.
				if ( $response[ $needle ] !== $stored_response_array[ $needle ] ) {
					$data_changed = true;
				}
			}
		} elseif ( is_array( $stored_response_array ) && is_array( $response ) ) {
			// Compare the stored data with the response.
			if ( ! empty( array_diff( $response, $stored_response_array ) ) ) {
				$data_changed = true;
			}
		}

		return $data_changed;
	}

	/**
	 * Update the stored data.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Int   $workflow_id       Workflow ID.
	 * @param Array $workflow_settings Workflow settings.
	 * @param Array $response          Response.
	 * @return void
	 */
	public function update_stored_data( $workflow_id, $workflow_settings, $response ) {
		// If the response contains certain keys, remove them.
		$response = $this->remove_frequently_updated_keys_from_response( $response );

		// Update the stored data.
		$workflow_settings['stored_response'] = base64_encode( wp_json_encode( $response ) ); // phpcs:ignore

		// Update the workflow settings.
		wp_flowmattic()->workflows_db->update_settings( $workflow_id, array( 'workflow_settings' => $workflow_settings ) );
	}

	/**
	 * Get the list of certain keys to be removed from the response.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array $response Response.
	 * @return Array
	 */
	public function remove_frequently_updated_keys_from_response( $response ) {
		$key_to_remove = array(
			'request_id',
			'channel_lastBuildDate',
			'channel_pubDate',
		);

		// Loop through the keys and remove them from the response.
		foreach ( $key_to_remove as $key ) {
			if ( isset( $response[ $key ] ) ) {
				unset( $response[ $key ] );
			}
		}

		return $response;
	}

	/**
	 * Get the stored data.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array  $workflow_settings  Workflow settings.
	 * @param Array  $request_body       Request body.
	 * @param Array  $processed_response Processed response.
	 * @param String $item_index         Item index.
	 * @param String $simple_response    Simple response.
	 * @param String $needle             Needle.
	 * @return Array
	 */
	public function get_records_to_process( $workflow_settings, $request_body, $processed_response, $item_index, $simple_response, $needle = '' ) {
		// Get the stored data.
		$stored_response = isset( $workflow_settings->stored_response ) ? $workflow_settings->stored_response : '';

		// If the stored response is empty, return the records to process.
		if ( empty( $stored_response ) ) {
			return $processed_response;
		}

		// Decode the stored response.
		$stored_response_decoded = base64_decode( $stored_response ); // phpcs:ignore
		$stored_response_array   = json_decode( $stored_response_decoded, true );

		$request_body = ( ! is_array( $request_body ) ) ? json_decode( $request_body, true ) : $request_body;

		$index_response = array();

		// If item index contains @, split the item index.
		if ( strpos( $item_index, '@' ) !== false ) {
			$item_index        = explode( '@', $item_index );
			$item_index_parent = $item_index[0];
			$item_index_child  = $item_index[1];

			// Check if request body contains the parent index.
			if ( isset( $request_body[ $item_index_parent ][ $item_index_child ] ) ) {
				$item_index                    = $item_index_parent . '_' . $item_index_child;
				$index_response[ $item_index ] = $request_body[ $item_index_parent ][ $item_index_child ];
				// Remove the indexed response from the request body.
				unset( $request_body[ $item_index_parent ][ $item_index_child ] );
			}
		} elseif ( '' !== $item_index ) {
			// If the item index is not empty, get the indexed response.
			if ( isset( $request_body[ $item_index ] ) ) {
				$index_response[ $item_index ] = $request_body[ $item_index ];

				// Remove the indexed response from the request body.
				unset( $request_body[ $item_index ] );
			}
		}

		$records_to_process = array();
		$response_data      = $request_body;

		$response_array = ( ! empty( $index_response ) ) ? $index_response : $response_data;

		// If the response array is not empty, process the response.
		if ( ! empty( $response_array ) ) {
			// Loop through the request body and get the records to process.
			foreach ( $response_array as $key => $value ) {
				$record_value = $value;

				foreach ( $record_value as $key2 => $value2 ) {
					$value_with_index = array(
						$item_index => $value2,
					);

					// Merge the response with request body.
					$processed_record = array_merge( $response_data, $value_with_index );

					// Process the response.
					$processed_record = $this->simple_response( $processed_record, $simple_response );

					// If the needle is provided, check if the response contains the needle.
					if ( '' !== $needle ) {
						// Check if both the arrays have the same needle.
						if ( isset( $stored_response_array[ $needle ] ) && isset( $processed_record[ $needle ] ) ) {
							// Compare the stored data with the response.
							if ( $processed_record[ $needle ] !== $stored_response_array[ $needle ] ) {
								$records_to_process[] = $processed_record;
							} else {
								break;
							}
						}
					} else {
						// If the stored response is not empty, compare the stored data with the response.
						if ( ! empty( $stored_response_decoded ) ) {
							// Check if the stored data matches the response.
							if ( ! empty( array_diff( $processed_record, $stored_response_array ) ) ) {
								$records_to_process[] = $processed_record;
							} else {
								break;
							}
						} else {
							$records_to_process[] = $processed_record;
						}
					}
				}
			}
		}

		// Get the workflow ID.
		$workflow_id = isset( $workflow_settings->workflow_id ) ? $workflow_settings->workflow_id : '';

		// Since the data is changed, update the stored data with first record.
		if ( isset( $records_to_process[0] ) ) {
			wp_flowmattic()->api_polling->update_stored_data( $workflow_id, (array) $workflow_settings, $records_to_process[0] );
		}

		// Flip the array to get the latest records first.
		$records_to_process = array_reverse( $records_to_process );

		return $records_to_process;
	}

	/**
	 * Simple response.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array  $response        Response.
	 * @param String $simple_response Simple response.
	 * @return Array
	 */
	public function simple_response( $response, $simple_response ) {
		$processed_response = array();
		foreach ( $response as $key => $value ) {
			if ( ( is_array( $value ) || is_object( $value ) ) && 'No' !== $simple_response ) {
				$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
			} else {
				$processed_response[ $key ] = ( is_array( $value ) || is_object( $value ) ) ? wp_json_encode( $value ) : $value;
			}
		}

		// If simple response is not set to No, return the processed response.
		if ( 'No' !== $simple_response ) {
			$processed_response_data = array();
			foreach ( $processed_response as $key => $value ) {
				if ( $value && json_decode( $value, true ) ) {
					$value                   = json_decode( $value, true );
					$processed_response_data = flowmattic_recursive_array( $processed_response_data, $key, $value );
				} else {
					$processed_response_data[ $key ] = $value;
				}
			}

			$processed_response = $processed_response_data;
		}

		// If the response contains certain keys, remove them.
		$processed_response = $this->remove_frequently_updated_keys_from_response( $processed_response );

		return $processed_response;
	}
}
