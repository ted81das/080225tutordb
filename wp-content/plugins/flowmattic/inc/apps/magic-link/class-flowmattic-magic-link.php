<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Magic_Link {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for magic link.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'magic_link',
			array(
				'name'         => esc_attr__( 'Magic Link', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/magic-link/icon.svg',
				'instructions' => '',
				'triggers'     => $this->get_triggers(),
				'base'         => 'core',
				'type'         => 'trigger',
			)
		);

		// Add shortcode for magic link.
		add_shortcode( 'fm_magic_link', array( $this, 'magic_link_shortcode' ), 10, 2 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-magic-link', FLOWMATTIC_PLUGIN_URL . 'inc/apps/magic-link/view-magic-link.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 5.1.1
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'magic_link_clicked' => array(
				'title'       => esc_attr__( 'Magic Link Clicked', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when the magic link is clicked.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Validate the magic link.
	 *
	 * @since 5.1.1
	 * @param string $workflow_id   Workflow ID for the workflow being executed.
	 * @param array  $workflow_step Current step in the workflow being executed.
	 * @param array  $capture_data  Data received in the webhook.
	 * @return bool
	 */
	public function validate_workflow_step( $workflow_id, $workflow_step, $capture_data ) {
		// Check if the action matches the event.
		if ( isset( $workflow_step['application'] ) && 'magic_link' === $workflow_step['application'] ) {
			if ( isset( $capture_data['magic_trigger'] ) ) {
				$captured_action = strtolower( $capture_data['magic_trigger'] );

				return ( 'magic_link' === $captured_action );
			} else {
				// If the action is not set, return false.
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * Magic link shortcode.
	 *
	 * @since 5.1.1
	 * @param array $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string
	 */
	public function magic_link_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'id'                   => '',
				'link_text'            => esc_html__( 'Click here', 'flowmattic' ),
				'visitor_link_text'    => esc_html__( 'Click here', 'flowmattic' ),
				'user_redirect_url'    => '',
				'visitor_redirect_url' => '',
				'css_class'            => '',
				'css_id'               => '',
			),
			$atts,
			'fm_magic_link'
		);

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$output  = '<a href="javascript:void(0);" class="fm-magic-link ' . esc_attr( $atts['css_class'] ) . '" id="' . esc_attr( $atts['css_id'] ) . '" data-id="' . esc_attr( $atts['id'] ) . '">';
		$output .= is_user_logged_in() ? esc_html( $atts['link_text'] ) : esc_html( $atts['visitor_link_text'] );
		$output .= '</a>';

		$magic_link_url   = is_user_logged_in() ? $atts['user_redirect_url'] : $atts['visitor_redirect_url'];
		$webhook_url      = FlowMattic_Webhook::get_url( $atts['id'] );
		$current_page_url = get_permalink();
		$current_page_id  = get_the_ID();

		$additional_data = array();

		// If user is logged in, add user data.
		if ( is_user_logged_in() ) {
			$current_user                  = wp_get_current_user();
			$additional_data['user_id']    = $current_user->ID;
			$additional_data['user_email'] = $current_user->user_email;
			$additional_data['user_name']  = $current_user->display_name;
		}

		// Add JS code to handle magic link click.
		$output .= '<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function () {
				const magicLink = document.querySelector(\'.fm-magic-link[data-id="' . esc_js( $atts['id'] ) . '"]\');
				if (magicLink) {
					magicLink.addEventListener("click", function () {
						const id = this.dataset.id;
						const redirect_url = "' . esc_js( $magic_link_url ) . '";
						const webhook_url = "' . esc_js( $webhook_url ) . '";
						const current_page_url = "' . esc_js( $current_page_url ) . '";
						const current_page_id = "' . esc_js( $current_page_id ) . '";
						const additional_data = "' . ( ! empty( $additional_data ) ? base64_encode( serialize( $additional_data ) ) : '' ) . '";
						const nonce = "' . esc_js( wp_create_nonce( 'flowmattic_magic_link' ) ) . '";

						const payload = {
							action: "flowmattic_magic_link_clicked",
							id: id,
							current_page_url: current_page_url,
							current_page_id: current_page_id,
							additional_data: additional_data,
							nonce: nonce,
						};

						fetch(webhook_url, {
							method: "POST",
							headers: {
								"Content-Type": "application/json"
							},
							body: JSON.stringify(payload)
						})
						.then(response => {
							if (!response.ok) {
								throw new Error("Network response was not ok");
							}
							return response.json();
						})
						.then(data => {
							window.location.href = redirect_url;
						})
						.catch(error => {
							console.error("Error occurred while processing the magic link:", error);
						});
					});
				}
			});
		</script>';

		// Add CSS for the magic link.
		$output .= '<style>
			.fm-magic-link {
				text-decoration: none;
				color: inherit;
			}
			.fm-magic-link:hover {
				text-decoration: underline;
			}
			.fm-magic-link:active {
				color: #0073aa;
			}
			.fm-magic-link:focus {
				outline: none;
				box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.5);
			}
		</style>';

		return $output;
	}
}

new FlowMattic_Magic_Link();
