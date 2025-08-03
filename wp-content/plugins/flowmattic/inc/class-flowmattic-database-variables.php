<?php
/**
 * Handle database queries for variables.
 *
 * @package flowmattic
 * @since 4.0
 */

/**
 * Handle database queries for variables.
 *
 * @since 4.0
 */
class FlowMattic_Database_Variables {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 4.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_variables';

	/**
	 * The Constructor.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert variable to database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Check if variable name is provided, else skip.
		if ( ! isset( $args['variable_name'] ) ) {
			return false;
		}

		// Check if variable value is provided, else skip.
		if ( ! isset( $args['variable_value'] ) ) {
			return false;
		}

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'variable_name'        => esc_attr( $args['variable_name'] ),
				'variable_value'       => esc_attr( $args['variable_value'] ),
				'variable_description' => isset( $args['variable_description'] ) ? esc_attr( $args['variable_description'] ) : '',
				'variable_time'        => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update the variable in database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return array
	 */
	public function update( $args ) {
		global $wpdb;

		// Check if variable ID is provided, else skip.
		if ( isset( $args['variable_name'] ) ) {
			$update = $wpdb->update(
				$wpdb->prefix . $this->table_name,
				$args,
				array(
					'variable_name' => esc_attr( $args['variable_name'] ),
				)
			);

			return $update;
		}
	}

	/**
	 * Delete the variable from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return array
	 */
	public function delete( $args ) {
		global $wpdb;

		// Check if trigger application is provided, else skip.
		if ( isset( $args['variable_name'] ) ) {
			$update = $wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'variable_name' => esc_attr( $args['variable_name'] ),
				)
			);

			return $update;
		}
	}

	/**
	 * Get the variable from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return string
	 */
	public function get( $args ) {
		global $wpdb;

		// Check if variable ID is provided, else skip.
		if ( isset( $args['variable_name'] ) ) {
			// In case variable is wrapped within curly braces, remove them.
			$args['variable_name'] = str_replace( array( '{', '}' ), '', $args['variable_name'] );

			$variable = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `variable_name` = %s', $args['variable_name'] )
			);
		}

		return $variable;
	}

	/**
	 * Get all variables from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param int $offset The pagination offset.
	 * @return object
	 */
	public function get_all( $offset = 0 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` ORDER BY `ID` ASC LIMIT %d, %d', $offset, 1000 )
		);

		return $results;
	}

	/**
	 * Get the total variables count.
	 *
	 * @since 4.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_variables_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}
}
