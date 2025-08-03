/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger API polling View.
		FlowMatticWorkflow.Api_PollingView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-api-polling-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				// Set the current time.
				this.model.set( 'current_time', new Date().getTime() );

				// If api_endpoint_url is not available then set the default value.
				if ( ! this.model.get( 'api_endpoint_url' ) ) {
					this.model.set( 'api_endpoint_url', '' );
				}

				// If api_polling_method is not available then set the default value.
				if ( ! this.model.get( 'api_polling_method' ) ) {
					this.model.set( 'api_polling_method', 'GET' );
				}

				// If api_parameters is not available then set the default value.
				if ( ! this.model.get( 'api_parameters' ) ) {
					this.model.set( 'api_parameters', {} );
				}

				// If api_headers is not available then set the default value.
				if ( ! this.model.get( 'api_headers' ) ) {
					this.model.set( 'api_headers', {} );
				}

				// If api_item_index is not available then set the default value.
				if ( ! this.model.get( 'api_item_index' ) ) {
					this.model.set( 'api_item_index', '' );
				}

				this.$el.html( this.template( this.model.toJSON() ) );
				this.$el.find( 'select' ).selectpicker();
				return this;
			}
		} );
	} );
}( jQuery ) );
