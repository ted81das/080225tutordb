<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-wordpress-data-template">
	<div class="flowmattic-wordpress-trigger-form-data">
		<div data-fields="trigger-fields" class="flowmattic-wordpress-trigger-data"></div>
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
		<# } #>
		<div class="fm-application-wordpress-data">
			<div class="fm-form-capture-button">
				<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
					<#
					if ( 'undefined' !== typeof capturedData ) {
						#>
						<?php echo esc_attr__( 'Re-capture Response', 'flowmattic' ); ?>
						<#
					} else {
						#>
						<?php echo esc_attr__( 'Capture Response', 'flowmattic' ); ?>
						<#
					}
					#>
				</a>
				<a href="javascript:void(0);" class="btn btn-outline-secondary flowmattic-wp-database-capture-button hidden">
					<?php echo esc_attr__( 'Fetch From Database', 'flowmattic' ); ?>
				</a>
			</div>
			<div class="fm-webhook-capture-data fm-response-capture-data"></div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-page_view-trigger-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Page / Post ID', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="trigger_post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof trigger_post_id ) { #>{{{ trigger_post_id }}}<# } #>" />
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the page / post / custom post type you want to check for this trigger. Leave empty to trigger with any page / post view.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post Type', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="trigger_post_type" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof trigger_post_type ) { #>{{{ trigger_post_type }}}<# } #>" />
		</div>
		<div class="fm-application-instructions">
			<p>Provide the slug of the post type you want to check for this trigger. Leave empty to trigger with any post type view.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-updated_profile_field-trigger-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Profile Field', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="trigger_profile_field" type="search" required class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof trigger_profile_field ) { #>{{{ trigger_profile_field }}}<# } #>" />
		</div>
		<div class="fm-application-instructions">
			<p>Provide the profile field key you want to check for this trigger. Use profile update trigger to check for any profile field update.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-updated_post_meta_field-trigger-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post Meta Field', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="trigger_meta_field" type="search" required class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof trigger_meta_field ) { #>{{{ trigger_meta_field }}}<# } #>" />
		</div>
		<div class="fm-application-instructions">
			<p>Provide the post meta field key you want to check for this trigger. Use post meta update trigger to check for any post meta field update.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-user_role_added-trigger-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'User Role', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div data-field="user-role" class="fm-dynamic-select-field">
			<select name="trigger_user_role" class="dynamic-field-input form-control w-100" required title="Choose User Role" data-live-search="true">
				<option value="any"><?php echo esc_attr__( 'Any User Role', 'flowmattic' ); ?></option>
				<#
				var userRoles = window.FMWPConfig.user_roles;
				_.each( userRoles, function( name, role ) {
					#>
					<option <# if ( 'undefined' !== typeof trigger_user_role && role === trigger_user_role ) { #>selected<# } #> value="{{ role }}">{{ name }}</option>
					<#
				} );
				#>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select the user role to trigger this action.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-user_role_from_specific_to_set-trigger-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'User Role Changed From', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-select-field">
			<select name="trigger_user_role" class="dynamic-field-input form-control w-100" required title="Choose User Role" data-live-search="true">
				<option value="any"><?php echo esc_attr__( 'Any User Role', 'flowmattic' ); ?></option>
				<#
				var userRoles = window.FMWPConfig.user_roles;
				_.each( userRoles, function( name, role ) {
					#>
					<option <# if ( 'undefined' !== typeof trigger_user_role && role === trigger_user_role ) { #>selected<# } #> value="{{ role }}">{{ name }}</option>
					<#
				} );
				#>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select the user role to check for changed from.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'User Role Changed To', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-select-field">
			<select name="trigger_user_role_to" class="dynamic-field-input form-control w-100" required title="Choose User Role" data-live-search="true">
				<option value="any"><?php echo esc_attr__( 'Any User Role', 'flowmattic' ); ?></option>
				<#
				var userRoles = window.FMWPConfig.user_roles;
				_.each( userRoles, function( name, role ) {
					#>
					<option <# if ( 'undefined' !== typeof trigger_user_role_to && role === trigger_user_role_to ) { #>selected<# } #> value="{{ role }}">{{ name }}</option>
					<#
				} );
				#>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select the user role to check for changed to.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-response-template">
