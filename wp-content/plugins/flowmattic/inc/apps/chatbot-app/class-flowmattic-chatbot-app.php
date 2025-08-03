<?php
/**
 * Application Name: FlowMattic Chatbot
 * Description: Add FlowMattic Chatbot integration to FlowMattic.
 * Version: 4.0
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
 * FlowMattic Chatbot App.
 *
 * @since 4.0
 */
class FlowMattic_Chatbot_App {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Webhook Outgoing.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'chatbot_app',
			array(
				'name'         => esc_attr__( 'FlowMattic Chatbot', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/chatbot-app/icon.svg',
				'instructions' => 'AI powered chatbot for your website.',
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
		wp_enqueue_script( 'flowmattic-app-view-chatbot-app', FLOWMATTIC_PLUGIN_URL . 'inc/apps/chatbot-app/view-chatbot-app.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
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
			'ai_response_received' => array(
				'title'       => esc_attr__( 'AI Response Received', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a response is received from the AI.', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Chatbot_App();
