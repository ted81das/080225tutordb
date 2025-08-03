<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */

?>
<script type="text/html" id="flowmattic-workflow-trigger-data-template">
<div class="fm-workflow-step-header">
	<div class="fm-workflow-icon">
		<#
		var appIcon = ( 'undefined' !== typeof triggerApps[ application ] ) ? triggerApps[ application ].icon : otherTriggerApps[ application ].icon;
		#>
		<img src="{{{ appIcon }}}" style="width:48px;height:48px;" />
	</div>
	<div class="fm-workflow-step-info">
		<span class="fm-workflow-hint-label"><?php esc_attr_e( 'Trigger: When this happens...', 'flowmattic' ); ?></span>
		<h4 class="fm-workflow-step-application-title">
			<strong>1. </strong>
			<span class="workflow-trigger-title">
				<#
				var appActionTitle = '';

				if ( 'undefined' !== typeof customStepTitle ) {
					appActionTitle = '';
				} else {
					if ( '' !== triggerAction && 'undefined' !== typeof applicationTriggers[ triggerAction ] ) {
						appActionTitle = applicationTriggers[ triggerAction ].title;
					} else {
						if ( '' !== triggerAction && 'webhook' !== application && 'undefined' !== typeof _.findWhere( applicationTriggers, {'value': triggerAction } ) ) {
							appActionTitle = _.findWhere( applicationTriggers, {'value': triggerAction } ).name;
						}

						_.each( applicationTriggers, function( trigger, value ) {
							if ( 'object' === typeof trigger[0] ) {
								triggerActions = _.find( trigger, function( triggerEvent ) {
									return ( typeof triggerEvent[ triggerAction ] );
								} );

								if ( 'undefined' !== typeof triggerActions[ triggerAction ] ) {
									appActionTitle = triggerActions[ triggerAction ].title;
									return false;
								}
							}
						} );
					}
				}
				#>
				<span class="fm-application-title">{{{applicationName}}}</span> <span class="fm-application-action">{{{ appActionTitle }}}</span>
			</span>
		</h4>
	</div>
	<div class="fm-workflow-step-header-actions">
		<div class="fm-workflow-step-action fm-workflow-step-rename" data-toggle="tooltip" title="<?php echo esc_html__( 'Rename Trigger', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path>
			</svg>
		</div>
		<div class="fm-workflow-step-action fm-workflow-trigger-description" style="opacity: 1;" data-toggle="tooltip" title="<?php echo esc_html__( 'Click to display or add the description for this step.', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M4 22H20C21.1 22 22 21.1 22 20V4C22 2.9 21.1 2 20 2H4C2.9 2 2 2.9 2 4V20C2 21.1 2.9 22 4 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M12 13C12.76 13 13.33 12.48 13.42 11.78L13.99 6.56C14.09 5.7 13.42 5 12.57 5H11.43C10.58 5 9.91 5.7 10.01 6.57L10.58 11.79C10.67 12.48 11.24 13 12 13Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#221b38" fill="none" d="M12 19C12.83 19 13.5 18.33 13.5 17.5C13.5 16.67 12.83 16 12 16C11.17 16 10.5 16.67 10.5 17.5C10.5 18.33 11.17 19 12 19Z"></path>
			</svg>
		</div>
		<div class="fm-workflow-step-action fm-workflow-step-accordion-collapse" data-toggle="tooltip" title="<?php echo esc_html__( 'Toggle Trigger Step', 'flowmattic' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</div>
	</div>
