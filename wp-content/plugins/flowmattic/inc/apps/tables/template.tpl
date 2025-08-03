<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 5.0
 */
?>
<script type="text/html" id="flowmattic-application-tables-trigger-data-template">
	<div class="flowmattic-tables-trigger-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title">Database <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-form-control w-100">
				<select class="form-control w-100" name="database_id" required>
					<option value="">Select Database</option>
					<option value="local" data-subtext="ID: local" <# if ( database_id === 'local' ) { #>selected<# } #>>Local</option>
					<# _.each( window.fmdatabases, function( database, id ) { #>
						<option value="{{id}}" data-subtext="ID: {{id}}" <# if ( database_id === id ) { #>selected<# } #>>{{database}}</option>
					<# } ); #>
				</select>
			</div>
			<p class="fm-input-desc">Select the database to get the tables from.</p>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title">Table <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-form-control w-100 d-flex">
				<select class="form-control w-100" name="table_name" required data-selected="<# if ( table_name ) { #>{{table_name}}<# } #>">
					<option value="">Select Table</option>
				</select>
				<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-tables-refresh-button">
					<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</a>
			</div>
			<p class="fm-input-desc">Select the table to use for the trigger.</p>
		</div>
		<div data-trigger="table" class="tables-trigger-data"></div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
				<#
				if ( window.captureData ) {
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
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
<script type="text/html" id="tables-trigger-updated_cell-data-template">
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Column Name <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
		<div class="fm-form-control w-100 d-flex gap-1">
			<select class="form-control w-100 table-columns" name="column_name" required data-selected="<# if ( column_name ) { #>{{column_name}}<# } #>">
				<option value="">Select Column</option>
			</select>
			<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-column-refresh-button">
				<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</a>
		</div>
		<p class="fm-input-desc">Enter the column name to watch for changes.</p>
	</div>
</script>
<script type="text/html" id="flowmattic-application-tables-action-data-template">
	<div class="flowmattic-tables-data-form">
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">Database <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-form-control w-100">
				<#
				var database_id = '';
				if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.database_id ) {
					database_id = actionAppArgs.database_id;
				}
				#>
				<select class="form-control w-100" name="database_id" required>
					<option value="">Select Database</option>
					<option value="local" data-subtext="ID: local" <# if ( database_id === 'local' ) { #>selected<# } #>>Local</option>
					<option value="local" data-subtext="ID: local" <# if ( database_id === 'local' ) { #>selected<# } #>>Local</option>
					<# _.each( window.fmdatabases, function( database, id ) { #>
						<option value="{{id}}" data-subtext="ID: {{id}}" <# if ( database_id === id ) { #>selected<# } #>>{{database}}</option>
					<# } ); #>
				</select>
			</div>
			<p class="fm-input-desc">Select the database to perform the action.</p>
		</div>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">Table <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-form-control w-100 d-flex">
				<#
				var table_name = '';
				if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.table_name ) {
					table_name = actionAppArgs.table_name;
				}
				#>
				<div class="select-wrapper w-100">
					<select class="form-control w-100" name="table_name" required data-selected="<# if ( table_name ) { #>{{table_name}}<# } #>">
						<option value="">Select Table</option>
					</select>
				</div>
				<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-tables-refresh-button">
					<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</a>
			</div>
			<p class="fm-input-desc">Select the table to perform the action.</p>
		</div>
		<div data-action="table" class="tables-action-data"></div>
	</div>
</script>
<script type="text/html" id="tables-action-new_record-data-template">
	<#
	if ( 'undefined' !== typeof window.columnFields ) {
		_.each( window.columnFields, function( column_options, column_name ) { #>
		<#
		var fieldType = column_options.type;
		var column_title = column_options.title;
		var options = column_options.options;
		#>
			<div class="form-group css-yagMiHw w-100">
				<h4 class="fm-input-title">{{column_title}}</h4>
				<div class="fm-dynamic-input-field w-100">
					<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{column_name}}"><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ column_name ] ) { #>{{{actionAppArgs[ column_name ]}}}<# } #></textarea>
					<span class="dynamic-tags-button dynamic-field-button css-EMqtR0L css-oLvqoIe"><span class="border border-solid d-flex"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="18" width="18" name="formAdd"><path fill="currentColor" d="M13 19v-6h6v-2h-6V5h-2v6H5v2h6v6h2Z"></path></svg></span></span>
				</div>
				<#
				if ( 'dropdown' === fieldType ) {
					var optionsList = options.map( function( option ) {
						return option.name;
					} ).join( '</code>, <code>' );
					#>
					<div class="fm-application-instructions">
						<p>Accepted values: <code>{{{optionsList}}}</code></p>
					</div>
					<#
				}

				if ( 'checkboxField' === fieldType ) {
					#>
					<div class="fm-application-instructions">
						<p>Enter value as <code>checked</code> to check the checkbox. Leave empty to uncheck the checkbox.</p>
					</div>
					<#
				}
				#>
			</div>
			<#
		} );
	}
	#>
