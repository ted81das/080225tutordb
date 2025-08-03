<?php
/**
 * Handle database queries for MCP Tool Executions.
 *
 * @package flowmattic
 * @since 5.2.0
 */

/**
 * Handle database queries for MCP Tool Executions.
 *
 * @since 5.2.0
 */
class FlowMattic_Database_MCP_Tool_Executions {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 5.2.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_mcp_tool_executions';

	/**
	 * The Constructor.
	 *
	 * @since 5.2.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert MCP tool execution to database.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Check if required fields are provided.
		if ( ! isset( $args['mcp_server_id'] ) || ! isset( $args['mcp_tool_id'] ) ) {
			return false;
		}

		// Prepare data for insertion.
		$data = array(
			'mcp_tool_execution_id' => isset( $args['mcp_tool_execution_id'] ) ? sanitize_text_field( $args['mcp_tool_execution_id'] ) : wp_generate_uuid4(),
			'mcp_server_id'         => sanitize_text_field( $args['mcp_server_id'] ),
			'mcp_tool_id'           => sanitize_text_field( $args['mcp_tool_id'] ),
			'execution_arguments'   => isset( $args['execution_arguments'] ) ? maybe_serialize( $args['execution_arguments'] ) : '',
			'execution_result'      => isset( $args['execution_result'] ) ? maybe_serialize( $args['execution_result'] ) : '',
			'execution_created'     => current_time( 'mysql' ),
			'execution_metadata'    => isset( $args['execution_metadata'] ) ? maybe_serialize( $args['execution_metadata'] ) : '',
		);

		$result = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			$data,
			array(
				'%s', // mcp_tool_execution_id
				'%s', // mcp_server_id
				'%s', // mcp_tool_id
				'%s', // execution_arguments
				'%s', // execution_result
				'%s', // execution_created
				'%s', // execution_metadata
			)
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get execution by ID.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $execution_id The execution ID.
	 * @return object|null
	 */
	public function get( $execution_id ) {
		global $wpdb;

		if ( empty( $execution_id ) ) {
			return null;
		}

		$execution = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `mcp_tool_execution_id` = %s',
				$execution_id
			)
		);

		if ( $execution ) {
			$execution = $this->prepare_execution_output( $execution );
		}

		return $execution;
	}

	/**
	 * Get all executions with pagination.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id Optional server ID to filter by.
	 * @param string $tool_id Optional tool ID to filter by.
	 * @param int    $page The page number (1-based).
	 * @param int    $per_page Items per page.
	 * @return array
	 */
	public function get_all( $server_id = '', $tool_id = '', $page = 1, $per_page = 10 ) {
		global $wpdb;

		$where_conditions = array();
		$prepare_args     = array();

		if ( ! empty( $server_id ) ) {
			$where_conditions[] = '`mcp_server_id` = %s';
			$prepare_args[]     = $server_id;
		}

		if ( ! empty( $tool_id ) ) {
			$where_conditions[] = '`mcp_tool_id` = %s';
			$prepare_args[]     = $tool_id;
		}

		$where_clause = '';
		if ( ! empty( $where_conditions ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		// Calculate offset
		$offset = ( $page - 1 ) * $per_page;

		$prepare_args[] = $offset;
		$prepare_args[] = $per_page;

		$query = 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '`' . $where_clause . ' ORDER BY `execution_created` DESC LIMIT %d, %d';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, $prepare_args )
		);

		// Prepare output for each execution
		if ( $results ) {
			foreach ( $results as $key => $execution ) {
				$results[ $key ] = $this->prepare_execution_output( $execution );
			}
		}

		return $results ? $results : array();
	}

	/**
	 * Get total execution count.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id Optional server ID to filter by.
	 * @param string $tool_id Optional tool ID to filter by.
	 * @return int
	 */
	public function get_count( $server_id = '', $tool_id = '' ) {
		global $wpdb;

		$where_conditions = array();
		$prepare_args     = array();

		if ( ! empty( $server_id ) ) {
			$where_conditions[] = '`mcp_server_id` = %s';
			$prepare_args[]     = $server_id;
		}

		if ( ! empty( $tool_id ) ) {
			$where_conditions[] = '`mcp_tool_id` = %s';
			$prepare_args[]     = $tool_id;
		}

		$where_clause = '';
		if ( ! empty( $where_conditions ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		$query = 'SELECT COUNT(*) FROM `' . $wpdb->prefix . $this->table_name . '`' . $where_clause;

		if ( ! empty( $prepare_args ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $query, $prepare_args ) );
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Delete executions older than specified days.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param int $days Number of days to keep executions.
	 * @return int|false Number of deleted rows or false on error.
	 */
	public function cleanup_old_executions( $days = 30 ) {
		global $wpdb;

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		return $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `execution_created` < %s',
				$cutoff_date
			)
		);
	}

	/**
	 * Get execution statistics.
	 *
	 * @since 5.2.0
	 * @access public
	 * @param string $server_id Server ID to get stats for.
	 * @param string $period Period to get stats for (day, week, month).
	 * @return array
	 */
	public function get_execution_stats( $server_id, $period = 'day' ) {
		global $wpdb;

		$date_format = '';
		$interval    = '';

		switch ( $period ) {
			case 'week':
				$date_format = '%Y-%u';
				$interval    = '7 DAY';
				break;
			case 'month':
				$date_format = '%Y-%m';
				$interval    = '30 DAY';
				break;
			case 'day':
			default:
				$date_format = '%Y-%m-%d';
				$interval    = '1 DAY';
				break;
		}

		$query = $wpdb->prepare(
			"SELECT 
				DATE_FORMAT(execution_created, %s) as period,
				COUNT(*) as total_executions,
				COUNT(DISTINCT mcp_tool_id) as unique_tools
			FROM `{$wpdb->prefix}{$this->table_name}` 
			WHERE mcp_server_id = %s 
			AND execution_created >= DATE_SUB(NOW(), INTERVAL %s)
			GROUP BY period 
			ORDER BY period DESC",
			$date_format,
			$server_id,
			$interval
		);

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Prepare execution output by decoding serialized fields.
	 *
	 * @since 5.2.0
	 * @access private
	 * @param object $execution The execution object.
	 * @return object
	 */
	private function prepare_execution_output( $execution ) {
		// Decode serialized fields
		if ( ! empty( $execution->execution_arguments ) ) {
			$execution->execution_arguments = maybe_unserialize( $execution->execution_arguments );
		} else {
			$execution->execution_arguments = array();
		}

		if ( ! empty( $execution->execution_result ) ) {
			$execution->execution_result = maybe_unserialize( $execution->execution_result );
		} else {
			$execution->execution_result = array();
		}

		if ( ! empty( $execution->execution_metadata ) ) {
			$execution->execution_metadata = maybe_unserialize( $execution->execution_metadata );
		} else {
			$execution->execution_metadata = array();
		}

		return $execution;
	}
}