<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_MCP_Trigger {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 5.2.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for plugin actions.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'mcp_trigger',
			array(
				'name'         => esc_attr__( 'FlowMattic MCP', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/mcp-trigger/icon.svg',
				'instructions' => __( 'Trigger this workflow when a specific MCP tool is executed.', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'trigger,action',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-mcp-trigger', FLOWMATTIC_PLUGIN_URL . 'inc/apps/mcp-trigger/view-mcp-trigger.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'mcp_trigger' => array(
				'title'       => esc_attr__( 'Workflow MCP Tool Triggered', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when this workflow specific MCP tool is executed.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'mcp_tool_response' => array(
				'title'       => esc_attr__( 'MCP Tool Response', 'flowmattic' ),
				'description' => esc_attr__( 'This action is used to handle the response to the MCP tool execution.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 4.0
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

		switch ( $action ) {
			case 'mcp_tool_response':
				// Handle MCP tool response.
				$response = $this->handle_mcp_tool_response( $fields );
				break;
			default:
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid action specified.', 'flowmattic' ),
				);
				break;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Handle MCP tool response.
	 *
	 * @access private
	 * @since 5.2.0
	 * @param array $fields Fields from the step.
	 * @return array
	 */
	private function handle_mcp_tool_response( $fields ) {
		// Get the response parameters.
		$response_parameters = ( isset( $fields['mcp_response_parameters'] ) ) ? $fields['mcp_response_parameters'] : array();

		// Set the request body.
		$this->request_body = $response_parameters;

		// Prepare the response.
		if ( empty( $this->request_body ) ) {
			return array(
				'status' => 'error',
				'error'  => esc_html__( 'No response parameters provided.', 'flowmattic' ),
			);
		}

		return array(
			'status'   => 'success',
			'response' => $this->request_body,
		);
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 5.2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event          = $event_data['event'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $event_data['actionAppArgs'] ) ? $event_data['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$event_data['fields'] = $fields;

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 5.2.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_MCP_Trigger();
