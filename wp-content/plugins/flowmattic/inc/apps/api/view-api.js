/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger API View.
		FlowMatticWorkflow.ApiView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-api-template' ).html() ),

			events: {
				'change select[name="response_format"]': 'changeResponseFormat',
			},

			initialize: function() {
			},

			render: function() {
				var appAction = this.model.get( 'action' ),
					application = this.model.get( 'application' ),
					actionApp = ( 'undefined' !== typeof actionApps[ application ] ) ? actionApps[ application ] : otherActionApps[ application ],
					authSelected = this.model.get( 'authentication' ),
					connectSelected = this.model.get( 'connect_id' ),
					contentTypeSelected = this.model.get( 'content_type' ),
					apiEndpoint = this.model.get( 'api_endpoint' ),
					customJSON = this.model.get( 'custom_json' ),
					conditional_execution = this.model.get( 'conditional_execution' ),
					simple_response = this.model.get( 'simple_response' ),
					response_format = this.model.get( 'response_format' );

				this.model.set( 'applicationEvents', actionApp.actions );
				this.model.set( 'applicationAction', appAction );
				this.model.set( 'contentType', ( 'undefined' !== typeof contentTypeSelected ) ? contentTypeSelected : 'json' );
				this.model.set( 'authType', ( 'undefined' !== typeof authSelected ) ? authSelected : 'no' );
				this.model.set( 'connectID', ( 'undefined' !== typeof connectSelected ) ? connectSelected : '' );
				this.model.set( 'endpointURL', ( 'undefined' !== typeof apiEndpoint ) ? apiEndpoint : '' );
				this.model.set( 'customJSON', ( 'undefined' !== typeof customJSON ) ? customJSON : '' );
				this.model.set( 'conditional_execution', ( 'undefined' !== typeof conditional_execution ) ? conditional_execution : '' );
				this.model.set( 'simple_response', ( 'undefined' === typeof simple_response ) ? 'Yes' : simple_response );
				this.model.set( 'response_format', ( 'undefined' !== typeof response_format ) ? response_format : 'simple' );

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'custom_json' === this.model.get( 'content_type' ) ) {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).removeClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).addClass( 'hidden' );
				} else {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).addClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).removeClass( 'hidden' );
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			changeResponseFormat: function( e ) {
				var responseFormat = jQuery( e.currentTarget ).val();

				this.model.set( 'response_format', responseFormat );
				this.model.set( 'simple_response', responseFormat );
			}
		} );
	} );
}( jQuery ) );
