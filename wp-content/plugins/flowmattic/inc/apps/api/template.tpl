<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-api-template">
	<#
	var time = Date.now();
	#>
	<div class="fm-application-api-data w-100">
		<div class="form-group fm-api-advanced-request data-event-advanced">
			<select name="request_type" class="workflow-api-events w-100 hidden d-none">
				<option disabled <# if ( 'undefined' !== typeof applicationAction && '' === applicationAction ) { #>selected<# } #>>Choose method</option>
				<#
				delete applicationEvents.advanced;
				_.each( applicationEvents, function( title, value ) {
					#>
					<option <# if ( applicationAction === value ) { #>selected<# } #> value="{{{ value }}}">{{{ title }}}</option>
					<#
				} )
				#>
			</select>
		</div>
		<div class="form-group api-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'API Endpoint URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea name="api_endpoint" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" rows="1" required type="search">{{{ endpointURL }}}</textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
		<div class="form-group fm-api-content-type w-100 data-event-post data-event-put data-event-patch">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Content Type', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<select name="workflow-api-content-type" class="workflow-api-content-type w-100" required>
				<#
				_.each( actionApps.api.content_types, function( settings, type ) {
					#>
					<option <# if ( contentType === type ) { #>selected<# } #> value="{{{ type }}}">{{{ settings.title }}}</option>
					<#
				} )
				#>
			</select>
		</div>
		<div class="form-group fm-api-authentication w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Authentication', 'flowmattic' ); ?></h4>
			<select name="workflow-api-authentication-type" class="workflow-api-authentication-type w-100">
				<#
				var authenticationTypes = {
					no: '<?php esc_attr_e( 'No Authentication', 'flowmattic' ); ?>',
					basic: '<?php esc_attr_e( 'Basic Authentication', 'flowmattic' ); ?>',
					bearer_token: '<?php esc_attr_e( 'Bearer Token', 'flowmattic' ); ?>',
					connect: '<?php esc_attr_e( 'FlowMattic Connect', 'flowmattic' ); ?>',
				}
				_.each( authenticationTypes, function( title, type ) {
					#>
					<option <# if ( authType === type ) { #>selected<# } #> value="{{{ type }}}">{{{ title }}}</option>
					<#
				} )
				#>
			</select>
			<div class="api-authentication-basic data-auth-basic w-100 m-t-20">
				<div class="auth-api-key">
					<h4 class="fm-input-title"><?php esc_attr_e( 'API Key / Username', 'flowmattic' ); ?></h4>
					<div class="fm-dynamic-input-field">
						<input class="fm-dynamic-inputs dynamic-field-input w-100" name="auth-api-key" type="search" autocomplete="new-password" value="<# if ( 'undefined' !== typeof auth_api_key ) { #>{{{ auth_api_key }}}<# } #>" />
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
				<div class="auth-api-secret">
					<h4 class="fm-input-title"><?php esc_attr_e( 'API Secret / Password', 'flowmattic' ); ?></h4>
					<div class="fm-dynamic-input-field">
						<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="auth-api-secret" autocomplete="new-password" rows="1"><# if ( 'undefined' !== typeof auth_api_secret ) { #>{{{ auth_api_secret }}}<# } #></textarea>
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
			</div>
			<div class="api-authentication-bearer data-auth-bearer_token w-100 m-t-20">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Bearer Token', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input class=" form-control fm-dynamic-inputs dynamic-field-input w-100" name="auth-api-bearer-token" autocomplete="new-password" type="search" value="<# if ( 'undefined' !== typeof auth_api_bearer_token ) { #>{{{ auth_api_bearer_token }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="api-authentication-bearer data-auth-connect w-100 m-t-20">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-select-field">
					<select name="workflow_api_connect" class="workflow-api-connect w-100 d-block" title="Choose Connect" data-live-search="true">
					<?php
						$all_connects = wp_flowmattic()->connects_db->get_all();
						foreach ( $all_connects as $key => $connect_item ) {
							$connect_id   = $connect_item->id;
							$connect_name = $connect_item->connect_name;
							?>
							<option <# if ( connectID === '<?php echo esc_attr( $connect_id ); ?>' ) { #>selected<# } #> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
							<?php
						}
					?>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs api-headers w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Headers', 'flowmattic' ); ?></h4>
			<input id="fm-checkbox-headers-{{{ time }}}" class="fm-checkbox" name="add-headers" type="checkbox" value="Set Headers" <# if ( 'undefined' !== typeof set_headers && set_headers ) { #>checked<# } #> /> <label for="fm-checkbox-headers-{{{ time }}}"><?php esc_attr_e( 'Set Headers', 'flowmattic' ); ?></label>
			<div class="fm-api-request-headers-body data-headers data-dynamic-fields" data-field-name="api_headers">
				<#
				if ( 'undefined' !== typeof api_headers && ! _.isEmpty( api_headers ) ) {
					_.each( api_headers, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-headers">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{key}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-header">
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
					<div class="fm-dynamic-input-wrap fm-api-request-headers">
						<div class="fm-dynamic-input-field">
							<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value"></textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
						<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-header">
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
				<div class="dynamic-input-add-more fm-api-headers-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-headers"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs api-parameters w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Parameters', 'flowmattic' ); ?></h4>
			<input id="fm-checkbox-parameters-{{{ time }}}" class="fm-checkbox" name="add-parameters" type="checkbox" value="Set Headers" <# if ( 'undefined' !== typeof set_parameters && set_parameters ) { #>checked<# } #>/> <label for="fm-checkbox-parameters-{{{ time }}}"><?php esc_attr_e( 'Set Parameters', 'flowmattic' ); ?></label>
			<div class="fm-api-request-parameters-body data-parameters m-t-20 data-dynamic-fields" data-field-name="api_parameters">
				<#
				if ( 'undefined' !== typeof api_parameters && ! _.isEmpty( api_parameters ) ) {
					_.each( api_parameters, function( value, key ) {
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
							<input class="fm-dynamic-inputs w-100" autocomplete="new-password" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="new-password" name="dynamic-field-value[]" placeholder="value"></textarea>
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
		<div class="form-group custom-json-wrapper hidden w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Custom JSON', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="fm-custom-json w-100 fm-dynamic-inputs dynamic-field-input" name="custom_json" rows="8"><# if ( 'undefined' !== typeof customJSON ) { #>{{{ customJSON }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo sprintf( __( 'Enter your custom JSON code here, or create one. Make sure to <a href="%s" target="_blank">validate the JSON</a> code to avoid failing of task.', 'flowmattic' ), 'https://jsonlint.com/' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Response Format', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<select name="response_format" class="form-control w-100" title="Choose option">
					<#
					if ( 'undefined' !== typeof simple_format ) {
						if ( simple_format && 'Yes' === simple_format ) {
							response_format = 'simple';
						}
						if ( simple_format && 'No' === simple_format ) {
							response_format = 'item_json';
						}
					}
					if ( 'undefined' === typeof response_format ) {
						response_format = 'simple';
					}
					#>
					<?php
					$response_formats = array(
						'simple'          => array(
							'title' => esc_html__( 'Simple', 'flowmattic' ),
							'description' => esc_html__( 'Simple response format to return after extracting individual items.', 'flowmattic' ),
						),
						'item_json'         => array(
							'title' => esc_html__( 'Advanced', 'flowmattic' ),
							'description' => esc_html__( 'Advanced response format includes items in JSON format.', 'flowmattic' ),
						),
					);

					foreach ( $response_formats as $response_format => $options ) {
						$title       = $options['title'];
						$description = $options['description'];
						?>
						<option
							<# if ( ( 'undefined' !== typeof response_format && '<?php echo $response_format; ?>' === response_format ) ) { #>selected<# } #>
							value="<?php echo $response_format; ?>" data-subtext="<?php echo $description; ?>">
							<?php echo $title; ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>Handles the response data received. Choose <code>Advanced</code> if you want to use the data in Iterator or want to send all the data to external service.</p>
			</div>
		</div>
	</div>
</script>
