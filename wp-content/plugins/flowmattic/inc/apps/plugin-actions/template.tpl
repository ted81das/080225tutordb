<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-plugin-actions-data-template">
	<div class="flowmattic-plugin-actions-form-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Plugin or WP Action Hook Name', 'flowmattic' ); ?></h4>
			<input type="text" class="form-control plugin-action-hook w-100" name="pluginAction" value="{{{pluginAction}}}" />
			<div class="fm-application-instructions">
				<p>{{{ triggerApps[ application ].instructions }}}</p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-plugin-action-hook-capture-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture Action Data', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Capture Action Data', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-action-hook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>