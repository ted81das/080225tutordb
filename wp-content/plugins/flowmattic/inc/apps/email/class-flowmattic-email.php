<?php
/**
 * Application Name: FlowMattic Email
 * Description: Add Email module to FlowMattic.
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
 * Email module integration class.
 *
 * @since 1.1
 */
class FlowMattic_Email {
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
			'email',
			array(
				'name'         => esc_attr__( 'Email by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/email/icon.svg',
				'instructions' => 'Send email using built-in WP mail function or custom SMTP',
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);

		// Register ajax to test email sending.
		add_action( 'wp_ajax_flowmattic_send_test_email', array( $this, 'send_test_email' ) );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-email', FLOWMATTIC_PLUGIN_URL . 'inc/apps/email/view-email.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'send_email' => array(
				'title'       => esc_attr__( 'Send Email', 'flowmattic' ),
				'description' => esc_attr__( 'Send an email using built-in WP mail function or custom SMTP', 'flowmattic' ),
			),
			'send_template_email' => array(
				'title'       => esc_attr__( 'Send Template Email', 'flowmattic' ),
				'description' => esc_attr__( 'Send an email using a prebuilt template', 'flowmattic' ),
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
		global $phpmailer;

		// (Re)create it, if it's gone missing.
		if ( ! ( $phpmailer instanceof PHPMailer\PHPMailer\PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
		}

		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$email_provider = ( isset( $fields['email_provider'] ) ) ? $fields['email_provider'] : '';
		$from_name      = ( isset( $fields['from_name'] ) ) ? $fields['from_name'] : '';
		$from_email     = ( isset( $fields['from_email'] ) ) ? $fields['from_email'] : '';
		$reply_to_email = ( isset( $fields['reply_to_email'] ) ) ? $fields['reply_to_email'] : '';
		$to_email       = ( isset( $fields['to_email'] ) ) ? $fields['to_email'] : '';
		$cc_email       = ( isset( $fields['cc_email'] ) ) ? $fields['cc_email'] : '';
		$bcc_email      = ( isset( $fields['bcc_email'] ) ) ? $fields['bcc_email'] : '';
		$email_subject  = ( isset( $fields['email_subject'] ) ) ? $fields['email_subject'] : '';
		$email_body     = ( isset( $fields['email_body'] ) ) ? $fields['email_body'] : '';
		$array_index    = ( isset( $fields['array_index'] ) ) ? $fields['array_index'] : '';
		$attachments    = ( isset( $step['attachments'] ) ) ? $step['attachments'] : array();

		// For SMTP.
		$host_name       = ( isset( $fields['host_name'] ) ) ? $fields['host_name'] : '';
		$smtp_username   = ( isset( $fields['smtp_username'] ) ) ? $fields['smtp_username'] : '';
		$smtp_password   = ( isset( $fields['smtp_password'] ) ) ? $fields['smtp_password'] : '';
		$encryption_type = ( isset( $fields['encryption_type'] ) ) ? $fields['encryption_type'] : 'TLS';
		$smtp_port       = ( isset( $fields['smtp_port'] ) ) ? $fields['smtp_port'] : 587;

		// For FlowMattic SMTP.
		$settings = get_option( 'flowmattic_settings', array() );

		$server_name        = ( isset( $settings['host'] ) ) ? $settings['host'] : '';
		$port_number        = ( isset( $settings['port'] ) ) ? $settings['port'] : '';
		$username           = ( isset( $settings['username'] ) ) ? $settings['username'] : '';
		$password           = ( isset( $settings['password'] ) ) ? $settings['password'] : '';
		$fm_encryption_type = ( isset( $settings['smtpsecure'] ) ) ? $settings['smtpsecure'] : '';

		$response_array = array();

		$error_message = '';

		// If using template, get the template data.
		if ( 'send_template_email' === $action ) {
			$template_id = ( isset( $fields['email_template'] ) ) ? $fields['email_template'] : '';
			$template    = wp_flowmattic()->email_templates_db->get( array( 'email_template_id' => $template_id ) );
			$template    = ! empty( $template ) ? $template[0] : '';

			if ( ! empty( $template ) ) {
				$template_json    = maybe_unserialize( $template->email_template_json );
				$template_subject = isset( $template_json['subject'] ) ? $template_json['subject'] : esc_html__( '(No Subject)', 'flowmattic' );
				$pre_header       = isset( $template_json['preHeader'] ) ? $template_json['preHeader'] : '';
				$template_body    = $template->email_template_html;
				$dynamic_data     = maybe_unserialize( $template->email_template_dynamic_data );
				$variables        = $fields['variables'];

				// Replace the variables in the template body and subject.
				if ( ! empty( $variables ) ) {
					foreach ( $variables as $key => $value ) {
						$template_body    = str_replace( '{{' . $key . '}}', $value, $template_body );
						$template_subject = str_replace( '{{' . $key . '}}', $value, $template_subject );

						if ( ! empty( $pre_header ) ) {
							$pre_header = str_replace( '{{' . $key . '}}', $value, $pre_header );
						}
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

				if ( '' === $email_subject ) {
					$email_subject = $template_subject;
				}

				// Record the sent count.
				$sent_count = isset( $template->email_template_sent_count ) ? $template->email_template_sent_count : 0;
				$sent_count++;
				
				wp_flowmattic()->email_templates_db->update(
					array(
						'email_template_sent_count' => $sent_count,
					),
					$template_id
				);
			}
		}

		// Fix the HTML entities in email body.
		$email_body = html_entity_decode( $email_body );

		// Get attachment file data.
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $key => $attachment ) {
				$file = flowmattic_attachment_url_to_path( $attachment );

				if ( $file ) {
					$attachments[ $key ] = $file;
				}
			}
		}

		if ( 'wp' !== $email_provider ) {
			// Initialize PHPMailer.
			$phpmailer = new PHPMailer\PHPMailer\PHPMailer( true );

			// Set the character encoding.
			$phpmailer->CharSet = 'UTF-8'; // @codingStandardsIgnoreLine

			if ( 'smtp' === $email_provider ) {
				// SMTP configuration.
				// @codingStandardsIgnoreStart
				$phpmailer->isSMTP();
				$phpmailer->Host       = $host_name;
				$phpmailer->SMTPAuth   = true;
				$phpmailer->Username   = $smtp_username;
				$phpmailer->Password   = $smtp_password;
				$phpmailer->SMTPSecure = $encryption_type;
				$phpmailer->Port       = $smtp_port;
				// @codingStandardsIgnoreEnd
			}

			if ( 'flowmattic' === $email_provider ) {
				if ( ! empty( $server_name ) && ! empty( $port_number ) && ! empty( $username ) && ! empty( $password ) ) {
					// FlowMattic SMTP configuration.
					// @codingStandardsIgnoreStart
					$phpmailer->isSMTP();
					$phpmailer->SMTPAuth   = true;
					$phpmailer->Host       = $server_name;
					$phpmailer->Port       = (int) $port_number;
					$phpmailer->Username   = $username;
					$phpmailer->Password   = $password;
					$phpmailer->SMTPSecure = $fm_encryption_type;
					// @codingStandardsIgnoreEnd
				}
			}

			// Send email.
			try {
				// Sender info.
				$phpmailer->setFrom( $from_email, $from_name );

				// Reply to.
				if ( '' !== $reply_to_email ) {
					$phpmailer->addReplyTo( $reply_to_email, $from_name );
				}

				// Add a recipient.
				$phpmailer->addAddress( $to_email );

				// Add CC.
				if ( '' !== $cc_email ) {
					$cc_emails = explode( ',', $cc_email );
					foreach ( $cc_emails as $key => $email_id ) {
						$phpmailer->addCC( $email_id );
					}
				}

				// Add BCC.
				if ( '' !== $bcc_email ) {
					$bcc_emails = explode( ',', $bcc_email );
					foreach ( $bcc_emails as $key => $email_id ) {
						$phpmailer->addBCC( $email_id );
					}
				}

				// Email subject.
				$phpmailer->Subject = $email_subject; // @codingStandardsIgnoreLine

				// Set email format to HTML.
				$phpmailer->isHTML( true );

				// Email body content.
				$phpmailer->Body = stripslashes( $email_body ); // @codingStandardsIgnoreLine

				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $key => $attachment ) {
						try {
							$phpmailer->addAttachment( $attachment );
						} catch ( PHPMailer\PHPMailer\Exception $e ) {
							continue;
						}
					}
				}

				$phpmailer->send();
			} catch ( PHPMailer\PHPMailer\Exception $e ) {
				$error_message = 'Message could not be sent. Error: ' . $e->getMessage(); // @codingStandardsIgnoreLine
			} catch ( Error $e ) {
				$error_message = $e->getMessage();
			}
		}

		// If WP default, use wp_mail.
		if ( 'wp' === $email_provider ) {
			$to      = $to_email;
			$subject = $email_subject;
			$body    = stripslashes( $email_body );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

			// Add Reply to header.
			if ( '' !== $reply_to_email ) {
				$headers[] = 'Reply-To: ' . $reply_to_email;
			}

			// Add CC.
			if ( '' !== $cc_email ) {
				$cc_emails = explode( ',', $cc_email );
				foreach ( $cc_emails as $key => $email_id ) {
					$headers[] = 'Cc: ' . $email_id;
				}
			}

			// Add BCC.
			if ( '' !== $bcc_email ) {
				$bcc_emails = explode( ',', $bcc_email );
				foreach ( $bcc_emails as $key => $email_id ) {
					$headers[] = 'Bcc: ' . $email_id;
				}
			}

			$send = wp_mail( $to, $subject, $body, $headers, $attachments );

			if ( ! $send ) {
				$error_message = esc_html__( 'Error sending email', 'flowmattic' );
			}
		}

		if ( '' === $error_message ) {
			$response = wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Message has been sent', 'flowmattic' ),
				)
			);
		} else {
			$response = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => $error_message,
				)
			);
		}

		return $response;
	}

	/**
	 * Send test email.
	 *
	 * @access public
	 * @since 5.1.0
	 * @return void
	 */
	public function send_test_email() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_email_template_nonce', 'email_template_nonce' );

		// Get the email.
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$body = isset( $_POST['template_html'] ) ? wp_unslash( $_POST['template_html'] ) : '';

		// Get from site name.
		$from_name = get_bloginfo( 'name' );

		// Get from email.
		$from_email = get_option( 'admin_email' );
		$from_email = sanitize_email( $from_email );

		// Check if email is empty.
		if ( empty( $email ) ) {
			wp_send_json_error( esc_html__( 'Email is required.', 'flowmattic' ) );
		}

		// Check if subject is empty.
		if ( empty( $subject ) ) {
			wp_send_json_error( esc_html__( 'Subject is required.', 'flowmattic' ) );
		}

		// Check if body is empty.
		if ( empty( $body ) ) {
			wp_send_json_error( esc_html__( 'Body is required.', 'flowmattic' ) );
		}

		// Send test email.
		$send = wp_mail( $email, 'TEST Email:' . $subject, $body, array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_name . ' <' . $from_email . '>' ) );
		if ( $send ) {
			wp_send_json_success( esc_html__( 'Test email sent successfully.', 'flowmattic' ) );
		} else {
			wp_send_json_error( esc_html__( 'Error sending test email.', 'flowmattic' ) );
		}
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
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$event_data['action'] = $event;

		// Set attachments.
		if ( isset( $event_data['settings']['attachments'] ) ) {
			$event_data['attachments'] = $event_data['settings']['attachments'];
		}

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}
}

new FlowMattic_Email();
