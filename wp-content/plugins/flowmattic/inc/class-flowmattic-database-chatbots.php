<?php
/**
 * Handle database queries for chatbots.
 *
 * @package flowmattic
 * @since 4.0
 */

/**
 * Handle database queries for chatbots.
 *
 * @since 4.0
 */
class FlowMattic_Database_Chatbots {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 4.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_chatbot';

	/**
	 * The Constructor.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insert chatbot to database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'chatbot_id'       => esc_attr( $args['chatbot_id'] ),
				'chatbot_name'     => esc_attr( $args['chatbot_name'] ),
				'chatbot_data'     => $args['chatbot_data'],
				'chatbot_settings' => $args['chatbot_settings'],
				'chatbot_actions'  => $args['chatbot_actions'],
				'chatbot_styles'   => $args['chatbot_styles'],
				'chatbot_time'     => date_i18n( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Update the chatbot in database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return array
	 */
	public function update( $args ) {
		global $wpdb;

		// Check if chatbot ID is provided, else skip.
		if ( isset( $args['chatbot_id'] ) ) {
			$update = $wpdb->update(
				$wpdb->prefix . $this->table_name,
				$args,
				array(
					'chatbot_id' => esc_attr( $args['chatbot_id'] ),
				)
			);

			return $update;
		}
	}

	/**
	 * Delete the chatbot from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function delete( $args ) {
		global $wpdb;

		// Check if trigger application is provided, else skip.
		if ( isset( $args['chatbot_id'] ) ) {
			$wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'chatbot_id' => esc_attr( $args['chatbot_id'] ),
				)
			);
		}
	}

	/**
	 * Delete the chatbot from database before the interval.
	 *
	 * @since 4.0
	 * @access public
	 * @param string $interval The interval in days.
	 * @return void
	 */
	public function delete_before( $interval ) {
		global $wpdb;

		// Check if interval is provided, else skip.
		if ( '' !== $interval ) {
			$wpdb->get_results(
				$wpdb->prepare( 'DELETE FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `chatbot_time` < DATE_ADD( NOW(), INTERVAL -%d HOUR )', ( $interval * 24 ) )
			);
		}
	}

	/**
	 * Get the chatbot from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function get( $args ) {
	}

	/**
	 * Get single chatbot for the given chatbot id from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $chatbot_id The application name.
	 * @return array Workflow data from database.
	 */
	public function get_chatbot_by_id( $chatbot_id ) {
		global $wpdb;

		$chatbot = array();

		// Check if chatbot ID is provided, else skip.
		if ( '' !== $chatbot_id ) {
			$chatbot = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `chatbot_id` LIKE %s', '%' . $chatbot_id . '%' )
			);
		}

		return $chatbot;
	}

	/**
	 * Get all chatbots from database.
	 *
	 * @since 4.0
	 * @access public
	 * @param int $offset The pagination offset.
	 * @return object
	 */
	public function get_all( $offset = 0 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` ORDER BY `ID` DESC LIMIT %d, %d', $offset, 12 )
		);

		return $results;
	}

	/**
	 * Get the total chatbots count.
	 *
	 * @since 4.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_chatbots_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}
}
