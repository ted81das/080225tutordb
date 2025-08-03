<?php
/**
 * MCP Server for FlowMattic
 * Supports both SSE and HTTP Stream Transport
 *
 * @package flowmattic
 * @since 5.2.0
 */

class FlowMattic_MCP_Server {
	/**
	 * Instance of the FlowMattic MCP Server
	 *
	 * @var FlowMattic_MCP_Server
	 * @since 5.2.0
	 */
	public static $instance;

	/**
	 * Session storage for OpenAI
	 *
	 * @var array
	 * @since 5.2.0
	 */
	private $sessions = array();

	/**
	 * Current session ID
	 *
	 * @var string
	 * @since 5.2.0
	 */
	private $current_session_id;

	/**
	 * MCP Client ID
	 *
	 * @var string
	 * @since 5.2.0
	 */
	public $mcp_client_id;

	/**
	 * MCP Client Name
	 *
	 * @var string
	 * @since 5.2.0
	 */
	public $mcp_client_name;

	/**
	 * Plugin tools
	 *
	 * @var array
	 * @since 5.2.0
	 */
	public static $plugin_tools = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_mcp_routes' ) );
		add_action( 'init', array( $this, 'add_cors_headers' ) );

		// AJAX handlers for admin
		add_action( 'wp_ajax_flowmattic_mcp_create_tool', array( $this, 'create_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_get_tool', array( $this, 'get_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_update_tool', array( $this, 'update_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_test_tool', array( $this, 'test_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_duplicate_tool', array( $this, 'duplicate_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_delete_tool', array( $this, 'delete_tool' ) );
		add_action( 'wp_ajax_flowmattic_mcp_update_server_settings', array( $this, 'update_server_settings' ) );

		add_action( 'wp_ajax_flowmattic_mcp_get_execution_details', array( $this, 'get_execution_details' ) );
		add_action( 'wp_ajax_flowmattic_mcp_get_execution_history', array( $this, 'get_execution_history' ) );
		add_action( 'wp_ajax_flowmattic_mcp_export_history', array( $this, 'export_execution_history' ) );

		add_action( 'admin_init', array( $this, 'schedule_mcp_cleanup' ) );
		add_action( 'flowmattic_mcp_cleanup_executions', array( $this, 'cleanup_old_mcp_executions' ) );

		// Add cleanup actions
		add_action( 'flowmattic_cleanup_mcp_response', array( $this, 'cleanup_mcp_response_files' ) );
		add_action( 'flowmattic_cleanup_mcp_sessions', array( $this, 'cleanup_old_sessions' ) );

		// Schedule cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'flowmattic_cleanup_mcp_sessions' ) ) {
			wp_schedule_event( time(), 'hourly', 'flowmattic_cleanup_mcp_sessions' );
		}
	}

	/**
	 * Get instance of the FlowMattic MCP Server
	 *
	 * @access public
	 * @since 5.2.0
	 * @return FlowMattic_MCP_Server
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Plugin additional tools from other plugins
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $plugin_name Name of the plugin
	 * @param array  $tools       Array of tools to add
	 * @return void
	 */
	public static function add_plugin_tools( $plugin_name, $tools ) {
		if ( ! is_array( $tools ) || empty( $tools ) ) {
			return;
		}

		// Tools schema.
		$tools_schema = array(
			'name'          => 'string',
			'description'   => 'string',
			'function_name' => 'string',
			'parameters'    => 'array',
			'request_type'  => 'string',
		);

		// Loop through tools and ensure they have required properties
		foreach ( $tools as $key => $tool ) {
			if ( ! is_array( $tool ) || empty( $tool ) ) {
				continue;
			}

			// Validate tool properties
			foreach ( $tools_schema as $property => $type ) {
				if ( ! isset( $tool[ $property ] ) || gettype( $tool[ $property ] ) !== $type ) {
					unset( $tools[ $key ] );
					break;
				}
			}

			// Prepare tool data.
			$tool_slug = sanitize_title( $tool['name'] );
			$tool_data = array(
				'name'          => $tool['name'],
				'description'   => $tool['description'],
				'function_name' => $tool['function_name'],
				'parameters'    => $tool['parameters'],
				'execution_method' => 'php_function',
				'request_type'  => isset( $tool['request_type'] ) ? $tool['request_type'] : 'individual',
			);

			// Add tool to the plugin tools array
			if ( ! isset( self::$plugin_tools[ $tool_slug ] ) ) {
				self::$plugin_tools[ $tool_slug ] = array(
					'plugin' => $plugin_name,
					'tool'   => $tool_data,
				);
			}
		}
	}

	/**
	 * Get all plugin tools
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id Server ID
	 * @return array
	 */
	public function get_all_plugin_tools( $server_id ) {
		return self::$plugin_tools;
	}

	/**
	 * Add CORS headers for preflight requests
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function add_cors_headers() {
		if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
			$this->send_cors_headers();
			exit;
		}
	}

	/**
	 * Register MCP routes for both Claude and OpenAI
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function register_mcp_routes() {
		// FlowMattic MCP endpoint supporting both approaches
		register_rest_route(
			'flowmattic/v1',
			'/mcp-server/(?P<server_id>[a-zA-Z0-9_-]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_get_request' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'handle_post_request' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'handle_delete_request' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'OPTIONS',
					'callback'            => array( $this, 'handle_options_request' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		// Endpoint to support SSE transport
		register_rest_route(
			'flowmattic/v1',
			'/mcp-server/(?P<server_id>[a-zA-Z0-9_-]+)/sse',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_sse_transport' ),
					'permission_callback' => '__return_true',
				),
			),
		);

		// Endpoint to handle messages for SSE
		register_rest_route(
			'flowmattic/v1',
			'/mcp-server/(?P<server_id>[a-zA-Z0-9_-]+)/message',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'handle_sse_message' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Handle SSE transport requests
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return void
	 */
	public function handle_sse_transport( $request ) {
		// Perform cleanup operations before starting SSE
		// $this->manual_cleanup_for_testing();
		// $this->force_cleanup_all_connections();

		// Set headers and prepare for SSE session
		@ini_set( 'max_execution_time', 0 );
		@ini_set( 'memory_limit', '512M' );

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		remove_all_actions( 'shutdown' );
		remove_all_actions( 'wp_footer' );

		$this->send_cors_headers();

		header( 'Content-Type: text/event-stream; charset=utf-8' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' );

		if ( function_exists( 'apache_setenv' ) ) {
			apache_setenv( 'no-gzip', '1' );
		}

		// Disable output buffering and compression for real-time updates
		@ini_set( 'output_buffering', 'off' );
		@ini_set( 'zlib.output_compression', false );

		$server_id                = $request['server_id'];
		$session_id               = $this->generate_session_id();
		$this->current_session_id = $session_id;

		// Build and send endpoint immediately - NO delays
		$message_endpoint = rest_url( "flowmattic/v1/mcp-server/{$server_id}/message" ) . "?sessionId={$session_id}";
		$message_endpoint = apply_filters( 'flowmattic_webhook_url', $message_endpoint );

		echo "event: endpoint\n";
		echo "data: {$message_endpoint}\n\n";

		if ( ob_get_level() ) {
			ob_flush();
		}
		flush();

		// Start the main loop for SSE
		$start_time               = time();
		$max_duration             = 10; // 10 seconds
		$heartbeat_interval       = 5; // 5 seconds
		$last_heartbeat           = 0;
		$temp_file                = sys_get_temp_dir() . "/mcp_response_{$session_id}.json";
		$consecutive_failures     = 0;
		$max_consecutive_failures = 3;

		while ( time() - $start_time < $max_duration ) {
			$current_time = time();

			// PRIORITY: Check for response file FIRST and FAST
			if ( file_exists( $temp_file ) ) {
				$response_content = file_get_contents( $temp_file );
				if ( $response_content ) {
					$response_data = json_decode( $response_content, true );
					if ( $response_data ) {
						echo "event: message\n";
						echo 'data: ' . wp_json_encode( $response_data ) . "\n\n";

						if ( ob_get_level() ) {
							ob_flush();
						}
						flush();

						// Check write success
						if ( ! connection_aborted() ) {
							$consecutive_failures = 0;

							// Clean up file immediately
							unlink( $temp_file );
						} else {
							++$consecutive_failures;
							if ( $consecutive_failures >= $max_consecutive_failures ) {
								// Fast SSE connection lost for session
								break;
							}
						}
					}
				}
			}

			// Heartbeat only when needed
			if ( $current_time - $last_heartbeat >= $heartbeat_interval ) {
				echo "event: heartbeat\n";
				echo 'data: ' . wp_json_encode(
					array(
						'time'      => $current_time,
						'sessionId' => $session_id,
					)
				) . "\n\n";

				if ( ob_get_level() ) {
					ob_flush();
				}
				flush();

				if ( ! connection_aborted() ) {
					$last_heartbeat       = $current_time;
					$consecutive_failures = 0;
					// Fast SSE heartbeat for session
				} else {
					++$consecutive_failures;
					if ( $consecutive_failures >= $max_consecutive_failures ) {
						// Fast SSE heartbeat failed for session
						break;
					}
				}
			}

			// FAST checking - 25ms (optimal balance)
			usleep( 25000 );
		}

		// Minimal cleanup on exit - only this session
		if ( file_exists( $temp_file ) ) {
			unlink( $temp_file );
		}

		echo "event: disconnect\n";
		echo 'data: ' . wp_json_encode(
			array(
				'reason'    => $consecutive_failures >= $max_consecutive_failures ? 'disconnect' : 'timeout',
				'sessionId' => $session_id,
			)
		) . "\n\n";

		if ( ob_get_level() ) {
			ob_flush();
		}
		flush();

		exit;
	}

	/**
	 * Handle SSE message requests - Send responses immediately via file system
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function handle_sse_message( WP_REST_Request $request ) {
		// Send CORS headers
		$this->send_cors_headers();

		$server_id  = $request['server_id'];
		$session_id = $request->get_param( 'sessionId' );

		if ( ! $session_id ) {
			return new WP_REST_Response( array( 'error' => 'Session ID required' ), 400 );
		}

		// Skip session validation for speed - just proceed
		$this->current_session_id = $session_id;
		$start_time               = microtime( true );

		// Fast request processing
		$body = $request->get_body();
		if ( empty( $body ) ) {
			return new WP_REST_Response( array( 'error' => 'Request body required' ), 400 );
		}

		$data = json_decode( $body, true );
		if ( ! $data || ! isset( $data['method'] ) ) {
			return new WP_REST_Response( array( 'error' => 'Invalid request' ), 400 );
		}

		$method = $data['method'];
		$params = $data['params'] ?? array();
		$id     = $data['id'] ?? 1;

		// Handle methods quickly
		$response = null;
		switch ( $method ) {
			case 'initialize':
				$response = $this->create_initialize_response( $server_id, $params, $id );
				break;
			case 'notifications/initialized':
				return new WP_REST_Response( array( 'status' => 'acknowledged' ), 200 );
			case 'tools/list':
				$response = $this->create_tools_list_response( $server_id, $id );
				break;
			case 'tools/call':
				$response = $this->create_tools_call_response( $server_id, $params, $id );
				break;
			case 'notifications/cancelled':
				$response = array(
					'jsonrpc' => '2.0',
					'result'  => array( 'acknowledged' => true ),
					'id'      => $id,
				);

				// Clean up immediately
				$this->cleanup_sse_connection( $session_id );

				break;
			default:
				$response = array(
					'jsonrpc' => '2.0',
					'error'   => array(
						'code'    => -32601,
						'message' => "Method not found: {$method}",
					),
					'id'      => $id,
				);
		}

		if ( $response ) {
			// FASTEST possible file write
			$temp_file = sys_get_temp_dir() . "/mcp_response_{$session_id}.json";
			$json_data = wp_json_encode( $response );

			if ( file_put_contents( $temp_file, $json_data ) !== false ) {
				$processing_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );
			}
		}

		return new WP_REST_Response(
			array(
				'status' => 'processed',
				'method' => $method,
			),
			200
		);
	}

	/**
	 * Send message immediately via temp file for SSE connection to pick up
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $session_id Session ID
	 * @param array  $response Response data
	 * @return void
	 */
	private function send_sse_message_immediately( $session_id, $response ) {
		$temp_file = sys_get_temp_dir() . "/mcp_response_{$session_id}.json";

		// Use atomic write with error handling
		$temp_write_file = $temp_file . '.tmp';
		$json_data       = wp_json_encode( $response );

		if ( $json_data === false ) {
			// Failed to encode JSON for SSE response
			return;
		}

		$bytes_written = @file_put_contents( $temp_write_file, $json_data );

		if ( $bytes_written === false ) {
			// Failed to write SSE response temp file
			return;
		}

		// Atomic move
		if ( @rename( $temp_write_file, $temp_file ) ) {
			// SSE response written successfully
		} else {
			// Failed to move SSE response file
			@unlink( $temp_write_file ); // Clean up temp file
		}
	}

	/**
	 * Background cleanup (runs separately, not per-request)
	 * Schedule this to run every 5 minutes, NOT on every request
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function background_cleanup_old_files() {
		$temp_dir = sys_get_temp_dir();
		$pattern  = $temp_dir . '/mcp_response_*.json';
		$files    = glob( $pattern );
		$cleaned  = 0;

		if ( $files ) {
			$cutoff_time = time() - 300; // 5 minutes
			foreach ( $files as $file ) {
				if ( filemtime( $file ) < $cutoff_time ) {
					if ( unlink( $file ) ) {
						++$cleaned;
					}
				}
			}
		}

		if ( $cleaned > 0 ) {
			// Background cleanup: removed {$cleaned} old response files
		}
	}

	/**
	 * Manual cleanup for testing - call this BETWEEN tests, not during
	 *
	 * @access public
	 * @since 5.2.0
	 * @return int Number of files cleaned
	 */
	public function manual_cleanup_for_testing() {
		$temp_dir = sys_get_temp_dir();
		$pattern  = $temp_dir . '/mcp_response_*.json*';
		$files    = glob( $pattern );
		$cleaned  = 0;

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( unlink( $file ) ) {
					++$cleaned;
				}
			}
		}

		return $cleaned;
	}

	/**
	 * Create initialize response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id Server ID
	 * @param array  $params Parameters
	 * @param mixed  $id Request ID
	 * @return array
	 */
	private function create_initialize_response( $server_id, $params, $id ) {
		// Use client's protocol version if provided
		$client_protocol_version = $params['protocolVersion'] ?? '2024-11-05';

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'protocolVersion' => $client_protocol_version,
				'capabilities'    => array(
					'tools'     => array( 'listChanged' => true ),
					'resources' => array(
						'subscribe'   => false,
						'listChanged' => false,
					),
					'prompts'   => array( 'listChanged' => false ),
				),
				'serverInfo'      => array(
					'name'    => 'FlowMattic MCP Server',
					'version' => '1.0.0',
				),
			),
			'id'      => $id,
		);

		return $response;
	}

