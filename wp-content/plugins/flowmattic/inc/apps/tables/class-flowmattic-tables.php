<?php
/**
 * Application Name: Tables
 * Description: Add Tables integration to FlowMattic.
 * Version: 5.0
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
 * Tables by FlowMattic.
 */
class FlowMattic_Table {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 5.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Tables Outgoing.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'tables',
			array(
				'name'         => esc_attr__( 'Tables by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/tables/icon.svg',
				'instructions' => 'Copy the Webhook URL and send your request to this url from your application or website.',
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'trigger, action',
			)
		);

		// Listen to the button click trigger.
		add_action( 'flowmattic_table_button_trigger', array( $this, 'trigger_button_clicked' ), 10, 2 );

		// Listen to the new record trigger.
		add_action( 'flowmattic_after_table_row_inserted', array( $this, 'trigger_new_record' ), 10, 2 );

		// Listen to the updated record trigger.
		add_action( 'flowmattic_after_table_row_updated', array( $this, 'trigger_updated_record' ), 10, 2 );

		// Listen to the deleted record trigger.
		add_action( 'flowmattic_after_table_row_deleted', array( $this, 'trigger_deleted_record' ), 10, 2 );

		// Listen to the updated cell trigger.
		add_action( 'flowmattic_after_table_cell_updated', array( $this, 'trigger_updated_cell' ), 10, 3 );

		// Listen to approve button click.
		add_action( 'flowmattic_after_table_row_approved', array( $this, 'execute_approved_workflow' ), 10, 5 );

