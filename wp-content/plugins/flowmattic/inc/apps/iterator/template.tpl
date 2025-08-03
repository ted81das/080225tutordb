<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.1
 */
?>
<script type="text/html" id="flowmattic-application-iterator-data-template">
	<div class="flowmattic-iterator-form-data">
		<div class="form-group w-100 fm-iterator-trigger-iterator-unit">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Iteration Array', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs fm-textarea form-control dynamic-field-input" name="iteratorArray" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iteratorArray ) { #>{{{ actionAppArgs.iteratorArray }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<div class="fm-input-wrap">
				<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Simple Response', 'flowmattic' ); ?></strong></h4>
				<div class="form-check form-switch">
					<#
					var simpleResponse = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iterator_simple_response ) ? actionAppArgs.iterator_simple_response : 'Yes';
					var iterator_simple_response = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iterator_simple_response ) ? actionAppArgs.iterator_simple_response : 'on';

					if ( 'on' === iterator_simple_response ) {
						simpleResponse = 'Yes';
					} else {
						simpleResponse = 'No';
					}
					#>
					<input class="form-check-input form-control me-2" type="checkbox" name="iterator_simple_response" id="fm-iterator-simple-response" <# if ( 'undefined' === typeof simpleResponse || 'Yes' === simpleResponse ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
					<label for="fm-iterator-simple-response"><?php esc_attr_e( 'Retrieve the data in simple format', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
		<div class="form-group w-100 fm-iterator-trigger-iterator-unit">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Iteration Processing Method', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-select-field">
				<select class="w-100 fm-dynamic-inputs fm-select form-control dynamic-field-input" name="iteratorMethod" required>
					<option value="direct" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iteratorMethod && 'direct' === actionAppArgs.iteratorMethod ) { #>selected<# } #>
						data-subtext="Processes the array in a single iteration ( Faster Processing )"
						>Direct</option>
					<option value="batch" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.iteratorMethod && 'batch' === actionAppArgs.iteratorMethod ) { #>selected<# } #>
						data-subtext="Processes the array in multiple iterations ( Slow Processing )"
						>Batch</option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<ul style="list-style: square;">
					<li><?php echo esc_attr__( 'Select the method to process the iteration array. Direct method will process the array in a single iteration. Batch method will process the array in multiple iterations.', 'flowmattic' ); ?></li>
					<li><?php echo esc_attr__( 'The batch method will require the batch size to be specified.', 'flowmattic' ); ?></li>
					<li><?php echo esc_attr__( 'The batch method is useful when the iteration array is large and the processing of the array in a single iteration may cause the server to timeout.', 'flowmattic' ); ?></li>
				</ul>
			</div>
		</div>
		<div class="form-group w-100 fm-iterator-trigger-iterator-unit">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Batch Size', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="w-100 fm-dynamic-inputs fm-text form-control dynamic-field-input" type="text" name="batchSize" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.batchSize ) { #>{{{ actionAppArgs.batchSize }}}<# } #>">
			</div>
			<div class="fm-application-instructions">
				<ul style="list-style: square;">
					<li><?php echo esc_attr__( 'Specify the number of items to process in each batch.', 'flowmattic' ); ?></li>
					<li><?php echo esc_attr__( 'This field is required when the batch method is selected.', 'flowmattic' ); ?></li>
					<li><?php echo esc_attr__( 'Batch method processes the array in background, so is it a little slow compared to the direct method.', 'flowmattic' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</script>
