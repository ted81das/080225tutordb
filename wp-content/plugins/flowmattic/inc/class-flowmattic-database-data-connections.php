<?php
/**
 * Handle database queries for database connections.
 *
 * @package flowmattic
 * @since 5.0
 */

/**
 * Handle database queries for database connections.
 *
 * @since 5.0
 */
class FlowMattic_Database_Data_Connections {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 5.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_data_connections';

	/**
	 * The Constructor.
	 *
	 * @since 5.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert table to database.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Check if table name is provided, else skip.
		if ( ! isset( $args['connection_name'] ) ) {
			return false;
		}

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'connection_name'     => esc_attr( $args['connection_name'] ),
				'connection_data'     => $args['connection_data'],
				'connection_settings' => isset( $args['connection_settings'] ) ? $args['connection_settings'] : '',
				'connection_time'     => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update the table in database.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $database_id The database ID.
	 * @param array $args        The arguments.
	 * @return array
	 */
	public function update( $database_id, $args ) {
		global $wpdb;

		// Check if database ID is provided, else skip.
		if ( '' !== $database_id ) {
			$update = $wpdb->update(
				$wpdb->prefix . $this->table_name,
				$args,
				array(
					'id' => esc_attr( $database_id ),
				)
			);

			// Delete cache.
			wp_cache_delete( 'flowmattic_connection_data_' . $database_id, 'flowmattic_connection_data' );

			return $update;
		}
	}

	/**
	 * Delete the table from database.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $args The arguments.
	 * @return array
	 */
	public function delete( $args ) {
		global $wpdb;

		// Check if database ID is provided, else skip.
		if ( isset( $args['database_id'] ) ) {
			$update = $wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'id' => esc_attr( $args['database_id'] ),
				)
			);

			// Delete the cache.
			wp_cache_delete( 'flowmattic_connection_data_' . $args['database_id'], 'flowmattic_connection_data' );

			return $update;
		}
	}

	/**
	 * Get the connection data from database.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $args The arguments.
	 * @return string
	 */
	public function get( $args ) {
		global $wpdb;

		// Check if database ID is provided, else skip.
		if ( isset( $args['database_id'] ) ) {
			$query_key       = 'flowmattic_connection_data_' . $args['database_id'];
			$query_id        = md5( $query_key );
			$connection_data = wp_cache_get( $query_id, 'flowmattic_connection_data' );

			// Check if the connection data is there in cache.
			if ( false !== $connection_data ) {
				return $connection_data;
			}

			$connection_data = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `id` = %s', $args['database_id'] )
			);

			$connection_data = isset( $connection_data[0] ) ? $connection_data[0] : false;

			// Set the connection data in cache.
			wp_cache_set( $query_id, $connection_data, 'flowmattic_connection_data' );

			return $connection_data;
		}

		return false;
	}

	/**
	 * Get all data_connections from database.
	 *
	 * @since 5.0
	 * @access public
	 * @param int $offset The pagination offset.
	 * @return object
	 */
	public function get_all( $offset = 0 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` ORDER BY `ID` ASC LIMIT %d, %d', $offset, 100 )
		);

		return $results;
	}

	/**
	 * Get the total data_connections count.
	 *
	 * @since 5.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_data_connections_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}
}
