<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for cusom app actions.
 */
class FlowMattic_Custom_User_App {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 3.1.1
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Retry count.
	 *
	 * @access public
	 * @since 4.0
	 * @var int
	 */
	public $retry_count = 0;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step   = (array) $step;
		$action = $step['action'];
		$fields = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );

		// Check response type.
		$simple_response = ( ! isset( $fields['simple_response'] ) ) ? 'Yes' : $fields['simple_response'];

		// Get the App ID.
		$app_id = $step['application'];

		// Get the app data.
		$flowmattic_apps = wp_flowmattic()->apps;
		$all_apps        = $flowmattic_apps->get_other_action_applications();
		$app_data        = $all_apps[ $app_id ];

		// Get the app settings.
		$app_settings = isset( $app_data['app_settings'] ) ? maybe_unserialize( $app_data['app_settings'] ) : array();

		// Check if needs custom ID.
		$needs_connect = isset( $app_data['needs_connect'] ) ? true : false;

		if ( isset( $fields['connect_id'] ) && 'default' !== $fields['connect_id'] ) {
			$needs_connect = true;
		}

		$connect_id = '';
		// Get connect ID used for the app.
		if ( $needs_connect ) {
			$connect_id = isset( $fields['connect_id'] ) ? $fields['connect_id'] : 'none';
		} else {
			$connect_id = isset( $app_settings['connect_id'] ) ? $app_settings['connect_id'] : 'none';
		}

		// Get the available actions.
		$app_actions = isset( $app_data['actions'] ) ? maybe_unserialize( $app_data['actions'] ) : array();

		// Check if the action event is availabe, get the data if available.
		$app_action = ( is_array( $app_actions ) && isset( $app_actions[ $action ] ) ) ? $app_actions[ $action ]['action_data'] : array();

		// Get the request type.
		$request_type = $app_action['http_method'];

		$body         = array();
		$headers      = array(
			'User-Agent: FlowMattic/' . FLOWMATTIC_VERSION,
		);
		$request_args = array(
			'body'        => $body,
			'headers'     => array(
				'User-Agent'   => 'FlowMattic/' . FLOWMATTIC_VERSION,
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => strtoupper( $request_type ),
		);

		// Trim the space for endpoint url.
		$api_endpoint = trim( $app_action['endpoint_url'] );

		if ( '' !== $connect_id ) {
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
				$auth_name = ( isset( $connect->connect_settings['auth_name'] ) && '' !== $connect->connect_settings['auth_name'] ) ? $connect->connect_settings['auth_name'] : 'Bearer';

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
		} else {
			// If there's no response, return default message.
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Connect ID not provided', 'flowmattic' ),
				)
			);
		}

		// If action requires custom headers, set them all.
		if ( isset( $app_action['add_headers'] ) && 'no' !== $app_action['add_headers'] ) {
			$api_headers = $app_action['dynamic-headers-key'];

			foreach ( $api_headers as $header_id => $header_key ) {
				if ( isset( $fields[ $header_key ] ) ) {
					$header_value = $fields[ $header_key ];

					// Headers used in regular requests.
					$request_args['headers'][ $header_key ] = trim( stripslashes( $header_value ) );

					// Headers used in cURL request.
					$headers[] = $header_key . ': ' . trim( stripslashes( $header_value ) );
				}
			}
		}

		// Get the content type header from app.
		$content_type = $app_action['content_type'];

		// Get the content type value.
		$content_type = $this->get_content_types()[ $content_type ]['type'];

		// Set the content type header.
		$request_args['headers']['Content-Type'] = $content_type;
		$headers[]                               = 'Content-Type: ' . $content_type;
		$headers[]                               = 'Accept: application/json';

		// Check if action has set raw data.
		$raw_data = stripslashes( $app_action['raw_data'] );

		if ( '""' === $raw_data ) {
			$raw_data = '';
		}

		$request_data = array();

		$api_params    = $app_action['dynamic-params-key'];
		$param_options = $app_action['dynamic-params-options'];

		// If action has body params, set them all.
		if ( isset( $app_action['add_params'] ) && 'no' !== $app_action['add_params'] ) {
			$body_params   = array();

			if ( '' !== $raw_data ) {
				$raw_data_json = json_decode( $raw_data, true );

				if ( ! is_array( $raw_data_json ) ) {
					$raw_data_json = json_decode( stripslashes( $raw_data ), true );
				}

				if ( ! is_array( $raw_data_json ) ) {
					// In case some dynamic tags are not replaced, replace them with the field values.
					foreach ( $fields as $field_key => $field_value ) {
						if ( false !== strpos( $raw_data, '{{' . $field_key . '}}' ) ) {
							// Replace the dynamic tags with the field values.
							$raw_data = str_replace( '{{' . $field_key . '}}', $field_value, $raw_data );
						}
					}

					$raw_data = stripslashes( $raw_data );
				}

				if ( is_array( $raw_data_json ) ) {
					// Convert the keys to lowercase.
					$lower_case_fields = array_change_key_case( $fields, CASE_LOWER );

					$raw_param_options = array();

					foreach ( $api_params as $param_id => $param_key ) {
						if ( isset( $fields[ $param_key ] ) || isset( $lower_case_fields[ $param_key ] ) ) {
							$decode_option = base64_decode( $param_options[ $param_id ] );
	
							// Remove the help text from the options with regex.
							$decode_option = preg_replace( '/,"help_text":(.*?)"}$/', '}', $decode_option );
	
							$param_option = json_decode( stripslashes( $decode_option ), true );
							$param_type   = $param_option['field_type'];
							$param_value  = isset( $fields[ $param_key ] ) ? stripslashes( trim( $fields[ $param_key ] ) ) : stripslashes( trim( $lower_case_fields[ $param_key ] ) );
							$replace_tag  = '{{' . $param_key . '}}';

							// Get the field value as per the field type.
							$field_value = $this->get_field_value( $param_option, $param_value );
							
							// Replace the dynamic tags with the field values.	
							$raw_data_json = $this->replace_dynamic_tags( $raw_data_json, $field_value, $replace_tag, $param_type );
							// $raw_data_json[ $param_key ] = ( 'number' === $param_type ) ? (float) $field_value : ( 'boolean' === $param_type ? ( 'yes' === $field_value ? true : false ) : $field_value );
						}
					}

					// Loop and remove any empty items recursively.
					$raw_data_json = $this->remove_empty_items( $raw_data_json );

					$raw_data = wp_json_encode( $raw_data_json );

					// Replace empty array with empty object.
					$raw_data = str_replace( '[]', '{}', $raw_data );

					// In case some dynamic tags are not replaced, replace them with the field values.
					foreach ( $fields as $field_key => $field_value ) {
						if ( false !== strpos( $raw_data, '{{' . $field_key . '}}' ) ) {
							// Replace the dynamic tags with the field values.
							$raw_data = str_replace( '{{' . $field_key . '}}', $field_value, $raw_data );
						}
					}

					// With lower case fields.
					foreach ( $lower_case_fields as $field_key => $field_value ) {
						if ( false !== strpos( $raw_data, '{{' . $field_key . '}}' ) ) {
							// Replace the dynamic tags with the field values.
							$raw_data = str_replace( '{{' . $field_key . '}}', $field_value, $raw_data );
						}
					}
				}

				// If endpoint URL contains dynamic tags, replace them with the values.
				if ( false !== strpos( $api_endpoint, '{{' ) ) {
					foreach ( $api_params as $param_id => $param_key ) {
						if ( isset( $fields[ $param_key ] ) ) {
							$decode_option = base64_decode( $param_options[ $param_id ] );
	
							// Remove the help text from the options with regex.
							$decode_option = preg_replace( '/,"help_text":(.*?)"}$/', '}', $decode_option );
	
							$param_option = json_decode( stripslashes( $decode_option ), true );
							$param_value  = stripslashes( trim( $fields[ $param_key ] ) );
							$replace_tag  = '{{' . $param_key . '}}';
	
							// Decode the URL endpoint to replace the dynamic tags.
							$api_endpoint = urldecode( $api_endpoint );

							if ( false !== strpos( $api_endpoint, $replace_tag ) ) {
								$api_endpoint = str_replace( $replace_tag, $param_value, $api_endpoint );
								if ( isset( $param_option['path_variable'] ) && 'no' !== $param_option['path_variable'] ) {
									continue;
								}
							}
						}
					}
				}
			} else {
				foreach ( $api_params as $param_id => $param_key ) {
					if ( isset( $fields[ $param_key ] ) ) {
						$decode_option = base64_decode( $param_options[ $param_id ] );

						// Remove the help text from the options with regex.
						$decode_option = preg_replace( '/,"help_text":(.*?)"}$/', '}', $decode_option );

						$param_option = json_decode( stripslashes( $decode_option ), true );
						$param_value  = stripslashes( trim( $fields[ $param_key ] ) );
						$replace_tag  = '{{' . $param_key . '}}';

						// Decode the URL endpoint to replace the dynamic tags.
						$api_endpoint = urldecode( $api_endpoint );

						// Get the field type.
						$field_type = $param_option['field_type'];

						// Check if field is required.
						$required = isset( $param_option['field_required'] ) && 'yes' === $param_option['field_required'] ? true : false;

						// Format data according to the field type.
						switch ( $field_type ) {
							case 'number':
								$param_value = (float) $param_value;
								break;

							case 'boolean':
								if ( '' !== $raw_data ) {
									$param_value = 'yes' === $param_value ? 'true' : 'false';
								} else {
									$param_value = 'yes' === $param_value ? true : false;
								}
								break;

							case 'string':
							default:
								$param_value = stripslashes( $param_value );
								break;
						}

						// Check if field is required.
						if ( $required && '' === $param_value ) {
							$param_value = null;
						}

						// Fix the JSON string in value.
						$param_value = flowmattic_convert_strings_to_json( $param_value );

						// Build request data.
						$request_data[ $param_key ] = $param_value;

						if ( false !== strpos( $api_endpoint, $replace_tag ) ) {
							$param_value = is_array( $param_value ) ? wp_json_encode( $param_value ) : $param_value;
							$api_endpoint = str_replace( $replace_tag, $param_value, $api_endpoint );
							if ( isset( $param_option['path_variable'] ) && 'no' !== $param_option['path_variable'] ) {
								continue;
							}
						}

						// Build params array.
						if ( '' !== $param_value ) {
							$body_params[ $param_key ] = $param_value;
						}
					}
				}
			}

			// If raw data is set, use it for request body.
			if ( '' !== $raw_data ) {
				// Strip slashes from raw data if raw data is not valid JSON.
				$raw_data = ( null === json_decode( $raw_data ) ) ? stripslashes( $raw_data ) : $raw_data;

				// Find and update the variables with their values in the request data.
				$raw_data = wp_flowmattic()->variables->find_and_replace( $raw_data );

				// Set the request body.
				$request_args['body'] = $raw_data;
			} elseif ( ! empty( $body_params ) ) {
				// Find and update the variables with their values in the request data.
				$body_params = wp_flowmattic()->variables->find_and_replace( $body_params );

				if ( 'get' === $request_type ) {
					$api_endpoint         = ( false === strpos( $api_endpoint, '?' ) ) ? $api_endpoint . '?' . http_build_query( $body_params ) : $api_endpoint . '&' . http_build_query( $body_params );
					$request_args['body'] = '';
				} elseif ( 'encoded_form_data' === $app_action['content_type'] ) {
						$request_args['body'] = http_build_query( $body_params );
				} elseif ( 'form_data' === $app_action['content_type'] ) {
					$request_args['body'] = $body_params;
				} elseif ( 'xml' === $app_action['content_type'] ) {
					$xml_data = new SimpleXMLElement( '<?xml version="1.0"?><data></data>' );

					foreach ( $body_params as $key => $value ) {
						$xml_data->addChild( $key, $value );
					}

					$xml_output = $xml_data->asXML();

					$request_args['body'] = $xml_output;
				} else {
					$request_args['body'] = wp_json_encode( $body_params );
				}
			}
		}

		// In case of XML, the parameters are already set in the body.
		if ( 'xml' === $app_action['content_type'] && empty( $request_args['body'] ) && '' !== $raw_data ) {
			// Find and update the variables with their values in the request data.
			$request_args['body'] = wp_flowmattic()->variables->find_and_replace( $raw_data );
		}

		$api_endpoint = stripslashes( $api_endpoint );

		// Set the request body.
		$this->request_body = array();
		$this->request_body = ! is_array( $request_args['body'] ) && json_decode( $request_args['body'], true ) ? json_decode( $request_args['body'], true ) : $request_args['body'];

		// Set the connect id for debugging.
		if ( is_array( $this->request_body ) ) {
			$this->request_body['connect_id'] = $connect_id;
		} else {
			$this->request_body = array(
				'connect_id' => $connect_id,
				'request'    => $this->request_body,
			);
		}

		// Make the live request.
		$request_response = $this->request( $request_args, $api_endpoint, $headers, $simple_response );

		if ( 'xml' === $app_action['content_type'] && '' !== $raw_data ) {
			$decode_response = json_decode( $request_response, true );
			$xml_string      = $decode_response['response'];

			$xml_string = preg_replace( '/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $xml_string );
			$xml        = new SimpleXMLElement( $xml_string );
			$json       = wp_json_encode( $xml );
			$array      = json_decode( $json, true );

			$decode_response['xml_json_response'] = wp_json_encode( $array );

			$request_response = wp_json_encode( $decode_response );
		}

		return $request_response;
	}

	/**
	 * Set content types.
	 *
	 * @access public
	 * @since 3.0
	 * @return array
	 */
	public function get_content_types() {
		return array(
			'json'              => array(
				'title' => esc_attr__( 'JSON - With Parameters', 'flowmattic' ),
				'type'  => 'application/json',
			),
			'custom_json'       => array(
				'title' => esc_attr__( 'Custom JSON - Use Your Code', 'flowmattic' ),
				'type'  => 'application/json',
			),
			'form_data'         => array(
				'title' => esc_attr__( 'Form Data', 'flowmattic' ),
				'type'  => 'multipart/form-data',
			),
			'encoded_form_data' => array(
				'title' => esc_attr__( 'URL Encoded Form Data', 'flowmattic' ),
				'type'  => 'application/x-www-form-urlencoded',
			),
			'text'              => array(
				'title' => esc_attr__( 'Text', 'flowmattic' ),
				'type'  => 'text/plain',
			),
			'html'              => array(
				'title' => esc_attr__( 'HTML', 'flowmattic' ),
				'type'  => 'text/html',
			),
			'xml'               => array(
				'title' => esc_attr__( 'XML', 'flowmattic' ),
				'type'  => 'text/xml',
			),
		);
	}

	/**
	 * Process the action request.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $request_args Request params.
	 * @param string $endpoint     Endpoint URL.
	 * @return string
	 */
	public function request( $request_args, $endpoint, $headers, $simple_response ) {
		// If it's a GET request, use wp_remote_get.
		if ( strtoupper( $request_args['method'] ) === 'GET' ) {
			$request       = wp_remote_get( $endpoint, array( 'headers' => $request_args['headers'] ) );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );

			// If the response code indicates a rate limit error, retry after a delay.
			if ( 429 === $response_code ) {
				// $retry_after = wp_remote_retrieve_header( $request, 'Retry-After' );
				// $retry_after = ( $retry_after ) ? $retry_after : 10;

				// Keep trying while retry count is less than 3 and response code is 429.
				while ( 3 > $this->retry_count && 429 === $response_code ) {
					++$this->retry_count;
					flowmattic_delay( 30 );

					// Retry the request.
					$request       = wp_remote_get( $endpoint, array( 'headers' => $request_args['headers'] ) );
					$response_code = wp_remote_retrieve_response_code( $request );
					$request_body  = wp_remote_retrieve_body( $request );
				}

				// Set the error message if the retry count is 3.
				if ( 3 === $this->retry_count ) {
					return wp_json_encode(
						array(
							'status'        => 'error',
							'message'       => 'Rate limit error. Failed after 3 retries.',
							'response'      => $request_body,
							'response_code' => $response_code,
						)
					);
				}
			}
		} else {
			// Initialize cURL.
			$curl = curl_init( $endpoint );

			// Set cURL options.
			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL            => $endpoint,
					CURLOPT_CUSTOMREQUEST  => $request_args['method'],
					CURLOPT_POSTFIELDS     => $request_args['body'],
					CURLOPT_RETURNTRANSFER => true, // Return the response as a string.
					CURLOPT_FOLLOWLOCATION => true, // Follow redirects.
					CURLOPT_MAXREDIRS      => 5, // Maximum number of redirects.
					CURLOPT_TIMEOUT        => 15, // Timeout in seconds.
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_HTTPHEADER     => $headers,
				)
			);

			// Execute the request.
			$request_body  = curl_exec( $curl );
			$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			// Close the cURL.
			curl_close( $curl );

			// If the response code indicates a rate limit error, retry after a delay.
			if ( 429 === $response_code ) {
				// Keep trying while retry count is less than 3 and response code is 429.
				while ( 3 > $this->retry_count && 429 === $response_code ) {
					++$this->retry_count;
					flowmattic_delay( 30 );

					// Retry the request.
					// Initialize cURL.
					$curl = curl_init( $endpoint );

					// Set cURL options.
					curl_setopt_array(
						$curl,
						array(
							CURLOPT_URL            => $endpoint,
							CURLOPT_CUSTOMREQUEST  => $request_args['method'],
							CURLOPT_POSTFIELDS     => $request_args['body'],
							CURLOPT_RETURNTRANSFER => true, // Return the response as a string.
							CURLOPT_FOLLOWLOCATION => true, // Follow redirects.
							CURLOPT_MAXREDIRS      => 5, // Maximum number of redirects.
							CURLOPT_TIMEOUT        => 15, // Timeout in seconds.
							CURLOPT_SSL_VERIFYHOST => 0,
							CURLOPT_SSL_VERIFYPEER => 0,
							CURLOPT_HTTPHEADER     => $headers,
						)
					);

					// Execute the request.
					$request_body  = curl_exec( $curl );
					$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

					// Close the cURL.
					curl_close( $curl );
				}

				// Set the error message if the retry count is 3.
				if ( 3 === $this->retry_count ) {
					return wp_json_encode(
						array(
							'status'        => 'error',
							'message'       => 'Rate limit error. Failed after 3 retries.',
							'response'      => $request_body,
							'response_code' => $response_code,
						)
					);
				}
			}

			// If curl fails, use wp_remote_request.
			if ( false === $request_body ) {
				$request       = wp_remote_request( $endpoint, $request_args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$request_body  = wp_remote_retrieve_body( $request );
			}
		}

		// Check if response is JSON but wrapped within double quotes.
		if ( '"' === substr( $request_body, 0, 1 ) && '"' === substr( $request_body, -1 ) ) {
			$response_json = substr( $request_body, 1, -1 );

			// If response is JSON, decode it.
			if ( is_array( json_decode( stripslashes( $response_json ), true ) ) ) {
				$request_body = stripslashes( $response_json );
			}
		}

		$request_decode = json_decode( $request_body, true );
		$response_array = array();

		$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
		if ( 'error' === $response_status ) {
			$response_array['status']   = 'error';
			$response_array['response'] = is_string( $request_body ) ? $request_body : wp_json_encode( $request_body );
		} else {
			$response_array['status'] = 'success';
		}

		if ( is_array( $request_decode ) ) {
			if ( isset( $request_decode[0] ) && is_array( $request_decode[0] ) && 'No' === $simple_response ) {
				$response_array['response'] = $request_body;
			} else {
				foreach ( $request_decode as $key => $value ) {
					if ( is_int( $key ) && 'No' === $simple_response ) {
						$response_array['response'] = wp_json_encode( $request_decode );
						break;
					}

					if ( is_array( $value ) && 'No' !== $simple_response ) {
						$response_array = flowmattic_recursive_array( $response_array, $key, $value );
					} else {
						$response_array[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
					}
				}
			}
		} else {
			// Add response as it is, if it can't be decoded.
			$response_array['response'] = $request_body;
		}

		// Add response code to the request.
		$response_array['response_code'] = $response_code;

		if ( '' === $request_body ) {
			// If there's no response, return default message.
			return wp_json_encode(
				array(
					'status'   => esc_attr__( 'Success', 'flowmattic' ),
					'response' => esc_attr__( 'Request returned no response data', 'flowmattic' ),
				)
			);
		} else {
			return wp_json_encode( $response_array );
		}
	}

	/**
	 * Get field value.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param array  $param_options  Param options.
	 * @param string $param_value    Param value.
	 * @return array
	 */
	public function get_field_value( $param_options, $param_value ) {
		// Get the field type.
		$field_type = $param_options['field_type'];

		// Check if field is required.
		$required = isset( $param_options['field_required'] ) && 'yes' === $param_options['field_required'] ? true : false;

		// Format data according to the field type.
		switch ( $field_type ) {
			case 'number':
				$field_value = is_numeric( $param_value ) ? (float) $param_value : $param_value;
				break;

			case 'boolean':
				$param_value = 'yes' === $param_value ? true : false;
				break;

			case 'string':
			default:
				$param_value = ! is_array( $param_value ) ? stripslashes( $param_value ) : $param_value;
				break;
		}

		// Check if field is required.
		if ( $required && '' === $param_value ) {
			$param_value = null;
		}

		return $param_value;
	}

	/**
	 * Replace dynamic tags.
	 *
	 * @access public
	 * @since 5.0
	 * @param array  $raw_data_json Raw data JSON.
	 * @param string $param_value   Param value.
	 * @param string $replace_tag   Replace tag.
	 * @param string $param_type    Param type.
	 * @return array
	 */
	public function replace_dynamic_tags( $raw_data_json, $param_value, $replace_tag, $param_type = 'string' ) {
		$is_param_array = false;

		$param_value = ( 'number' === $param_type ) ? (float) $param_value : ( 'boolean' === $param_type ? ( 'yes' === $param_value ? true : false ) : $param_value );

		// Check if the param value is an array.
		if ( is_array( json_decode( stripslashes( $param_value ), true ) ) ) {
			$param_value    = wp_json_encode( json_decode( stripslashes( $param_value ), true ) );
			$is_param_array = true;
		}

		foreach ( $raw_data_json as $key => $value ) {			
			if ( is_array( $value ) ) {
				$raw_data_json[ $key ] = $this->replace_dynamic_tags( $value, $param_value, $replace_tag, $param_type );
			} else {
				if ( '{{' . $key . '}}' === $replace_tag ) {
					$raw_data_json[ $key ] = $param_value;
				} elseif ( false !== strpos( $value, $replace_tag ) ) {
					$raw_data_json[ $key ] = str_replace( $replace_tag, $param_value, $value );

					// Check if the param value is an array.
					if ( $is_param_array && is_array( json_decode( stripslashes( $raw_data_json[ $key ] ), true ) ) ) {
						$raw_data_json[ $key ] = json_decode( $raw_data_json[ $key ], true );
					}
				}

				// Check if the param type is boolean.
				if ( 'boolean' === $param_type && ( 'yes' === $raw_data_json[ $key ] || 'no' === $raw_data_json[ $key ] ) ) {
					$raw_data_json[ $key ] = 'yes' === $raw_data_json[ $key ] ? true : false;
				}
			}
		}

		return $raw_data_json;
	}

	/**
	 * Remove empty items from array.
	 *
	 * @access public
	 * @since 5.0.5
	 * @param array $array Array to remove empty items.
	 * @return array
	 */
	public function remove_empty_items( $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = $this->remove_empty_items( $value );
			}

			if ( '' === $value || null === $value ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 3.0
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

	/**
	 * Return the request data sent to API endpoint.
	 *
	 * @access public
	 * @since 3.1.1
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Custom_User_App();
