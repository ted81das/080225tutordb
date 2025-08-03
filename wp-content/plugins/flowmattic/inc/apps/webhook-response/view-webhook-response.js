/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger webhook-response View.
		FlowMatticWorkflow.Webhook_ResponseView = Backbone.View.extend( {
			responseActionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-webhook-response-action-data-template' ).html() ),
			redirectActionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-webhook-redirect-action-data-template' ).html() ),

			events: {
				'change .response-type-select': 'setResponseFields'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' );

				if ( 'undefined' === typeof this.model.get( 'actionAppArgs' ) ) {
					this.model.set( 'actionAppArgs', [] );
				}

				if ( 'set_webhook_redirect' === appAction ) {
					this.$el.html( this.redirectActionTemplate( this.model.toJSON() ) );
				} else {
					this.$el.html( this.responseActionTemplate( this.model.toJSON() ) );
				}

				this.setActionOptions();
				this.setResponseFields();

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-webhook-response-action-data, .response-field' ),
					currentAction = this.model.get( 'action' );

				// Hide all elements initially.
				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-webhook-response-action-data' ).show();
				}
			},

			setResponseFields: function() {
				var elements = jQuery( this.$el ).find( '.response-field' ),
					responseType = jQuery( this.$el ).find( '.response-type-select[name="webhook_response_type"]' ).val();

				// Hide all elements initially.
				elements.hide();

				// Display respective response type.
				jQuery( this.$el ).find( '.response-field.response-' + responseType ).show();
			}
		} );
	} );
}( jQuery ) );
