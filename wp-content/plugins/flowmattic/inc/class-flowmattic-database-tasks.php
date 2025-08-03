<?php
/**
 * Handle database queries for tasks.
 *
 * @package flowmattic
 * @since 1.0
 */

/**
 * Handle database queries for tasks.
 *
 * @since 1.0
 */
class FlowMattic_Database_Tasks {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 1.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_tasks';

	/**
	 * The Constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert task to database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Filter to check if the workflow is allowed for task history.
		$task_history_enabled = apply_filters( 'flowmattic_workflow_task_history_enabled', true, $args['workflow_id'] );

		// Check if task history is enabled for the workflow.
		if ( ! $task_history_enabled ) {
			return false;
		}

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'task_id'     => esc_attr( $args['task_id'] ),
				'workflow_id' => esc_attr( $args['workflow_id'] ),
				'task_data'   => wp_json_encode( $args['task_data'] ),
				'task_time'   => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update the task in database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return array
	 */
	public function update( $args ) {
		global $wpdb;

		// Filter to check if the workflow is allowed for task history.
		$task_history_enabled = isset( $args['workflow_id'] ) ? apply_filters( 'flowmattic_workflow_task_history_enabled', true, $args['workflow_id'] ) : true;

		// Check if task history is enabled for the workflow.
		if ( ! $task_history_enabled ) {
			return false;
		}

		// Check if task ID is provided, else skip.
		if ( isset( $args['task_id'] ) ) {
			return $wpdb->update(
				$wpdb->prefix . $this->table_name,
				array(
					'task_data' => wp_json_encode( $args['task_data'] ),
				),
				array(
					'task_id' => esc_attr( $args['task_id'] ),
				)
			);
		}
	}

	/**
	 * Delete the task from database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function delete( $args ) {
		global $wpdb;

		// Check if trigger application is provided, else skip.
		if ( isset( $args['workflow_id'] ) ) {
			$wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'workflow_id' => esc_attr( $args['workflow_id'] ),
				)
			);
		}
	}

	/**
	 * Delete the task from database by task ID.
	 *
	 * @since 4.3.0
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function delete_by_task_id( $args ) {
		global $wpdb;

		// Check if trigger application is provided, else skip.
		if ( isset( $args['task_id'] ) ) {
			$wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'task_id' => esc_attr( $args['task_id'] ),
				)
			);
		}
	}

	/**
	 * Delete the task from database before the interval.
	 *
	 * @since 1.0
	 * @access public
	 * @param string $interval The interval in days.
	 * @return void
	 */
	public function delete_before( $interval ) {
		global $wpdb;

		// Check if interval is provided, else skip.
		if ( '' !== $interval ) {
			$wpdb->get_results(
				$wpdb->prepare( 'DELETE FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `task_time` < DATE_ADD( NOW(), INTERVAL -%d HOUR )', ( $interval * 24 ) )
			);
		}
	}

	/**
	 * Get the task from database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function get( $args ) {
	}

	/**
	 * Get the tasks for the given workflow from database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $workflow_id The application name.
	 * @return array Workflow data from database.
	 */
	public function get_tasks_by_workflow( $workflow_id ) {
		global $wpdb;

		// Check if workflow ID is provided, else skip.
		if ( '' !== $workflow_id ) {
			$tasks = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `workflow_id` LIKE %s', '%' . $workflow_id . '%' )
			);
		}

