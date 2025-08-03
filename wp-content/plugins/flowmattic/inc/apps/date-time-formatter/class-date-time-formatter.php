<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date/Time Formatter.
 *
 * @class FlowMattic_Date_Time_Formatter
 */
class FlowMattic_Date_Time_Formatter {
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
	 * @since 3.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Date/Time Formatter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'date_time_formatter',
			array(
				'name'         => esc_attr__( 'Date/Time Formatter', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/date-time-formatter/icon.svg',
				'instructions' => __( 'Format date and time.', 'flowmattic' ),
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
	 * @since 3.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-date-time-formatter', FLOWMATTIC_PLUGIN_URL . 'inc/apps/date-time-formatter/view-date-time-formatter.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 3.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'add_subtract_time'   => array(
				'title'       => esc_attr__( 'Add/Subtract Time', 'flowmattic' ),
				'description' => esc_attr__( 'Manipulate a date and/or time by adding/subtracting days, months, years, hours, minutes, seconds.', 'flowmattic' ),
			),
			'modify_current_date' => array(
				'title'       => esc_attr__( 'Modify Current Date', 'flowmattic' ),
				'description' => esc_attr__( 'Get the modified current date/time.', 'flowmattic' ),
			),
			'compare_dates'       => array(
				'title'       => esc_attr__( 'Compare Two Dates', 'flowmattic' ),
				'description' => esc_attr__( 'Calculate and get the duration between two dates.', 'flowmattic' ),
			),
			'modify_date_format'  => array(
				'title'       => esc_attr__( 'Modify Date Format', 'flowmattic' ),
				'description' => esc_attr__( 'Modify the given date format to another format.', 'flowmattic' ),
			),
			'calculate_age'       => array(
				'title'       => esc_attr__( 'Calculate Age', 'flowmattic' ),
				'description' => esc_attr__( 'Calculate the age based on the given date.', 'flowmattic' ),
			),
			'convert_timezone'    => array(
				'title'       => esc_attr__( 'Convert Timezone', 'flowmattic' ),
				'description' => esc_attr__( 'Convert the given date/time to another timezone.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step    = (array) $step;
		$action  = $step['action'];
		$fields  = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$task_id = isset( $step['task_history_id'] ) ? $step['task_history_id'] : $workflow_id;

		// CS.
		$capture_data;

		// Assign the step ID.
		$step_id = isset( $step['stepID'] ) ? $step['stepID'] : $task_id;

		switch ( $action ) {
			case 'add_subtract_time':
				$response = $this->add_subtract_time( $fields );
				break;

			case 'modify_current_date':
				$response = $this->modify_current_date( $fields );
				break;

			case 'compare_dates':
				$response = $this->compare_dates( $fields );
				break;

			case 'modify_date_format':
				$response = $this->modify_date_format( $fields );
				break;

			case 'calculate_age':
				$response = $this->calculate_age( $fields );
				break;

			case 'convert_timezone':
				$response = $this->convert_timezone( $fields );
				break;
		}

		return $response;
	}

	/**
	 * Add/Subtract Time.
	 *
	 * @access public
	 * @since 3.0
	 * @param array $fields Request data.
	 * @return string
	 */
	public function add_subtract_time( $fields ) {
		$input_date  = isset( $fields['input_date'] ) ? $fields['input_date'] : '';
		$expression  = isset( $fields['expression'] ) ? $fields['expression'] : '';
		$to_format   = isset( $fields['to_format'] ) ? $fields['to_format'] : '';
		$from_format = isset( $fields['from_format'] ) ? $fields['from_format'] : '';

		// Set the request body.
		$this->request_body = array(
			'input_date'  => $input_date,
			'expression'  => $expression,
			'to_format'   => $to_format,
			'from_format' => $from_format,
		);

		// Fix the date format by replacing / with -.
		$from_format = str_replace( '/', '-', $from_format );

		// Fix the date by replacing / with -.
		$input_date_original = str_replace( '/', '-', $input_date );

		// Check if input date is current.
		if ( '[current_date]' === $input_date_original ) {
			$input_date_original = date_i18n( 'Y-m-d H:i:s' );
			$from_format         = 'Y-m-d H:i:s';
		}

		// Get the date time format from the input date.
		$new_input_date = date_create_from_format( $from_format, $input_date_original );

		// If the date format is invalid, throw an error.
		if ( false === $new_input_date ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid From date format selected', 'flowmattic' ),
				)
			);
		}

		$input_time = date_format( $new_input_date, 'H:i:s' );
		$input_date = date_format( $new_input_date, 'Y-m-d H:i:s' );

		$original_date_timestamp = strtotime( $input_date_original );
		$original_date_time      = gmdate( 'H:i:s', $original_date_timestamp );

		$from_format = 'Y-m-d H:i:s';

		// Replace the time with 00:00:00, if input date has no time that matches to the $input_time.
		if ( $input_time !== $original_date_time ) {
			$input_date = str_replace( $input_time, $original_date_time, $input_date );
		}

		$timestamp = strtotime( $input_date );

		if ( '' !== $from_format ) {
			// Convert the input date to given format.
			$converted_date = date_create_from_format( $from_format, $input_date );

			// Get the date in the given format.
			$input_date = date_format( $converted_date, 'Y-m-d H:i:s' );

			// If the time format is invalid, throw an error.
			if ( false === $input_date ) {
				return wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid time format selected', 'flowmattic' ),
					)
				);
			}
		}

