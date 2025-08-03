<?php
/**
 * Handle Variables in FlowMattic.
 *
 * @package flowmattic
 * @since 4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Variables in FlowMattic.
 *
 * @since 4.0
 * @access public
 */
class FlowMattic_Variables {
	/**
	 * System variables.
	 *
	 * @var array
	 */
	public $system_vars;

	/**
	 * The Constructor.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function __construct() {
		// Set the system variables.
		$this->system_vars = $this->get_system_vars();

		// Register admin ajax to create variable.
		add_action( 'wp_ajax_flowmattic_create_variable', array( $this, 'create_variable' ) );

		// Register admin ajax to delete variable.
		add_action( 'wp_ajax_flowmattic_delete_variable', array( $this, 'delete_variable' ) );

		// Add the variables to the workflow.
		add_filter( 'flowmattic_workflow_variables', array( $this, 'add_variables' ) );
	}

	/**
	 * Get the system variables.
	 *
	 * @since 4.0
	 * @access public
	 * @return array The system variables.
	 */
	public function get_system_vars() {
		$system_vars = array(
			'iso_8601_utc'          => array(
				'variable_value'       => gmdate( 'c' ),
				'variable_description' => esc_attr__( 'The current date and time in ISO 8601 format in UTC timezone.', 'flowmattic' ),
			),
			'iso_8601_local'        => array(
				'variable_value'       => date_i18n( 'c' ),
				'variable_description' => esc_attr__( 'The current date and time in ISO 8601 format in local timezone.', 'flowmattic' ),
			),
			'utc_cur_date_time_24'  => array(
				'variable_value'       => gmdate( 'Y-m-d H:i:s' ),
				'variable_description' => esc_attr__( 'The current date and time in UTC 24-hour format.', 'flowmattic' ),
			),
			'utc_cur_date_time_12'  => array(
				'variable_value'       => gmdate( 'Y-m-d h:i:s A' ),
				'variable_description' => esc_attr__( 'The current date and time in UTC 12-hour format.', 'flowmattic' ),
			),
			'utc_cur_date'          => array(
				'variable_value'       => gmdate( 'Y-m-d' ),
				'variable_description' => esc_attr__( 'The current date in UTC.', 'flowmattic' ),
			),
			'utc_cur_time_24'       => array(
				'variable_value'       => gmdate( 'H:i:s' ),
				'variable_description' => esc_attr__( 'The current time in UTC 24-hour format.', 'flowmattic' ),
			),
			'utc_cur_time_12'       => array(
				'variable_value'       => gmdate( 'h:i:s A' ),
				'variable_description' => esc_attr__( 'The current time in UTC 12-hour format.', 'flowmattic' ),
			),
			'utc_cur_timestamp'     => array(
				'variable_value'       => gmdate( 'U' ),
				'variable_description' => esc_attr__( 'The current timestamp in UTC.', 'flowmattic' ),
			),
			'local_cur_date_time24' => array(
				'variable_value'       => date_i18n( 'Y-m-d H:i:s' ),
				'variable_description' => esc_attr__( 'The current date and time in local time 24-hour format.', 'flowmattic' ),
			),
			'local_cur_date_time12' => array(
				'variable_value'       => date_i18n( 'Y-m-d h:i:s A' ),
				'variable_description' => esc_attr__( 'The current date and time in local time 12-hour format.', 'flowmattic' ),
			),
			'local_cur_date'        => array(
				'variable_value'       => date_i18n( 'Y-m-d' ),
				'variable_description' => esc_attr__( 'The current date in local time.', 'flowmattic' ),
			),
			'local_cur_time_24'     => array(
				'variable_value'       => date_i18n( 'H:i:s' ),
				'variable_description' => esc_attr__( 'The current time in local time 24-hour format.', 'flowmattic' ),
			),
			'local_cur_time_12'     => array(
				'variable_value'       => date_i18n( 'h:i:s A' ),
				'variable_description' => esc_attr__( 'The current time in local time 12-hour format.', 'flowmattic' ),
			),
			'local_cur_timestamp'   => array(
				'variable_value'       => date_i18n( 'U' ),
				'variable_description' => esc_attr__( 'The current timestamp in local time.', 'flowmattic' ),
			),
			'cur_year'              => array(
				'variable_value'       => date_i18n( 'Y' ),
				'variable_description' => esc_attr__( 'The current year in local time.', 'flowmattic' ),
			),
			'cur_month'             => array(
				'variable_value'       => date_i18n( 'm' ),
				'variable_description' => esc_attr__( 'The current month in local time.', 'flowmattic' ),
			),
			'cur_day'               => array(
				'variable_value'       => date_i18n( 'd' ),
				'variable_description' => esc_attr__( 'The current day in local time.', 'flowmattic' ),
			),
			'blank'                 => array(
				'variable_value'       => '',
				'variable_description' => esc_attr__( 'Prints blank value', 'flowmattic' ),
			),
			'space'                 => array(
				'variable_value'       => ' ',
				'variable_description' => esc_attr__( 'Inserts single space', 'flowmattic' ),
			),
			'line_break'            => array(
				'variable_value'       => '\n',
				'variable_description' => esc_attr__( 'Inserts line break', 'flowmattic' ),
			),
			'boolean:true'          => array(
				'variable_value'       => 'true',
				'variable_description' => esc_attr__( 'Inserts boolean true value. Accepted values after colon - true, yes, 1', 'flowmattic' ),
			),
			'boolean:false'         => array(
				'variable_value'       => 'false',
				'variable_description' => esc_attr__( 'Inserts boolean false value. Accepted values after colon - false, no, 0', 'flowmattic' ),
			),
			'random_number:100'     => array(
				'variable_value'       => wp_rand( 1, 100 ),
				'variable_description' => esc_attr__( 'Inserts random number between 1 and 100. Accept any number in place of 100', 'flowmattic' ),
			),
			'random_string:10'      => array(
				'variable_value'       => flowmattic_random_string( 10 ),
				'variable_description' => esc_attr__( 'Inserts random string of 10 characters. Accept any number between 2 and 100.', 'flowmattic' ),
			),
		);

		return $system_vars;
	}

