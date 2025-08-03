<?php
/**
 * Application Name: FlowMattic PHP Functions
 * Description: Add PHP Functions module to FlowMattic.
 * Version: 1.1
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
 * PHP Functions module integration class.
 *
 * @since 1.1
 */
class FlowMattic_Php_Functions {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 3.1.1
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for filter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'php_functions',
			array(
				'name'         => esc_attr__( 'PHP Functions by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/php-functions/icon.svg',
				'instructions' => 'Call any PHP function and pass data from your workflow.',
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
	 * @since 1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-php-functions', FLOWMATTIC_PLUGIN_URL . 'inc/apps/php-functions/view-php-functions.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.1
	 * @return array
	 */
	public function get_actions() {
		return array(
			'call_function' => array(
				'title'       => esc_attr__( 'Call PHP Function', 'flowmattic' ),
				'description' => esc_attr__( 'Call any PHP function and pass data from your workflow.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.1
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$php_function   = isset( $fields['php_function'] ) ? $fields['php_function'] : ( isset( $step['actionAppArgs']['php_function'] ) ? $step['actionAppArgs']['php_function'] : '' );
		$value_decode   = ( isset( $fields['parameter_value_decode'] ) ) ? $fields['parameter_value_decode'] : 'default';
		$parameter_type = ( isset( $fields['parameter_type'] ) && '' !== $fields['parameter_type'] ) ? $fields['parameter_type'] : 'none';
		$response_array = array();
		$error_message  = '';

		$function_parameters = array();
		if ( 'none' !== $parameter_type ) {
			$function_parameters = ( isset( $step['phpfunction_parameters'] ) ) ? $step['phpfunction_parameters'] : ( isset( $step['settings'] ) ? $step['settings']['phpfunction_parameters'] : array() );

			if ( 'array' === $value_decode ) {
				foreach ( $function_parameters as $parameter_key => $parameter_value ) {
					$maybe_json_decode                     = json_decode( $parameter_value, true );
					$function_parameters[ $parameter_key ] = ( is_array( $maybe_json_decode ) ) ? $maybe_json_decode : ( ( is_array( json_decode( stripslashes( $parameter_value ), true ) ) ) ? json_decode( stripslashes( $parameter_value ), true ) : $parameter_value );
				}
			}
		}

		$function_callable = false;

		if ( false !== strpos( $php_function, '::' ) ) {
			$php_function = explode( '::', $php_function );
			$class_name   = $php_function[0];
			$method_name  = $php_function[1];

			if ( class_exists( $class_name ) && method_exists( $class_name, $method_name ) ) {
				$function_callable = true;
			}
		} elseif ( function_exists( $php_function ) ) {
			$function_callable = true;
		}

		if ( $function_callable ) {
			if ( ! empty( $function_parameters ) && 'none' !== $parameter_type ) {
				// Set the request body.
				$this->request_body = $function_parameters;

				if ( 'array' === $parameter_type ) {
					try {
						$response = $php_function( $function_parameters );
					} catch ( Error $e ) {
						$error_message = $e->getMessage();
					}
				} else {
					$variables_array = array_values( $function_parameters );

					try {
						$response = call_user_func_array( $php_function, $variables_array );
					} catch ( Error $e ) {
						$error_message = $e->getMessage();
					}
				}
			} else {
				try {
					$response = $php_function();
				} catch ( Error $e ) {
					$error_message = $e->getMessage();
				}
			}

			if ( '' !== $error_message ) {
				$response = false;
			}

			$response_array = array();

			if ( is_array( $response ) || is_object( $response ) ) {
				foreach ( (array) $response as $key => $value ) {
					$key = str_replace( array( '*', chr( 0 ) ), '', $key );
					if ( is_array( $value ) ) {
						$response_array[ $key ] = $value;
					} elseif ( is_object( $value ) ) {
						$response_array[ $key ] = (array) $value;
					} else {
						$response_array[ $key ] = $value;
					}
				}

				$response = array(
					'status'   => 'success',
					'response' => wp_json_encode( $response_array ),
				);

				// For direct use in mapping.
				foreach ( $response_array as $key => $array_item ) {
					if ( is_array( $array_item ) ) {
						$response = flowmattic_recursive_array( $response, $key, $array_item );
					} else {
						$response[ $key ] = $array_item;
					}
				}
			} elseif ( '' !== $error_message ) {
				$response = array(
					'status'   => 'error',
					'response' => $error_message,
				);
			} else {
				$response = array(
					'status'   => 'success',
					'response' => $response,
				);
			}
		} else {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Function or method does not exists', 'flowmattic' ),
			);
		}

		return wp_json_encode( $response );
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 3.1.1
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.1
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event          = $event_data['event'];
		$settings       = $event_data['settings'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Php_Functions();
