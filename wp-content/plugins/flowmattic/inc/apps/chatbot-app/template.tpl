<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 4.0
 */
?>
<script type="text/html" id="flowmattic-application-fm-chatbot-data-template">
	<div class="flowmattic-fm-chatbot-form-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Intructions to use this trigger:', 'flowmattic' ); ?></h4>
			<div class="fm-application-instructions">
				<p>
					<ul style="list-style: square;">
						<li>Save this workflow first</li>
						<li>Go to your <a href="admin.php?page=flowmattic-chatbot" target="_blank">FlowMattic Chatbot</a></li>
						<li>Go to <strong>Actions</strong></li>
						<li>Choose the <strong>Action</strong> as <strong>Trigger Workflow</strong></li>
						<li>Choose this workflow from the dropdown in <strong>Workflow</strong> field</li>
						<li>Save settings</li>
						<li>Click on <strong>Capture Response</strong> and ask question in your Chatbot</li>
					</ul>
				</p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Capture response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>