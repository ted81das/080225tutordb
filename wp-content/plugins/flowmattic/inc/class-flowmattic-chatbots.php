<?php
/**
 * Handle chatbots.
 *
 * @package flowmattic
 * @since 3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chatbots main class.
 *
 * @since 4.0
 */
class FlowMattic_Chatbots {

	/**
	 * OpenAI API URL
	 *
	 * @access public
	 * @since 4.0
	 * @var string
	 */
	public $api_url = 'https://api.openai.com/v1/';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function __construct() {
		// Register REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Ajax to create assistant from dashboard.
		add_action( 'wp_ajax_flowmattic_create_ai_assistant', array( $this, 'chatbot_create_ai_assistant' ) );

		// Ajax to delete the assistant.
		add_action( 'wp_ajax_flowmattic_delete_assistant', array( $this, 'flowmattic_delete_assistant' ) );

		// Ajax to create assistant in OpenAI.
		add_action( 'wp_ajax_flowmattic_chatbot_create_assistant', array( $this, 'chatbot_create_assistant' ) );

		// Ajax to refresh assistants.
		add_action( 'wp_ajax_flowmattic_chatbot_refresh_assistants', array( $this, 'chatbot_refresh_assistants' ) );

		// Ajax to save chatbot.
		add_action( 'wp_ajax_flowmattic_chatbot_insert_or_update', array( $this, 'chatbot_insert_or_update' ) );

		// Ajax to get the chatbot settings.
		add_action( 'wp_ajax_flowmattic_chatbot_edit_settings', array( $this, 'get_chatbot_settings' ) );

		// Add shortcode for the chatbot to embed in the same site.
		add_shortcode( 'flowmattic_chatbot', array( $this, 'chatbot_embed_shortcode' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'flowmattic/v1',
			'/ajax/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'flowmattic_handle_ajax_request' ),
				'permission_callback' => function( $request ) {
					// Get the action.
					$action = $request->get_param( 'action' );

					// Check if user ID is passed.
					$user_id = $request->get_param( 'userId' );

					if ( ( 'get_threads' === $action || 'upload_content_source' === $action || 'add_content_source' === $action || 'delete_content_source' === $action ) ) {
						if ( ! empty( $user_id ) ) {
							// Check if passed user ID is correct.
							$user = get_user_by( 'id', $user_id );

							if ( ! $user ) {
								return false;
							} else {
								// Get the secure key from request.
								$user_pass = $request->get_param( 'secureKey' );

								// Get the secure key from database.
								$user_pass_db = $user->user_pass;

								// Check if the secure keys match.
								if ( $user_pass === $user_pass_db ) {
									return true;
								}

								return false;
							}
						} else {
							return false;
						}
					} else {
						return true;
					}
				},
			)
		);

		register_rest_route(
			'flowmattic/v1',
			'/embed/chatbot/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'chatbot_embed' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'flowmattic/v1',
			'/embed/chatbot/widget/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'chatbot_embed_widget' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle the ajax request.
	 *
	 * @access public
	 * @since 4.0
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function flowmattic_handle_ajax_request( WP_REST_Request $request ) {
		if ( ! defined( 'REST_REQUEST' ) ) {
			if ( ! wp_verify_nonce( $request->get_param( 'chatbot_nonce' ), 'wp_rest' ) ) {
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Authentication failed!', 'flowmattic' ),
				);

				return new WP_REST_Response( $response, 403 );
			}
		}

		// Get the action.
		$action = $request->get_param( 'action' );

		// Get the settings.
		$settings = $request->get_param( 'settings' );

		// Initialize response.
		$response = array(
			'status'  => 'error',
			'message' => esc_html__( 'Invalid action.', 'flowmattic' ),
		);

		// Switch to the action.
		switch ( $action ) {
			case 'create_run':
				$response = $this->create_run( $settings );
				break;

			case 'get_chatbot':
				$response = $this->get_chatbot( $settings );
				break;

			case 'get_threads':
				$response = $this->get_threads( $settings );
				break;

			case 'get_thread':
				$response = $this->get_thread( $settings );
				break;

			case 'create_message':
				$response = $this->create_message( $settings );
				break;

			case 'create_thread':
				$response = $this->create_thread( $settings );
				break;

			case 'upload_content_source':
				$response = $this->upload_content_source( $settings );
				break;

			case 'add_content_source':
				$response = $this->add_content_source( $settings );
				break;

			case 'delete_content_source':
				$response = $this->delete_content_source( $settings );
				break;
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Embed chatbot.
	 *
	 * @access public
	 * @since 4.0
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function chatbot_embed( WP_REST_Request $request ) {
		if ( ! $request->get_param( 'chatbot_id' ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Chatbot ID is required.', 'flowmattic' ),
				),
				403
			);
		}

		// Get the chatbot ID.
		$chatbot_id = sanitize_text_field( esc_attr( base64_decode( $request->get_param( 'chatbot_id' ) ) ) );

		// Get the chatbot data.
		$chatbot_object = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// If chatbot id is wrong, return error.
		if ( empty( $chatbot_object ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid chatbot ID.', 'flowmattic' ),
				),
				403
			);
		}

		// Set the header as text/html.
		header( 'Content-Type: text/html' );

		ob_start();
		// Include the chatbot embed file.
		include_once FLOWMATTIC_PLUGIN_DIR . 'inc/embed/chatbot.php';
	}

	/**
	 * Embed chatbot widget.
	 *
	 * @access public
	 * @since 4.0
	 * @param WP_REST_Request $request The request.
	 * @return void
	 */
	public function chatbot_embed_widget( WP_REST_Request $request ) {
		// Get the chatbot ID.
		$chatbot_id = sanitize_text_field( esc_attr( $request->get_param( 'chatbot_id' ) ) );

		// Get the chatbot route URL.
		$chatbot_url = rest_url( 'flowmattic/v1/embed/chatbot/' ) . '?chatbot_id=' . $chatbot_id . '&timestamp=' . time();

		// Set the header as application/x-javascript.
		header( 'Content-Type: application/x-javascript' );

		// Get the chatbot js file URL.
		$chatbot_widget_js = FLOWMATTIC_PLUGIN_URL . 'assets/admin/min/flowmattic-chatbot-widget.min.js';

		// Decode the chatbot ID.
		$chatbot_id_decoded = base64_decode( $chatbot_id );

		// Get the chatbot data.
		$chatbot_object = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id_decoded );

		$chatbot_styles = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_styles ) : array();

