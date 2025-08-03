<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-csv-parser-action-data-template">
	<div class="flowmattic-csv-parser-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'CSV File URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" name="csv_file_url" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.csv_file_url ) { #>{{{ actionAppArgs.csv_file_url }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Enter the publicly accessible CSV File url.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Header Row', 'flowmattic' ); ?></h4>
			<div class="d-flex">
				<select name="has_headers" class="widget-select form-control w-100" data-live-search="true" title="Choose value...">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.has_headers ) { #>selected<# } #> value="yes"><?php echo esc_html__( 'Yes', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.has_headers ) { #>selected<# } #> value="no"><?php echo esc_html__( 'No', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>Select Yes, if your CSV file has first row as header row.</p>
			</div>
		</div>
	</div>
</script>
