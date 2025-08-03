<?php
/**
 * Handle Email Templates in FlowMattic.
 *
 * @package flowmattic
 * @since 5.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Email Templates in FlowMattic.
 *
 * @since 5.1.0
 * @access public
 */
class FlowMattic_Email_Templates {
	/**
	 * Prebuilt email templates.
	 *
	 * @var array
	 */
	public $prebuilt_templates;

	/**
	 * The Constructor.
	 *
	 * @since 5.1.0
	 * @access public
	 */
	public function __construct() {
		// Set the prebuilt email templates.
		$this->prebuilt_templates = $this->get_prebuilt_templates();

		// Register admin ajax to create/update email template.
		add_action( 'wp_ajax_flowmattic_update_email_template', array( $this, 'update_email_template' ) );

		// Register admin ajax to clone email template.
		add_action( 'wp_ajax_flowmattic_clone_email_template', array( $this, 'clone_email_template' ) );

		// Register admin ajax to delete email template.
		add_action( 'wp_ajax_flowmattic_delete_email_template', array( $this, 'delete_email_template' ) );

		// Add the email templates to the workflow.
		add_filter( 'flowmattic_workflow_email_templates', array( $this, 'add_email_templates' ) );
	}

	/**
	 * Get the prebuilt email templates.
	 *
	 * @since 5.1.0
	 * @access public
	 * @return array The prebuilt email templates.
	 */
	public function get_prebuilt_templates() {
		$prebuilt_templates = array(
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/blank-template.png',
				'title'    => 'Blank template',
				'template' => '#',
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/weekly-newsletter-template.jpg',
				'title'    => 'Weekly newsletter',
				'template' => '#template/weekly-newsletter'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/best-sellers-template.jpg',
				'title'    => 'Best sellers',
				'template' => '#template/best-sellers'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/multi-choice-survey-template.jpg',
				'title'    => 'Multi-choice survey',
				'template' => '#template/multi-choice-survey'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/notification-newsletter-template.jpg',
				'title'    => 'Notification newsletter',
				'template' => '#template/notification-newsletter'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/feedback-survey-template.jpg',
				'title'    => 'Feedback survey',
				'template' => '#template/feedback-survey'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/announcement-template.jpg',
				'title'    => 'Announcement',
				'template' => '#template/announcement'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/welcome-template.png',
				'title'    => 'Welcome email',
				'template' => '#template/welcome'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/one-time-password-template.png',
				'title'    => 'One-time passcode (OTP)',
				'template' => '#template/one-time-password'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/reset-password-template.png',
				'title'    => 'Reset password',
				'template' => '#template/reset-password'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/order-ecomerce-template.png',
				'title'    => 'E-commerce receipt',
				'template' => '#template/order-ecomerce'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/subscription-receipt-template.png',
				'title'    => 'Subscription receipt',
				'template' => '#template/subscription-receipt'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/reservation-reminder-template.png',
				'title'    => 'Reservation reminder',
				'template' => '#template/reservation-reminder'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/post-metrics-report-template.png',
				'title'    => 'Post metrics',
				'template' => '#template/post-metrics-report'
			),
			array(
				'img'      => FLOWMATTIC_PLUGIN_URL . 'emails/images/respond-to-message-template.png',
				'title'    => 'Respond to inquiry',
				'template' => '#template/respond-to-message'
			),
		);