	/**
	 * Get the custom variables.
	 *
	 * @since 4.0
	 * @access public
	 * @return array The custom variables.
	 */
	public function get_custom_vars() {
		// Get the variables data from database.
		$custom_vars = wp_flowmattic()->variables_db->get_all();

		// If not empty, loop through the variables and set the var name as key.
		$updated_custom_vars = array();

		if ( ! empty( $custom_vars ) ) {
			foreach ( $custom_vars as $custom_var ) {
				$updated_custom_vars[ $custom_var->variable_name ] = $custom_var;
			}
		}

		return $updated_custom_vars;
	}

	/**
	 * Get the variables.
	 *
	 * @since 4.0
	 * @access public
	 * @return array The variables.
	 */
	public function get_vars() {
		// Get the system variables.
		$system_vars = $this->get_system_vars();

		// Get the custom variables.
		$custom_vars = $this->get_custom_vars();

		// Merge the variables.
		$vars = array_merge( $system_vars, $custom_vars );

		return $vars;
	}

	/**
	 * Get the variable value.
	 *
	 * @since 4.0
	 * @access public
	 * @param string $variable_name The variable name.
	 * @return string The variable value.
	 */
	public function get_var_value( $variable_name ) {
		// Get the variables.
		$vars = (array) $this->get_vars();

		// Make sure the variable name is not wrapped in curly braces.
		$variable_name = str_replace( array( '{', '}' ), '', $variable_name );

		// Check if variable is the array or object.
		if ( isset( $vars[ $variable_name ] ) && is_array( $vars[ $variable_name ] ) ) {
			// Get the variable value. If not set, return variable name.
			$variable_value = isset( $vars[ $variable_name ]['variable_value'] ) ? $vars[ $variable_name ]['variable_value'] : $variable_name;
		} else {
			// Get the variable value. If not set, return variable name.
			$variable_value = isset( $vars[ $variable_name ] ) && is_object( $vars[ $variable_name ] ) ? $vars[ $variable_name ]->variable_value : $variable_name;
		}

		return $variable_value;
	}

