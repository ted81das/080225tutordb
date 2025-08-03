/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Email View.
		FlowMatticWorkflow.EmailView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-email-data-template' ).html() ),

			events: {
				'change #fm-select-email-provider': 'updateMailProvider',
				'change #fm-select-email-template': 'updateMailTemplateVariables',
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					submissionData = {};

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.updateMailProvider();

				if ( 'send_template_email' === appAction ) {
					// Hide the email body field if the action is send_template_email.
					this.$el.find( '.email-body' ).hide();

					// Show the email template field if the action is send_template_email.
					this.$el.find( '.email-template-variables' ).show();
				} else {
					// Show the email body field if the action is not send_template_email.
					this.$el.find( '.email-body' ).show();

					// Hide the email template field if the action is not send_template_email.
					this.$el.find( '.email-template-variables' ).hide();
				}

				this.$el.find( 'select' ).selectpicker();

				setTimeout( function() {
					if ( thisEl.$el.find( '.flowmattic-content-editor' ).length ) {
						var editorTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-text-editor-template' ).html() ),
							editorWrapper = thisEl.$el.find( '.flowmattic-content-editor' ),
							actionAppArgs = thisEl.model.get( 'actionAppArgs' ),
							content = ( 'undefined' !== typeof actionAppArgs ) ? actionAppArgs.email_body : '';

						// Initialize the editor.
						editorWrapper.html( editorTemplate( { post_content: content } ) );

						editorWrapper.find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

						// Handle the editor commands.
						window.actionView.handleEditorCommands( thisEl );
					}
				}, 500 );

				return this;
			},

			updateMailProvider: function( event ) {
				var provider = jQuery( this.$el ).find( '#fm-select-email-provider' ).val(),
					actionTemplate;

				this.model.set( 'email_provider', provider );

				if ( 'smtp' !== provider ) {
					jQuery( this.$el ).find( '.flowmattic-email-smtp-fields' ).html( '' );
				} else {
					actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-application-email-smtp-data-template' ).html() );
					jQuery( this.$el ).find( '.flowmattic-email-smtp-fields' ).html( actionTemplate( this.model.toJSON() ) );
				}
			},

			updateMailTemplateVariables: function( event ) {
				var selectedTemplate = jQuery( this.$el ).find( '#fm-select-email-template' ).val(),
					template = jQuery( this.$el ).find( 'option[value="' + selectedTemplate + '"]' ).data( 'dynamic-data' ),
					actionTemplate,
					actionAppArgs = this.model.get( 'actionAppArgs' );

				this.model.set( 'email_template', selectedTemplate );

				if ( 'undefined' === typeof template || '' === template ) {
					jQuery( this.$el ).find( '.email-template-dynamic-data' ).html( '' );
					return;
				}

				actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-email-template-dynamic-data-template' ).html() );
				jQuery( this.$el ).find( '.email-template-dynamic-data' ).html( actionTemplate( { data: template, actionAppArgs: actionAppArgs } ) );
			}
		} );
	} );
}( jQuery ) );
