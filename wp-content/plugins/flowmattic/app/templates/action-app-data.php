<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */

?>
<script type="text/html" id="flowmattic-workflow-action-data-template">
<#
var time = Date.now();
#>
<div class="fm-workflow-step-header">
	<div class="fm-workflow-icon position-relative">
		<#
		var appIcon = ( 'undefined' !== typeof actionApps[ application ] ) ? actionApps[ application ].icon : otherActionApps[ application ].icon;
		#>
		<img src="{{{ appIcon }}}" style="width:48px;height:48px;" />
		<#
		if ( ! window.routerEditorOpen ) {
			#>
			<div class="drag-action-step position-absolute bg-light" style="width: 48px;height: 48px;">
				<svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 0 24 24" width="48px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5.83L15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9 12 5.83zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15 12 18.17z"/></svg>
			</div>
			<#
		}
		#>
	</div>
	<div class="fm-workflow-step-info">
		<span class="fm-workflow-hint-label"><?php esc_attr_e( 'Action: Do this...', 'flowmattic' ); ?></span>
		<h4 class="fm-workflow-step-application-title">
			<strong>{{{ stepIndex }}}. </strong>
			<span class="workflow-step-title">
				<span class="fm-application-title">{{{applicationName}}}:</span>
				<span class="fm-application-action">
					<#
					var appActionTitle = ( '' !== applicationAction ) ? applicationEvents[ applicationAction ] : '';
					#>
					{{{ appActionTitle }}}
				</span>
			</span>
		</h4>
	</div>
	<div class="fm-workflow-step-header-actions">
		<div class="fm-workflow-step-action fm-workflow-step-rename" data-toggle="tooltip" title="<?php echo esc_html__( 'Rename Action Step', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path>
			</svg>
		</div>
		<div class="fm-workflow-step-action fm-workflow-step-close" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete Action Step', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M16.13 22H7.87C7.37 22 6.95 21.63 6.88 21.14L5 8H19L17.12 21.14C17.05 21.63 16.63 22 16.13 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" d="M3.5 8H20.5"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" d="M10 12V18"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" d="M14 12V18"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M16 5H8L9.7 2.45C9.89 2.17 10.2 2 10.54 2H13.47C13.8 2 14.12 2.17 14.3 2.45L16 5Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" d="M3 5H21"></path>
			</svg>
		</div>
		<div class="fm-workflow-step-action fm-workflow-step-description" style="opacity: 1;" data-toggle="tooltip" title="<?php echo esc_html__( 'Click to display or add the description for this step.', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M4 22H20C21.1 22 22 21.1 22 20V4C22 2.9 21.1 2 20 2H4C2.9 2 2 2.9 2 4V20C2 21.1 2.9 22 4 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M12 13C12.76 13 13.33 12.48 13.42 11.78L13.99 6.56C14.09 5.7 13.42 5 12.57 5H11.43C10.58 5 9.91 5.7 10.01 6.57L10.58 11.79C10.67 12.48 11.24 13 12 13Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M12 19C12.83 19 13.5 18.33 13.5 17.5C13.5 16.67 12.83 16 12 16C11.17 16 10.5 16.67 10.5 17.5C10.5 18.33 11.17 19 12 19Z"></path>
			</svg>
		</div>
		<div class="fm-workflow-step-action fm-workflow-step-accordion-collapse" data-toggle="tooltip" title="<?php echo esc_html__( 'Toggle Action Step', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</div>
	</div>
