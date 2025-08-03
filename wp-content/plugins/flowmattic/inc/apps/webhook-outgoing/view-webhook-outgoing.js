/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger webhook-outgoing View.
		FlowMatticWorkflow.Webhook_OutgoingView = Backbone.View.extend( {
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-webhook-outgoing-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				if ( 'undefined' === typeof this.model.get( 'actionAppArgs' ) ) {
					this.model.set( 'actionAppArgs', [] );
				}

				this.$el.html( this.actionTemplate( this.model.toJSON() ) );
				this.setActionOptions();

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-webhook-outgoing-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-webhook-outgoing-action-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
