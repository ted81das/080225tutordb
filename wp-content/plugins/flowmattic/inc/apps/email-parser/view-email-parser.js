/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger email-parser View.
		FlowMatticWorkflow.Email_ParserView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-email-parser-data-template' ).html() ),

			events: {
				'click .flowmattic-capture-email-button': 'captureEmail'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				return this;
			},

			captureEmail: function( event ) {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				// Ajax to register the email parser.
				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'flowmattic_register_email_parser',
						workflow_id: workflowId,
						app_action: appAction,
						workflow_nonce: flowMatticAppConfig.workflow_nonce
					},
					success: function( response ) {
						console.log( response );
					}
				} );
				
			}
		} );
	} );
}( jQuery ) );
