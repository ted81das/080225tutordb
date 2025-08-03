<?php
/**
 * Custom Tables in FlowMattic.
 *
 * @package flowmattic
 * @since 5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main FlowMattic_Tables Class.
 *
 * @since 5.0
 * @access public
 */
class FlowMattic_Tables {
	/**
	 * The Constructor.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function __construct() {
		// Register REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register admin ajax to test database connection.
		add_action( 'wp_ajax_flowmattic_test_database_connection', array( $this, 'test_db_connection' ) );

		// Register admin ajax to save database connection.
		add_action( 'wp_ajax_flowmattic_add_new_database', array( $this, 'add_new_database' ) );

		// Register admin ajax to update database connection.
		add_action( 'wp_ajax_flowmattic_update_database', array( $this, 'update_database' ) );

		// Register admin ajax to get database connection.
		add_action( 'wp_ajax_flowmattic_get_database_connection', array( $this, 'get_db_connection_details' ) );

		// Register admin ajax to create new table.
		add_action( 'wp_ajax_flowmattic_create_table', array( $this, 'create_table' ) );

		// Register admin ajax to get list of tables for a database.
		add_action( 'wp_ajax_flowmattic_get_tables', array( $this, 'get_tables' ), 1 );

		// Register admin ajax to delete table.
		add_action( 'wp_ajax_flowmattic_delete_table', array( $this, 'delete_table' ) );

		// Register admin ajax to delete database connection.
		add_action( 'wp_ajax_flowmattic_delete_database', array( $this, 'delete_database' ) );

		// Register admin ajax to get table data.
		add_action( 'wp_ajax_flowmattic_get_table_data', array( $this, 'get_table_data' ) );
		add_action( 'wp_ajax_nopriv_flowmattic_get_table_data', array( $this, 'get_table_data' ) );

		// Register admin ajax to insert table data.
		add_action( 'wp_ajax_flowmattic_add_table_data', array( $this, 'add_table_data' ) );

		// Register admin ajax to update table data.
		add_action( 'wp_ajax_flowmattic_update_table_data', array( $this, 'update_table_data' ) );

		// Register admin ajax to delete table data.
		add_action( 'wp_ajax_flowmattic_delete_table_data', array( $this, 'delete_table_data' ) );

		// Register admin ajax to add new column.
		add_action( 'wp_ajax_flowmattic_add_new_column', array( $this, 'add_new_column' ) );

		// Register admin ajax to get table columns.
		add_action( 'wp_ajax_flowmattic_get_columns', array( $this, 'get_table_columns' ) );

		// Register admin ajax to update column.
		add_action( 'wp_ajax_flowmattic_update_column', array( $this, 'update_column' ) );

		// Register admin ajax to delete column.
		add_action( 'wp_ajax_flowmattic_delete_column', array( $this, 'delete_column' ) );

		// Register admin ajax to listen to the table button trigger action.
		add_action( 'wp_ajax_flowmattic_table_button_trigger', array( $this, 'table_button_trigger' ) );

		// Register admin ajax to approve row data.
		add_action( 'wp_ajax_flowmattic_table_button_approve', array( $this, 'approve_row' ) );

		// Register admin ajax to reject row data.
		add_action( 'wp_ajax_flowmattic_table_button_reject', array( $this, 'reject_row' ) );

		// Add shortcode to display the table on the frontend.
		add_shortcode( 'flowmattic_table', array( $this, 'display_table_shortcode' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function register_rest_routes() {
		// Register the REST API route to display table.
		register_rest_route(
			'flowmattic/v1',
			'/embed/table/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'tables_embed' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Embed tables.
	 *
	 * @access public
	 * @since 5.0
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function tables_embed( WP_REST_Request $request ) {
		// Get the tables ID.
		$table_id = sanitize_text_field( esc_attr( base64_decode( $request->get_param( 'table_id' ) ) ) );

		// Set the header as text/html.
		header( 'Content-Type: text/html' );

		ob_start();
		// Include the tables embed file.
		include_once FLOWMATTIC_PLUGIN_DIR . 'inc/embed/tables.php';
	}

	/**
	 * Check if database connection is active
	 *
	 * @since 5.0
	 * @param int $database_id The database ID.
	 * @access public
	 */
	public function is_database_connection_active( $database_id ) {
		// Get the database connection db instance.
		$database_connections_db = wp_flowmattic()->database_connections_db;

		// Get the database connection.
		$database_connections = $database_connections_db->get( array( 'database_id' => $database_id ) );

		// Get the database connection data.
		$database_connection_data = maybe_unserialize( $database_connections->connection_data );

		// Test the database connection.
		$fm_db_connector = new FlowMattic_DB_Connector( $database_connection_data );
		$test_connection = $fm_db_connector->test_connection();

		return $test_connection;
	}