</div>
<div class="fm-workflow-step-body w-100">
	<div class="fm-workflow-action-select w-100">
		<div class="form-group fm-workflow-applications">
			<div class="fm-workflow-action-heading">
				<h4><?php esc_attr_e( 'Choose Application', 'flowmattic' ); ?></h4>
			</div>
			<div class="flowmattic-dropdown">
				<select name="workflow-application" class="workflow-application w-100" required title="Choose Application" data-live-search="true">
					<optgroup label="FlowMattic Apps" data-max-options="1">
						<#
						const sortedApps = {};
						Object.keys(actionApps).sort().forEach(key => {
							sortedApps[key] = actionApps[key];
						});
						_.each( sortedApps, function( settings, appSlug ) {
							settings.name = settings.name.replace( 'by FlowMattic', '' );
							#>
							<option
								<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
								<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' /> {{{ settings.name }}}" <# } #>
								value="{{{appSlug}}}">
								{{{ settings.name }}}
							</option>
							<#
						} );
						#>
					</optgroup>
					<optgroup label="Other Apps" data-max-options="1">
						<#
						_.each( otherActionApps, function( settings, appSlug ) {
							#>
							<option
								<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
								<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' /> {{{ settings.name }}}" <# } #>
								value="{{{appSlug}}}">
								{{{ settings.name }}}
							</option>
							<#
						} );
						#>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="form-group fm-workflow-actions">
			<div class="fm-workflow-application-events">
				<div class="fm-workflow-event-heading">
					<h4><?php esc_attr_e( 'Action Event', 'flowmattic' ); ?></h4>
				</div>
				<select name="workflow-application-events" class="workflow-application-events w-100 required" required title="Choose an action" data-live-search="true">
					<#
					_.each( applicationEvents, function( action, value ) {
						if ( 'object' === typeof action[0] ) {
							#>
							<optgroup label="{{{ FlowMatticWorkflow.UCWords( value ) }}}" data-max-options="1">
								<#
									_.each( action, function( triggers ) {
										_.each( triggers, function( triggerKey, triggerValue ) {
											#>
											<option <# if ( applicationAction === triggerValue ) { #>selected<# } #> value="{{{triggerValue}}}" data-subtext="{{{triggerKey.description}}}">{{{triggerKey.title}}}</option>
											<#
										} );
									} );
								#>
							</optgroup>
							<#
						} else {
							var description = ( 'undefined' !== typeof action.description ) ? ' data-subtext="' + action.description + '"' : '';
							#>
							<option <# if ( applicationAction === value ) { #>selected<# } #> value="{{{ value }}}"{{{ description }}}>{{{ action.title }}}</option>
							<#
						}
					} )
					#>
				</select>
			</div>
		</div>
	</div>
	<div class="fm-workflow-action-data">
	</div>
	<#
	if ( 'filter' !== application && 'router' !== application && 'iterator_storage' !== application && 'iterator_end' !== application ) {
		#>
		<div class="form-group dynamic-inputs action-conditional w-100" style="background-color: rgba(13,202,240, 10%) !important;border-color: rgba(13,202,240, 10%) !important;">
			<div class="fm-input-wrap mb-3">
				<h4 class="fm-input-title text-primary"><?php esc_attr_e( 'Enable Conditional Execution', 'flowmattic' ); ?></h4>
				<input id="fm-checkbox-action-conditional-{{{ time }}}" class="fm-checkbox form-control fm-conditional-execution" name="conditional_execution" type="checkbox" value="Yes" <# if ( 'undefined' !== typeof conditional_execution && 'Yes' === conditional_execution ) { #>checked<# } #> />
				<label for="fm-checkbox-action-conditional-{{{ time }}}"><?php esc_attr_e( 'Execute This Step Only if Conditions Meet', 'flowmattic' ); ?></label>
			</div>
			<div class="action-conditions-wrap fm-filter-conditions-body hidden">
			</div>
		</div>
		<div class="form-group ignore-errors w-100" style="background-color: rgba(13,202,240, 10%) !important;border-color: rgba(13,202,240, 10%) !important;">
			<div class="fm-input-wrap mb-3">
				<h4 class="fm-input-title text-primary"><?php esc_attr_e( 'Ignore Errors', 'flowmattic' ); ?></h4>
				<input id="fm-checkbox-ignore-errors-{{{ time }}}" class="fm-checkbox form-control fm-ignore-errors" name="ignore_errors" type="checkbox" value="No" <# if ( 'undefined' !== typeof ignore_errors && 'Yes' === ignore_errors ) { #>checked<# } #> />
				<label for="fm-checkbox-ignore-errors-{{{ time }}}"><?php esc_attr_e( 'Check to ignore the error responses. This will set the error status to success and prevent the error notification email as well.', 'flowmattic' ); ?></label>
			</div>
		</div>
		<#
	}
	#>
	<div class="fm-form-capture-button">
		<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-save-test-action-button">
			<?php echo esc_attr__( 'Save & Test Action', 'flowmattic' ); ?>
		</a>
	</div>
	<div class="fm-action-capture-data fm-response-capture-data"></div>
