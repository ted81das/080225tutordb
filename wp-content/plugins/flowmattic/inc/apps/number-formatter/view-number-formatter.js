/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger number-formatter View.
		FlowMatticWorkflow.Number_FormatterView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-number-formatter-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' );

				thisEl.$el.html( thisEl.template( thisEl.model.toJSON() ) );

				if ( jQuery( '#flowmattic-number-formatter-action-' + appAction + '-data-template' ).length ) {
					appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-number-formatter-action-' + appAction + '-data-template' ).html() );
					jQuery( thisEl.$el ).find( '.flowmattic-number-formatter-action-data' ).html( appActionTemplate( thisEl.model.toJSON() ) );
				}

				return this;
			}
		} );
	} );
}( jQuery ) );
