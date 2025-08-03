/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Chatbot app View.
		FlowMatticWorkflow.Chatbot_AppView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-fm-chatbot-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );
				this.$el.find( 'select' ).selectpicker();
				return this;
			}
		} );
	} );
}( jQuery ) );
