<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */

?>
<script type="text/html" id="flowmattic-workflow-app-template">
	<div class="fm-workflow-steps">
		<div class="fm-workflow-trigger fm-workflow-step" step-id="{{{ stepID }}}">
			<div class="fm-workflow-step-header">
				<div class="fm-workflow-icon">
					<svg width="26px" height="26px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M16.24 7L15.1201 7"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M14.3629 3.5L13.6558 4.20711"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M10.1201 2V3"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M6.62012 4.20711L5.91301 3.5"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M5.12012 7L4 7"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" fill="none" d="M11.3825 14.8V10.47C11.3825 9.77 11.9525 9.2 12.6525 9.2C13.3525 9.2 13.9225 9.77 13.9225 10.47V14.8V12.07C13.9225 11.37 14.4925 10.8 15.1925 10.8C15.8925 10.8 16.4625 11.37 16.4625 12.07V14.8V13.62C16.4625 12.95 17.0325 12.4 17.7325 12.4C18.4325 12.4 19.0025 12.95 19.0025 13.62V17.6C19.0025 20.04 16.9725 22 14.4425 22H13.3625C12.2825 22 11.3325 21.63 10.5725 21.08L5.3825 16.13C4.8725 15.64 4.8725 14.8 5.3825 14.31C5.8925 13.82 6.6525 13.93 7.1525 14.42L8.8525 16.01V7.22C8.8525 6.55 9.4225 6 10.1225 6C10.8225 6 11.3925 6.55 11.3925 7.22L11.3825 14.8Z"></path>
					</svg>
				</div>
				<div class="fm-workflow-step-info">
					<span class="fm-workflow-hint-label"><?php esc_attr_e( 'Trigger: When this happens...', 'flowmattic' ); ?></span>
					<h4 class="fm-workflow-step-application-title"><strong>1.</strong> <?php esc_attr_e( 'Choose a trigger', 'flowmattic' ); ?></h4>
				</div>
			</div>
			<div class="fm-workflow-step-body w-100">
				<div class="fm-workflow-trigger-select">
					<div class="fm-workflow-actions-popup">
						<div class="fm-workflow-applications">
							<div class="form-group fm-workflow-action-heading">
								<h4><?php esc_attr_e( 'Choose Application', 'flowmattic' ); ?></h4>
								<div class="flowmattic-dropdown">
									<select name="workflow-application" class="workflow-trigger w-100" title="Choose Application" data-live-search="true">
										<optgroup label="FlowMattic Apps" data-max-options="1">
											<#
											_.each( triggerApps, function( settings, appSlug ) {
												settings.name = settings.name.replace( 'by FlowMattic', '' );
												#>
												<option
													<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
													<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}"<# } #>
													value="{{{appSlug}}}">{{{ settings.name }}}
												</option>
												<#
											} );
											#>
											</optgroup>
											<optgroup label="Other Apps" data-max-options="1">
												<#
												_.each( otherTriggerApps, function( settings, appSlug ) {
													settings.name = settings.name.trim();
													#>
													<option
														<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
														<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}"<# } #>
														value="{{{appSlug}}}">{{{ settings.name }}}
													</option>
													<#
												} );
											#>
										</optgroup>
									</select>
								</div>
							</div>
						</div>
						<div class="fm-workflow-core-actions">
							<?php
							$flowmattic_apps  = wp_flowmattic()->apps;
							$all_applications = $flowmattic_apps->get_all_applications();
							?>
							<div class="fm-workflow-core-highlight-app">
								<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-trigger-app" data-trigger="webhook">
									<div class="highlight-app-icon pe-3">
										<img src="<?php echo ( isset( $all_applications['webhook'] ) ? $all_applications['webhook']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
									</div>
									<div class="highlight-app-title text-start">
										<strong><?php echo esc_html__( 'Webhook', 'flowmattic' ); ?></strong>
										<p class="description"><?php echo esc_html__( 'Capture data from another app using webhook.', 'flowmattic' ); ?></p>
									</div>
								</button>
							</div>
							<div class="fm-workflow-core-highlight-app">
								<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-trigger-app" data-trigger="schedule">
									<div class="highlight-app-icon pe-3">
										<img src="<?php echo ( isset( $all_applications['schedule'] ) ? $all_applications['schedule']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
									</div>
									<div class="highlight-app-title text-start">
										<strong><?php echo esc_html__( 'Schedule', 'flowmattic' ); ?></strong>
										<p class="description"><?php echo esc_html__( 'Trigger the workflow every hour, day, or custom interval.', 'flowmattic' ); ?></p>
									</div>
								</button>
							</div>
							<div class="fm-workflow-core-highlight-app">
								<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-trigger-app" data-trigger="custom">
									<div class="highlight-app-icon pe-3">
										<img src="<?php echo ( isset( $all_applications['custom'] ) ? $all_applications['custom']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
									</div>
									<div class="highlight-app-title text-start">
										<strong><?php echo esc_html__( 'Custom Action', 'flowmattic' ); ?></strong>
										<p class="description"><?php echo esc_html__( 'Use custom code to trigger the workflow.', 'flowmattic' ); ?></p>
									</div>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="fm-workflow-add-step">
				<a href="javascript:void(0)" class="fm-add-step fm-add-trigger" data-toggle="tooltip" title="<?php echo esc_html__( 'Add New Action', 'flowmattic' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
				</a>
			</div>
		</div>
	</div>
	<div class="fm-workflow-footer gap-3">
		<#
		var isChecked = '';
		if ( 'undefined' !== typeof window.workflowStatus && 'off' !== window.workflowStatus ) {
			isChecked = 'checked';
		}
		#>
		<input type="checkbox" class="workflow-onoff-switch" {{{ isChecked }}} data-toggle="toggle" data-on="Live" data-off="Draft" data-onstyle="success" data-offstyle="secondary">
		<a href="javascript:void(0);" class="btn-do-test-execution btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="tooltip" title="Do a Test Run">
			<span class="d-inline-block fs-3 d-flex align-items-center justify-content-center">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" height="20" width="20" fill="currentColor"><path d="M380-300v-360l280 180-280 180ZM480-40q-108 0-202.5-49.5T120-228v108H40v-240h240v80h-98q51 75 129.5 117.5T480-120q115 0 208.5-66T820-361l78 18q-45 136-160 219.5T480-40ZM42-520q7-67 32-128.5T143-762l57 57q-32 41-52 87.5T123-520H42Zm214-241-57-57q53-44 114-69.5T440-918v80q-51 5-97 25t-87 52Zm449 0q-41-32-87.5-52T520-838v-80q67 6 128.5 31T762-818l-57 57Zm133 241q-5-51-25-97.5T761-705l57-57q44 52 69 113.5T918-520h-80Z"/></svg>
			</span>
		</a>
		<a href="javascript:void(0);" class="btn btn-primary flowmattic-workflow-save-button"><?php echo esc_attr__( 'Save Workflow', 'flowmattic' ); ?></a>
	</div>
	<div class="flowmattic-route-editor">
		<div class="router-heading d-flex justify-content-between border-bottom bg-light">
			<h3 class="fm-router-heading m-0 p-3"></h3>
			<a href="javascript:void(0);" class="router-editor-close btn text-center align-items-center d-inline-flex px-4" data-toggle="tooltip" title="Close router editor. Make sure to save your action steps before closing this window, or your changes will lost.">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
					<path d="M22.7071 1.29289C23.0976 1.68342 23.0976 2.31658 22.7071 2.70711L2.70711 22.7071C2.31658 23.0976 1.68342 23.0976 1.29289 22.7071C0.902369 22.3166 0.902369 21.6834 1.29289 21.2929L21.2929 1.29289C21.6834 0.902369 22.3166 0.902369 22.7071 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
					<path d="M1.29289 1.29289C1.68342 0.902369 2.31658 0.902369 2.70711 1.29289L22.7071 21.2929C23.0976 21.6834 23.0976 22.3166 22.7071 22.7071C22.3166 23.0976 21.6834 23.0976 21.2929 22.7071L1.29289 2.70711C0.902369 2.31658 0.902369 1.68342 1.29289 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
				</svg>
			</a>
		</div>
		<div class="router-steps">
			<div class="fm-workflow-steps"></div>
		</div>
		<div class="router-footer m-0 p-3 border-top bg-light text-end">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-router-save-button"><?php echo esc_attr__( 'Save Route', 'flowmattic' ); ?></a>
		</div>
	</div>
	<div class="fm-step-description-wrapper"></div>
