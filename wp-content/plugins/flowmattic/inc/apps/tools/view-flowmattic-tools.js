/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Tools View.
		FlowMatticWorkflow.ToolsView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-tools-action-data-template' ).html() ),

			events: {
				'change select[name="variable_type"]': 'onChangeVariableType',
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' ),
					actionTemplate;

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'undefined' !== typeof appAction && '' !== appAction ) {
					actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-tools-action-' + appAction + '-template' ).html() );

					jQuery( thisEl.$el ).find( '.tools-action-data' ).html( actionTemplate( thisEl.model.toJSON() ) );
				}

				// Hide all .variable-type-values elements.
				jQuery( thisEl.$el ).find( '.variable-type-values' ).hide();

				// Show only the selected variable type.
				var actionAppArgs = thisEl.model.get( 'actionAppArgs' );
				if ( 'undefined' !== typeof actionAppArgs && '' !== actionAppArgs ) {
					var variableType = actionAppArgs.variable_type || 'string';
					jQuery( thisEl.$el ).find( '.variable-type-values.variable-type-' + variableType ).show();
				}

				thisEl.$el.find( 'select' ).selectpicker();

				return this;
			},

			onChangeVariableType: function( event ) {
				var selectedType = jQuery( 'select[name="variable_type"]' ).val(),
					$variableTypeValues = jQuery( this.$el ).find( '.variable-type-values' );

				// Hide all .variable-type-values elements.
				$variableTypeValues.hide();

				// Show only the selected variable type.
				if ( '' !== selectedType ) {
					$variableTypeValues.filter( '.variable-type-' + selectedType ).show();
				}
			}
		} );
	} );
}( jQuery ) );
