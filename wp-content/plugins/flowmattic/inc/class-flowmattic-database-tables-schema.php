<?php
/**
 * Handle database queries for tables schema.
 *
 * @package flowmattic
 * @since 5.0
 */

/**
 * Handle database queries for tables schema.
 *
 * @since 5.0
 */
class FlowMattic_Database_Tables_Schema {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 5.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_tables_schema';

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
		if ( ! isset( $args['table_name'] ) ) {
			return false;
		}

		// Delete the cache.
		wp_cache_delete( 'flowmattic_table_schema_' . $args['table_name'], 'flowmattic_table_schema' );

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'table_name'     => esc_attr( $args['table_name'] ),
				'table_columns'  => $args['table_columns'],
				'table_settings' => isset( $args['table_settings'] ) ? $args['table_settings'] : '',
				'table_time'     => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update the table in database.
	 *
	 * @since 5.0
	 * @access public
	 * @param int   $table_id The table ID.
	 * @param array $args The arguments.
	 * @return array
	 */
	public function update( $table_id, $args ) {
		global $wpdb;

		// Check if table ID is provided, else skip.
		if ( '' !== $table_id ) {
			$update = $wpdb->update(
				$wpdb->prefix . $this->table_name,
				$args,
				array(
					'id' => esc_attr( $table_id ),
				)
			);

			// Delete the cache
			wp_cache_delete( 'flowmattic_table_schema_' . $args['table_name'], 'flowmattic_table_schema' );

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

		// Check if table name is provided, else skip.
		if ( isset( $args['table_name'] ) ) {
			$update = $wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'table_name' => esc_attr( $args['table_name'] ),
				)
			);

			// Delete the cache.
			wp_cache_delete( 'flowmattic_table_schema_' . $args['table_name'], 'flowmattic_table_schema' );

			return $update;
		}
	}

	/**
	 * Get the table from database.
	 *
	 * @since 5.0
	 * @access public
	 * @param array $args The arguments.
	 * @return string
	 */
	public function get( $args ) {
		global $wpdb;

		// Check if table name is provided, else skip.
		if ( isset( $args['table_name'] ) ) {
			$query_key    = 'flowmattic_table_schema_' . $args['table_name'];
			$query_id     = md5( $query_key );
			$table_schema = wp_cache_get( $query_id, 'flowmattic_table_schema' );

			// Check if table schema is in cache.
			if ( false !== $table_schema ) {
				return $table_schema;
			}

			$table = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `table_name` = %s', $args['table_name'] )
			);

			$table_schema = isset( $table[0] ) ? $table[0] : false;

			// Set the cache.
			wp_cache_set( $query_id, $table_schema, 'flowmattic_table_schema' );

			return $table_schema;
		}

		return false;
	}

	/**
	 * Get all tables_schema from database.
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
	 * Get the total tables_schema count.
	 *
	 * @since 5.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_tables_schema_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}
}
