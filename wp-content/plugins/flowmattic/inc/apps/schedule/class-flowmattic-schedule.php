<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Schedule {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		flowmattic_add_application(
			'schedule',
			array(
				'name'         => esc_attr__( 'Schedule by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/schedule/icon.svg',
				'instructions' => 'Schedule the workflow every hour, day, week or a custom interval.',
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		add_action( 'flowmattic_workflow_cron', array( $this, 'run_scheduled_workflow' ), 10, 4 );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'minutes' => array(
				'title'    => esc_attr__( 'Every X Minutes', 'flowmattic' ),
				'triggers' => array(
					'weekend_trigger' => esc_attr__( 'Trigger on Weekends?', 'flowmattic' ),
				),
			),
			'hour'    => array(
				'title'    => esc_attr__( 'Every Hour', 'flowmattic' ),
				'triggers' => array(
					'weekend_trigger' => esc_attr__( 'Trigger on Weekends?', 'flowmattic' ),
				),
			),
			'day'     => array(
				'title'    => esc_attr__( 'Every Day', 'flowmattic' ),
				'triggers' => array(
					'weekend_trigger' => esc_attr__( 'Trigger on Weekends?', 'flowmattic' ),
					'day_time'        => esc_attr__( 'Time of the day', 'flowmattic' ),
				),
			),
			'week'    => array(
				'title'    => esc_attr__( 'Every Week', 'flowmattic' ),
				'triggers' => array(
					'week_day' => esc_attr__( 'Day of the Week', 'flowmattic' ),
					'day_time' => esc_attr__( 'Time of the day', 'flowmattic' ),
				),
			),
			'month'   => array(
				'title'    => esc_attr__( 'Every Month', 'flowmattic' ),
				'triggers' => array(
					'month_day' => esc_attr__( 'Day of the month', 'flowmattic' ),
					'day_time'  => esc_attr__( 'Time of the day', 'flowmattic' ),
				),
			),
		);
	}

	/**
	 * Run the scheduled workflow.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id     Workflow ID of the workflow being executed.
	 * @param bool   $weekend_trigger Flag to run the workflow on weekends.
	 * @param bool   $week_day        Flag to run the workflow on week days.
	 * @param bool   $month_day       Flag to run the workflow on specific month day.
	 * @return bool
	 */
	public function run_scheduled_workflow( $workflow_id, $weekend_trigger = true, $week_day = false, $month_day = false ) {
		// Check if day is trigger for monthly triggers.
		if ( $month_day ) {
			$day = date_i18n( 'd', time() );

			if ( (int) $month_day !== (int) $day ) {
				return false;
			}
		}

		// Check if week day is trigger.
		if ( $week_day ) {
			$date = date_i18n( 'l', time() );
			$day  = strtolower( $date );

			if ( $week_day !== $day ) {
				return false;
			}
		}

		// Check if workflow can be executed on weekends.
		if ( 'false' === $weekend_trigger || 'no' === $weekend_trigger ) {
			$date = date_i18n( 'l', time() );
			$day  = strtolower( $date );

			if ( 'saturday' === $day || 'sunday' === $day ) {
				return false;
			}
		}

		$flowmattic_workflow = new FlowMattic_Workflow();
		$flowmattic_workflow->run( $workflow_id, array() );
	}
}

new FlowMattic_Schedule();
