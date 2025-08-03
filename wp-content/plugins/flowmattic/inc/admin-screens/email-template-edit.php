<?php
/**
 * Admin page template for emails.
 *
 * @package FlowMattic
 * @since 5.0
 */

FlowMattic_Admin::loader();

$license_key = get_option( 'flowmattic_license_key', '' );

// Get template ID.
$template_id = isset( $_GET['template-id'] ) ? sanitize_text_field( wp_unslash( $_GET['template-id'] ) ) : '';

if ( '' === $template_id ) {
	// Redirect to the emails list page.
	$redirect_url = admin_url( 'admin.php?page=flowmattic-email-templates' );

	// Redirect to the emails list page.
	wp_safe_redirect( $redirect_url );
	exit;
}

// Get the email template.
$email_template = wp_flowmattic()->email_templates_db->get( array( 'email_template_id' => $template_id ) );

if ( ! empty ( $email_template ) ) {
	$email_template = $email_template[0];
} else {
	$email_template = new stdClass();
	$email_template->email_template_id = $_GET['template-id'];
	$email_template->email_template_name = $_GET['template-name'];
	$email_template->email_template_json = '';
	$email_template->email_template_html = '';
	$email_template->email_template_dynamic_data = '';
	$email_template->email_template_meta = '';
}
?>
<div class="wrap flowmattic-wrap flowmattic-email-builder">
	<div class="flowmattic-wrapper d-flex">
		<div class="flowmattic-container flowmattic-emails-list-container w-100">
			<div class="flowmattic-dashboard-content">
				<div class="row">
					<div id="flowmattic-emails">
						<div id="root"></div>
						<script type="text/javascript">
							// Set the email template ID.
							var emailTemplateId = '<?php echo esc_attr( $email_template->email_template_id ); ?>';

							// Set the email template name.
							var emailTemplateName = '<?php echo esc_attr( $email_template->email_template_name ); ?>';

							// Set the email template JSON.
							var emailTemplateJson = <?php echo wp_json_encode( maybe_unserialize( $email_template->email_template_json ) ); ?>;

							// Set the email template dynamic data.
							var templateDynamicData = <?php echo wp_json_encode( maybe_unserialize( $email_template->email_template_dynamic_data ) ); ?>;

							// Set the email template meta.
							var emailTemplateMeta = <?php echo wp_json_encode( $email_template->email_template_meta ); ?>;
						</script>
						<script type="module" src="<?php echo FLOWMATTIC_PLUGIN_URL; ?>/emails/dist/assets/js/index.js"></script>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	html {
		padding: 0 !important;
		scroll-padding-top: 0 !important;
	}
	.flowmattic-email-builder {
		width: 100%;
		max-width: 100%;
		margin: 0 auto;
		padding: 0;
		z-index: 999999 !important;
		position: relative;
	}
	.MuiModal-root {
		z-index: 99999999 !important;
	}
	#adminmenumain,
	#wpadminbar {
		display: none;
	}
	#wpcontent {
		margin: 0 !important;
		padding: 0 !important;
	}

	#wpbody-content {
		float: none !important;
	}
</style>
<script type="text/javascript">
	<?php
	$prebuilt_templates = wp_flowmattic()->email_templates->get_prebuilt_templates();
	?>
	// Set the prebuilt templates.
	var prebuiltTemplates = <?php echo wp_json_encode( $prebuilt_templates ); ?>;

	// Security nonce.
	var emailTemplateNonce = '<?php echo esc_attr( wp_create_nonce( 'flowmattic_email_template_nonce' ) ); ?>';

	// Admin Ajax URL.
	var fmAdminAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

	// On page load, remove the template= url parameter and #sample- from the url.
	jQuery(document).ready(function($) {
		setTimeout(() => {
			// Remove the template= url parameter.
			var url = new URL(window.location.href);
			if (url.searchParams.has('template')) {
				url.searchParams.delete('template');
			}

			// Remove the template-name url parameter.
			if (url.searchParams.has('template-name')) {
				url.searchParams.delete('template-name');
			}

			// On page load, remove the #sample- from the url.
			if (url.hash) {
				url.hash = '';
			}

			window.history.replaceState({}, document.title, url.toString());
		}, 500);
	});
</script>
<?php

// FlowMattic_Admin::footer();
add_filter( 'admin_footer_text', function( $text ) {
	return '';
} );

add_filter( 'update_footer', function( $text ) {
	return '';
} );