<div class="fm-response-body-wrapper">
	<a href="javascript:void(0);" class="fm-response-data-toggle wordpress-data-toggle toggle">
		<span class="fm-response-toggle-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</span>
		<?php echo esc_attr__( 'WordPress Submission Response', 'flowmattic' ); ?>
	</a>
	<div class="fm-response-body wordpress-response-body w-100" style="display:none;">
		<table class="fm-wordpress-response-data-table w-100">
			<thead>
				<tr>
					<th class="w-50">Key</th>
					<th class="w-50">Value</th>
				</tr>
			</thead>
			<tbody>
			<#
			_.each( captureData, function( value, key ) {
				key = FlowMatticWorkflow.UCWords( key );
				#>
				<tr>
					<td>
						<input class="fm-response-form-input w-100" type="text" value="{{{ key }}}" readonly />
					</td>
					<td>
						<textarea class="fm-response-form-input w-100" rows="1" readonly>{{{ value }}}</textarea>
					</td>
				<#
			} );
			#>
			</tbody>
		</table>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-new_user-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Username', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="username" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="username" value="<# if ( 'undefined' !== typeof username ) { #>{{{ username }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Email', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="email" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="email" value="<# if ( 'undefined' !== typeof email ) { #>{{{ email }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'First Name', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="first_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="first_name" value="<# if ( 'undefined' !== typeof first_name ) { #>{{{ first_name }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Last Name', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="last_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="last_name" value="<# if ( 'undefined' !== typeof last_name ) { #>{{{ last_name }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Password', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-input-wrap mb-3">
					<div class="form-check form-switch">
						<input name="auto_password_generation" data-name="user-field-<?php echo flowmattic_random_string(); ?>" class="form-check-input fm-auto-password-generation me-2" type="checkbox" id="fm-checkbox-auto-password-generation" data-field="auto_password_generation" <# if ( 'undefined' !== typeof auto_password_generation && 'Yes' === auto_password_generation ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
						<label for="fm-checkbox-auto-password-generation"><?php esc_attr_e( 'Generate Password Automatically', 'flowmattic' ); ?></label>
					</div>
				</div>
				<div class="wp-custom-password <# if ( 'undefined' !== typeof auto_password_generation && 'Yes' === auto_password_generation ) { #>hidden<# } #>">
					<h6 class="input-title fw-bold"><?php echo esc_attr__( 'Provide Custom User Password', 'flowmattic' ); ?></h6>
					<div class="fm-dynamic-input-field">
						<input name="password" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input wordpress-password w-100" data-field="password" value="<# if ( 'undefined' !== typeof password ) { #>{{{ password }}}<# } #>" />
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<button type="button" class="btn btn-outline-primary wp-generate-pw">Generate password</button>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'User Role(s)', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-user-role-input-field fm-dynamic-input-field">
					<input name="user_role" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" required data-field="user_role" value="<# if ( 'undefined' !== typeof user_role ) {#>{{{ user_role }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Select the user role(s). For more than one user role, enter comma (,) separated list of user roles.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Notification Email', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="notification_type" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Notify to" class="w-100" data-field="notification_type">
						<option <# if ( 'undefined' !== typeof notification_type && 'admin' === notification_type ) { #>selected<# } #> value="admin"><?php echo esc_attr__( 'Admin', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof notification_type && 'user' === notification_type ) { #>selected<# } #> value="user"><?php echo esc_attr__( 'User', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof notification_type && 'both' === notification_type ) { #>selected<# } #> value="both"><?php echo esc_attr__( 'Both', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof notification_type && 'none' === notification_type ) { #>selected<# } #> value="none"><?php echo esc_attr__( 'No one', 'flowmattic' ); ?></option>
					</select>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_user-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'User Id', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="user_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Email', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="email" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="email" value="<# if ( 'undefined' !== typeof email ) { #>{{{ email }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'First Name', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="first_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="first_name" value="<# if ( 'undefined' !== typeof first_name ) { #>{{{ first_name }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Last Name', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="last_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="last_name" value="<# if ( 'undefined' !== typeof last_name ) { #>{{{ last_name }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'User Role(s)', 'flowmattic' ); ?></h4>
				<div class="fm-user-role-input-field fm-dynamic-input-field">
					<input name="user_role" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" data-field="user_role" value="<# if ( 'undefined' !== typeof user_role ) {#>{{{ user_role }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Select the user role(s). For more than one user role, enter comma (,) separated list of user roles.</p>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-new_media-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'File URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="file" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="file" value="<# if ( 'undefined' !== typeof file ) { #>{{{ file }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Alternative Text', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="alt_text" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="alt_text" value="<# if ( 'undefined' !== typeof alt_text ) { #>{{{ alt_text }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Caption', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="caption" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="caption" value="<# if ( 'undefined' !== typeof caption ) { #>{{{ caption }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Description', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="description" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="description" rows="1"><# if ( 'undefined' !== typeof description ) { #>{{{ description }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-new_post-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_title" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="post_title" value="<# if ( 'undefined' !== typeof post_title ) { #>{{{ post_title }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Type', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-post-type-field">
					<select name="post_type" data-name="user-field-post_type" title="Choose Post Type" class="w-100" required data-field="post_type">
						<?php
						$post_types = get_post_types(
							array(
								'public' => true,
							),
							'objects'
						);

						foreach ( $post_types as $post_type ) {
							?>
							<option <# if ( 'undefined' !== typeof post_type && '<?php echo $post_type->name?>' === post_type ) { #>selected<# } #> value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_attr( $post_type->labels->singular_name ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Content', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<div id="flowmattic-content-editor" class="flowmattic-content-editor"></div>
					<div class="d-none">
						<textarea name="post_content" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100 content-editor-input" data-field="post_content" rows="5"><# if ( 'undefined' !== typeof post_content ) { #>{{{ post_content }}}<# } #></textarea>
					</div>
				</div>
				<div class="fm-application-instructions">
					<p>Use the content editor to create the post content. You can also use dynamic fields to populate the content. Type @ to open the dynamic field popup and select the field you want to use.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Excerpt', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_excerpt" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_excerpt" rows="1"><# if ( 'undefined' !== typeof post_excerpt ) { #>{{{ post_excerpt }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Date', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_date" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_date" rows="1"><# if ( 'undefined' !== typeof post_date ) { #>{{{ post_date }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Date GMT', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_date_gmt" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_date_gmt" rows="1"><# if ( 'undefined' !== typeof post_date_gmt ) { #>{{{ post_date_gmt }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Slug', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_name" rows="1"><# if ( 'undefined' !== typeof post_name ) { #>{{{ post_name }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Parent', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_parent" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_parent" rows="1"><# if ( 'undefined' !== typeof post_parent ) { #>{{{ post_parent }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Provide the ID of the parent post if this is a child post.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Password', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_password" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_password" rows="1"><# if ( 'undefined' !== typeof post_password ) { #>{{{ post_password }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Status', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-post-type-field">
					<select name="post_status" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Status" class="w-100" required data-field="post_status">
						<option <# if ( 'undefined' !== typeof post_status && 'publish' === post_status ) { #>selected<# } #> value="publish"><?php echo esc_attr__( 'Publish', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof post_status && 'pending' === post_status ) { #>selected<# } #> value="pending"><?php echo esc_attr__( 'Pending Review', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof post_status && 'draft' === post_status ) { #>selected<# } #> value="draft"><?php echo esc_attr__( 'Draft', 'flowmattic' ); ?></option>
					</select>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Featured Image ID / URL', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_thumbnail_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="post_thumbnail_id" value="<# if ( 'undefined' !== typeof post_thumbnail_id ) { #>{{{ post_thumbnail_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Provide the Image ID ( if image imported in previous steps ) OR image URL to import and set as featured image.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Author ID / Email', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_author_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="post_author_id" value="<# if ( 'undefined' !== typeof post_author_id ) { #>{{{ post_author_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
      		<div class="form-group w-100 fm-post-taxonomies">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Categories', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_category" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Categories" multiple data-actions-box="true" class="w-100" data-field="post_category">
		            <?php
		            $post_categories = get_categories(
		              array(
		                'orderby'    => 'name',
		                'order'      => 'ASC',
		                'hide_empty' => false
		              )
		            );

		            foreach ( $post_categories as $cat ) {
		                ?>
		                <option <# if ( 'undefined' !== typeof post_category && -1 !== jQuery.inArray( '<?php echo esc_attr( $cat->term_id ); ?>', post_category ) ) { #>selected<# } #> value="<?php echo esc_attr( $cat->term_id ); ?>">
		                  <?php echo esc_attr( $cat->name ); ?>
		                </option>
		                <?php
		            }
		            ?>
					</select>
				</div>
			</div>
			<div class="form-group w-100 fm-post-taxonomies">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Tags', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_tags" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Tags" multiple data-actions-box="true" class="w-100" data-field="post_tags">
		            <?php
		            $post_tags = get_tags(
		              array(
		                'orderby'    => 'name',
		                'order'      => 'ASC',
		                'hide_empty' => false
		              )
		            );

		            foreach ( $post_tags as $cat ) {
		                ?>
		                <option <# if ( 'undefined' !== typeof post_tags && -1 !== jQuery.inArray( '<?php echo esc_attr( $cat->slug ); ?>', post_tags ) ) { #>selected<# } #> value="<?php echo esc_attr( $cat->slug ); ?>">
		                  <?php echo esc_attr( $cat->name ); ?>
		                </option>
		                <?php
		            }
		            ?>
					</select>
				</div>
			</div>
			<div class="form-group dynamic-inputs w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Custom Fields', 'flowmattic' ); ?></h4>
				<div class="fm-custom-fields-body data-dynamic-fields" data-field-name="custom_fields">
					<#
					var customFields = 'undefined' !== typeof actionAppArgs.custom_fields ? actionAppArgs.custom_fields : undefined;

					if ( 'undefined' !== typeof customFields ) {
						var custom_fields = customFields;
					}

					if ( 'undefined' !== typeof custom_fields && ! _.isEmpty( custom_fields ) ) {
						_.each( custom_fields, function( value, key ) {
							#>
							<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 gap-2 p-2 mb-2 border rounded">
								<div class="d-flex flex-column gap-2 w-100 mb-2">
									<div class="fm-dynamic-input-field">
										<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
									</div>
									<div class="fm-dynamic-input-field">
										<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value">{{{value}}}</textarea>
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
									<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
								</div>
								<div class="fm-dynamic-input-field">
									<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="off" name="dynamic-field-value[]" rows="1" placeholder="value"></textarea>
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
			<div class="form-group dynamic-inputs w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Custom Taxonomies', 'flowmattic' ); ?></h4>
				<div data-fields="custom-taxonomy" class="fm-custom-fields-body data-dynamic-fields custom-taxonomy-fields d-flex flex-column gap-3" data-field-name="custom_taxonomies"></div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-new_comment-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="post_id" value="<# if ( 'undefined' !== typeof post_id ) { #>{{{ post_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Comment', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="comment" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="comment" value="<# if ( 'undefined' !== typeof comment ) { #>{{{ comment }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Author Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="author" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="author" value="<# if ( 'undefined' !== typeof author ) { #>{{{ author }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Author Email', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="email" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="email" value="<# if ( 'undefined' !== typeof email ) { #>{{{ email }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Author Website URL', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="author_url" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="author_url" value="<# if ( 'undefined' !== typeof author_url ) { #>{{{ author_url }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_post-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" required autocomplete="off" data-field="post_id" value="<# if ( 'undefined' !== typeof post_id ) { #>{{{ post_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Title', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_title" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="post_title" value="<# if ( 'undefined' !== typeof post_title ) { #>{{{ post_title }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Content', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<div id="flowmattic-content-editor" class="flowmattic-content-editor"></div>
				</div>
				<div class="fm-dynamic-input-field d-none">
					<textarea name="post_content" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100 content-editor-input" data-field="post_content" rows="5"><# if ( 'undefined' !== typeof post_content ) { #>{{{ post_content }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Use the content editor to create the post content. You can also use dynamic fields to populate the content. Type @ to open the dynamic field popup and select the field you want to use.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Excerpt', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_excerpt" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" data-field="post_excerpt" rows="2"><# if ( 'undefined' !== typeof post_excerpt ) { #>{{{ post_excerpt }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Slug', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_name" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_name" rows="1"><# if ( 'undefined' !== typeof post_name ) { #>{{{ post_name }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Parent', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<textarea name="post_parent" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-textarea w-100" data-field="post_parent" rows="1"><# if ( 'undefined' !== typeof post_parent ) { #>{{{ post_parent }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Provide the ID of the parent post if this is a child post.</p>
				</div>
			</div>
      		<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Type', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_type" data-name="user-field-post_type" title="Choose Post Type" class="w-100" data-field="post_type">
						<?php
						$post_types = get_post_types(
							array(
								'public' => true,
							),
							'objects'
						);

						foreach ( $post_types as $post_type ) {
							?>
							<option <# if ( 'undefined' !== typeof post_type && '<?php echo $post_type->name?>' === post_type ) { #>selected<# } #> value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_attr( $post_type->labels->singular_name ); ?></option>
							<?php
						}
						?>
            			<option data-reset="true" value="fm-reset" class="text-danger"><?php echo esc_html__( 'RESET', 'flowmattic' ); ?></option>
					</select>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Status', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_status" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Status" class="w-100" data-field="post_status">
						<option <# if ( 'undefined' !== typeof post_status && 'publish' === post_status ) { #>selected<# } #> value="publish"><?php echo esc_attr__( 'Publish', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof post_status && 'pending' === post_status ) { #>selected<# } #> value="pending"><?php echo esc_attr__( 'Pending Review', 'flowmattic' ); ?></option>
						<option <# if ( 'undefined' !== typeof post_status && 'draft' === post_status ) { #>selected<# } #> value="draft"><?php echo esc_attr__( 'Draft', 'flowmattic' ); ?></option>
						<option data-reset="true" value="fm-reset" class="text-danger"><?php echo esc_html__( 'RESET', 'flowmattic' ); ?></option>
					</select>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Featured Image ID / URL', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_thumbnail_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="post_thumbnail_id" value="<# if ( 'undefined' !== typeof post_thumbnail_id ) { #>{{{ post_thumbnail_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Provide the Image ID ( if image imported in previous steps ) OR image URL to import and set as featured image.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Author ID / Email', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input name="post_author_id" data-name="user-field-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input w-100" autocomplete="off" data-field="post_author_id" value="<# if ( 'undefined' !== typeof post_author_id ) { #>{{{ post_author_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group w-100 fm-post-taxonomies">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Categories', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_category" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Categories" multiple class="w-100 fm-show-tick show-tick" data-actions-box="true" data-field="post_category">
		            <?php
		            $post_categories = get_categories(
		              array(
		                'orderby'    => 'name',
		                'order'      => 'ASC',
		                'hide_empty' => false
		              )
		            );

		            foreach ( $post_categories as $cat ) {
		                ?>
		                <option <# if ( 'undefined' !== typeof post_category && -1 !== jQuery.inArray( '<?php echo esc_attr( $cat->term_id ); ?>', post_category ) ) { #>selected<# } #> value="<?php echo esc_attr( $cat->term_id ); ?>">
		                  <?php echo esc_attr( $cat->name ); ?>
		                </option>
		                <?php
		            }
		            ?>
					</select>
				</div>
			</div>
			<div class="form-group w-100 fm-post-taxonomies">
				<h4 class="input-title"><?php echo esc_attr__( 'Post Tags', 'flowmattic' ); ?></h4>
				<div class="fm-post-type-field">
					<select name="post_tags" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose Post Tags" multiple class="w-100 fm-show-tick show-tick" data-actions-box="true" data-field="post_tags">
					<?php
		            $post_tags = get_tags(
		              array(
		                'orderby'    => 'name',
		                'order'      => 'ASC',
		                'hide_empty' => false
		              )
		            );

		            foreach ( $post_tags as $cat ) {
		                ?>
		                <option <# if ( 'undefined' !== typeof post_tags && -1 !== jQuery.inArray( '<?php echo esc_attr( $cat->slug ); ?>', post_tags ) ) { #>selected<# } #> value="<?php echo esc_attr( $cat->slug ); ?>">
		                  <?php echo esc_attr( $cat->name ); ?>
		                </option>
		                <?php
		            }
		            ?>
					</select>
				</div>
			</div>
			<div class="form-group dynamic-inputs w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Custom Fields', 'flowmattic' ); ?></h4>
				<div class="fm-custom-fields-body data-dynamic-fields" data-field-name="custom_fields">
					<#
					if ( 'undefined' !== typeof custom_fields && ! _.isEmpty( custom_fields ) ) {
						_.each( custom_fields, function( value, key ) {
							#>
							<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 gap-2 p-2 mb-2 border rounded">
								<div class="d-flex flex-column gap-2 w-100 mb-2">
									<div class="fm-dynamic-input-field">
										<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
									</div>
									<div class="fm-dynamic-input-field">
										<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value">{{{value}}}</textarea>
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
									<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
								</div>
								<div class="fm-dynamic-input-field">
									<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="off" name="dynamic-field-value[]" rows="1" placeholder="value"></textarea>
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
			<div class="form-group dynamic-inputs w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Custom Taxonomies', 'flowmattic' ); ?></h4>
				<div data-fields="custom-taxonomy" class="fm-custom-fields-body data-dynamic-fields custom-taxonomy-fields d-flex flex-column gap-3" data-field-name="custom_taxonomies"></div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_user_meta-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="fm-application-wordpress-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'User ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input name="user_id" data-name="user_id" type="text" class="dynamic-field-input form-control w-100" required autocomplete="off" data-field="user_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.user_id }}}<# } #>" />
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="form-group dynamic-inputs w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'User Meta', 'flowmattic' ); ?></h4>
				<div class="fm-custom-fields-body data-dynamic-fields" data-field-name="meta_fields">
					<#
					if( 'undefined' !== typeof meta_fields ) {
						_.each( meta_fields, function( value, key ) {
							#>
							<div class="fm-dynamic-input-wrap fm-custom-fields">
								<div class="fm-dynamic-input-field">
									<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
								</div>
								<div class="fm-dynamic-input-field">
									<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value">{{{value}}}</textarea>
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
						<div class="fm-dynamic-input-wrap fm-custom-fields">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value"></textarea>
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
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_user_by_id-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="user_id" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.user_id ) { #>{{{ actionAppArgs.user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to get the user details.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_user_by_email-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User Email <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="user_email" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.user_email ) { #>{{{ actionAppArgs.user_email }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user email to get the user details.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-create_category-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Category Name <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="category_name" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.category_name ) { #>{{{ actionAppArgs.category_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the category name to create the category.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_category-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="cat_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cat_id ) { #>{{{ actionAppArgs.cat_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the category you want to update.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category Name', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="cat_name" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cat_name ) { #>{{{ actionAppArgs.cat_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category Description', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea name="cat_description" class="dynamic-field-input fm-textarea form-control w-100" rows="1" autocomplete="off"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cat_description ) { #>{{{ actionAppArgs.cat_description }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category Slug', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="cat_slug" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cat_slug ) { #>{{{ actionAppArgs.cat_slug }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_tag-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_id ) { #>{{{ actionAppArgs.tag_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the tag you want to update.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag Name', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_name" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_name ) { #>{{{ actionAppArgs.tag_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag Description', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea name="tag_description" class="dynamic-field-input fm-textarea form-control w-100" rows="1" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_description ) { #>{{{ actionAppArgs.tag_description }}}<# } #>"></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag Slug', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_slug" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_slug ) { #>{{{ actionAppArgs.tag_slug }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-update_term-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_id ) { #>{{{ actionAppArgs.tag_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the tag you want to update.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Taxonomy', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="term_taxonomy" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.term_taxonomy ) { #>{{{ actionAppArgs.term_taxonomy }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Name', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_name" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_name ) { #>{{{ actionAppArgs.tag_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Description', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea name="tag_description" class="dynamic-field-input fm-textarea form-control w-100" rows="1" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_description ) { #>{{{ actionAppArgs.tag_description }}}<# } #>"></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Slug', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_slug" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_slug ) { #>{{{ actionAppArgs.tag_slug }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-create_tag-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Tag Name <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="tag_name" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_name ) { #>{{{ actionAppArgs.tag_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user email to get the user details.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-add_role-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Role Name <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="role_name" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.role_name ) { #>{{{ actionAppArgs.role_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user role name.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Role Display Name <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" name="role_display_name" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.role_display_name ) { #>{{{ actionAppArgs.role_display_name }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Display name for role.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Role Capabilities</h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control user-role-capabilities dynamic-field-input w-100" name="role_capabilities" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.role_capabilities ) { #>{{{ actionAppArgs.role_capabilities }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Provide one or more user role capabilities seperated by comma.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-add_user_role-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="user_id" class="form-control dynamic-field-input w-100" name="user-field-user_id" required data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to add new user role.</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'User Role(s)', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-user-role-input-field fm-dynamic-input-field">
				<input name="user_role" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" required data-field="user_role" value="<# if ( 'undefined' !== typeof user_role ) {#>{{{ user_role }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Select the user role(s). For more than one user role, enter comma (,) separated list of user roles.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-change_user_role-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="user_id" class="form-control dynamic-field-input w-100" name="user-field-user_id" required data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to change user role.</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'User Role(s)', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-user-role-input-field fm-dynamic-input-field">
				<input name="user_role" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" required data-field="user_role" value="<# if ( 'undefined' !== typeof user_role ) {#>{{{ user_role }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Select the user role(s). For more than one user role, enter comma (,) separated list of user roles.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-remove_user_role-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="user_id" class="form-control dynamic-field-input w-100" name="user-field-user_id" required data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to remove user role.</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'User Role(s)', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-user-role-input-field fm-dynamic-input-field">
				<input name="user_role" data-name="user-field-<?php echo flowmattic_random_string(); ?>" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" required data-field="user_role" value="<# if ( 'undefined' !== typeof user_role ) {#>{{{ user_role }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Select the user role(s). For more than one user role, enter comma (,) separated list of user roles.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-delete_user-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="user_id" class="form-control dynamic-field-input w-100" name="user-field-user_id" required data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to remove user role.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Reassign User ID</h4>
			<div class="fm-dynamic-input-field">
				<input name="reassign_id" class="form-control dynamic-field-input w-100" name="user-field-user_reassign" data-field="reassign_id" value="<# if ( 'undefined' !== typeof reassign_id ) { #>{{{ reassign_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user ID to reassign posts and links of the user being deleted.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-delete_post-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Post ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="post_id" class="form-control dynamic-field-input w-100" name="user-field-post_id" required data-field="post_id" value="<# if ( 'undefined' !== typeof post_id ) { #>{{{ post_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the Post ID to delete the post.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_post_meta-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Post ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="post_id" class="form-control dynamic-field-input w-100" name="user-field-post_id" required data-field="post_id" value="<# if ( 'undefined' !== typeof post_id ) { #>{{{ post_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the Post ID to retrieve metadata.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Post Meta Key</h4>
			<div class="fm-dynamic-input-field">
				<input name="post_meta_key" class="form-control dynamic-field-input w-100" name="user-field-post_meta_key" data-field="post_meta_key" value="<# if ( 'undefined' !== typeof post_meta_key ) { #>{{{ post_meta_key }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the post meta key to retrieve data for single meta. Leave empty to retrieve all metadata for the post. ( Supports ACF Post Meta Fields )</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_posts_by_post_type-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Post Type <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-post-type-field">
				<select name="post_type" data-name="post_type" title="Choose Post Type" class="form-control w-100" required data-field="post_type">
					<?php
					$post_types = get_post_types(
						array(
							'public' => true,
						),
						'objects'
					);

					foreach ( $post_types as $post_type ) {
						?>
						<option <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $post_type->name?>' === actionAppArgs.post_type ) { #>selected<# } #> value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_attr( $post_type->labels->singular_name ); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>Select the Post type to retrieve posts.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Number of Posts</h4>
			<div class="fm-dynamic-input-field">
				<input name="posts_per_page" class="form-control dynamic-field-input w-100" name="posts_per_page" data-field="posts_per_page" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.posts_per_page }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the number of posts to retrieve.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Query Order</h4>
			<div class="fm-dynamic-input-field">
				<input name="order" class="form-control dynamic-field-input w-100" name="order" data-field="order" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.order }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the order in which you want to query the posts. Accepted values: <code>ASC</code>, <code>DESC</code>.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Query Order By</h4>
			<select name="orderby" class="widget-select form-control w-100" title="Select Order By">
			  <?php
			  $options = array(
			  	'none'          => esc_html__( 'No order', 'flowmattic' ),
			  	'ID'            => esc_html__( 'Order by post id', 'flowmattic' ),
			  	'author'        => esc_html__( 'Order by author', 'flowmattic' ),
			  	'title'         => esc_html__( 'Order by title', 'flowmattic' ),
			  	'name'          => esc_html__( 'Order by post name (post slug)', 'flowmattic' ),
			  	'date'          => esc_html__( 'Order by date', 'flowmattic' ),
			  	'modified'      => esc_html__( 'Order by last modified date', 'flowmattic' ),
			  	'parent'        => esc_html__( 'Order by post/page parent id', 'flowmattic' ),
			  	'rand'          => esc_html__( 'Random order', 'flowmattic' ),
			  	'comment_count' => esc_html__( 'Order by number of comments', 'flowmattic' ),
			  );

			  foreach ( $options as $type => $title ) {
				?>
				<option
				  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $type; ?>' === actionAppArgs.orderby ) { #>selected<# } #>
				  value="<?php echo $type; ?>">
				  <?php echo $title; ?>
				</option>
				<?php
			  }
			  ?>
			</select>
			<div class="fm-application-instructions">
				<p>Select orderby value to query posts.</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Get All Posts in Single Array', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="all_posts_as_array" id="fm-checkbox-get-all-posts-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.all_posts_as_array ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-get-all-posts-{{eid}}"><?php esc_attr_e( 'Retrieve all the posts in a single array, which will help you use in Iterator to loop through each post.', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Exclude Post Content', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="exclude_post_content" id="fm-checkbox-exclude-content-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.exclude_post_content ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-exclude-content-{{eid}}"><?php esc_attr_e( 'Enable to exclude the post content from the response. This will reduce the response size significantly and fix the issues due to conflicts with post content escaping.', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Exclude Post Meta', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="exclude_post_meta" id="fm-checkbox-exclude-meta-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.exclude_post_meta ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-exclude-meta-{{eid}}"><?php esc_attr_e( 'Enable to exclude the post meta from the response. This will reduce the response size significantly and fix issues due to meta value escaping.', 'flowmattic' ); ?></label></div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_posts_by_meta-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">Post Type <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-post-type-field">
				<select name="post_type" data-name="post_type" title="Choose Post Type" class="form-control w-100" required data-field="post_type">
					<?php
					$post_types = get_post_types(
						array(
							'public' => true,
						),
						'objects'
					);

					foreach ( $post_types as $post_type ) {
						?>
						<option <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $post_type->name?>' === actionAppArgs.post_type ) { #>selected<# } #> value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_attr( $post_type->labels->singular_name ); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>Select the Post type to retrieve posts.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Post Meta Key <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="post_meta_key" class="form-control dynamic-field-input w-100" required name="post_meta_key" data-field="post_meta_key" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.post_meta_key }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the post meta key to retrieve posts for.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Post Meta Value <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="post_meta_value" class="form-control dynamic-field-input w-100" required name="post_meta_value" data-field="post_meta_value" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.post_meta_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the post meta value to search posts for.</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Get All Posts in Single Array', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="all_posts_as_array" id="fm-checkbox-get-all-posts-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.all_posts_as_array ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-get-all-posts-{{eid}}"><?php esc_attr_e( 'Retrieve all the posts in a single array, which will help you use in Iterator to loop through each post.', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Exclude Post Content', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="exclude_post_content" id="fm-checkbox-exclude-content-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.exclude_post_content ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-exclude-content-{{eid}}"><?php esc_attr_e( 'Enable to exclude the post content from the response. This will reduce the response size significantly and fix the issues due to conflicts with post content escaping.', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Exclude Post Meta', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="exclude_post_meta" id="fm-checkbox-exclude-meta-{{eid}}" <# if ( 'undefined' === typeof actionAppArgs || 'Yes' === actionAppArgs.exclude_post_meta ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-checkbox-exclude-meta-{{eid}}"><?php esc_attr_e( 'Enable to exclude the post meta from the response. This will reduce the response size significantly and fix issues due to meta value escaping.', 'flowmattic' ); ?></label></div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_user_meta-action-template">
	<div class="flowmattic-wordpress-action-form-data">
		<div class="form-group">
			<h4 class="fm-input-title">User ID <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input name="user_id" class="form-control dynamic-field-input w-100" name="user-field-user_id" required data-field="user_id" value="<# if ( 'undefined' !== typeof user_id ) { #>{{{ user_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the User ID to retrieve metadata.</p>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">User Meta Key</h4>
			<div class="fm-dynamic-input-field">
				<input name="user_meta_key" class="form-control dynamic-field-input w-100" name="user-field-user_meta_key" data-field="user_meta_key" value="<# if ( 'undefined' !== typeof user_meta_key ) { #>{{{ user_meta_key }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>Enter the user meta key to retrieve data for single meta. Leave empty to retrieve all metadata for the user. ( Supports ACF User Meta Fields )</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-delete_media-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Attachment ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="media_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.media_id ) { #>{{{ actionAppArgs.media_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the Attachment from the media library to delete the media.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-rename_media-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Attachment ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="media_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.media_id ) { #>{{{ actionAppArgs.media_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the Attachment from the media library to rename the media.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'New Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="media_title" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.media_title ) { #>{{{ actionAppArgs.media_title }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the new title for the media.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_post_taxonomies-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to get the taxonomies names for.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_the_terms-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to get the terms of the taxonomy for.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_name ) { #>{{{ actionAppArgs.taxonomy_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Name of the taxonomy to get the terms for.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_post_by_id-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to get the details of.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_taxonomy_by_name-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Term', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tax_term" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tax_term ) { #>{{{ actionAppArgs.tax_term }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy term to get the details of. e.g category</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tax_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tax_name ) { #>{{{ actionAppArgs.tax_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy to search for. e.g software</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_all_users_by_role-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'User Role', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-user-role-input-field fm-dynamic-input-field">
			<input name="user_role" data-name="user_role" title="Choose User Role" class="dynamic-field-input form-control user-role-field w-100" required data-field="user_role" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.user_role ) {#>{{{ actionAppArgs.user_role }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-add_tag_to_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to add the tag to.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_name ) { #>{{{ actionAppArgs.tag_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the tag to add to the post.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-remove_tag_from_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to remove the tag from.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Tag Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="tag_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.tag_name ) { #>{{{ actionAppArgs.tag_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the tag to remove from the post.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-add_category_to_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to add the category to.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category Name or Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="category_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.category_name ) { #>{{{ actionAppArgs.category_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name or slug of the category to add to the post.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-remove_category_from_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to remove the category from.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Category Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="category_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.category_name ) { #>{{{ actionAppArgs.category_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the category to remove from the post.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-check_plugin_active-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Plugin File', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="plugin" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.plugin ) { #>{{{ actionAppArgs.plugin }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the plugin file name of the plugin to check if it is active. e.g <code>contact-form-7/wp-contact-form-7.php</code></p>
			<p>You can find the plugin file name in the <a href="<?php echo admin_url( 'plugin-editor.php' ); ?>" target="_blank">plugin file editor</a> screen.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-activate_plugin-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Plugin File', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="plugin" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.plugin ) { #>{{{ actionAppArgs.plugin }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the plugin file name of the plugin to activate. e.g <code>contact-form-7/wp-contact-form-7.php</code></p>
			<p>You can find the plugin file name in the <a href="<?php echo admin_url( 'plugin-editor.php' ); ?>" target="_blank">plugin file editor</a> screen.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-search_media_by_title-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Media Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="media_title" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.media_title ) { #>{{{ actionAppArgs.media_title }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the title of the media to search for.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-add_taxonomy_to_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to add the taxonomy to.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_name ) { #>{{{ actionAppArgs.taxonomy_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy to add to the post. e.g category</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Term', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_term" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_term ) { #>{{{ actionAppArgs.taxonomy_term }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy term to add to the post. e.g wordpress</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-remove_taxonomy_from_post-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Post ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="post_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.post_id ) { #>{{{ actionAppArgs.post_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the post you want to remove the taxonomy from.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_name ) { #>{{{ actionAppArgs.taxonomy_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy to remove from the post. e.g category</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Term', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_term" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_term ) { #>{{{ actionAppArgs.taxonomy_term }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy term to remove from the post. e.g wordpress</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-create_term-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Taxonomy Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="taxonomy_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.taxonomy_name ) { #>{{{ actionAppArgs.taxonomy_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the taxonomy to create the term for. e.g category</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="term_name" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.term_name ) { #>{{{ actionAppArgs.term_name }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the name of the term to create. e.g wordpress</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="term_slug" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.term_slug ) { #>{{{ actionAppArgs.term_slug }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the slug of the term to create. e.g wordpress</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Description', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea name="term_description" class="dynamic-field-input form-control w-100" autocomplete="off"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.term_description ) { #>{{{ actionAppArgs.term_description }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the description of the term to create.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Parent Term ID', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="parent_term_id" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.parent_term_id ) { #>{{{ actionAppArgs.parent_term_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the parent term to create the term under.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Term Alias Of', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input name="alias_of" type="search" class="dynamic-field-input form-control w-100" autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.alias_of ) { #>{{{ actionAppArgs.alias_of }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the slug of the term to create the term as an alias of.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-get_media_by_id-action-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Media ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input name="media_id" type="search" class="dynamic-field-input form-control w-100" required autocomplete="off" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.media_id ) { #>{{{ actionAppArgs.media_id }}}<# } #>" />
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Provide the ID of the media to get the details of.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-wordpress-taxonomy-field-template">
<#
if ( ! _.isEmpty( taxonomies ) ) {
	_.each( taxonomies, function( value, key ) {
		var taxValue = 'undefined' !== typeof actionAppArgs.custom_taxonomies && 'undefined' !== typeof actionAppArgs.custom_taxonomies[ key ] ? actionAppArgs.custom_taxonomies[ key ] : '';
		#>
		<div class="fm-dynamic-input-wrap fm-custom-fields d-flex w-100 flex-column gap-2">
			<div class="fm-dynamic-input-field">
				<label class="fm-input-title" for="taxonomy-{{key}}">{{{value}}}</label>
				<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="hidden" placeholder="key" value="{{{key}}}" />
			</div>
			<div class="fm-dynamic-input-field">
				<textarea class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="Comma separated list of {{key}} taxonomy slugs">{{{taxValue}}}</textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<#
	} );
} else {
	#>
	<p><?php esc_attr_e( 'No taxonomies found for the selected post type.', 'flowmattic' ); ?></p>
	<#
}
#>
</script>
<script type="text/javascript">
<?php
// Get the list of all the available post types.
$post_types = get_post_types(
	array(
		'public' => true,
	),
	'objects'
);

// Get the list of all taxonomies for the post types.
$taxonomies = array();
foreach ( $post_types as $post_type ) {
	$object_taxonomies = get_object_taxonomies( $post_type->name, 'objects' );	

	// Remove the default taxonomies.
	$default_taxonomies = array( 'category', 'post_tag', 'post_format' );

	$post_tax = array();
	foreach ( $object_taxonomies as $taxonomy ) {
		if ( in_array( $taxonomy->name, $default_taxonomies ) ) {
			continue;
		}

		$post_tax[ $taxonomy->name ] = $taxonomy->label;
	}

	$taxonomies[ $post_type->name ] = $post_tax;
}

echo 'var flowmattic_taxonomies = ' . wp_json_encode( $taxonomies ) . ';';
?>
</script>