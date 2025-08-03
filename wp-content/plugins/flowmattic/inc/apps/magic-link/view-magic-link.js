/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Magic Link View.
		FlowMatticWorkflow.Magic_LinkView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-magic-link-trigger-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				return this;
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-magic-link-trigger-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-magic-link-trigger-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