</script>
<script type="text/html" id="tables-action-update_record-data-template">
	<#
	const fields = [
		{ key: 'row_id', title: 'Row Id' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } ); 
	#>
	<#
	if ( 'undefined' !== typeof window.columnFields ) {
		_.each( window.columnFields, function( column_options, column_name ) { #>
		<#
		var fieldType = column_options.type;
		var column_title = column_options.title;
		var options = column_options.options;
		#>
			<div class="form-group css-yagMiHw w-100">
				<h4 class="fm-input-title">{{column_title}}</h4>
				<div class="fm-dynamic-input-field w-100">
					<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{column_name}}"><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ column_name ] ) { #>{{{actionAppArgs[ column_name ]}}}<# } #></textarea>
					<span class="dynamic-tags-button dynamic-field-button css-EMqtR0L css-oLvqoIe"><span class="border border-solid d-flex"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="18" width="18" name="formAdd"><path fill="currentColor" d="M13 19v-6h6v-2h-6V5h-2v6H5v2h6v6h2Z"></path></svg></span></span>
				</div>
				<#
				if ( 'dropdown' === fieldType ) {
					var optionsList = options.map( function( option ) {
						return option.name;
					} ).join( '</code>, <code>' );
					#>
					<div class="fm-application-instructions">
						<p>Accepted values: <code>{{{optionsList}}}</code></p>
					</div>
					<#
				}

				if ( 'checkboxField' === fieldType ) {
					#>
					<div class="fm-application-instructions">
						<p>Enter value as <code>checked</code> to check the checkbox. Leave empty to uncheck the checkbox.</p>
					</div>
					<#
				}
				#>
			</div>
			<#
		} );
	}
	#>
</script>
<script type="text/html" id="tables-action-find_record-data-template">
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Lookup Column <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
		<div class="fm-form-control w-100 d-flex gap-1">
			<#
			var column_name = '';
			if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.column_name ) {
				column_name = actionAppArgs.column_name;
			}
			#>
			<select class="form-control table-columns w-100" name="column_name" required data-selected="<# if ( column_name ) { #>{{column_name}}<# } #>">
				<option value="">Select Column</option>
			</select>
			<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-column-refresh-button">
				<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</a>
		</div>
		<p class="fm-input-desc">Select the column to search for the record.</p>
	</div>
	<#
	const fields = [
		{ key: 'lookup_value', title: 'Lookup Value' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } ); 
	#>
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Lookup Column <span class="badge outline text-warning css-ssW1jyn"><span class="text-warning">Optional</span></span></h4>
		<div class="fm-form-control w-100 d-flex gap-1">
			<#
			var column_name_optional = '';
			if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.column_name_optional ) {
				column_name_optional = actionAppArgs.column_name_optional;
			}
			#>
			<select class="form-control table-columns w-100" name="column_name_optional" data-selected="<# if ( column_name_optional ) { #>{{column_name_optional}}<# } #>">
				<option value="">Select Column</option>
			</select>
			<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-column-refresh-button">
				<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</a>
		</div>
		<p class="fm-input-desc">Select the optional column to search for the record.</p>
	</div>
	<#
	const optionalFields = [
		{ key: 'lookup_value_optional', title: 'Lookup Value' }
	];
	#>
	<# _.each( optionalFields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline text-warning css-ssW1jyn"><span class="text-warning">Optional</span></span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}"><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } ); 
	#>
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Lookup Method <span class="badge outline text-warning css-ssW1jyn"><span class="text-warning">Optional</span></span></h4>
		<div class="fm-form-control w-100">
			<#
			var lookup_method = '';
			if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.lookup_method ) {
				lookup_method = actionAppArgs.lookup_method;
			}
			#>
			<select class="form-control w-100" name="lookup_method" data-selected="<# if ( lookup_method ) { #>{{lookup_method}}<# } #>">
				<option value="">Select Lookup Method</option>
				<option value="exact" <# if ( lookup_method === 'exact' ) { #>selected<# } #>>Exact</option>
				<option value="contains" <# if ( lookup_method === 'contains' ) { #>selected<# } #>>Contains</option>
			</select>
		</div>
		<p class="fm-input-desc">Select the method to search for the record.</p>
	</div>
