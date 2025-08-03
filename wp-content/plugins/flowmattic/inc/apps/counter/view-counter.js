/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Counter View.
		FlowMatticWorkflow.CounterView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-counter-action-data-template' ).html() ),

			events: {
				'change select[name="reset_execution"]': 'toggleResetValueCounter'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.find( 'select' ).selectpicker();

				// Toggle options.
				this.toggleResetValueCounter();

				return this;
			},

			toggleResetValueCounter: function() {
				var resetExecution = jQuery( this.$el ).find( 'select[name="reset_execution"]' ).val();

				if ( 'yes' === resetExecution ) {
					jQuery( this.$el ).find( '.reset-counter-value' ).show();
				} else {
					jQuery( this.$el ).find( '.reset-counter-value' ).hide();
				}
			}
		} );
	} );
}( jQuery ) );
