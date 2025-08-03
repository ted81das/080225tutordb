<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for API module.
 */
class FlowMattic_Api {
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
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Webhook Outgoing.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'api',
			array(
				'name'          => esc_attr__( 'API by FlowMattic', 'flowmattic' ),
				'icon'          => FLOWMATTIC_PLUGIN_URL . 'inc/apps/api/icon.svg',
				'instructions'  => 'Copy the API URL and send your request to this url from your application or website.',
				'actions'       => $this->get_actions(),
				'content_types' => $this->get_content_types(),
				'base'          => 'core',
				'type'          => 'action',
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
		wp_enqueue_script( 'flowmattic-app-view-api', FLOWMATTIC_PLUGIN_URL . 'inc/apps/api/view-api.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
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
		$api_settings    = isset( $event_data['settings']['actionAppArgs'] ) ? $event_data['settings']['actionAppArgs'] : $event_data['fields'];
		$fields          = $event_data['fields'];
		$webhook_capture = get_option( 'webhook-capture-' . $event_data['workflow_id'], false );

		$workflow_id = $event_data['workflow_id'];

		if ( ! isset( $api_settings['set_parameters'] ) ) {
			$api_settings['set_parameters'] = false;
		}

		$response = $this->run_action_step( $workflow_id, $api_settings, array() );

		return $response;
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
			'get'    => array(
				'title' => esc_attr__( 'GET', 'flowmattic' ),
				'description' => esc_attr__( 'GET request is used to retrieve data from a specified resource.', 'flowmattic' ),
			),
			'post'   => array(
				'title' => esc_attr__( 'POST', 'flowmattic' ),
				'description' => esc_attr__( 'POST request is used to submit data to a specified resource.', 'flowmattic' ),
			),
			'put'    => array(
				'title' => esc_attr__( 'PUT', 'flowmattic' ),
				'description' => esc_attr__( 'PUT request is used to update data to a specified resource.', 'flowmattic' ),
			),
			'delete' => array(
				'title' => esc_attr__( 'DELETE', 'flowmattic' ),
				'description' => esc_attr__( 'DELETE request is used to delete data from a specified resource.', 'flowmattic' ),
			),
			'patch'  => array(
				'title' => esc_attr__( 'PATCH', 'flowmattic' ),
				'description' => esc_attr__( 'PATCH request is used to apply partial modifications to a resource.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Set content types.
	 *
	 * @access public
	 * @since 1.0
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
				'type'  => 'text',
			),
			'html'              => array(
				'title' => esc_attr__( 'HTML', 'flowmattic' ),
				'type'  => 'text/html',
			),
		);
	}

	/**
	 * Run the workflow step.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param array  $api_settings API Settings.
	 * @param array  $capture_data Captured data.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $api_settings, $capture_data ) {
		$request_type    = isset( $api_settings['request_type'] ) ? $api_settings['request_type'] : $api_settings['action'];
		$simple_response = ( ! isset( $api_settings['simple_response'] ) ) ? 'Yes' : $api_settings['simple_response'];
		$api_response    = '';
		$content_type    = ( isset( $api_settings['content_type'] ) && '' !== $api_settings['content_type'] ) ? $api_settings['content_type'] : 'json';
		$settings        = isset( $api_settings['settings'] ) ? $api_settings['settings'] : $api_settings;

		// Update the simple response value for new API settings.
		if ( 'Yes' === $simple_response ) {
			$simple_response = 'simple';
		} else {
			$simple_response = 'item_json';
		}

		// If response_format is set, use that for simple response.
		if ( isset( $api_settings['response_format'] ) && '' !== $api_settings['response_format'] ) {
			$simple_response = $api_settings['response_format'];
		}

		// If endpoint URL is missing, throw an error.
		if ( ! isset( $api_settings['api_endpoint'] ) || '' === $api_settings['api_endpoint'] ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'API Endpoint URL missing',
				)
			);
		} else {
			$api_endpoint = stripslashes( $api_settings['api_endpoint'] );
		}

		$body    = array();
		$headers = array();
		$args    = array(
			'body'        => $body,
			'headers'     => array(
				'User-Agent' => 'FlowMattic',
				'Accept'     => 'application/json',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
		);

		// Trim the space for endpoint url.
		$api_endpoint = trim( $api_endpoint );

		// Set authentication.
		if ( isset( $api_settings['authentication'] ) && 'basic' === $api_settings['authentication'] ) {
			if ( '' !== $api_settings['auth_api_key'] && '' !== $api_settings['auth_api_secret'] ) {
				$args['headers']['Authorization'] = 'Basic ' . base64_encode( $api_settings['auth_api_key'] . ':' . $api_settings['auth_api_secret'] ); // @codingStandardsIgnoreLine

				// Headers used in cURL request.
				$headers[] = 'Authorization: Basic ' . base64_encode( $api_settings['auth_api_key'] . ':' . $api_settings['auth_api_secret'] ); // @codingStandardsIgnoreLine
			}
		} elseif ( isset( $api_settings['authentication'] ) && 'bearer_token' === $api_settings['authentication'] ) {
			if ( '' !== $api_settings['auth_api_bearer_token'] ) {
				$args['headers']['Authorization'] = 'Bearer ' . $api_settings['auth_api_bearer_token'];

				// Headers used in cURL request.
				$headers[] = 'Authorization: Bearer ' . $api_settings['auth_api_bearer_token'];
			}
		} elseif ( isset( $api_settings['authentication'] ) && 'connect' === $api_settings['authentication'] ) {
			if ( '' !== $api_settings['connect_id'] ) {
				$connect_id = $api_settings['connect_id'];

				// Get the connect data.
				$connect_args = array(
					'connect_id' => $connect_id,
				);

				// Get the connect data from db.
				$connect = wp_flowmattic()->connects_db->get( $connect_args );

				// Check if external connect.
				$external_connect = ( isset( $connect->connect_settings['is_external'] ) ) ? flowmattic_get_connects( $connect->connect_settings['external_slug'] ) : false;

				// Get the auth type.
				$auth_type = ! empty( $external_connect ) ? $external_connect['fm_auth_type'] : $connect->connect_settings['fm_auth_type'];

				// Get the auth name.
				$auth_name = ( isset( $connect->connect_settings['auth_name'] ) && '' !== trim( $connect->connect_settings['auth_name'] ) ) ? $connect->connect_settings['auth_name'] : 'Bearer';

				// Set the authorization according to the auth type.
				switch ( $auth_type ) {
					case 'oauth':
						$connect_data = $connect->connect_data;
						$auth_name    = ! empty( $external_connect ) && isset( $external_connect['auth_name'] ) ? $external_connect['auth_name'] : $auth_name;

						if ( ! isset( $connect_data['access_token'] ) ) {
							return wp_json_encode(
								array(
									'status'  => 'error',
									'message' => 'Connect not authenticated.',
								)
							);
						}

						// Add authentication to header.
						$args['headers']['Authorization'] = $auth_name . ' ' . $connect_data['access_token'];

						// Headers used in cURL request.
						$headers[] = 'Authorization: ' . $auth_name . ' ' . $connect_data['access_token'];

						break;

					case 'bearer':
						$token = $connect->connect_settings['auth_bearer_token'];

						// Add authentication to header.
						$args['headers']['Authorization'] = 'Bearer ' . $token;

						// Headers used in cURL request.
						$headers[] = 'Authorization: Bearer ' . $token;

						break;

					case 'basic':
						$api_key    = $connect->connect_settings['auth_api_key'];
						$api_secret = $connect->connect_settings['auth_api_secret'];

						$args['headers']['Authorization'] = 'Basic ' . base64_encode( $api_key . ':' . $api_secret ); // @codingStandardsIgnoreLine

						// Headers used in cURL request.
						$headers[] = 'Authorization: Basic ' . base64_encode( $api_key . ':' . $api_secret ); // @codingStandardsIgnoreLine

						break;

					case 'api':
						// Set API add to setting.
						$add_to    = ! empty( $external_connect ) ? $external_connect['auth_api_addto'] : $connect->connect_settings['auth_api_addto'];
						$api_key   = ! empty( $external_connect ) ? $external_connect['auth_api_key'] : $connect->connect_settings['auth_api_key'];
						$api_value = $connect->connect_settings['auth_api_value'];

						if ( 'query' === $add_to ) {
							$api_endpoint = add_query_arg( $api_key, $api_value, $api_endpoint );
						} else {
							$args['headers'][ $api_key ] = $api_value;

							// Headers used in cURL request.
							$headers[] = $api_key . ': ' . $api_value;
						}

						break;
				}
			}
		}

		if ( isset( $api_settings['set_headers'] ) && ( 'false' !== $api_settings['set_headers'] && false !== $api_settings['set_headers'] ) && ( isset( $api_settings['api_headers'] ) && ! empty( $api_settings['api_headers'] ) ) ) {
			$api_headers = $api_settings['api_headers'];

			foreach ( $api_headers as $header_key => $header_value ) {
				// Headers used in regular requests.
				$args['headers'][ $header_key ] = stripslashes( $header_value );

				// Headers used in cURL request.
				$headers[] = $header_key . ': ' . stripslashes( $header_value );
			}
		}

		if ( 'custom_json' === $content_type && isset( $api_settings['custom_json'] ) && 'get' !== $request_type ) {
			$custom_json  = stripslashes( $api_settings['custom_json'] );
			$args['body'] = $custom_json;
			$content_type = 'json';
		} elseif ( isset( $settings['set_parameters'] ) && false !== $settings['set_parameters'] && 'false' !== $settings['set_parameters'] && '' !== $settings['set_parameters'] ) {
			if ( isset( $settings['api_parameters'] ) && ! empty( $settings['api_parameters'] ) ) {
				$api_parameters = (array) $settings['api_parameters'];

				if ( 'encoded_form_data' === $content_type && 'get' !== $request_type ) {
					$params_array = array();
					foreach ( $api_parameters as $parameter_key => $parameter_value ) {
						$params_array[ $parameter_key ] = stripslashes( $parameter_value );
					}

					$body = http_build_query( $params_array );
				} elseif ( 'html' === $content_type || 'text' === $content_type || 'form_data' === $content_type ) {
					foreach ( $api_parameters as $parameter_key => $parameter_value ) {
						if ( '' !== trim( $parameter_value ) ) {
							$body[ $parameter_key ] = stripslashes( $parameter_value );
						}
					}
				} else {
					foreach ( $api_parameters as $parameter_key => $parameter_value ) {
						if ( '' === trim( $parameter_value ) ) {
							continue;
						}

						$json_param = json_decode( stripslashes( $parameter_value ), true );
						if ( false !== strpos( $parameter_key, '[' ) ) {
							$check_parameter_arr = explode( '[', $parameter_key );
							if ( is_array( $check_parameter_arr ) ) {
								if ( ! isset( $body[ $check_parameter_arr[0] ] ) ) {
									$body[ $check_parameter_arr[0] ] = array();
								}

								// Fix typecasting on parameter value.
								$parameter_value = is_numeric( $parameter_value ) ? (float) $parameter_value : stripslashes( $parameter_value );

								$body[ $check_parameter_arr[0] ][ $check_parameter_arr[1] ] = is_array( $json_param ) ? $json_param : $parameter_value;
							}
						} elseif ( is_array( $json_param ) ) {
							foreach ( $json_param as $child_key => $child_value ) {
								$body[ $parameter_key ][ $child_key ] = $child_value;
							}
						} elseif ( is_numeric( $parameter_value ) ) {
							if ( false !== strpos( $parameter_value, '.' ) ) {
								$body[ $parameter_key ] = $parameter_value;
							} else {
								$body[ $parameter_key ] = (int) $parameter_value;
							}
						} elseif ( 'true' === $parameter_value || 'false' === $parameter_value ) {
							$body[ $parameter_key ] = ( 'true' === $parameter_value ) ? true : false;
						} else {
							$body[ $parameter_key ] = stripslashes( $parameter_value );
						}
					}
				}

				if ( 'json' === $content_type && 'get' !== $request_type && 'delete' !== $request_type ) {
					$args['body'] = wp_json_encode( $body );
				} else {
					$args['body'] = $body;
				}
			}
		}

		// Set the request body.
		$this->request_body = ! is_array( $args['body'] ) ? json_decode( $args['body'], true ) : $args['body'];

		// Add endpoint URL to request body.
		$this->request_body['endpoint_url'] = $api_endpoint;

		// Add headers to request body.
		if ( isset( $api_settings['api_headers'] ) && ! empty( $api_settings['api_headers'] ) ) {
			$this->request_body['headers'] = $api_settings['api_headers'];
		}

		// Add Simple Response to request body.
		$this->request_body['simple_response'] = $simple_response;

		switch ( $request_type ) {
			case 'get':
				$endpoint_url  = $api_endpoint;
				$request       = wp_remote_get( $endpoint_url, $args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$response_body = wp_remote_retrieve_body( $request );
				$response      = $response_body;

				// If the response code indicates a rate limit error, retry after a delay.
				if ( 429 === $response_code ) {
					// $retry_after = wp_remote_retrieve_header( $request, 'Retry-After' );
					// $retry_after = ( $retry_after ) ? $retry_after : 10;

					// Keep trying while retry count is less than 3 and response code is 429.
					while ( 3 > $this->retry_count && 429 === $response_code ) {
						++$this->retry_count;
						flowmattic_delay( 30 );

						// Retry the request.
						$request       = wp_remote_get( $endpoint_url, $args );
						$response_code = wp_remote_retrieve_response_code( $request );
						$response_body = wp_remote_retrieve_body( $request );
						$response      = $response_body;
					}

					// Set the error message if the retry count is 3.
					if ( 3 === $this->retry_count ) {
						$api_response = wp_json_encode(
							array(
								'status'        => 'error',
								'message'       => 'Rate limit error. Failed after 3 retries.',
								'response'      => $response_body,
								'response_code' => $response_code,
							)
						);

						break;
					}
				}

				// Initial empty response array.
				$processed_response = array(
					'fm_raw_response' => $response_body,
				);

				$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
				if ( 'error' === $response_status ) {
					if ( 404 === $response_code ) {
						$response = wp_json_encode(
							array(
								'status'  => 'error',
								'message' => '404 - Resource not found',
							)
						);
					} else {
						$response = json_decode( $response_body, true );

						if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
							$response = wp_json_encode( $response['error'] );
						} elseif ( is_array( $response ) ) {
							foreach ( $response as $key => $value ) {
								if ( is_array( $value ) && 'simple' === $simple_response ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
								} else {
									$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
								}
							}

							$response = wp_json_encode( $processed_response );
						} else {
							$response = wp_json_encode(
								array(
									'response' => $response_body,
								),
							);
						}
					}
				} else {
					$response_array     = json_decode( $response_body, true );
					$processed_response = array(
						'fm_raw_response' => $response_body,
					);

					if ( is_array( $response_array ) ) {
						foreach ( $response_array as $key => $value ) {
							if ( is_int( $key ) && 'item_json' === $simple_response ) {
								$processed_response['response'] = wp_json_encode( $response_array );
								break;
							}

							if ( is_array( $value ) && 'simple' === $simple_response ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} elseif ( is_array( $value ) ) {
									$processed_response[ $key ] = wp_json_encode( $value );
							} else {
								$processed_response[ $key ] = $value;
							}
						}

						$response = wp_json_encode( $processed_response );
					} else {
						$response = wp_json_encode(
							array(
								'response' => esc_html( $response_body ),
							)
						);
					}
				}

				$api_response = $response;

				break;

			case 'post':
				$content_type = $this->get_content_types()[ $content_type ]['type'];

				$no_content_type = true;
				foreach ( $headers as $key => $header ) {
					if ( 'Content-Type: ' . $content_type === $header ) {
						$no_content_type = false;
					}
				}

				if ( $no_content_type ) {
					$headers[] = 'Content-Type: ' . $content_type;
				}

				// Set user agent.
				$headers[] = 'User-Agent: FlowMattic';

				// If body is not set, set as empty.
				if ( ! isset( $args['body'] ) ) {
					$args['body'] = 'null';
				}

				// @codingStandardsIgnoreStart
				$curl = curl_init( $api_endpoint );
				curl_setopt( $curl, CURLOPT_URL, $api_endpoint );
				curl_setopt( $curl, CURLOPT_POST, true );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
				curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $args['body'] );
				curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
				curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

				$response_body = curl_exec( $curl );
				$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

				curl_close( $curl );
				// @codingStandardsIgnoreEnd

				// If the response code indicates a rate limit error, retry after a delay.
				if ( 429 === $response_code ) {
					// Keep trying while retry count is less than 3 and response code is 429.
					while ( 3 > $this->retry_count && 429 === $response_code ) {
						++$this->retry_count;
						flowmattic_delay( 30 );

						// @codingStandardsIgnoreStart
						$curl = curl_init( $api_endpoint );
						curl_setopt( $curl, CURLOPT_URL, $api_endpoint );
						curl_setopt( $curl, CURLOPT_POST, true );
						curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $curl, CURLOPT_POSTFIELDS, $args['body'] );
						curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
						curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );

						$response_body = curl_exec( $curl );
						$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

						curl_close( $curl );
						// @codingStandardsIgnoreEnd
					}

					// Set the error message if the retry count is 3.
					if ( 3 === $this->retry_count ) {
						$api_response = wp_json_encode(
							array(
								'status'        => 'error',
								'message'       => 'Rate limit error. Failed after 3 retries.',
								'response'      => $response_body,
								'response_code' => $response_code,
							)
						);

						break;
					}
				}

				$processed_response = array(
					'fm_raw_response' => $response_body,
				);

				// Check if response is JSON but wrapped within double quotes.
				if ( '"' === substr( $response_body, 0, 1 ) && '"' === substr( $response_body, -1 ) ) {
					$response_json = substr( $response_body, 1, -1 );

					// If response is JSON, decode it.
					if ( is_array( json_decode( stripslashes( $response_json ), true ) ) ) {
						$response_body = stripslashes( $response_json );
					}
				}

				$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
				if ( 'error' === $response_status ) {
					if ( 404 === $response_code ) {
						$response = wp_json_encode(
							array(
								'status'  => 'error',
								'message' => '404 - Resource not found',
							)
						);
					} else {
						$response = json_decode( $response_body, true );

						if ( is_array( $response ) ) {
							foreach ( $response as $key => $value ) {
								$json_value = ( ! is_array( $value ) ) ? json_decode( stripslashes( $value ), true ) : $value;
								if ( is_array( $json_value ) ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $json_value );
								} else {
									$processed_response[ $key ] = $value;
								}
							}

							$response = wp_json_encode( $processed_response );
						} else {
							$response = wp_json_encode(
								array(
									'status'     => 'error',
									'error_code' => $response_code,
									'message'    => $response_body,
								)
							);
						}
					}
				} else {
					$response_array = json_decode( $response_body, true );

					if ( is_array( $response_array ) ) {
						foreach ( $response_array as $key => $value ) {
							if ( is_int( $key ) && 'item_json' === $simple_response ) {
								$processed_response['response'] = wp_json_encode( $response_array );
								break;
							}

							$json_value = ( ! is_array( $value ) && $value ) ? json_decode( stripslashes( $value ), true ) : $value;

							if ( is_array( $value ) && empty( $value ) ) {
								$processed_response[ $key ] = wp_json_encode( $value );
							} elseif ( is_array( $json_value ) && 'simple' === $simple_response ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $json_value );
							} elseif ( is_array( $value ) && 'simple' === $simple_response ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} elseif ( is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
									$processed_response[ $key ] = wp_json_encode( $value );
							} elseif ( is_array( $value ) && ! empty( $value ) ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} else {
								$processed_response[ $key ] = $value;
							}
						}

						$response = wp_json_encode( $processed_response );
					} else {
						$response = wp_json_encode(
							array(
								'response' => esc_html( $response_body ),
							)
						);
					}
				}

				$api_response = $response;

				break;

			case 'put':
				$content_type = $this->get_content_types()[ $content_type ]['type'];

				$args['headers']['Content-Type'] = $content_type;
				$args['method']                  = 'PUT';

				$endpoint_url  = $api_endpoint;
				$request       = wp_remote_request( $endpoint_url, $args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$response_body = wp_remote_retrieve_body( $request );
				$response      = $response_body;

				// If the response code indicates a rate limit error, retry after a delay.
				if ( 429 === $response_code ) {
					// Keep trying while retry count is less than 3 and response code is 429.
					while ( 3 > $this->retry_count && 429 === $response_code ) {
						++$this->retry_count;
						flowmattic_delay( 30 );

						$request       = wp_remote_request( $endpoint_url, $args );
						$response_code = wp_remote_retrieve_response_code( $request );
						$response_body = wp_remote_retrieve_body( $request );
						$response      = $response_body;
					}

					// Set the error message if the retry count is 3.
					if ( 3 === $this->retry_count ) {
						$api_response = wp_json_encode(
							array(
								'status'        => 'error',
								'message'       => 'Rate limit error. Failed after 3 retries.',
								'response'      => $response_body,
								'response_code' => $response_code,
							)
						);

						break;
					}
				}

				$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
				if ( 'error' === $response_status ) {
					if ( 404 === $response_code ) {
						$response = wp_json_encode(
							array(
								'status'  => 'error',
								'message' => '404 - Resource not found',
							)
						);
					} else {
						$response = json_decode( $response_body, true );

						if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
							$response = wp_json_encode( $response['error'] );
						} elseif ( is_array( $response ) ) {
							foreach ( $response as $key => $value ) {
								if ( is_array( $value ) && 'simple' === $simple_response ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
								} else {
									$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
								}
							}

							$response = wp_json_encode( $processed_response );
						} else {
							$response = $response_body;
						}
					}
				} else {
					$response_array     = json_decode( $response_body, true );
					$processed_response = array();

					if ( is_array( $response_array ) ) {
						foreach ( $response_array as $key => $value ) {
							if ( is_int( $key ) && 'item_json' === $simple_response ) {
								$processed_response['response'] = wp_json_encode( $response_array );
								break;
							}

							if ( is_array( $value ) && 'simple' === $simple_response ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} else {
								$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
							}
						}

						$response = wp_json_encode( $processed_response );
					} else {
						$response = $response_body;
					}
				}

				$api_response = $response;

				break;

			case 'delete':
				$args['method'] = 'DELETE';

				$content_type = $this->get_content_types()[ $content_type ]['type'];

				$args['headers']['Content-Type'] = $content_type;

				$endpoint_url  = $api_endpoint;
				$request       = wp_remote_request( $endpoint_url, $args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$response_body = wp_remote_retrieve_body( $request );
				$response      = $response_body;

				$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
				if ( 'error' === $response_status ) {
					if ( 404 === $response_code ) {
						$response = wp_json_encode(
							array(
								'status'  => 'error',
								'message' => '404 - Resource not found',
							)
						);
					} else {
						$response = json_decode( $response_body, true );

						if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
							$response = wp_json_encode( $response['error'] );
						} elseif ( is_array( $response ) ) {
							foreach ( $response as $key => $value ) {
								if ( is_array( $value ) && 'simple' === $simple_response ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
								} else {
									$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
								}
							}

							$response = wp_json_encode( $processed_response );
						} else {
							$response = $response_body;
						}
					}
				} else {
					$response_array     = json_decode( $response_body, true );
					$processed_response = array();

					if ( is_array( $response_array ) ) {
						foreach ( $response_array as $key => $value ) {
							if ( is_int( $key ) && 'item_json' === $simple_response ) {
								$processed_response['response'] = wp_json_encode( $response_array );
								break;
							}

							if ( is_array( $value ) && 'simple' !== $simple_response ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} else {
								$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
							}
						}

						$response = wp_json_encode( $processed_response );
					} else {
						$response = $response_body;
					}
				}

				$api_response = $response;

				break;

			case 'patch':
				$content_type = $this->get_content_types()[ $content_type ]['type'];

				$args['headers']['Content-Type'] = $content_type;
				$args['method']                  = 'PATCH';

				$endpoint_url  = $api_endpoint;
				$request       = wp_remote_request( $endpoint_url, $args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$response_body = wp_remote_retrieve_body( $request );
				$response      = $response_body;

				$response_status = ( $response_code >= 200 && $response_code <= 300 ) ? 'success' : 'error';
				if ( 'error' === $response_status ) {
					if ( 404 === $response_code ) {
						$response = wp_json_encode(
							array(
								'status'  => 'error',
								'message' => '404 - Resource not found',
							)
						);
					} else {
						$response = json_decode( $response_body, true );

						if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
							$response = wp_json_encode( $response['error'] );
						} elseif ( is_array( $response ) ) {
							foreach ( $response as $key => $value ) {
								if ( is_array( $value ) && 'simple' !== $simple_response ) {
									$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
								} else {
									$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
								}
							}

							$response = wp_json_encode( $processed_response );
						} else {
							$response = $response_body;
						}
					}
				} else {
					$response_array     = json_decode( $response_body, true );
					$processed_response = array();

					if ( is_array( $response_array ) ) {
						foreach ( $response_array as $key => $value ) {
							if ( is_int( $key ) && 'item_json' === $simple_response ) {
								$processed_response['response'] = wp_json_encode( $response_array );
								break;
							}

							if ( is_array( $value ) && 'simple' !== $simple_response ) {
								$processed_response = flowmattic_recursive_array( $processed_response, $key, $value );
							} else {
								$processed_response[ $key ] = ( is_array( $value ) ) ? wp_json_encode( $value ) : $value;
							}
						}

						$response = wp_json_encode( $processed_response );
					} else {
						$response = $response_body;
					}
				}

				$api_response = $response;

				break;

		}

		// If there's no response, return default message.
		if ( '' === $api_response ) {
			$api_response = wp_json_encode(
				array(
					'status'   => 'success',
					'no_reply' => esc_attr__( 'Request has no response data', 'flowmattic' ),
				)
			);
		}

		if ( 'ok' === strtolower( $api_response ) ) {
			$api_response = wp_json_encode(
				array(
					'response' => esc_html( $api_response ),
				)
			);
		}

		return $api_response;
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

new FlowMattic_Api();
