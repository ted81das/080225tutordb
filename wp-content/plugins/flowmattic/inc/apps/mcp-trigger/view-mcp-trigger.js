/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger MCP trigger View.
		FlowMatticWorkflow.Mcp_TriggerView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-mcp-trigger-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-mcp-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				if ( 'trigger' === this.model.get( 'type' ) ) {
					this.$el.html( this.triggerTemplate( this.model.toJSON() ) );
				} else {
					this.$el.html( this.actionTemplate( this.model.toJSON() ) );
				}

				return this;
			}
		} );
	} );
}( jQuery ) );
