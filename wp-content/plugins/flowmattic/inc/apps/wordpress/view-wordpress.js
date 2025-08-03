/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger WordPress View.
		FlowMatticWorkflow.WordpressView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-wordpress-data-template' ).html() ),

			events: {
				'click .wordpress-data-toggle': 'toggleResponseData',
				'change [data-name*=""]': 'updateFieldValues',
				'click .wp-generate-pw': 'generatePassword',
				'click .flowmattic-wp-database-capture-button': 'fetchDatabaseRecord',
				'change [name="post_type"]': 'setCustomTaxonomies',
			},

			initialize: function() {
				// Listen to form submission.
				this.listenTo( FlowMatticWorkflowEvents, 'formSubmissionReceived', this.updateFormSubmissionData );

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );
			},

			render: function() {
				var thisEl = this,
					applicationSettings = {},
					submissionData = {},
					dbTriggers = {},
					templateTriggers = {},
					appActionTemplate = '',
					appAction = this.model.get( 'action' );

				if ( 'action' === this.model.get( 'type' ) && '' !== appAction ) {
					appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-wordpress-' + appAction + '-action-template' ).html() );

					var actionAppArgs = this.model.get( 'actionAppArgs' );

					if ( 'undefined' !== typeof actionAppArgs ) {
						this.model.set( 'actionAppArgs', actionAppArgs );
					} else {
						// Merge the actionAppArgs with the model.
						var modelJSON = this.model.toJSON();
						actionAppArgs = jQuery.extend( modelJSON, actionAppArgs );

						this.model.set( 'actionAppArgs', actionAppArgs );
					}

					this.$el.html( appActionTemplate( this.model.toJSON() ) );

					// If the action is new post or update post, show the post taxonomies.
					if ( 'new_post' === appAction || 'update_post' === appAction ) {
						thisEl.setCustomTaxonomies();
					}
				} else {
					this.$el.html( this.template( this.model.toJSON() ) );

					// Add certain triggers to the database triggers list.
					dbTriggers = [
						'new_post',
						'new_page',
						'new_user',
						'new_media',
						'post_updated',
						'page_updated',
						'updated_user_profile'
					];

					// If appAction is available in the dbTriggers, show the fetch button.
					if ( -1 !== jQuery.inArray( appAction, dbTriggers ) ) {
						this.showFetchButton();
					} else {
						this.hideFetchButton();
					}

					// Add certain triggers to the template triggers list.
					templateTriggers = [
						'page_view',
						'updated_profile_field',
						'updated_post_meta_field',
						'user_role_added',
						'user_role_removed',
						'user_role_changed',
						'user_role_from_specific_to_set'
					];

					if ( -1 !== jQuery.inArray( appAction, templateTriggers ) ) {
						if ( 'user_role_added' === appAction || 'user_role_removed' === appAction || 'user_role_changed' === appAction ) {
							appAction = 'user_role_added';
						}

						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-wordpress-' + appAction + '-trigger-template' ).html() );
						jQuery( this.$el ).find( '.flowmattic-wordpress-trigger-data' ).html( appActionTemplate( this.model.toJSON() ) );
					} else {
						jQuery( this.$el ).find( '.flowmattic-wordpress-trigger-data' ).remove();
					}

					if ( 'undefined' !== typeof this.model.get( 'applicationSettings' ) ) {
						applicationSettings        = this.model.get( 'applicationSettings' ).webhook_capture;
						submissionData.captureData = applicationSettings;
						this.updateFormSubmissionData( submissionData );
					}
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				setTimeout( function() {
					if ( thisEl.$el.find( '.flowmattic-content-editor' ).length ) {
						var editorTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-text-editor-template' ).html() ),
							editorWrapper = thisEl.$el.find( '.flowmattic-content-editor' );

						// Initialize the editor.
						editorWrapper.html( editorTemplate( thisEl.model.toJSON() ) );

						editorWrapper.find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

						// Handle the editor commands.
						window.actionView.handleEditorCommands( thisEl );
					}
				}, 500 );

				return this;
			},

			showFetchButton: function() {
				jQuery( this.$el ).find( '.flowmattic-wp-database-capture-button' ).removeClass( 'd-none' );
			},

			hideFetchButton: function() {
				jQuery( this.$el ).find( '.flowmattic-wp-database-capture-button' ).addClass( 'd-none' );
			},

			updateFieldValues: function( event ) {
				var wpField = jQuery( event.target ),
					name = wpField.attr( 'data-field' ),
					value = wpField.val();

				if ( wpField.is( ':checkbox' ) ) {
					value = wpField.is( ':checked' ) ? 'Yes' : 'No';
				}

				if ( 'auto_password_generation' === name ) {
					jQuery( this.$el ).find( '.wp-custom-password' ).hide();

					if ( 'Yes' !== value ) {
						jQuery( this.$el ).find( '.wp-custom-password' ).show();
					}
				}

				if ( 'post_type' === name ) {
					jQuery( this.$el ).find( '.fm-post-taxonomies' ).hide();

					if ( 'post' === value ) {
						jQuery( this.$el ).find( '.fm-post-taxonomies' ).show();
					}
				}

				if ( 'object' === typeof value && -1 !== jQuery.inArray( 'fm-reset', value ) ) {
					wpField.selectpicker( 'val', '' );
					wpField.selectpicker( 'refresh' );
					wpField.selectpicker( 'hide' );
				} else if ( 'fm-reset' === value ) {
					wpField.selectpicker( 'val', '' );
					wpField.selectpicker( 'refresh' );
				}

				this.model.set( name, value );

				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', name, value, this );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			toggleResponseData: function() {
				var toggleLink = jQuery( this.$el ).find( '.wordpress-data-toggle' ),
					toggleDataWrap = toggleLink.next( '.wordpress-response-body' );

				toggleLink.toggleClass( 'toggle' );
				toggleDataWrap.toggle( 'slideTop' );
			},

			updateFormSubmissionData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-wordpress-response-template' ).html() );

				this.$el.find( '.fm-wordpress-capture-data' ).html( responseTemplate( response ) );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-wordpress-form-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( 'post' === this.model.get( 'post_type' ) ) {
					jQuery( this.$el ).find( '.fm-post-taxonomies' ).show();
				} else {
					jQuery( this.$el ).find( '.fm-post-taxonomies' ).hide();
				}

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-wordpress-form-data' ).show();
				}
			},

			triggerParentDataUpdate: function() {
				var formSubmission = this.model.get( 'capturedData' );

				setTimeout( function() {
					FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdated', formSubmission );
				}, 200 );
			},

			generatePassword: function() {
				var thisEl = this;

				Backbone.ajax( {
					url: ajaxurl,
					data: { action: 'generate-password' },
					type: 'POST',
					success: function( response ) {
						jQuery( thisEl.$el ).find(  '.wordpress-password' ).val( response.data ).trigger( 'change' );
					}
				} );
			},

			setCustomTaxonomies: function() {
				var thisEl = this,
				    postType = jQuery( thisEl.$el ).find( '[name="post_type"]' ).find( ':selected' ).val(),
					taxonomyFieldTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-wordpress-taxonomy-field-template' ).html() );

				var postTaxonomies = window.flowmattic_taxonomies[ postType ];

				if ( 'undefined' !== typeof postTaxonomies ) {
					jQuery( this.$el ).find( '.custom-taxonomy-fields' ).html( taxonomyFieldTemplate( { taxonomies: postTaxonomies, actionAppArgs: thisEl.model.toJSON() } ) );
				} else {
					jQuery( this.$el ).find( '.custom-taxonomy-fields' ).html( 'No taxonomies found for the selected post type.' );
				}
			},

			fetchDatabaseRecord: function( event ) {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					recordType = '',
					captureResponseBtn = jQuery( '.flowmattic-webhook-capture-button' ),
					captureButton = jQuery( event.target );

				if ( -1 !== appAction.indexOf( 'post' ) ) {
					recordType = 'post';
				}

				if ( -1 !== appAction.indexOf( 'page' ) ) {
					recordType = 'page';
				}

				if ( -1 !== appAction.indexOf( 'user' ) ) {
					recordType = 'user';
				}

				if ( 'new_media' === appAction ) {
					recordType = 'media';
				}

				event.preventDefault();

				captureButton.text( 'Fetching record' ).prepend( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: inline; margin-right: 10px;" width="20px" height="20px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\
					<circle cx="50" cy="50" r="30" stroke="#ffffff" stroke-width="10" fill="none"></circle>\
					<circle cx="50" cy="50" r="30" stroke="#007bff" stroke-width="8" stroke-linecap="round" fill="none">\
					<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>\
					<animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1s" values="5.654866776461627 182.84069243892594;94.2477796076938 94.24777960769377;5.654866776461627 182.84069243892594" keyTimes="0;0.5;1"></animate>\
					</circle></svg>'
				);

				// Disable the capture button.
				captureButton.addClass( 'disabled' );
				captureResponseBtn.addClass( 'disabled' );

				// Reset the previous captured data.
				this.$el.find( '.fm-response-capture-data' ).html('');

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_wp_capture_from_database', recordType: recordType, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							var webhookResponse;
							response = JSON.parse( response );
							webhookResponse = {
								webhook_capture: response
							};
							captureButton.text( 'Fetch From Database' );
							captureResponseBtn.text( 'Re-capture Response' );

							window.webhook_captured = true;
							FlowMatticWorkflowApp.webhookResponse = webhookResponse;
							FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', webhookResponse );
							thisEl.model.set( 'capturedData', webhookResponse );

							FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'capturedData', webhookResponse );

							// Update the parent data.
							thisEl.triggerParentDataUpdate();

							// Autosave response data.
							FlowMatticWorkflowEvents.trigger( 'processAutosave' );

							// Enable the capture button.
							captureButton.removeClass( 'disabled' );
							captureResponseBtn.removeClass( 'disabled' );
						}
					}
				);
			},

			generateDynamicFieldsHTML: function( application, currentInput ) {
				var userRoles = window.FMWPConfig.user_roles,
					user_roles_output = '',
					optionsHTML = '';

				_.each ( userRoles, function( name, role ) {
					user_roles_output += '<option value="' + role + '">' + name + '</option>';;
				} );

				optionsHTML += '<optgroup label="WordPress User Roles" data-max-options="1">' + user_roles_output + '</optgroup>';

				// WordPress User Roles.
				if ( window.isVisualWorkflowBuilder ) {
					if ( application === this.model.get( 'application' ) && currentInput[0].name === 'user_role' && window.dynamicFieldOptionsHTML.indexOf( 'label="WordPress User Roles"' ) === -1 ) {
						basesDropdownHTML = optionsHTML;
						window.dynamicFieldOptionsHTML = basesDropdownHTML + window.dynamicFieldOptionsHTML;
					}
				}
			}
		} );
	} );
}( jQuery ) );
