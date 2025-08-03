<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text Formatter by FlowMattic.
 *
 * @class FlowMattic_Text_Formatter
 */
class FlowMattic_Text_Formatter {
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
	 * @since 2.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for text formatter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'text_formatter',
			array(
				'name'         => esc_html__( 'Text Formatter by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/text-formatter/icon.svg',
				'instructions' => __( 'Format text with different methods.', 'flowmattic' ),
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
	 * @since 2.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-text-formatter', FLOWMATTIC_PLUGIN_URL . 'inc/apps/text-formatter/view-text-formatter.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'extract_pattern' => array(
				'title'       => esc_html__( 'Extract Pattern', 'flowmattic' ),
				'description' => esc_html__( 'Provide regular expression to extract string/text.', 'flowmattic' ),
			),
			'find_in_text'    => array(
				'title'       => esc_html__( 'Find in Text', 'flowmattic' ),
				'description' => esc_html__( 'Search text in the given content.', 'flowmattic' ),
			),
			'replace_text'    => array(
				'title'       => esc_html__( 'Replace Text', 'flowmattic' ),
				'description' => esc_html__( 'Search and replace text in the given content.', 'flowmattic' ),
			),
			'format_text'     => array(
				'title'       => esc_html__( 'Basic Formatting', 'flowmattic' ),
				'description' => esc_html__( 'Format text as capitalize, lowercase, uppercase, wordcount etc.', 'flowmattic' ),
			),
			'split_text'      => array(
				'title'       => esc_html__( 'Split Text', 'flowmattic' ),
				'description' => esc_html__( 'Split the text on a character or word and return a segment', 'flowmattic' ),
			),
			'text_between'    => array(
				'title'       => esc_html__( 'Text Between', 'flowmattic' ),
				'description' => esc_html__( 'Extract text between two strings', 'flowmattic' ),
			),
			'default_value'   => array(
				'title'       => esc_html__( 'Default Value', 'flowmattic' ),
				'description' => esc_html__( 'Return a default value if the text is empty', 'flowmattic' ),
			),
			'truncate'        => array(
				'title'       => esc_html__( 'Truncate', 'flowmattic' ),
				'description' => esc_html__( 'Limit your text to a specific character length, and delete anything over that', 'flowmattic' ),
			),
			'url_encode'      => array(
				'title'       => esc_html__( 'URL Encode', 'flowmattic' ),
				'description' => esc_html__( 'Encodes text for use in URLs', 'flowmattic' ),
			),
			'url_decode'      => array(
				'title'       => esc_html__( 'URL Decode', 'flowmattic' ),
				'description' => esc_html__( 'Decodes text from URL string', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$response_array = array();

		switch ( $action ) {
			case 'extract_pattern':
				$response = $this->extract_pattern( $fields );
				break;

			case 'find_in_text':
				$response = $this->find_in_text( $fields );
				break;

			case 'replace_text':
				$response = $this->replace_text( $fields );
				break;

			case 'format_text':
				$response = $this->format_text( $fields );
				break;

			case 'split_text':
				$response = $this->split_text( $fields );
				break;

			case 'text_between':
				$response = $this->text_between( $fields );
				break;

			case 'default_value':
				$response = $this->default_value( $fields );
				break;

			case 'truncate':
				$response = $this->truncate( $fields );
				break;

			case 'url_encode':
				$response = $this->url_encode( $fields );
				break;

			case 'url_decode':
				$response = $this->url_decode( $fields );
				break;
		}

		return $response;
	}

	/**
	 * Extract pattern.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function extract_pattern( $fields ) {
		$content         = $fields['content'];
		$regex_pattern   = $fields['regex_pattern'];
		$combine_results = isset( $fields['combine_results'] ) && 'yes' === $fields['combine_results'] ? true : false;

		// Set the request body.
		$this->request_body = array(
			'content'       => $content,
			'regex_pattern' => $regex_pattern,
		);

		$all_matches = array();

		if ( preg_match_all( '/' . $regex_pattern . '/', $content, $matches ) ) {
			$all_matches = $matches;
		} elseif ( preg_match_all( '/' . $regex_pattern, $content, $matches ) ) {
			$all_matches = $matches;
		} elseif ( preg_match_all( $regex_pattern . '/', $content, $matches ) ) {
			$all_matches = $matches;
		} elseif ( preg_match_all( $regex_pattern, $content, $matches ) ) {
			$all_matches = $matches;
		}

		if ( ! empty( $all_matches ) ) {
			// If multiple matches found, return all matches.
			$simple_entry = array();

			$n = 0;
			foreach ( $all_matches as $key => $match ) {
				if ( is_array( $match ) && 1 < count( $match ) ) {
					foreach ( $match as $i => $item ) {
						$simple_entry[ 'result_' . $n ] = is_array( $item ) ? $item[0] : $item;
						$n++;
					}
				} else {
					$simple_entry[ 'result_' . $n ] = is_array( $match ) ? $match[0] : $match;
				}

				$n++;
			}

			// Combined result.
			if ( $combine_results ) {
				$combined_result = implode( '', $simple_entry );
				$simple_entry['combined_result'] = $combined_result;
			}

			return wp_json_encode( $simple_entry );
		} elseif ( preg_match( '/' . $regex_pattern . '/', $content, $matches ) ) {
			// If single match found, return the first match.
			return wp_json_encode(
				array(
					'status'          => 'success',
					'result_0'        => isset( $matches[0] ) ? $matches[0] : '',
					'combined_result' => isset( $matches[0] ) ? $matches[0] : '',
					'message'         => esc_html__( 'Single match found for the given pattern.', 'flowmattic' ),
				)
			);		
		} else {
			// If no match found, return error.
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No match found for the given pattern.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Find in text.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function find_in_text( $fields ) {
		$content       = $fields['content'];
		$search_string = $fields['search_string'];

		// Set the request body.
		$this->request_body = array(
			'content'       => $content,
			'search_string' => $search_string,
		);

		$string_position = strpos( $content, $search_string );

		return wp_json_encode(
			array(
				'result' => $string_position,
			)
		);
	}

	/**
	 * Replace text.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function replace_text( $fields ) {
		$content        = $fields['content'];
		$search_string  = $fields['search_string'];
		$replace_string = $fields['replace_string'];

		// Set the request body.
		$this->request_body = array(
			'content'        => $content,
			'search_string'  => $search_string,
			'replace_string' => $replace_string,
		);

		$replaced_string = str_replace( $search_string, $replace_string, $content );

		return wp_json_encode(
			array(
				'result' => $replaced_string,
			)
		);
	}

	/**
	 * Format text.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function format_text( $fields ) {
		$content = $fields['content'];
		$method  = $fields['method'];

		// Set the request body.
		$this->request_body = array(
			'content' => $content,
			'method'  => $method,
		);

		$updated_string = '';

		switch ( $method ) {
			case 'ucwords':
			case 'title_case':
				$updated_string = ucwords( $content );
				break;
			case 'strtoupper':
				$updated_string = strtoupper( $content );
				break;
			case 'strtolower':
				$updated_string = strtolower( $content );
				break;
			case 'ucfirst':
				$updated_string = ucfirst( strtolower( $content ) );
				break;

			case 'remove_html_tags':
				$updated_string = wp_strip_all_tags( $content );
				break;

			case 'extract_number':
				if ( preg_match_all( '/\d+/', $content, $matches ) ) {
					$simple_entry = array();

					foreach ( $matches[0] as $key => $match ) {
						$simple_entry[ 'number_' . $key ] = $match;
					}

					return wp_json_encode( $simple_entry );
				}
				break;

			case 'extract_email':
				$emails = flowmattic_parse_emails_from_mailhook( $content );

				if ( isset( $emails['array'] ) ) {
					$simple_entry = array();

					foreach ( $emails['array'] as $key => $match ) {
						$simple_entry[ 'email_' . $key ] = $match;
					}

					return wp_json_encode( $simple_entry );
				}
				break;

			case 'extract_phone':
				if ( preg_match_all( '/\+?\d{1,4}+[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/', $content, $matches ) ) {
					$simple_entry = array();

					foreach ( $matches[0] as $key => $match ) {
						$simple_entry[ 'phone_' . $key ] = $match;
					}

					return wp_json_encode( $simple_entry );
				}
				break;

			case 'extract_url':
				if ( preg_match_all( '/https?:\/\/[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|\/))/', $content, $matches ) ) {
					$simple_entry = array();

					foreach ( $matches[0] as $key => $match ) {
						$simple_entry[ 'url_' . $key ] = $match;
					}

					return wp_json_encode( $simple_entry );
				}
				break;

			case 'get_string_length':
				$updated_string = strlen( $content );
				break;

			case 'get_word_count':
				$updated_string = str_word_count( $content );
				break;

			case 'trim_whitespace':
				$updated_string = preg_replace( '/\s+/', ' ', trim( $content ) );
				break;
		}

		return wp_json_encode(
			array(
				'result' => $updated_string,
			)
		);
	}

	/**
	 * Split text.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function split_text( $fields ) {
		$content   = $fields['content'];
		$separator = $fields['separator'];
		$segment   = $fields['segment'];

		// Set the request body.
		$this->request_body = array(
			'content'   => $content,
			'separator' => $separator,
			'segment'   => $segment,
		);

		// If is content empty, return error.
		if ( '' === $content ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Content is empty', 'flowmattic' ),
				)
			);
		}

		// If space placeholder is used, replace it with single space.
		$separator = str_replace( '[space]', ' ', $separator );

		$segments = explode( $separator, $content );

		switch ( $segment ) {
			case 'first':
				$result = trim( $segments[0] );
				break;

			case 'second':
				$result = trim( $segments[1] );
				break;

			case 'last':
				$last_index = count( $segments ) - 1;
				$result     = trim( $segments[ $last_index ] );
				break;

			case 'second_to_last':
				$last_index = count( $segments ) - 2;
				$result     = trim( $segments[ $last_index ] );
				break;

			case 'all':
				$simple_entry = array(
					'all_items' => array(),
				);

				$all_items = array();

				if ( ! empty( $segments ) ) {
					$n = 0;
					foreach ( $segments as $key => $item ) {
						$simple_entry[ 'item_' . $n ] = $item;

						// Add all items for use with Iterator.
						$all_items[] = array( $item );
						$n++;
					}

					$simple_entry['all_items'] = wp_json_encode( $all_items );

					return wp_json_encode( $simple_entry );
				}
				break;

		}

		return wp_json_encode(
			array(
				'result' => $result,
			)
		);
	}

	/**
	 * Text between.
	 *
	 * @access public
	 * @since 4.1.9
	 * @param array $fields Fields.
	 * @return array
	 */
	public function text_between( $fields ) {
		$content = $fields['content'];
		$start   = $fields['start_text'];
		$end     = $fields['end_text'];

		// Set the request body.
		$this->request_body = array(
			'content'    => $content,
			'start_text' => $start,
			'end_text'   => $end,
		);

		$pattern = '/' . preg_quote( $start, '/' ) . '\s*([\s\S]*?)\s*' . preg_quote( $end, '/' ) . '/';

		if ( preg_match( $pattern, $content, $matches ) ) {
			return wp_json_encode(
				array(
					'status' => 'success',
					'result' => $matches[1],
				)
			);
		} else {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'Something went wrong. Please try again.',
				)
			);
		}
	}

	/**
	 * Default value.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function default_value( $fields ) {
		$field         = $fields['field'];
		$default_value = $fields['default_value'];

		// Set the request body.
		$this->request_body = array(
			'field'         => $field,
			'default_value' => $default_value,
		);

		$updated_value = ( empty( $field ) ) ? $default_value : $field;

		return wp_json_encode(
			array(
				'value' => $updated_value,
			)
		);
	}

	/**
	 * Truncate string.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function truncate( $fields ) {
		$content         = $fields['content'];
		$max_length      = ( ! empty( $fields['max_length'] ) ) ? $fields['max_length'] : null;
		$skip_characters = ( ! empty( $fields['skip_characters'] ) ) ? $fields['skip_characters'] : 0;
		$append_ellipsis = $fields['append_ellipsis'];
		$ellipsis_text   = ( ! empty( $fields['custom_ellipsis_text'] ) ) ? $fields['custom_ellipsis_text'] : '...';

		// Set the request body.
		$this->request_body = array(
			'content'         => $content,
			'max_length'      => $max_length,
			'skip_characters' => $skip_characters,
			'append_ellipsis' => $append_ellipsis,
			'ellipsis_text'   => $ellipsis_text,
		);

		// Remove white space.
		$content = preg_replace( '/\s+/', ' ', trim( $content ) );

		$truncated_string = substr( $content, $skip_characters, $max_length );

		// If set Ellipsis.
		if ( 'yes' === $append_ellipsis ) {
			$truncated_string = $truncated_string . $ellipsis_text;
		}

		return wp_json_encode(
			array(
				'value' => $truncated_string,
			)
		);
	}

	/**
	 * Encode string to URL.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function url_encode( $fields ) {
		$content       = $fields['content'];
		$convert_space = $fields['convert_space'];

		// Set the request body.
		$this->request_body = array(
			'content'       => $content,
			'convert_space' => $convert_space,
		);

		$updated_string = rawurlencode( $content );

		if ( 'yes' === $convert_space ) {
			$updated_string = str_replace( '%20', '+', $updated_string );
		} else {
			$updated_string = str_replace( '%2F', '/', $updated_string );
		}

		return wp_json_encode(
			array(
				'result' => $updated_string,
			)
		);
	}

	/**
	 * Decode URL string.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function url_decode( $fields ) {
		$content      = $fields['content'];
		$convert_plus = $fields['convert_plus'];

		// Set the request body.
		$this->request_body = array(
			'content'      => $content,
			'convert_plus' => $convert_plus,
		);

		$updated_string = rawurldecode( $content );

		if ( 'yes' === $convert_plus ) {
			$updated_string = str_replace( '+', ' ', $updated_string );
		}

		return wp_json_encode(
			array(
				'result' => $updated_string,
			)
		);
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event       = $event_data['event'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$event_data['fields'] = is_array( $event_data['fields'] ) ? stripslashes_deep( $event_data['fields'] ) : array_map( 'stripslashes', $event_data['fields'] );

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

new FlowMattic_Text_Formatter();
