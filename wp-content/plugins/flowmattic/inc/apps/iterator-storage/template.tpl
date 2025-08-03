<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-iterator-storage-action-data-template">
	<div data-storage="true" class="iterator-storage-action-data"></div>
</script>
<script type="text/html" id="flowmattic-iterator-storage-action-store_as_variable-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Create the String', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="iterator_storage_content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iterator_storage_content ) { #>{{{ actionAppArgs.iterator_storage_content }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Create the string to be stored. Each time the iterator runs, this data will be appended to the existing. If you want to use this as URL param later, make sure to add additional & at the end. Eg. param{iterator2.array_item_number}={step1.value}&', 'flowmattic' ); ?>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-iterator-storage-action-store_as_array-data-template">
	<div class="form-group dynamic-inputs api-parameters w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Data Parameters', 'flowmattic' ); ?></h4>
		<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="iterator_storage_parameters">
			<#
			if ( 'undefined' !== typeof iterator_storage_parameters && ! _.isEmpty( iterator_storage_parameters ) ) {
				_.each( iterator_storage_parameters, function( value, key ) {
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
			<p><?php echo esc_attr__( 'The stored data will be a combination of all the iterator items as arrays. The above data parameters will make a single array, and the final output will be JSON of combined array of the above array data.', 'flowmattic' ); ?>
		</div>
	</div>
</script>