	/**
	 * Test database connection.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function test_db_connection() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_test_db_connection', 'security' );

		// Get the database credentials.
		$db_credentials = array(
			'host'     => sanitize_text_field( $_POST['database_host'] ),
			'name'     => sanitize_text_field( $_POST['database_name'] ),
			'user'     => sanitize_text_field( $_POST['database_user'] ),
			'password' => sanitize_text_field( $_POST['database_password'] ),
		);

		// Get the database name and user.
		$dbname = $db_credentials['name'];
		$dbuser = $db_credentials['user'];
		delete_transient( 'fmdb_' . $dbname . '_' . $dbuser . '_connected' );

		// Create a new instance of the FlowMattic_DB_Connector class.
		$fm_db_connector = new FlowMattic_DB_Connector( $db_credentials );

		// Test the database connection.
		$test_connection = $fm_db_connector->test_connection();

		// Send the response.
		if ( $test_connection ) {
			wp_send_json_success( array( 'message' => 'Database connection successful.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Database connection failed.' ) );
		}
	}

	/**
	 * Get the database connection instance.
	 *
	 * @since 5.0
	 * @param string $table_database The table database.
	 * @access public
	 */
	public function get_db_connection( $table_database ) {
		// If database is local, create the table locally.
		$is_local_db              = false;
		$database_connection_data = array();

		if ( 'local' === $table_database ) {
			$is_local_db = true;
		} else {
			// Get the database connection.
			$database_connections = wp_flowmattic()->database_connections_db->get( array( 'database_id' => $table_database ) );

			// Get the database connection data.
			$database_connection_data = maybe_unserialize( $database_connections->connection_data );
		}

		// Create a new instance of the FlowMattic_DB_Connector class.
		$fm_db_connector = new FlowMattic_DB_Connector( $database_connection_data, $is_local_db );

		return $fm_db_connector;
	}

	/**
	 * Add new database.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function add_new_database() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic-add-new-database', 'security' );

		// Get the database credentials.
		$db_credentials = array(
			'host'     => sanitize_text_field( $_POST['database_host'] ),
			'name'     => sanitize_text_field( $_POST['database_name'] ),
			'user'     => sanitize_text_field( $_POST['database_user'] ),
			'password' => sanitize_text_field( $_POST['database_password'] ),
		);

		// Get database label.
		$database_label = sanitize_text_field( $_POST['database_label'] );

		// Get database description.
		$database_description = sanitize_text_field( $_POST['database_description'] );

		// Get the database connection db instance.
		$database_connections_db = wp_flowmattic()->database_connections_db;

		$args = array(
			'connection_name'     => $database_label,
			'connection_data'     => maybe_serialize( $db_credentials ),
			'connection_settings' => maybe_serialize(
				array(
					'description' => $database_description,
				)
			),
		);

		// Add the new database.
		$add_database = $database_connections_db->insert( $args );

		// Send the response.
		if ( $add_database ) {
			wp_send_json_success( array( 'message' => 'Database added successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Database could not be added.' ) );
		}
	}

	/**
	 * Update database.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function update_database() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic-add-new-database', 'security' );

		// Get the database ID.
		$database_id = sanitize_text_field( $_POST['database_id'] );

		// Get the database credentials.
		$db_credentials = array(
			'host'     => sanitize_text_field( $_POST['database_host'] ),
			'name'     => sanitize_text_field( $_POST['database_name'] ),
			'user'     => sanitize_text_field( $_POST['database_user'] ),
			'password' => sanitize_text_field( $_POST['database_password'] ),
		);

		// Get database label.
		$database_label = sanitize_text_field( $_POST['database_label'] );

		// Get database description.
		$database_description = sanitize_text_field( $_POST['database_description'] );

		// Get the database connection db instance.
		$database_connections_db = wp_flowmattic()->database_connections_db;

		$args = array(
			'connection_name'     => $database_label,
			'connection_data'     => maybe_serialize( $db_credentials ),
			'connection_settings' => maybe_serialize(
				array(
					'description' => $database_description,
				)
			),
		);

		// Update the database.
		$update_database = $database_connections_db->update( $database_id, $args );

		// Delete the database connection transient.
		delete_transient( 'fmdb_' . $db_credentials['name'] . '_' . $db_credentials['user'] . '_connected' );

		// Send the response.
		if ( $update_database ) {
			wp_send_json_success( array( 'message' => 'Database updated successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Database could not be updated.' ) );
		}
	}

	/**
	 * Get database connection.
	 *
	 * @since 5.0
	 * @access public
	 *
	 * @return array|boolean The database connection data or false if query failed.
	 */
	public function get_db_connection_details() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic-get-database-connection', 'security' );

		// Get the database ID.
		$database_id = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection.
		$database_connections = wp_flowmattic()->database_connections_db->get( array( 'database_id' => $database_id ) );

		// Send the response.
		if ( $database_connections ) {
			wp_send_json_success(
				array(
					'connection_name'     => $database_connections->connection_name,
					'database_connection' => maybe_unserialize( $database_connections->connection_data ),
					'connection_settings' => maybe_unserialize( $database_connections->connection_settings ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'Database connection could not be retrieved.' ) );
		}
	}

