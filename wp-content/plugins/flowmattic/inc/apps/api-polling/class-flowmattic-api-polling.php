<?php
/**
 * Application Name: FlowMattic API Polling
 * Description: Add FlowMattic API Polling integration to FlowMattic.
 * Version: 4.0
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
class FlowMattic_API_Polling {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Webhook Outgoing.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'api_polling',
			array(
				'name'         => esc_attr__( 'API Polling', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/api-polling/icon.svg',
				'instructions' => 'Fetch data through API on set frequency.',
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		// Add filter for the API Polling.
		add_filter( 'flowmattic_poll_api_api_polling', array( $this, 'poll_api' ), 10, 5 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-api-polling', FLOWMATTIC_PLUGIN_URL . 'inc/apps/api-polling/view-api-polling.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return Array
	 */
	public function get_triggers() {
		return array(
			'changes_detected' => array(
				'title'       => esc_attr__( 'API Changes Detected', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when app detects changes in the API response.', 'flowmattic' ),
				'api_polling' => true,
			),
		);
	}

	/**
	 * Poll API.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array  $default_response  Default response.
	 * @param String $workflow_id       Workflow ID.
	 * @param String $workflow_data     Workflow Data.
	 * @param Array  $workflow_settings Workflow Settings.
	 * @param Bool   $is_capturing      Whether the workflow is in capture mode.
	 * @return Array
	 */
	public function poll_api( $default_response, $workflow_id, $workflow_data, $workflow_settings, $is_capturing = false ) {
		// Get the API Endpoint URL.
		$api_endpoint_url = isset( $workflow_data['api_endpoint_url'] ) ? $workflow_data['api_endpoint_url'] : '';

		// Get the API Polling Method.
		$api_polling_method = isset( $workflow_data['api_polling_method'] ) ? $workflow_data['api_polling_method'] : 'GET';

		// Get the item index.
		$item_index = isset( $workflow_data['api_item_index'] ) ? $workflow_data['api_item_index'] : '';

		// Get the API Polling Frequency.
		$api_polling_frequency = isset( $workflow_data['apiPollingFrequency'] ) ? (int) $workflow_data['apiPollingFrequency'] : 10;

		// Get the connect id.
		$connect_id = isset( $workflow_data['trigger_connect_id'] ) ? $workflow_data['trigger_connect_id'] : '';

		// Get API parameters.
		$api_parameters = isset( $workflow_data['api_polling_parameters'] ) ? $workflow_data['api_polling_parameters'] : '';

		// Get API headers.
		$api_headers = isset( $workflow_data['api_polling_headers'] ) ? $workflow_data['api_polling_headers'] : '';

		// Get the simple response.
		$simple_response = isset( $workflow_data['simple_response'] ) ? $workflow_data['simple_response'] : 'Yes';

		// If API Endpoint URL is empty, return.
		if ( empty( $api_endpoint_url ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'API Endpoint URL is empty.', 'flowmattic' ),
			);
		}

		// If connect id is empty, return.
		if ( empty( $connect_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Connect ID is empty.', 'flowmattic' ),
			);
		}

		$body         = array();
		$headers      = array(
			'User-Agent: FlowMattic/' . FLOWMATTIC_VERSION,
			'Content-Type: application/json',
		);
		$request_args = array(
			'body'        => $body,
			'headers'     => array(
				'User-Agent'   => 'FlowMattic/' . FLOWMATTIC_VERSION,
				'Content-Type' => 'application/json',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => strtoupper( $api_polling_method ),
		);

		// Get and set the connect authentication data.
		if ( 'none' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Check if external connect.
			$external_connect = ( isset( $connect->connect_settings['is_external'] ) ) ? flowmattic_get_connects( $connect->connect_settings['external_slug'] ) : false;

			// If is external connect, get the auth type.
			$auth_type = ( $external_connect ) ? $external_connect['fm_auth_type'] : $connect->connect_settings['fm_auth_type'];

			// Get the auth name.
			$auth_name = ( isset( $connect->connect_settings['auth_name'] ) ) ? $connect->connect_settings['auth_name'] : 'Bearer';

			// Set the authorization according to the auth type.
			switch ( $auth_type ) {
				case 'oauth':
					$connect_data = $connect->connect_data;
					$auth_name    = ! empty( $external_connect ) && isset( $external_connect['auth_name'] ) ? $external_connect['auth_name'] : $auth_name;

					// Add authentication to header.
					$request_args['headers']['Authorization'] = $auth_name . ' ' . $connect_data['access_token'];

					// Headers used in cURL request.
					$headers[] = 'Authorization: ' . $auth_name . ' ' . $connect_data['access_token'];

					break;

				case 'bearer':
					$token = $connect->connect_settings['auth_bearer_token'];

					// Add authentication to header.
					$request_args['headers']['Authorization'] = 'Bearer ' . $token;

					// Headers used in cURL request.
					$headers[] = 'Authorization: Bearer ' . $token;

					break;

				case 'basic':
					$api_key    = $connect->connect_settings['auth_api_key'];
					$api_secret = $connect->connect_settings['auth_api_secret'];

					$request_args['headers']['Authorization'] = 'Basic ' . base64_encode( $api_key . ':' . $api_secret ); // @codingStandardsIgnoreLine

					// Headers used in cURL request.
					$headers[] = 'Authorization: Basic ' . base64_encode( $api_key . ':' . $api_secret ); // @codingStandardsIgnoreLine

					break;

				case 'api':
					$add_to    = ( $external_connect ) ? $external_connect['auth_api_addto'] : $connect->connect_settings['auth_api_addto'];
					$api_key   = ( $external_connect ) ? $external_connect['auth_api_key'] : $connect->connect_settings['auth_api_key'];
					$api_value = $connect->connect_settings['auth_api_value'];

					if ( 'query' === $add_to ) {
						$api_endpoint = add_query_arg( $api_key, $api_value, $api_endpoint );
					} else {
						$request_args['headers'][ $api_key ] = trim( $api_value );

						// Headers used in cURL request.
						$headers[] = $api_key . ': ' . trim( $api_value );
					}

					break;
			}
		}

		// If API parameters are set, add them to the request.
		if ( ! empty( $api_parameters ) ) {
			// If the method is GET, add the parameters to the URL.
			if ( 'GET' === strtoupper( $api_polling_method ) ) {
				$api_endpoint_url = add_query_arg( $api_parameters, $api_endpoint_url );

				// Remove the body from the request.
				unset( $request_args['body'] );
			} else {
				foreach ( $api_parameters as $key => $value ) {
					// Remove the line breaks from the value.
					$value = str_replace( array( "\r", "\n" ), '', $value );

					// Add the parameter to the body.
					$api_parameters[ $key ] = ( json_decode( $value ) ) ? json_decode( $value ) : $value;
				}

				// Set the body.
				$request_args['body'] = wp_json_encode( $api_parameters );
			}
		}

		// If API headers are set, add them to the request.
		if ( ! empty( $api_headers ) ) {
			$request_args['headers'] = array_merge( $request_args['headers'], $api_headers );
		}

		// Get the response.
		$response = wp_remote_request( $api_endpoint_url, $request_args );

		// If the response is an error, return.
		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'error',
				'message' => $response->get_error_message(),
			);
		}

		// Get the response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// If the response code is 404, return.
		if ( 404 === $response_code ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'API Endpoint URL not found.', 'flowmattic' ),
			);
		}

		// Get the response body.
		$request_body = wp_remote_retrieve_body( $response );

		// If the response body is empty, return.
		if ( empty( $request_body ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'API response is empty.', 'flowmattic' ),
			);
		}

		// Decode the response body.
		$response_json = json_decode( $request_body, true );

		$response = array(
			'status'          => 'success',
			'message'         => esc_html__( 'API response received.', 'flowmattic' ),
			'webhook_capture' => '',
		);

		$response_body      = array();
		$processed_response = array();
		$records_to_process = array();
		$index_record       = array();
		$is_xml             = false;
		$is_json            = false;

		// If the response body is XML, parse it and return as array.
		if ( ! is_array( $response_json ) ) {
			$xml_body = (array) simplexml_load_string( $request_body, 'SimpleXMLElement', LIBXML_NOCDATA );

			if ( ! empty( $xml_body ) ) {
				$is_xml = true;
			}

			$request_body = wp_json_encode( $xml_body );
			$xml_json     = json_decode( $request_body, true );

			// If is RSS feed, get the channel items.
			if ( isset( $xml_json['channel'] ) && isset( $xml_json['channel']['item'] ) ) {
				$xml_json_channel             = $xml_json['channel']['item'];
				$index_record['channel_item'] = $xml_json_channel[0];
				$item_index                   = 'channel@item';

				// Remove the channel items from the response body.
				unset( $xml_json['channel']['item'] );

				// Set the response body.
				$response_body = $xml_json;
			} else {
				// Loop through the response body and get only the first level of nested arrays.
				foreach ( $xml_body as $key => $value ) {
					if ( is_array( $value ) || is_object( $value ) ) {
						foreach ( $value as $k => $v ) {
							if ( is_array( $v ) || is_object( $v ) ) {
								$v = (array) $v;
								$index_record[ $key ][ $k ] = ( 1 === count( $v ) ) ? ( isset( $v[0] ) ? $v[0] : $v ) : $v;
							} else {
								$index_record[ $key ][ $k ] = $v;
							}

							// Set the item index.
							$item_index = $key . '@' . $k;

							continue;
						}
					} else {
						$response_body[ $key ] = $value;
					}
				}
			}
		} elseif ( is_array( $response_json ) ) {
			$is_json = true;

			// Loop through the response body and get only the first level of nested arrays.
			foreach ( $response_json as $key => $value ) {
				// If index is set, get the item index.
				if ( '' !== $item_index && $key === $item_index ) {
					$index_record[ $key ] = ( ! empty( $value ) && is_array( $value ) ) ? $value[0] : $value;
					continue;
				}

				// If index is not set, try finding the key in itemizer indexes.
				if ( '' === $item_index && in_array( $key, $this->get_array_itemizer_indexes(), true ) ) {
					$index_record[ $key ] = ( ! empty( $value ) && is_array( $value ) ) ? $value[0] : $value;

					// Set the item index.
					$item_index = $key;

					continue;
				}

				$response_body[ $key ] = $value;
			}
		} else {
			// If the response body is not an array or XML, it is a string. Wrap it in an array.
			$response_body = array( 'response' => $request_body );
		}

		// If index record is found, merge it with the response body.
		if ( ! empty( $index_record ) ) {
			$response_body = array_merge( $response_body, $index_record );
		}

		$processed_response = wp_flowmattic()->api_polling->simple_response( $response_body, $simple_response );

		// Set the response body.
		$response['webhook_capture'] = $processed_response;

		if ( $is_capturing ) {
			// Since the data is just captured, update it as the stored data.
			wp_flowmattic()->api_polling->update_stored_data( $workflow_id, (array) $workflow_settings, $processed_response );

			// Return the response.
			return $response;
		}

		// Remove the webhook capture.
		unset( $response['webhook_capture'] );

		// Check if data is changed.
		$is_data_changed = wp_flowmattic()->api_polling->compare_stored_data( $workflow_settings, $processed_response );

		// If the data is not changed, return.
		if ( ! $is_data_changed ) {
			return array(
				'status'  => 'success',
				'message' => esc_html__( 'No changes detected in the API response.', 'flowmattic' ),
			);
		}

		// Merge the response.
		$response = array_merge( $processed_response, $response );

		// If is json or xml, loop through the request body and get the records to process until the stored data is found.
		if ( $is_json || $is_xml ) {
			$records_to_process = wp_flowmattic()->api_polling->get_records_to_process( $workflow_settings, $request_body, $processed_response, $item_index, $simple_response );
		} else {
			// If the response body is not an array or XML, it is a string. Wrap it in an array.
			$records_to_process = array( $response_body );
		}

		// If records to process are found, process them.
		if ( ! empty( $records_to_process ) ) {
			// Loop through the records to process.
			foreach ( $records_to_process as $record ) {
				// Add the status and message to the record.
				$record['status']  = 'success';
				$record['message'] = esc_html__( 'API response received.', 'flowmattic' );

				// Run the workflow.
				wp_flowmattic()->api_polling->run_workflow( $workflow_id, $record );
			}
		}
	}

	/**
	 * Get possible array itemizer indexes.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return Array
	 */
	public function get_array_itemizer_indexes() {
		return array(
			'lists',
			'results',
			'posts',
			'items',
			'recipes',
			'members',
			'interviews',
			'videos',
			'locations',
			'participants',
			'articles',
			'stories',
			'products',
			'images',
			'files',
			'documents',
			'categories',
			'tags',
			'labels',
			'albums',
			'artists',
			'tracks',
			'playlists',
			'episodes',
			'seasons',
			'chapters',
			'lessons',
			'courses',
			'tutorials',
			'faqs',
			'questions',
			'answers',
			'comments',
			'reviews',
			'ratings',
			'events',
			'speakers',
			'sponsors',
			'jobs',
			'applications',
			'users',
			'profiles',
			'contacts',
			'friends',
			'groups',
			'teams',
			'departments',
			'classes',
			'students',
			'teachers',
			'schools',
			'colleges',
			'universities',
			'businesses',
			'invoices',
			'transactions',
			'payments',
			'orders',
			'carts',
			'wishlists',
			'followers',
			'subscribers',
			'likes',
			'favorites',
			'bookmarks',
			'messages',
			'notifications',
			'tasks',
			'projects',
			'notes',
			'updates',
			'logs',
			'records',
			'data',
			'stats',
			'options',
			'threads',
			'forums',
			'connections',
			'links',
			'histories',
			'schedules',
			'calendars',
			'appointments',
			'reservations',
		);
	}
}

new FlowMattic_API_Polling();