		// Listen to reject button click.
		add_action( 'flowmattic_after_table_row_rejected', array( $this, 'execute_rejected_workflow' ), 10, 5 );
	}

	/**
	 * Enqueue custom view for Tables Outgoing.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function enqueue_views() {
		// Enqueue the custom view.
		wp_enqueue_script( 'flowmattic-tables', FLOWMATTIC_PLUGIN_URL . 'inc/apps/tables/view-tables.js', array( 'jquery' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 5.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'trigger_button_clicked' => array(
				'title'       => esc_attr__( 'Trigger Workflow Button Clicked', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when the trigger workflow button is clicked in the table.', 'flowmattic' ),
			),
			'new_record'             => array(
				'title'       => esc_attr__( 'New Record', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new row is added to a table.', 'flowmattic' ),
			),
			'updated_record'         => array(
				'title'       => esc_attr__( 'Updated Record', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a row is updated on a table.', 'flowmattic' ),
			),
			'deleted_record'         => array(
				'title'       => esc_attr__( 'Deleted Record', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a row is deleted from a table.', 'flowmattic' ),
			),
			'updated_cell'           => array(
				'title'       => esc_attr__( 'Updated Cell', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a specific cell is updated on a table.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Capture the button click trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @return void
	 */
	public function trigger_button_clicked( $table_name, $row_data ) {
		$trigger_data = array(
			'table_name' => $table_name,
		);

		// Add row data to the trigger in simplified format.
		foreach ( $row_data as $key => $value ) {
			$trigger_data[ $key ] = $value;
		}

		// Add row data as raw JSON.
		$trigger_data['raw_json'] = wp_json_encode( $row_data );

		$conditions = array(
			'condition'   => 'AND',
			'table_name' => array(
				'operator' => 'contains',
				'field'    => 'table_name',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', 'tables', 'trigger_button_clicked', $trigger_data, $conditions );

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_table_button_trigger', array( $this, 'trigger_button_clicked' ), 10, 2 );
	}

	/**
	 * Capture the new record trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @return void
	 */
	public function trigger_new_record( $table_name, $row_data ) {
		$trigger_data = array(
			'table_name' => $table_name,
		);

		// Add row data to the trigger in simplified format.
		foreach ( $row_data as $key => $value ) {
			$trigger_data[ $key ] = $value;
		}

		// Add row data as raw JSON.
		$trigger_data['raw_json'] = wp_json_encode( $row_data );

		$conditions = array(
			'condition'   => 'AND',
			'table_name' => array(
				'operator' => 'contains',
				'field'    => 'table_name',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', 'tables', 'new_record', $trigger_data, $conditions );

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_row_inserted', array( $this, 'trigger_new_record' ), 10, 2 );
	}

	/**
	 * Capture the updated record trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @return void
	 */
	public function trigger_updated_record( $table_name, $row_data ) {
		$trigger_data = array(
			'table_name' => $table_name,
		);

		// Add row data to the trigger in simplified format.
		foreach ( $row_data as $key => $value ) {
			$trigger_data[ $key ] = $value;
		}

		// Set request body.
		$this->request_body = $trigger_data;

		// Add row data as raw JSON.
		$trigger_data['raw_json'] = wp_json_encode( $row_data );

		$conditions = array(
			'condition'   => 'AND',
			'table_name' => array(
				'operator' => 'contains',
				'field'    => 'table_name',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', 'tables', 'updated_record', $trigger_data, $conditions );

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_row_updated', array( $this, 'trigger_updated_record' ), 10, 2 );
	}

	/**
	 * Capture the deleted record trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @return void
	 */
	public function trigger_deleted_record( $table_name, $row_data ) {
		$trigger_data = array(
			'table_name' => $table_name,
		);

		// Add row data to the trigger in simplified format.
		foreach ( $row_data as $key => $value ) {
			$trigger_data[ $key ] = $value;
		}

		// Add row data as raw JSON.
		$trigger_data['raw_json'] = wp_json_encode( $row_data );

		$conditions = array(
			'condition'   => 'AND',
			'table_name' => array(
				'operator' => 'contains',
				'field'    => 'table_name',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', 'tables', 'deleted_record', $trigger_data, $conditions );

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_row_deleted', array( $this, 'trigger_deleted_record' ), 10, 2 );
	}

	/**
	 * Capture the updated cell trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name      Table name.
	 * @param array  $updated_columns Updated columns.
	 * @param array  $updated_record  Updated record.
	 * @return void
	 */
	public function trigger_updated_cell( $table_name, $updated_columns, $updated_record ) {
		$trigger_data = array(
			'table_name' => $table_name,
		);

		// Add updated columns to the trigger in simplified format.
		foreach ( $updated_record as $key => $value ) {
			$trigger_data[ $key ] = $value;
		}

		// Add updated columns as raw JSON.
		$trigger_data['raw_json'] = wp_json_encode( $updated_record );

		// Add updated columns to the trigger.
		$updated_columns = array_keys( $updated_columns );
		$trigger_data['updated_columns'] = implode( ', ', $updated_columns );

		// If columns are empty, return.
		if ( empty( $trigger_data['updated_columns'] ) ) {
			return;
		}

		$conditions = array(
			'condition'   => 'AND',
			'column_name' => array(
				'operator' => 'contains',
				'field'    => 'updated_columns',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', 'tables', 'updated_cell', $trigger_data, $conditions );

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_cell_updated', array( $this, 'trigger_updated_cell' ), 10, 3 );
	}

	/**
	 * Capture the workflow continue trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @param string $approve_column Approve column.
	 * @param string $primary_value Primary value.
	 * @param array  $continue_data Continue data.
	 * @return void
	 */
	public function execute_approved_workflow( $table_name, $table_data, $approve_column, $primary_value, $continue_data ) {
		if ( ! empty( $continue_data ) ) {
			// Prepare the response.
			$response = array(
				'approve_button_clicked' => 'yes',
				'reject_button_clicked'  => 'no',
				'new_data'               => $table_data,
				'row_id'                 => $primary_value,
				'message'                => esc_attr__( 'Approve button clicked. Continuing the workflow.', 'flowmattic' ),
				'button_click_timestamp' => time(),
			);

			// Add response to the continue data.
			$continue_data['response'] = $response;

			// Get the workflow ID.
			$workflow_id = $continue_data['workflow_id'];

			// Continue the workflow.
			$flowmattic_workflow = new FlowMattic_Workflow();
			$flowmattic_workflow->run( $workflow_id, array(), array(), array(), false, $continue_data );
		}

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_row_approved', array( $this, 'execute_approved_workflow' ), 10, 5 );
	}

	/**
	 * Capture the workflow continue trigger.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $table_name Table name.
	 * @param array  $row_data   Row data.
	 * @param string $approve_column Approve column.
	 * @param string $primary_value Primary value.
	 * @param array  $continue_data Continue data.
	 * @return void
	 */
	public function execute_rejected_workflow( $table_name, $table_data, $approve_column, $primary_value, $continue_data ) {
		if ( ! empty( $continue_data ) ) {
			// Prepare the response.
			$response = array(
				'approve_button_clicked' => 'no',
				'reject_button_clicked'  => 'yes',
				'new_data'               => $table_data,
				'row_id'                 => $primary_value,
				'message'                => esc_attr__( 'Reject button clicked. Continuing the workflow.', 'flowmattic' ),
				'button_click_timestamp' => time(),
			);

			// Add response to the continue data.
			$continue_data['response'] = $response;

			// Get the workflow ID.
			$workflow_id = $continue_data['workflow_id'];

			// Continue the workflow.
			$flowmattic_workflow = new FlowMattic_Workflow();
			$flowmattic_workflow->run( $workflow_id, array(), array(), array(), false, $continue_data );
		}

		// Remove the action to prevent infinite loop.
		remove_action( 'flowmattic_after_table_row_rejected', array( $this, 'execute_rejected_workflow' ), 10, 5 );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 5.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'new_record'            => array(
				'title'       => esc_attr__( 'New Record', 'flowmattic' ),
				'description' => esc_attr__( 'Add a new record in the table.', 'flowmattic' ),
			),
			'update_record'         => array(
				'title'       => esc_attr__( 'Update Record', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing record on a table.', 'flowmattic' ),
			),
			'find_record'           => array(
				'title'       => esc_attr__( 'Find Record', 'flowmattic' ),
				'description' => esc_attr__( 'Finds a record based on a search value.', 'flowmattic' ),
			),
			'find_multiple_records' => array(
				'title'       => esc_attr__( 'Find Multiple Records', 'flowmattic' ),
				'description' => esc_attr__( 'Finds multiple records based on a search value. Returns an array of maximum 1000 records.', 'flowmattic' ),
			),
			'delete_record'         => array(
				'title'       => esc_attr__( 'Delete Record', 'flowmattic' ),
				'description' => esc_attr__( 'Delete an existing record on a table.', 'flowmattic' ),
			),
			'update_cell'           => array(
				'title'       => esc_attr__( 'Update Cell', 'flowmattic' ),
				'description' => esc_attr__( 'Update a specific column value in a row.', 'flowmattic' ),
			),
			'continue_workflow'     => array(
				'title'       => esc_attr__( 'Continue on Approve or Reject Button', 'flowmattic' ),
				'description' => esc_attr__( 'Continue the workflow when the Approve or Reject button is clicked.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step   = (array) $step;
		$action = $step['action'];
		$fields = $step['actionAppArgs'];

		switch ( $action ) {
			case 'new_record':
				$response = $this->new_record( $fields );
				break;

			case 'update_record':
				$response = $this->update_record( $fields );
				break;

			case 'find_record':
				$response = $this->find_record( $fields );
				break;

			case 'find_multiple_records':
				$response = $this->find_record( $fields, true );
				break;

			case 'delete_record':
				$response = $this->delete_record( $fields );
				break;

			case 'update_cell':
				$response = $this->update_cell( $fields );
				break;

			case 'continue_workflow':
				$response = $this->continue_workflow( $fields, $capture_data );
				break;

			default:
				$response = wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_attr__( 'Action not found.', 'flowmattic' ),
					)
				);
				break;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create new record.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function new_record( $fields ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get the column names.
		$column_names = $this->get_column_names( $database_id, $table_name );

		// Prepare the data.
		$data = array();

		// Loop through the column names, and get the data from the fields.
		foreach ( $column_names as $column_name ) {
			$data[ $column_name ] = isset( $fields[ $column_name ] ) ? stripslashes( $fields[ $column_name ] ) : '';
		}

		// Set request body.
		$this->request_body = $data;

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Insert the data.
		$fm_db_connector->get_db()->insert( $table_name, $data );

		// Get the inserted ID.
		$inserted_id = $fm_db_connector->get_db()->insert_id;

		// Get the last error.
		$last_error = $fm_db_connector->get_db()->last_error;

		$inserted_record       = $data;
		$inserted_record['id'] = $inserted_id;

		// Fire an action after the table data is inserted.
		do_action( 'flowmattic_after_table_row_inserted', $table_name, $inserted_record );

		// Send the response.
		$response = array(
			'status'  => 'success',
			'message' => esc_attr__( 'Record added successfully.', 'flowmattic' ),
			'row_id'  => $inserted_id,
		);

		if ( ! empty( $last_error ) ) {
			$response['status']  = 'error';
			$response['message'] = $last_error;
		}

		return $response;
	}

	/**
	 * Update record.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function update_record( $fields ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get the record ID.
		$record_id = isset( $fields['row_id'] ) ? $fields['row_id'] : '';

		// Return error if any of the required fields are missing.
		if ( empty( $database_id ) || empty( $table_name ) || empty( $record_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'Database ID, Table Name, or Record ID is missing.', 'flowmattic' ),
			);
		}

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the database instance
		$db = $fm_db_connector->get_db();

		// Get the table columns.
		$table_columns = $this->get_columns_from_cache( $database_id, $table_name );

		$primary_key   = '';
		$primary_value = $record_id;

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Get the old record.
		$old_record = $db->get_row( $db->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $primary_key . '` = %d', $primary_value ), ARRAY_A );

		// Get the column names.
		$column_names = $this->get_column_names( $database_id, $table_name );

		// Prepare the data.
		$data = array();

		// Loop through the column names, and get the data from the fields.
		foreach ( $column_names as $column_name ) {
			if ( isset( $fields[ $column_name ] ) && $fields[ $column_name ] !== $old_record[ $column_name ] && '' !== $fields[ $column_name ] ) {
				$data[ $column_name ] = stripslashes( $fields[ $column_name ] );
			}
		}

		// Set request body.
		$this->request_body = $data;

		// If no data is changed, return error.
		if ( empty( $data ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'No data is changed. No update is required.', 'flowmattic' ),
			);
		}

		// Use wpdb's update method for proper escaping and charset handling
		$where = array( $primary_key => $primary_value );
		$result = $db->update( $table_name, $data, $where );

		// Get the updated record.
		$updated_record       = $data;
		$updated_record['id'] = $record_id;

		// Get table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );
		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Format the record data.
		$updated_record = $this->format_record_data( $updated_record, $table_schema );
		$old_record     = $this->format_record_data( $old_record, $table_schema );

		// Fire an action after the table data is updated.
		do_action( 'flowmattic_after_table_row_updated', $table_name, $updated_record );

		// Check which columns have been updated.
		if ( is_array( $old_record ) ) {
			$updated_columns = array_diff_assoc( $updated_record, $old_record );
		} else {
			$updated_columns = $updated_record;
		}

		// If no columns are updated, return.
		if ( ! empty( $updated_columns ) ) {
			// Fire an action for the cell update.
			do_action( 'flowmattic_after_table_cell_updated', $table_name, $updated_columns, $updated_record );
		}

		// Send the response.
		$response = array(
			'status'  => 'success',
			'message' => esc_attr__( 'Record updated successfully.', 'flowmattic' ),
		);

		$response = array_merge( $response, $updated_record );

		return $response;
	}

	/**
	 * Find record.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields   Fields data.
	 * @param bool  $multiple Multiple records.
	 * @return array
	 */
	public function find_record( $fields, $multiple = false ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get column name.
		$column_name = isset( $fields['column_name'] ) ? $fields['column_name'] : '';

		// Get lookup value.
		$lookup_value = isset( $fields['lookup_value'] ) ? $fields['lookup_value'] : '';

		// Get lookup method.
		$lookup_method = isset( $fields['lookup_method'] ) ? $fields['lookup_method'] : 'exact';

		// Get optional fields.
		$column_name_optional  = isset( $fields['column_name_optional'] ) ? $fields['column_name_optional'] : '';
		$lookup_value_optional = isset( $fields['lookup_value_optional'] ) ? $fields['lookup_value_optional'] : '';

		// Return error if any of the required fields are missing.
		if ( empty( $database_id ) || empty( $table_name ) || empty( $column_name ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'All fields are required. Please fill all the fields.', 'flowmattic' ),
			);
		}

		if( empty( $lookup_value ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'Lookup value is empty. Cannot update the cell.', 'flowmattic' ),
			);
		}

		// Set request body.
		$this->request_body = array(
			'database_id'  => $database_id,
			'table_name'   => $table_name,
			'column_name'  => $column_name,
			'lookup_value' => $lookup_value,
		);

		if ( ! empty( $column_name_optional ) && ! empty( $lookup_value_optional ) ) {
			$this->request_body['column_name_optional']  = $column_name_optional;
			$this->request_body['lookup_value_optional'] = $lookup_value_optional;
		}

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the database instance
		$db = $fm_db_connector->get_db();

		// Prepare the query with proper escaping
		if ( 'exact' === $lookup_method ) {
			$query = $db->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $column_name . '` = %s', $lookup_value );
		} else {
			$query = $db->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $column_name . '` LIKE %s', '%' . $db->esc_like( $lookup_value ) . '%' );
		}

		if ( ! empty( $column_name_optional ) && ! empty( $lookup_value_optional ) ) {
			if ( 'exact' === $lookup_method ) {
				$query .= $db->prepare( ' AND `' . $column_name_optional . '` = %s', $lookup_value_optional );
			} else {
				$query .= $db->prepare( ' AND `' . $column_name_optional . '` LIKE %s', '%' . $db->esc_like( $lookup_value_optional ) . '%' );
			}
		}

		// Get the record.
		if ( $multiple ) {
			$record = $db->get_results( $query . ' LIMIT 1000', ARRAY_A );
		} else {
			$record = $db->get_row( $query, ARRAY_A );
		}

		if ( ! $record ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'No record found.', 'flowmattic' ),
			);
		}

		// Send the response.
		$response = array(
			'status'  => 'success',
			'message' => $multiple ? esc_attr__( 'Records found successfully.', 'flowmattic' ) : esc_attr__( 'Record found successfully.', 'flowmattic' ),
		);

		// Get the table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		if ( $multiple ) {
			$records = array();

			foreach ( $record as $row ) {
				$record_data = $this->format_record_data( $row, $table_schema );
				$records[]   = $record_data;
			}

			$response['records'] = wp_json_encode( $records );
			$response['count']   = count( $records );
		} else {
			$record   = $this->format_record_data( $record, $table_schema );
			$response = array_merge( $response, $record );
		}

		return $response;
	}

	/**
	 * Delete record.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function delete_record( $fields ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get the record ID.
		$record_id = isset( $fields['row_id'] ) ? $fields['row_id'] : '';

		// Return error if any of the required fields are missing.
		if ( empty( $database_id ) || empty( $table_name ) || empty( $record_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'Database ID, Table Name, or Record ID is missing.', 'flowmattic' ),
			);
		}

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the table columns.
		$table_columns = $this->get_columns_from_cache( $database_id, $table_name );

		$primary_key   = '';
		$primary_value = $record_id;

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Get the record.
		$record = $fm_db_connector->get_db()->get_row( $fm_db_connector->get_db()->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $primary_key . '` = %d', $primary_value ), ARRAY_A );

		// Set request body.
		$this->request_body = array(
			'database_id' => $database_id,
			'table_name'  => $table_name,
			'row_id'      => $record_id,
		);

		// Delete the record.
		$fm_db_connector->get_db()->delete( $table_name, array( $primary_key => $record_id ) );

		// Fire an action after the table data is deleted.
		do_action( 'flowmattic_after_table_row_deleted', $table_name, $record );

		// Send the response.
		$response = array(
			'status'  => 'success',
			'message' => esc_attr__( 'Record deleted successfully.', 'flowmattic' ),
		);

		return $response;
	}

	/**
	 * Update Cell.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function update_cell( $fields ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get the record ID.
		$record_id = isset( $fields['row_id'] ) ? $fields['row_id'] : '';

		// Get the column name.
		$column_name = isset( $fields['column_name'] ) ? $fields['column_name'] : '';

		// Get the column value.
		$column_value = isset( $fields['column_value'] ) ? $fields['column_value'] : '';

		// Return error if any of the required fields are missing.
		if ( empty( $database_id ) || empty( $table_name ) || empty( $record_id ) || empty( $column_name ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'All fields are required. Please fill all the fields.', 'flowmattic' ),
			);
		}

		if( empty( $column_value ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_attr__( 'Column value is empty. Cannot update the cell.', 'flowmattic' ),
			);
		}

		// Set request body.
		$this->request_body = array(
			'database_id'  => $database_id,
			'table_name'   => $table_name,
			'row_id'       => $record_id,
			'column_name'  => $column_name,
			'column_value' => $column_value,
		);

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the table columns.
		$table_columns = $this->get_columns_from_cache( $database_id, $table_name );

		$primary_key   = '';
		$primary_value = $record_id;

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Update the data.
		$fm_db_connector->get_db()->update( $table_name, array( $column_name => $column_value ), array( $primary_key => $record_id ) );

		// Get the updated record.
		$updated_record = $fm_db_connector->get_db()->get_row( $fm_db_connector->get_db()->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $primary_key . '` = %d', $primary_value ), ARRAY_A );

		// Get the table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );
		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		$updated_record = $this->format_record_data( $updated_record, $table_schema );

		// Fire an action after the table cell is updated.
		do_action( 'flowmattic_after_table_cell_updated', $table_name, array( $column_name => $column_value ), $updated_record );

		// Send the response.
		$response = array(
			'status'  => 'success',
			'message' => esc_attr__( 'Cell updated successfully.', 'flowmattic' ),
			'row_id'  => $record_id,
			'column'  => $column_name,
			'value'   => $column_value,
		);

		return $response;
	}

	/**
	 * Continue workflow.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields        Fields data.
	 * @param array $continue_data Data to continue.
	 * @return array
	 */
	public function continue_workflow( $fields, $continue_data ) {
		// Get database ID.
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Get table name.
		$table_name = isset( $fields['table_name'] ) ? $fields['table_name'] : '';

		// Get the record ID.
		$record_id = isset( $fields['row_id'] ) ? $fields['row_id'] : '';

		// Get the table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Get the column names from the schema, which has the button type.
		$button_columns = array_filter(
			$table_schema,
			function( $column ) {
				return 'button' === $column['type'] && 'continue' === $column['buttonOptions'][0]['type'];
			}
		);

		if ( empty( $button_columns ) ) {
			return array(
				'status'     => 'error',
				'message'    => esc_attr__( 'Selected table does not contain the approve button column', 'flowmattic' ),
				'table_name' => $table_name,
			);
		}

		$button_column  = array_keys( $button_columns )[0];
		$button         = $button_columns[ $button_column ];
		$button_options = $button['buttonOptions'];

		// Add the continue data to the button options/
		if ( ! empty( $continue_data ) ) {
			$button_options[0]['continue_data'] = $continue_data;
		}

		// Set the default and secondary state to enabled.
		$button_options[0]['defaultState']   = 'enabled';
		$button_options[0]['secondaryState'] = 'enabled';

		// Add the approved state.
		$button_options[0]['rowApproved'] = true;
		$button_options[0]['rowRejected'] = true;

		// Encode the button options.
		$button_options = json_encode( $button_options );

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the table columns.
		$table_columns = $this->get_columns_from_cache( $database_id, $table_name );

		$primary_key   = '';
		$primary_value = $record_id;

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Get previous data for the row.
		$previous_data = $fm_db_connector->get_db()->get_row( $fm_db_connector->get_db()->prepare( 'SELECT * FROM `' . $table_name . '` WHERE `' . $primary_key . '` = %d', $primary_value ), ARRAY_A );

		$previous_column_data = isset( $previous_data[ $button_column ] ) ? json_decode( $previous_data[ $button_column ], true ) : array();

		// Update the approve column.
		$update_approve_column = $fm_db_connector->get_db()->update( $table_name, array( $button_column => $button_options ), array( $primary_key => $primary_value ) );

		$row_approved = isset( $previous_column_data[0]['approvedButtonClicked'] ) && $previous_column_data[0]['approvedButtonClicked'] ? 'yes' : 'no';
		$row_rejected = isset( $previous_column_data[0]['rejectedButtonClicked'] ) && $previous_column_data[0]['rejectedButtonClicked'] ? 'yes' : 'no';

		// Remove the button data from the previous data.
		unset( $previous_data[ $button_column ] );

		// Prepare the response.
		$response = array(
			'approve_button_clicked' => $row_approved,
			'reject_button_clicked'  => $row_rejected,
			'new_data'               => $previous_data,
			'old_data'               => $previous_data,
			'row_id'                 => $record_id,
			'message'                => esc_attr__( 'Workflow will continue execution after button is clicked. The data in this response will be updated accordingly' ),
		);

		return $response;
	}

	/**
	 * Get column names.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $database_id Database ID.
	 * @param string $table_name  Table name.
	 * @return array
	 */
	public function get_column_names( $database_id, $table_name ) {
		// Get the table columns.
		$table_columns = $this->get_columns_from_cache( $database_id, $table_name );

		// Send the response.
		$all_columns = array();
		if ( $table_columns ) {
			foreach ( $table_columns as $key => $column ) {
				$all_columns[ $column->Field ] = $column->Field;
			}
		}

		return $all_columns;
	}

	/**
	 * Get columns from cache.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $database_id Database ID.
	 * @param string $table_name  Table name.
	 * @return array
	 */
	public function get_columns_from_cache( $database_id, $table_name ) {
		$query_key     = 'flowmattic_table_columns_' . $database_id . '_' . $table_name;
		$query_id      = md5( $query_key );
		$table_columns = wp_cache_get( $query_id, 'flowmattic_table_columns' );

		// Return the cached response.
		if ( false !== $table_columns ) {
			return $table_columns;
		}

		// Get the database connection.
		$fm_db_connector = wp_flowmattic()->tables->get_db_connection( $database_id );

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		// Cache the response.
		wp_cache_set( $query_id, $table_columns, 'flowmattic_table_columns' );

		return $table_columns;
	}

	/**
	 * Format the record data.
	 *
	 * @access public
	 * @since 5.0.10
	 * @param array $record Record data.
	 * @param array $table_schema Table schema.
	 * @return array
	 */
	public function format_record_data( $record, $table_schema ) {
		// Loop through the records and format the data.
		foreach ( $record as $key => $value ) {
			if ( 'id' === $key ) {
				continue;
			}

			$record_type = $table_schema[ $key ]['type'];

			switch ( $record_type ) {
				case 'date':
				case 'datetime':
					$date_format = $table_schema[ $key ]['dateFormat'];

					// Get the PHP date format.
					$php_date_format = $this->get_date_format( $date_format );

					// Remove the part in parentheses (timezone name)
					$date_string = preg_replace('/\s\([^)]+\)$/', '', $value);

					// Replace - with / to make it compatible with DateTime
					$date_string = str_replace( '-', '/', $date_string );

					// Convert the date string to a DateTime object
					$date = new DateTime( $date_string );

					// Format the date
					$currentdate = $date->format( $php_date_format );

					$record[ $key ] = $currentdate;
					break;

				case 'link':
					$link_json = json_decode( $value, true );

					$link_url  = isset( $link_json['href'] ) ? $link_json['href'] : $value;
					$link_text = isset( $link_json['text'] ) ? $link_json['text'] : $value;
					$link_html = '<a href="' . $link_url . '" target="_blank">' . $link_text . '</a>';

					// Unset the original value to re-index the array.
					unset( $record[ $key ] );

					$record[ $key ] = $link_html;
					$record[ $key . '__url' ] = $link_url;
					$record[ $key . '__text' ] = $link_text;
					$record[ $key . '__object' ] = wp_json_encode( $link_json );
					break;

				case 'button':
					$button_json = json_decode( stripslashes( $value ), true );

					// Unset the original value to re-index the array.
					unset( $record[ $key ] );

					// If the button is approval button, set the value as approved or rejected.
					if ( isset( $button_json[0]['type'] ) && 'continue' === $button_json[0]['type'] ) {	
						if ( isset( $button_json[0]['rowApproved'] ) && $button_json[0]['rowApproved'] ) {
							$record[ $key ] = 'Approved';
						} elseif ( isset( $button_json[0]['rowRejected'] ) && $button_json[0]['rowRejected'] ) {
							$record[ $key ] = 'Rejected';
						}
						
						// If both the buttons are true, set the value as pending.
						if ( ( isset( $button_json[0]['rowApproved'] ) && $button_json[0]['rowApproved'] ) && ( isset( $button_json[0]['rowRejected'] ) && $button_json[0]['rowRejected'] ) ) {
							$record[ $key ] = 'Pending';
						}
					} else {
						$record[ $key ] = $button_json;
					}

					$record[ $key . '__object' ] = wp_json_encode( $button_json );

					break;

				default:
					$record[ $key ] = $value;
					break;
			}
		}

		return $record;
	}

	/**
	 * Get the correct date format.
	 *
	 * @access public
	 * @since 5.0.10
	 * @param string $date_format Date format.
	 * @return string
	 */
	public function get_date_format( $date_format ) {
		// Array of date format options
		$formats = array(
			"M/dd/y" => "m/d/y",
			"dd/M/y" => "d/m/y",
			"y/M/dd" => "y/m/d",
			"y/dd/M" => "y/d/m",
			"M-dd-y" => "m-d-y",
			"dd-M-y" => "d-m-y",
			"y-M-dd" => "y-m-d",
			"y-dd-M" => "y-d-m",
			"dd-MMMM-y" => "d-F-y",
			"MMMM-dd-y" => "F-d-y",
			"dd/MMMM/y" => "d/F/y",
			"MMMM/dd/y" => "F/d/y",
			"dd MMMM, y" => "d F, y",
			"MMMM dd, y" => "F d, y",
			"MMMM-y" => "F-y",
			"y-MMMM" => "y-F",
			"M-y" => "m-y",
			"y-M" => "y-m",
			"MM/dd/y hh:mm a" => "m/d/y h:i a",
			"MM/dd/y hh:mm:ss" => "m/d/y h:i:s",
			"MMMM/dd/y hh:mm a" => "F/d/y h:i a",
			"MMMM/dd/y hh:mm:ss" => "F/d/y h:i:s",
			"dd/MM/y hh:mm a" => "d/m/y h:i a",
			"dd/MM/y hh:mm:ss" => "d/m/y h:i:s",
			"dd/MMMM/y hh:mm:ss" => "d/F/y h:i:s",
			"dd/MMMM/y hh:mm a" => "d/F/y h:i a",
			"y/MM/dd hh:mm a" => "y/m/d h:i a",
			"y/MM/dd hh:mm:ss" => "y/m/d h:i:s",
			"y/MMMM/dd hh:mm a" => "y/F/d h:i a",
			"y/MMMM/dd hh:mm:ss" => "y/F/d h:i:s",
			"y/dd/MM hh:mm a" => "y/d/m h:i a",
			"y/dd/MM hh:mm:ss" => "y/d/m h:i:s",
			"y/dd/MMMM hh:mm a" => "y/d/F h:i a",
			"y/dd/MMMM hh:mm:ss" => "y/d/F h:i:s",
			"MM-dd-y hh:mm a" => "m-d-y h:i a",
			"MM-dd-y hh:mm:ss" => "m-d-y h:i:s",
			"MMMM-dd-y hh:mm a" => "F-d-y h:i a",
			"MMMM-dd-y hh:mm:ss" => "F-d-y h:i:s",
			"dd-MM-y hh:mm a" => "d-m-y h:i a",
			"dd-MM-y hh:mm:ss" => "d-m-y h:i:s",
			"dd-MMMM-y hh:mm a" => "d-F-y h:i a",
			"dd-MMMM-y hh:mm:ss" => "d-F-y h:i:s",
			"y-MM-dd hh:mm a" => "y-m-d h:i a",
			"y-MM-dd hh:mm:ss" => "y-m-d h:i:s",
			"y-MMMM-dd hh:mm a" => "y-F-d h:i a",
			"y-MMMM-dd hh:mm:ss" => "y-F-d h:i:s",
			"y-dd-MM hh:mm a" => "y-d-m h:i a",
			"y-dd-MM hh:mm:ss" => "y-d-m h:i:s",
			"y-dd-MMMM hh:mm a" => "y-d-F h:i a",
			"y-dd-MMMM hh:mm:ss" => "y-d-F h:i:s",
			"MMMM dd, y hh:mm a" => "F d, y h:i a",
			"MMMM dd, y hh:mm:ss" => "F d, y h:i:s",
			"dd MMMM, y hh:mm a" => "d F, y h:i a",
			"dd MMMM, y hh:mm:ss" => "d F, y h:i:s",
		);

		// Get the date format.
		$new_date_format = $formats[ $date_format ];

		// Replace the y with Y.
		$new_date_format = str_replace( 'y', 'Y', $new_date_format );

		// Return the correct date format.
		return $new_date_format;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event       = $event_data['event'];
		$settings    = $event_data['settings'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 5.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}

}

new FlowMattic_Table();
