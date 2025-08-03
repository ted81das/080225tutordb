<?php
/**
 * Application Name: FlowMattic Delay
 * Description: Add Delay module to FlowMattic.
 * Version: 1.0
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
 * Delay module integration class.
 *
 * @since 1.0
 */
class FlowMattic_Delay {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for filter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'delay',
			array(
				'name'         => esc_attr__( 'Delay by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/delay/icon.svg',
				'instructions' => 'Pause actions for certain amount of time.',
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);

		add_action( 'flowmattic_delay_workflow_step', array( $this, 'run_delayed_workflow' ), 10, 3 );
		add_action( 'flowmattic_delay_workflow_route', array( $this, 'run_delayed_router_step' ), 10, 4 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-delay', FLOWMATTIC_PLUGIN_URL . 'inc/apps/delay/view-delay.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'delay_for'   => array(
				'title'       => esc_attr__( 'Delay For', 'flowmattic' ),
				'description' => esc_attr__( 'Wait for a certain amount of time before executing the action step', 'flowmattic' ),
			),
			'delay_until' => array(
				'title'       => esc_attr__( 'Delay Until', 'flowmattic' ),
				'description' => esc_attr__( 'Wait to execute the action step until the set date or time', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the delayed workflow.
	 *
	 * @access public
	 * @since 1.0
	 * @param bool   $task_history_id Flag to run the workflow on weekends.
	 * @param bool   $next_step_id    Flag to run the workflow on week days.
	 * @param string $workflow_id     Workflow ID of the workflow being executed.
	 * @return void
	 */
	public function run_delayed_workflow( $task_history_id, $next_step_id, $workflow_id ) {

		$delay_data = array(
			'task_history_id' => $task_history_id,
			'step_id'         => $next_step_id,
			'workflow_id'     => $workflow_id,
		);

		$flowmattic_workflow = new FlowMattic_Workflow();
		$flowmattic_workflow->run( $workflow_id, array(), $delay_data );
	}

	/**
	 * Run the delayed router step.
	 *
	 * @access public
	 * @since 2.0
	 * @param bool   $task_history_id Flag to run the workflow on weekends.
	 * @param bool   $next_step_id    Flag to run the workflow on week days.
	 * @param string $workflow_id     Workflow ID of the workflow being executed.
	 * @param array  $route_context   Route context data.
	 * @return void
	 */
	public function run_delayed_router_step( $task_history_id, $next_step_id, $workflow_id, $route_context ) {

		$delay_data = array(
			'task_history_id' => $task_history_id,
			'step_id'         => $next_step_id,
			'workflow_id'     => $workflow_id,
			'route_context'   => $route_context,
		);

		$flowmattic_workflow = new FlowMattic_Workflow();
		$flowmattic_workflow->run( $workflow_id, array(), $delay_data );
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event    = isset( $event_data['event'] ) ? $event_data['event'] : '';
		$fields   = $event_data['fields'];
		$response = '';

		if ( 'delay_for' === $event ) {
			$delay_unit  = $fields['delayUnit'];
			$delay_value = $fields['delayValue'];
			$response    = sprintf( __( 'Execution delayed for %1$s %2$s', 'flowmattic' ), $delay_value, $delay_unit );
		} elseif ( 'delay_until' === $event ) {
			$delay_time = $fields['delayTime'];
			$response   = sprintf( __( 'Execution paused until %s', 'flowmattic' ), $delay_time );

			if ( time() >= strtotime( $delay_time ) ) {
				$response = sprintf( __( 'Delay execution time already passed on %s. Execution of the next step will be attempted in a few moments.', 'flowmattic' ), $delay_time );
			}
		}

		return wp_json_encode(
			array(
				'status'  => esc_attr__( 'Success', 'flowmattic' ),
				'message' => $response,
			)
		);
	}
}

new FlowMattic_Delay();