</script>
<script type="text/html" id="flowmattic-select-dropdown-template">
<optgroup label="{{{ options.index }}}. {{{ FlowMatticWorkflow.UCWords( options.appName ) }}}" data-subtext="{{{ options.title }}}" data-max-options="1">
<#
var count = 1;
if ( _.isEmpty( options.data ) ) {
	if ( 1 === options.index ) {
		#>
		<option value="" disabled readonly>No data available. Please capture data in Trigger step.</option>
		<#
	} else {
		#>
		<option value="" disabled readonly>No data available. Please Save and Test Action.</option>
		<#
	}
} else {
	_.each( options.data, function( value, key ) {
		var optionValue = '{' + options.name + options.index + '.' + key + '}';
		key = FlowMatticWorkflow.UCWords( key );

		// Trim the value to max. 110 characters, and append '...' if the value is longer than 110 characters.
		if ( value && ( key.length + value.length ) > 110 ) {
			var lengthLeft = 100 - key.length;
			value = value.substring( 0, lengthLeft ) + '...';
		}
		value = FlowMatticWorkflow.escapeHTML( value );
		#>
		<option value="{{{ optionValue }}}">{{{ count }}}. {{{ key }}} <span class="value-text">: {{{ value }}}</span></option>
		<#
		count++;
	} );
}
#>
</optgroup>
</script>
<script type="text/html" id="flowmattic-select-router-dropdown-template">
<optgroup label="{{{ options.index }}}. Route {{{ options.routeLetter }}} - {{{ FlowMatticWorkflow.UCWords( options.name ) }}}" data-subtext="{{{ options.title }}}" data-max-options="1">
<#
var count = 1;
if ( _.isEmpty( options.data ) ) {
	if ( 1 === options.index ) {
		#>
		<option value="" disabled readonly>No data available. Please capture data in Trigger step.</option>
		<#
	} else {
		#>
		<option value="" disabled readonly>No data available. Please Save and Test Action.</option>
		<#
	}
} else {
	_.each( options.data, function( value, key ) {
		var optionValue = '{route' + options.routeLetter + '.' + options.name + options.index + '.' + key + '}';
		value = FlowMatticWorkflow.escapeHTML( value );
		#>
		<option value="{{{ optionValue }}}">{{{ count }}}. {{{ FlowMatticWorkflow.UCWords( key ) }}} : {{{ value }}}</option>
		<#
		count++;
	} );
}
#>
</optgroup>
</script>
<script type="text/html" id="flowmattic-app-request-response-template">
<div class="fm-response-body-wrapper">
	<a href="javascript:void(0);" class="fm-response-data-toggle toggle">
		<span class="fm-response-toggle-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</span>
		<?php echo esc_attr__( 'Request Response', 'flowmattic' ); ?>
	</a>
	<div class="fm-response-body webhook-response-body w-100" style="display:none;">
		<table class="fm-webhook-response-data-table w-100">
			<thead>
				<tr>
					<th class="w-50">Key</th>
					<th class="w-50">Value</th>
				</tr>
			</thead>
			<tbody>
			<#
			if ( 'undefined' !== typeof capturedData && ! _.isEmpty( capturedData ) ) {
				delete( capturedData.length );
			}
			_.each( capturedData, function( value, key ) {
				key = FlowMatticWorkflow.UCWords( key );
				value = FlowMatticWorkflow.escapeHTML( value );
				#>
				<tr>
					<td>
						<input class="fm-response-form-input w-100" type="text" value="{{{ key }}}" readonly />
					</td>
					<td>
						<textarea class="fm-response-form-input w-100" rows="1" readonly>{{{ value }}}</textarea>
					</td>
				</tr>
				<#
			} );
			#>
			</tbody>
		</table>
	</div>
