<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.1
 */
?>
<script type="text/html" id="flowmattic-application-email-data-template">
	<div class="flowmattic-email-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Email Provider', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<select id="fm-select-email-provider" class="form-control fm-select-box w-100" name="email_provider" required>
					<option value="wp" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_provider && 'wp' === actionAppArgs.email_provider ) { #>selected<# } #>>WP Default</option>
					<option value="flowmattic" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_provider && 'flowmattic' === actionAppArgs.email_provider ) { #>selected<# } #>>FlowMattic Default</option>
					<option value="smtp" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_provider && 'smtp' === actionAppArgs.email_provider ) { #>selected<# } #>>Custom SMTP</option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Choose how you want to send the email. WP default does not report errors.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'From Name', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="from_name" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.from_name ) { #>{{{ actionAppArgs.from_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter email sender name.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'From Email', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="from_email" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.from_email ) { #>{{{ actionAppArgs.from_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Email will be sent from this email. If you\'re using SMTP with WP Default, this setting won\'t work, as WP Default configuration with SMTP sends only from the email configured in SMTP settings.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Reply-to Email', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="reply_to_email" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.reply_to_email ) { #>{{{ actionAppArgs.reply_to_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter email to set as reply address. Make sure it is not same as the from email. If it is same, skip this field. WP Default will ignore this setting if using with SMTP plugins.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'To Email', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" required name="to_email" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.to_email ) { #>{{{ actionAppArgs.to_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter recipient\'s email address.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'CC Email', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="cc_email" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cc_email ) { #>{{{ actionAppArgs.cc_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter recipient\'s email addresses set to be in CC. You can add multiple emails separated by comma. Eg. email1@domain.com, email2@domain.com', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'BCC Email', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="bcc_email" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.bcc_email ) { #>{{{ actionAppArgs.bcc_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter recipient\'s email addresses set to be in BCC. You can add multiple emails separated by comma. Eg. email1@domain.com, email2@domain.com', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Email Subject', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="email_subject" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_subject ) { #>{{{ actionAppArgs.email_subject }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter your email subject. If using template and want to use template subject, leave this field empty.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100 email-body">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Email Body', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<div id="flowmattic-content-editor" class="flowmattic-content-editor"></div>
				<div class="d-none">
					<textarea class="fm-textarea form-control content-editor-input w-100" rows="4" name="email_body"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_body ) { #>{{{ actionAppArgs.email_body }}}<# } #></textarea>
				</div>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'HTML or text for the email content. To insert dynamic tag, type @ anywhere you want to insert the tag and dynamic tags popup will open, then choose the appropriate tag.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="email-template-variables">
			<div class="form-group w-100">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Email Template', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<select id="fm-select-email-template" class="form-control fm-select-box w-100" name="email_template">
						<?php
						$all_email_templates = wp_flowmattic()->email_templates_db->get_all();

						if ( ! empty( $all_email_templates ) ) {
							foreach ( $all_email_templates as $email_template ) {
								$dynamic_data = $email_template->email_template_dynamic_data;
								$dynamic_data = ! empty( $dynamic_data ) ? maybe_unserialize( $dynamic_data ) : array();
								$dynamic_data = wp_json_encode( $dynamic_data );
								$dynamic_data = base64_encode( $dynamic_data );
								?>
								<option value="<?php echo esc_attr( $email_template->email_template_id ); ?>" data-dynamic-data="<?php echo $dynamic_data; ?>"  <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email_template && actionAppArgs.email_template === '<?php echo esc_attr( $email_template->email_template_id ); ?>' ) { #>selected<# } #>><?php echo esc_html( $email_template->email_template_name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="fm-application-instructions">
					<p>
						<?php echo esc_attr__( 'Choose email template to use for this email. If you choose a template, the email body will be replaced with the template content.', 'flowmattic' ); ?>
					</p>
				</div>
			</div>
			<div class="email-template-dynamic-data"></div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Attachments', 'flowmattic' ); ?></h4>
			<div class="fm-custom-fields-body data-dynamic-fields" data-field-name="attachments">
				<#
				if( 'undefined' !== typeof attachments && attachments.length ) {
					_.each( attachments, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 gap-2 p-2 mb-2 border rounded">
							<div class="d-flex flex-column gap-2 w-100 mb-2">
								<div class="fm-dynamic-input-field">
									<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="File Name" value="{{{key}}}" />
								</div>
								<div class="fm-dynamic-input-field">
									<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="File URL">{{{value}}}</textarea>
									<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
								</div>
							</div>
							<div>
								<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
										<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
										<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
										<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
									</svg>
								</a>
							</div>
						</div>
						<#
					} );
				} else {
					#>
					<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 gap-2 p-2 mb-2 border rounded">
						<div class="d-flex flex-column gap-2 w-100 mb-2">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="File Name" value="" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="File URL"></textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
						</div>
						<div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
								</svg>
							</a>
						</div>
					</div>
					<#
				}
				#>
				<div class="dynamic-input-add-more fm-api-parameters-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-parameters"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
		<div class="flowmattic-email-smtp-fields">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-email-smtp-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Host Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="host_name" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.host_name ) { #>{{{ actionAppArgs.host_name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter your SMTP host name. eg. smtp.gmail.com', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Username', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="smtp_username" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.smtp_username ) { #>{{{ actionAppArgs.smtp_username }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter your SMTP username.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Password', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="smtp_password" autocomplete="off" type="password" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.smtp_password ) { #>{{{ actionAppArgs.smtp_password }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter your SMTP password.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Encryption Type', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="encryption_type" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.encryption_type ) { #>{{{ actionAppArgs.encryption_type }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter your SMTP Encryption Type ( TLS / SSL / NONE ). Ex - TLS.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Port Number', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="smtp_port" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.smtp_port ) { #>{{{ actionAppArgs.smtp_port }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter your SMTP Port ( 587 / 465 / 2525 / 25 ). Ex - 587', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-email-template-dynamic-data-template">
	<#
	// Decode the dynamic data from base64
	var dynamic_data = atob( data );

	// Parse the JSON string into an object
	var dynamic_data_obj = JSON.parse( dynamic_data );

	// Check if the object is not empty
	if ( ! _.isEmpty( dynamic_data_obj ) ) {
		#>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Dynamic Data', 'flowmattic' ); ?></h4>
			<div class="fm-custom-fields-body data-dynamic-fields" data-field-name="variables">
			<#
				// Loop through the array and create the HTML
				_.each( dynamic_data_obj, function( value, key ) {
					var dataValue = ( 'undefined' !== typeof actionAppArgs.variables ) ? actionAppArgs.variables[ key ] : '';
					#>
					<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 gap-2 p-2 mb-2 border rounded">
						<div class="d-flex flex-column gap-2 w-100 mb-2">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100 disabled" readonly name="dynamic-field-key[]" type="text" placeholder="Variable Name" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="Variable Value">{{{dataValue}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
						</div>
					</div>
					<#
				} );
			#>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Configure dynamic data for the email template. The fields here are defined in "Data" tab in email template editor.', 'flowmattic' ); ?>
			</p>
		</div>
		<#
	}
	#>
</script>