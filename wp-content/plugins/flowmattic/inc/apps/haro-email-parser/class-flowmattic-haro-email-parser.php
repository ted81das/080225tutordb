<?php
/**
 * HARO Email Parser by FlowMattic.
 *
 * @package FlowMattic\Haro_Email_Parser
 * @since 4.0
 * @version 1.0
 * @link https://flowmattic.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HARO Email Parser by FlowMattic.
 */
class FlowMattic_Haro_Email_Parser {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for maths.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'haro_email_parser',
			array(
				'name'         => esc_attr__( 'HARO Email Parser', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/haro-email-parser/icon.svg',
				'instructions' => 'Send your HARO emails to the above email. FlowMattic will parse your email and return the data. Attachments upto 5MB are supported.',
				'actions'      => array(),
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-haro-email-parser', FLOWMATTIC_PLUGIN_URL . 'inc/apps/haro-email-parser/view-haro-email-parser.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'email_received' => array(
				'title'       => esc_attr__( 'Email Received', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an email is received on the email parser email.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.0
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

		$encoded_domain = base64_encode( $url_without_trailing_slash ); // @codingStandardsIgnoreLine
		$encoded_domain = str_replace( '=', '', $encoded_domain );
		$mailhook_url   = 'haro' . $workflow_id . $encoded_domain . '@flowmatticmail.com';

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

new FlowMattic_Haro_Email_Parser();
