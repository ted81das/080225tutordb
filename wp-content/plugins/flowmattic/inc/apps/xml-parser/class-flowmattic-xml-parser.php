<?php
/**
 * Application Name: XML Parser
 * Description: Add XML Parser integration to FlowMattic.
 * Version: 4.2.0
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
 * XML Parser class.
 */
class FlowMattic_Xml_Parser {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 4.2.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for xml-parser.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'xml_parser',
			array(
				'name'         => esc_attr__( 'XML Parser by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/xml-parser/icon.svg',
				'instructions' => __( 'Parse XML file in your workflow.', 'flowmattic' ),
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
	 * @since 4.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-xml-parser', FLOWMATTIC_PLUGIN_URL . 'inc/apps/xml-parser/view-xml-parser.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'parse_xml_file' => array(
				'title'       => esc_attr__( 'Parse XML File', 'flowmattic' ),
				'description' => esc_attr__( 'Parse any XML file URL into JSON data.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 4.2.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step     = (array) $step;
		$action   = $step['action'];
		$fields   = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$xml_file = ( isset( $fields['xml_file_url'] ) && '' !== trim( $fields['xml_file_url'] ) ) ? $fields['xml_file_url'] : '';

		// CS.
		$capture_data;

		// Set the request body.
		$this->request_body = $fields;

		if ( '' === $xml_file ) {
			$response = array(
				'status' => 'error',
				'result' => esc_html__( 'CSV File URL is required.', 'flowmattic' ),
			);
		} else {
			// Set the file to be non-https.
			$xml_file = str_replace( 'https://', 'http://', $xml_file );
			$xml_data = simplexml_load_file( $xml_file, 'SimpleXMLElement', LIBXML_NOCDATA );

			// Convert to JSON.
			$xml_data = wp_json_encode( $xml_data );

			// Replace @attributes with attributes.
			$xml_data = preg_replace( '/@attributes/', 'attributes', $xml_data );

			// Convert JSON to array.
			$xml_data = json_decode( $xml_data, true );

			$response = array(
				'status' => 'success',
				'result' => is_array( $xml_data ) ? wp_json_encode( $xml_data ) : $xml_data,
			);

			// Simplify the array.
			foreach ( $xml_data as $key => $value ) {
				if ( is_array( $value ) ) {
					$response = flowmattic_recursive_array( $response, $key, $value );
				} else {
					$response[ $key ] = $value;
				}
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 4.2.0
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
	 * @since 4.2.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Xml_Parser();
