<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 4.0
 */
?>
<script type="text/html" id="flowmattic-application-tools-action-data-template">
	<div data-field="tools" class="tools-action-data"></div>
</script>
<script type="text/html" id="flowmattic-tools-action-get_variable_value-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_name" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_name ) { #>{{{ actionAppArgs.variable_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable name you want to pull the value of. Make sure it is not wrapped in curly braces. eg. my_custom_app', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-set_variable_value-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_name" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_name ) { #>{{{ actionAppArgs.variable_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable name you want to pull the value of. Make sure it is not wrapped in curly braces. eg. my_custom_app', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Value', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_value" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_value ) { #>{{{ actionAppArgs.variable_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'The new value for the variable.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-turn_on_workflow-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Workflow ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="fm_workflow_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.fm_workflow_id ) { #>{{{ actionAppArgs.fm_workflow_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the workflow ID you want to turn on. You can find the workflow ID in the URL when you are editing the workflow.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-turn_off_workflow-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Workflow ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="fm_workflow_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.fm_workflow_id ) { #>{{{ actionAppArgs.fm_workflow_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the workflow ID you want to turn off. You can find the workflow ID in the URL when you are editing the workflow.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-get_workflow_status-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Workflow ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="fm_workflow_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.fm_workflow_id ) { #>{{{ actionAppArgs.fm_workflow_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the workflow ID you want to get the status of. You can find the workflow ID in the URL when you are editing the workflow.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-redirect-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Redirect URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="redirect_url" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.redirect_url ) { #>{{{ actionAppArgs.redirect_url }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the URL you want to redirect to.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="alert alert-warning" role="alert">
			<?php echo esc_attr__( 'Note: Redirect only works if the trigger app allows. Better to use at the end of the workflow.', 'flowmattic' ); ?>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-generate_magic_login_link-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'User ID or Email', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="user_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.user_id ) { #>{{{ actionAppArgs.user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the user ID or email address of the user you want to generate the magic login link for.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="alert alert-warning" role="alert">
			<?php echo esc_attr__( 'Note: Magic login link will be valid for an hour only and can be used only once.', 'flowmattic' ); ?>
		</div>
	</div>
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Redirect URL', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="redirect_url" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.redirect_url ) { #>{{{ actionAppArgs.redirect_url }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the URL you want the user to be redirected after clicking the magic login link. Default is the home page.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Redirect URL on Expired', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="redirect_url_on_expired" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.redirect_url_on_expired ) { #>{{{ actionAppArgs.redirect_url_on_expired }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the URL you want the user to be redirected after the magic login link expires. Default is the login page.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-create_local_variable-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_name" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_name ) { #>{{{ actionAppArgs.variable_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable name you want to create. eg. my_custom_variable', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Type', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<select name="variable_type" class="form-control dynamic-field-input w-100">
					<option value="string" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_type && actionAppArgs.variable_type === 'string' ) { #>selected<# } #>><?php esc_attr_e( 'String', 'flowmattic' ); ?></option>
					<option value="array" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_type && actionAppArgs.variable_type === 'array' ) { #>selected<# } #>><?php esc_attr_e( 'Array', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Select the type of the variable.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100 variable-type-values variable-type-string">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Value', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_value" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_value ) { #>{{{ actionAppArgs.variable_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'The value for the variable.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="w-100 variable-type-values variable-type-array">
			<div class="form-group dynamic-inputs api-parameters w-100">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Values', 'flowmattic' ); ?></h4>
				<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="variable_array_values">
					<#
					if ( 'undefined' !== typeof variable_array_values && ! _.isEmpty( variable_array_values ) ) {
						_.each( variable_array_values, function( value, key ) {
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
		</div>
		<div class="alert alert-warning" role="alert">
			<?php echo esc_attr__( 'Note: Variable will be available only in the current workflow.', 'flowmattic' ); ?>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-get_local_variable-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_id ) { #>{{{ actionAppArgs.variable_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable ID you want to get the value of. You can get the variable ID from the create local variable action.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="alert alert-warning" role="alert">
			<?php echo esc_attr__( 'Note: Variable ID not the variable name. Variable ID is the unique ID assigned to the variable when it is created.', 'flowmattic' ); ?>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-update_local_variable-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_id ) { #>{{{ actionAppArgs.variable_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable ID you want to update. You can get the variable ID from the create local variable action.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Type', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<select name="variable_type" class="form-control dynamic-field-input w-100">
					<option value="string" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_type && actionAppArgs.variable_type === 'string' ) { #>selected<# } #>><?php esc_attr_e( 'String', 'flowmattic' ); ?></option>
					<option value="array" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_type && actionAppArgs.variable_type === 'array' ) { #>selected<# } #>><?php esc_attr_e( 'Array', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Select the type of the variable.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100 variable-type-values variable-type-string">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Value', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_value" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_value ) { #>{{{ actionAppArgs.variable_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'The value for the variable.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="w-100 variable-type-values variable-type-array">
			<div class="form-group dynamic-inputs api-parameters w-100">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Variable Values', 'flowmattic' ); ?></h4>
				<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="variable_array_values">
					<#
					if ( 'undefined' !== typeof variable_array_values && ! _.isEmpty( variable_array_values ) ) {
						_.each( variable_array_values, function( value, key ) {
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
		</div>
		<div class="alert alert-warning" role="alert">
			<?php echo esc_attr__( 'Note: Variable will be available only in the current workflow. Updated value will be available in the next steps.', 'flowmattic' ); ?>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-tools-action-delete_local_variable-template">
	<div class="tools-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Variable ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="variable_id" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.variable_id ) { #>{{{ actionAppArgs.variable_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the variable ID you want to delete. You can get the variable ID from the create local variable action.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="alert alert-warning" role="alert">
			<ul style="list-style: disc;padding-left: 10px;">
				<li><?php echo esc_attr__( 'Variable ID not the variable name. Variable ID is the unique ID assigned to the variable when it is created.', 'flowmattic' ); ?></li>
				<li><?php echo esc_attr__( 'Deleted variable will not be available in the next steps.', 'flowmattic' ); ?></li>
				<li><?php echo esc_attr__( 'Deleted variable will not be available in the current workflow, unless it is created again.', 'flowmattic' ); ?></li>
			</ul>
			<p><?php echo esc_attr__( 'Note: Variable will be deleted only in the current workflow.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>