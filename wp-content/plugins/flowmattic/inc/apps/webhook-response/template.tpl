<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-webhook-response-action-data-template">
	<div class="flowmattic-webhook-response-action-data">
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Response Type', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-post-type-field">
				<select name="webhook_response_type" title="How do you want to set the response?" class="form-control response-type-select w-100">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.webhook_response_type && 'string' === actionAppArgs.webhook_response_type ) { #>selected<# } #> value="string"><?php echo esc_attr__( 'String', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.webhook_response_type && 'parameters_json' === actionAppArgs.webhook_response_type ) { #>selected<# } #> value="parameters_json"><?php echo esc_attr__( 'Parameters as JSON', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.webhook_response_type && 'custom_json' === actionAppArgs.webhook_response_type ) { #>selected<# } #> value="custom_json"><?php echo esc_attr__( 'Custom JSON', 'flowmattic' ); ?></option>
				</select>
			</div>
		</div>
		<div class="form-group response-field response-string w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Response String', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<input class="fm-dynamic-inputs form-control dynamic-field-input w-100" name="response_string" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.response_string ) { #>{{{ actionAppArgs.response_string }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs response-field response-parameters_json w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Response Parameters', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="webhook_response_parameters">
				<#
				if ( 'undefined' !== typeof webhook_response_parameters && ! _.isEmpty( webhook_response_parameters ) ) {
					_.each( webhook_response_parameters, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-parameters">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
								</svg>
							</a>
						</div>
						<#
					} );
				} else {
					#>
					<div class="fm-dynamic-input-wrap fm-api-request-parameters">
						<div class="fm-dynamic-input-field">
							<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="off" name="dynamic-field-value[]" placeholder="value"></textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
						<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
							</svg>
						</a>
					</div>
					<#
				}
				#>
				<div class="dynamic-input-add-more fm-api-parameters-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-parameters"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
		<div class="form-group response-field response-custom_json w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Custom JSON', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="fm-custom-json w-100 fm-dynamic-inputs dynamic-field-input" name="custom_json" rows="8"><# if ( 'undefined' !== typeof custom_json ) { #>{{{ custom_json }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo sprintf( __( 'Enter your custom JSON code here, or create one. Make sure to <a href="%s" target="_blank">validate the JSON</a> code to avoid failing of task.', 'flowmattic' ), 'https://jsonlint.com/' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-webhook-redirect-action-data-template">
<div class="flowmattic-webhook-redirect-action-data">
	<div class="form-group redirect-field redirect-redirect_url w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Redirect URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="fm-dynamic-inputs form-control dynamic-field-input w-100 fm-textarea" name="redirect_url" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.redirect_url ) { #>{{{ actionAppArgs.redirect_url }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo __( 'Enter the URL where you want to redirect the user.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
</div>
</script>