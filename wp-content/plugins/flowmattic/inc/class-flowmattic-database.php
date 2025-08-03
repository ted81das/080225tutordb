<?php
/**
 * Creates database tables.
 *
 * @package flowmattic
 * @since 1.0
 */

/**
 * Creates database tables.
 *
 * @since 1.0
 */
class FlowMattic_Database {

	/**
	 * Table arguments.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected $tables = array();

	/**
	 * The Constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		// Database table for workflows.
		$this->tables['flowmattic_workflows'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The workflow ID.
				array(
					'name'     => 'workflow_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Workflow Name.
				array(
					'name' => 'workflow_name', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Workflow Steps.
				array(
					'name' => 'workflow_steps', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Workflow Settings.
				array(
					'name' => 'workflow_settings', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),
			),
		);

		// Database table to store tasks.
		$this->tables['flowmattic_tasks'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The workflow task ID.
				array(
					'name' => 'task_id',
					'type' => 'longtext',
				),

				// The workflow ID of which the task is executed.
				array(
					'name'     => 'workflow_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Task data.
				array(
					'name' => 'task_data', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Task record time.
				array(
					'name' => 'task_time', // Future-proofing.
					'type' => 'datetime', // Can be used to get tasks in specific date range.
				),
			),
		);

		// Database table to store connects.
		// @since 3.0.
		$this->tables['flowmattic_connects'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The FlowMattic connect ID.
				array(
					'name' => 'connect_id',
					'type' => 'longtext',
				),

				// The FlowMattic connect name.
				array(
					'name'     => 'connect_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Connect data.
				array(
					'name' => 'connect_data', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Connect Settings.
				array(
					'name' => 'connect_settings', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Connect created time.
				array(
					'name' => 'connect_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store custom apps.
		// @since 3.0.
		$this->tables['flowmattic_custom_apps'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The FlowMattic app ID.
				array(
					'name' => 'app_id',
					'type' => 'longtext',
				),

				// The FlowMattic app name.
				array(
					'name'     => 'app_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// App actions.
				array(
					'name' => 'app_actions', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// App triggers.
				array(
					'name' => 'app_triggers', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// App settings.
				array(
					'name' => 'app_settings', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// App record time.
				array(
					'name' => 'app_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store Chatbot settings and other data.
		// @since 4.0.
		$this->tables['flowmattic_chatbot'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The FlowMattic chatbot ID.
				array(
					'name' => 'chatbot_id',
					'type' => 'longtext',
				),

				// The FlowMattic chatbot name.
				array(
					'name'     => 'chatbot_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Chatbot data sources.
				array(
					'name' => 'chatbot_data', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Chatbot Settings ( setup ).
				array(
					'name' => 'chatbot_settings', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Chatbot actions.
				array(
					'name' => 'chatbot_actions', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Chatbot styles.
				array(
					'name' => 'chatbot_styles', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Chatbot created time.
				array(
					'name' => 'chatbot_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store Chatbot threads.
		// @since 4.0.
		$this->tables['flowmattic_chatbot_threads'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The FlowMattic chatbot thread ID.
				array(
					'name'     => 'thread_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// OpenAI assistant ID.
				array(
					'name'     => 'assistant_id', // Future-proofing.
					'type'     => 'longtext', // Can contain encoded data.
					'not_null' => true,
				),

				// The FlowMattic chatbot ID, to pick the right threads.
				array(
					'name' => 'chatbot_id',
					'type' => 'longtext',
				),

				// Chatbot thread data.
				array(
					'name' => 'thread_data', // Future-proofing.
					'type' => 'longtext', // Can contain encoded data.
				),

				// Thread created time.
				array(
					'name' => 'thread_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store variables.
		$this->tables['flowmattic_variables'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// Variable name.
				array(
					'name'     => 'variable_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Variable value.
				array(
					'name' => 'variable_value', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Variable description.
				array(
					'name' => 'variable_description', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Variable created time.
				array(
					'name' => 'variable_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store RSS Feed items.
		$this->tables['flowmattic_rss_feed'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// RSS Feed slug.
				array(
					'name'     => 'feed_slug',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// RSS Feed data.
				array(
					'name'     => 'feed_data',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// RSS Feed items.
				array(
					'name' => 'feed_items', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// RSS Feed item published time.
				array(
					'name' => 'item_published', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store custom database connections.
		// @since 5.0.
		$this->tables['flowmattic_data_connections'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// Connection name.
				array(
					'name'     => 'connection_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Connection data.
				array(
					'name'     => 'connection_data',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Connection settings.
				array(
					'name' => 'connection_settings', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Connection created time.
				array(
					'name' => 'connection_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table to store custom tables schema.
		// @since 5.0.
		$this->tables['flowmattic_tables_schema'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// Table name.
				array(
					'name'     => 'table_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Table columns.
				array(
					'name' => 'table_columns', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Table settings.
				array(
					'name' => 'table_settings', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Table created time.
				array(
					'name' => 'table_time', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),
			),
		);

		// Database table for email templates.
		$this->tables['flowmattic_email_templates'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// The FlowMattic email template ID.
				array(
					'name' => 'email_template_id',
					'type' => 'longtext',
				),

				// Email template name.
				array(
					'name'     => 'email_template_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Email template JSON.
				array(
					'name'     => 'email_template_json',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Email template HTML.
				array(
					'name' => 'email_template_html', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Email template dynamic data.
				array(
					'name' => 'email_template_dynamic_data', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Email template created time.
				array(
					'name' => 'email_template_created', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),

				// Email template updated time.
				array(
					'name' => 'email_template_updated', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),

				// Email template meta data.
				array(
					'name' => 'email_template_meta', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Email template sent count.
				array(
					'name' => 'email_template_sent_count', // Future-proofing.
					'type' => 'bigint(20)', // For user's reference.
				),
			),
		);

		// Database table for MCP server tools.
		$this->tables['flowmattic_mcp_server'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// MCP server ID.
				array(
					'name'     => 'mcp_server_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// MCP tool ID.
				array(
					'name'     => 'mcp_tool_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// MCP tool name.
				array(
					'name'     => 'mcp_tool_name',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// MCP tool description.
				array(
					'name' => 'mcp_tool_description', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Execution method field.
				array(
					'name'    => 'mcp_tool_execution_method',
					'type'    => 'varchar(50)',
					'default' => "'api_endpoint'",
				),

				// PHP function name (for php_function method).
				array(
					'name' => 'mcp_tool_function_name',
					'type' => 'longtext',
				),

				// Database query (for database_query method).
				array(
					'name' => 'mcp_tool_db_query',
					'type' => 'longtext',
				),

				// Workflow ID (for workflow_id method).
				array(
					'name' => 'mcp_tool_workflow_id',
					'type' => 'longtext',
				),

				// MCP tool API endpoint.
				array(
					'name'     => 'mcp_tool_api_endpoint',
					'type'     => 'longtext',
				),

				// MCP tool HTTP method.
				array(
					'name'     => 'mcp_tool_http_method',
					'type'     => 'longtext',
				),

				// MCP tool request headers.
				array(
					'name' => 'mcp_tool_request_headers', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// MCP tool Input Schema (JSON).
				array(
					'name' => 'mcp_tool_input_schema', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// MCP tool webhook URL.
				array(
					'name'     => 'mcp_tool_webhook_url',
					'type'     => 'longtext',
				),

				// MCP tool metadata.
				array(
					'name' => 'mcp_tool_metadata', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),
			),
		);

		// Database table for MCP tool executions.
		$this->tables['flowmattic_mcp_tool_executions'] = array(
			'unique_key'  => array( 'id' ),
			'primary_key' => 'id',
			'columns'     => array(
				// The database row ID.
				array(
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				),

				// MCP tool execution ID.
				array(
					'name'     => 'mcp_tool_execution_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// MCP Server ID.
				array(
					'name'     => 'mcp_server_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// MCP tool ID.
				array(
					'name'     => 'mcp_tool_id',
					'type'     => 'longtext',
					'not_null' => true,
				),

				// Execution arguments.
				array(
					'name' => 'execution_arguments', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Execution result.
				array(
					'name' => 'execution_result', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),

				// Execution created time.
				array(
					'name' => 'execution_created', // Future-proofing.
					'type' => 'datetime', // For user's reference.
				),

				// Execution metadata.
				array(
					'name' => 'execution_metadata', // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				),
			),
		);

	}

	/**
	 * Create tables.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;

		// Include file from wp-core if not already loaded.
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Get collation.
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Loop tables.
		 *
		 * Generate the needed query, and create the table.
		 */
		foreach ( $this->tables as $table_name => $table_args ) {
			$query_array = array();

			/**
			 * Loop columns for this table.
			 *
			 * Generates the query fragment for this column
			 * which will be them used to build the final query.
			 */
			foreach ( $table_args['columns'] as $column ) {

				// Basic row properties.
				$query_fragment = array(
					$column['name'],
					$column['type'],
				);

				// Add "NOT NULL" if needed.
				if ( isset( $column['not_null'] ) && $column['not_null'] ) {
					$query_fragment[] = 'NOT NULL';
				}

				// Add "AUTO_INCREMENT" if needed.
				if ( isset( $column['auto_increment'] ) && $column['auto_increment'] ) {
					$query_fragment[] = 'AUTO_INCREMENT';
				}

				// Add "DEFAULT" if needed.
				if ( isset( $column['default'] ) ) {
					$query_fragment[] = "DEFAULT {$column['default']}";
				}

				// Add our row to the query array.
				$query_array[] = implode( ' ', $query_fragment );
			}

			// Add "UNIQUE KEY" if needed.
			if ( isset( $table_args['unique_key'] ) ) {
				foreach ( $table_args['unique_key'] as $unique_key ) {
					$query_array[] = "UNIQUE KEY $unique_key ($unique_key)";
				}
			}

			// Add "PRIMARY KEY" if needed.
			if ( isset( $table_args['primary_key'] ) ) {
				$query_array[] = "PRIMARY KEY {$table_args['primary_key']} ({$table_args['primary_key']})";
			}

			// Build the query string.
			$columns_query_string = implode( ', ', $query_array );

			// Run the SQL query.
			$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}$table_name` ($columns_query_string) $charset_collate" ); // @codingStandardsIgnoreLine

			update_option( 'flowmattic_data_tables_created', true, false );
		}
	}
}