</div>
<div class="fm-workflow-step-body w-100">
	<div class="fm-workflow-trigger-select">
		<div class="fm-workflow-applications">
			<div class="form-group fm-workflow-action-heading">
				<h4><?php esc_attr_e( 'Choose Application', 'flowmattic' ); ?></h4>
				<div class="flowmattic-dropdown">
					<select name="workflow-application" class="workflow-trigger w-100" title="Choose Application" data-live-search="true">
						<optgroup label="FlowMattic Apps" data-max-options="1">
							<#
							_.each( triggerApps, function( settings, appSlug ) {
								#>
								<option
									<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
									<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}" <# } #>
									value="{{{appSlug}}}">{{{ settings.name }}}
								</option>
								<#
							} );
							#>
						</optgroup>
						<optgroup label="Other Apps" data-max-options="1">
						<#
						_.each( otherTriggerApps, function( settings, appSlug ) {
							#>
							<option
								<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
								<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}" <# } #>
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
		</div>
		<div class="fm-workflow-triggers">
			<div class="form-group fm-workflow-application-events">
				<div class="fm-workflow-event-heading">
					<h4><?php esc_attr_e( 'Trigger Event', 'flowmattic' ); ?></h4>
				</div>
				<select name="workflow-trigger-events" class="workflow-trigger-events w-100" title="Choose an event" data-live-search="true">
					<#
					_.each( applicationTriggers, function( trigger, value ) {
						if ( 'object' === typeof trigger[0] ) {
							#>
							<optgroup label="{{{ FlowMatticWorkflow.UCWords( value ) }}}" data-max-options="1">
								<#
									_.each( trigger, function( triggers ) {
										_.each( triggers, function( triggerKey, triggerValue ) {
											#>
											<option <# if ( triggerAction === triggerValue ) { #>selected<# } #> value="{{{ triggerValue }}}" data-subtext="{{{ triggerKey.description }}}">{{{ triggerKey.title }}}</option>
											<#
										} );
									} );
								#>
							</optgroup>
							<#
						} else {
							#>
							<option
								<# if ( triggerAction === value ) { #>selected<# } #>
								value="{{{ value }}}"
								<# if ( 'undefined' !== typeof trigger.description ) { #>data-subtext="{{{ trigger.description }}}"<# } #>>
								{{{ trigger.title }}}
							</option>
							<#
						}
					} )
					#>
				</select>
			</div>
		</div>
	</div>
	<div class="fm-workflow-trigger-app-data"></div>
</div>
<div class="fm-workflow-add-step">
	<a href="javascript:void(0)" class="fm-add-step fm-add-trigger" data-toggle="tooltip" title="<?php echo esc_html__( 'Add New Action', 'flowmattic' ); ?>">
		<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
	</a>
</div>
</script>
<script type="text/html" id="flowmattic-trigger-description-template">
<div class="modal fade" id="triggerStepDescription" tabindex="-1" aria-labelledby="triggerStepDescriptionLabel" aria-hidden="true" data-backdrop="static" style="z-index: 999999;">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="triggerStepDescriptionLabel"><?php echo esc_html__( 'Trigger Step Description', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label for="description-text" class="col-form-label pt-0"><?php echo esc_html__( 'Description:', 'flowmattic' ); ?></label>
					<span class="form-control span-textarea" role="textbox" contenteditable id="fm-trigger-description-text">{{{ stepDescriptionText }}}</span>
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
<script type="text/html" id="flowmattic-simple-response-template">
<div class="form-group dynamic-inputs simple-response-input w-100">
	<div class="fm-input-wrap">
		<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Simple Response', 'flowmattic' ); ?></strong></h4>
		<div class="form-check form-switch">
			<input class="form-check-input form-control fm-simple-response me-2" type="checkbox" name="simple_response" id="fm-checkbox-simple-response" <# if ( ( 'undefined' === typeof simple_response || 'Yes' === simple_response ) && ( 'undefined' === typeof simple_reponse || 'on' === simple_reponse ) ) { #>checked<# } #> style="width:2em;margin-top: 0.25em;background-repeat: no-repeat;">
			<label for="fm-checkbox-simple-response"><?php esc_attr_e( 'Retrieve the data in simple format', 'flowmattic' ); ?></label>
		</div>
	</div>
</div>
<#
if ( ! window.isVisualWorkflowBuilder ) {
	var allCapturedResponses = ( 'undefined' !== typeof window.capturedResponses ) ? window.capturedResponses : false;
	var selectedResponse  = ( 'undefined' !== typeof window.selectedResponse ) ? window.selectedResponse : '';
	#>
	<div class="form-group dynamic-inputs workflow-responses-dropdown w-100 <# if ( ! capturedResponses ) { #>d-none<# } #>">
		<div class="fm-input-wrap">
			<h4 class="fm-input-title"><strong><?php esc_attr_e( 'Choose Response', 'flowmattic' ); ?></strong></h4>
			<div class="responses-dropdown w-100">
				<select class="form-control form-select w-100" name="capturedResponses" id="captured-responses-select" data-live-search="true">
					<#
					_.each( allCapturedResponses, function( response, key ) {
						#>
						<option data-subtext="Captured at: {{{ response.captured_at }}}" value="{{{response.letter}}}" <# if ( response.letter === selectedResponse ) { #>selected<# } #>>Response {{{response.letter}}}</option>
						<#
					} );
					#>
				</select>
				<p class="fm-application-instructions"><?php esc_attr_e( 'Select the response you want to use in the workflow.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<#
}
#>
</script>
<script type="text/html" id="flowmattic-api-polling-basic-fields-template">
<div class="api-polling-basic-fields w-100">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-select-field">
			<select name="trigger_connect_id" class="trigger-api-connect form-control w-100 d-block" required title="Choose Connect" data-live-search="true">
				<#
				let connectID = ( 'undefined' !== typeof trigger_connect_id ) ? trigger_connect_id : '';
				#>
				<option value="none" <# if ( 'none' === connectID ) { #>selected<# } #>><?php esc_attr_e( 'No authentication required', 'flowmattic' ); ?></option>
				<?php
				$all_connects = wp_flowmattic()->connects_db->get_all();
				foreach ( $all_connects as $key => $connect_item ) {
					$connect_id   = $connect_item->id;
					$connect_name = $connect_item->connect_name;
					?>
					<option <# if ( connectID === '<?php echo esc_attr( $connect_id ); ?>' ) { #>selected<# } #> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
					<?php
				}
				?>
			</select>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Frequency', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-select-field">
			<select class="form-control form-select w-100" name="apiPollingFrequency" id="api-polling-frequency">
				<#
				var apiPollingFrequencies = {
					'1': '<?php echo esc_attr__( '1 Minute', 'flowmattic' ); ?>',
					'2': '<?php echo esc_attr__( '2 Minutes', 'flowmattic' ); ?>',
					'5': '<?php echo esc_attr__( '5 Minutes', 'flowmattic' ); ?>',
					'10': '<?php echo esc_attr__( '10 Minutes', 'flowmattic' ); ?>',
					'15': '<?php echo esc_attr__( '15 Minutes', 'flowmattic' ); ?>',
					'30': '<?php echo esc_attr__( '30 Minutes', 'flowmattic' ); ?>',
					'60': '<?php echo esc_attr__( '1 Hour', 'flowmattic' ); ?>',
					'120': '<?php echo esc_attr__( '2 Hours', 'flowmattic' ); ?>',
					'180': '<?php echo esc_attr__( '3 Hours', 'flowmattic' ); ?>',
					'360': '<?php echo esc_attr__( '6 Hours', 'flowmattic' ); ?>',
					'720': '<?php echo esc_attr__( '12 Hours', 'flowmattic' ); ?>',
					'1440': '<?php echo esc_attr__( '1 Day', 'flowmattic' ); ?>',
				};

				apiPollingFrequency = ( 'undefined' !== typeof apiPollingFrequency ) ? apiPollingFrequency : '10';
				
				_.each( apiPollingFrequencies, function( frequency, value ) {
					#>
					<option value="{{{value}}}" <# if ( value === apiPollingFrequency ) { #>selected<# } #>>{{{frequency}}}</option>
					<#
				} );
				#>
			</select>
		</div>
	</div>
</div>
</script>
<script type="text/html" id="flowmattic-custom-webhook-app-data-template">
	<div class="flowmattic-custom-app-form-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input type="text" class="w-100" readonly value="{{{webhookURL}}}" />
			<div data-application="{{application}}" class="fm-application-instructions">
				<#
				if ( '' !== otherTriggerApps[ application ].triggers[ action ].instructions ) {
					#>
					<p>{{{ otherTriggerApps[ application ].triggers[ action ].instructions }}}</p>
					<#
				} else {
					#>
					<p>{{{ otherTriggerApps[ application ].instructions }}}</p>
					<#
				}
				#>
			</div>
		</div>
		<#
		if ( ! window.isVisualWorkflowBuilder ) {
		#>
			<div class="fm-webhook-capture-button">
				<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
					<#
					if ( 'undefined' !== typeof capturedData ) {
						#>
						<?php echo esc_attr__( 'Re-capture Webhook Response', 'flowmattic' ); ?>
						<#
					} else {
						#>
						<?php echo esc_attr__( 'Capture Webhook Response', 'flowmattic' ); ?>
						<#
					}
					#>
				</a>
			</div>
			<div class="fm-webhook-capture-data fm-response-capture-data">
			</div>
		<#
		}
		#>
	</div>
</script>
