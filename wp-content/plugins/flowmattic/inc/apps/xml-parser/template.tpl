<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 4.2.0
 */
?>
<script type="text/html" id="flowmattic-application-xml-parser-action-data-template">
	<div class="flowmattic-xml-parser-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'XML File URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" name="xml_file_url" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.xml_file_url ) { #>{{{ actionAppArgs.xml_file_url }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Enter the publicly accessible XML File url.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</script>
