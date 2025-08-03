<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 2.2.0
 */
?>
<script type="text/html" id="flowmattic-application-haro-email-parser-data-template">
	<div class="fm-application-webhook-data">
		<div class="form-group webhook-url">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Email', 'flowmattic' ); ?></h4>
			<input type="text" class="form-control" name="haroMailhookURL" readonly data-type="mailhook" value="{{{ haroMailhookURL }}}" />
			<div class="fm-application-instructions">
				<p>{{{ triggerApps[ application ].instructions }}}</p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
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
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
