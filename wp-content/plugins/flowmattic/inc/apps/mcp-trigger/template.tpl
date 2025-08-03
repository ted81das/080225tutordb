<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-mcp-trigger-data-template">
	<div class="w-100 p-2">
		<div class="mb-4">
			<h6 class="text-primary mb-3 fw-bold">
				MCP Tool Trigger
			</h6>
		</div>

		<!-- Instructions Alert -->
		<div class="alert alert-info border-start border-2 border-info mb-4">
			<div class="d-flex">
				<div>
					<p class="mb-0">
						This workflow will be triggered when MCP tool for this workflow is executed. 
						To create a MCP tool to trigger this workflow, follow these instructions:
					</p>
				</div>
			</div>
		</div>

		<!-- Step-by-step Instructions -->
		<div class="card bg-white border-0">
			<div class="card-body">
				<h6 class="text-secondary mb-3">
					Setup Instructions
				</h6>
				
				<div class="row">
					<div class="col-12">
						<ul class="list-unstyled">
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">1</span>
								<span>Save this workflow.</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">2</span>
								<span>
									Please visit the 
									<a class="text-decoration-none fw-bold" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=flowmattic-mcp-server&openModal=createTool&executionMethod=workflow_id' ) ); ?>"><?php esc_html_e( 'FlowMattic MCP Server', 'flowmattic' ); ?></a>
									page.
								</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">3</span>
								<span>Click on <kbd>Add New Tool</kbd> button.</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">4</span>
								<span>Fill in the required fields, and select <mark>Workflow</mark> as execution method.</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">5</span>
								<span>Select this workflow from the dropdown for <strong>"Workflow to execute"</strong> field.</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">6</span>
								<span>In the <strong>"Input Parameters"</strong> section, add parameters you want to pass to this workflow.</span>
							</li>
							
							<li class="mb-3 d-flex">
								<span class="badge bg-primary me-3 mt-1 flex-shrink-0" style="height:20px">7</span>
								<span>Save the MCP tool.</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- Success Message -->
		<div class="alert alert-success border-start border-2 border-success mt-4">
			<div class="d-flex">
				<div>
					<strong>All Set!</strong>
					<p class="mb-0 mt-1">
						Now, whenever this MCP tool is executed, this workflow will be triggered with the 
						parameters you provided. To capture response, you just need to trigger this workflow 
						from the MCP tool.
					</p>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-mcp-action-data-template">
	<div class="form-group dynamic-inputs response-parameters w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Response Parameters', 'flowmattic' ); ?></h4>
		<div class="alert alert-info border-start border-1 border-info mb-4">
			<div class="d-flex">
				<div>
					<p class="mb-0">
						<?php esc_html_e( 'To provide data back to the MCP tool, you can define response parameters here. These parameters will be returned as JSON response when this workflow is executed from MCP tool.', 'flowmattic' ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="mcp_response_parameters">
			<#
			if ( 'undefined' !== typeof mcp_response_parameters && ! _.isEmpty( mcp_response_parameters ) ) {
				_.each( mcp_response_parameters, function( value, key ) {
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
		<div class="fm-api-request-parameters-footer mt-3 border-top p-3 border-secondary bg-light">
			<p class="text-muted mb-0">
				<?php esc_html_e( 'Note: This action step must be the last step in your workflow. It will return the response parameters defined here to the MCP tool that triggered this workflow.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>