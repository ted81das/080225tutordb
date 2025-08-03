<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Email_Parser {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for maths.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'email_parser',
			array(
				'name'         => esc_attr__( 'Email Parser by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/email-parser/icon.svg',
				'instructions' => 'Send your emails to the above email. FlowMattic will parse your email and return the data. Attachments upto 5MB are supported.',
				'actions'      => array(),
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		// Ajax to register the email parser email.
		add_action( 'wp_ajax_flowmattic_register_email_parser', array( $this, 'register_email_parser' ) );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-email-parser', FLOWMATTIC_PLUGIN_URL . 'inc/apps/email-parser/view-email-parser.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Register email parser.
	 *
	 * @access public
	 * @since 4.3.0
	 * @return void
	 */
	public function register_email_parser() {
		// Check the nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the workflow ID.
		$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

		// Get the action.
		$action = isset( $_POST['app_action'] ) ? sanitize_text_field( wp_unslash( $_POST['app_action'] ) ) : '';

		// Get the email parser email.
		$email_parser_email = 'email_received' === $action ? self::get_mailhook_url( $workflow_id ) : self::get_mailhook_url_v2( $workflow_id );

		$endpoint = 'email_received' === $action ? 'https://api.flowmattic.com/mail-parser/' : 'https://api.flowmattic.com/mail-parser-v2/';

		// Send request to server.
		$response = wp_remote_post(
			$endpoint,
			array(
				'body'    => wp_json_encode(
					array(
						'email' => $email_parser_email,
					)
				),
				'headers' => array(
					'Content-Type'         => 'application/json',
					'X-FlowMattic-Email'   => $email_parser_email,
					'X-FlowMattic-Webhook' => FlowMattic_Webhook::get_url( $workflow_id ),
					'User-Agent'           => 'FlowMattic',
				),
			)
		);

		$response_body = wp_remote_retrieve_body( $response );

		wp_send_json_success( $response_body );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'email_received' => array(
				'title'       => esc_attr__( 'Email Received', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an email is received on the email parser email.', 'flowmattic' ),
			),
			'email_received_v2' => array(
				'title'       => esc_attr__( 'Email Received (v2)', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an email is received on the email parser v2 email.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Get mailhook URL.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $workflow_id Workflow ID.
	 * @return string
	 */
	public static function get_mailhook_url( $workflow_id ) {
		// Get the full URL of the current site.
		$site_url = home_url();

		// Remove the protocol (http:// or https://).
		$url_without_protocol = preg_replace( '(^https?://)', '', $site_url );

		// Remove the trailing slash if it exists.
		$url_without_trailing_slash = rtrim( $url_without_protocol, '/' );

		// Filter the url for customization.
		$url_without_trailing_slash = apply_filters( 'flowmattic_webhook_url', $url_without_trailing_slash );

		$encoded_domain = base64_encode( $url_without_trailing_slash ); // @codingStandardsIgnoreLine
		$encoded_domain = str_replace( '=', '', $encoded_domain );
		$mailhook_url   = $workflow_id . $encoded_domain . '@flowmatticmail.com';

		// Get the license key.
		$license_key = get_option( 'flowmattic_license_key', '' );

		// Check the license.
		$license = wp_flowmattic()->check_license();

		// If the license is not valid, redirect to the license page.
		if ( ! $license || '' === $license_key ) {
			$mailhook_url = 'License key expired. In order to use Email Parser, you need a valid license key';
		}

		return $mailhook_url;
	}

	/**
	 * Get mailhook URL v2.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param string $workflow_id Workflow ID.
	 * @return string
	 */
	public static function get_mailhook_url_v2( $workflow_id ) {
		// Get the full URL of the current site.
		$site_url = home_url();

		// Remove the protocol (http:// or https://).
		$url_without_protocol = preg_replace( '(^https?://)', '', $site_url );

		// Remove the trailing slash if it exists.
		$url_without_trailing_slash = rtrim( $url_without_protocol, '/' );

		// Filter the url for customization.
		$url_without_trailing_slash = apply_filters( 'flowmattic_webhook_url', $url_without_trailing_slash );

		$encoded_domain = base64_encode( $url_without_trailing_slash ); // @codingStandardsIgnoreLine
		$encoded_domain = str_replace( '=', '', $encoded_domain );
		$mailhook_url   = strtolower( $workflow_id . $encoded_domain ) . '@fmparser.work';

		// Get the license key.
		$license_key = get_option( 'flowmattic_license_key', '' );

		// Check the license.
		$license = wp_flowmattic()->check_license();

		// If the license is not valid, redirect to the license page.
		if ( ! $license || '' === $license_key ) {
			$mailhook_url = 'License key expired. In order to use Email Parser, you need a valid license key';
		}

		return $mailhook_url;
	}
}

new FlowMattic_Email_Parser();
