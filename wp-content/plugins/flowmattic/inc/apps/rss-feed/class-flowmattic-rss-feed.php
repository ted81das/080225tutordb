<?php
/**
 * Application Name: FlowMattic RSS Feed
 * Description: Add FlowMattic RSS Feed integration to FlowMattic.
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
 * FlowMattic RSS Feed.
 *
 * @since 4.1.0
 */
class FlowMattic_Rss_Feed {
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
			'rss_feed',
			array(
				'name'         => esc_attr__( 'RSS Feed', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/rss-feed/icon.svg',
				'instructions' => 'Fetch RSS feed through using polling on set frequency.',
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'trigger,action',
			)
		);

		// Add filter for the RSS Feed.
		add_filter( 'flowmattic_poll_api_rss_feed', array( $this, 'poll_api' ), 10, 5 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-rss-feed', FLOWMATTIC_PLUGIN_URL . 'inc/apps/rss-feed/view-rss-feed.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
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
			'new_item' => array(
				'title'       => esc_attr__( 'New Item in RSS Feed', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger on new RSS Feed items', 'flowmattic' ),
				'api_polling' => true,
			),
		);
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.1.0
	 * @return Array
	 */
	public function get_actions() {
		return array(
			'add_new_feed_item' => array(
				'title'       => esc_attr__( 'Create New Item in RSS Feed', 'flowmattic' ),
				'description' => esc_attr__( 'Power your custom RSS Feed with FlowMattic', 'flowmattic' ),
			),
			'retrieve_rss_feed' => array(
				'title'       => esc_attr__( 'Retrieve RSS Feed Items', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieves RSS feed items from a specified URL', 'flowmattic' ),
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
		// Get the RSS Feed URL.
		$rss_feed_url = isset( $workflow_data['rss_feed_url'] ) ? $workflow_data['rss_feed_url'] : '';

		// Get the RSS Feed Method.
		$api_polling_method = 'GET';

		// Get the item index.
		$item_index = '';

		// Get the RSS Feed Frequency.
		$api_polling_frequency = isset( $workflow_data['apiPollingFrequency'] ) ? (int) $workflow_data['apiPollingFrequency'] : 10;

		// Get the connect id.
		$connect_id = isset( $workflow_data['trigger_connect_id'] ) ? $workflow_data['trigger_connect_id'] : '';

		// Get the simple response.
		$simple_response = isset( $workflow_data['simple_response'] ) ? $workflow_data['simple_response'] : 'Yes';

		// If RSS Feed URL is empty, return.
		if ( empty( $rss_feed_url ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'RSS Feed URL is empty.', 'flowmattic' ),
			);
		}

		// If connect id is empty, return.
		if ( empty( $connect_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Connect ID is empty.', 'flowmattic' ),
			);
		}

		$body         = array(
			'timestamp' => time(),
		);
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

		// Get the response.
		$response = wp_remote_request( $rss_feed_url, $request_args );

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
				'message' => esc_html__( 'RSS Feed URL not found.', 'flowmattic' ),
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

		$response = array(
			'status'          => 'success',
			'message'         => esc_html__( 'API response received.', 'flowmattic' ),
			'webhook_capture' => '',
		);

		$response_body      = array();
		$processed_response = array();
		$records_to_process = array();
		$index_record       = array();

		// Replace the <content:encoded> tag with <content_encoded> tag.
		$request_body = str_replace( '<content:encoded>', '<content_encoded>', $request_body );
		$request_body = str_replace( '</content:encoded>', '</content_encoded>', $request_body );

		// Replace the <media:content> tag with <media_content> tag.
		$request_body = str_replace( '<media:content', '<media_content', $request_body );
		$request_body = str_replace( '</media:content>', '</media_content>', $request_body );

		// Replace the <media:thumbnail> tag with <media_thumbnail> tag.
		$request_body = str_replace( '<media:thumbnail', '<media_thumbnail', $request_body );
		$request_body = str_replace( '</media:thumbnail>', '</media_thumbnail>', $request_body );

		// Replace <dc:creator> tag with <dc_creator> tag.
		$request_body = str_replace( '<dc:creator>', '<dc_creator>', $request_body );
		$request_body = str_replace( '</dc:creator>', '</dc_creator>', $request_body );

		// Replace <sy:updatePeriod> tag with <sy_updatePeriod> tag.
		$request_body = str_replace( '<sy:updatePeriod>', '<sy_updatePeriod>', $request_body );
		$request_body = str_replace( '</sy:updatePeriod>', '</sy_updatePeriod>', $request_body );

		// Replace <sy:updateFrequency> tag with <sy_updateFrequency> tag.
		$request_body = str_replace( '<sy:updateFrequency>', '<sy_updateFrequency>', $request_body );
		$request_body = str_replace( '</sy:updateFrequency>', '</sy_updateFrequency>', $request_body );

		// Replace any other tags that match the pattern. Replace : with _.
		preg_match_all( '/<?((\w+):\w+)(\s|>)/', $request_body, $matches );

		if ( ! empty( $matches ) ) {
			foreach ( $matches[0] as $match ) {
				$replace = str_replace( ':', '_', $match );
				$request_body = str_replace( $match, $replace, $request_body );

				// Replace @Attribute with _Attribute.
				$replace = str_replace( '@', '_', $request_body );
			}
		}

		// If the response body is XML, parse it and return as array.
		$xml_body = (array) simplexml_load_string( $request_body, 'SimpleXMLElement', LIBXML_NOCDATA );

		$request_body = wp_json_encode( $xml_body );
		$xml_json     = json_decode( $request_body, true );
		$xml_body     = json_decode( $request_body, true );

		// If is RSS feed, get the channel items.
		if ( isset( $xml_json['channel'] ) && isset( $xml_json['channel']['item'] ) ) {
			$xml_json_channel             = $xml_json['channel']['item'];
			$index_record['channel_item'] = $xml_json_channel[0];
			$item_index                   = 'channel@item';

			// Remove the channel items from the response body.
			unset( $xml_json['channel']['item'] );

			// Set the response body.
			$response_body = $xml_json;
		}

		// If index record is found, merge it with the response body.
		if ( ! empty( $index_record ) ) {
			$response_body = array_merge( $response_body, $index_record );
		}

		$processed_response = wp_flowmattic()->api_polling->simple_response( $response_body, $simple_response );

		// Set the response body.
		$response['webhook_capture'] = $processed_response;

		// Data to store.
		$data_to_store = array(
			'channel_item_title' => $processed_response['channel_item_title'],
		);

		if ( $is_capturing ) {
			// Since the data is just captured, update it as the stored data.
			wp_flowmattic()->api_polling->update_stored_data( $workflow_id, (array) $workflow_settings, $data_to_store );

			// Return the response.
			return $response;
		}

		// Remove the webhook capture.
		unset( $response['webhook_capture'] );

		// Convert workflow settings to array.
		$workflow_settings = (array) $workflow_settings;

		$stored_response_array = array();

		// Decode the stored response.
		if ( isset( $workflow_settings['stored_response'] ) ) {
			$stored_response         = $workflow_settings['stored_response'];
			$stored_response_decoded = base64_decode( $stored_response ); // phpcs:ignore
			$stored_response_array   = json_decode( $stored_response_decoded, true );

			// Get the last message id.
			$stored_item_title = isset( $stored_response_array['channel_item_title'] ) ? $stored_response_array['channel_item_title'] : 0;
		} else {
			$stored_item_title = $data_to_store['channel_item_title'];
		}

		// If the stored channel item is not the first in the list, add the records to process till it is found.
		foreach ( $xml_body['channel']['item'] as $key => $value ) {
			if ( $value['title'] === $stored_item_title ) {
				break;
			}

			// Record to process.
			$record_to_process = array();

			// Record item key.
			$record_item_key = 'channel_item';
			foreach ( $value as $k => $v ) {
				$record_to_process[ $record_item_key . '_' . $k ] = $v;
			}

			// Simplify the response.
			$record_to_process = wp_flowmattic()->api_polling->simple_response( $record_to_process, 'Yes' );

			// Add the record to the records to process.
			$records_to_process[] = $record_to_process;
		}

		// If records to process are found, process them.
		if ( ! empty( $records_to_process ) ) {
			// Get the first record to process.
			$first_record = $records_to_process[0];

			// Data to store.
			$data_to_store = array(
				'channel_item_title' => $first_record['channel_item_title'],
			);

			// Update the stored data.
			wp_flowmattic()->api_polling->update_stored_data( $workflow_id, $workflow_settings, $data_to_store );

			// Flip the array to get the latest records first.
			$records_to_process = array_reverse( $records_to_process );

			// Loop through the records to process.
			foreach ( $records_to_process as $record ) {
				// Add the status and message to the record.
				$record['status']  = 'success';
				$record['message'] = esc_html__( 'New feed item detected', 'flowmattic' );

				// Let the server breathe a bit. Wait for 25 milliseconds.
				usleep( 25000 );

				// Run the workflow.
				wp_flowmattic()->api_polling->run_workflow( $workflow_id, $record );
			}
		}
	}

	/**
	 * Run action step.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param String $workflow_id Workflow ID.
	 * @param Array  $step        Workflow current step.
	 * @param Array  $fields      Fields.
	 * @return Array
	 */
	public function run_action_step( $workflow_id, $step, $fields ) {
		$action   = $step['action'];
		$fields   = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$response = '';

		switch ( $action ) {
			case 'add_new_feed_item':
				$response = $this->add_new_feed_item_action( $fields );
				break;

			case 'retrieve_rss_feed':
				$response = $this->retrieve_rss_feed_action( $fields );
				break;
		}

		return $response;
	}

	/**
	 * Add new feed item action.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array $fields     Fields.
	 * @return Array
	 */
	public function add_new_feed_item_action( $fields ) {
		$rss_feed_slug        = isset( $fields['rss_feed_slug'] ) ? $fields['rss_feed_slug'] : '';
		$feed_title           = isset( $fields['feed_title'] ) ? $fields['feed_title'] : '';
		$feed_description     = isset( $fields['feed_description'] ) ? $fields['feed_description'] : '';
		$feed_link            = isset( $fields['feed_link'] ) ? $fields['feed_link'] : '';
		$max_records          = isset( $fields['max_records'] ) ? $fields['max_records'] : 50;
		$remove_older_records = isset( $fields['remove_older_records'] ) ? $fields['remove_older_records'] : 'No';
		$item_title           = isset( $fields['item_title'] ) ? $fields['item_title'] : '';
		$item_source          = isset( $fields['item_source'] ) ? $fields['item_source'] : '';
		$item_description     = isset( $fields['item_description'] ) ? $fields['item_description'] : '';
		$item_author          = isset( $fields['item_author'] ) ? $fields['item_author'] : '';
		$item_author_email    = isset( $fields['item_author_email'] ) ? $fields['item_author_email'] : '';
		$item_author_link     = isset( $fields['item_author_link'] ) ? $fields['item_author_link'] : '';
		$item_category        = isset( $fields['item_category'] ) ? $fields['item_category'] : '';
		$item_media_url       = isset( $fields['item_media_url'] ) ? $fields['item_media_url'] : '';
		$item_media_length    = isset( $fields['item_media_length'] ) ? $fields['item_media_length'] : '';
		$item_media_mime      = isset( $fields['item_media_mime'] ) ? $fields['item_media_mime'] : '';
		$item_pub_date        = isset( $fields['item_pub_date'] ) ? $fields['item_pub_date'] : '';

		// If RSS Feed Slug is empty, return.
		if ( empty( $rss_feed_slug ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'RSS Feed Slug is empty.', 'flowmattic' ),
			);
		}

		// Create feed data array.
		$feed_data = array(
			'feed_title'           => $feed_title,
			'feed_link'            => $feed_link,
			'max_records'          => $max_records,
			'remove_older_records' => $remove_older_records,
		);

		// Create item data array.
		$item_data = array(
			'item_title'        => $item_title,
			'item_source'       => $item_source,
			'item_description'  => $item_description,
			'item_author'       => $item_author,
			'item_author_email' => $item_author_email,
			'item_author_link'  => $item_author_link,
			'item_category'     => $item_category,
			'item_media_url'    => $item_media_url,
			'item_media_length' => $item_media_length,
			'item_media_mime'   => $item_media_mime,
			'item_pub_date'     => $item_pub_date,
		);

		// Insert the feed data into the database.
		$feed_id = wp_flowmattic()->rss_feed_db->insert(
			array(
				'feed_slug'  => $rss_feed_slug,
				'feed_data'  => $feed_data,
				'feed_items' => $item_data,
			)
		);

		// If the feed id is empty, return.
		if ( empty( $feed_id ) ) {
			return wp_json_encode(
				array(
					'status'   => 'error',
					'message'  => esc_html__( 'Feed item not added.', 'flowmattic' ),
					'response' => $feed_id,
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Feed item added.', 'flowmattic' ),
				'feed_id' => $feed_id,
			)
		);
	}

	/**
	 * Retrieve RSS feed action.
	 *
	 * @access public
	 * @since 4.1.0
	 * @param Array $fields     Fields.
	 * @return Array
	 */
	public function retrieve_rss_feed_action( $fields ) {
		$rss_feed_url       = isset( $fields['rss_feed_url'] ) ? $fields['rss_feed_url'] : '';
		$response_format    = isset( $fields['response_format'] ) ? $fields['response_format'] : 'simple';
		$processed_response = array();

		// If RSS Feed URL is empty, return.
		if ( empty( $rss_feed_url ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'RSS Feed URL is empty.', 'flowmattic' ),
			);
		}

		// Get the response.
		$response = wp_remote_get( $rss_feed_url );

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
				'message' => esc_html__( 'RSS Feed URL not found.', 'flowmattic' ),
			);
		}

		// Get the response body.
		$request_body = wp_remote_retrieve_body( $response );

		// If the response body is empty, return.
		if ( empty( $request_body ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Feed response is empty.', 'flowmattic' ),
			);
		}

		// Replace the <content:encoded> tag with <content_encoded> tag.
		$request_body = str_replace( '<content:encoded>', '<content_encoded>', $request_body );
		$request_body = str_replace( '</content:encoded>', '</content_encoded>', $request_body );

		// Replace the <media:content> tag with <media_content> tag.
		$request_body = str_replace( '<media:content', '<media_content', $request_body );
		$request_body = str_replace( '</media:content>', '</media_content>', $request_body );

		// Replace the <media:thumbnail> tag with <media_thumbnail> tag.
		$request_body = str_replace( '<media:thumbnail', '<media_thumbnail', $request_body );
		$request_body = str_replace( '</media:thumbnail>', '</media_thumbnail>', $request_body );

		// Replace <dc:creator> tag with <dc_creator> tag.
		$request_body = str_replace( '<dc:creator>', '<dc_creator>', $request_body );
		$request_body = str_replace( '</dc:creator>', '</dc_creator>', $request_body );

		// Replace <sy:updatePeriod> tag with <sy_updatePeriod> tag.
		$request_body = str_replace( '<sy:updatePeriod>', '<sy_updatePeriod>', $request_body );
		$request_body = str_replace( '</sy:updatePeriod>', '</sy_updatePeriod>', $request_body );

		// Replace <sy:updateFrequency> tag with <sy_updateFrequency> tag.
		$request_body = str_replace( '<sy:updateFrequency>', '<sy_updateFrequency>', $request_body );
		$request_body = str_replace( '</sy:updateFrequency>', '</sy_updateFrequency>', $request_body );

		// If the response body is XML, parse it and return as array.
		$xml_body = (array) simplexml_load_string( $request_body, 'SimpleXMLElement', LIBXML_NOCDATA );

		$response_json = wp_json_encode( $xml_body );
		$response_body = json_decode( $response_json, true );

		if ( 'simple' === $response_format ) {
			$processed_response = wp_flowmattic()->api_polling->simple_response( $response_body, 'Yes' );
		} else {
			// Loop through the response body and get the feed items except items.
			foreach ( $response_body['channel'] as $key => $value ) {
				if ( 'item' === $key ) {
					continue;
				}

				$processed_response[ 'channel_' . $key ] = $value;
			}

			// Set the feed items json.
			$processed_response['feed_items_json'] = wp_json_encode( $response_body['channel']['item'] );
		}

		$processed_response['status']  = 'success';
		$processed_response['message'] = esc_html__( 'RSS Feed response received.', 'flowmattic' );

		return wp_json_encode( $processed_response );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 4.1.0
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

new FlowMattic_Rss_Feed();