</div>
</script>
<script type="text/html" id="flowmattic-text-editor-template">
<div class="editor-container">
	<nav class="navbar navbar-expand-lg navbar-light flowmattic-editor-nav bg-white border p-0">
		<div class="container-fluid p-0">
			<div class="collapse navbar-collapse">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0 flowmattic-editor-toolbar">
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Bold', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="bold"><span class="dashicons dashicons-editor-bold"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Italic', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="italic"><span class="dashicons dashicons-editor-italic"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Align Left', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="justifyLeft"><span class="dashicons dashicons-editor-alignleft"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Align Center', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="justifyCenter"><span class="dashicons dashicons-editor-aligncenter"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Align Right', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="justifyRight"><span class="dashicons dashicons-editor-alignright"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Justify Content', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="justifyFull"><span class="dashicons dashicons-editor-justify"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Insert Ordered List', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="insertOrderedList"><span class="dashicons dashicons-editor-ol"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Insert Unordered List', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="insertUnorderedList"><span class="dashicons dashicons-editor-ul"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Insert Link', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="createLink"><span class="dashicons dashicons-admin-links"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Insert Image', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="createImage"><span class="dashicons dashicons-format-image"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Undo', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="undo"><span class="dashicons dashicons-undo"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Redo', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="redo"><span class="dashicons dashicons-redo"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0 dynamic-tag-inserter">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Insert Dynamic Tag', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="dynamicTag"><span class="dashicons dashicons-database" title="Insert dynamic tag"></span></button>
					</li>
					<li class="nav-item d-inline-flex align-items-center mb-0">
						<button data-toggle="tooltip" title="<?php esc_html_e( 'Toggle Code View', 'flowmattic' ); ?>" class="d-inline-flex btn" data-command="toggleCode"><span class="dashicons dashicons-editor-code" title="Toggle Code View"></span></button>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<#
	if ( 'undefined' !== typeof post_content ) {
		post_content = post_content.replace(/\n/g, '<br>');

		// Convert the content from HTML entities to plain text.
		post_content = FlowMatticWorkflow.decodeEntities( post_content );
	} else {
		post_content = '';
	}
	#>
	<div class="flowmattic-editor border border-top-0 p-3" contenteditable="true">{{{ post_content }}}</div>
