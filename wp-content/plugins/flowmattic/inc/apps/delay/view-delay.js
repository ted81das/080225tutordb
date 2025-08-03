/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Delay View.
		FlowMatticWorkflow.DelayView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-delay-data-template' ).html() ),
			delayUntilTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-delay-until-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					submissionData = {};

				if ( 'delay_for' === appAction ) {
					this.$el.html( this.template( this.model.toJSON() ) );
				} else if ( 'delay_until' === appAction ) {
					this.$el.html( this.delayUntilTemplate( this.model.toJSON() ) );
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
