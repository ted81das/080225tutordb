/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger custom View.
		FlowMatticWorkflow.CustomView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-custom-data-template' ).html() ),

			events: {
				'click .flowmattic-custom-action-capture-button': 'captureCustomActionData'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				this.updateFormSubmissionData( this.model.toJSON() );

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				return this;
			},

			captureCustomActionData: function() {
				var thisEl = this,
					captureButton = jQuery( event.target ),
					customAction = setInterval( captureCustomAction, 2000 ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					captureResponse = 1,
					customActionResponse = {};

				event.preventDefault();

				// Reset the previous captured data.
				this.$el.find( '.fm-webhook-capture-data' ).html('');

				captureButton.text( 'Initializing request...' );

				// Reset the previous captured data.
				this.$el.find( '.fm-custom-action-capture-data' ).html('');

				function captureCustomAction() {
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
							data: { action: 'flowmattic_capture_custom_action_data', 'webhook-id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								response = JSON.parse( response );
								if ( 'pending' !== response.status ) {
									clearInterval( customAction );
									captureButton.text( 'Re-capture Action Data' );
									thisEl.model.set( 'capturedData', response );
									FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'capturedData', response );
									FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', response );

									// Auto save workflow.
									FlowMatticWorkflowEvents.trigger( 'processAutosave' );
								}

								captureResponse = '0';
							}
						}
					);
				}
			},

			updateFormSubmissionData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-custom-response-template' ).html() ),
					captureData = {};

				if ( 'undefined' !== typeof response.capturedData ) {
					if ( 'undefined' !== typeof response.capturedData.webhook_capture ) {
						captureData.capturedData = response.capturedData.webhook_capture;
					} else {
						captureData.capturedData = response.capturedData;
					}

					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( captureData ) );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-custom-form-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-custom-form-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