		// Convert the input date to timestamp.
		$timestamp = strtotime( $input_date );

		// Modify the timestamp with the expression to calculate the accurate date.
		$modified_timestamp = strtotime( $expression, $timestamp );

		// Get the date after modification.
		$date_output = date_i18n( $to_format, $modified_timestamp );

		return wp_json_encode(
			array(
				'status' => 'success',
				'output' => $date_output,
			)
		);
	}

	/**
	 * Get and modify current date.
	 *
	 * @access public
	 * @since 3.0
	 * @param array $fields Request data.
	 * @return array
	 */
	public function modify_current_date( $fields ) {
		$input_date      = isset( $fields['input_date'] ) ? $fields['input_date'] : '';
		$to_format       = isset( $fields['to_format'] ) ? $fields['to_format'] : '';
		$timezone        = isset( $fields['timezone'] ) ? $fields['timezone'] : '';
		$operation       = isset( $fields['operation'] ) && '' !== $fields['operation'] ? $fields['operation'] : 'add';
		$time_unit       = isset( $fields['time_unit'] ) && '' !== $fields['time_unit'] ? $fields['time_unit'] : 'minutes';
		$time_unit_value = isset( $fields['time_unit_value'] ) && '' !== $fields['time_unit_value'] ? $fields['time_unit_value'] : '0';

		// Set the request body.
		$this->request_body = array(
			'input_date'      => $input_date,
			'to_format'       => $to_format,
			'timezone'        => $timezone,
			'operation'       => $operation,
			'time_unit'       => $time_unit,
			'time_unit_value' => $time_unit_value,
		);

		// Get WP default timezone, if no timezone provided.
		$timezone = ( '' !== $timezone ) ? new DateTimeZone( $timezone ) : wp_timezone();

		// Get the date and time in the given format from the timzone.
		$date_output = wp_date( 'd-m-Y h:i:s A', time(), $timezone );

		// Do the operation, if needed.
		$expression = '+';
		if ( '' !== $operation ) {
			if ( 'subtract' === $operation ) {
				$expression = '-';
			}

			$expression .= $time_unit_value;
			$expression .= ' ' . $time_unit;

			// Convert the input date to timestamp.
			$timestamp = strtotime( $date_output );

			// Modify the timestamp with the expression to calculate the accurate date.
			$modified_timestamp = strtotime( $expression, $timestamp );

			// Get the date after modification.
			$date_output = date_i18n( $to_format, $modified_timestamp );
		}

		return wp_json_encode(
			array(
				'status' => 'success',
				'output' => $date_output,
			)
		);
	}

	/**
	 * Compare two dates.
	 *
	 * @access public
	 * @since 3.0
	 * @param array $fields Request data.
	 * @return array
	 */
	public function compare_dates( $fields ) {
		$start_date        = isset( $fields['start_date'] ) ? $fields['start_date'] : '';
		$end_date          = isset( $fields['end_date'] ) ? $fields['end_date'] : '';
		$start_date_format = isset( $fields['start_date_format'] ) ? $fields['start_date_format'] : '';
		$end_date_format   = isset( $fields['end_date_format'] ) ? $fields['end_date_format'] : '';

		// Check if input date is current.
		if ( '[current_date]' === $start_date ) {
			$start_date = date_i18n( $start_date_format );
		}

		// Set the request body.
		$this->request_body = array(
			'start_date'        => $start_date,
			'end_date'          => $end_date,
			'start_date_format' => $start_date_format,
			'end_date_format'   => $end_date_format,
		);

		$start_date_time = date_create_from_format( $start_date_format, $start_date );
		$end_date_time   = date_create_from_format( $end_date_format, $end_date );

		if ( ! $start_date_time ) {
			return wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Invalid date format for start date', 'flowmattic' ),
				)
			);
		}

		if ( ! $end_date_time ) {
			return wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Invalid date format for end date', 'flowmattic' ),
				)
			);
		}

		$swapped = false;
		$same    = false;

		if ( $start_date_time == $end_date_time ) {
			$same = true;
		} elseif ( $start_date_time > $end_date_time ) {
			$temp            = $start_date_time;
			$start_date_time = $end_date_time;
			$end_date_time   = $temp;
			$swapped         = true;
		}

		$interval = $start_date_time->diff( $end_date_time );

		$years   = 0;
		$months  = $interval->m;
		$days    = $interval->d;
		$hours   = $interval->h;
		$minutes = $interval->i;

		if ( 1 <= round( $interval->days / 365 ) ) {
			$years = round( $interval->days / 365 );
		}

		$difference = "{$years} years, {$months} months, {$days} days, {$hours} hours, {$minutes} minutes";

		$difference_array = array(
			'status'        => 'success',
			'difference'    => $difference,
			'years'         => $years,
			'months'        => $months,
			'days'          => $days,
			'hours'         => $hours,
			'minutes'       => $minutes,
			'dates_swapped' => $swapped,
			'same_dates'    => $same,
		);

		return wp_json_encode( $difference_array );
	}

	/**
	 * Modify date format.
	 *
	 * @access public
	 * @since 4.2.0
	 * @param array $fields Request data.
	 * @return array
	 */
	public function modify_date_format( $fields ) {
		$input_date  = isset( $fields['input_date'] ) ? $fields['input_date'] : '';
		$from_format = isset( $fields['from_format'] ) ? $fields['from_format'] : '';
		$to_format   = isset( $fields['to_format'] ) ? $fields['to_format'] : '';

		// Set the request body.
		$this->request_body = array(
			'input_date'  => $input_date,
			'from_format' => $from_format,
			'to_format'   => $to_format,
		);

		// Check if input date is current.
		if ( '[current_date]' === $input_date ) {
			$input_date = date_i18n( $from_format );
		}

		// Fix the date format by replacing / with -.
		$input_date = str_replace( '/', '-', $input_date );

		// Convert the input date to given format.
		$input_time = strtotime( $input_date );

		// Get the date in the given format.
		$date_output = date_i18n( $to_format, $input_time );

		return wp_json_encode(
			array(
				'status' => 'success',
				'output' => $date_output,
			)
		);
	}

	/**
	 * Calculate age.
	 *
	 * @access public
	 * @since 4.2.0
	 * @param array $fields Request data.
	 * @return array
	 */
	public function calculate_age( $fields ) {
		$birth_date        = isset( $fields['birth_date'] ) ? $fields['birth_date'] : '';
		$birth_date_format = isset( $fields['birth_date_format'] ) ? $fields['birth_date_format'] : 'd/m/Y';

		// Set the request body.
		$this->request_body = array(
			'birth_date'        => $birth_date,
			'birth_date_format' => $birth_date_format,
		);

		$birth_date_time = date_create_from_format( $birth_date_format, $birth_date );

		if ( ! $birth_date_time ) {
			return wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Invalid date format for birth date', 'flowmattic' ),
				)
			);
		}

		$now = new DateTime();
		$age = $now->diff( $birth_date_time );

		$age_array = array(
			'status' => 'success',
			'years'  => $age->y,
			'months' => $age->m,
			'days'   => $age->d,
			'hours'  => $age->h,
			'since'  => $age->format( '%y years, %m months, %d days, %h hours' ),
			'age'    => $age->y . ' years, ' . $age->m . ' months, ' . $age->d . ' days',
		);

		return wp_json_encode( $age_array );
	}

	/**
	 * Convert timezone.
	 *
	 * @access public
	 * @since 5.0
	 * @param array $fields Request data.
	 * @return array
	 */
	public function convert_timezone( $fields ) {
		$input_date = isset( $fields['input_date'] ) ? $fields['input_date'] : '';
		$from_tz    = isset( $fields['from_timezone'] ) ? $fields['from_timezone'] : '';
		$to_tz      = isset( $fields['timezone'] ) ? $fields['timezone'] : '';
		$format     = isset( $fields['to_format'] ) ? $fields['to_format'] : '';
		$from_format = isset( $fields['from_format'] ) ? $fields['from_format'] : 'Y-m-d H:i:s';

		// Set the request body.
		$this->request_body = array(
			'input_date' => $input_date,
			'from_tz'    => $from_tz,
			'to_tz'      => $to_tz,
			'format'     => $format,
		);

		// Fix the date format by replacing / with -.
		$from_format = str_replace( '/', '-', $from_format );

		// Check if input date is current.
		if ( '[current_date]' === $input_date ) {
			$input_date = date_i18n( $from_format );
		}

		// Create a DateTime object from the given date string and source timezone
		try {
			$date = new DateTime(
				$input_date,
				new DateTimeZone( $from_tz )
			);
		} catch ( Exception $e ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Date parsing error', 'flowmattic' ),
					'error'   => $e->getMessage(),
				)
			);
		}

		// Set the target timezone
		$date->setTimezone( new DateTimeZone( $to_tz ) );

		// Get the date in the given format.
		$date_output = $date->format( $format );

		return wp_json_encode(
			array(
				'status'        => 'success',
				'output'        => $date_output,
				'from_timezone' => $from_tz,
				'to_timezone'   => $to_tz,
			)
		);
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 3.0
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

new FlowMattic_Date_Time_Formatter();
