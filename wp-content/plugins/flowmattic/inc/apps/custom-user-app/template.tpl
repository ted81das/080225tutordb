<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 3.0
 */
?>
<script type="text/html" id="flowmattic-custom-app-action-data-template">
	<#
	var application    = appData.application,
		action         = appData.action,
		actionAppArgs  = ( 'undefined' !== typeof appData.actionAppArgs ) ? appData.actionAppArgs : '',
		simpleResponse = ( 'undefined' !== typeof actionAppArgs.simple_response ) ? actionAppArgs.simple_response : 'Yes',
		actionData     = ( '' !== action && 'undefined' !== typeof otherActionApps[ application ]['actions'][ action ] ) ? otherActionApps[ application ]['actions'][ action ]['action_data'] : '',
		needsConnect   = ( 'undefined' !== typeof otherActionApps[ application ]['needs_connect'] ) ? true : false,
		eid = appData.eid,
		headersEnabled = 'no',
		paramsEnabled = 'no',
		headers,
		headerOptions,
		params,
		paramOptions;

	headers = ( 'undefined' !== typeof actionData['dynamic-headers-key'] ) ? actionData['dynamic-headers-key'] : false;
	headersEnabled = ( 'undefined' !== typeof actionData['add_headers'] ) ? actionData['add_headers'] : 'no';

	if ( headers && 'no' !== headersEnabled ) {
		#>
		<div class="form-group dynamic-inputs api-parameters w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Headers', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-headers-body data-headers data-dynamic-fields d-block" data-field-name="api_headers">
			<#
			_.each ( headers, function( header, key ) {
				var field_type,
					defaultValue = '',
					inputValue = '',
					helpText = '';

				headerOptions = ( 'undefined' !== typeof actionData['dynamic-headers-options'][ key ] && '' !== actionData['dynamic-headers-options'][ key ] ) ? JSON.parse( atob( actionData['dynamic-headers-options'][ key ] ) ) : false;

				if ( headerOptions && ! _.isEmpty( headerOptions ) ) {
					defaultValue = ( '' !== headerOptions.default_value ) ? headerOptions.default_value : '';

					if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs[ header ] ) {
						inputValue = actionAppArgs[ header ];
					} else {
						inputValue = defaultValue;
					}
					#>
					<div class="fm-dynamic-input-wrap fm-api-request-parameters d-flex">
						<div class="fm-dynamic-input-field w-50">
							<input class="fm-dynamic-inputs w-100 disabled" readonly name="dynamic-field-key[]" type="text" placeholder="key" value="{{{ headerOptions.field_label }}}" />
						</div>
						<div class="fm-dynamic-input-field w-50">
							<textarea rows="1" class="fm-textarea form-control fm-dynamic-inputs dynamic-field-input w-100" name="{{{ header }}}" placeholder="value">{{{ inputValue }}}</textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
					</div>
					<#
				}
			} );
			#>
			</div>
		</div>
		<#
	}

	params = ( 'undefined' !== typeof actionData['dynamic-params-key'] ) ? actionData['dynamic-params-key'] : false;
	paramsEnabled = ( 'undefined' !== typeof actionData['add_params'] ) ? actionData['add_params'] : 'no';

	if ( params && 'no' !== paramsEnabled ) {
		_.each ( params, function( param, key ) {
			var field_type,
				defaultValue = '',
				inputValue = '',
				required = '',
				requiredLabel = '',
				helpText = '';

			paramOptions = ( 'undefined' !== typeof actionData['dynamic-params-options'][ key ] ) ? JSON.parse( atob( actionData['dynamic-params-options'][ key ] ) ) : false;

			if ( paramOptions ) {
				field_type    = paramOptions.field_type;
				required      = ( 'no' !== paramOptions.field_required ) ? 'required' : '';
				defaultValue  = ( '' !== paramOptions.default_value ) ? paramOptions.default_value : '';
				requiredLabel = ( 'no' !== paramOptions.field_required ) ? '<span class="badge outline bg-danger">Required</span>' : '';
				helpText      = ( '' !== paramOptions.help_text ) ? '<div class="fm-application-instructions p-0 pt-2">' + paramOptions.help_text + '</div>' : '';

				if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs[ param ] ) {
					inputValue = actionAppArgs[ param ];
				} else {
					inputValue = defaultValue;
				}

				// window.paramOptions = paramOptions;
				// FlowMatticWorkflowEvents.trigger( 'updateDropdownSource', application, action, window.paramOptions, this );
				// paramOptions = window.paramOptions;
				#>
				<div class="form-group dynamic-inputs w-100">
					<div class="fm-input-wrap">
						<h4 class="fm-input-title"><strong>{{{ paramOptions.field_label }}}</strong> {{{ requiredLabel }}}</h4>
						<#
						switch ( field_type ) {
							case 'string':
								#>
								<div class="fm-dynamic-input-field">
									<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" {{{ required }}} name="{{{ param }}}" autocomplete="new-password">{{{ inputValue }}}</textarea>
									<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
								</div>
								<#
								break;
							case 'boolean':
								#>
								<div class="fm-dynamic-input-field">
									<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" {{{ required }}} name="{{{ param }}}" autocomplete="new-password">{{{ inputValue }}}</textarea>
									<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
								</div>
								<div class="fm-application-instructions p-0 pt-2">
									<p><?php esc_attr_e( 'Enter a boolean value.', 'flowmattic' ); ?> <code>yes</code> for TRUE and <code>no</code> for FALSE.</p>
								</div>
								<#
								break;
							case 'number':
								#>
								<div class="fm-dynamic-input-field">
									<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" {{{ required }}} name="{{{ param }}}" autocomplete="new-password">{{{ inputValue }}}</textarea>
									<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
								</div>
								<div class="fm-application-instructions p-0 pt-2">
									<p><?php esc_attr_e( 'Enter a number. Integer of Float value, eg. 1 or 1.44', 'flowmattic' ); ?></p>
								</div>
								<#
								break;
							case 'select':
								#>
								<div class="fm-select-field">
									<select name="{{{ param }}}" class="form-control fm-select input-border w-100" {{{ required }}} title="<?php esc_html_e( 'Choose option', 'flowmattic' ); ?>" data-live-search="true">
										<#
										// If select field options, set the dynamic fields.
										if ( 'undefined' !== typeof paramOptions['select-field-key[]'] ) {
											var selectOptionValues = paramOptions['select-field-value[]'];
											_.each ( paramOptions['select-field-key[]'], function( optionKey, optionIndex ) {
												var optionValue = selectOptionValues[ optionIndex ];
												#>
												<option <# if ( optionValue === inputValue ) { #>selected<# } #> value="{{{ optionValue }}}">{{{ optionKey }}}</option>
												<#
											} );
										}
										#>
									</select>
								</div>
								<#
								break
						}
						#>
						{{{ helpText }}}
					</div>
				</div>
				<#
			}
		} )
	}

	// Show connect selection, if required.
	// if ( needsConnect ) {
		let connectID = ( 'undefined' !== typeof actionAppArgs.connect_id ) ? actionAppArgs.connect_id : '';
		#>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="form-control w-100 d-block" title="Choose Connect" data-live-search="true">
					<option <# if ( connectID === 'default' || connectID === '' ) { #>selected<# } #> value="default"><?php echo esc_attr__( 'Default', 'flowmattic' ); ?></option>
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
				<div class="fm-application-instructions">
					<p><?php esc_attr_e( 'Choose the connect account you want to use other than default for this action step. Default connect set in custom app settings will be used instead.', 'flowmattic' ); ?></p>
				</div>		
			</div>
		</div>
		<#
	// }
	#>
	<div class="form-group dynamic-inputs w-100">
		<div class="fm-input-wrap">
			<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Simple Response', 'flowmattic' ); ?></strong></h4>
			<div class="form-check form-switch">
				<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="simple_response" id="fm-checkbox-simple-response-{{{ eid }}}" <# if ( 'undefined' === typeof simpleResponse || 'Yes' === simpleResponse ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
				<label for="fm-checkbox-simple-response-{{{ eid }}}"><?php esc_attr_e( 'Retrieve the data in simple format', 'flowmattic' ); ?></label>
			</div>
		</div>
	</div>
</script>