		// Get the chatbot message background color.
		$chatbot_message_background = isset( $chatbot_styles->chatbot_message_background ) ? $chatbot_styles->chatbot_message_background : '#0d6efd';

		// Get chatbox icon image.
		$chatbox_icon = isset( $chatbot_styles->chatbox_icon ) ? $chatbot_styles->chatbox_icon : FLOWMATTIC_PLUGIN_URL . 'assets/admin/img/chat-icon.svg';

		// Create the params array.
		$params = array(
			'chatbotURL' => $chatbot_url,
			'chatbotID'  => $chatbot_id,
			'chatIcon'   => $chatbox_icon,
			'chatIconBG' => $chatbot_message_background,
		);

		ob_start();
		// Add the params as a global variable.
		echo 'var FMChatbotParams = ' . wp_json_encode( $params ) . ';';
		$get_script = wp_remote_get( $chatbot_widget_js, array( 'sslverify' => false ) );
		echo $get_script['body'];
	}

	/**
	 * Embed chatbot shortcode.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $atts The attributes.
	 * @return string
	 */
	public function chatbot_embed_shortcode( $atts ) {
		// Get the chatbot ID.
		$chatbot_id = sanitize_text_field( $atts['chatbot_id'] );

		// Get the width.
		$width = isset( $atts['width'] ) ? sanitize_text_field( $atts['width'] ) : '100%';

		// Get the height.
		$height = isset( $atts['height'] ) ? sanitize_text_field( $atts['height'] ) : '600px';

		// Get the chatbot route URL.
		$chatbot_url = rest_url( 'flowmattic/v1/embed/chatbot/' ) . '?chatbot_id=' . $chatbot_id;

		// Generate iframe.
		$chatbot_iframe = '<iframe src="' . $chatbot_url . '" style="width: ' . $width . '; height: ' . $height . '; border: none;"></iframe>';

		return $chatbot_iframe;
	}

	/**
	 * Upload content source.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function upload_content_source( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] ); // @codingStandardsIgnoreLine

		// Get file name.
		$file_name = $settings['fileName'];

		// Get file URL.
		$file_url = $settings['fileUrl'];

		// Get file type.
		$file_type = $settings['fileType'];

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get the assistant ID.
		$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

		// Get the connect ID.
		$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

		// If assistant ID or connect ID is not present, return error.
		if ( '' === $connect_id ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
			);
		}

		// Get the bearer token.
		$token = $this->get_bearer_token( $connect_id );

		// Download the file.
		$file = wp_remote_get( $file_url, array( 'sslverify' => false ) );

		if ( is_wp_error( $file ) ) {
			return array(
				'status'  => 'error',
				'message' => $file->get_error_message(),
			);
		}

		// Get the file body.
		$file_content = wp_remote_retrieve_body( $file );

		// Initialize the WordPress filesystem.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		// Create a temporary file and write the content to it.
		$temp_file = wp_tempnam( $file_url );
		if ( ! $wp_filesystem->put_contents( $temp_file, $file_content ) ) {
			return new WP_Error( 'cannot_write_file', 'Unable to write temporary file.' );
		}

		// Generate the array data to send to OpenAI for creating assistant.
		$file_data = array(
			'purpose' => 'assistants',
			'file'    => new CURLFile( realpath( $temp_file ), $file_type, $file_name ),
		);

		$request_headers = array(
			'Authorization: Bearer ' . $token,
			'User-Agent: FlowMattic',
			'Content-Type: multipart/form-data',
		);

		// Send request.
		// @codingStandardsIgnoreStart
		$curl = curl_init( $this->api_url . 'files' );
		curl_setopt( $curl, CURLOPT_URL, $this->api_url . 'files' );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $request_headers );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $file_data );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );

		$request_body  = curl_exec( $curl );

		$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		$request_decode = json_decode( $request_body, true );

		// If file created successfully, send the file ID back.
		if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
			// Create vector store file with this file id.
			$this->add_file_to_vector_store( $chatbot_assistant, $request_decode['id'], $token );

			// Create request data to retrieve message.
			$request_data = array(
				'status'  => 'success',
				'file_id' => $request_decode['id'],
			);
		} else {
			$request_data = array(
				'status'   => 'error',
				'message'  => esc_html__( 'Error uploading content source!', 'flowmattic' ),
				'response' => $request_body,
			);
		}

		return $request_data;
	}

	/**
	 * Add content source.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function add_content_source( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] ); // @codingStandardsIgnoreLine

		// Get the file ID.
		$file_id = $settings['openaiFileId'];

		// Get the file name.
		$file_name = $settings['fileName'];

		// Get the file description.
		$file_description = $settings['description'];

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get previous content sources.
		$chatbot_content_sources = ! empty( $chatbot->chatbot_data ) ? json_decode( $chatbot->chatbot_data, true ) : array();

		// Get the assistant ID.
		$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

		// Get the connect ID.
		$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

		// Get chatbot name.
		$chatbot_name = $chatbot_settings->chatbot_name;

		// If assistant ID or connect ID is not present, return error.
		if ( '' === $connect_id ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
			);
		}

		// Add the entry to database.
		$chatbot_content_source = array(
			'chatbot_id'  => $chatbot_id,
			'file_id'     => $file_id,
			'file_name'   => $file_name,
			'description' => $file_description,
		);

		// Append the new content source to the existing content sources.
		$chatbot_content_sources[] = $chatbot_content_source;

		// Create the chatbot data array.
		$chatbot_data = array(
			'chatbot_id'   => $chatbot_id,
			'chatbot_data' => wp_json_encode( $chatbot_content_sources ),
		);

		// Update the chatbot data.
		wp_flowmattic()->chatbots_db->update( $chatbot_data );

		// Create request data to retrieve message.
		$request_data = array(
			'status'  => 'success',
			'message' => esc_html__( 'Content source added successfully!', 'flowmattic' ),
		);

		return $request_data;
	}

	/**
	 * Delete content source.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function delete_content_source( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] ); // @codingStandardsIgnoreLine

		// Get the file ID.
		$file_id = $settings['sourceId'];

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get previous content sources.
		$chatbot_content_sources = ! empty( $chatbot->chatbot_data ) ? json_decode( $chatbot->chatbot_data ) : array();

		// Get the assistant ID.
		$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

		// Get the connect ID.
		$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

		// If assistant ID or connect ID is not present, return error.
		if ( '' === $connect_id ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
			);
		}

		// Get the bearer token.
		$token = $this->get_bearer_token( $connect_id );

		// Get vector store ID.
		$vector_store_id = $this->get_vector_store_id( $chatbot_assistant, $token );

		// Create request arguments.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => 'DELETE',
		);

		// If vector store ID is present, delete the file from vector store first.
		if ( '' !== $vector_store_id ) {
			wp_remote_request( $this->api_url . 'vector_stores/' . $vector_store_id . '/files/' . $file_id, $args );
		}

		// Send request.
		$request        = wp_remote_request( $this->api_url . '/files/' . $file_id, $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		// If we should process with deleting the content sources from database.
		$process_delete = false;

		// If the file is not found, process the delete.
		if ( is_array( $request_decode ) && isset( $request_decode['error']['message'] ) && false !== strpos( $request_decode['error']['message'], 'No file found' ) ) {
			$process_delete = true;
		}

		// If content source deleted successfully, process deleting from database.
		if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
			$process_delete = true;
		}

		// Remove the entry from database.
		foreach ( $chatbot_content_sources as $key => $chatbot_content_source ) {
			if ( $chatbot_content_source->file_id === $file_id ) {
				if ( is_array( $chatbot_content_sources ) ) {
					unset( $chatbot_content_sources[ $key ] );
				} else {
					unset( $chatbot_content_sources->$key );
				}
			}
		}

		// Create the chatbot data array.
		$chatbot_data = array(
			'chatbot_id'   => $chatbot_id,
			'chatbot_data' => wp_json_encode( $chatbot_content_sources ),
		);

		// Update the chatbot data.
		wp_flowmattic()->chatbots_db->update( $chatbot_data );

		if ( $process_delete ) {
			// Create request data to retrieve message.
			$request_data = array(
				'status'  => 'success',
				'message' => esc_html__( 'Content source deleted successfully!', 'flowmattic' ),
			);
		} else {
			$request_data = array(
				'status'   => 'error',
				'message'  => isset( $request_decode['error']['message'] ) ? $request_decode['error']['message'] : esc_html__( 'Unknown error occured while deleting content source!', 'flowmattic' ),
			);
		}

		return $request_data;
	}

	/**
	 * Get chatbot.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function get_chatbot( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] ); // @codingStandardsIgnoreLine

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get chatbot name.
		$chatbot_name = $chatbot_settings->chatbot_name;

		return array(
			'status'      => 'success',
			'chatbotName' => $chatbot_name,
		);
	}

	/**
	 * Get threads.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function get_threads( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] );

		// Get threads from database that belongs to the chatbot.
		$threads = wp_flowmattic()->chatbot_threads_db->get_threads_by_chatbot( $chatbot_id );

		// Flip the array to get the latest thread first.
		$threads = array_reverse( $threads );

		if ( ! empty( $threads ) ) {
			return array(
				'status'  => 'success',
				'message' => esc_html__( 'Threads fetched successfully!', 'flowmattic' ),
				'threads' => $threads,
			);
		} else {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'No threads found!', 'flowmattic' ),
				'threads' => array(),
			);
		}
	}

	/**
	 * Get thread.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function get_thread( $settings ) {
		// Get the chatbot ID.
		$chatbot_id = base64_decode( $settings['chatbotID'] );

		// Get the thread ID.
		$thread_id = $settings['threadID'];

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get the assistant ID.
		$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

		// Get the connect ID.
		$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

		// Get chatbot name.
		$chatbot_name = $chatbot_settings->chatbot_name;

		// If assistant ID or connect ID is not present, return error.
		if ( '' === $connect_id ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
			);
		}

		// Get the bearer token.
		$token = $this->get_bearer_token( $connect_id );

		// Create request data to retrieve message.
		$request_data = array(
			'limit' => 30,
			'order' => 'asc',
		);

		// Create request arguments.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => $request_data,
		);

		// Send request.
		$request        = wp_remote_get( $this->api_url . 'threads/' . $thread_id . '/messages', $args );
		$request_body   = wp_remote_retrieve_body( $request );

		// Remove the sources from the AI message.
		$request_body = preg_replace('/【\d+(:\d+)?+†(.*?)】/', '', $request_body);

		// Replace HTML markdown strings - ```html and ``` with empty string.
		$request_body = str_replace( '```html', '', $request_body );
		$request_body = str_replace( '```', '', $request_body );

		$request_decode = json_decode( $request_body, true );

		if ( isset( $request_decode['data'] ) ) {
			return array(
				'status'    => 'success',
				'message'   => esc_html__( 'Messages fetched successfully!', 'flowmattic' ),
				'messages'  => $request_decode['data'],
				'chatbot'   => $chatbot_name,
				'thread_id' => $thread_id,
			);
		} else {
			return array(
				'status'   => 'error',
				'message'  => esc_html__( 'Error fetching messages!', 'flowmattic' ),
				'messages' => array(),
				'chatbot'  => $chatbot_name,
			);
		}
	}

	/**
	 * Get the bearer token.
	 *
	 * @access public
	 * @since 4.0
	 * @param string $connect_id The connect ID.
	 * @return array
	 */
	public function get_bearer_token( $connect_id ) {
		// Get the connect data.
		$connect_args = array(
			'connect_id' => $connect_id,
		);

		// Get the connect data from db.
		$connect = wp_flowmattic()->connects_db->get( $connect_args );

		// Get the connect data.
		$connect_data = $connect->connect_settings;

		// Get the token.
		$token = $connect_data['auth_bearer_token'];

		return $token;
	}

	/**
	 * Create AI assistant.
	 *
	 * @access public
	 * @since 5.0
	 * @return array
	 */
	public function chatbot_create_ai_assistant() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_create_assistant', 'security' );

		// Get the assistant name.
		$assistant_name = sanitize_text_field( $_POST['assistant_name'] );

		// Get the assistant description.
		$assistant_description = sanitize_text_field( $_POST['assistant_description'] );

		// Get Connect ID.
		$connect_id = sanitize_text_field( $_POST['connect_id'] );

		// Generate unique chatbot id.
		$chatbot_id = flowmattic_random_string( 10 );

		// Create assistant in OpenAI.
		$assistant_data = $this->chatbot_create_assistant( true );
		$assistant_data = json_decode( $assistant_data, true );

		// Get the assistant ID.
		$assistant_id = $assistant_data['assistant_id'];

		// Prepare the array to save to database.
		$assistant_data = array(
			'chatbot_name'        => $assistant_name,
			'chatbot_connect'     => $connect_id,
			'chatbot_description' => $assistant_description,
			'chatbot_assistant'   => $assistant_id,
			'chatbot_model'       => 'gpt-4-turbo',
		);

		$args = array(
			'chatbot_id'       => esc_attr( $chatbot_id ),
			'chatbot_name'     => esc_attr( $assistant_name ),
			'chatbot_data'     => wp_json_encode( array() ),
			'chatbot_settings' => wp_json_encode( $assistant_data ),
			'chatbot_actions'  => wp_json_encode( array() ),
			'chatbot_styles'   => wp_json_encode( array() ),
		);

		$chatbots_db = wp_flowmattic()->chatbots_db;
		$chatbots_db->insert( $args );

		echo wp_json_encode(
			array(
				'status'       => 'success',
				'message'      => esc_html__( 'AI Assistant created successfully!', 'flowmattic' ),
				'assistant_id' => $chatbot_id,
			)
		);

		die();
	}

	/**
	 * Delete AI assistant.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function flowmattic_delete_assistant() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_delete_assistant', 'security' );

		// Get the assistant ID.
		$ai_assistant_id = sanitize_text_field( base64_decode( $_POST['assistant_id'] ) );

		// Get the chatbot.
		$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $ai_assistant_id );

		// Get the first chatbot.
		$chatbot = $chatbot[0];

		// Get the chatbot settings.
		$chatbot_settings = json_decode( $chatbot->chatbot_settings );

		// Get the assistant ID.
		$assistant_id = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

		// Get the connect ID.
		$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

		// If assistant ID or connect ID is not present, return error.
		if ( '' === $connect_id ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
				)
			);
			die();
		}

		// Get the bearer token.
		$token = $this->get_bearer_token( $connect_id );

		// Create request arguments.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => 'DELETE',
		);

		// Check if vector store is available for this assistant, delete it.
		$vector_store_id = $this->get_vector_store_id( $assistant_id, $token );

		if ( '' !== $vector_store_id ) {
			wp_remote_request( $this->api_url . 'vector_stores/' . $vector_store_id, $args );
		}

		// Send request to delete assistant in OpenAI.
		$request        = wp_remote_request( $this->api_url . 'assistants/' . $assistant_id, $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		// Delete the chatbot from database.
		wp_flowmattic()->chatbots_db->delete( array( 'chatbot_id' => $ai_assistant_id ) );

		echo wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'AI Assistant deleted successfully!', 'flowmattic' ),
			)
		);

		die();
	}

	/**
	 * Create assistant ajax.
	 *
	 * @access public
	 * @since 4.0
	 * @param bool $bypass Bypass nonce check if call is from dashboard.
	 * @return void
	 */
	public function chatbot_create_assistant( $bypass = false ) {
		// Verify nonce.
		if ( ! $bypass ) {
			check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );
		}

		$response = '';

		if ( ! empty( $_POST ) ) {
			$data = $_POST;

			// Get the connect ID.
			$connect_id = $data['connect_id'];

			// Get the bearer token.
			$token = $this->get_bearer_token( $connect_id );

			$with_retrieval = array(
				array(
					'type' => 'file_search',
				),
			);

			$without_retrieval = array();

			// Create a vector store for this assistant.
			$vector_store_id = $this->create_vector_store( $token, esc_attr( $data['assistant_name'] ) );

			// Generate the array data to send to OpenAI for creating assistant.
			$request_data = array(
				'name'         => esc_attr( $data['assistant_name'] ),
				'instructions' => isset( $data['assistant_instructions'] ) ? esc_attr( $data['assistant_instructions'] ) : '',
				'description'  => isset( $data['assistant_description'] ) ? esc_attr( $data['assistant_description'] ) : '',
				'model'        => isset( $data['assistant_model'] ) ? esc_attr( $data['assistant_model'] ) : 'gpt-4-turbo',
				'tools'        => $with_retrieval,
			);

			// If vector store created successfully, add the vector store ID to the request data.
			if ( '' !== $vector_store_id ) {
				$request_data['tool_resources'] = array(
					'file_search' => array(
						'vector_store_ids' => array( $vector_store_id ),
					),
				);
			}

			// Create the assistant.
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode( $request_data ),
			);

			// Send request.
			$request        = wp_remote_post( $this->api_url . 'assistants', $args );
			$request_body   = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request_body, true );

			// If assistant created successfully, update the assistant data.
			if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
				$response = wp_json_encode(
					array(
						'status'         => 'success',
						'message'        => esc_html__( 'Assistant created successfully!', 'flowmattic' ),
						'assistant_id'   => $request_decode['id'],
						'assistant_name' => $request_decode['name'],
					)
				);
			} else {
				$response = wp_json_encode(
					array(
						'success'  => false,
						'status'   => 'error',
						'message'  => esc_html__( 'Error creating assistant. Please try again.', 'flowmattic' ),
						'response' => $request_decode,
					)
				);
			}
		} else {
			$response = wp_json_encode(
				array(
					'success' => false,
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		if ( ! $bypass ) {
			echo $response;
			wp_die();
		} else {
			return $response;
		}
	}

	/**
	 * Refresh assistants ajax.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function chatbot_refresh_assistants() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$data = $_POST;

			// Get the connect ID.
			$connect_id = $data['connect_id'];

			// Get the bearer token.
			$token = $this->get_bearer_token( $connect_id );

			// Get the assistants.
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
			);

			// Send request.
			$request        = wp_remote_get( $this->api_url . 'assistants', $args );
			$request_body   = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request_body, true );

			// If assistants fetched successfully, update the assistant data.
			if ( is_array( $request_decode ) && isset( $request_decode['data'] ) ) {
				$assistants = $request_decode['data'];

				// Return the assistants.
				echo wp_json_encode(
					array(
						'status'     => 'success',
						'message'    => esc_html__( 'Assistants fetched successfully!', 'flowmattic' ),
						'assistants' => $assistants,
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'success'  => false,
						'status'   => 'error',
						'message'  => esc_html__( 'Error fetching assistants. Please try again.', 'flowmattic' ),
						'response' => $request_decode,
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to save the chatbot settings.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function chatbot_insert_or_update() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$chatbots_db = wp_flowmattic()->chatbots_db;
			$data        = $_POST;

			// Remove the nonce.
			unset( $data['workflow_nonce'] );

			// Remove the action.
			unset( $data['action'] );

			// Get chatbot settings.
			$chatbot_settings = $data['chatbot_settings'];

			// Update the assistant.
			$assistant_updated = $this->update_assistant( $chatbot_settings );

			// Generate the array data to store in database.
			$args = array(
				'chatbot_id'       => esc_attr( $data['chatbot_id'] ),
				'chatbot_name'     => esc_attr( $data['chatbot_name'] ),
				'chatbot_data'     => isset( $data['chatbot_data'] ) && is_array( $data['chatbot_data'] ) && ! empty( $data['chatbot_data'] ) ? wp_json_encode( $data['chatbot_data'] ) : '',
				'chatbot_settings' => isset( $data['chatbot_settings'] ) && is_array( $data['chatbot_settings'] ) && ! empty( $data['chatbot_settings'] ) ? wp_json_encode( $data['chatbot_settings'] ) : '',
				'chatbot_actions'  => isset( $data['chatbot_actions'] ) && is_array( $data['chatbot_actions'] ) && ! empty( $data['chatbot_actions'] ) ? wp_json_encode( array( $data['chatbot_actions'] ) ) : '',
				'chatbot_styles'   => isset( $data['chatbot_styles'] ) && is_array( $data['chatbot_styles'] ) && ! empty( $data['chatbot_styles'] ) ? wp_json_encode( $data['chatbot_styles'] ) : '',
			);

			// Check if chatbot ID is present, else insert.
			if ( empty( $chatbots_db->get_chatbot_by_id( $data['chatbot_id'] ) ) ) {
				$chatbot_id = $chatbots_db->insert( $args );
			} else {
				// To avoid replace data with empty data, remove the blank data items.
				foreach ( $args as $key => $value ) {
					if ( '' === $value ) {
						unset( $args[ $key ] );
					}
				}

				// If chatbot ID present in database, update.
				$chatbots_db->update( $args );
			}

			echo wp_json_encode(
				array(
					'status'            => 'success',
					'message'           => esc_html__( 'Saved successfully!', 'flowmattic' ),
					'chatbot_id'        => $data['chatbot_id'],
					'assistant_updated' => $assistant_updated,
				)
			);
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Get chatbot settings for editing.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function get_chatbot_settings() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( isset( $_POST['chatbot_id'] ) ) {
			$chatbot_id = sanitize_text_field( wp_unslash( $_POST['chatbot_id'] ) );

			// Get the chatbot data.
			$args = array(
				'chatbot_id' => $chatbot_id,
			);

			$chatbot       = wp_flowmattic()->chatbots_db->get( $args );
			$response_data = wp_json_encode( $chatbot );
		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid chatbot ID. Please try refreshing the page, or contact support', 'flowmattic' ),
				)
			);
		}

		// Print the JSON for Ajax.
		wp_send_json( $response_data );

		wp_die();
	}

	/**
	 * Create a thread.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function create_thread( $settings ) {
		if ( ! empty( $settings ) ) {
			// Get the chatbot ID.
			$chatbot_id = base64_decode( $settings['chatbotID'] ); // @codingStandardsIgnoreLine

			// Get the message.
			$message = $settings['message'];

			// Create a title from the message, remove the HTML tags, and limit to 7 words.
			$message = wp_trim_words( wp_strip_all_tags( $message ), 7 );

			// Update the thread data.
			$thread_data = array(
				'title' => $message,
			);

			// Get the chatbot.
			$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

			// Get the first chatbot.
			$chatbot = $chatbot[0];

			// Get the chatbot settings.
			$chatbot_settings = json_decode( $chatbot->chatbot_settings );

			// Get the assistant ID.
			$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

			// Get the connect ID.
			$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

			// Get chatbot name.
			$chatbot_name = $chatbot_settings->chatbot_name;

			// If assistant ID or connect ID is not present, return error.
			if ( '' === $chatbot_assistant || '' === $connect_id ) {
				return array(
					'success' => false,
					'status'  => 'error',
					'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
				);
			}

			// Get the bearer token.
			$token = $this->get_bearer_token( $connect_id );

			// Get vector store ID.
			$vector_store_id = $this->get_vector_store_id( $chatbot_assistant, $token );

			// Generate the array data to send to OpenAI for creating assistant.
			$request_data = array();

			// If vector store ID is present, add it to the request data.
			if ( '' !== $vector_store_id ) {
				$request_data = array(
					'tool_resources' => array(
						'file_search' => array(
							'vector_store_ids' => array( $vector_store_id ),
						),
					),
				);
			}

			// Create the assistant.
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode( $request_data ),
			);

			// Send request.
			$request        = wp_remote_post( $this->api_url . 'threads', $args );
			$request_body   = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request_body, true );

			// If assistant created successfully, update the assistant data.
			if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
				// Add the thread to database.
				$thread_args = array(
					'chatbot_id'   => $chatbot_id,
					'thread_id'    => $request_decode['id'],
					'assistant_id' => $chatbot_assistant,
					'thread_data'  => $thread_data,
					'thread_time'  => date_i18n( 'Y-m-d H:i:s' ),
				);

				// Insert the thread.
				wp_flowmattic()->chatbot_threads_db->insert( $thread_args );

				return array(
					'status'    => 'success',
					'message'   => esc_html__( 'Thread created successfully!', 'flowmattic' ),
					'thread_id' => $request_decode['id'],
					'bot_name'  => $chatbot_name,
				);
			} else {
				return array(
					'success'  => false,
					'status'   => 'error',
					'message'  => esc_html__( 'Error creating thread. Please try again.', 'flowmattic' ),
					'response' => $request_body,
				);
			}
		} else {
			return array(
				'success' => false,
				'status'  => 'error',
				'message' => esc_html__( 'No data received.', 'flowmattic' ),
			);
		}
	}

	/**
	 * Create a message.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function create_message( $settings ) {
		if ( ! empty( $settings ) ) {
			$data = $settings;

			// Get the chatbot ID.
			$chatbot_id = base64_decode( $data['chatbotID'] ); // @codingStandardsIgnoreLine

			// Get the thread ID.
			$thread_id = $data['threadID'];

			// Get the user message.
			$user_message = $data['message'];

			// Get the chatbot.
			$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

			// Get the first chatbot.
			$chatbot = $chatbot[0];

			// Get the chatbot settings.
			$chatbot_settings = json_decode( $chatbot->chatbot_settings );

			// Get the assistant ID.
			$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

			// Get the connect ID.
			$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

			// Get default reply.
			$default_reply = isset( $chatbot_settings->chatbot_default_reply ) ? $chatbot_settings->chatbot_default_reply : '';

			// Default reply prompt - ask AI.
			$default_reply_prompt_ai = 'Before attempting to respond, you have access to the uploaded files as your data source, which you can query. When querying, you are ONLY allowed to respond from information provided by the query. You are NOT ALLOWED to introduce information that is not provided in the results. NEVER try and answer from information outside of the results. If you do not get relevant results from a query, YOU ARE NOT ALLOWED TO TRY TO ANSWER FROM GENERAL KNOWLEDGE. Instead, simply respond with a message letting the user know that you do not have that information.';

			// Default reply prompt.
			$default_reply_prompt_custom = "Before attempting to respond, you have access to the uploaded files as your data source, which you can query. When querying, you are ONLY allowed to respond from information provided by the query. You are NOT ALLOWED to introduce information that is not provided in the results. NEVER try and answer from information outside of the results. If you do not get relevant results from a query, YOU ARE NOT ALLOWED TO TRY TO ANSWER FROM GENERAL KNOWLEDGE. If you don't found the relevant data in the data sources provided, respond with the following message: '" . $default_reply  . "'";

			// Get chatbot name.
			$chatbot_name = $chatbot_settings->chatbot_name;

			// Get the instructions.
			$instructions = isset( $chatbot_settings->chatbot_instructions ) ? $chatbot_settings->chatbot_instructions : '';

			// Get the chatbot actions.
			$chatbot_actions = ! empty( $chatbot ) ? json_decode( $chatbot->chatbot_actions ) : array();

			// If assistant ID or connect ID is not present, return error.
			if ( '' === $chatbot_assistant || '' === $connect_id ) {
				$message_response = array(
					'success' => false,
					'status'  => 'error',
					'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
				);

				return $message_response;
			}

			// Get the bearer token.
			$token = $this->get_bearer_token( $connect_id );

			// Generate the array data to send to OpenAI for creating assistant.
			$request_data = array(
				'role'     => 'user',
				'content'  => $user_message,
				'metadata' => array(
					'platform' => 'FlowMattic',
				),
			);

			// Create the assistant.
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode( $request_data ),
			);

			// Send request.
			$request        = wp_remote_post( $this->api_url . 'threads/' . $thread_id . '/messages', $args );
			$request_body   = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request_body, true );

			// If message created successfully, send the id.
			if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
				// Create run.
				$run_args = array(
					'assistant_id' => $chatbot_assistant,
				);

				// Update the request body.
				$args['body'] = wp_json_encode( $run_args );

				// Send request.
				$run_request        = wp_remote_post( $this->api_url . 'threads/' . $thread_id . '/runs', $args );
				$run_request_body   = wp_remote_retrieve_body( $run_request );
				$run_request_decode = json_decode( $run_request_body, true );

				// If run created successfully, get the id.
				if ( is_array( $run_request_decode ) && isset( $run_request_decode['id'] ) ) {
					$run_id = $run_request_decode['id'];

					// Retrieve the run, check if it is completed, else wait for 5 seconds and check again.
					$run_completed = false;

					// Remove the body from request arguments.
					unset( $args['body'] );

					// Wait for 5 sec.
					flowmattic_delay( 3 );

					// Set the wait time. Starts from 3 sec. Can be customized.
					$wait_time = apply_filters( 'flowmattic_chatbot_request_wait_time', 3 );

					// Loop until run is completed.
					while ( ! $run_completed ) {
						// Send request.
						$run_request        = wp_remote_get( $this->api_url . 'threads/' . $thread_id . '/runs/' . $run_id, $args );
						$run_request_body   = wp_remote_retrieve_body( $run_request );
						$run_request_decode = json_decode( $run_request_body, true );

						// If run created successfully, get the id.
						if ( is_array( $run_request_decode ) && isset( $run_request_decode['status'] ) && 'completed' === $run_request_decode['status'] ) {
							$run_completed = true;
						} else {
							// Log the error.
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								error_log( 'Run not completed yet. Waiting for ' . $wait_time . ' seconds.' );
							}

							// Wait for the wait time.
							flowmattic_delay( $wait_time );
						}
					}

					// Send request.
					$message_request        = wp_remote_get( $this->api_url . 'threads/' . $thread_id . '/messages', $args );
					$message_request_body   = wp_remote_retrieve_body( $message_request );
					$message_request_decode = json_decode( $message_request_body, true );

					$data       = $message_request_decode['data'][0];
					$content    = $data['content'][0];
					$ai_message = $content['text']['value'];

					// Remove the sources from the AI message.
					$ai_message = preg_replace('/【\d+(:\d+)?+†(.*?)】/', '', $ai_message);

					// Replace HTML markdown strings - ```html and ``` with empty string.
					$ai_message = str_replace( '```html', '', $ai_message );
					$ai_message = str_replace( '```', '', $ai_message );

					// If message created successfully, get the data from the first message in response.
					if ( is_array( $message_request_decode ) && isset( $message_request_decode['data'] ) ) {
						// Check if there are actions to perform.
						if ( ! empty( $chatbot_actions ) ) {
							// Loop through each action.
							foreach ( $chatbot_actions as $k => $chatbot_action ) {
								// Get the chatbot action event.
								$chatbot_action_event = isset( $chatbot_action->chatbot_action_event ) ? $chatbot_action->chatbot_action_event : 'chatbot_responded';

								// Get the chatbot action type.
								$chatbot_action_type = isset( $chatbot_action->chatbot_action_type ) ? $chatbot_action->chatbot_action_type : '';

								// Get the chatbot action workflow ID.
								$chatbot_action_workflow_id = isset( $chatbot_action->chatbot_action_workflow_id ) ? $chatbot_action->chatbot_action_workflow_id : '';

								// Get the chatbot action webhook URL.
								$chatbot_action_webhook_url = isset( $chatbot_action->chatbot_action_webhook_url ) ? $chatbot_action->chatbot_action_webhook_url : '';

								// See if user is logged in.
								$is_user_logged_in = is_user_logged_in() ? 'yes' : 'no';

								// Get the user ID and email if user is logged in.
								if ( 'yes' === $is_user_logged_in ) {
									$user_id    = get_current_user_id();
									$user_email = get_the_author_meta( 'user_email', $user_id );
								} else {
									$user_id    = '';
									$user_email = '';
								}

								// Create the response array.
								$response_array = array(
									'status'              => 'success',
									'action'              => esc_html__( 'Chatbot response received', 'flowmattic' ),
									'response'            => wp_json_encode( $message_request_decode['data'] ),
									'user_latest_message' => $user_message,
									'ai_latest_response'  => $ai_message,
									'first_message_id'    => $message_request_decode['first_id'],
									'last_message_id'     => $message_request_decode['last_id'],
									'user_logged_in'      => $is_user_logged_in,
									'user_id'             => $user_id,
									'user_email'          => $user_email,
								);

								// Switch between actions.
								switch ($chatbot_action_type) {
									case 'trigger_workflow':
										if ( '' !== $chatbot_action_workflow_id ) {
											// Trigger the workflow.
											do_action(
												'flowmattic_trigger_workflow', // Workflow trigger action.
												$chatbot_action_workflow_id,   // Workflow ID.
												$response_array,               // Data to be passed to the Workflow.
											);
										}
										break;
									
									case 'send_to_webhook':
										if ( '' !== $chatbot_action_webhook_url ) {
											$args = array(
												'headers'   => array(
													'Accept'       => 'application/json',
													'Content-Type' => 'application/json',
													'X-User-Agent' => 'FlowMattic',
												),
												'sslverify' => false,
												'timeout'   => 10,
												'body'      => wp_json_encode( $response_array ),
												'blocking'  => false,
											);
									
											// Send webhook request.
											wp_remote_post( $chatbot_action_webhook_url, $args );
										}
										break;
								}
							}
						}

						$message_response = array(
							'status'     => 'success',
							'message'    => esc_html__( 'Message created successfully!', 'flowmattic' ),
							'thread_id'  => $thread_id,
							'message_id' => $request_decode['id'],
							'bot_name'   => $chatbot_name,
							'content'    => $ai_message,
						);
					}
				} else {
					$message_response = array(
						'success'  => false,
						'status'   => 'error',
						'message'  => esc_html__( 'Error creating run. Please try again.', 'flowmattic' ),
						'response' => $run_request_body,
					);
				}
			} else {
				$message_response = array(
					'success'  => false,
					'status'   => 'error',
					'message'  => esc_html__( 'Error creating message. Please try again.', 'flowmattic' ),
					'response' => $request_body,
				);
			}
		} else {
			$message_response = array(
				'success' => false,
				'status'  => 'error',
				'message' => esc_html__( 'No data received.', 'flowmattic' ),
			);
		}

		return $message_response;
	}

	/**
	 * Create a run.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $settings The settings.
	 * @return array
	 */
	public function create_run( $settings ) {
		// Verify nonce.
		// check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $settings ) ) {
			$data = $settings;

			// Get the thread ID.
			$thread_id = $data['threadID'];

			// If thread ID is not empty, create a message.
			if ( '' !== $thread_id ) {
				$message_response = $this->create_message( $settings );

				return $message_response;
			}

			// Get the chatbot ID.
			$chatbot_id = base64_decode( $data['chatbotID'] ); // @codingStandardsIgnoreLine

			// Get the user message.
			$message = $data['message'];

			// Get the chatbot.
			$chatbot = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

			// Get the first chatbot.
			$chatbot = $chatbot[0];

			// Get the chatbot settings.
			$chatbot_settings = json_decode( $chatbot->chatbot_settings );

			// Get the assistant ID.
			$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

			// Get the connect ID.
			$connect_id = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

			// Get chatbot name.
			$chatbot_name = $chatbot_settings->chatbot_name;

			// If assistant ID or connect ID is not present, return error.
			if ( '' === $chatbot_assistant || '' === $connect_id ) {
				return array(
					'success' => false,
					'status'  => 'error',
					'message' => esc_html__( 'Chatbot authentication error. Please check chatbot settings.', 'flowmattic' ),
				);
			}

			// Get the bearer token.
			$token = $this->get_bearer_token( $connect_id );

			// Create message array.
			$messages = array(
				'role'    => 'user',
				'content' => $message,
			);

			// Create thread array.
			$thread_data = array(
				'messages' => array(
					$messages,
				),
			);

			// Generate the array data to send to OpenAI for creating assistant.
			$request_data = array(
				'assistant_id' => $chatbot_assistant,
				'thread'       => $thread_data,
				'instructions' => "Before attempting to respond, you have datasources you can query. When querying, you are ONLY allowed to respond from information provided by the query. You are NOT ALLOWED to introduce information that is not provided in the results. NEVER try and answer from information outside of the results. If a query does not return sufficient results to answer a user's question, DO NOT APOLOGIZE, DO NOT ATTEMPT TO HELP FURTHER, AND DO NOT EXPLAIN. Respond with exactly the following message: 'Sorry, I don’t know.'",
			);

			// Create the assistant.
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode( $request_data ),
			);

			// Send request to create thread and run.
			$run_request        = wp_remote_post( $this->api_url . 'threads/runs', $args );
			$run_request_body   = wp_remote_retrieve_body( $run_request );
			$run_request_decode = json_decode( $run_request_body, true );

			// If run created successfully, get the id.
			if ( is_array( $run_request_decode ) && isset( $run_request_decode['id'] ) ) {
				$run_id    = $run_request_decode['id'];
				$thread_id = $run_request_decode['thread_id'];

				// Retrieve the run, check if it is completed, else wait for 5 seconds and check again.
				$run_completed = false;

				// Remove the body from request arguments.
				unset( $args['body'] );

				while ( ! $run_completed ) {
					// Send request.
					$run_request        = wp_remote_get( $this->api_url . 'threads/' . $thread_id . '/runs/' . $run_id, $args );
					$run_request_body   = wp_remote_retrieve_body( $run_request );
					$run_request_decode = json_decode( $run_request_body, true );

					// If run created successfully, get the id.
					if ( is_array( $run_request_decode ) && isset( $run_request_decode['status'] ) && 'completed' === $run_request_decode['status'] ) {
						$run_completed = true;
					} else {
						usleep( 500000 );
					}
				}

				// Send request to create message.
				$message_request        = wp_remote_get( $this->api_url . 'threads/' . $thread_id . '/messages', $args );
				$message_request_body   = wp_remote_retrieve_body( $message_request );
				$message_request_decode = json_decode( $message_request_body, true );

				// If message created successfully, get the data from the first message in response.
				if ( is_array( $message_request_decode ) && isset( $message_request_decode['data'] ) ) {
					$data    = $message_request_decode['data'][0];
					$content = $data['content'][0];
					$content = $content['text']['value'];

					$response = array(
						'status'    => 'success',
						'message'   => esc_html__( 'Message created successfully!', 'flowmattic' ),
						'thread_id' => $thread_id,
						'bot_name'  => $chatbot_name,
						'content'   => $content,
					);
				}
			} else {
				$response = array(
					'success'  => false,
					'status'   => 'error',
					'message'  => esc_html__( 'Error creating run. Please try again.', 'flowmattic' ),
					'response' => $run_request_body,
				);
			}
		} else {
			$response = array(
				'success' => false,
				'status'  => 'error',
				'message' => esc_html__( 'No data received.', 'flowmattic' ),
			);
		}

		return $response;
	}

	/**
	 * Update the chatbot assistant.
	 *
	 * @access public
	 * @since 4.0
	 * @param string $assistant_settings The assistant settings.
	 * @return bool
	 */
	public function update_assistant( $assistant_settings ) {
		// Get the assistant ID.
		$assistant_id = $assistant_settings['chatbot_assistant'];

		// Get the connect ID.
		$connect_id = $assistant_settings['chatbot_connect'];

		// Get the bearer token.
		$token = $this->get_bearer_token( $connect_id );

		// Initialize the request data.
		$request_data = array();

		// Add model if present and is not empty.
		if ( isset( $assistant_settings['chatbot_model'] ) && '' !== $assistant_settings['chatbot_model'] ) {
			$request_data['model'] = esc_attr( $assistant_settings['chatbot_model'] );
		}

		// Add instructions if present and is not empty.
		if ( isset( $assistant_settings['chatbot_instructions'] ) && '' !== $assistant_settings['chatbot_instructions'] ) {
			$request_data['instructions'] = esc_attr( $assistant_settings['chatbot_instructions'] );
		}

		// Update the assistant.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request        = wp_remote_post( $this->api_url . 'assistants/' . $assistant_id, $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		// If assistant updated successfully, update the assistant data.
		if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create vector store.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $token The bearer token.
	 * @param string $name The name of the vector store.
	 * @return string
	 */
	public function create_vector_store( $token, $name ) {
		// Initialize the request data.
		$request_data = array(
			'name' => $name,
		);

		// Create the vector store.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request        = wp_remote_post( $this->api_url . 'vector_stores', $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		// If vector store created successfully, return the ID.
		if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
			return $request_decode['id'];
		} else {
			return '';
		}
	}

	/**
	 * Get assistant.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $assistant_id The assistant ID.
	 * @param string $token        The bearer token.
	 * @return string
	 */
	public function get_assistant( $assistant_id, $token ) {
		// Get the assistant.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
		);

		// Send request.
		$request        = wp_remote_get( $this->api_url . 'assistants/' . $assistant_id, $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		return $request_decode;
	}

	/**
	 * Get vector store ID.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $assistant_id The assistant ID.
	 * @param string $token        The bearer token.
	 * @return string
	 */
	public function get_vector_store_id( $assistant_id, $token ) {
		// Get the assistant.
		$assistant = $this->get_assistant( $assistant_id, $token );

		// If assistant is not present, return empty.
		if ( empty( $assistant ) ) {
			return '';
		}

		// Check if assistant has tool_resources.
		if ( ! isset( $assistant['tool_resources'] ) ) {
			return '';
		}

		// Get the tool resources.
		$tool_resources = $assistant['tool_resources'];

		// Get the vector store ID.
		$vector_store_id = isset( $tool_resources['file_search']['vector_store_ids'] ) ? $tool_resources['file_search']['vector_store_ids'][0] : '';

		return $vector_store_id;
	}

	/**
	 * Add file to vector store.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $assistant_id The assistant ID.
	 * @param string $file_id      The file ID.
	 * @param string $token        The bearer token.
	 * @return string
	 */
	public function add_file_to_vector_store( $assistant_id, $file_id, $token ) {
		// Get the vector store ID.
		$vector_store_id = $this->get_vector_store_id( $assistant_id, $token );

		// If vector store ID is not present, return empty.
		if ( '' === $vector_store_id ) {
			return '';
		}

		// Initialize the request data.
		$request_data = array(
			'file_id' => $file_id,
		);

		// Add the file to vector store.
		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'OpenAI-Beta'   => 'assistants=v2',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request        = wp_remote_post( $this->api_url . 'vector_stores/' . $vector_store_id . '/files', $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		// If file added to vector store successfully, return the ID.
		if ( is_array( $request_decode ) && isset( $request_decode['id'] ) ) {
			return $request_decode['id'];
		} else {
			return '';
		}
	}
}
