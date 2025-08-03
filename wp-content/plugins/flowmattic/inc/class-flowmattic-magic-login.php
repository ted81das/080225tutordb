<?php
/**
 * Generates one-time use magic login links for users with custom redirect support.
 * Version: 5.0
 *
 * @package FlowMattic/Magic_Login
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class FlowMattic_Magic_Login
 *
 * Handles the generation and validation of one-time magic login links.
 *
 * @since 5.0
 */
class FlowMattic_Magic_Login {

	/**
	 * Transient prefix for storing magic login data.
	 *
	 * @var string
	 * @since 5.0
	 */
	const TRANSIENT_PREFIX = '_magic_login_';

	/**
	 * Expiration time (in seconds) for the magic link.
	 * One hour by default.
	 *
	 * @var int
	 * @since 5.0
	 */
	const EXPIRATION_TIME = HOUR_IN_SECONDS;

	/**
	 * The Constructor.
	 *
	 * @since 5.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'handle_magic_login_request' ) );
	}

	/**
	 * Generate a one-time magic login link for a given user with optional redirect URL.
	 *
	 * @since 5.0
	 * @access public
	 * @param int    $user_id                 User ID.
	 * @param string $redirect_url            Optional. URL to redirect the user after login.
	 * @param string $redirect_url_on_expired Optional. URL to redirect the user if the magic link has expired.
	 * @return string The magic login URL.
	 */
	public function get_magic_login_link( $user_id, $redirect_url = '', $redirect_url_on_expired = '' ) {
		// Check if user ID is email or ID.
		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );
			if ( ! $user ) {
				return '';
			}
			$user_id = $user->ID;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return '';
		}

		// Generate a random token.
		$raw_token = wp_generate_password( 32, false );

		// Set expiration to one hour from now.
		$expiration = time() + self::EXPIRATION_TIME;

		// Sanitize redirect URL if provided.
		$redirect_url = ! empty( $redirect_url ) ? esc_url_raw( $redirect_url ) : '';

		// Sanitize redirect URL on expiration if provided.
		$redirect_url_on_expired = ! empty( $redirect_url_on_expired ) ? esc_url_raw( $redirect_url_on_expired ) : '';

		// Create a transient key using a hash of the token.
		$transient_key = self::TRANSIENT_PREFIX . md5( $raw_token );

		// Store user data, expiration, and redirect in the transient.
		set_transient(
			$transient_key,
			array(
				'user_id'                 => $user_id,
				'expiration'              => $expiration,
				'redirect_url'            => $redirect_url,
				'redirect_url_on_expired' => $redirect_url_on_expired,
			),
			self::EXPIRATION_TIME
		);

		// Build the link with the token.
		$link = add_query_arg(
			array(
				'magic_login' => rawurlencode( $raw_token ),
			),
			home_url()
		);

		return esc_url( $link );
	}

	/**
	 * Handle magic login requests.
	 *
	 * @since 5.0
	 * @access public
	 * @return void
	 */
	public function handle_magic_login_request() {
		// Check if this is a magic login request.
		if ( isset( $_GET['magic_login'] ) ) {
			$raw_token = sanitize_text_field( wp_unslash( $_GET['magic_login'] ) );

			if ( empty( $raw_token ) ) {
				$this->redirect_to_login();
			}

			$transient_key = self::TRANSIENT_PREFIX . md5( $raw_token );
			$data          = get_transient( $transient_key );

			// Get the url after the magic link has expired.
			$redirect_url_on_expired = isset( $data['redirect_url_on_expired'] ) ? $data['redirect_url_on_expired'] : home_url();

			if ( ! $data || ! isset( $data['user_id'], $data['expiration'] ) ) {
				// No matching data or incomplete data.
				$this->redirect_to_login( $redirect_url_on_expired );
			}

			$user_id      = (int) $data['user_id'];
			$expiration   = (int) $data['expiration'];
			$redirect_url = isset( $data['redirect_url'] ) ? $data['redirect_url'] : '';

			if ( time() > $expiration ) {
				// Token expired.
				delete_transient( $transient_key );
				$this->redirect_to_login( $redirect_url_on_expired );
			}

			$user = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				// User no longer exists.
				delete_transient( $transient_key );
				$this->redirect_to_login( $redirect_url_on_expired );
			}

			// Valid token: log the user in.
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, false, is_ssl() );

			// Invalidate the token immediately.
			delete_transient( $transient_key );

			// Redirect user to the provided redirect URL if set, otherwise home.
			if ( ! empty( $redirect_url ) ) {
				wp_safe_redirect( $redirect_url );
			} else {
				wp_safe_redirect( home_url() );
			}

			exit;
		}
	}

	/**
	 * Redirect to the default login page.
	 *
	 * @since 5.0
	 * @access private
	 * @param string $redirect_url Optional. URL to redirect the user after login.
	 * @return void
	 */
	private function redirect_to_login( $redirect_url = '' ) {
		$redirect_url = ! empty( $redirect_url ) ? $redirect_url : wp_login_url();

		// Redirect to the given URL or the login page.
		wp_safe_redirect( $redirect_url );
		exit;
	}
}
