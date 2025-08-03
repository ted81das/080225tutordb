/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Webhook View.
		FlowMatticWorkflow.WebhookView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-webhook-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				if ( 'undefined' === typeof this.model.get( 'simple_reponse' ) ) {
					this.model.set( 'simple_reponse', 'Yes' );
				}

				if ( 'undefined' === typeof this.model.get( 'simple_response' ) ) {
					this.model.set( 'simple_response', 'Yes' );
				}

				if ( 'undefined' === typeof this.model.get( 'webhook_response' ) ) {
					this.model.set( 'webhook_response', 'No' );
				}

				if ( 'undefined' === typeof this.model.get( 'webhook_security' ) ) {
					this.model.set( 'webhook_security', 'No' );
				}

				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.find( 'select' ).selectpicker();

				return this;
			}
		} );
	} );
}( jQuery ) );
