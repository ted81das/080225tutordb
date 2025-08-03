<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.1
 */
?>
<script type="text/html" id="flowmattic-application-php_array-data-template">
	<div class="flowmattic-php_array-action-data">
		<div class="flowmattic-php-array-function-fields">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-get_array_count-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Data', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_array" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_array ) { #>{{{ actionAppArgs.json_array }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Select the field having JSON data to process the array functions.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-get_value_by_index-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Data', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_array" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_array ) { #>{{{ actionAppArgs.json_array }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Select the field having JSON data to process the array functions.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Array Index(es)', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="array_index" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.array_index ) { #>{{{ actionAppArgs.array_index }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the array index number. Array index starts from 0. You can also get multiple values by entering indexes as comma separated list. eg. 1,3. It will retrieve the values for array index 1 and 3.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-array_search-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Data', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_array" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_array ) { #>{{{ actionAppArgs.json_array }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Select the field having JSON data to process the array functions.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Array Search Term', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="array_search_term" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.array_search_term ) { #>{{{ actionAppArgs.array_search_term }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the search term to search the array for. If the value found, it will return the array index, or error if not found.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-convert_list_to_array-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'List of Values', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="list_of_values" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.list_of_values ) { #>{{{ actionAppArgs.list_of_values }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the list of values separated by the given separator. It will convert the list of values to an array.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Separator', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="separator" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.separator ) { #>{{{ actionAppArgs.separator }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the separator to split the list of values. Default is comma (,).', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-convert_array_to_list-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Array', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_array" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_array ) { #>{{{ actionAppArgs.json_array }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the array to convert to list of values. It will convert the array to list of values separated by the given separator.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Separator', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="separator" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.separator ) { #>{{{ actionAppArgs.separator }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the separator to join the array values. Default is comma (,).', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-insert_value_at_index-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Array', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_array" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_array ) { #>{{{ actionAppArgs.json_array }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the array to convert to JSON. It will convert the array to JSON format.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Array Index', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" required name="array_index" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.array_index ) { #>{{{ actionAppArgs.array_index }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the array index to insert the value at. Array index starts from 0.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Value to Insert', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="value_to_insert" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.value_to_insert ) { #>{{{ actionAppArgs.value_to_insert }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Enter the value to insert at the given array index.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-new_line_to_array-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'New Line Separated List', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="new_line_separated_list" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.new_line_separated_list ) { #>{{{ actionAppArgs.new_line_separated_list }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Provide the list of values separated by new line. It will convert the list of values to an array.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-extract_json-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Data to Extract', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_data" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_data ) { #>{{{ actionAppArgs.json_data }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Provide the JSON data to be extracted as individual items in response. Nested items will not be affected and presented as JSON of the individual item.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-itemize_array-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Data', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="fm-textarea form-control dynamic-field-input w-100" name="json_data" rows="1" placeholder="value"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.json_data ) { #>{{{ actionAppArgs.json_data }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Provide the JSON data to be itemized. It will convert the JSON data to individual items.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-php_array-create_itemized_array-data-template">
	<div class="form-group dynamic-inputs itemize-parameters w-100">
	<h4 class="fm-input-title"><?php esc_attr_e( 'Line Items', 'flowmattic' ); ?></h4>
	<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="line_items">
		<#
		if ( 'undefined' !== typeof line_items && ! _.isEmpty( line_items ) ) {
			_.each( line_items, function( value, key ) {
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
	<div class="fm-application-instructions">
		<p>
			<?php echo esc_attr__( 'Enter the line items to be converted to array. Line items will be created with the combination of key and value pairs from each line item.', 'flowmattic' ); ?>
		</p>
	</div>
</div>
</script>