</div>
<style>
.flowmattic-editor img {
	border: 1px dotted transparent !important;
	position: relative;
	cursor: nwse-resize;
}
.flowmattic-editor img:hover {
	border: 1px dotted #ccc !important;
}
.flowmattic-editor-nav {
	position: sticky !important;
	top: 110px;
	z-index: 9;
}
.flowmattic-editor pre {
	white-space: pre-wrap;       /* css-3 */
	white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	white-space: -pre-wrap;      /* Opera 4-6 */
	white-space: -o-pre-wrap;    /* Opera 7 */
	word-wrap: break-word;       /* Internet Explorer 5.5+ */
}
</style>
</script>
<script type="text/html" id="flowmattic-dynamic-map-toggle">
	<div class="dynamic-mapping-toggle d-flex align-items-top justify-content-end pb-1" style="margin-top: -35px;margin-bottom: 5px;">
		<span class="badge outline bg-transparent text-secondary">Map</span>
		<div class="form-check form-switch">
			<input class="form-check-input dynamic-map-toggle" type="checkbox" role="switch" {{checked}} name="map-field-{{fieldName}}" value="{{fieldName}}" style="margin-top: 0.25em;background-repeat: no-repeat;">
		</div>
	</div>
	<div class="fm-dynamic-map-toggle-input fm-dynamic-input-field">
		<input type="text" class="form-control map-field dynamic-field-input w-100" name="{{fieldName}}" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof fieldValue ) { #>{{{ fieldValue }}}<# } #>"/>
		<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
	</div>
</script>
<!-- Help modal -->
<div class="modal fade" id="testRunModal" tabindex="-1" role="dialog" aria-labelledby="testRunModal-label" aria-hidden="true" style="z-index: 1000000000; height: calc(75% - 32px); margin: 0 auto; top: calc( 25% / 2 - 32px );">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content border-0 flowmattic-re-execute-workflow-modal">
			<div class="modal-header">
				<h5 class="modal-title" id="testRunModal-label"><?php esc_html_e( 'Run Workflow Test', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body d-flex flex-column gap-3">
				<div class="alert alert-primary" role="alert">
					<span class="fs-6"><?php echo esc_html__( 'Here is the response of your trigger app. You can use the same data or you can change it manually to perform test run of workflow.', 'flowmattic' ); ?></span>
				</div>
				<div class="fm-response-table w-100">
					<table class="fm-response-data-table w-100">
						<thead>
							<tr>
								<th class="w-50">Key</th>
								<th class="w-50">Value</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="do-test-run"><?php esc_html_e( 'Run Now', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary" id="cancel-run" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
/*\
|*| ========================================================================
|*| Bootstrap Toggle: bootstrap4-toggle.js v3.6.1
|*| https://gitbrent.github.io/bootstrap4-toggle/
\*/
!function(a){"use strict";function l(t,e){this.$element=a(t),this.options=a.extend({},this.defaults(),e),this.render()}l.VERSION="3.6.0",l.DEFAULTS={on:"On",off:"Off",onstyle:"primary",offstyle:"light",size:"normal",style:"",width:null,height:null},l.prototype.defaults=function(){return{on:this.$element.attr("data-on")||l.DEFAULTS.on,off:this.$element.attr("data-off")||l.DEFAULTS.off,onstyle:this.$element.attr("data-onstyle")||l.DEFAULTS.onstyle,offstyle:this.$element.attr("data-offstyle")||l.DEFAULTS.offstyle,size:this.$element.attr("data-size")||l.DEFAULTS.size,style:this.$element.attr("data-style")||l.DEFAULTS.style,width:this.$element.attr("data-width")||l.DEFAULTS.width,height:this.$element.attr("data-height")||l.DEFAULTS.height}},l.prototype.render=function(){this._onstyle="btn-"+this.options.onstyle,this._offstyle="btn-"+this.options.offstyle;var t="large"===this.options.size||"lg"===this.options.size?"btn-lg":"small"===this.options.size||"sm"===this.options.size?"btn-sm":"mini"===this.options.size||"xs"===this.options.size?"btn-xs":"",e=a('<label for="'+this.$element.prop("id")+'" class="btn">').html(this.options.on).addClass(this._onstyle+" "+t),s=a('<label for="'+this.$element.prop("id")+'" class="btn">').html(this.options.off).addClass(this._offstyle+" "+t),o=a('<span class="toggle-handle btn btn-light">').addClass(t),i=a('<div class="toggle-group">').append(e,s,o),l=a('<div class="toggle btn" data-toggle="toggle" role="button">').addClass(this.$element.prop("checked")?this._onstyle:this._offstyle+" off").addClass(t).addClass(this.options.style);this.$element.wrap(l),a.extend(this,{$toggle:this.$element.parent(),$toggleOn:e,$toggleOff:s,$toggleGroup:i}),this.$toggle.append(i);var n=this.options.width||Math.max(e.outerWidth(),s.outerWidth())+o.outerWidth()/2,h=this.options.height||Math.max(e.outerHeight(),s.outerHeight());e.addClass("toggle-on"),s.addClass("toggle-off"),this.$toggle.css({width:n,height:h}),this.options.height&&(e.css("line-height",e.height()+"px"),s.css("line-height",s.height()+"px")),this.update(!0),this.trigger(!0)},l.prototype.toggle=function(){this.$element.prop("checked")?this.off():this.on()},l.prototype.on=function(t){if(this.$element.prop("disabled"))return!1;this.$toggle.removeClass(this._offstyle+" off").addClass(this._onstyle),this.$element.prop("checked",!0),t||this.trigger()},l.prototype.off=function(t){if(this.$element.prop("disabled"))return!1;this.$toggle.removeClass(this._onstyle).addClass(this._offstyle+" off"),this.$element.prop("checked",!1),t||this.trigger()},l.prototype.enable=function(){this.$toggle.removeClass("disabled"),this.$toggle.removeAttr("disabled"),this.$element.prop("disabled",!1)},l.prototype.disable=function(){this.$toggle.addClass("disabled"),this.$toggle.attr("disabled","disabled"),this.$element.prop("disabled",!0)},l.prototype.update=function(t){this.$element.prop("disabled")?this.disable():this.enable(),this.$element.prop("checked")?this.on(t):this.off(t)},l.prototype.trigger=function(t){this.$element.off("change.bs.toggle"),t||this.$element.change(),this.$element.on("change.bs.toggle",a.proxy(function(){this.update()},this))},l.prototype.destroy=function(){this.$element.off("change.bs.toggle"),this.$toggleGroup.remove(),this.$element.removeData("bs.toggle"),this.$element.unwrap()};var t=a.fn.bootstrapToggle;a.fn.bootstrapToggle=function(o){var i=Array.prototype.slice.call(arguments,1)[0];return this.each(function(){var t=a(this),e=t.data("bs.toggle"),s="object"==typeof o&&o;e||t.data("bs.toggle",e=new l(this,s)),"string"==typeof o&&e[o]&&"boolean"==typeof i?e[o](i):"string"==typeof o&&e[o]&&e[o]()})},a.fn.bootstrapToggle.Constructor=l,a.fn.toggle.noConflict=function(){return a.fn.bootstrapToggle=t,this},a(function(){a("input[type=checkbox][data-toggle^=toggle]").bootstrapToggle()}),a(document).on("click.bs.toggle","div[data-toggle^=toggle]",function(t){a(this).find("input[type=checkbox]").bootstrapToggle("toggle"),t.preventDefault()})}(jQuery);
</script>