	/**
	 * Create tools list response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id Server ID
	 * @param mixed  $id Request ID
	 * @return array
	 */
	private function create_tools_list_response( $server_id, $id ) {
		$all_tools = $this->get_all_tools( $server_id );
		$tools     = array();

		foreach ( $all_tools as $tool ) {
			if ( is_object( $tool ) ) {
				$tools[] = array(
					'name'        => str_replace( array( ' ', '-' ), '_', strtolower( $tool->mcp_tool_name ) ),
					'description' => $tool->mcp_tool_description ?: "Execute {$tool->mcp_tool_name} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ),
				);
			} else {
				$tools[] = array(
					'name'        => str_replace( array( ' ', '-' ), '_', strtolower( $tool['name'] ) ),
					'description' => $tool['description'] ?: "Execute {$tool['name']} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool['parameters'] ?? array() ),
				);
			}
		}

		return array(
			'jsonrpc' => '2.0',
			'result'  => array( 'tools' => $tools ),
			'id'      => $id,
		);
	}

	/**
	 * Create tools call response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id Server ID
	 * @param array  $params Parameters
	 * @param mixed  $id Request ID
	 * @return array
	 */
	private function create_tools_call_response( $server_id, $params, $id ) {
		$tool_name = $params['name'] ?? '';
		$arguments = $params['arguments'] ?? array();

		if ( empty( $tool_name ) ) {
			return array(
				'jsonrpc' => '2.0',
				'error'   => array(
					'code'    => -32602,
					'message' => 'Tool name required',
				),
				'id'      => $id,
			);
		}

		// Find the tool
		$all_tools = $this->get_all_tools( $server_id );
		$tool      = null;

		foreach ( $all_tools as $t ) {
			$t_name = is_object( $t ) ? $t->mcp_tool_name : $t['name'];
			$t_name = str_replace( array( ' ', '-' ), '_', strtolower( $t_name ) );
			if ( $t_name === $tool_name ) {
				$tool = $t;
				break;
			}
		}

		if ( ! $tool ) {
			return array(
				'jsonrpc' => '2.0',
				'error'   => array(
					'code'    => -32602,
					'message' => "Tool '{$tool_name}' not found",
				),
				'id'      => $id,
			);
		}

		// Execute the tool
		$result = $this->execute_tool( $tool, $arguments, $server_id );

		if ( isset( $result['error'] ) && $result['error'] ) {
			return array(
				'jsonrpc' => '2.0',
				'error'   => array(
					'code'    => -32603,
					'message' => $result['message'],
				),
				'id'      => $id,
			);
		}

		return array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'content' => array(
					array(
						'type' => 'text',
						'text' => wp_json_encode( $result ),
					),
				),
			),
			'id'      => $id,
		);
	}

	/**
	 * Create other method response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $method Method name
	 * @param mixed  $id Request ID
	 * @return array
	 */
	private function create_other_method_response( $method, $id ) {
		$default_responses = array(
			'resources/list'          => array( 'resources' => array() ),
			'resources/read'          => array( 'content' => array() ),
			'prompts/list'            => array( 'prompts' => array() ),
			'prompts/get'             => array( 'content' => array() ),
			'ping'                    => array( 'status' => 'pong' ),
			'notifications/cancelled' => array( 'acknowledged' => true ),
		);

		$result = $default_responses[ $method ] ?? array( 'status' => 'ok' );

		return array(
			'jsonrpc' => '2.0',
			'result'  => $result,
			'id'      => $id,
		);
	}

	/**
	 * Handle OPTIONS requests (CORS preflight)
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function handle_options_request( $request ) {
		$this->send_cors_headers();
		return new WP_REST_Response( '', 200 );
	}

	/**
	 * Handle GET requests - Claude manifest or OpenAI SSE
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_get_request( $request ) {
		$this->send_cors_headers();

		// Detect client type first
		$this->detect_client_type( $request );

		$server_id = $request['server_id'];

		if ( $this->mcp_client_id === 'claude' ) {
			// Claude expects manifest on GET
			return $this->get_claude_manifest( $request );
		} else {
			// OpenAI SSE stream
			return $this->handle_openai_sse( $request );
		}
	}

	/**
	 * Handle POST requests - Route based on client type
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_post_request( $request ) {
		// Get server ID from request
		$server_id = $request['server_id'];

		// Get server settings
		$server_settings = flowmattic_get_mcp_server_data( $server_id );

		// If server is not active, return empty manifest
		if ( ! $server_settings || ! $server_settings['active'] ) {
			return new WP_REST_Response( array( 'status' => 'inactive' ), 403 );
		}

		$this->send_cors_headers();

		// Detect client type first
		$this->detect_client_type( $request );

		$body = $request->get_body();

		// Decode JSON body if present and get the client name.
		if ( ! empty( $body ) ) {
			$data                  = json_decode( $body, true );
			$this->mcp_client_name = isset( $data['client_name'] ) ? sanitize_text_field( $data['client_name'] ) : '';
		}

		if ( $this->mcp_client_id === 'claude' ) {
			// Claude JSON-RPC handling
			return $this->handle_claude_post( $server_id, $request );
		} else {
			// OpenAI HTTP Stream Transport handling
			return $this->handle_openai_post( $server_id, $request );
		}
	}

	/**
	 * Enhanced client detection
	 *
	 * @access private
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return void
	 */
	private function detect_client_type( $request ) {
		$headers    = $request->get_headers();
		$user_agent = isset( $headers['user_agent'] ) ? $headers['user_agent'][0] : '';

		// If user agent is node, set it to Claude
		if ( strpos( $user_agent, 'node' ) !== false ) {
			$this->mcp_client_id = 'claude';

			return;
		}

		// Extract base user agent
		if ( strpos( $user_agent, '/' ) !== false ) {
			$base_user_agent = explode( '/', $user_agent )[0];
		} else {
			$base_user_agent = $user_agent;
		}

		// Detect based on user agent
		if ( $base_user_agent === 'Claude-User' || $base_user_agent === 'Claude User' ) {
			$this->mcp_client_id = 'claude';
		} elseif ( strpos( $user_agent, 'openai' ) !== false || strpos( $user_agent, 'OpenAI' ) !== false ) {
			$this->mcp_client_id = 'openai';
		} else {
			// Default fallback - check Accept header
			$accept_header = isset( $headers['accept'] ) ? $headers['accept'][0] : '';
			if ( strpos( $accept_header, 'text/event-stream' ) !== false ) {
				$this->mcp_client_id = 'openai';
			} else {
				$this->mcp_client_id = 'claude'; // Default to Claude
			}
		}
	}

	/**
	 * Claude manifest handling
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function get_claude_manifest( $request ) {
		$server_id = $request['server_id'];

		// Get tools
		$all_tools = $this->get_all_tools( $server_id );

		// Get server settings
		$server_settings = flowmattic_get_mcp_server_data( $server_id );

		// If server is not active, return empty manifest
		if ( ! $server_settings || ! $server_settings['active'] ) {
			return new WP_REST_Response( array( 'status' => 'inactive' ), 403 );
		}

		// Convert to MCP format for Claude
		$tools = array();
		foreach ( $all_tools as $tool ) {
			if ( is_object( $tool ) ) {
				$tools[] = array(
					'name'        => $tool->mcp_tool_name,
					'description' => $tool->mcp_tool_description ?: "Execute {$tool->mcp_tool_name} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ),
				);
			}
		}

		// Full MCP manifest for Claude
		$manifest = array(
			'schema_version' => '1.0',
			'name'           => $server_settings['name'] ?: 'FlowMattic MCP Server',
			'description'    => $server_settings['description'] ?: 'FlowMattic MCP Server for Claude',
			'version'        => '1.0.0',
			'author'         => get_bloginfo( 'name' ),
			'tools'          => $tools,
			'capabilities'   => array(
				'tools'     => array(
					'listChanged' => true,
				),
				'resources' => array(
					'subscribe'   => false,
					'listChanged' => false,
				),
				'prompts'   => array(
					'listChanged' => false,
				),
			),
			'serverInfo'     => array(
				'name'    => $server_settings['name'] ?: 'FlowMattic MCP Server',
				'version' => '1.0.0',
			),
		);

		$response = new WP_REST_Response( $manifest, 200 );
		$response->header( 'Content-Type', 'application/json' );

		return $response;
	}

	/**
	 * Claude POST handling with JSON-RPC requests
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string          $server_id The server ID.
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_claude_post( $server_id, $request ) {
		$body = $request->get_body();

		if ( empty( $body ) ) {
			return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
		}

		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {

			return new WP_Error( 'invalid_json', 'Invalid JSON', array( 'status' => 400 ) );
		}

		$method = $data['method'] ?? '';
		$params = $data['params'] ?? array();
		$id     = $data['id'] ?? 1;

		switch ( $method ) {
			case 'initialize':
				return $this->handle_claude_initialize( $server_id, $params, $id );

			case 'tools/list':
				return $this->handle_claude_tools_list( $server_id, $id );

			case 'tools/call':
				return $this->handle_claude_tools_call( $server_id, $params, $id );

			case 'resources/list':
			case 'resources/read':
			case 'prompts/list':
			case 'prompts/get':
			case 'notifications/initialized':
			case 'notifications/cancelled':
			case 'ping':
				return $this->handle_claude_other_methods( $method, $id );

			default:
				return $this->claude_error_response( -32601, 'Method not found: ' . $method, $id );
		}
	}

	/**
	 * Claude initialize handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param array  $params    The parameters from the request.
	 * @param mixed  $id        The request ID.
	 * @return WP_REST_Response
	 */
	public function handle_claude_initialize( $server_id, $params, $id ) {
		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'protocolVersion' => '2024-11-05',
				'capabilities'    => array(
					'tools'     => array(
						'listChanged' => true,
					),
					'resources' => array(
						'subscribe'   => false,
						'listChanged' => false,
					),
					'prompts'   => array(
						'listChanged' => false,
					),
				),
				'serverInfo'      => array(
					'name'    => 'FlowMattic MCP Server',
					'version' => '1.0.0',
				),
			),
			'id'      => $id,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Claude tools/list handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param mixed  $id        The request ID.
	 * @return WP_REST_Response
	 */
	public function handle_claude_tools_list( $server_id, $id ) {
		$all_tools = $this->get_all_tools( $server_id );
		$tools     = array();

		foreach ( $all_tools as $tool ) {
			if ( is_object( $tool ) ) {
				$tools[] = array(
					'name'        => str_replace( array( ' ', '-' ), '_', strtolower( $tool->mcp_tool_name ) ),
					'description' => $tool->mcp_tool_description ?: "Execute {$tool->mcp_tool_name} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ),
				);
			} else {
				$tools[] = array(
					'name'        => str_replace( array( ' ', '-' ), '_', strtolower( $tool['name'] ) ),
					'description' => $tool['description'] ?: "Execute {$tool['name']} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ) ?? array(),
				);
			}
		}

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'tools' => $tools,
			),
			'id'      => $id,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Claude tools/call handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param array  $params    The parameters from the request.
	 * @param mixed  $id        The request ID.
	 * @return WP_REST_Response
	 */
	public function handle_claude_tools_call( $server_id, $params, $id ) {
		$tool_name = $params['name'] ?? '';
		$arguments = $params['arguments'] ?? array();

		if ( empty( $tool_name ) ) {
			return $this->claude_error_response( -32602, 'Tool name required', $id );
		}

		// Find the tool
		$all_tools = $this->get_all_tools( $server_id );
		$tool      = null;

		foreach ( $all_tools as $t ) {
			$t_name = is_object( $t ) ? $t->mcp_tool_name : $t['name'];
			$t_name = str_replace( array( ' ', '-' ), '_', strtolower( $t_name ) );
			if ( $t_name === $tool_name ) {
				$tool = $t;
				break;
			}
		}

		if ( ! $tool ) {
			return $this->claude_error_response( -32602, "Tool '{$tool_name}' not found", $id );
		}

		// If the tool is not an object, convert it to an object
		if ( ! is_object( $tool ) ) {
			$tool = (object) $tool;

			$tool->mcp_tool_id = $tool_name;

			// Set the mcp_tool_execution_method if not already set
			if ( ! isset( $tool->mcp_tool_execution_method ) ) {
				$tool->mcp_tool_execution_method = isset( $tool->execution_method ) ? $tool->execution_method : '';
			}

			// Set the mcp_tool_name if not already set
			if ( ! isset( $tool->mcp_tool_name ) ) {
				$tool->mcp_tool_name = isset( $tool->name ) ? $tool->name : '';
			}

			// Set the mcp_tool_function_name if not already set
			if ( ! isset( $tool->mcp_tool_function_name ) ) {
				$tool->mcp_tool_function_name = isset( $tool->function_name ) ? $tool->function_name : '';
			}
		}

		// Execute the tool with server_id for logging
		$result = $this->execute_tool( $tool, $arguments, $server_id );

		if ( isset( $result['error'] ) && $result['error'] ) {
			$error_message = isset( $result['message'] ) ? $result['message'] : $result['error'];
			return $this->claude_error_response( -32603, $error_message, $id );
		}

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'content' => array(
					array(
						'type' => 'text',
						'text' => wp_json_encode( $result ),
					),
				),
			),
			'id'      => $id,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Claude other methods handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $method The method name.
	 * @param mixed  $id     The request ID.
	 * @return WP_REST_Response
	 */
	public function handle_claude_other_methods( $method, $id ) {
		$default_responses = array(
			'resources/list'            => array( 'resources' => array() ),
			'resources/read'            => array( 'content' => array() ),
			'prompts/list'              => array( 'prompts' => array() ),
			'prompts/get'               => array( 'content' => array() ),
			'ping'                      => array( 'status' => 'pong' ),
			'notifications/initialized' => array( 'acknowledged' => true ),
			'notifications/cancelled'   => array( 'acknowledged' => true ),
		);

		$result = $default_responses[ $method ] ?? array( 'status' => 'ok' );

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => $result,
			'id'      => $id,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Claude error response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param int    $code    The error code.
	 * @param string $message The error message.
	 * @param mixed  $id      The request ID.
	 * @return WP_REST_Response
	 */
	private function claude_error_response( $code, $message, $id ) {
		$response = array(
			'jsonrpc' => '2.0',
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
			'id'      => $id,
		);

		return new WP_REST_Response( $response, 400 );
	}

	/**
	 * OpenAI SSE handling
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return void
	 */
	public function handle_openai_sse( $request ) {
		$server_id     = $request['server_id'];
		$session_param = $request->get_param( 'session' );

		// Extract session ID from parameter or header
		if ( $session_param ) {
			$this->current_session_id = $session_param;
		} else {
			$this->extract_session_id( $request );
		}

		// Validate session
		if ( empty( $this->current_session_id ) || ! isset( $this->sessions[ $this->current_session_id ] ) ) {
			return new WP_REST_Response(
				array( 'error' => 'Invalid or missing session ID' ),
				404
			);
		}

		// Start SSE stream
		$this->start_sse_stream( $server_id );
	}

	/**
	 * OpenAI POST handling (HTTP Stream Transport)
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string          $server_id The server ID.
	 * @param WP_REST_Request $request The REST request object.
	 * @return void
	 */
	public function handle_openai_post( $server_id, $request ) {
		$body = $request->get_body();

		// Extract session ID from headers
		$this->extract_session_id( $request );

		if ( empty( $body ) ) {
			return $this->openai_error_response( -32600, 'Invalid Request: Empty body', null );
		}

		$data = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return $this->openai_error_response( -32700, 'Parse error: Invalid JSON', null );
		}

		$method = $data['method'] ?? '';
		$params = $data['params'] ?? array();
		$id     = $data['id'] ?? null;

		switch ( $method ) {
			case 'initialize':
				return $this->handle_openai_initialize( $server_id, $params, $id );

			case 'tools/list':
				return $this->handle_openai_tools_list( $server_id, $id );

			case 'tools/call':
				return $this->handle_openai_tools_call( $server_id, $params, $id );

			default:
				return $this->openai_error_response( -32601, 'Method not found: ' . $method, $id );
		}
	}

	/**
	 * OpenAI initialize handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param array  $params    The parameters from the request.
	 * @param mixed  $id        The request ID.
	 * @return void
	 */
	public function handle_openai_initialize( $server_id, $params, $id ) {
		// Generate new session ID if not provided
		if ( empty( $this->current_session_id ) ) {
			$this->current_session_id = $this->generate_session_id();
		}

		// Store session data
		$this->sessions[ $this->current_session_id ] = array(
			'server_id'   => $server_id,
			'created_at'  => time(),
			'last_active' => time(),
			'client_type' => 'openai',
		);

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'protocolVersion' => '2024-11-05',
				'capabilities'    => array(
					'tools'     => array( 'listChanged' => true ),
					'resources' => array(
						'subscribe'   => false,
						'listChanged' => false,
					),
					'prompts'   => array( 'listChanged' => false ),
				),
				'serverInfo'      => array(
					'name'    => 'FlowMattic MCP Server',
					'version' => '1.0.0',
				),
			),
			'id'      => $id,
		);

		$this->send_sse_response( $response );
	}

	/**
	 * OpenAI tools/list handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param mixed  $id        The request ID.
	 * @return void
	 */
	public function handle_openai_tools_list( $server_id, $id ) {
		// Create session if none exists (for debugging)
		if ( empty( $this->current_session_id ) ) {
			$this->current_session_id                    = $this->generate_session_id();
			$this->sessions[ $this->current_session_id ] = array(
				'server_id'   => $server_id,
				'created_at'  => time(),
				'last_active' => time(),
				'client_type' => 'openai-auto',
			);
		}

		// Update session activity
		if ( isset( $this->sessions[ $this->current_session_id ] ) ) {
			$this->sessions[ $this->current_session_id ]['last_active'] = time();
		}

		// Get tools
		$all_tools = $this->get_all_tools( $server_id );
		$tools     = array();

		foreach ( $all_tools as $tool ) {
			if ( is_object( $tool ) ) {
				$tool_name = str_replace( array( ' ', '-' ), '_', strtolower( $tool->mcp_tool_name ) );
				$tools[]   = array(
					'name'        => $tool_name,
					'description' => $tool->mcp_tool_description ?: "Execute {$tool->mcp_tool_name} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ),
				);
			} else {
				$tool_name = str_replace( array( ' ', '-' ), '_', strtolower( $tool['name'] ) );
				$tools[]   = array(
					'name'        => $tool_name,
					'description' => $tool['description'] ?: "Execute {$tool['name']} tool",
					'inputSchema' => $this->get_tool_input_schema( $tool ) ?? array(),
				);
			}
		}

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array( 'tools' => $tools ),
			'id'      => $id,
		);

		$this->send_sse_response( $response );
	}

	/**
	 * OpenAI tools/call handler
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $server_id The server ID.
	 * @param array  $params    The parameters from the request.
	 * @param mixed  $id        The request ID.
	 * @return void
	 */
	public function handle_openai_tools_call( $server_id, $params, $id ) {
		$tool_name = $params['name'] ?? '';
		$arguments = $params['arguments'] ?? array();

		if ( empty( $tool_name ) ) {
			$this->send_sse_response(
				array(
					'jsonrpc' => '2.0',
					'error'   => array(
						'code'    => -32602,
						'message' => 'Tool name required',
					),
					'id'      => $id,
				)
			);
		}

		// Find and execute tool
		$all_tools = $this->get_all_tools( $server_id );
		$tool      = null;

		foreach ( $all_tools as $t ) {
			$t_name = is_object( $t ) ? $t->mcp_tool_name : $t['name'];
			$t_name = str_replace( array( ' ', '-' ), '_', strtolower( $t_name ) );
			if ( $t_name === $tool_name ) {
				$tool = $t;
				break;
			}
		}

		if ( ! $tool ) {
			$this->send_sse_response(
				array(
					'jsonrpc' => '2.0',
					'error'   => array(
						'code'    => -32602,
						'message' => "Tool '{$tool_name}' not found",
					),
					'id'      => $id,
				)
			);
		}

		// If the tool is not an object, convert it to an object
		if ( ! is_object( $tool ) ) {
			$tool = (object) $tool;

			$tool->mcp_tool_id = $tool_name;

			// Set the mcp_tool_execution_method if not already set
			if ( ! isset( $tool->mcp_tool_execution_method ) ) {
				$tool->mcp_tool_execution_method = isset( $tool->execution_method ) ? $tool->execution_method : '';
			}

			// Set the mcp_tool_name if not already set
			if ( ! isset( $tool->mcp_tool_name ) ) {
				$tool->mcp_tool_name = isset( $tool->name ) ? $tool->name : '';
			}

			// Set the mcp_tool_function_name if not already set
			if ( ! isset( $tool->mcp_tool_function_name ) ) {
				$tool->mcp_tool_function_name = isset( $tool->function_name ) ? $tool->function_name : '';
			}
		}

		// Execute tool
		$result = $this->execute_tool( $tool, $arguments, $server_id );

		if ( isset( $result['error'] ) && $result['error'] ) {
			$this->send_sse_response(
				array(
					'jsonrpc' => '2.0',
					'error'   => array(
						'code'    => -32603,
						'message' => $result['message'],
					),
					'id'      => $id,
				)
			);
		}

		$response = array(
			'jsonrpc' => '2.0',
			'result'  => array(
				'content' => array(
					array(
						'type' => 'text',
						'text' => wp_json_encode( $result ),
					),
				),
			),
			'id'      => $id,
		);

		$this->send_sse_response( $response );
	}

	/**
	 * OpenAI error response
	 *
	 * @access private
	 * @since 5.2.0
	 * @param int    $code    The error code.
	 * @param string $message The error message.
	 * @param mixed  $id      The request ID.
	 * @return void
	 */
	private function openai_error_response( $code, $message, $id ) {
		$response = array(
			'jsonrpc' => '2.0',
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
			'id'      => $id,
		);

		$this->send_sse_response( $response );
	}

	/**
	 * Send SSE response for OpenAI
	 *
	 * @access private
	 * @since 5.2.0
	 * @param array $response_data The data to send in the SSE response.
	 * @return void
	 */
	private function send_sse_response( $response_data ) {
		// Set SSE headers
		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' );

		// Set session ID header if available
		if ( ! empty( $this->current_session_id ) ) {
			header( "Mcp-Session-Id: {$this->current_session_id}" );
		}

		// Send CORS headers
		$this->send_cors_headers();

		// Disable output buffering
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		// Send the SSE event
		echo "event: message\n";
		echo 'data: ' . json_encode( $response_data ) . "\n\n";

		// Ensure output is sent immediately
		flush();

		// Exit cleanly to prevent WordPress from adding extra content
		exit();
	}

	/**
	 * Extract session ID from request headers
	 *
	 * @access private
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return void
	 */
	private function extract_session_id( $request ) {
		$headers = $request->get_headers();

		$session_id = null;
		if ( isset( $headers['mcp_session_id'] ) ) {
			$session_id = is_array( $headers['mcp_session_id'] )
				? $headers['mcp_session_id'][0]
				: $headers['mcp_session_id'];
		} elseif ( isset( $headers['mcp-session-id'] ) ) {
			$session_id = is_array( $headers['mcp-session-id'] )
				? $headers['mcp-session-id'][0]
				: $headers['mcp-session-id'];
		}

		$this->current_session_id = $session_id;
	}

	/**
	 * Generate UUID v4 for session ID
	 *
	 * @access private
	 * @since 5.2.0
	 * @return string
	 * @see https://www.ietf.org/rfc/rfc4122.txt
	 */
	private function generate_session_id() {
		$data    = random_bytes( 16 );
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // Version 4
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // Variant bits

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}

	/**
	 * Send CORS headers
	 *
	 * @access private
	 * @since 5.2.0
	 * @return void
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
	 */
	private function send_cors_headers() {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Content-Type, Accept, Authorization, x-api-key, Mcp-Session-Id, Last-Event-ID' );
		header( 'Access-Control-Expose-Headers: Content-Type, Authorization, x-api-key, Mcp-Session-Id' );
		header( 'Access-Control-Max-Age: 86400' );
	}

	/**
	 * Handle DELETE requests (session termination)
	 *
	 * @access public
	 * @since 5.2.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function handle_delete_request( $request ) {
		$this->send_cors_headers();

		$this->extract_session_id( $request );

		if ( empty( $this->current_session_id ) ) {
			return new WP_REST_Response(
				array( 'error' => 'Session ID required' ),
				400
			);
		}

		// Terminate session
		if ( isset( $this->sessions[ $this->current_session_id ] ) ) {
			unset( $this->sessions[ $this->current_session_id ] );

		}

		return new WP_REST_Response(
			array( 'message' => 'Session terminated successfully' ),
			200
		);
	}

	/**
	 * Start SSE stream for server-to-client messages
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id The server ID to start the stream for
	 * @return void
	 */
	private function start_sse_stream( $server_id ) {
		// Set SSE headers
		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' );

		// Set session ID header
		if ( ! empty( $this->current_session_id ) ) {
			header( "Mcp-Session-Id: {$this->current_session_id}" );
		}

		// Disable output buffering
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		// Send initial message
		echo "event: connected\n";
		echo 'data: ' . json_encode(
			array(
				'type'      => 'connection_established',
				'sessionId' => $this->current_session_id,
				'timestamp' => time(),
			)
		) . "\n\n";
		flush();

		// Keep connection alive
		$start_time = time();
		$timeout    = 300; // 5 minutes

		while ( time() - $start_time < $timeout ) {
			if ( ( time() - $start_time ) % 30 === 0 ) {
				echo "event: heartbeat\n";
				echo 'data: ' . json_encode(
					array(
						'type'      => 'heartbeat',
						'timestamp' => time(),
					)
				) . "\n\n";
				flush();
			}

			// sleep( 1 );

			if ( ! isset( $this->sessions[ $this->current_session_id ] ) ) {
				break;
			}
		}

		echo "event: disconnect\n";
		echo 'data: ' . json_encode(
			array(
				'type'   => 'connection_closed',
				'reason' => 'timeout',
			)
		) . "\n\n";
		flush();

		exit();
	}

	/**
	 * Get all tools from database and dummy functions
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id The server ID to get tools for
	 * @return array List of tools available for the server
	 */
	private function get_all_tools( $server_id ) {
		$tools = array();

		// Get database tools first
		if ( class_exists( 'FlowMattic_Database_MCP_Server' ) && wp_flowmattic() && wp_flowmattic()->mcp_server_db ) {
			$db_tools = wp_flowmattic()->mcp_server_db->get_tools_by_server( $server_id );
			$tools    = array_merge( $tools, $db_tools );
		}

		// Add filter to allow other plugins to add tools
		$plugin_tools = array();
		if ( ! empty( self::$plugin_tools ) ) {
			foreach ( self::$plugin_tools as $tool_slug => $plugin_tool ) {
				$plugin_tools[] = $plugin_tool['tool'];
			}
		}

		return array_merge( $tools, $plugin_tools );
	}

	/**
	 * Execute a tool
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object containing execution details
	 * @param array  $arguments Arguments to pass to the tool
	 * @param string $server_id Server ID for logging
	 * @return array Result of the tool execution or error details
	 */
	private function execute_tool( $tool, $arguments, $server_id = '' ) {
		$start_time = microtime( true );

		// Apply the type casting to ensure the argument values are in the correct format
		$arguments = $this->cast_arguments( $arguments );

		if ( is_object( $tool ) ) {
			$execution_method = $tool->mcp_tool_execution_method ?? 'api_endpoint';

			$result = null;

			switch ( $execution_method ) {
				case 'php_function':
					$result = $this->execute_php_function( $tool, $arguments );
					break;
				case 'workflow_id':
					$result = $this->execute_workflow( $tool, $arguments );
					break;
				case 'database_query':
					$result = $this->execute_database_query( $tool, $arguments );
					break;
				case 'api_endpoint':
				default:
					$result = $this->execute_api_endpoint( $tool, $arguments );
					break;
			}

			// Add execution time to result if not already present
			if ( ! isset( $result['execution_time'] ) ) {
				$result['execution_time'] = round( microtime( true ) - $start_time, 4 );
			}

			// Log the execution
			if ( ! empty( $server_id ) ) {
				$this->log_tool_execution( $server_id, $tool, $arguments, $result );
			}

			return $result;
		}

		return array(
			'error'   => true,
			'message' => 'Invalid tool object',
		);
	}

	/**
	 * Cast arguments to ensure they are in the correct format
	 *
	 * @access private
	 * @since 5.2.0
	 * @param array $arguments Arguments to cast
	 * @return array Casted arguments
	 */
	private function cast_arguments( $arguments ) {
		if ( ! is_array( $arguments ) ) {
			return array( 'data' => $arguments );
		}

		// Cast each argument value to ensure it's in the correct format
		foreach ( $arguments as $key => $value ) {
			if ( is_array( $value ) ) {
				$arguments[ $key ] = $this->cast_arguments( $value );
			} elseif ( is_array( $value ) ) {
				// If it's an array, ensure it's properly formatted
				$arguments[ $key ] = array_values( $value );
			} elseif ( is_object( $value ) ) {
				// Convert objects to arrays
				$arguments[ $key ] = (array) $value;
			} elseif ( is_string( $value ) && is_numeric( $value ) ) {
				// Cast numeric strings to integers or floats
				if ( strpos( $value, '.' ) !== false ) {
					$arguments[ $key ] = floatval( $value );
				} else {
					$arguments[ $key ] = intval( $value );
				}
			} elseif ( is_bool( $value ) ) {
				// Ensure booleans are cast correctly
				$arguments[ $key ] = (bool) $value;
			} else {
				// For other types, just keep the value as is
				$arguments[ $key ] = $value;
			}
		}

		return $arguments;
	}

	/**
	 * Execute PHP function
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object containing function name and other details
	 * @param array  $arguments Arguments to pass to the function
	 * @return array Result of the function execution or error details
	 */
	private function execute_php_function( $tool, $arguments ) {
		$function_name = $tool->mcp_tool_function_name;

		if ( empty( $function_name ) ) {
			return array(
				'error'   => true,
				'message' => 'No function name specified',
				'tool'    => $tool->mcp_tool_name,
			);
		}

		if ( ! function_exists( $function_name ) && ! is_callable( $function_name ) ) {
			return array(
				'error'   => true,
				'message' => "Function '{$function_name}' does not exist or is not callable",
				'tool'    => $tool->mcp_tool_name,
			);
		}

		// Set execution time limit for PHP functions
		$original_time_limit = ini_get( 'max_execution_time' );
		set_time_limit( 30 ); // 30 seconds max

		// Start output buffering to capture any echo/print statements
		ob_start();

		$start_time    = microtime( true );
		$result        = null;
		$error_message = '';
		$has_error     = false;

		// If tool metadata specifies 'request_type' as 'array', handle it accordingly
		if ( isset( $tool->mcp_tool_metadata ) && ! empty( $tool->mcp_tool_metadata ) ) {
			$tool_metadata = maybe_unserialize( $tool->mcp_tool_metadata );
			if ( is_array( $tool_metadata ) && isset( $tool_metadata['params_request_type'] ) ) {
				$tool->request_type = $tool_metadata['params_request_type'] === 'array' ? 'array' : 'individual';
			} else {
				$tool->request_type = 'individual'; // Default to 'individual' if not set
			}
		}

		try {
			// Set error handler to catch non-exception errors
			set_error_handler(
				function( $severity, $message, $file, $line ) {
					throw new ErrorException( $message, 0, $severity, $file, $line );
				}
			);

			// Check if request_type is set to 'array'
			if ( isset( $tool->request_type ) && $tool->request_type === 'array' ) {
				// If function expects an array, pass arguments as is
				// This is useful for functions that expect a single associative array argument
				// containing all parameters
				$result = call_user_func( $function_name, $arguments );
			} else {
				// Try different argument formats that functions might expect
				if ( is_array( $arguments ) && count( $arguments ) === 1 && isset( $arguments['data'] ) ) {
					// If arguments wrapped in 'data' key, unwrap it
					$result = call_user_func( $function_name, $arguments['data'] );
				} elseif ( is_array( $arguments ) && count( $arguments ) === 1 ) {
					// If single argument in array, pass its value directly
					$result = call_user_func( $function_name, reset( $arguments ) );
				} elseif ( is_array( $arguments ) && ! empty( $arguments ) ) {
					// Pass arguments as individual parameters with their values
					$result = call_user_func_array( $function_name, array_values( $arguments ) );
				} else {
					// Pass arguments as single parameter
					$result = call_user_func( $function_name, $arguments );
				}
			}

			// Restore error handler
			restore_error_handler();

		} catch ( Exception $e ) {
			$has_error     = true;
			$error_message = 'Exception: ' . $e->getMessage();
			restore_error_handler();
		} catch ( Error $e ) {
			$has_error     = true;
			$error_message = 'Error: ' . $e->getMessage();
			restore_error_handler();
		} catch ( Throwable $e ) {
			$has_error     = true;
			$error_message = 'Fatal error: ' . $e->getMessage();
			restore_error_handler();
		}

		$execution_time = microtime( true ) - $start_time;

		// Get any output that was echoed/printed
		$output = ob_get_clean();

		// Restore original time limit
		set_time_limit( $original_time_limit );

		// Check for timeout (if execution took too long)
		if ( $execution_time > 30 ) {
			return array(
				'error'   => true,
				'message' => "Function execution timed out after {$execution_time} seconds",
				'tool'    => $tool->mcp_tool_name,
			);
		}

		if ( $has_error ) {
			return array(
				'error'          => true,
				'message'        => 'Function execution failed: ' . $error_message,
				'tool'           => $tool->mcp_tool_name,
				'execution_time' => $execution_time,
				'output'         => $output,
			);
		}

		// Handle case where function returns nothing/null
		if ( $result === null ) {
			// If function didn't return anything but produced output, use that
			if ( ! empty( $output ) ) {
				$result = $output;
			} else {
				$result = array( 'message' => 'Function executed successfully but returned no data' );
			}
		}

		// If result is a string, try to decode it as JSON
		if ( is_string( $result ) ) {
			$decoded = json_decode( $result, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$result = $decoded;
			}
		}

		// If output is blank, set it to $result
		if ( empty( $output ) ) {
			$output = $result;
		}

		$response = array(
			'execution_time' => round( $execution_time, 4 ),
			'timestamp'      => current_time( 'c' ),
		);

		if ( is_array( $result ) ) {
			$response = $result;
		} elseif ( is_string( $result ) ) {
			$response = array( 'message' => $result );
		} else {
			$response = array( 'data' => $result );
		}

		return $response;
	}

	/**
	 * Execute workflow
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object.
	 * @param array  $arguments Arguments to pass to the workflow.
	 * @return array Response data.
	 */
	private function execute_workflow( $tool, $arguments ) {
		$workflow_id = $tool->mcp_tool_workflow_id;

		if ( empty( $workflow_id ) ) {
			return array(
				'error'   => true,
				'message' => 'No workflow ID specified',
				'tool'    => $tool->mcp_tool_name,
			);
		}

		try {
			$workflow_live_id = get_option( 'webhook-capture-live', false );

			if ( $workflow_live_id ) {
				update_option( 'webhook-capture-' . $workflow_live_id, $arguments, false );
				delete_option( 'webhook-capture-live' );

				// Do not execute workflow if capture data in process.
				return;
			}

			// Execute the workflow with the provided arguments
			$flowmattic_workflow = new FlowMattic_Workflow();
			$workflow_response   = $flowmattic_workflow->run( $workflow_id, $arguments );

			$response_data = array(
				'tool_name'      => $tool->mcp_tool_name,
				'execution_type' => 'workflow',
				'workflow_id'    => $workflow_id,
				'success'        => true,
				'data'           => array( 'message' => 'Workflow executed successfully' ),
				'timestamp'      => current_time( 'c' ),
			);

			if ( $workflow_response ) {
				$response_data['data'] = $workflow_response;
			}

			return $response_data;
		} catch ( Exception $e ) {
			return array(
				'error'   => true,
				'message' => 'Workflow execution failed: ' . $e->getMessage(),
				'tool'    => $tool->mcp_tool_name,
			);
		}
	}

	/**
	 * Execute API endpoint
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object.
	 * @param array  $arguments Arguments to pass to the API endpoint.
	 * @return array Response data.
	 */
	private function execute_api_endpoint( $tool, $arguments ) {
		$endpoint   = $tool->mcp_tool_api_endpoint;
		$method     = strtoupper( $tool->mcp_tool_http_method ?: 'GET' );
		$headers    = is_array( $tool->mcp_tool_request_headers ) ? $tool->mcp_tool_request_headers : array();
		$metadata   = is_array( $tool->mcp_tool_metadata ) ? $tool->mcp_tool_metadata : array();
		$connect_id = isset( $metadata['api_connect'] ) ? $metadata['api_connect'] : false;

		if ( empty( $endpoint ) ) {
			return array(
				'error'   => true,
				'message' => 'No API endpoint specified',
				'tool'    => $tool->mcp_tool_name,
			);
		}

		$args = array(
			'method'    => $method,
			'headers'   => array_merge(
				array(
					'Content-Type' => 'application/json',
					'User-Agent'   => 'FlowMattic-MCP/1.0',
				),
				$headers
			),
			'timeout'   => 30,
			'sslverify' => false,
		);

		if ( $connect_id ) {
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
						$endpoint = add_query_arg( $api_key, $api_value, $endpoint );
					} else {
						$args['headers'][ $api_key ] = $api_value;

						// Headers used in cURL request.
						$headers[] = $api_key . ': ' . $api_value;
					}

					break;
			}
		}

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) && ! empty( $arguments ) ) {
			$args['body'] = wp_json_encode( $arguments );
		} elseif ( $method === 'GET' && ! empty( $arguments ) ) {
			$endpoint = add_query_arg( $arguments, $endpoint );
		}

		$response = wp_remote_request( $endpoint, $args );

		if ( is_wp_error( $response ) ) {

			return array(
				'error'   => true,
				'message' => $response->get_error_message(),
				'tool'    => $tool->mcp_tool_name,
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		$parsed_body = json_decode( $response_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$parsed_body = $response_body;
		}

		$response_data = array(
			'mcp_tool_name'  => $tool->mcp_tool_name,
			'execution_type' => 'api_endpoint',
			'status_code'    => $response_code,
			'success'        => $response_code >= 200 && $response_code < 300,
			'timestamp'      => current_time( 'c' ),
		);

		$response_data = array_merge( $response_data, $parsed_body );

		return $response_data;
	}

	/**
	 * Handle database query execution
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object.
	 */
	private function execute_database_query( $tool, $arguments ) {
		// Check if the query is set
		if ( empty( $tool->mcp_tool_db_query ) ) {
			return array(
				'error'   => true,
				'message' => 'No database query specified',
				'tool'    => $tool->mcp_tool_name,
			);
		}

		$query = stripslashes( $tool->mcp_tool_db_query );

		// Prepare the query with arguments
		if ( ! empty( $arguments ) && is_array( $arguments ) ) {
			// Use wpdb to prepare the query
			global $wpdb;
			$query = $wpdb->prepare( $query, $arguments );

			// Loop through arguments and replace placeholders
			foreach ( $arguments as $key => $value ) {
				// If the value is an array, convert it to a string
				if ( is_array( $value ) ) {
					$value = implode( ',', array_map( 'esc_sql', $value ) );
				} else {
					$value = esc_sql( $value );
				}

				// Replace the placeholder with the escaped value
				$query = str_replace( ':' . $key, $value, $query );
			}

			// If the query is a SELECT, we can use get_results
			if ( stripos( $query, 'SELECT' ) === 0 ) {
				$results = $wpdb->get_results( $query, ARRAY_A );
				if ( $wpdb->last_error ) {
					return array(
						'error'   => true,
						'message' => 'Database query error: ' . $wpdb->last_error,
						'tool'    => $tool->mcp_tool_name,
					);
				}
				return array(
					'tool_name'      => $tool->mcp_tool_name,
					'execution_type' => 'database_query',
					'success'        => true,
					'data'           => ! empty( $results ) ? $results : esc_html( 'No results found for query.' ),
					'timestamp'      => current_time( 'c' ),
				);
			} else {
				// For non-SELECT queries, we can execute directly
				$result = $wpdb->query( $query );
				if ( $wpdb->last_error ) {
					return array(
						'error'   => true,
						'message' => 'Database query error: ' . $wpdb->last_error,
						'tool'    => $tool->mcp_tool_name,
					);
				}

				return array(
					'tool_name'      => $tool->mcp_tool_name,
					'execution_type' => 'database_query',
					'success'        => true,
					'data'           => array( 'affected_rows' => $result ),
					'timestamp'      => current_time( 'c' ),
				);
			}
		} else {
			// If no arguments, just execute the query as is
			global $wpdb;
			$results = $wpdb->get_results( $query, ARRAY_A );
			if ( $wpdb->last_error ) {
				return array(
					'error'   => true,
					'message' => 'Database query error: ' . $wpdb->last_error,
					'tool'    => $tool->mcp_tool_name,
				);
			}

			return $results;
		}
	}

	/**
	 * Get input schema for tool
	 *
	 * @access private
	 * @since 5.2.0
	 * @param object $tool Tool object.
	 */
	private function get_tool_input_schema( $tool ) {
		if ( is_object( $tool ) && ! empty( $tool->mcp_tool_input_schema ) && is_array( $tool->mcp_tool_input_schema ) ) {
			return $tool->mcp_tool_input_schema;
		}

		$execution_method = '';
		if ( is_object( $tool ) && ! empty( $tool->mcp_tool_execution_method ) ) {
			$execution_method = $tool->mcp_tool_execution_method;
		} elseif ( isset( $tool['execution_method'] ) ) {
			$execution_method = $tool['execution_method'];
		}

		switch ( $execution_method ) {
			case 'php_function':
				// Get the parameters for the PHP function
				$parameters = $tool['parameters'] ?? array();

				// Prepare the input schema for PHP function
				$input_schema = array(
					'type'       => 'object',
					'properties' => array(),
				);
				if ( ! empty( $parameters ) && is_array( $parameters ) ) {
					$properties = array();
					foreach ( $parameters as $param ) {
						$properties[ $param ] = array(
							'type'        => 'string',
							'description' => '',
						);
					}
					$input_schema['properties'] = $properties;
				}

				return $input_schema;

			case 'workflow_id':
				return array(
					'type'       => 'object',
					'properties' => array(
						'trigger_data' => array(
							'type'        => 'object',
							'description' => 'Data to trigger the workflow with',
						),
					),
				);

			case 'api_endpoint':
			default:
				return array(
					'type'       => 'object',
					'properties' => array(
						'data' => array(
							'type'        => 'object',
							'description' => 'Data to send to the API endpoint',
						),
					),
				);
		}
	}

	/**
	 * Create a new tool
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function create_tool() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$server_id        = sanitize_text_field( $_POST['server_id'] );
		$tool_name        = sanitize_text_field( $_POST['tool_name'] );
		$description      = sanitize_textarea_field( $_POST['description'] );
		$execution_method = sanitize_text_field( $_POST['execution_method'] );
		$input_schema     = $_POST['input_schema'];
		$database_query   = isset( $_POST['database_query'] ) ? sanitize_textarea_field( $_POST['database_query'] ) : '';
		$metadata         = isset( $_POST['metadata'] ) ? $_POST['metadata'] : '';

		// Parse input schema if provided
		$parsed_schema = array();
		if ( ! empty( $input_schema ) ) {
			$input_schema  = stripslashes( $input_schema ); // Remove slashes if needed.
			$parsed_schema = json_decode( $input_schema, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_send_json_error( 'Invalid JSON format in input schema' );
			}
		}

		// Check if tool name already exists for this server
		if ( wp_flowmattic()->mcp_server_db->tool_name_exists( $tool_name, $server_id ) ) {
			wp_send_json_error( 'Tool name already exists for this server' );
		}

		// Prepare base tool data
		$tool_data = array(
			'mcp_server_id'             => $server_id,
			'mcp_tool_name'             => $tool_name,
			'mcp_tool_description'      => $description,
			'mcp_tool_input_schema'     => $parsed_schema,
			'mcp_tool_execution_method' => $execution_method,
		);

		// Add execution method specific data
		switch ( $execution_method ) {
			case 'php_function':
				$function_name = sanitize_text_field( $_POST['function_name'] );
				if ( empty( $function_name ) ) {
					wp_send_json_error( 'Function name is required for PHP Function method' );
				}
				$tool_data['mcp_tool_function_name'] = $function_name;
				$tool_data['mcp_tool_http_method']   = ''; // Not applicable
				$tool_data['mcp_tool_api_endpoint']  = ''; // Not applicable

				// Add metadata if provided
				if ( ! empty( $metadata ) ) {
					$tool_data['mcp_tool_metadata'] = $metadata;
				} else {
					$tool_data['mcp_tool_metadata'] = array();
				}
				break;

			case 'workflow_id':
				$workflow_id = sanitize_text_field( $_POST['workflow_id'] );
				if ( empty( $workflow_id ) ) {
					wp_send_json_error( 'Workflow ID is required for Workflow method' );
				}
				$tool_data['mcp_tool_workflow_id']  = $workflow_id;
				$tool_data['mcp_tool_http_method']  = ''; // Not applicable
				$tool_data['mcp_tool_api_endpoint'] = ''; // Not applicable
				break;

			case 'api_endpoint':
				$endpoint    = esc_url_raw( $_POST['endpoint'] );
				$method      = strtoupper( sanitize_text_field( $_POST['method'] ) );
				$headers     = $_POST['headers'];
				$webhook_url = esc_url_raw( $_POST['webhook_url'] );

				if ( empty( $endpoint ) ) {
					wp_send_json_error( 'API Endpoint is required for API method' );
				}

				// Parse headers JSON if provided
				$parsed_headers = array();
				if ( ! empty( $headers ) ) {
					$parsed_headers = json_decode( $headers, true );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						wp_send_json_error( 'Invalid JSON format in headers' );
					}
				}

				$tool_data['mcp_tool_api_endpoint']    = $endpoint;
				$tool_data['mcp_tool_http_method']     = $method;
				$tool_data['mcp_tool_request_headers'] = $parsed_headers;
				$tool_data['mcp_tool_webhook_url']     = $webhook_url;

				// Add metadata if provided
				if ( ! empty( $metadata ) ) {
					$tool_data['mcp_tool_metadata'] = $metadata;
				} else {
					$tool_data['mcp_tool_metadata'] = array();
				}

				break;

			case 'database_query':
				if ( empty( $database_query ) ) {
					wp_send_json_error( 'Database query is required for Database Query method' );
				}
				$tool_data['mcp_tool_db_query']         = stripslashes( $database_query ); // Remove slashes if needed.
				$tool_data['mcp_tool_execution_method'] = 'database_query';
				$tool_data['mcp_tool_http_method']      = ''; // Not applicable
				$tool_data['mcp_tool_api_endpoint']     = ''; // Not applicable
				$tool_data['mcp_tool_webhook_url']      = ''; // Not applicable
				$tool_data['mcp_tool_function_name']    = ''; // Not applicable
				$tool_data['mcp_tool_workflow_id']      = ''; // Not applicable
				break;

			default:
				wp_send_json_error( 'Invalid execution method: ' . $execution_method );
				return;
		}

		// Validate tool data
		$validated = wp_flowmattic()->mcp_server_db->validate_tool_data( $tool_data );
		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( $validated->get_error_message() );
		}

		// Insert tool
		$tool_id = wp_flowmattic()->mcp_server_db->insert( $tool_data );

		if ( $tool_id ) {
			wp_send_json_success(
				array(
					'message' => sprintf( 'Tool "%s" created successfully with %s execution method!', $tool_name, ucwords( str_replace( '_', ' ', $execution_method ) ) ),
					'tool_id' => $tool_id,
				)
			);
		} else {
			wp_send_json_error( 'Failed to create tool. Please try again.' );
		}
	}

	/**
	 * Get tool data
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function get_tool() {
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$tool_id = sanitize_text_field( $_POST['tool_id'] );
		$tool    = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tool_id ) );

		if ( $tool ) {
			wp_send_json_success(
				array(
					'tool' => $tool,
				)
			);
		} else {
			wp_send_json_error( 'Tool not found' );
		}
	}

	/**
	 * Update a tool
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function update_tool() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$tool_id          = sanitize_text_field( $_POST['tool_id'] );
		$tool_name        = sanitize_text_field( $_POST['tool_name'] );
		$description      = sanitize_textarea_field( $_POST['description'] );
		$execution_method = sanitize_text_field( $_POST['execution_method'] );
		$input_schema     = $_POST['input_schema'];
		$database_query   = isset( $_POST['database_query'] ) ? stripslashes( sanitize_textarea_field( $_POST['database_query'] ) ) : '';
		$metadata         = isset( $_POST['metadata'] ) ? $_POST['metadata'] : array();

		if ( empty( $tool_id ) ) {
			wp_send_json_error( 'Tool ID is required' );
		}

		if ( empty( $tool_name ) ) {
			wp_send_json_error( 'Tool name is required' );
		}

		// Parse input schema if provided
		$parsed_schema = array();
		if ( ! empty( $input_schema ) ) {
			$input_schema  = stripslashes( $input_schema ); // Remove slashes if needed.
			$parsed_schema = json_decode( $input_schema, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_send_json_error( 'Invalid JSON format in input schema' );
			}
		}

		// Get current tool to find server_id
		$current_tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tool_id ) );
		if ( ! $current_tool ) {
			wp_send_json_error( 'Tool not found' );
		}

		// Check if tool name already exists for this server (excluding current tool)
		if ( wp_flowmattic()->mcp_server_db->tool_name_exists( $tool_name, $current_tool->mcp_server_id, $tool_id ) ) {
			wp_send_json_error( 'Tool name already exists for this server' );
		}

		// Prepare base tool data
		$tool_data = array(
			'mcp_tool_name'             => $tool_name,
			'mcp_tool_description'      => $description,
			'mcp_tool_input_schema'     => $parsed_schema,
			'mcp_tool_execution_method' => $execution_method,
		);

		// Add execution method specific data
		switch ( $execution_method ) {
			case 'php_function':
				$function_name = sanitize_text_field( $_POST['function_name'] );
				if ( empty( $function_name ) ) {
					wp_send_json_error( 'Function name is required for PHP Function method' );
				}
				$tool_data['mcp_tool_function_name']   = $function_name;
				$tool_data['mcp_tool_workflow_id']     = ''; // Clear other method fields
				$tool_data['mcp_tool_api_endpoint']    = '';
				$tool_data['mcp_tool_http_method']     = '';
				$tool_data['mcp_tool_request_headers'] = array();
				$tool_data['mcp_tool_webhook_url']     = '';

				// Add metadata if provided
				if ( ! empty( $metadata ) && is_array( $metadata ) ) {
					$tool_data['mcp_tool_metadata'] = $metadata;
				} else {
					$tool_data['mcp_tool_metadata'] = array(); // Ensure metadata is always an array
				}
				break;

			case 'workflow_id':
				$workflow_id = sanitize_text_field( $_POST['workflow_id'] );
				if ( empty( $workflow_id ) ) {
					wp_send_json_error( 'Workflow ID is required for Workflow method' );
				}
				$tool_data['mcp_tool_workflow_id']     = $workflow_id;
				$tool_data['mcp_tool_function_name']   = ''; // Clear other method fields
				$tool_data['mcp_tool_api_endpoint']    = '';
				$tool_data['mcp_tool_http_method']     = '';
				$tool_data['mcp_tool_request_headers'] = array();
				$tool_data['mcp_tool_webhook_url']     = '';
				break;

			case 'api_endpoint':
				$endpoint    = esc_url_raw( $_POST['endpoint'] );
				$method      = strtoupper( sanitize_text_field( $_POST['method'] ) );
				$headers     = $_POST['headers'];
				$webhook_url = esc_url_raw( $_POST['webhook_url'] );

				if ( empty( $endpoint ) ) {
					wp_send_json_error( 'API Endpoint is required for API method' );
				}

				// Parse headers JSON if provided
				$parsed_headers = array();
				if ( ! empty( $headers ) ) {
					$parsed_headers = json_decode( $headers, true );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						wp_send_json_error( 'Invalid JSON format in headers' );
					}
				}

				$tool_data['mcp_tool_api_endpoint']    = $endpoint;
				$tool_data['mcp_tool_http_method']     = $method;
				$tool_data['mcp_tool_request_headers'] = $parsed_headers;
				$tool_data['mcp_tool_webhook_url']     = $webhook_url;
				$tool_data['mcp_tool_function_name']   = ''; // Clear other method fields
				$tool_data['mcp_tool_workflow_id']     = '';

				// Add metadata if provided
				if ( ! empty( $metadata ) && is_array( $metadata ) ) {
					$tool_data['mcp_tool_metadata'] = $metadata;
				} else {
					$tool_data['mcp_tool_metadata'] = array(); // Ensure metadata is always an array
				}

				break;

			case 'database_query':
				if ( empty( $database_query ) ) {
					wp_send_json_error( 'Database query is required for Database Query method' );
				}
				$tool_data['mcp_tool_db_query']        = $database_query;
				$tool_data['mcp_tool_function_name']   = ''; // Clear other method fields
				$tool_data['mcp_tool_workflow_id']     = ''; // Not applicable
				$tool_data['mcp_tool_api_endpoint']    = ''; // Not applicable
				$tool_data['mcp_tool_http_method']     = ''; // Not applicable
				$tool_data['mcp_tool_request_headers'] = array(); // Not applicable
				$tool_data['mcp_tool_webhook_url']     = ''; // Not applicable
				break;

			default:
				wp_send_json_error( 'Invalid execution method' );
		}

		// Validate tool data
		$validated = wp_flowmattic()->mcp_server_db->validate_tool_data( $tool_data );
		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( $validated->get_error_message() );
		}

		// Update tool
		$result = wp_flowmattic()->mcp_server_db->update( $tool_data, $tool_id );

		if ( $result !== false ) {
			wp_send_json_success(
				array(
					'message' => sprintf( 'Tool "%s" updated successfully with %s execution method!', $tool_name, ucwords( str_replace( '_', ' ', $execution_method ) ) ),
				)
			);
		} else {
			wp_send_json_error( 'Failed to update tool. Please try again.' );
		}
	}

	/**
	 * Test a tool
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function test_tool() {
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$tool_id   = sanitize_text_field( $_POST['tool_id'] );
		$test_data = $_POST['test_data'] ?? array();

		$tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tool_id ) );

		if ( ! $tool ) {
			wp_send_json_error( 'Tool not found' );
		}

		// Execute the tool
		$endpoint = $tool->mcp_tool_api_endpoint;
		$method   = strtoupper( $tool->mcp_tool_http_method );
		$headers  = is_array( $tool->mcp_tool_request_headers ) ? $tool->mcp_tool_request_headers : array();

		$args = array(
			'method'    => $method,
			'headers'   => array_merge(
				array(
					'Content-Type' => 'application/json',
					'User-Agent'   => 'FlowMattic-MCP-Test/1.0',
				),
				$headers
			),
			'timeout'   => 30,
			'sslverify' => false,
		);

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) && ! empty( $test_data ) ) {
			$args['body'] = wp_json_encode( $test_data );
		} elseif ( $method === 'GET' && ! empty( $test_data ) ) {
			$endpoint = add_query_arg( $test_data, $endpoint );
		}

		$response = wp_remote_request( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( 'Request failed: ' . $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		wp_send_json_success(
			array(
				'message'  => 'Tool test completed',
				'response' => array(
					'status_code' => $response_code,
					'body'        => $response_body,
					'success'     => $response_code >= 200 && $response_code < 300,
				),
			)
		);
	}

	/**
	 * Duplicate a tool
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function duplicate_tool() {
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$tool_id     = sanitize_text_field( $_POST['tool_id'] );
		$new_tool_id = wp_flowmattic()->mcp_server_db->clone( $tool_id );

		if ( $new_tool_id ) {
			wp_send_json_success(
				array(
					'message'     => 'Tool duplicated successfully!',
					'new_tool_id' => $new_tool_id,
				)
			);
		} else {
			wp_send_json_error( 'Failed to duplicate tool' );
		}
	}

	/**
	 * Delete a tool.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return bool True on success, false on failure.
	 */
	public function delete_tool() {
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$tool_id = sanitize_text_field( $_POST['tool_id'] );
		$result  = wp_flowmattic()->mcp_server_db->delete( $tool_id );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => 'Tool deleted successfully!',
				)
			);
		} else {
			wp_send_json_error( 'Failed to delete tool' );
		}
	}

	/**
	 * Update server settings
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function update_server_settings() {
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$server_id        = sanitize_text_field( $_POST['server_id'] );
		$server_name      = sanitize_text_field( $_POST['server_name'] );
		$refresh_interval = intval( $_POST['refresh_interval'] );
		$active           = isset( $_POST['active'] ) && $_POST['active'] ? true : false;

		// Update server settings
		$settings = array(
			'name'             => $server_name,
			'refresh_interval' => $refresh_interval,
			'active'           => $active,
		);

		update_option( 'flowmattic_mcp_server_' . $server_id, $settings );

		wp_send_json_success(
			array(
				'message' => 'Server settings updated successfully!',
			)
		);
	}

	/**
	 * Log tool execution to database
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $server_id Server ID
	 * @param object $tool Tool object
	 * @param array  $arguments Arguments passed to the tool
	 * @param array  $result Execution result
	 * @param array  $metadata Additional metadata
	 * @return void
	 */
	private function log_tool_execution( $server_id, $tool, $arguments, $result, $metadata = array() ) {
		// Get tool ID
		$tool_id = is_object( $tool ) ? $tool->mcp_tool_id : $tool['mcp_tool_id'];

		// Prepare execution data
		$execution_data = array(
			'mcp_server_id'       => $server_id,
			'mcp_tool_id'         => $tool_id,
			'execution_arguments' => $arguments,
			'execution_result'    => $result,
			'execution_metadata'  => array_merge(
				array(
					'client_type'    => $this->mcp_client_id,
					'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
					'ip_address'     => $this->get_client_ip(),
					'execution_time' => $result['execution_time'] ?? 0,
				),
				$metadata
			),
		);

		// Insert execution log
		wp_flowmattic()->mcp_executions_db->insert( $execution_data );
	}

	/**
	 * Get client IP address
	 *
	 * @access private
	 * @since 5.2.0
	 * @return string
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}

	/**
	 * AJAX handler to get execution details
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function get_execution_details() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$execution_id = sanitize_text_field( $_POST['execution_id'] );

		if ( empty( $execution_id ) ) {
			wp_send_json_error( 'Execution ID is required' );
		}

		// Initialize executions database if not exists
		if ( ! isset( wp_flowmattic()->mcp_executions_db ) ) {
			wp_flowmattic()->mcp_executions_db = new FlowMattic_Database_MCP_Tool_Executions();
		}

		$execution = wp_flowmattic()->mcp_executions_db->get( $execution_id );

		if ( ! $execution ) {
			wp_send_json_error( 'Execution not found' );
		}

		// Get server ID from execution
		$server_id = $execution->mcp_server_id;

		// Get tool details
		$tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $execution->mcp_tool_id ) );

		if ( ! $tool ) {
			$mcp_server     = FlowMattic_MCP_Server::get_instance();
			$plugin_tools   = $mcp_server->get_all_plugin_tools( $server_id );
			$external_tools = array();

			if ( ! empty( $plugin_tools ) ) {
				foreach ( $plugin_tools as $tool_slug => $tool ) {
					$mcp_tool_id = str_replace( array( ' ', '-' ), '_', strtolower( $tool_slug ) );

					$external_tools[ $mcp_tool_id ] = array(
						'mcp_tool_id'                => $mcp_tool_id,
						'name'                       => $tool['tool']['name'],
						'mcp_tool_name'              => $tool['tool']['name'],
						'description'                => $tool['tool']['description'],
						'mcp_tool_execution_method'  => 'php_function',
						'function_name'              => $tool['tool']['function_name'],
						'source'                     => 'plugin',
						'source_name'                => $tool['plugin'],
					);
				}
			}

			if ( isset( $external_tools[ $execution->mcp_tool_id ] ) ) {
				$tool = (object) $external_tools[ $execution->mcp_tool_id ];
			}
		}

		wp_send_json_success(
			array(
				'execution' => $execution,
				'tool'      => $tool,
			)
		);
	}

	/**
	 * AJAX handler to get execution history
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function get_execution_history() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$server_id = sanitize_text_field( $_POST['server_id'] );
		$tool_id   = sanitize_text_field( $_POST['tool_id'] ?? '' );
		$page      = intval( $_POST['page'] ?? 1 );
		$per_page  = intval( $_POST['per_page'] ?? 10 );

		// Initialize executions database if not exists
		if ( ! isset( wp_flowmattic()->mcp_executions_db ) ) {
			wp_flowmattic()->mcp_executions_db = new FlowMattic_Database_MCP_Tool_Executions();
		}

		$executions = wp_flowmattic()->mcp_executions_db->get_all( $server_id, $tool_id, $page, $per_page );
		$total      = wp_flowmattic()->mcp_executions_db->get_count( $server_id, $tool_id );

		$mcp_server     = FlowMattic_MCP_Server::get_instance();
		$plugin_tools   = $mcp_server->get_all_plugin_tools( $server_id );
		$external_tools = array();

		if ( ! empty( $plugin_tools ) ) {
			foreach ( $plugin_tools as $tool_slug => $tool ) {
				$mcp_tool_id = str_replace( array( ' ', '-' ), '_', strtolower( $tool_slug ) );

				$external_tools[ $mcp_tool_id ] = array(
					'mcp_tool_id'                => $mcp_tool_id,
					'name'                       => $tool['tool']['name'],
					'mcp_tool_name'              => $tool['tool']['name'],
					'description'                => $tool['tool']['description'],
					'mcp_tool_execution_method'  => 'php_function',
					'function_name'              => $tool['tool']['function_name'],
					'source'                     => 'plugin',
					'source_name'                => $tool['plugin'],
				);
			}
		}

		// Get tool names for display
		$tool_names = array();
		if ( ! empty( $executions ) ) {
			$tool_ids = array_unique( array_column( $executions, 'mcp_tool_id' ) );
			foreach ( $tool_ids as $tid ) {
				$tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tid ) );

				if ( $tool ) {
					$tool_names[ $tid ] = $tool->mcp_tool_name;
				} elseif ( isset( $external_tools[ $tid ] ) ) {
					$tool_names[ $tid ] = $external_tools[ $tid ]['mcp_tool_name'];
				} else {
					// Fallback to tool ID if not found
					$tool_names[ $tid ] = 'Unknown Tool (' . $tid . ')';
				}
			}
		}

		wp_send_json_success(
			array(
				'executions'  => $executions,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
				'tool_names'  => $tool_names,
			)
		);
	}

	/**
	 * Export execution history (AJAX handler)
	 *
	 * @since 5.2.0
	 * @access public
	 * @return void
	 */
	public function export_execution_history() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['workflow_nonce'], 'flowmattic_workflow_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$server_id  = sanitize_text_field( $_POST['server_id'] );
		$tool_id    = sanitize_text_field( $_POST['tool_id'] ?? '' );
		$start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
		$end_date   = sanitize_text_field( $_POST['end_date'] ?? '' );
		$format     = sanitize_text_field( $_POST['format'] ?? 'csv' );

		if ( empty( $server_id ) ) {
			wp_send_json_error( 'Server ID is required' );
		}

		// Initialize executions database if not exists
		if ( ! isset( wp_flowmattic()->mcp_executions_db ) ) {
			wp_flowmattic()->mcp_executions_db = new FlowMattic_Database_MCP_Tool_Executions();
		}

		try {
			if ( $format === 'json' ) {
				$download_url = $this->generate_json_export( $server_id, $tool_id, $start_date, $end_date );
			} else {
				$download_url = $this->generate_csv_export( $server_id, $tool_id, $start_date, $end_date );
			}

			wp_send_json_success(
				array(
					'message'      => 'Export generated successfully',
					'download_url' => $download_url,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error( 'Export failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Generate CSV export
	 *
	 * @since 5.2.0
	 * @access private
	 * @param string $server_id Server ID
	 * @param string $tool_id Tool ID (optional)
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return string Download URL
	 */
	private function generate_csv_export( $server_id, $tool_id = '', $start_date = '', $end_date = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'flowmattic_mcp_tool_executions';

		// Build WHERE clause
		$where_conditions = array( 'mcp_server_id = %s' );
		$prepare_args     = array( $server_id );

		if ( ! empty( $tool_id ) ) {
			$where_conditions[] = 'mcp_tool_id = %s';
			$prepare_args[]     = $tool_id;
		}

		if ( ! empty( $start_date ) ) {
			$where_conditions[] = 'DATE(execution_created) >= %s';
			$prepare_args[]     = $start_date;
		}

		if ( ! empty( $end_date ) ) {
			$where_conditions[] = 'DATE(execution_created) <= %s';
			$prepare_args[]     = $end_date;
		}

		$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

		// Get executions
		$query      = "SELECT * FROM `{$table_name}` {$where_clause} ORDER BY execution_created DESC LIMIT 10000";
		$executions = $wpdb->get_results( $wpdb->prepare( $query, $prepare_args ) );

		// Get tool names
		$tool_names = array();
		if ( ! empty( $executions ) ) {
			$tool_ids = array_unique( array_column( $executions, 'mcp_tool_id' ) );
			foreach ( $tool_ids as $tid ) {
				$tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tid ) );
				if ( $tool ) {
					$tool_names[ $tid ] = $tool->mcp_tool_name;
				}
			}
		}

		// Create temporary file
		$upload_dir = wp_upload_dir();
		$filename   = 'mcp-execution-history-' . date( 'Y-m-d-H-i-s' ) . '.csv';
		$file_path  = $upload_dir['path'] . '/' . $filename;
		$file_url   = $upload_dir['url'] . '/' . $filename;

		$file = fopen( $file_path, 'w' );

		// CSV headers
		fputcsv(
			$file,
			array(
				'Execution ID',
				'Tool Name',
				'Tool ID',
				'Status',
				'Execution Time (s)',
				'Client Type',
				'IP Address',
				'User Agent',
				'Executed At',
				'Arguments (JSON)',
				'Result (JSON)',
			)
		);

		// CSV data
		foreach ( $executions as $execution ) {
			$execution_data = maybe_unserialize( $execution->execution_result );
			$execution_args = maybe_unserialize( $execution->execution_arguments );
			$execution_meta = maybe_unserialize( $execution->execution_metadata );

			$is_success     = ! ( isset( $execution_data['error'] ) && $execution_data['error'] );
			$tool_name      = $tool_names[ $execution->mcp_tool_id ] ?? $execution->mcp_tool_id;
			$execution_time = $execution_meta['execution_time'] ?? 0;
			$client_type    = $execution_meta['client_type'] ?? 'Unknown';
			$ip_address     = $execution_meta['ip_address'] ?? 'N/A';
			$user_agent     = $execution_meta['user_agent'] ?? 'N/A';

			fputcsv(
				$file,
				array(
					$execution->mcp_tool_execution_id,
					$tool_name,
					$execution->mcp_tool_id,
					$is_success ? 'Success' : 'Error',
					$execution_time,
					$client_type,
					$ip_address,
					$user_agent,
					$execution->execution_created,
					wp_json_encode( $execution_args ),
					wp_json_encode( $execution_data ),
				)
			);
		}

		fclose( $file );

		// Schedule file cleanup after 1 hour
		wp_schedule_single_event( time() + 3600, 'flowmattic_cleanup_export_file', array( $file_path ) );

		return $file_url;
	}

	/**
	 * Generate JSON export
	 *
	 * @since 5.2.0
	 * @access private
	 * @param string $server_id Server ID
	 * @param string $tool_id Tool ID (optional)
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return string Download URL
	 */
	private function generate_json_export( $server_id, $tool_id = '', $start_date = '', $end_date = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'flowmattic_mcp_tool_executions';

		// Build WHERE clause
		$where_conditions = array( 'mcp_server_id = %s' );
		$prepare_args     = array( $server_id );

		if ( ! empty( $tool_id ) ) {
			$where_conditions[] = 'mcp_tool_id = %s';
			$prepare_args[]     = $tool_id;
		}

		if ( ! empty( $start_date ) ) {
			$where_conditions[] = 'DATE(execution_created) >= %s';
			$prepare_args[]     = $start_date;
		}

		if ( ! empty( $end_date ) ) {
			$where_conditions[] = 'DATE(execution_created) <= %s';
			$prepare_args[]     = $end_date;
		}

		$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

		// Get executions
		$query      = "SELECT * FROM `{$table_name}` {$where_clause} ORDER BY execution_created DESC LIMIT 10000";
		$executions = $wpdb->get_results( $wpdb->prepare( $query, $prepare_args ) );

		// Get tool names
		$tool_names = array();
		if ( ! empty( $executions ) ) {
			$tool_ids = array_unique( array_column( $executions, 'mcp_tool_id' ) );
			foreach ( $tool_ids as $tid ) {
				$tool = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tid ) );
				if ( $tool ) {
					$tool_names[ $tid ] = $tool->mcp_tool_name;
				}
			}
		}

		// Prepare data
		$export_data = array(
			'exported_at'      => current_time( 'c' ),
			'server_id'        => $server_id,
			'tool_id'          => $tool_id,
			'date_range'       => array(
				'start' => $start_date,
				'end'   => $end_date,
			),
			'total_executions' => count( $executions ),
			'executions'       => array(),
		);

		foreach ( $executions as $execution ) {
			$execution_data = maybe_unserialize( $execution->execution_result );
			$execution_args = maybe_unserialize( $execution->execution_arguments );
			$execution_meta = maybe_unserialize( $execution->execution_metadata );

			$export_data['executions'][] = array(
				'execution_id'   => $execution->mcp_tool_execution_id,
				'tool_name'      => $tool_names[ $execution->mcp_tool_id ] ?? $execution->mcp_tool_id,
				'tool_id'        => $execution->mcp_tool_id,
				'server_id'      => $execution->mcp_server_id,
				'is_success'     => ! ( isset( $execution_data['error'] ) && $execution_data['error'] ),
				'execution_time' => $execution_meta['execution_time'] ?? 0,
				'client_type'    => $execution_meta['client_type'] ?? 'Unknown',
				'ip_address'     => $execution_meta['ip_address'] ?? 'N/A',
				'user_agent'     => $execution_meta['user_agent'] ?? 'N/A',
				'executed_at'    => $execution->execution_created,
				'arguments'      => $execution_args,
				'result'         => $execution_data,
				'metadata'       => $execution_meta,
			);
		}

		// Create temporary file
		$upload_dir = wp_upload_dir();
		$filename   = 'mcp-execution-history-' . date( 'Y-m-d-H-i-s' ) . '.json';
		$file_path  = $upload_dir['path'] . '/' . $filename;
		$file_url   = $upload_dir['url'] . '/' . $filename;

		file_put_contents( $file_path, wp_json_encode( $export_data, JSON_PRETTY_PRINT ) );

		// Schedule file cleanup after 1 hour
		wp_schedule_single_event( time() + 3600, 'flowmattic_cleanup_export_file', array( $file_path ) );

		return $file_url;
	}

	/**
	 * Schedule MCP executions cleanup
	 *
	 * @since 5.2.0
	 * @access public
	 * @return void
	 */
	public function schedule_mcp_cleanup() {
		if ( ! wp_next_scheduled( 'flowmattic_mcp_cleanup_executions' ) ) {
			wp_schedule_event( time(), 'weekly', 'flowmattic_mcp_cleanup_executions' );
		}
	}

	/**
	 * Cleanup old MCP executions
	 *
	 * @since 5.2.0
	 * @access public
	 * @return void
	 */
	public function cleanup_old_mcp_executions() {
		if ( isset( wp_flowmattic()->mcp_executions_db ) ) {
			// Keep executions for 30 days by default
			$days_to_keep = apply_filters( 'flowmattic_mcp_executions_retention_days', 30 );
			wp_flowmattic()->mcp_executions_db->cleanup_old_executions( $days_to_keep );
		}
	}

	/**
	 * Convert string parameters to appropriate types
	 *
	 * @access private
	 * @since 5.2.0
	 * @param array $arguments The arguments to process
	 * @return array Processed arguments with converted types
	 */
	private function convert_parameter_types( $arguments ) {
		if ( ! is_array( $arguments ) ) {
			return $arguments;
		}

		$converted = array();

		foreach ( $arguments as $key => $value ) {
			if ( is_string( $value ) ) {
				// Check if it's a number
				if ( is_numeric( $value ) ) {
					// Convert to int if it's a whole number, float otherwise
					$converted[ $key ] = ( (float) $value == (int) $value ) ? (int) $value : (float) $value;
				} elseif ( in_array( strtolower( $value ), array( 'true', 'false' ), true ) ) {
					// Convert boolean strings
					$converted[ $key ] = strtolower( $value ) === 'true';
				} else {
					// Keep as string
					$converted[ $key ] = $value;
				}
			} else {
				// Keep non-string values as is
				$converted[ $key ] = $value;
			}
		}

		return $converted;
	}

	/**
	 * Cleanup old MCP response files
	 *
	 * @access public
	 * @since 5.2.0
	 * @param string $response_key Optional specific response file to cleanup
	 * @return void
	 */
	public function cleanup_mcp_response_files( $response_key = '' ) {
		if ( ! empty( $response_key ) ) {
			// Clean up specific response file
			$file_path = sys_get_temp_dir() . "/{$response_key}.json";
			if ( file_exists( $file_path ) ) {
				@unlink( $file_path );
				// Cleaned up specific MCP response file
			}
			return;
		}

		// Clean up all old response files
		$temp_dir = sys_get_temp_dir();
		$pattern  = $temp_dir . '/mcp_response_*.json';
		$files    = glob( $pattern );

		if ( $files ) {
			$cleaned = 0;
			foreach ( $files as $file ) {
				// Delete files older than 1 hour
				if ( file_exists( $file ) && ( time() - filemtime( $file ) ) > 3600 ) {
					@unlink( $file );
					++$cleaned;
				}
			}
			if ( $cleaned > 0 ) {
				// Cleaned up {$cleaned} old MCP response files
			}
		}

		// Also clean up temp files
		$temp_pattern = $temp_dir . '/mcp_response_*.json.tmp';
		$temp_files   = glob( $temp_pattern );
		if ( $temp_files ) {
			foreach ( $temp_files as $file ) {
				@unlink( $file );
			}
		}
	}

	/**
	 * Check if the SSE write was successful
	 *
	 * @access private
	 * @since 5.2.0
	 * @return bool True if write successful, false otherwise
	 */
	private function sse_write_check() {
		if ( ob_get_level() ) {
			ob_flush();
		}

		$result = flush();

		// Check various disconnect indicators
		if ( connection_aborted() || connection_status() !== CONNECTION_NORMAL ) {
			return false;
		}

		return true;
	}

	/**
	 * Cleanup SSE connection
	 *
	 * @access private
	 * @since 5.2.0
	 * @param string $session_id Session ID
	 * @return void
	 */
	private function cleanup_sse_connection( $session_id ) {
		// Remove from active connections
		$active_connections = get_option( 'mcp_active_sse_connections', array() );
		if ( isset( $active_connections[ $session_id ] ) ) {
			unset( $active_connections[ $session_id ] );
			update_option( 'mcp_active_sse_connections', $active_connections, false );
		}

		// Clean up temp files
		$temp_file = sys_get_temp_dir() . "/mcp_response_{$session_id}.json";
		if ( file_exists( $temp_file ) ) {
			@unlink( $temp_file );
		}
	}

	/**
	 * Manual cleanup of all zombie connections
	 * Call this method to force cleanup all old SSE connections
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function force_cleanup_zombie_connections() {
		$active_connections = get_option( 'mcp_active_sse_connections', array() );
		$cleaned            = 0;

		foreach ( $active_connections as $session_id => $data ) {
			// Clean up any connection older than 1 minute
			if ( ( time() - $data['last_activity'] ) > 60 ) {
				$this->cleanup_sse_connection( $session_id );
				++$cleaned;
			}
		}

		// Force clean all temp response files
		$temp_dir = sys_get_temp_dir();
		$pattern  = $temp_dir . '/mcp_response_*.json*';
		$files    = glob( $pattern );
		if ( $files ) {
			foreach ( $files as $file ) {
				@unlink( $file );
				++$cleaned;
			}
		}

		return $cleaned;
	}

	/**
	 * Enhanced cleanup for old sessions
	 */
	public function cleanup_old_sessions() {
		global $wpdb;

		// First force cleanup zombie connections
		$this->force_cleanup_zombie_connections();

		$cleaned_count = 0;

		// Clean up old session options (older than 1 hour instead of 2)
		$old_options = $wpdb->get_results(
			"
        SELECT option_name 
        FROM {$wpdb->options} 
        WHERE option_name LIKE 'mcp_session_%' 
        OR option_name LIKE 'mcp_queue_%'
        OR option_name LIKE 'mcp_has_response_%'
        OR option_name LIKE 'mcp_response_%'
        OR option_name LIKE 'mcp_fast_response_%'
        OR option_name LIKE 'mcp_has_fast_response_%'
    "
		);

		foreach ( $old_options as $option ) {
			delete_option( $option->option_name );
			++$cleaned_count;
		}

		// Also clean up temp files
		$this->cleanup_mcp_response_files();
	}

	/**
	 * Force cleanup all connections (for debugging)
	 *
	 * @access public
	 * @since 5.2.0
	 * @return int Number of connections cleaned
	 */
	public function force_cleanup_all_connections() {
		delete_transient( 'mcp_active_connections' );

		// Clean all temp files
		$temp_dir = sys_get_temp_dir();
		$pattern  = $temp_dir . '/mcp_response_*.json';
		$files    = glob( $pattern );
		$cleaned  = 0;

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( unlink( $file ) ) {
					++$cleaned;
				}
			}
		}

		return $cleaned;
	}
}
