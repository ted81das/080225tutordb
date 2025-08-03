<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Lookup_Table {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 5.1.1
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Lookup Table.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'lookup_table',
			array(
				'name'         => esc_attr__( 'Lookup Table', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/lookup-table/icon.svg',
				'instructions' => '',
				'actions'     => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-lookup-table', FLOWMATTIC_PLUGIN_URL . 'inc/apps/lookup-table/view-lookup-table.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return array
	 */
	public function get_actions() {
		return array(
			'lookup_table' => array(
				'title'       => esc_attr__( 'Lookup Table', 'flowmattic' ),
				'description' => esc_attr__( 'Lookup a value in a custom data table and return the corresponding value.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 5.1.1
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		// CS.
		$capture_data;

		$step   = (array) $step;
		$action = $step['action'];
		$fields = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );

		$lookup_table = isset( $fields['lookup_table_parameters'] ) ? $fields['lookup_table_parameters'] : array();

		// Set the lookup table to the fields.
		$fields['lookup_table'] = $lookup_table;

		switch ( $action ) {
			case 'lookup_table':
				$response = $this->lookup_table( $fields );
				break;

			default:
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid action.', 'flowmattic' ),
				);
				break;
		}

		// Return the response.
		return wp_json_encode( $response );
	}

	/**
	 * Lookup table.
	 *
	 * @access public
	 * @since 5.1.1
	 * @param array $fields Fields to lookup.
	 * @return array
	 */
	public function lookup_table( $fields ) {
		$lookup_key = isset( $fields['lookup_key'] ) ? $fields['lookup_key'] : '';
		$fallback_value = isset( $fields['fallback_value'] ) ? $fields['fallback_value'] : '';
		$lookup_table = isset( $fields['lookup_table'] ) ? $fields['lookup_table'] : array();
		
		if ( '' === $lookup_key ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Lookup key is required.', 'flowmattic' ),
			);
		}

		if ( empty( $lookup_table ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Lookup table is empty.', 'flowmattic' ),
			);
		}

		$fallback_used = false;

		// Check if the lookup key exists in the table.
		if ( array_key_exists( $lookup_key, $lookup_table ) ) {
			$value = $lookup_table[ $lookup_key ];
		} else {
			$value         = $fallback_value;
			$fallback_used = true;
		}

		// Set the response.
		$response = array(
			'status'        => 'success',
			'lookup_key'    => $lookup_key,
			'lookup_value'  => $value,
			'fallback_used' => $fallback_used,
		);

		// Set the request body.
		$this->request_body = array(
			'lookup_key'     => $lookup_key,
			'lookup_table'   => $lookup_table,
			'fallback_value' => $fallback_value,
		);

		return $response;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 5.1.1
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

		// Perform the action.
		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Lookup_Table();
