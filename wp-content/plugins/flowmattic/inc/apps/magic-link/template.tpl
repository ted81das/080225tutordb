<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-magic-link-trigger-data-template">
	<div class="flowmattic-magic-link-trigger-data">
        <div class="form-group w-100" style="position: relative;">
            <h4 class="fm-input-title"><?php esc_attr_e( 'Magic Link Shortcode', 'flowmattic' ); ?></h4>
            <input type="hidden" class="w-100" readonly />
            <div class="fm-code-highlight position-relative">
                <div id="fm-shortcode-text" class="fm-shortcode-text w-100 border rounded-2" style="padding: 10px; background-color: #f8f9fa;font-family: monospace;white-space: pre-line;">[fm_magic_link id="{{window.workflowId}}" link_text="Click Here"]</div>
            </div>
            <div class="fm-application-instructions">
                <p><?php esc_attr_e( 'Copy the above Shortcode and use it on your frontend.', 'flowmattic' ); ?></p>
				<a href="https://support.flowmattic.com/kb/article/70/using-magic-link-module-in-flowmattic" target="_blank">
					<?php esc_attr_e( 'Learn more about Magic Link', 'flowmattic' ); ?>
				</a>
            </div>
        </div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Magic Link Shortcode Parameters', 'flowmattic' ); ?></h4>
			<table class="table table-bordered fm-shortcode-parameters-table">
				<thead>
					<tr>
						<th><?php esc_attr_e( 'Parameter', 'flowmattic' ); ?></th>
						<th><?php esc_attr_e( 'Description', 'flowmattic' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>id</code></td>
						<td><?php esc_attr_e( 'The ID of the workflow to be triggered.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>link_text</code></td>
						<td><?php esc_attr_e( 'The text to be displayed for the magic link when the user is logged in.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>visitor_link_text</code></td>
						<td><?php esc_attr_e( 'The text to be displayed for the magic link when the visitor is not logged in.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>user_redirect_url</code></td>
						<td><?php esc_attr_e( 'The URL to which the user will be redirected after clicking the magic link.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>visitor_redirect_url</code></td>
						<td><?php esc_attr_e( 'The URL to which the visitor will be redirected after clicking the magic link.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>css_class
						<td><?php esc_attr_e( 'The CSS class to be applied to the magic link for styling purposes.', 'flowmattic' ); ?></td>
					</tr>
					<tr>
						<td><code>css_id</code></td>
						<td><?php esc_attr_e( 'The CSS ID to be applied to the magic link for styling purposes.', 'flowmattic' ); ?></td>
					</tr>
				</tbody>
			</table>
			<div class="fm-application-instructions">
				<p><?php esc_attr_e( 'You can add the above parameters to the shortcode to customize the magic link.', 'flowmattic' ); ?></p>
			</div>			
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-api-poll-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Save & Capture response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>