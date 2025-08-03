<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlowMattic Maths class.
 *
 * @since 2.1.0
 */
class FlowMattic_Maths {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 4.3.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for maths.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'maths',
			array(
				'name'         => esc_attr__( 'Maths by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/maths/icon.svg',
				'instructions' => __( 'Execute math functions in your workflow.', 'flowmattic' ),
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
	 * @since 2.1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-maths', FLOWMATTIC_PLUGIN_URL . 'inc/apps/maths/view-maths.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'execute_math' => array(
				'title'       => esc_attr__( 'Execute Math Equation', 'flowmattic' ),
				'description' => esc_attr__( 'Provide your math equation to execute and get results.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step          = (array) $step;
		$action        = $step['action'];
		$fields        = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$math_equation = isset( $fields['math_equation'] ) ? $fields['math_equation'] : '';

		if ( '' === $math_equation ) {
			// Check if math equation is available in the actionAppArgs.
			$math_equation = isset( $step['actionAppArgs']['math_equation'] ) ? $step['actionAppArgs']['math_equation'] : '';
		}

		// Set the request body.
		$this->request_body = array(
			'math_equation' => $math_equation,
		);

		if ( '' === $math_equation ) {
			$response = array(
				'status' => 'error',
				'result' => esc_html__( 'Invalid math equation', 'flowmattic' ),
			);
		} else {
			// Execute the math equation.
			try {
				// Replace hiphan with dash.
				$math_equation = str_replace( '–', '-', $math_equation );
				$math_equation = stripslashes( $math_equation );

				// Execute the equation.
				$result = eval( 'return ' . $math_equation . ';' ); // @codingStandardsIgnoreLine

				$response = array(
					'status' => 'success',
					'result' => $result,
				);
			} catch ( Error $e ) {
				$response = array(
					'status'  => 'error',
					'result'  => esc_html__( 'Something went wrong. Please check your math equation.', 'flowmattic' ),
					'message' => $e->getMessage(),
				);
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 2.1.0
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
	 * Return the request data.
	 *
	 * @access public
	 * @since 4.3.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Maths();