		return $prebuilt_templates;
	}

	/**
	 * Create or update a email template.
	 *
	 * @since 5.1.0
	 * @access public
	 */
	public function update_email_template() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_email_template_nonce', 'email_template_nonce' );

		$id           = isset( $_POST['template_id'] ) && '' !== $_POST['template_id'] ? $_POST['template_id'] : '';
		$template_id  = isset( $_POST['email_template_id'] ) && '' !== $_POST['email_template_id'] ? $_POST['email_template_id'] : '';
		$name         = isset( $_POST['email_template_name'] ) && '' !== $_POST['email_template_name'] ? $_POST['email_template_name'] : '';
		$json         = isset( $_POST['email_template_json'] ) && '' !== $_POST['email_template_json'] ? $_POST['email_template_json'] : '';
		$html         = isset( $_POST['email_template_html'] ) && '' !== $_POST['email_template_html'] ? $_POST['email_template_html'] : '';
		$dynamic_data = isset( $_POST['email_template_dynamic_data'] ) && '' !== $_POST['email_template_dynamic_data'] ? $_POST['email_template_dynamic_data'] : '';
		$meta         = isset( $_POST['email_template_meta'] ) && '' !== $_POST['email_template_meta'] ? $_POST['email_template_meta'] : '';

		// Set the mysql date format.
		$mysql_date_format = 'Y-m-d H:i:s';
		$created           = date_i18n( $mysql_date_format );
		$updated           = $created;

		// Get the template name.
		$template_name = sanitize_text_field( wp_unslash( $name ) );

		// Check if the Template exists.
		$template = wp_flowmattic()->email_templates_db->get(
			array(
				'email_template_id' => $id,
			)
		);

		// Decode the json for $json and $dynamic_data.
		$json         = json_decode( stripslashes( $json ), true );
		$dynamic_data = ! is_array( $dynamic_data ) ? json_decode( $dynamic_data, true ) : $dynamic_data;
		$meta         = json_decode( $meta, true );

		// If the json is not an array, then set it to an empty array.
		if ( ! is_array( $json ) ) {
			$json = array();
		}

		// If the dynamic data is not an array, then set it to an empty array.
		if ( ! is_array( $dynamic_data ) ) {
			$dynamic_data = array();
		}

		// If the meta is not an array, then set it to an empty array.
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		// If the Template exists, then update the email template.
		if ( ! empty( $template ) ) {
			// Update the email template.
			$template_response = wp_flowmattic()->email_templates_db->update(
				array(
					'email_template_name'         => $template_name,
					'email_template_json'         => is_array( $json ) ? maybe_serialize( $json ) : $json,
					'email_template_html'         => $html,
					'email_template_dynamic_data' => is_array( $dynamic_data ) ? maybe_serialize( $dynamic_data ) : $dynamic_data,
					'email_template_updated'      => $updated,
					'email_template_meta'         => is_array( $meta ) ? maybe_serialize( $meta ) : $meta,
				),
				$id
			);

			// Set update message.
			$message = esc_attr__( 'Updated', 'flowmattic' );
		} else {
			// Create the email template.
			$template_response = wp_flowmattic()->email_templates_db->insert(
				array(
					'email_template_id'           => $id,
					'email_template_name'         => $template_name,
					'email_template_json'         => is_array( $json ) ? maybe_serialize( $json ) : $json,
					'email_template_html'         => $html,
					'email_template_dynamic_data' => is_array( $dynamic_data ) ? maybe_serialize( $dynamic_data ) : $dynamic_data,
					'email_template_created'      => $created,
					'email_template_updated'      => $updated,
					'email_template_meta'         => is_array( $meta ) ? maybe_serialize( $meta ) : $meta,
				)
			);

			// Set update message.
			$message = esc_attr__( 'Created', 'flowmattic' );
		}

		// If the Template is created, return success.
		if ( ! empty( $template_response ) ) {
			wp_send_json_success( $message );
		}

		// If the Template is not created, return error.
		wp_send_json_error( esc_attr__( 'Error creating template.', 'flowmattic' ) );

		// End the ajax call.
		wp_die();
	}

	/**
	 * Clone a email template.
	 *
	 * @since 5.1.0
	 * @access public
	 */
	public function clone_email_template() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_email_template_nonce', 'nonce' );

		// Check if the template id is set.
		if ( ! isset( $_POST['template_id'] ) ) {
			wp_send_json_error( esc_attr__( 'Template ID is required.', 'flowmattic' ) );
		}

		// Get the Template ID.
		$template_id = sanitize_text_field( wp_unslash( $_POST['template_id'] ) );

		// Clone the email template.
		$template = wp_flowmattic()->email_templates_db->clone( $template_id );

		// If the Template is cloned, return success.
		if ( ! empty( $template ) ) {
			wp_send_json_success( $template );
		}

		// If the Template is not cloned, return error.
		wp_send_json_error( esc_attr__( 'Error cloning template.', 'flowmattic' ) );

		// End the ajax call.
		wp_die();
	}

	/**
	 * Delete a email template.
	 *
	 * @since 5.1.0
	 * @access public
	 */
	public function delete_email_template() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_email_template_nonce', 'nonce' );

		// Check if the template id is set.
		if ( ! isset( $_POST['template_id'] ) ) {
			wp_send_json_error( esc_attr__( 'Template ID is required.', 'flowmattic' ) );
		}

		// Get the Template ID.
		$template_id = sanitize_text_field( wp_unslash( $_POST['template_id'] ) );

		// Delete the email template.
		$template = wp_flowmattic()->email_templates_db->delete( $template_id );

		// If the Template is deleted, return success.
		if ( ! empty( $template ) ) {
			wp_send_json_success( esc_attr__( 'Template deleted successfully.', 'flowmattic' ) );
		}

		// If the Template is not deleted, return error.
		wp_send_json_error( esc_attr__( 'Error deleting template.', 'flowmattic' ) );

		// End the ajax call.
		wp_die();
	}

	/**
	 * Add the email templates to the workflow.
	 *
	 * @since 5.1.1
	 * @access public
	 * @param string $template_id The email template ID.
	 * @param array  $variables   The template variables.
	 * @return array Compiled template HTML.
	 */
	public function generate_email( $template_id, $variables ) {
		$template = wp_flowmattic()->email_templates_db->get( array( 'email_template_id' => $template_id ) );
		$template = ! empty( $template ) ? $template[0] : '';

		if ( ! empty( $template ) ) {
			$template_json    = maybe_unserialize( $template->email_template_json );
			$template_subject = isset( $template_json['subject'] ) ? $template_json['subject'] : esc_html__( '(No Subject)', 'flowmattic' );
			$pre_header       = isset( $template_json['preHeader'] ) ? $template_json['preHeader'] : '';
			$template_body    = $template->email_template_html;
			$dynamic_data     = maybe_unserialize( $template->email_template_dynamic_data );

			// Replace the variables in the template body and subject.
			if ( ! empty( $variables ) ) {
				foreach ( $variables as $key => $value ) {
					$template_body    = str_replace( '{{' . $key . '}}', $value, $template_body );
					$template_subject = str_replace( '{{' . $key . '}}', $value, $template_subject );
				}
			}

			// Assign the template body and subject to the email body and subject.
			$email_body = $template_body;

			// If the template has pre-header, add it to the email body.
			if ( ! empty( $pre_header ) ) {
				$preview_text  = '<span style="display:none!important;opacity:0;">' . $pre_header . '</span>';
				$preview_text .= '<span style="display:none!important;opacity:0;">';

				for ( $i = 0; $i < 120; $i++ ) {
					$preview_text .= '&nbsp;&zwnj;';
				}

				$preview_text .= '</span>';

				$email_body = $preview_text . $email_body;
			}

			return stripslashes( $email_body );
		}

		return esc_html__( 'No template found.', 'flowmattic' );
	}
}
