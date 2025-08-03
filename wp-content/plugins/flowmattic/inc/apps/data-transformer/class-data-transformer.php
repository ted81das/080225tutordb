<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Transformer by FlowMattic.
 *
 * @class FlowMattic_Data_Transformer
 */
class FlowMattic_Data_Transformer {
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
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for data transformer.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'data_transformer',
			array(
				'name'         => esc_html__( 'Data Transformer by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/data-transformer/icon.svg',
				'instructions' => __( 'Transform data from one format to another.', 'flowmattic' ),
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
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-data-transformer', FLOWMATTIC_PLUGIN_URL . 'inc/apps/data-transformer/view-data-transformer.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Get actions.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'html_encode'   => array(
				'title'       => esc_html__( 'HTML Encode', 'flowmattic' ),
				'description' => esc_html__( 'Encode an HTML text.', 'flowmattic' ),
			),
			'html_decode'   => array(
				'title'       => esc_html__( 'HTML Decode', 'flowmattic' ),
				'description' => esc_html__( 'Decode an HTML text.', 'flowmattic' ),
			),
			'base64_encode' => array(
				'title'       => esc_html__( 'Base64 Encode', 'flowmattic' ),
				'description' => esc_html__( 'Encodes a plain text to a Base64.', 'flowmattic' ),
			),
			'base64_decode' => array(
				'title'       => esc_html__( 'Base64 Decode', 'flowmattic' ),
				'description' => esc_html__( 'Decodes a Base64 encoded text.', 'flowmattic' ),
			),
			'sha256_hash'   => array(
				'title'       => esc_html__( 'SHA256', 'flowmattic' ),
				'description' => esc_html__( 'Encodes a plain text to a SHA256 format.', 'flowmattic' ),
			),
			'sha1_hash'     => array(
				'title'       => esc_html__( 'SHA1', 'flowmattic' ),
				'description' => esc_html__( 'Encodes a plain text to a SHA1 format.', 'flowmattic' ),
			),
			'sha512_hash'   => array(
				'title'       => esc_html__( 'SHA512', 'flowmattic' ),
				'description' => esc_html__( 'Encodes a plain text to a SHA512 format.', 'flowmattic' ),
			),
			'hmac_sha256'   => array(
				'title'       => esc_html__( 'HMAC SHA256', 'flowmattic' ),
				'description' => esc_html__( 'Encodes to a HMAC-SHA256.', 'flowmattic' ),
			),
			'md5_hash'      => array(
				'title'       => esc_html__( 'MD5', 'flowmattic' ),
				'description' => esc_html__( 'Encodes a plain text to a MD5 format.', 'flowmattic' ),
			),
			'strip_tags'    => array(
				'title'       => esc_html__( 'Strip Tags', 'flowmattic' ),
				'description' => esc_html__( 'Strips given text from HTML tags.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$response_array = array();

		// Send error if $fields['content'] is empty.
		if ( empty( $fields['content'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Content field is required.', 'flowmattic' ),
				)
			);
		}

		switch ( $action ) {
			case 'html_encode':
				$response = $this->html_encode( $fields );
				break;

			case 'html_decode':
				$response = $this->html_decode( $fields );
				break;

			case 'base64_encode':
				$response = $this->base64_encode( $fields );
				break;

			case 'base64_decode':
				$response = $this->base64_decode( $fields );
				break;

			case 'sha256_hash':
				$response = $this->sha256_hash( $fields );
				break;

			case 'sha1_hash':
				$response = $this->sha1_hash( $fields );
				break;

			case 'sha512_hash':
				$response = $this->sha512_hash( $fields );
				break;

			case 'hmac_sha256':
				$response = $this->hmac_sha256( $fields );
				break;

			case 'md5_hash':
				$response = $this->md5_hash( $fields );
				break;

			case 'strip_tags':
				$response = $this->strip_tags( $fields );
				break;

			default:
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid action specified.', 'flowmattic' ),
				);
		}
		return wp_json_encode( $response );
	}

	/**
	 * HTML encode.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode HTML text.
	 * @return array
	 */
	public function html_encode( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$encoded_content = htmlentities( $content );
			$response        = array(
				'status'   => 'success',
				'response' => $encoded_content,
			);

		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * HTML decode.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to decode HTML text.
	 * @return array
	 */
	public function html_decode( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$decoded_content = html_entity_decode( $content );
			$response        = array(
				'status'   => 'success',
				'response' => $decoded_content,
			);

		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * Base64 encode.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode Base64 text.
	 * @return array
	 */
	public function base64_encode( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$encoded_content = base64_encode( $content );
			$response        = array(
				'status'   => 'success',
				'response' => $encoded_content,
			);

		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * Base64 decode.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to decode Base64 text.
	 * @return array
	 */
	public function base64_decode( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$decoded_content = base64_decode( $content, true );
			if ( $decoded_content === false ) {
				throw new Error( esc_html__( 'Invalid Base64 encoded string.', 'flowmattic' ) );
			}

			$response = array(
				'status'   => 'success',
				'response' => $decoded_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * SHA256 hash.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode SHA256 text.
	 * @return array
	 */
	public function sha256_hash( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$hashed_content = hash( 'sha256', $content );
			$response       = array(
				'status'   => 'success',
				'response' => $hashed_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * SHA1 hash.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode SHA1 text.
	 * @return array
	 */
	public function sha1_hash( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$hashed_content = sha1( $content );
			$response       = array(
				'status'   => 'success',
				'response' => $hashed_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * SHA512 hash.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode SHA512 text.
	 * @return array
	 */
	public function sha512_hash( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$hashed_content = hash( 'sha512', $content );
			$response       = array(
				'status'   => 'success',
				'response' => $hashed_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * HMAC SHA256.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode HMAC SHA256 text.
	 * @return array
	 */
	public function hmac_sha256( $fields ) {
		$content = $fields['content'];
		// Check if key is provided, otherwise use default.
		$key = isset( $fields['key'] ) ? $fields['key'] : 'flowmattic';

		$this->request_body = array(
			'content' => $content,
			'key'     => $key,
		);

		$response = array();

		try {
			$hmac_hashed_content = hash_hmac( 'sha256', $content, $key );
			$response            = array(
				'status'   => 'success',
				'response' => $hmac_hashed_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * MD5 hash.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to encode MD5 text.
	 * @return array
	 */
	public function md5_hash( $fields ) {
		$content = $fields['content'];

		$this->request_body = array(
			'content' => $content,
		);

		$response = array();
		try {
			$hashed_content = md5( $content );
			$response       = array(
				'status'   => 'success',
				'response' => $hashed_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * Strip tags.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $fields fields to strip tags from text.
	 * @return array
	 */
	public function strip_tags( $fields ) {
		$content      = $fields['content'];
		$allowed_tags = isset( $fields['allowed_tags'] ) ? $fields['allowed_tags'] : '';

		$this->request_body = array(
			'content'      => $content,
			'allowed_tags' => $allowed_tags,
		);

		$response = array();

		try {
			$stripped_content = strip_tags( $content, $allowed_tags );
			$response         = array(
				'status'   => 'success',
				'response' => $stripped_content,
			);
		} catch ( Error $e ) {
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			);
		}

		return $response;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.0.0
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

new FlowMattic_Data_Transformer();