	/**
	 * Create new table.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function create_table() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_create_table', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table description.
		$table_description = sanitize_text_field( $_POST['table_description'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['table_database'] );

		// Get table columns.
		$column_names = $_POST['column_names'];

		// Table columns.
		$column_query  = '';
		$table_columns = array();

		if ( '' !== $column_names ) {
			$column_names = explode( ',', $column_names );

			foreach ( $column_names as $column_name ) {
				if ( '' !== trim( $column_name ) && 'id' !== strtolower( $column_name ) ) {
					// Set the original column name for schema.
					$original_column_name = $column_name;

					// Remove any spaces, special characters and convert to lowercase for column name.
					$column_name = strtolower( preg_replace( '/[^A-Za-z0-9]+/', '_', $column_name ) );

					// Add the column to the query.
					$column_query .= "`$column_name` longtext,";

					// Guess the column type.
					$column_type = 'text';

					// Check if column name contains email.
					if ( false !== strpos( $column_name, 'email' ) ) {
						$column_type = 'email';
					}

					// Check if column name contains date.
					if ( false !== strpos( $column_name, 'date' ) ) {
						$column_type = 'date';
					}

					// Add the column to the table columns array for schema.
					$table_columns[ $column_name ] = array(
						'name' => ucwords( str_replace( '_', ' ', $original_column_name ) ),
						'type' => $column_type,
						'key'  => $column_name,
					);

					// If column type is date, add a date format.
					if ( 'date' === $column_type ) {
						$table_columns[ $column_name ]['dateFormat'] = 'dd/M/y';
					}
				}
			}
		}

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Create the table query.
		$create_table_query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` int(11) NOT NULL AUTO_INCREMENT, " . $column_query . "
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

		// Create the table.
		$create_table = $fm_db_connector->maybe_create_table( $table_name, $create_table_query );

		// Remove the table transient.
		delete_transient( 'fm_tables_' . $table_database );

		// Send the response.
		if ( $create_table ) {
			// Add the table schema.
			$tables_schema_db = wp_flowmattic()->tables_schema_db;

			$args = array(
				'table_name'     => $table_name,
				'table_columns'  => ! empty( $table_columns ) ? maybe_serialize( $table_columns ) : '',
				'table_settings' => maybe_serialize(
					array(
						'description' => $table_description,
					)
				),
			);

			// Add the table schema.
			$add_table_schema = $tables_schema_db->insert( $args );

			wp_send_json_success( array( 'message' => 'Table created successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table could not be created.' ) );
		}
	}

	/**
	 * Get tables.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function get_tables() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the database ID.
		$database_id = sanitize_text_field( $_POST['database_id'] );

		// Get the tables transient.
		$tables_transient = get_transient( 'fm_tables_' . $database_id );

		// If tables transient exists, return the tables.
		if ( false !== $tables_transient ) {
			wp_send_json_success( array( 'tables' => $tables_transient ) );
		}

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $database_id );

		$settings              = get_option( 'flowmattic_settings', array() );
		$fetch_existing_tables = isset( $settings['fetch_existing_tables'] ) && 'yes' === $settings['fetch_existing_tables'] ? true : false;
		$table_names           = array();
		$database_name         = $fm_db_connector->get_db()->dbname;

		$is_local     = $database_name === DB_NAME;
		$table_prefix = '';

		if ( $is_local ) {
			global $wpdb;
			$table_prefix = $wpdb->prefix;
		}

		$tables_query  = 'SHOW TABLES FROM `' . $database_name . '` ';
		$second_clause = $fetch_existing_tables ? ' AND ' : ' WHERE ';

		if ( $fetch_existing_tables && $is_local ) {
			$tables_query .= ' WHERE Tables_in_' . $database_name . ' LIKE "' . $table_prefix . '%" ';

			$second_clause = ' AND ';
		}

		// Get the table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get_all();

		$table_names = array();
		if ( ! empty( $table_schema_db ) ) {
			foreach ( $table_schema_db as $table_schema ) {
				$table_names[ $table_schema->table_name ] = $table_schema->table_name;
			}
		}

		if ( ! $fetch_existing_tables ) {
			if ( ! empty( $table_names ) ) {
				$clause = ( $fetch_existing_tables && $is_local ) ? ' OR ' : ' WHERE ';

				if ( ! $fetch_existing_tables ) {
					$tables_query .= ' WHERE Tables_in_' . $database_name . ' IN (' . "'" . implode( "', '", $table_names ) . "') ";
				} else {
					$tables_query .= ' ' . $clause . ' Tables_in_' . $database_name . '  IN (' . "'" . implode( "', '", $table_names ) . "') ";
				}

				$second_clause = ' AND ';
			}
		} elseif ( ! empty( $table_names ) && ! $fetch_existing_tables ) {
			$tables_query .= ' WHERE Tables_in_' . $database_name . '  IN (' . "'" . implode( "', '", $table_names ) . "') ";
		} elseif ( $fetch_existing_tables ) {
			$second_clause = 'WHERE ';
			if ( $is_local ) {
				$second_clause = ' OR ';
			}
		} else {
			$second_clause = '' !== $table_prefix ? ' AND ' : ' WHERE ';
		}

		if ( $fetch_existing_tables ) {
			$fm_tables     = array(
				'flowmattic_chatbot',
				'flowmattic_chatbot_threads',
				'flowmattic_connects',
				'flowmattic_custom_apps',
				'flowmattic_data_connections',
				'flowmattic_data_tables',
				'flowmattic_tables_schema',
				'flowmattic_workflows',
			);
			$tables_query .= $second_clause . ' Tables_in_' . $database_name . " NOT LIKE '%" . implode( "%' OR '%", $fm_tables ) . "%' ";
		}

		// Get the tables.
		$tables = $fm_db_connector->get_db()->get_results( $tables_query );

		// Send the response.
		if ( $tables ) {
			$all_tables = array();

			foreach ( $tables as $table ) {
				$table_name                = $table->{'Tables_in_' . $database_name};
				$all_tables[ $table_name ] = $table_name;
			}

			// Store the tables in the transient.
			set_transient( 'fm_tables_' . $database_id, $all_tables, 60 * 60 * 24 );

			wp_send_json_success( array( 'tables' => $all_tables ) );
		} else {
			wp_send_json_error( array( 'message' => 'Tables could not be retrieved.' ) );
		}
	}

	/**
	 * Delete table.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function delete_table() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_delete_table', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['table_database'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Delete the table.
		$delete_table = $fm_db_connector->maybe_drop_table( $table_name );

		// Send the response.
		if ( $delete_table ) {
			// Delete the table schema.
			$tables_schema_db = wp_flowmattic()->tables_schema_db;

			$delete_table_schema = $tables_schema_db->delete( array( 'table_name' => $table_name ) );

			wp_send_json_success( array( 'message' => 'Table deleted successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table could not be deleted.' ) );
		}
	}

	/**
	 * Delete database.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function delete_database() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_delete_database', 'security' );

		// Get the database ID.
		$database_id = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection db instance.
		$database_connections_db = wp_flowmattic()->database_connections_db;

		// Delete the database.
		$delete_database = $database_connections_db->delete( array( 'database_id' => $database_id ) );

		// Send the response.
		if ( $delete_database ) {
			wp_send_json_success( array( 'message' => 'Database deleted successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Database could not be deleted.' ) );
		}
	}

	/**
	 * Get table data.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function get_table_data() {
		if ( $_POST['is_frontend'] ) {
			if ( ! wp_verify_nonce( $_POST['security'], 'flowmattic_table_nonce_frontend' ) ) {
				wp_send_json_error( array( 'message' => 'Security check failed' ) );
			}
		} else {
			// Check the nonce.
			check_ajax_referer( 'flowmattic_table_nonce', 'security' );
		}

		if ( ! isset( $_POST['table_name'] ) || ! isset( $_POST['database_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Table name and database ID are required.' ) );
		}

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the skip and limit.
		$skip = isset( $_POST['skip'] ) ? sanitize_text_field( $_POST['skip'] ) : 0;
		$take = isset( $_POST['take'] ) ? sanitize_text_field( $_POST['take'] ) : 15;

		// Get the sort name and direction.
		$sort_name      = isset( $_POST['sort_field'] ) ? sanitize_text_field( $_POST['sort_field'] ) : '';
		$sort_direction = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'ASC';

		if ( 'descending' === $sort_direction ) {
			$sort_direction = 'DESC';
		} else {
			$sort_direction = 'ASC';
		}

		// Get the filter field, operator and value.
		$filter_field    = isset( $_POST['filter_field'] ) ? sanitize_text_field( $_POST['filter_field'] ) : '';
		$filter_operator = isset( $_POST['filter_operator'] ) ? sanitize_text_field( $_POST['filter_operator'] ) : '';
		$filter_value    = isset( $_POST['filter_value'] ) ? sanitize_text_field( $_POST['filter_value'] ) : '';

		// If field is checkbox, check for unchecked value.
		if ( 'checkbox' === $filter_field && 'unchecked' === $filter_value ) {
			$filter_value    = 'checked';
			$filter_operator = 'notequal';
		}

		$filter_conditions = array(
			'startswith'       => $filter_field . ' LIKE "' . $filter_value . '%" ',
			'endswith'         => $filter_field . ' LIKE "%' . $filter_value . '" ',
			'contains'         => $filter_field . ' LIKE "%' . $filter_value . '%" ',
			'doesnotcontain'   => $filter_field . ' NOT LIKE "%' . $filter_value . '%" ',
			'equal'            => $filter_field . ' = "' . $filter_value . '" ',
			'isempty'          => $filter_field . ' = "" ',
			'isnotempty'       => $filter_field . ' != "" ',
			'doesnotstartwith' => $filter_field . ' NOT LIKE "' . $filter_value . '%" ',
			'doesnotendwith'   => $filter_field . ' NOT LIKE "%' . $filter_value . '" ',
			'notequal'         => $filter_field . ' != "' . $filter_value . '" ',
			'like'             => $filter_field . ' LIKE "%' . $filter_value . '%" ',
		);

		// Get the search query.
		$search_query = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

		// Prepare the query with search.
		$query = 'SELECT * FROM `' . $table_name . '`';

		if ( ! empty( $search_query ) ) {
			$query .= ' WHERE ';

			// Get the table columns.
			$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

			// Create an empty array.
			$columns = array();

			// Loop through the column names.
			foreach ( $table_columns as $table_column ) {
				$columns[] = $table_column->Field;
			}

			// Create an empty array.
			$where = array();

			// Loop through the columns.
			foreach ( $columns as $column ) {
				$where[] = "`$column` LIKE '%$search_query%'";
			}

			$query .= implode( ' OR ', $where );
		}

		// Add the filter.
		if ( '' !== $filter_field && '' !== $filter_operator ) {
			$query .= ' WHERE ' . $filter_conditions[ $filter_operator ];
		}

		// Check if frontend filter is active.
		if ( isset( $_POST['frontend_filter_field'] ) ) {
			$frontend_filter_field    = $_POST['frontend_filter_field'];
			$frontend_filter_value    = $_POST['frontend_filter_value'];
			$frontend_filter_operator = 'equal';

			$frontend_filter_conditions = array(
				'startswith'       => $frontend_filter_field . ' LIKE "' . $frontend_filter_value . '%" ',
				'endswith'         => $frontend_filter_field . ' LIKE "%' . $frontend_filter_value . '" ',
				'contains'         => $frontend_filter_field . ' LIKE "%' . $frontend_filter_value . '%" ',
				'doesnotcontain'   => $frontend_filter_field . ' NOT LIKE "%' . $frontend_filter_value . '%" ',
				'equal'            => $frontend_filter_field . ' = "' . $frontend_filter_value . '" ',
				'isempty'          => $frontend_filter_field . ' = "" ',
				'isnotempty'       => $frontend_filter_field . ' != "" ',
				'doesnotstartwith' => $frontend_filter_field . ' NOT LIKE "' . $frontend_filter_value . '%" ',
				'doesnotendwith'   => $frontend_filter_field . ' NOT LIKE "%' . $frontend_filter_value . '" ',
				'notequal'         => $frontend_filter_field . ' != "' . $frontend_filter_value . '" ',
				'like'             => $frontend_filter_field . ' LIKE "%' . $frontend_filter_value . '%" ',
			);

			$query .= ( '' !== $filter_field && '' !== $filter_operator ) ? ' AND ' . $frontend_filter_conditions[ $frontend_filter_operator ] : ' WHERE ' . $frontend_filter_conditions[ $frontend_filter_operator ];
		}

		// Add the sort.
		if ( '' !== $sort_name ) {
			$query .= ' ORDER BY `' . $sort_name . '` ' . $sort_direction;
		}

		$query .= ' LIMIT ' . $skip . ', ' . $take;

		// Get the table data.
		$table_data = $fm_db_connector->get_db()->get_results( $query );

		if ( '' !== $search_query || '' !== $filter_field ) {
			// Get the total count.
			$total_count = count( $table_data );
		} else {
			// Get the total count.
			$total_count = $fm_db_connector->get_db()->get_var( 'SELECT COUNT(*) FROM `' . $table_name . '`' );
		}

		// Get the table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Send the response.
		if ( $table_data ) {
			wp_send_json_success(
				array(
					'table_data'   => $table_data,
					'table_schema' => $table_schema,
					'total_count'  => (int) $total_count,
					'system_table' => $table_schema_db ? false : true,
				)
			);
		} elseif ( '' !== $search_query ) {
				wp_send_json_success(
					array(
						'table_data'   => array(),
						'table_schema' => $table_schema,
						'total_count'  => 0,
						'system_table' => $table_schema_db ? false : true,
					)
				);
		} else {
			// Get column names.
			$column_names = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

			// Create an empty array.
			$table_data = array();

			// Loop through the column names.
			foreach ( $column_names as $column_name ) {
				$table_data[ $column_name->Field ] = '';
			}

			wp_send_json_success(
				array(
					'table_data'   => array(),
					'table_schema' => $table_schema,
					'table_cols'   => array( $table_data ),
					'system_table' => $table_schema_db ? false : true,
				)
			);
		}
	}

	/**
	 * Add table data.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function add_table_data() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table data.
		$table_data = $_POST['added_rows'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		// Create an empty array.
		$columns = array();

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			$columns[] = $table_column->Field;
		}

		// Loop through the table data.
		foreach ( $table_data as $key => $data ) {
			// Update the data array to fix the slashes.
			$data = array_map( 'stripslashes_deep', $data );

			// Insert the table data.
			$insert_table_data = $fm_db_connector->get_db()->insert( $table_name, $data );

			// Get the last insert ID.
			$last_insert_id = $fm_db_connector->get_db()->insert_id;

			$inserted_record       = $data;
			$inserted_record['id'] = $last_insert_id;

			// Fire an action after the table data is inserted.
			do_action( 'flowmattic_after_table_row_inserted', $table_name, $inserted_record );
		}

		// Send the response.
		if ( $insert_table_data ) {
			wp_send_json_success( array( 'message' => 'Table data added successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table data could not be added.' ) );
		}
	}

	/**
	 * Update table data.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function update_table_data() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table data.
		$table_data = $_POST['updated_rows'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		$primary_key   = '';
		$primary_value = '';

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Loop through the table data.
		foreach ( $table_data as $key => $data ) {
			$updated_record = $data;
			$old_record     = array();

			// If the primary key is set, set the primary value.
			if ( isset( $data[ $primary_key ] ) ) {
				// Add the primary key value to the array.
				$primary_value = isset( $data[ $primary_key ] ) ? $data[ $primary_key ] : '';

				// Remove the primary key from the array.
				unset( $data[ $primary_key ] );
			}

			// Get the old record.
			$old_record = $fm_db_connector->get_db()->get_row( 'SELECT * FROM `' . $table_name . '` WHERE ' . $primary_key . ' = ' . $primary_value, ARRAY_A );

			// Update the data array to fix the slashes.
			$data = array_map( 'stripslashes_deep', $data );

			// Update the table data.
			$update_table_data = $fm_db_connector->get_db()->update( $table_name, $data, array( $primary_key => $primary_value ) );

			// Get the table schema.
			$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

			$table_schema = array();

			if ( $table_schema_db ) {
				$table_schema = maybe_unserialize( $table_schema_db->table_columns );
			}

			// Format the updated record.
			$updated_record = $this->format_record_data( $updated_record, $table_schema );
			$old_record     = $this->format_record_data( $old_record, $table_schema );

			// Fire an action after the table data is updated.
			do_action( 'flowmattic_after_table_row_updated', $table_name, $updated_record, $old_record );

			// Check which columns have been updated.
			$updated_columns = array_diff_assoc( $updated_record, $old_record );

			// Fire an action for the cell update.
			do_action( 'flowmattic_after_table_cell_updated', $table_name, $updated_columns, $updated_record );
		}

		// Send the response.
		if ( $update_table_data ) {
			wp_send_json_success( array( 'message' => 'Table data updated successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table data could not be updated.' ) );
		}
	}

	/**
	 * Delete table data.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function delete_table_data() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table data.
		$table_data = $_POST['deleted_rows'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		$primary_key   = '';
		$primary_value = '';

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// Loop through the table data.
		foreach ( $table_data as $key => $data ) {
			// If the primary key is set, set the primary value.
			if ( isset( $data[ $primary_key ] ) ) {
				// Add the primary key value to the array.
				$primary_value = isset( $data[ $primary_key ] ) ? $data[ $primary_key ] : '';
			}

			// Delete the table data.
			$delete_table_data = $fm_db_connector->get_db()->delete( $table_name, array( $primary_key => $primary_value ) );

			// Fire an action after the table data is deleted.
			do_action( 'flowmattic_after_table_row_deleted', $table_name, $data );
		}

		// Send the response.
		if ( $delete_table_data ) {
			wp_send_json_success( array( 'message' => 'Table data deleted successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table data could not be deleted.' ) );
		}
	}

	/**
	 * Add new column.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function add_new_column() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the column name.
		$column_name = sanitize_text_field( $_POST['column_name'] );

		// Use the column name key to create the column name.
		$column_name_key = strtolower( $column_name );

		// Prepare the column name key, remove special characters, spaces and hyphens and replace with underscores.
		$column_name_key = preg_replace( '/[^A-Za-z0-9]+/', '_', $column_name_key );

		// Get the column data.
		$column_data = $_POST['column_data'];

		// Add the column name as column header.
		$column_data['key'] = $column_name_key;

		// Use the column name key as column name.
		$column_name = $column_name_key;

		// Get the column type.
		$column_type = 'longtext';

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Column definition.
		$column_def = $column_type;

		// Add the column.
		$add_column = $fm_db_connector->maybe_add_column( $table_name, $column_name, $column_def );

		// Send the response.
		if ( $add_column ) {
			// Add the column to the table schema.
			$tables_schema_db = wp_flowmattic()->tables_schema_db;

			$args = array(
				'table_name'    => $table_name,
				'table_columns' => maybe_serialize( $column_data ),
			);

			// Check if the table schema exists.
			$table_schema_exists = $tables_schema_db->get( array( 'table_name' => $table_name ) );

			if ( $table_schema_exists ) {
				// Get the table id.
				$table_id = $table_schema_exists->id;

				// Add new column to the existing columns.
				$old_columns = maybe_unserialize( $table_schema_exists->table_columns );

				$old_columns = is_array( $old_columns ) ? $old_columns : array();

				// Add the new column to the old columns.
				$old_columns[ $column_name ] = $column_data;

				// Serialize the old columns.
				$args['table_columns'] = maybe_serialize( $old_columns );

				// Delete the unused keys.
				unset( $args['table_id'] );

				// Update the column schema.
				$add_column_schema = $tables_schema_db->update( $table_id, $args );
			} else {
				// Insert the column schema.
				$add_column_schema = $tables_schema_db->insert( $args );
			}

			// Remove the table columns transient.
			delete_transient( 'fm_columns_' . $table_database . '_' . $table_name );

			wp_send_json_success( array( 'message' => 'Column added successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Column could not be added.' ) );
		}
	}

	/**
	 * Get columns of a table.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function get_table_columns() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the table name.
		$table_name = isset( $_POST['table_name'] ) ? sanitize_text_field( $_POST['table_name'] ) : '';

		// Get the table database.
		$table_database = isset( $_POST['database_id'] ) ? sanitize_text_field( $_POST['database_id'] ) : '';

		// Get the table columns transient.
		$table_columns_transient = get_transient( 'fm_columns_' . $table_database . '_' . $table_name );

		// If table columns transient exists, return the table columns.
		if ( false !== $table_columns_transient ) {
			wp_send_json_success( array( 'table_columns' => $table_columns_transient ) );
		}

		if ( empty( $table_name ) ) {
			wp_send_json_error( array( 'message' => 'Table name is required.' ) );
		}

		if ( empty( $table_database ) ) {
			wp_send_json_error( array( 'message' => 'Database ID is required.' ) );
		}

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		// Get table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Send the response.
		if ( $table_columns ) {
			$all_columns = array();
			foreach ( $table_columns as $key => $column ) {
				if ( 'PRI' !== $column->Key && 'button' !== $table_schema[ $column->Field ]['type'] ) {
					$column_name                   = isset( $table_schema[ $column->Field ]['name'] ) ? $table_schema[ $column->Field ]['name'] : $column->Field;
					$all_columns[ $column->Field ] = array(
						'title'   => $column_name,
						'type'    => $table_schema[ $column->Field ]['type'],
						'options' => isset( $table_schema[ $column->Field ]['dropdownItems'] ) ? $table_schema[ $column->Field ]['dropdownItems'] : array(),
					);
				}
			}

			// Store the columns in the transient.
			set_transient( 'fm_columns_' . $table_database . '_' . $table_name, $all_columns, 60 * 60 * 24 );

			wp_send_json_success( array( 'table_columns' => $all_columns ) );
		} else {
			wp_send_json_error( array( 'message' => 'Table columns could not be retrieved.' ) );
		}
	}

	/**
	 * Update column.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function update_column() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the column name.
		$column_name = sanitize_text_field( $_POST['column_name'] );

		// Prepare the column name key, remove special characters, spaces and hyphens and replace with underscores.
		$column_name_key = preg_replace( '/[^A-Za-z0-9]/', '_', $column_name );

		// Use the column name key to create the column name.
		$column_name_key = strtolower( $column_name_key );

		// Get the column data.
		$column_data = $_POST['column_data'];

		// Use the column name key as column name.
		$column_name = $column_name_key;

		// Update the column in the table schema.
		$tables_schema_db = wp_flowmattic()->tables_schema_db;

		$args = array(
			'table_name' => $table_name,
		);

		// Get the column key.
		$column_key = sanitize_text_field( $_POST['column_key'] );

		// Add the column name as column header.
		$column_data['key'] = $column_key;

		// Check if the table schema exists.
		$table_schema_exists = $tables_schema_db->get( array( 'table_name' => $table_name ) );

		if ( $table_schema_exists ) {
			// Get the table id.
			$table_id = $table_schema_exists->id;

			// Get the old columns.
			$old_columns = maybe_unserialize( $table_schema_exists->table_columns );

			$old_columns = is_array( $old_columns ) ? $old_columns : array();

			// Loop through the old columns.
			foreach ( $old_columns as $key => $column ) {
				// If the column found, update it.
				if ( isset( $column['key'] ) && $column_key === $column['key'] ) {
					$old_columns[ $key ] = $column_data;
				}
			}

			// Serialize the old columns.
			$args['table_columns'] = maybe_serialize( $old_columns );

			// Delete the unused keys.
			unset( $args['table_id'] );

			// Update the column schema.
			$update_column_schema = $tables_schema_db->update( $table_id, $args );
		}

		wp_send_json_success( array( 'message' => 'Column updated successfully.' ) );
	}

	/**
	 * Delete column.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function delete_column() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the column name.
		$column_name = sanitize_text_field( $_POST['column_name'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Delete the column.
		$delete_column = $fm_db_connector->maybe_drop_column( $table_name, $column_name );

		// Send the response.
		if ( $delete_column ) {
			// Delete the column from the table schema.
			$tables_schema_db = wp_flowmattic()->tables_schema_db;

			$args = array(
				'table_name' => $table_name,
			);

			// Check if the table schema exists.
			$table_schema_exists = $tables_schema_db->get( array( 'table_name' => $table_name ) );

			if ( $table_schema_exists ) {
				// Get the table id.
				$table_id = $table_schema_exists->id;

				// Get the old columns.
				$old_columns = maybe_unserialize( $table_schema_exists->table_columns );
				$old_columns = is_array( $old_columns ) ? $old_columns : array();

				// Remove the column from the old columns.
				unset( $old_columns[ $column_name ] );

				// Loop through the old columns.
				foreach ( $old_columns as $key => $column ) {
					// If the column found, remove it.
					if ( $column_name === $column['key'] ) {
						unset( $old_columns[ $key ] );
					}
				}

				// Serialize the old columns.
				$args['table_columns'] = maybe_serialize( $old_columns );

				// Delete the unused keys.
				unset( $args['table_id'] );

				// Update the column schema.
				$delete_column_schema = $tables_schema_db->update( $table_id, $args );
			}

			wp_send_json_success( array( 'message' => 'Column deleted successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Column could not be deleted.' ) );
		}
	}

	/**
	 * Listen to the table button click.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function table_button_trigger() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the button clicked row data.
		$table_data = $_POST['row_data'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Update the data array to fix the slashes.
		$table_data = array_map( 'stripslashes_deep', $table_data );

		// Prepare the data array to include the table name.
		$table_data['table_name'] = $table_name;
	
		// Send the response.
		flowmattic_return_response( array( 'message' => 'Button action triggered successfully.' ) );

		// Fire an action to send the data.
		do_action( 'flowmattic_table_button_trigger', $table_name, $table_data );
	}

	/**
	 * Listen approve button click to approve row data
	 *
	 * @since 5.0
	 * @access public
	 */
	public function approve_row() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the approve button column name.
		$approve_column = sanitize_text_field( $_POST['approve_field'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table data.
		$table_data = $_POST['row_data'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		$primary_key   = '';
		$primary_value = isset( $table_data['id'] ) ? $table_data['id'] : '';

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// If the primary key is set, set the primary value.
		if ( isset( $table_data[ $primary_key ] ) ) {
			// Add the primary key value to the array.
			$primary_value = isset( $table_data[ $primary_key ] ) ? $table_data[ $primary_key ] : '';
		}

		// Get table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Get the approve column schema.
		$approve_column_schema = $table_schema[ $approve_column ];

		// Get button options.
		$button_options = $approve_column_schema['buttonOptions'];

		// Set the default and secondary state to disabled.
		$button_options[0]['defaultState']   = 'disabled';
		$button_options[0]['secondaryState'] = 'disabled';

		// Add the approved state.
		$button_options[0]['rowApproved'] = true;
		$button_options[0]['rowRejected'] = false;

		// Add the click status.
		$button_options[0]['approvedButtonClicked'] = true;
		$button_options[0]['rejectedButtonClicked'] = false;

		// Get the record from the database.
		$record = $fm_db_connector->get_db()->get_row( 'SELECT * FROM `' . $table_name . '` WHERE ' . $primary_key . ' = ' . $primary_value, ARRAY_A );

		// Get the button options.
		$button_column = $record[ $approve_column ];
		$button_data   = json_decode( $button_column, true );
		$old_button_options = $button_data;

		if (! empty( $old_button_options ) ) {
			// Merge the old button options with the new button options.
			$button_options = array_replace_recursive( $old_button_options, $button_options );
		}

		// Get the continue data, if available.
		$continue_data = isset( $old_button_options[0]['continue_data'] ) ? $old_button_options[0]['continue_data'] : array();

		// Encode the button options.
		$button_options = json_encode( $button_options );

		// Update the approve column.
		$update_approve_column = $fm_db_connector->get_db()->update( $table_name, array( $approve_column => $button_options ), array( $primary_key => $primary_value ) );

		// Send the response.
		flowmattic_return_response( array( 'message' => 'Row approved successfully.' ) );

		// Fire an action after the row is approved.
		do_action( 'flowmattic_after_table_row_approved', $table_name, $table_data, $approve_column, $primary_value, $continue_data );
	}

	/**
	 * Listen reject button click to reject row data
	 *
	 * @since 5.0
	 * @access public
	 */
	public function reject_row() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_table_nonce', 'security' );

		// Get the table name.
		$table_name = sanitize_text_field( $_POST['table_name'] );

		// Get the table database.
		$table_database = sanitize_text_field( $_POST['database_id'] );

		// Get the reject button column name.
		$reject_column = sanitize_text_field( $_POST['reject_field'] );

		// Get the database connection.
		$fm_db_connector = $this->get_db_connection( $table_database );

		// Get the table data.
		$table_data = $_POST['row_data'];

		// JSON decode the table data.
		$table_data = ! is_array( $table_data ) ? json_decode( stripslashes( $table_data ), true ) : $table_data;

		// Get the table columns.
		$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );

		$primary_key   = '';
		$primary_value = isset( $table_data['id'] ) ? $table_data['id'] : '';

		// Loop through the column names.
		foreach ( $table_columns as $table_column ) {
			// If is primary key, set the primary key and break.
			if ( 'PRI' === $table_column->Key ) {
				$primary_key = $table_column->Field;
				break;
			}
		}

		// If the primary key is set, set the primary value.
		if ( isset( $table_data[ $primary_key ] ) ) {
			// Add the primary key value to the array.
			$primary_value = isset( $table_data[ $primary_key ] ) ? $table_data[ $primary_key ] : '';
		}

		// Get table schema.
		$table_schema_db = wp_flowmattic()->tables_schema_db->get( array( 'table_name' => $table_name ) );

		$table_schema = array();

		if ( $table_schema_db ) {
			$table_schema = maybe_unserialize( $table_schema_db->table_columns );
		}

		// Get the reject column schema.
		$reject_column_schema = $table_schema[ $reject_column ];

		// Get button options.
		$button_options = $reject_column_schema['buttonOptions'];

		// Set the default and secondary state to disabled.
		$button_options[0]['defaultState']   = 'disabled';
		$button_options[0]['secondaryState'] = 'disabled';

		// Add the approved state.
		$button_options[0]['rowApproved'] = false;
		$button_options[0]['rowRejected'] = true;

		// Add the click status.
		$button_options[0]['approvedButtonClicked'] = false;
		$button_options[0]['rejectedButtonClicked'] = true;

		// Get the record from the database.
		$record = $fm_db_connector->get_db()->get_row( 'SELECT * FROM `' . $table_name . '` WHERE ' . $primary_key . ' = ' . $primary_value, ARRAY_A );

		// Get the button options.
		$button_column = $record[ $reject_column ];
		$button_data   = json_decode( $button_column, true );
		$old_button_options = $button_data;

		if (! empty( $old_button_options ) ) {
			// Merge the old button options with the new button options.
			$button_options = array_replace_recursive( $old_button_options, $button_options );
		}

		// Get the continue data, if available.
		$continue_data = isset( $old_button_options[0]['continue_data'] ) ? $old_button_options[0]['continue_data'] : array();

		// Encode the button options.
		$button_options = json_encode( $button_options );

		// Update the reject column.
		$update_reject_column = $fm_db_connector->get_db()->update( $table_name, array( $reject_column => $button_options ), array( $primary_key => $primary_value ) );

		// Send the response.
		flowmattic_return_response( array( 'message' => 'Row rejected successfully.' ) );

		// Fire an action after the row is rejected.
		do_action( 'flowmattic_after_table_row_rejected', $table_name, $table_data, $reject_column, $primary_value, $continue_data );
	}

