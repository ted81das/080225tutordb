<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-webhook-data-template">
	<div class="fm-application-webhook-data">
		<div class="form-group webhook-url">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input class="form-control w-100" type="text" name="webhookURL" readonly value="{{{window.webhookURL}}}" />
			<div class="fm-application-instructions">
				<# if ( 'undefined' !== typeof triggerApps[ application ] && 'undefined' !== typeof triggerApps[ application ].instructions ) { #>
					<p>{{{ triggerApps[ application ].instructions }}}</p>
				<# } #>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Workflow End Action', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select class="form-control form-select w-100" name="workflowEndAction" id="workflow-end-action">
					<#
					var workflowEndActions = {
						'message': '<?php echo esc_attr__( 'Display Response Message', 'flowmattic' ); ?>',
						'redirect': '<?php echo esc_attr__( 'Redirect to URL', 'flowmattic' ); ?>',
					};

					workflowEndAction = ( 'undefined' !== typeof workflowEndAction ) ? workflowEndAction : 'message';
					
					_.each( workflowEndActions, function( frequency, value ) {
						#>
						<option value="{{{value}}}" <# if ( value === workflowEndAction ) { #>selected<# } #>>{{{frequency}}}</option>
						<#
					} );
					#>
				</select>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo __( 'In case of Redirect URL, you need to add a webhook response module at the end of the workflow and set the URL to redirect.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Custom Webhook Response', 'flowmattic' ); ?></h4>
				<div class="form-check form-switch">
					<input class="form-check-input fm-webhook-response me-2" type="checkbox" name="webhook_response" value="Yes" id="fm-checkbox-webhook-response" <# if ( 'undefined' !== typeof webhook_response && 'Yes' === webhook_response ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-webhook-response"><?php esc_attr_e( 'Turn on to enable custom webhook response', 'flowmattic' ); ?></label>
				</div>
			</div>
			<div class="webhook-response-wrap mt-3 <# if ( 'undefined' !== typeof webhook_response && 'Yes' !== webhook_response ) { #>hidden<# } #>">
				<h4 class="fm-input-title mb-2"><?php echo esc_html__( 'Webhook Response', 'flowmattic' ); ?></h4>
				<div class="fm-form-control">
					<div class="fm-dynamic-input-field">
						<textarea class="span-textarea fm-custom-response w-100 fm-dynamic-inputs dynamic-field-input" name="webhook_custom_responce" id="fm-custom-response-text"><# if ( 'undefined' !== typeof webhook_custom_responce ) { #>{{{ webhook_custom_responce }}}<# } #></textarea>
					</div>
					<div class="fm-application-instructions pt-1">
						<p class="description m-t-0"><?php echo sprintf( __( 'Enter your custom response JSON code or text here. Make sure to <a href="%s" target="_blank">validate the JSON</a> code to avoid failing of task.', 'flowmattic' ), 'https://jsonlint.com/' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Enable Webhook Authentication', 'flowmattic' ); ?></h4>
				<div class="form-check form-switch">
					<input class="form-check-input fm-webhook-security me-2" type="checkbox" name="webhook_security" value="Yes" id="fm-checkbox-webhook-security" <# if ( 'undefined' !== typeof webhook_security && 'Yes' === webhook_security ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-webhook-security"><?php esc_attr_e( 'Turn on to enable webhook authentication', 'flowmattic' ); ?></label>
				</div>
			</div>
			<div class="webhook-security-wrap mt-3 <# if ( 'undefined' !== typeof webhook_security && 'Yes' !== webhook_security ) { #>hidden<# } #>">
				<h4 class="fm-input-title mb-2"><?php echo esc_html__( 'Authentication Key', 'flowmattic' ); ?></h4>
				<input name="webhook-auth-key" class="form-control fm-response-form-input w-100 mb-2" readonly value="{{{ fmAuthKey }}}">
				<div class="fm-application-instructions">
					<?php echo esc_html__( 'Add the above authentication key to your request in authentication when sending data to this webhook URL -', 'flowmattic' ); ?>
					<ul class="pt-3 m-0" style="list-style: disc;">
						<li><strong>Bearer Token</strong></li>
						<li><strong>Basic Authentication</strong> - use <strong>flowmattic</strong> as username and the above key as password/secret</li>
					</ul>
				</div>
			</div>
		</div>
		<#
		var simpleResponseSelected = ( 'undefined' === typeof simple_reponse ) ? 'Yes' : simple_reponse;
		if ( 'undefined' !== typeof simple_response ) {
			if ( 'Yes' === simple_response ) {
				simpleResponseSelected = 'Yes';
			} else {
				simpleResponseSelected = 'No';
			}
		}
		#>
		<div class="form-group dynamic-inputs simple-response-input w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Simple Response', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="simple_response" id="fm-checkbox-simple-response" value="Yes"  <# if ( 'Yes' === simpleResponseSelected ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;"
					onchange="jQuery( this ).val( this.checked ? 'Yes' : 'No' );">
					<label for="fm-checkbox-simple-response"><?php esc_attr_e( 'Retrieve the data in simple format', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<#
		if ( ! window.isVisualWorkflowBuilder ) {
			var allCapturedResponses = ( 'undefined' !== typeof window.capturedResponses ) ? window.capturedResponses : false;
			var selectedResponse  = ( 'undefined' !== typeof window.selectedResponse ) ? window.selectedResponse : '';
			#>
			<div class="form-group dynamic-inputs workflow-responses-dropdown w-100 <# if ( ! capturedResponses ) { #>d-none<# } #>">
				<div class="fm-input-wrap">
					<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Choose Response', 'flowmattic' ); ?></strong></h4>
					<div class="responses-dropdown w-100">
						<select class="form-control form-select w-100" name="capturedResponses" id="captured-responses-select" data-live-search="true">
							<#
							_.each( allCapturedResponses, function( response, key ) {
								#>
								<option data-subtext="Captured at: {{{ response.captured_at }}}" value="{{{response.letter}}}" <# if ( response.letter === selectedResponse ) { #>selected<# } #>>Response {{{response.letter}}}</option>
								<#
							} );
							#>
						</select>
						<p class="fm-application-instructions"><?php esc_attr_e( 'Select the response you want to use in the workflow.', 'flowmattic' ); ?></p>
					</div>
				</div>
			</div>
			<#
		}
		#>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
				<#
				if ( window.captureData ) {
					#>
					<?php echo esc_attr__( 'Re-capture Webhook Data', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Capture Webhook Data', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-webhook-response-template">
<div class="fm-response-body-wrapper">
	<a href="javascript:void(0);" class="fm-response-data-toggle webhook-data-toggle toggle">
		<span class="fm-response-toggle-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</span>
		<?php echo esc_attr__( 'Response Data', 'flowmattic' ); ?>
	</a>
	<div class="fm-response-body webhook-response-body w-100" style="display:none;">
		<table class="fm-webhook-response-data-table w-100">
			<thead>
				<tr>
					<th class="w-50">Key</th>
					<th class="w-50">Value</th>
				</tr>
			</thead>
			<tbody>
			<#
			delete( webhook_capture.length );
			_.each( webhook_capture, function( value, key ) {
				key = FlowMatticWorkflow.UCWords( key );
				#>
				<tr>
					<td>
						<input class="fm-response-form-input w-100" type="text" value="{{{ key }}}" readonly />
					</td>
					<td>
						<textarea class="fm-response-form-input w-100" rows="1" readonly>{{{ value }}}</textarea>
					</td>
				</tr>
				<#
			} );
			#>
			</tbody>
		</table>
	</div>
</script>
