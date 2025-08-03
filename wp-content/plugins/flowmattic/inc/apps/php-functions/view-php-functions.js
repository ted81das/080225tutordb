/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger PHP Function View.
		FlowMatticWorkflow.Php_FunctionsView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-phpfunction-data-template' ).html() ),

			events: {
				'change select[name="parameter_type"]': 'changeParameterTypes'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					submissionData = {},
					actionAppArgs;

				if ( 'undefined' !== typeof this.model.get( 'parameter_type' ) ) {
					actionAppArgs = ( 'undefined' === typeof this.model.get( 'actionAppArgs' ) ) ? {} : this.model.get( 'actionAppArgs' );
					actionAppArgs.parameter_type = this.model.get( 'parameter_type' );
				}

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.changeParameterTypes();
				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			changeParameterTypes: function( event ) {
				var inputValue = jQuery( this.$el ).find( 'select[name="parameter_type"]' ).val();
				this.model.set( 'parameter_type', inputValue );

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'parameter_type', inputValue, this );

				if ( 'none' === inputValue ) {
					jQuery( this.$el ).find( '.function-parameters' ).addClass( 'hidden' );
				} else {
					jQuery( this.$el ).find( '.function-parameters' ).removeClass( 'hidden' );
				}

				jQuery( this.$el ).find( '.function-parameters .fm-application-instructions' ).addClass( 'hidden' );
				jQuery( this.$el ).find( '.function-parameters .fm-application-instructions.instructions-' + inputValue ).removeClass( 'hidden' );
			}
		} );
	} );
}( jQuery ) );