</script>
<script type="text/html" id="tables-action-find_multiple_records-data-template">
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Lookup Column <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
		<div class="fm-form-control w-100 d-flex gap-1">
			<#
			var column_name = '';
			if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.column_name ) {
				column_name = actionAppArgs.column_name;
			}
			#>
			<select class="form-control table-columns w-100" name="column_name" required data-selected="<# if ( column_name ) { #>{{column_name}}<# } #>">
				<option value="">Select Column</option>
			</select>
			<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-column-refresh-button">
				<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</a>
		</div>
		<p class="fm-input-desc">Enter the column name to search for the records.</p>
	</div>
	<#
	const fields = [
		{ key: 'lookup_value', title: 'Lookup Value' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } );
	#>
</script>
<script type="text/html" id="tables-action-delete_record-data-template">
	<#
	const fields = [
		{ key: 'row_id', title: 'Row Id' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } ); 
	#>
</script>
<script type="text/html" id="tables-action-update_cell-data-template">
	<div class="form-group css-yagMiHw w-100">
		<h4 class="fm-input-title">Lookup Column <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
		<div class="fm-form-control w-100 d-flex gap-1">
			<#
			var column_name = '';
			if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.column_name ) {
				column_name = actionAppArgs.column_name;
			}
			#>
			<select class="form-control table-columns w-100" name="column_name" required data-selected="<# if ( column_name ) { #>{{column_name}}<# } #>">
				<option value="">Select Column</option>
			</select>
			<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm ms-2 flowmattic-button flowmattic-column-refresh-button">
				<svg class="p-0" width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</a>
		</div>
		<p class="fm-input-desc">Enter the column name to search for the record.</p>
	</div>
	<#
	const fields = [
		{ key: 'row_id', title: 'Row Id' },
		{ key: 'column_value', title: 'Column Value' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	<# } ); 
	#>
</script>
<script type="text/html" id="tables-action-continue_workflow-data-template">
	<#
	const fields = [
		{ key: 'row_id', title: 'Row Id' }
	];
	#>
	<# _.each( fields, function( field ) { #>
		<div class="form-group css-yagMiHw w-100">
			<h4 class="fm-input-title">{{field.title}} <span class="badge outline bg-danger css-ssW1jyn">Required</span></h4>
			<div class="fm-dynamic-input-field w-100">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100" name="{{field.key}}" required><# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs[ field.key ] ) { #>{{{actionAppArgs[ field.key ]}}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<p class="fm-input-desc">Enter the row ID to continue the workflow. Enter row ID from previous step. Workflow will continue when the Approve/Reject button is clicked.</p>
		</div>
	<# } ); 
	#>
</script>
<script type="text/javascript">
<?php
$databases     = array();
$transient_dbs = get_transient( 'flowmattic_databases_list', false );

if ( false === $transient_dbs ) {
	$db_connections = wp_flowmattic()->database_connections_db->get_all();
	if ( ! empty( $db_connections ) ) {
		foreach ( $db_connections as $db_connection ) {
			$db_settings = maybe_unserialize( $db_connection->connection_settings );

			// Check if the database is connected.
			$is_database_connected = wp_flowmattic()->tables->is_database_connection_active( $db_connection->id );

			if ( $is_database_connected ) {
				$databases[ $db_connection->id ] = $db_connection->connection_name;
			}
		}

		if ( ! empty( $databases ) ) {
			// Store the databases in transient for 1 hour.
			set_transient( 'flowmattic_databases_list', $databases, DAY_IN_SECONDS );
		}
	}
} else {
	$databases = $transient_dbs;
}

echo 'var fmdatabases = ' . wp_json_encode( $databases ) . ';';
?>
</script>