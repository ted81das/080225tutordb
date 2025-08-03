<?php
/**
 * FlowMattic Database Connector Class.
 *
 * This class connects to an external database using the wpdb class
 * and provides methods for database operations, including table creation
 * and schema updates.
 *
 * @package FlowMatticDBConnector
 * @since   5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FlowMattic_DB_Connector
 *
 * Connects to a FlowMattic database and performs CRUD operations.
 */
class FlowMattic_DB_Connector {

	/**
	 * FlowMattic database connection.
	 *
	 * @access private
	 * @since  5.0
	 * @var wpdb
	 */
	private $fm_db;

	/**
	 * Database credentials.
	 *
	 * @access private
	 * @since  5.0
	 * @var array
	 */
	private $db_credentials;

	/**
	 * Database connection status.
	 *
	 * @access private
	 * @since  5.0
	 * @var bool
	 */
	private $connected = false;

	/**
	 * Constructor.
	 *
	 * @since 5.0
	 * @param array $db_credentials Database connection details.
	 * @param bool  $local         Whether the database is local or remote.
	 */
	public function __construct( $db_credentials, $local = false ) {
		$local = isset( $db_credentials['host'] ) && 'localhost' === $db_credentials['host'] ? true : $local;
		if ( $local ) {
			// Use the global wpdb instance.
			global $wpdb;
			$this->fm_db = $wpdb;
		} else {
			$this->db_credentials = $db_credentials;
			$this->connect_to_db();
		}
	}

