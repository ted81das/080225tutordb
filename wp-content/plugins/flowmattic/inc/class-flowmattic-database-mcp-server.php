<?php
/**
 * Handle database queries for MCP Server.
 *
 * @package flowmattic
 * @since 5.2.0
 */

/**
 * Handle database queries for MCP Server.
 *
 * @since 5.2.0
 */
class FlowMattic_Database_MCP_Server {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 5.2.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_mcp_server';

	/**
	 * The Constructor.
	 *
	 * @since 5.2.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert MCP server tool to database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Check if required fields are provided.
		if ( ! isset( $args['mcp_server_id'] ) || ! isset( $args['mcp_tool_name'] ) ) {
			return false;
		}

		// Prepare data for insertion.
		$data = array(
			'mcp_server_id'             => sanitize_text_field( $args['mcp_server_id'] ),
			'mcp_tool_id'               => isset( $args['mcp_tool_id'] ) ? sanitize_text_field( $args['mcp_tool_id'] ) : wp_generate_uuid4(),
			'mcp_tool_name'             => sanitize_text_field( $args['mcp_tool_name'] ),
			'mcp_tool_description'      => isset( $args['mcp_tool_description'] ) ? sanitize_textarea_field( $args['mcp_tool_description'] ) : '',
			'mcp_tool_execution_method' => isset( $args['mcp_tool_execution_method'] ) ? sanitize_text_field( $args['mcp_tool_execution_method'] ) : 'api_endpoint',
			'mcp_tool_function_name'    => isset( $args['mcp_tool_function_name'] ) ? sanitize_text_field( $args['mcp_tool_function_name'] ) : '',
			'mcp_tool_db_query'         => isset( $args['mcp_tool_db_query'] ) ? sanitize_text_field( stripslashes( $args['mcp_tool_db_query'] ) ) : '',
			'mcp_tool_workflow_id'      => isset( $args['mcp_tool_workflow_id'] ) ? sanitize_text_field( $args['mcp_tool_workflow_id'] ) : '',
			'mcp_tool_api_endpoint'     => isset( $args['mcp_tool_api_endpoint'] ) ? esc_url_raw( $args['mcp_tool_api_endpoint'] ) : '',
			'mcp_tool_http_method'      => isset( $args['mcp_tool_http_method'] ) ? strtoupper( sanitize_text_field( $args['mcp_tool_http_method'] ) ) : 'GET',
			'mcp_tool_request_headers'  => isset( $args['mcp_tool_request_headers'] ) ? wp_json_encode( $args['mcp_tool_request_headers'] ) : '',
			'mcp_tool_input_schema'     => isset( $args['mcp_tool_input_schema'] ) ? $args['mcp_tool_input_schema'] : '',
			'mcp_tool_webhook_url'      => isset( $args['mcp_tool_webhook_url'] ) ? esc_url_raw( $args['mcp_tool_webhook_url'] ) : '',
			// Future-proofing: Add metadata field.
			'mcp_tool_metadata'         => isset( $args['mcp_tool_metadata'] ) ? maybe_serialize( $args['mcp_tool_metadata'] ) : '',
		);

		// Validate HTTP method if provided.
		if ( ! empty( $data['mcp_tool_http_method'] ) ) {
			$allowed_methods = array( 'GET', 'POST', 'PUT', 'DELETE', 'PATCH' );
			if ( ! in_array( $data['mcp_tool_http_method'], $allowed_methods, true ) ) {
				$data['mcp_tool_http_method'] = 'GET';
			}
		}

		// Validate execution method.
		$allowed_execution_methods = array( 'php_function', 'workflow_id', 'api_endpoint', 'database_query' );
		if ( ! in_array( $data['mcp_tool_execution_method'], $allowed_execution_methods, true ) ) {
			$data['mcp_tool_execution_method'] = 'api_endpoint';
		}

		// If the input schema is an array, serialize it.
		if ( is_array( $data['mcp_tool_input_schema'] ) ) {
			$data['mcp_tool_input_schema'] = maybe_serialize( $data['mcp_tool_input_schema'] );
		} elseif ( is_string( $data['mcp_tool_input_schema'] ) && ( strpos( $data['mcp_tool_input_schema'], '{' ) === 0 || strpos( $data['mcp_tool_input_schema'], '[' ) === 0 ) ) {
			// If it's a JSON string, decode it and re-encode to ensure it's properly formatted.
			$decoded_schema = json_decode( $data['mcp_tool_input_schema'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$data['mcp_tool_input_schema'] = maybe_serialize( $decoded_schema );
			} else {
				$data['mcp_tool_input_schema'] = '';
			}
		}

		// If metadata is an array, serialize it.
		if ( is_array( $data['mcp_tool_metadata'] ) && ! empty( $data['mcp_tool_metadata'] ) ) {
			$data['mcp_tool_metadata'] = maybe_serialize( $data['mcp_tool_metadata'] );
		}

		$result = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			$data,
			array(
				'%s', // mcp_server_id
				'%s', // mcp_tool_id
				'%s', // mcp_tool_name
				'%s', // mcp_tool_description
				'%s', // mcp_tool_execution_method
				'%s', // mcp_tool_function_name
				'%s', // mcp_tool_db_query
				'%s', // mcp_tool_workflow_id
				'%s', // mcp_tool_api_endpoint
				'%s', // mcp_tool_http_method
				'%s', // mcp_tool_request_headers
				'%s', // mcp_tool_input_schema
				'%s', // mcp_tool_webhook_url
			)
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update the MCP server tool in database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param array  $args The arguments.
	 * @param string $tool_id The tool ID.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( $args, $tool_id ) {
		global $wpdb;

		// Check if tool ID is provided.
		if ( empty( $tool_id ) ) {
			return false;
		}

		// Prepare update data.
		$update_data = array();
		$format      = array();

		if ( isset( $args['mcp_tool_name'] ) ) {
			$update_data['mcp_tool_name'] = sanitize_text_field( $args['mcp_tool_name'] );
			$format[]                     = '%s';
		}

		if ( isset( $args['mcp_tool_description'] ) ) {
			$update_data['mcp_tool_description'] = sanitize_textarea_field( $args['mcp_tool_description'] );
			$format[]                            = '%s';
		}

		if ( isset( $args['mcp_tool_api_endpoint'] ) ) {
			$update_data['mcp_tool_api_endpoint'] = esc_url_raw( $args['mcp_tool_api_endpoint'] );
			$format[]                             = '%s';
		}

		if ( isset( $args['mcp_tool_http_method'] ) ) {
			$method          = strtoupper( sanitize_text_field( $args['mcp_tool_http_method'] ) );
			$allowed_methods = array( 'GET', 'POST', 'PUT', 'DELETE', 'PATCH' );
			if ( in_array( $method, $allowed_methods, true ) ) {
				$update_data['mcp_tool_http_method'] = $method;
				$format[]                            = '%s';
			}
		}

		if ( isset( $args['mcp_tool_request_headers'] ) ) {
			$update_data['mcp_tool_request_headers'] = wp_json_encode( $args['mcp_tool_request_headers'] );
			$format[]                                = '%s';
		}

		if ( isset( $args['mcp_tool_input_schema'] ) ) {
			$update_data['mcp_tool_input_schema'] = maybe_serialize( $args['mcp_tool_input_schema'] );
			$format[]                             = '%s';
		}

		if ( isset( $args['mcp_tool_webhook_url'] ) ) {
			$update_data['mcp_tool_webhook_url'] = esc_url_raw( $args['mcp_tool_webhook_url'] );
			$format[]                            = '%s';
		}

		if ( isset( $args['mcp_tool_execution_method'] ) ) {
			$allowed_execution_methods = array( 'php_function', 'workflow_id', 'api_endpoint', 'database_query' );
			if ( in_array( $args['mcp_tool_execution_method'], $allowed_execution_methods, true ) ) {
				$update_data['mcp_tool_execution_method'] = sanitize_text_field( $args['mcp_tool_execution_method'] );
				$format[]                                 = '%s';
			}
		}

		if ( isset( $args['mcp_tool_function_name'] ) ) {
			$update_data['mcp_tool_function_name'] = sanitize_text_field( $args['mcp_tool_function_name'] );
			$format[]                              = '%s';
		}

		if ( isset( $args['mcp_tool_db_query'] ) ) {
			$update_data['mcp_tool_db_query'] = sanitize_text_field( $args['mcp_tool_db_query'] );
			$format[]                         = '%s';
		}

		if ( isset( $args['mcp_tool_workflow_id'] ) ) {
			$update_data['mcp_tool_workflow_id'] = sanitize_text_field( $args['mcp_tool_workflow_id'] );
			$format[]                            = '%s';
		}

		// Future-proofing: Add metadata field.
		if ( isset( $args['mcp_tool_metadata'] ) ) {
			if ( is_array( $args['mcp_tool_metadata'] ) && ! empty( $args['mcp_tool_metadata'] ) ) {
				$update_data['mcp_tool_metadata'] = maybe_serialize( $args['mcp_tool_metadata'] );
			} else {
				$update_data['mcp_tool_metadata'] = '';
			}
			$format[] = '%s';
		}

		// Return false if no data to update.
		if ( empty( $update_data ) ) {
			return false;
		}

		return $wpdb->update(
			$wpdb->prefix . $this->table_name,
			$update_data,
			array( 'mcp_tool_id' => $tool_id ),
			$format,
			array( '%s' )
		);
	}

	/**
	 * Clone the MCP server tool in database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $tool_id The tool ID.
	 * @return string|false New tool ID on success, false on failure.
	 */
	public function clone( $tool_id ) {
		global $wpdb;

		// Check if tool ID is provided.
		if ( empty( $tool_id ) ) {
			return false;
		}

		// Get the original tool.
		$original_tool = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `mcp_tool_id` = %s',
				$tool_id
			),
			ARRAY_A
		);

		if ( ! $original_tool ) {
			return false;
		}

		// Generate new tool ID and modify name.
		$new_tool_id                    = wp_generate_uuid4();
		$original_tool['mcp_tool_id']   = $new_tool_id;
		$original_tool['mcp_tool_name'] = $original_tool['mcp_tool_name'] . ' - ' . esc_attr__( 'Copy', 'flowmattic' );

		// Remove the ID field as it's auto-increment.
		unset( $original_tool['id'] );

		// Insert the cloned tool.
		$result = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			$original_tool,
			array(
				'%s', // mcp_server_id
				'%s', // mcp_tool_id
				'%s', // mcp_tool_name
				'%s', // mcp_tool_description
				'%s', // mcp_tool_execution_method
				'%s', // mcp_tool_function_name
				'%s', // mcp_tool_db_query
				'%s', // mcp_tool_workflow_id
				'%s', // mcp_tool_api_endpoint
				'%s', // mcp_tool_http_method
				'%s', // mcp_tool_request_headers
				'%s', // mcp_tool_input_schema
				'%s',  // mcp_tool_webhook_url
				// Future-proofing: Add metadata field.
				'%s' // mcp_tool_metadata
			)
		);

		return $result ? $new_tool_id : false;
	}

	/**
	 * Delete the MCP server tool from database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $tool_id The tool ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( $tool_id ) {
		global $wpdb;

		// Check if tool ID is provided.
		if ( empty( $tool_id ) ) {
			return false;
		}

		return $wpdb->delete(
			$wpdb->prefix . $this->table_name,
			array( 'mcp_tool_id' => $tool_id ),
			array( '%s' )
		);
	}

	/**
	 * Get the MCP server tool from database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param array $args The arguments.
	 * @return object|null
	 */
	public function get( $args ) {
		global $wpdb;

		// Check if tool ID is provided.
		if ( ! isset( $args['mcp_tool_id'] ) ) {
			return null;
		}

		// Clean tool ID (remove curly braces if present).
		$tool_id = str_replace( array( '{', '}' ), '', $args['mcp_tool_id'] );

		$tool = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `mcp_tool_id` = %s',
				$tool_id
			)
		);

		// Decode JSON fields.
		if ( $tool ) {
			$tool = $this->prepare_tool_output( $tool );
		}

		return $tool;
	}

	/**
	 * Get all MCP Server tools from database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id The server ID to filter by.
	 * @param int    $offset The pagination offset.
	 * @param int    $limit The number of results to return.
	 * @return array
	 */
	public function get_all( $server_id = '', $offset = 0, $limit = 100 ) {
		global $wpdb;

		$where_clause = '';
		$prepare_args = array();

		if ( ! empty( $server_id ) ) {
			$where_clause   = ' WHERE `mcp_server_id` = %s';
			$prepare_args[] = $server_id;
		}

		$prepare_args[] = $offset;
		$prepare_args[] = $limit;

		$query = 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '`' . $where_clause . ' ORDER BY `id` ASC LIMIT %d, %d';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, $prepare_args )
		);

		// Prepare output for each tool.
		if ( $results ) {
			foreach ( $results as $key => $tool ) {
				$results[ $key ] = $this->prepare_tool_output( $tool );
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Get tools by server ID.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id The server ID.
	 * @return array
	 */
	public function get_tools_by_server( $server_id ) {
		global $wpdb;

		if ( empty( $server_id ) ) {
			return array();
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `mcp_server_id` = %s ORDER BY `mcp_tool_name` ASC',
				$server_id
			)
		);

		// Prepare output for each tool.
		if ( $results ) {
			foreach ( $results as $key => $tool ) {
				$results[ $key ] = $this->prepare_tool_output( $tool );
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Get the total MCP Server tools count.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id Optional server ID to filter by.
	 * @return int
	 */
	public function get_tools_count( $server_id = '' ) {
		global $wpdb;

		$where_clause = '';
		$prepare_args = array();

		if ( ! empty( $server_id ) ) {
			$where_clause   = ' WHERE `mcp_server_id` = %s';
			$prepare_args[] = $server_id;
		}

		$query = 'SELECT COUNT(*) FROM `' . $wpdb->prefix . $this->table_name . '`' . $where_clause;

		if ( ! empty( $prepare_args ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $query, $prepare_args ) );
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Check if tool name exists for a server.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $tool_name The tool name.
	 * @param string $server_id The server ID.
	 * @param string $exclude_tool_id Optional tool ID to exclude from check.
	 * @return bool
	 */
	public function tool_name_exists( $tool_name, $server_id, $exclude_tool_id = '' ) {
		global $wpdb;

		$where_clause = 'WHERE `mcp_tool_name` = %s AND `mcp_server_id` = %s';
		$prepare_args = array( $tool_name, $server_id );

		if ( ! empty( $exclude_tool_id ) ) {
			$where_clause  .= ' AND `mcp_tool_id` != %s';
			$prepare_args[] = $exclude_tool_id;
		}

		$query = 'SELECT COUNT(*) FROM `' . $wpdb->prefix . $this->table_name . '` ' . $where_clause;

		$count = $wpdb->get_var( $wpdb->prepare( $query, $prepare_args ) );

		return $count > 0;
	}

	/**
	 * Prepare tool output by decoding JSON fields.
	 *
	 * @since 5.2.0
	 * @access private
	 * @param object $tool The tool object.
	 * @return object
	 */
	private function prepare_tool_output( $tool ) {
		// Decode JSON fields.
		if ( ! empty( $tool->mcp_tool_request_headers ) ) {
			$decoded_headers                = json_decode( $tool->mcp_tool_request_headers, true );
			$tool->mcp_tool_request_headers = is_array( $decoded_headers ) ? $decoded_headers : array();
		} else {
			$tool->mcp_tool_request_headers = array();
		}

		if ( $tool->mcp_tool_input_schema ) {
			$input_schema = $tool->mcp_tool_input_schema;

			// Remove the starting and ending double quotes if they exist.
			if ( is_string( $input_schema ) && ( strpos( $input_schema, '"' ) === 0 || strpos( $input_schema, "'" ) === 0 ) ) {
				$input_schema = trim( $input_schema, '"\'' );
			}

			$decoded_schema = maybe_unserialize( $input_schema );
			try {
				$decoded_schema = maybe_unserialize( $tool->mcp_tool_input_schema );
			} catch ( Error $e ) {
				$decoded_schema = array();
			}

			$tool->mcp_tool_input_schema = $decoded_schema;
		} else {
			$tool->mcp_tool_input_schema = array();
		}

		if ( ! empty( $tool->mcp_tool_metadata ) ) {
			$decoded_metadata = maybe_unserialize( $tool->mcp_tool_metadata );
			$tool->mcp_tool_metadata = is_array( $decoded_metadata ) ? $decoded_metadata : array();
		} else {
			$tool->mcp_tool_metadata = array();
		}

		return $tool;
	}

	/**
	 * Validate tool data before insert/update.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param array $args The tool data.
	 * @return array|WP_Error Array of validated data or WP_Error on failure.
	 */
	public function validate_tool_data( $args ) {
		$errors = array();

		// Required fields validation.
		if ( empty( $args['mcp_tool_name'] ) ) {
			$errors[] = __( 'Tool name is required.', 'flowmattic' );
		}

		// Get execution method, default to api_endpoint
		$execution_method = isset( $args['mcp_tool_execution_method'] ) ? $args['mcp_tool_execution_method'] : 'api_endpoint';

		// Validate execution method
		$allowed_execution_methods = array( 'php_function', 'workflow_id', 'api_endpoint', 'database_query' );
		if ( ! in_array( $execution_method, $allowed_execution_methods, true ) ) {
			$errors[] = __( 'Invalid execution method.', 'flowmattic' );
		}

		// Validate based on execution method
		switch ( $execution_method ) {
			case 'php_function':
				if ( empty( $args['mcp_tool_function_name'] ) ) {
					$errors[] = __( 'PHP function name is required for PHP Function execution method.', 'flowmattic' );
				} elseif ( ! function_exists( $args['mcp_tool_function_name'] ) ) {
					$errors[] = sprintf( __( 'PHP function "%s" does not exist.', 'flowmattic' ), $args['mcp_tool_function_name'] );
				}
				break;

			case 'workflow_id':
				if ( empty( $args['mcp_tool_workflow_id'] ) ) {
					$errors[] = __( 'Workflow ID is required for Workflow execution method.', 'flowmattic' );
				}
				// Optional: Validate if workflow exists
				// if ( ! empty( $args['mcp_tool_workflow_id'] ) && function_exists( 'flowmattic_workflow_exists' ) ) {
				//     if ( ! flowmattic_workflow_exists( $args['mcp_tool_workflow_id'] ) ) {
				//         $errors[] = sprintf( __( 'Workflow with ID "%s" does not exist.', 'flowmattic' ), $args['mcp_tool_workflow_id'] );
				//     }
				// }
				break;

			case 'api_endpoint':
				if ( empty( $args['mcp_tool_api_endpoint'] ) ) {
					$errors[] = __( 'API endpoint is required for API Endpoint execution method.', 'flowmattic' );
				}

				// Validate URL format for API endpoint
				if ( ! empty( $args['mcp_tool_api_endpoint'] ) && ! filter_var( $args['mcp_tool_api_endpoint'], FILTER_VALIDATE_URL ) ) {
					$errors[] = __( 'Invalid API endpoint URL format.', 'flowmattic' );
				}

				// Validate HTTP method for API endpoint
				$allowed_methods = array( 'GET', 'POST', 'PUT', 'DELETE', 'PATCH' );
				if ( ! empty( $args['mcp_tool_http_method'] ) && ! in_array( strtoupper( $args['mcp_tool_http_method'] ), $allowed_methods, true ) ) {
					$errors[] = __( 'Invalid HTTP method.', 'flowmattic' );
				}
				break;
			
			case 'database_query':
				if ( empty( $args['mcp_tool_db_query'] ) ) {
					$errors[] = __( 'Database query is required for Database Query execution method.', 'flowmattic' );
				} elseif ( ! is_string( $args['mcp_tool_db_query'] ) || empty( trim( $args['mcp_tool_db_query'] ) ) ) {
					$errors[] = __( 'Invalid database query format.', 'flowmattic' );
				}

				// Perform a basic validation of the query
				if ( ! preg_match( '/^\s*(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER)\s/i', $args['mcp_tool_db_query'] ) ) {
					$errors[] = __( 'Database query must start with a valid SQL command (SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER).', 'flowmattic' );
				}

				// Fix slashes in the query
				$args['mcp_tool_db_query'] = stripslashes( $args['mcp_tool_db_query'] );

				break;
		}

		// Validate webhook URL if provided (applicable to all methods)
		if ( ! empty( $args['mcp_tool_webhook_url'] ) && ! filter_var( $args['mcp_tool_webhook_url'], FILTER_VALIDATE_URL ) ) {
			$errors[] = __( 'Invalid webhook URL format.', 'flowmattic' );
		}

		// Validate JSON fields
		if ( ! empty( $args['mcp_tool_request_headers'] ) && is_string( $args['mcp_tool_request_headers'] ) ) {
			$decoded = json_decode( $args['mcp_tool_request_headers'], true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$errors[] = __( 'Invalid JSON format in request headers.', 'flowmattic' );
			}
		}

		if ( ! empty( $args['mcp_tool_input_schema'] ) ) {
			// Handle both string and array input schema
			if ( is_string( $args['mcp_tool_input_schema'] ) ) {
				$decoded = json_decode( $args['mcp_tool_input_schema'], true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					$errors[] = __( 'Invalid JSON format in input schema.', 'flowmattic' );
				} else {
					$args['mcp_tool_input_schema'] = $decoded; // Convert to array for further validation
				}
			}

			// Validate schema structure if it's an array
			if ( is_array( $args['mcp_tool_input_schema'] ) ) {
				// Validate required fields in the schema
				if ( isset( $args['mcp_tool_input_schema']['required'] ) && ! is_array( $args['mcp_tool_input_schema']['required'] ) ) {
					$errors[] = __( 'The "required" field in input schema must be an array.', 'flowmattic' );
				}

				// Validate type field
				if ( isset( $args['mcp_tool_input_schema']['type'] ) && ! in_array( $args['mcp_tool_input_schema']['type'], array( 'object', 'array', 'string', 'number', 'boolean' ), true ) ) {
					$errors[] = __( 'Invalid type in input schema.', 'flowmattic' );
				}
			}
		}

		// Validate tool name format (optional - add constraints as needed)
		if ( ! empty( $args['mcp_tool_name'] ) ) {
			// Tool name should not contain special characters that might break MCP protocol
			if ( ! preg_match( '/^[a-zA-Z0-9_\-\s]+$/', $args['mcp_tool_name'] ) ) {
				$errors[] = __( 'Tool name can only contain letters, numbers, underscores, hyphens, and spaces.', 'flowmattic' );
			}

			// Tool name length validation
			if ( strlen( $args['mcp_tool_name'] ) > 100 ) {
				$errors[] = __( 'Tool name cannot exceed 100 characters.', 'flowmattic' );
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', implode( ' ', $errors ), $errors );
		}

		return $args;
	}
}