		return $tasks;
	}

	/**
	 * Get single task for the given task id from database.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $task_id The application name.
	 * @return array Workflow data from database.
	 */
	public function get_task_by_id( $task_id ) {
		global $wpdb;

		// Check if task ID is provided, else skip.
		if ( '' !== $task_id ) {
			$task = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `task_id` LIKE %s', '%' . $task_id . '%' )
			);
		}

		return $task;
	}

	/**
	 * Get all tasks from database.
	 *
	 * @since 1.0
	 * @access public
	 * @param int $offset The pagination offset.
	 * @return object
	 */
	public function get_all( $offset = 0, $per_page = 10 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` ORDER BY `ID` DESC LIMIT %d, %d', $offset, $per_page )
		);

		return $results;
	}

	/**
	 * Get the total tasks count.
	 *
	 * @since 1.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_tasks_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = "SELECT COUNT(1) FROM (" . $query . ") AS combined_table";
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}

	/**
	 * Generate and return the search term query.
	 *
	 * @since 3.0
	 * @access public
	 * @param array $search_term The search term.
	 * @return array Tasks.
	 */
	public function get_search_query( $search_term ) {
		global $wpdb;

		$search_query   = '';
		$workflow_table = $wpdb->prefix . 'flowmattic_workflows';
		$this_table     = $wpdb->prefix . $this->table_name;

		// Generate the query.
		$search_query  = ' JOIN `' . $workflow_table . '` ON `' . $this_table . '`.`workflow_id` = `' . $workflow_table . '`.`workflow_id`';
		$search_query .= ' WHERE (`' . $this_table . '`.`task_data` LIKE "%%' . $search_term . '%%"';
		$search_query .= ' OR `' . $workflow_table . '`.`workflow_steps` LIKE "%%' . $search_term . '%%"';
		$search_query .= ' OR `' . $workflow_table . '`.`workflow_name` LIKE "%%' . $search_term . '%%" )';

		return $search_query;
	}

	/**
	 * Generate and return the search term query.
	 *
	 * @since 3.0
	 * @access public
	 * @param array $args         The search query arguments.
	 * @param array $offset       Query offset.
	 * @param array $return_query Whether to retun the query only.
	 * @return array Tasks.
	 */
	public function get_tasks_by_search( $args, $offset = 0, $return_query = false ) {
		global $wpdb;

		$per_page	    = isset( $args['per_page'] ) ? $args['per_page'] : 10;
		$search_results = array();
		$this_table     = $wpdb->prefix . $this->table_name;

		$status      = isset( $args['status'] ) ? $args['status'] : '';
		$from_date   = isset( $args['from_date'] ) ? Date( 'Y-m-d', strtotime( $args['from_date'] ) ) : '';
		$to_date     = isset( $args['to_date'] ) ? Date( 'Y-m-d', strtotime( $args['to_date'] ) ) : '';
		$workflow_id = isset( $args['workflow_id'] ) ? $args['workflow_id'] : '';
		$search_term = isset( $args['search_term'] ) ? $args['search_term'] : '';
		$where_query = '';

		// Generate where query for search term.
		if ( '' !== $search_term ) {
			$where_query .= $this->get_search_query( $search_term );
		}

		// Generate where query for workflow id.
		if ( '' !== $workflow_id ) {
			$where_query .= ( '' !== $where_query ) ? ' AND ' : '';
			$where_query .= '`' . $this_table . '`.`workflow_id` LIKE "%%' . $workflow_id . '%%"';
		}

		// Generate where query for date query.
		if ( '' !== $from_date && '' !== $to_date ) {
			if ( $from_date === $to_date ) {
				$where_query .= ' AND date(`' . $this_table . '`.`task_time`) = ' . "'" . $from_date . "'";
			} else {
				$where_query .= ' AND date(`' . $this_table . '`.`task_time`) >= ' . "'" . $from_date . "'" . ' AND date(`' . $this_table . '`.`task_time`) <= ' . "'" . $to_date . "'";
			}
		}

		$query = 'SELECT * ';

		// Check if task status is provided, else skip.
		if ( '' !== $status ) {
			$case_query = '';
			switch ( $status ) {
				case 'success':
					$case_query .= 'WHEN task_data NOT LIKE \'%"status":"Error"%\' OR task_data NOT LIKE \'%"status":"error"%\'
						THEN \'success\'';
					break;

				case 'failed':
					$case_query .= 'WHEN task_data LIKE \'%"status":"Error"%\' OR task_data LIKE \'%"status":"error"%\'
						THEN \'failed\'';
					break;

			}

			$query .= ', CASE '
				. $case_query .
				'
				 END as status';
		}

		$query .= ' FROM `' . $this_table . '`';
		if ( '' !== $where_query ) {
			$query .= ( false === strpos( $where_query, 'WHERE' ) ) ? ' WHERE ' . $where_query : ' ' . $where_query;
		}

		if ( '' !== $status ) {
			$query .= ' HAVING status="' . $status . '"';
		}

		if ( $return_query ) {
			return $query;
		}

		$query .= ' ORDER BY `' . $this_table . '`.`task_time` DESC LIMIT %d, %d';

		$search_results = $wpdb->get_results(
			$wpdb->prepare( $query, $offset, $per_page )
		);

		return $search_results;
	}

	/**
	 * Calculate the count of search query without offset for pagination.
	 *
	 * @since 3.0
	 * @access public
	 * @param array $args The search query arguments.
	 * @return array Tasks.
	 */
	public function get_search_results_without_offset( $args ) {
		global $wpdb;

		$results = array();
		$query   = $this->get_tasks_by_search( $args, 0, true );

		$query = str_replace( '*', '`' . $wpdb->prefix . $this->table_name . '`.`id`', $query );

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$results     = $wpdb->get_var( $total_query );

		return (int) $results;
	}
}
