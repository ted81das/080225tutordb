<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-webpage-parser-action-data-template">
	<div class="flowmattic-webpage-parser-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Enter URL to Parse', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" name="webpage_url" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.webpage_url ) { #>{{{ actionAppArgs.webpage_url }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Enter the publicly accessible url to parse.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</script>
