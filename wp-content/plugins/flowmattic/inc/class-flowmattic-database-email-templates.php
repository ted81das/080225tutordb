<?php
/**
 * Handle database queries for email templates.
 *
 * @package flowmattic
 * @since 5.1.0
 */

/**
 * Handle database queries for email templates.
 *
 * @since 5.1.0
 */
class FlowMattic_Database_Email_Templates {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 5.1.0
	 * @var string
	 */
	protected $table_name = 'flowmattic_email_templates';

	/**
	 * The Constructor.
	 *
	 * @since 5.1.0
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Insertemail template to database.
	 *
	 * @since 5.1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Check if email template name is provided, else skip.
		if ( ! isset( $args['email_template_name'] ) ) {
			return false;
		}

		// Check if email template json is provided, else skip.
		if ( ! isset( $args['email_template_json'] ) ) {
			return false;
		}

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'email_template_id'           => esc_attr( $args['email_template_id'] ),
				'email_template_name'         => esc_attr( $args['email_template_name'] ),
				'email_template_json'         => $args['email_template_json'],
				'email_template_html'         => $args['email_template_html'],
				'email_template_dynamic_data' => $args['email_template_dynamic_data'],
				'email_template_created'      => $args['email_template_created'],
				'email_template_updated'      => $args['email_template_updated'],
				'email_template_meta'         => $args['email_template_meta'],
				'email_template_sent_count'   => 0,
			)
		);
	}

	/**
	 * Update the email template in database.
	 *
	 * @since 5.1.0
	 * @access public
	 * @param array $args The arguments.
	 * @param int   $id The template ID.
	 * @return array
	 */
	public function update( $args, $id ) {
		global $wpdb;

		// Check if email template ID is provided, else skip.
		if ( '' !== $id ) {
			$update = $wpdb->update(
				$wpdb->prefix . $this->table_name,
				$args,
				array(
					'email_template_id' => $id,
				)
			);

			return $update;
		}
	}

	/**
	 * Clone the email template in database.
	 *
	 * @since 5.1.0
	 * @access public
	 * @param int $id The template ID.
	 * @return array
	 */
	public function clone( $id ) {
		global $wpdb;

		// Check if email template ID is provided, else skip.
		if ( '' !== $id ) {
			$template = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `email_template_id` = %s', $id )
			);

			$new_template_id = wp_generate_uuid4();

			// Clone the email template.
			$wpdb->insert(
				$wpdb->prefix . $this->table_name,
				array(
					'email_template_id'           => $new_template_id,
					'email_template_name'         => $template->email_template_name . ' - ' . esc_attr__( 'Copy', 'flowmattic' ),
					'email_template_json'         => $template->email_template_json,
					'email_template_html'         => $template->email_template_html,
					'email_template_dynamic_data' => $template->email_template_dynamic_data,
					'email_template_created'      => current_time( 'mysql' ),
					'email_template_updated'      => current_time( 'mysql' ),
					'email_template_meta'         => $template->email_template_meta,
					'email_template_sent_count'   => 0,
				)
			);

			return $new_template_id;
		}
	}

	/**
	 * Delete the email template from database.
	 *
	 * @since 5.1.0
	 * @access public
	 * @param string $id The template ID.
	 * @return array
	 */
	public function delete( $id ) {
		global $wpdb;

		// Check if id is provided, else skip.
		if ( '' !== $id ) {
			$update = $wpdb->delete(
				$wpdb->prefix . $this->table_name,
				array(
					'email_template_id' => $id,
				)
			);

			return $update;
		}
	}

	/**
	 * Get the email template from database.
	 *
	 * @since 5.1.0
	 * @access public
	 * @param array $args The arguments.
	 * @return string
	 */
	public function get( $args ) {
		global $wpdb;

		// Check if email template ID is provided, else skip.
		if ( isset( $args['email_template_id'] ) ) {
			// In caseemail template is wrapped within curly braces, remove them.
			$args['email_template_id'] = str_replace( array( '{', '}' ), '', $args['email_template_id'] );

			$email_templates = $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `email_template_id` = %s', $args['email_template_id'] )
			);
		}

		return $email_templates;
	}

	/**
	 * Get all email templates from database.
	 *
	 * @since 5.1.0
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
	 * Get the total email templates count.
	 *
	 * @since 5.1.0
	 * @access public
	 * @return array Workflow data from database.
	 */
	public function get_email_templates_count() {
		global $wpdb;

		// Query to count results.
		$query = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name;

		$total_query = 'SELECT COUNT(1) FROM (' . $query . ') AS combined_table';
		$total       = $wpdb->get_var( $total_query );

		return $total;
	}
}