	/**
	 * Connect to the FlowMattic database.
	 *
	 * @access private
	 * @since  5.0
	 * @return void
	 */
	private function connect_to_db() {
		// Extract credentials.
		$dbhost     = $this->db_credentials['host'];
		$dbuser     = $this->db_credentials['user'];
		$dbpassword = $this->db_credentials['password'];
		$dbname     = $this->db_credentials['name'];
		$dbprefix   = isset( $this->db_credentials['prefix'] ) ? $this->db_credentials['prefix'] : '';

		// Include wpdb class if not already included.
		if ( ! class_exists( 'wpdb' ) ) {
			require_once ABSPATH . WPINC . '/class-wpdb.php';
		}

		// Check if the connection status is stored in transient.
		$connected = get_transient( 'fmdb_' . $dbname . '_' . $dbuser . '_connected' );

		$this->connected = ( 'yes' === $connected );

		// If the connection status is not stored in transient, test the connection.
		if ( false === $connected ) {
			try {
				// Initialize the database connection.
				$dbh = mysqli_init();

				// Enable exceptions for error handling.
				mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

				// Connect to the database.
				$conn = @mysqli_real_connect( $dbh, $dbhost, $dbuser, $dbpassword, $dbname, null, null, MYSQLI_CLIENT_SSL );

				// Set the connection status.
				$this->connected = $conn;

				// Close the connection.
				mysqli_close( $dbh );
			} catch ( Exception $e ) {
				// Set the connection status to false.
				$this->connected = false;
			}

			// Store the connection status in transient.
			$connected = $this->connected ? 'yes' : 'no';
			set_transient( 'fmdb_' . $dbname . '_' . $dbuser . '_connected', $connected, HOUR_IN_SECONDS );
		}

		// Check if the connection was successful.
		if ( $this->connected ) {
			if ( ! defined( 'DB_SSL' ) ) {
				define( 'DB_SSL', true );
			}

			if ( ! defined( 'MYSQL_CLIENT_FLAGS' ) ) {
				define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL );
			}

			// Create a new wpdb instance.
			$this->fm_db = new wpdb( $dbuser, $dbpassword, $dbname, $dbhost );

			// Set the prefix for the FlowMattic database tables.
			$this->fm_db->set_prefix( $dbprefix );

			// Set charset to UTF-8 to support special characters like Chinese
			$this->fm_db->set_charset( $this->fm_db->dbh, 'utf8mb4' );

			// Check for connection errors.
			if ( $this->fm_db->last_error ) {
				// Return WP error.
				return new WP_Error( 'db_error', $this->fm_db->last_error );
			}
		}
	}

	/**
	 * Get the FlowMattic wpdb instance.
	 *
	 * @access public
	 * @since 5.0
	 * @return wpdb
	 */
	public function get_db() {
		return $this->fm_db;
	}

	/**
	 * Test the FlowMattic database connection.
	 *
	 * @access public
	 * @since  5.0
	 * @return bool True if connection is successful, false otherwise.
	 */
	public function test_connection() {
		return $this->connected;
	}

	/**
	 * Create a table if it does not exist.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name Table name without prefix.
	 * @param string $create_sql SQL statement to create the table.
	 *
	 * @return void
	 */
	public function maybe_create_table( $table_name, $create_sql ) {
		$full_table_name = $this->fm_db->prefix . $table_name;

		// Check if the table exists.
		$table_exists = $this->fm_db->get_var(
			$this->fm_db->prepare( 'SHOW TABLES LIKE %s', $full_table_name )
		);

		if ( $table_exists !== $full_table_name ) {
			// Replace the table name placeholder.
			$sql = str_replace( '{table_name}', $full_table_name, $create_sql );

			// Execute the SQL.
			$this->fm_db->query( $sql );

			// Check for errors.
			if ( $this->fm_db->last_error ) {
				return new WP_Error( 'db_error', $this->fm_db->last_error );
			}

			return true;
		}

		return false;
	}

	/**
	 * Drop a table if it exists.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name Table name without prefix.
	 *
	 * @return bool|WP_Error True if table was dropped, false if it does not exist, WP_Error on failure.
	 */
	public function maybe_drop_table( $table_name ) {
		// $full_table_name = $this->fm_db->prefix . $table_name;
		$full_table_name = $table_name;

		// Check if the table exists.
		$table_exists = $this->fm_db->get_var(
			$this->fm_db->prepare( 'SHOW TABLES LIKE %s', $full_table_name )
		);

		if ( $table_exists === $full_table_name ) {
			// Drop the table.
			$sql = "DROP TABLE IF EXISTS `$full_table_name`";

			$this->fm_db->query( $sql );

			// Check for errors.
			if ( $this->fm_db->last_error ) {
				return new WP_Error( 'db_error', $this->fm_db->last_error );
			}

			return true;
		}

		return false;
	}

	/**
	 * Add a column to a table if it does not exist.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name  Table name without prefix.
	 * @param string $column_name Column name.
	 * @param string $column_def  Column definition.
	 *
	 * @return bool|WP_Error True if column was added, false if it already exists, WP_Error on failure.
	 */
	public function maybe_add_column( $table_name, $column_name, $column_def ) {
		// $full_table_name = $this->fm_db->prefix . $table_name;
		$full_table_name = $table_name;

		// Check if column exists.
		$column_exists = $this->fm_db->get_results(
			$this->fm_db->prepare(
				"SHOW COLUMNS FROM `$full_table_name` LIKE %s",
				$column_name
			)
		);

		if ( empty( $column_exists ) ) {
			// Add the column.
			$sql = "ALTER TABLE `$full_table_name` ADD `$column_name` $column_def";

			$this->fm_db->query( $sql );

			// Check for errors.
			if ( $this->fm_db->last_error ) {
				return new WP_Error( 'db_error', $this->fm_db->last_error );
			}

			return true;
		}

		return false;
	}

	/**
	 * Drop a column from a table if it exists.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name Table name without prefix.
	 * @param string $column_name Column name.
	 *
	 * @return bool|WP_Error True if column was dropped, false if it does not exist, WP_Error on failure.
	 */
	public function maybe_drop_column( $table_name, $column_name ) {
		// $full_table_name = $this->fm_db->prefix . $table_name;
		$full_table_name = $table_name;

		// Check if column exists.
		$column_exists = $this->fm_db->get_results(
			$this->fm_db->prepare(
				"SHOW COLUMNS FROM `$full_table_name` LIKE %s",
				$column_name
			)
		);

		if ( ! empty( $column_exists ) ) {
			// Drop the column.
			$sql = "ALTER TABLE `$full_table_name` DROP COLUMN `$column_name`";

			$this->fm_db->query( $sql );

			// Check for errors.
			if ( $this->fm_db->last_error ) {
				return new WP_Error( 'db_error', $this->fm_db->last_error );
			}

			return true;
		}

		return false;
	}

	/**
	 * Get data from the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table  Table name without prefix.
	 * @param array  $where  WHERE conditions.
	 * @param string $fields Fields to select.
	 *
	 * @return array|object|null Database query results.
	 */
	public function get_data( $table, $where = array(), $fields = '*' ) {
		$table_name = $this->fm_db->prefix . $table;

		// Build the WHERE clause.
		$where_clause = '';
		$values       = array();

		if ( ! empty( $where ) ) {
			$where_clause = 'WHERE ';
			$conditions   = array();

			foreach ( $where as $field => $value ) {
				$conditions[] = "`$field` = %s";
				$values[]     = $value;
			}

			$where_clause .= implode( ' AND ', $conditions );
		}

		// Prepare the SQL query.
		$query = $this->fm_db->prepare(
			"SELECT {$fields} FROM {$table_name} {$where_clause}",
			$values
		);

		// Execute the query and return results.
		return $this->fm_db->get_results( $query );
	}

	/**
	 * Insert data into the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table Table name without prefix.
	 * @param array  $data  Data to insert (column => value).
	 *
	 * @return int|false Inserted row ID or false on failure.
	 */
	public function insert_data( $table, $data ) {
		$table_name = $this->fm_db->prefix . $table;

		$inserted = $this->fm_db->insert( $table_name, $data );

		if ( false === $inserted ) {
			return false;
		}

		return $this->fm_db->insert_id;
	}

	/**
	 * Update data in the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table Table name without prefix.
	 * @param array  $data  Data to update (column => value).
	 * @param array  $where WHERE conditions.
	 *
	 * @return int|false Number of rows updated or false on failure.
	 */
	public function update_data( $table, $data, $where ) {
		$table_name = $this->fm_db->prefix . $table;

		return $this->fm_db->update( $table_name, $data, $where );
	}

	/**
	 * Delete data from the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table Table name without prefix.
	 * @param array  $where WHERE conditions.
	 *
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public function delete_data( $table, $where ) {
		$table_name = $this->fm_db->prefix . $table;

		return $this->fm_db->delete( $table_name, $where );
	}

	/**
	 * Check if a table exists.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name Table name without prefix.
	 *
	 * @return bool True if table exists, false otherwise.
	 */
	public function table_exists( $table_name ) {
		$full_table_name = $this->fm_db->prefix . $table_name;

		$table_exists = $this->fm_db->get_var(
			$this->fm_db->prepare( 'SHOW TABLES LIKE %s', $full_table_name )
		);

		return $table_exists === $full_table_name;
	}

	/**
	 * Check if a column exists in a table.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $table_name  Table name without prefix.
	 * @param string $column_name Column name.
	 *
	 * @return bool True if column exists, false otherwise.
	 */
	public function column_exists( $table_name, $column_name ) {
		$full_table_name = $this->fm_db->prefix . $table_name;

		$column_exists = $this->fm_db->get_results(
			$this->fm_db->prepare(
				"SHOW COLUMNS FROM `$full_table_name` LIKE %s",
				$column_name
			)
		);

		return ! empty( $column_exists );
	}

	/**
	 * Get the last error from the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @return string Last error message.
	 */
	public function get_last_error() {
		return $this->fm_db->last_error;
	}

	/**
	 * Perform a custom query on the FlowMattic database.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $query SQL query.
	 *
	 * @return mixed Query result.
	 */
	public function query( $query ) {
		return $this->fm_db->query( $query );
	}

	/**
	 * Escape a string for use in a SQL query.
	 *
	 * @access public
	 * @since  5.0
	 * @param string $string The string to escape.
	 *
	 * @return string Escaped string.
	 */
	public function escape( $string ) {
		return $this->fm_db->_escape( $string );
	}
}
