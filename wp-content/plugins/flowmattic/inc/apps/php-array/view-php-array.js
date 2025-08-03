/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger PHP Array View.
		FlowMatticWorkflow.Php_ArrayView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-php_array-data-template' ).html() ),

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

				if ( '' !== appAction ) {
					if ( 'undefined' !== typeof jQuery( '#flowmattic-application-php_array-' + appAction + '-data-template' ).html() ) {
						actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-application-php_array-' + appAction + '-data-template' ).html() );
						jQuery( this.$el ).find( '.flowmattic-php-array-function-fields' ).html( actionTemplate( this.model.toJSON() ) );
					} else {
						jQuery( this.$el ).find( '.flowmattic-php-array-function-fields' ).html( '' );
					}
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			}
		} );
	} );
}( jQuery ) );