</div>
</script>
<script type="text/html" id="flowmattic-react-step-conditions-template">
	<#
	var time = Date.now();
	#>
	<div class="css-daexgLj">
		<div class="form-group css-yagMiHw dynamic-inputs action-conditional w-100">
			<div class="fm-input-wrap gap-3 mb-2">
				<h4 class="fm-input-title css-rPBDSnn css-Tirhb7Y"><?php esc_attr_e( 'Enable Conditional Execution', 'flowmattic' ); ?></h4>
				<div class="css-UX4inQD">
					<input id="fm-checkbox-action-conditional-{{{ time }}}" class="fm-checkbox form-control fm-conditional-execution" name="conditional_execution" type="checkbox" value="Yes" <# if ( 'undefined' !== typeof conditional_execution && 'Yes' === conditional_execution ) { #>checked<# } #> style="width: 20px !important;height: 20px !important;min-height: auto !important;padding: 2px !important;" />
					<label for="fm-checkbox-action-conditional-{{{ time }}}"><?php esc_attr_e( 'Execute This Step Only if Conditions Meet', 'flowmattic' ); ?></label>
				</div>
			</div>
			<div class="action-conditions-wrap fm-filter-conditions-body hidden">
			</div>
		</div>
		<div class="form-group css-yagMiHw ignore-errors w-100">
			<div class="fm-input-wrap gap-3 mb-2">
				<h4 class="fm-input-title css-rPBDSnn css-Tirhb7Y"><?php esc_attr_e( 'Ignore Errors', 'flowmattic' ); ?></h4>
				<input id="fm-checkbox-ignore-errors-{{{ time }}}" class="fm-checkbox form-control fm-ignore-errors" name="ignore_errors" type="checkbox" value="No" <# if ( 'undefined' !== typeof ignore_errors && 'Yes' === ignore_errors ) { #>checked<# } #> style="width: 20px !important;height: 20px !important;min-height: auto !important;padding: 2px !important;" />
				<label for="fm-checkbox-ignore-errors-{{{ time }}}"><?php esc_attr_e( 'Check to ignore the error responses. This will set the error status to success and prevent the error notification email as well.', 'flowmattic' ); ?></label>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-step-description-template">
<div class="modal fade" id="stepDescription" tabindex="-1" aria-labelledby="stepDescriptionLabel" aria-hidden="true" data-backdrop="static" style="z-index: 999999;">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="stepDescriptionLabel"><?php echo esc_html__( 'Action Step Description', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label for="description-text" class="col-form-label pt-0"><?php echo esc_html__( 'Description:', 'flowmattic' ); ?></label>
					<span class="form-control span-textarea" role="textbox" contenteditable id="fm-step-description-text">{{{ stepDescriptionText }}}</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-primary btn-save-step-description"><?php echo esc_html__( 'Save', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>
</script>
<style type="text/css">
.drag-action-step {
	opacity: 0;
	transition: opacity 0.1s ease-in-out;
	cursor: grab;
}
.flowmattic-action-step.ui-sortable-helper .drag-action-step {
	cursor: grabbing;
}
.fm-workflow-icon:hover .drag-action-step {
	opacity: 1;
}
.fm-workflow-icon:hover img {
	opacity: 0;
}
.fm-workflow-trigger .fm-workflow-icon:hover img,
.router-steps .fm-workflow-icon:hover img {
	opacity: 1 !important;
}
.fm-workflow-icon img {
	transition: opacity 0.1s ease-in-out;
}
.flowmattic-action-step.ui-sortable-helper:after,
.flowmattic-action-step.ui-sortable-helper .fm-workflow-add-step,
.flowmattic-action-step.ui-sortable-helper .fm-workflow-step-header-actions {
	opacity: 0 !important;
}
.flowmattic-action-step-placeholder.ui-sortable-placeholder:after {
	content: "";
	position: absolute;
	top: 100%;
	width: 1px;
	height: 30px;
	background-color: #cccccc;
	left: calc( 50% - 0.5px );
}
.fm-workflow-step-header {
	transition: all 0.1s ease-in-out;
}
.flowmattic-action-step.ui-sortable-helper .fm-workflow-step-header {
	width: calc( 100% - 15px );
	height: 96px;
	margin: 0 auto;
}
</style>