	/**
	 * Find and replace variables with their values in a given string
	 *
	 * @since 4.0
	 * @access public
	 * @param array $data The data.
	 * @return array The data with variables replaced with their values.
	 */
	public function find_and_replace( $data ) {
		// Get the variables.
		$vars = $this->get_vars();

		// Check if there are any variables in the data using a pattern.
		$pattern = '/\{{([^\}]+)\}}/';

		// Convert the data array to string.
		$dummy_data = is_array( $data ) ? wp_json_encode( $data ) : $data;

		// Convert the data string to array for replacing variables.
		$dummy_array = is_array( $data ) ? $data : json_decode( $dummy_data, true );

		// Find all the variables in the data.
		preg_match_all( $pattern, $dummy_data, $matches );

		// If there are no variables, return the data.
		if ( empty( $matches[1] ) ) {
			return $data;
		}

		// Loop through the variables.
		foreach ( $matches[1] as $variable_name ) {
			// Check if the variable name has colon in it.
			if ( strpos( $variable_name, ':' ) ) {
				// Get the variable name and value.
				list( $variable_kind, $dynamic_part ) = explode( ':', $variable_name );

				// Check if the variable kind is boolean.
				if ( 'boolean' === $variable_kind ) {
					// Check if the dynamic part is true, yes or 1.
					if ( in_array( $dynamic_part, array( 'true', 'yes', '1' ), true ) ) {
						// Set the variable value to true.
						$variable_value = 'true';
					} else {
						// Set the variable value to false.
						$variable_value = 'false';
					}
				} elseif ( 'random_number' === $variable_kind ) {
					// Set the variable value to random number.
					$variable_value = wp_rand( 1, $dynamic_part );
				} elseif ( 'random_string' === $variable_kind ) {
					// Set the variable value to random string.
					$variable_value = flowmattic_random_string( $dynamic_part );
				}
			} else {
				// Get the variable value.
				$variable_value = $this->get_var_value( $variable_name );

				// If the variable value starts with PHP: then evaluate it.
				if ( strpos( $variable_value, 'PHP:' ) === 0 ) {
					// Remove the PHP: from the variable value.
					$variable_value = substr( $variable_value, 4 );

					// Evaluate the variable value.
					$variable_value = eval( 'return ' . $variable_value . ';' );
				}
			}

			// Convert $variable_value to be JSON safe string.
			$variable_value = esc_attr( $variable_value );

			// Replace the variable with its value in the array.
			foreach ( $dummy_array as $key => $value ) {
				if ( ! is_array( $value ) ) {
					if ( false !== strpos( $value, '{{' . $variable_name . '}}' ) ) {
						$dummy_array[ $key ] = str_replace( '{{' . $variable_name . '}}', $variable_value, $value );
					}
				} else {
					foreach ( $value as $sub_key => $sub_value ) {
						if ( ! is_array( $sub_value ) ) {
							if ( $sub_value && false !== strpos( $sub_value, '{{' . $variable_name . '}}' ) ) {
								$dummy_array[ $key ][ $sub_key ] = str_replace( '{{' . $variable_name . '}}', $variable_value, $sub_value );
							}
						} else {
							foreach ( $sub_value as $sub_sub_key => $sub_sub_value ) {
								if ( ! is_array( $sub_sub_value ) ) {
									if ( false !== strpos( $sub_sub_value, '{{' . $variable_name . '}}' ) ) {
										$dummy_array[ $key ][ $sub_key ][ $sub_sub_key ] = str_replace( '{{' . $variable_name . '}}', $variable_value, $sub_sub_value );
									}
								} else {
									foreach ( $sub_sub_value as $sub_sub_sub_key => $sub_sub_sub_value ) {
										if ( ! is_array( $sub_sub_sub_value ) ) {
											if ( false !== strpos( $sub_sub_sub_value, '{{' . $variable_name . '}}' ) ) {
												$dummy_array[ $key ][ $sub_key ][ $sub_sub_key ][ $sub_sub_sub_key ] = str_replace( '{{' . $variable_name . '}}', $variable_value, $sub_sub_sub_value );
											}
										} else {
											// Convert sub_sub_sub_value to be JSON safe string.
											$sub_sub_sub_value = wp_json_encode( $sub_sub_sub_value );

											// Replace the variable with its value.
											$dummy_array[ $key ][ $sub_key ][ $sub_sub_key ][ $sub_sub_sub_key ] = str_replace( '{{' . $variable_name . '}}', $variable_value, $sub_sub_sub_value );
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// Return the data with variables replaced with their values.
		$data = $dummy_array;

		return $data;
	}

	/**
	 * Create a variable.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function create_variable() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'nonce' );

		// Check if the variable name is set.
		if ( ! isset( $_POST['variable_name'] ) ) {
			wp_send_json_error( esc_attr__( 'Variable name is required.', 'flowmattic' ) );
		}

		// Check if the variable value is set.
		if ( ! isset( $_POST['variable_value'] ) ) {
			wp_send_json_error( esc_attr__( 'Variable value is required.', 'flowmattic' ) );
		}

		// Check if the variable description is set.
		if ( ! isset( $_POST['variable_description'] ) ) {
			wp_send_json_error( esc_attr__( 'Variable description is required.', 'flowmattic' ) );
		}

		// Get the variable name.
		$variable_name = sanitize_text_field( wp_unslash( $_POST['variable_name'] ) );

		// Get the variable value.
		$variable_value = $_POST['variable_value']; // @codingStandardsIgnoreLine - Input is expected to be safe and support HTML value.

		// Get the variable description.
		$variable_description = sanitize_text_field( wp_unslash( $_POST['variable_description'] ) );

		// Check if the variable name already exists.
		$variable = wp_flowmattic()->variables_db->get( array( 'variable_name' => $variable_name ) );

		// If the variable exists, update it.
		if ( ! empty( $variable ) ) {
			// Create the variable.
			$variable = wp_flowmattic()->variables_db->update(
				array(
					'variable_name'        => $variable_name,
					'variable_value'       => $variable_value,
					'variable_description' => $variable_description,
				)
			);

			// Set update message.
			$message = esc_attr__( 'Updated', 'flowmattic' );
		} else {
			// Create the variable.
			$variable = wp_flowmattic()->variables_db->insert(
				array(
					'variable_name'        => $variable_name,
					'variable_value'       => $variable_value,
					'variable_description' => $variable_description,
				)
			);

			// Set update message.
			$message = esc_attr__( 'Created', 'flowmattic' );
		}

		// If the variable is created, return success.
		if ( ! empty( $variable ) ) {
			wp_send_json_success( $message );
		}

		// If the variable is not created, return error.
		wp_send_json_error( esc_attr__( 'Variable not created.', 'flowmattic' ) );

		// End the ajax call.
		wp_die();
	}

	/**
	 * Delete a variable.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function delete_variable() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'nonce' );

		// Check if the variable name is set.
		if ( ! isset( $_POST['variable_name'] ) ) {
			wp_send_json_error( esc_attr__( 'Variable name is required.', 'flowmattic' ) );
		}

		// Get the variable name.
		$variable_name = sanitize_text_field( wp_unslash( $_POST['variable_name'] ) );

		// Delete the variable.
		$variable = wp_flowmattic()->variables_db->delete(
			array(
				'variable_name' => $variable_name,
			)
		);

		// If the variable is deleted, return success.
		if ( ! empty( $variable ) ) {
			wp_send_json_success( esc_attr__( 'Variable deleted successfully.', 'flowmattic' ) );
		}

		// If the variable is not deleted, return error.
		wp_send_json_error( esc_attr__( 'Variable not deleted.', 'flowmattic' ) );

		// End the ajax call.
		wp_die();
	}
}
