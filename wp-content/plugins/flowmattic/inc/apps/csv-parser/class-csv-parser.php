<?php
/**
 * Application Name: CSV Parser
 * Description: Add CSV Parser integration to FlowMattic.
 * Version: 3.2.0
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
 * CSV Parser class.
 */
class FlowMattic_Csv_Parser {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 3.2.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for csv-parser.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'csv_parser',
			array(
				'name'         => esc_attr__( 'CSV Parser by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/csv-parser/icon.svg',
				'instructions' => __( 'Parse CSV file in your workflow.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-csv-parser', FLOWMATTIC_PLUGIN_URL . 'inc/apps/csv-parser/view-csv-parser.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'parse_csv_file' => array(
				'title'       => esc_attr__( 'Parse CSV File', 'flowmattic' ),
				'description' => esc_attr__( 'Parse any CSV file URL into JSON data.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step        = (array) $step;
		$action      = $step['action'];
		$fields      = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$csv_file    = ( isset( $fields['csv_file_url'] ) && '' !== trim( $fields['csv_file_url'] ) ) ? $fields['csv_file_url'] : '';
		$has_headers = ( isset( $fields['has_headers'] ) && 'yes' === trim( $fields['has_headers'] ) ) ? true : false;

		// Set the request body.
		$this->request_body = $fields;

		if ( '' === $csv_file ) {
			$response = array(
				'status' => 'error',
				'result' => esc_html__( 'CSV File URL is required.', 'flowmattic' ),
			);
		} else {
			$response = flowmattic_parse_csv_file( $csv_file, $has_headers );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event       = $event_data['event'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data sent to API endpoint.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Csv_Parser();
