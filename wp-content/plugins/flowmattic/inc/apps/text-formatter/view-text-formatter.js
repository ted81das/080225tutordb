/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger text-formatter View.
		FlowMatticWorkflow.Text_FormatterView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-text-formatter-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' );

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( jQuery( '#flowmattic-text-formatter-action-' + appAction + '-data-template' ).length ) {
					appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-text-formatter-action-' + appAction + '-data-template' ).html() );
					jQuery( this.$el ).find( '.flowmattic-text-formatter-action-data' ).html( appActionTemplate( this.model.toJSON() ) );
				}

				return this;
			}
		} );
	} );
}( jQuery ) );
