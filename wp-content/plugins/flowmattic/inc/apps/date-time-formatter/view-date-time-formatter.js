/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger date time formatter View.
		FlowMatticWorkflow.Date_Time_FormatterView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-date-time-formatter-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					actionTemplate;

				thisEl.$el.html( thisEl.template( thisEl.model.toJSON() ) );

				if ( jQuery( '#flowmattic-date-time-formatter-action-' + appAction + '-data-template' ).length ) {
					actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-date-time-formatter-action-' + appAction + '-data-template' ).html() );
					jQuery( thisEl.$el ).find( '.date-time-formatter-action-data' ).html( actionTemplate( thisEl.model.toJSON() ) );
				}

				thisEl.$el.find( 'select' ).selectpicker();

				if ( this.$el.find( '.timezone-dropdown' ) ) {
					var timezoneInputs = this.$el.find( '.timezone-dropdown' );
					_.each( timezoneInputs, function( input ) {
						var timezoneValue = jQuery( input ).attr( 'data-value' );
						// Update the value on DOM.
						jQuery( input ).val( timezoneValue );
						jQuery( input ).find( 'option[value="' + timezoneValue + '"]' ).attr( 'selected', 'selected' );

						if ( ! window.visualWorkflowBuilder ) {
							jQuery( input ).selectpicker( 'val', timezoneValue );
						}
					} );
				}

				return this;
			}
		} );
	} );
}( jQuery ) );
