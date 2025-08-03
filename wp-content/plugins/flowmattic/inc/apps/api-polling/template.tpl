<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 4.1.0
 */
?>
<script type="text/html" id="flowmattic-application-api-polling-data-template">
	<div class="flowmattic-api-polling-form-data">
		<div class="form-group api-endpoint">
			<h4 class="fm-input-title"><?php esc_attr_e( 'API Endpoint', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<input type="text" name="api_endpoint_url" class="form-control fm-api-endpoint" value="{{{ api_endpoint_url }}}">
			<div class="fm-application-instructions">
				<p><?php esc_attr_e( 'Enter the API endpoint URL to poll for data.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="form-group api-polling-method">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Polling Method', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<select name="api_polling_method" class="form-control fm-api-polling-method" title="Choose Method ">
				<option value="GET" <# if ( 'GET' === api_polling_method ) { #>selected<# } #>><?php esc_attr_e( 'GET', 'flowmattic' ); ?></option>
				<option value="POST" <# if ( 'POST' === api_polling_method ) { #>selected<# } #>><?php esc_attr_e( 'POST', 'flowmattic' ); ?></option>
			</select>
			<div class="fm-application-instructions">
				<p><?php esc_attr_e( 'Select the method to poll the API endpoint.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="form-group api-item-index">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Item Index Key', 'flowmattic' ); ?></h4>
			<input type="text" name="api_item_index" class="form-control fm-api-item-index" value="{{{ api_item_index }}}" placeholder="results">
			<div class="fm-application-instructions">
				<p><?php esc_attr_e( 'Enter the index key name that contains the array of items in the API response. eg. results, items, posts, reviews etc. Check your API documentation for more details. Leave empty to let FlowMattic try to pick.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Headers', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body data-parameters m-t-20 data-parameter-fields" data-field-name="api_polling_headers">
				<#
				if ( 'undefined' !== typeof api_polling_headers && ! _.isEmpty( api_polling_headers ) ) {
					_.each( api_polling_headers, function( value, key ) {
						if ( 0 === key ) {
							key = '';
						}
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-parameters">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
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
							<input class="fm-dynamic-inputs w-100" autocomplete="new-password" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="new-password" name="dynamic-field-value[]" placeholder="value"></textarea>
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
				<div class="polling-input-add-more fm-api-headers-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-headers"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Parameters', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body data-parameters m-t-20 data-parameter-fields" data-field-name="api_polling_parameters">
				<#
				if ( 'undefined' !== typeof api_polling_parameters && ! _.isEmpty( api_polling_parameters ) ) {
					_.each( api_polling_parameters, function( value, key ) {
						if ( 0 === key ) {
							key = '';
						}
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-parameters">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
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
							<input class="fm-dynamic-inputs w-100" autocomplete="new-password" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="new-password" name="dynamic-field-value[]" placeholder="value"></textarea>
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
				<div class="polling-input-add-more fm-api-parameters-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-parameters"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-api-poll-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Save & Capture response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>