/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger iterator storage View.
		FlowMatticWorkflow.Iterator_StorageView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-iterator-storage-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					actionTemplate,
					submissionData = {};

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( jQuery( '#flowmattic-iterator-storage-action-' + appAction + '-data-template' ).length ) {
					actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-iterator-storage-action-' + appAction + '-data-template' ).html() );
					jQuery( this.$el ).find( '.iterator-storage-action-data' ).html( actionTemplate( this.model.toJSON() ) );
				}

				this.$el.find( 'select' ).selectpicker();
				return this;
			}
		} );
	} );
}( jQuery ) );
