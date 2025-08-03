/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger plugin actions View.
		FlowMatticWorkflow.Plugin_ActionsView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-plugin-actions-data-template' ).html() ),

			events: {
				'click .flowmattic-plugin-action-hook-capture-button': 'capturePluginActionHookData',
				'change .plugin-action-hook': 'updateActionHook'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				if ( 'undefined' === typeof this.model.get( 'pluginAction' ) ) {
					this.model.set( 'pluginAction', '' );
				}

				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				return this;
			},

			capturePluginActionHookData: function() {
				var thisEl = this,
					captureButton = jQuery( event.target ),
					customAction = setInterval( capturePluginActionHook, 2000 ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					actionHook = jQuery( 'body' ).find( '.plugin-action-hook' ).val(),
					captureResponse = 1,
					customActionResponse = {};

				// Set the plugin action.
				this.model.set( 'pluginAction', actionHook );

				// Reset the previous captured data.
				this.$el.find( '.fm-action-hook-capture-data' ).html('');

				captureButton.text( 'Initializing request...' );

				// Reset the previous captured data.
				this.$el.find( '.fm-custom-action-capture-data' ).html('');

				// Save the action hook first.
				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_save_plugin_action_data', actionHook: actionHook, 'webhook-id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
						success: function( response ) {
						}
					}
				);

				function capturePluginActionHook() {
					captureButton.text( 'Waiting for response' ).prepend( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="32px" height="32px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\
						<circle cx="50" cy="50" r="30" stroke="#ffffff" stroke-width="10" fill="none"></circle>\
						<circle cx="50" cy="50" r="30" stroke="#007bff" stroke-width="8" stroke-linecap="round" fill="none">\
						<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>\
						<animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1s" values="5.654866776461627 182.84069243892594;94.2477796076938 94.24777960769377;5.654866776461627 182.84069243892594" keyTimes="0;0.5;1"></animate>\
						</circle></svg>'
					);

					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_capture_plugin_action_data', actionHook: actionHook, 'webhook-id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								var processedResponse = {};
								response = JSON.parse( response );

								if ( 'pending' !== response.status ) {
									clearInterval( customAction );

									_.each( response.webhook_capture, function( value, key ) {
										if ( 'object' === typeof value ) {
											_.each( value, function( innerValue, innerKey ) {
												if ( 'object' === typeof innerValue ) {
													_.each( innerValue, function( innerValue2, innerKey2 ) {
														if ( 'object' === typeof innerValue2 ) {
															processedResponse[ key + '_' + innerKey + '_' + innerKey2 ] = JSON.stringify( innerValue2 );
														} else {
															processedResponse[ key + '_' + innerKey + '_' + innerKey2 ] = innerValue2;
														}
													} );
												} else {
													processedResponse[ key + '_' + innerKey ] = innerValue;
												}
											} );
										} else {
											processedResponse[ key ] = value;
										}
									} );

									setTimeout( function() {
										response.webhook_capture = processedResponse;

										captureButton.text( 'Re-capture Action Data' );
										thisEl.model.set( 'capturedData', { webhook_capture: processedResponse } );
										FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'capturedData', { webhook_capture: processedResponse } );
										FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', { webhook_capture: processedResponse } );

										// Auto save workflow.
										FlowMatticWorkflowEvents.trigger( 'processAutosave' );
									}, 500 );
								}

								captureResponse = '0';
							}
						}
					);
				}
			},

			updateActionHook: function() {
				var actionHook = jQuery( this.$el ).find( '.plugin-action-hook' ).val();

				if ( '' !== actionHook ) {
					// Set the plugin action.
					this.model.set( 'pluginAction', actionHook );

					FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'pluginAction', actionHook );
				}
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-plugin-actions-form-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-plugin-actions-form-data' ).show();
				}
			},

			toggleResponseData: function() {
				var toggleLink = jQuery( this.$el ).find( '.fm-response-data-toggle' ),
					toggleDataWrap = toggleLink.next( '.fm-response-body' );

				toggleLink.toggleClass( 'toggle' );
				toggleDataWrap.toggle( 'slideTop' );
			}
		} );
	} );
}( jQuery ) );