	/**
	 * Display the table.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $args The table arguments.
	 * @return string The table HTML.
	 */
	public function display_table_shortcode( $args = array() ) {
		// Get the table name.
		$table_name = isset( $args['table_name'] ) ? $args['table_name'] : '';

		// Get the table database.
		$table_database = isset( $args['database'] ) ? $args['database'] : '';

		// Get the filter.
		$filter_column = isset( $args['filter_column'] ) ? $args['filter_column'] : '';

		// Get the filter value.
		$filter_value = isset( $args['filter_value'] ) ? $args['filter_value'] : '';

		// Get the width.
		$width = isset( $atts['width'] ) ? sanitize_text_field( $atts['width'] ) : '100%';

		// Get the height.
		$height = isset( $atts['height'] ) ? sanitize_text_field( $atts['height'] ) : '800px';

		$table_id = base64_encode( $table_name . '@@' . $table_database );

		// Get the table route URL.
		$table_url = rest_url( 'flowmattic/v1/embed/table/' ) . '?table_id=' . $table_id;

		if ( '' !== $filter_column && '' !== $filter_value ) {
			$table_url .= '&filter_column=' . $filter_column . '&filter_value=' . $filter_value;
		}

		$nonce = wp_create_nonce( 'flowmattic_table_nonce_frontend' );

		$table_url .= '&nonce=' . $nonce;

		// Generate iframe.
		$table_iframe = '<iframe src="' . $table_url . '" style="width: ' . $width . '; height: ' . $height . '; border: none;"></iframe>';

		return $table_iframe;
	}

	/**
	 * Get the correct date format.
	 *
	 * @access public
	 * @since 5.1.1
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
	 * Format the record data.
	 *
	 * @access public
	 * @since 5.1.1
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
}
