/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger View.
		FlowMatticWorkflow.TriggerView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-app-template' ).html() ),
			simpleResponseTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-simple-response-template' ).html() ),
			dataTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-trigger-data-template' ).html() ),
			pollingOptions: {},

			events: {
				'click .fm-add-trigger': 'addTrigger',
				'change [name="trigger_connect_id"]': 'handleTriggerAPIChangeEvent',
				'change select.workflow-application-events': 'setApplicationAction',
				'change select.workflow-trigger': 'setWorkflowTrigger',
				'change select.workflow-trigger-events': 'setApplicationTrigger',
				'click .fm-webhook-response': 'toggleWebhookCustomResponse',
				'change [name="workflowEndAction"]': 'setWorkflowEndAction',
				'click .fm-webhook-security': 'toggleWebhookSecurity',
				'click .flowmattic-webhook-capture-button': 'captureWebhook',
				'click .flowmattic-api-poll-button': 'pollAPI',
				'click .webhook-data-toggle': 'toggleResponseData',
				'click .fm-workflow-trigger .fm-workflow-step-rename': 'renameStepTitle',
				'click .flowmattic-trigger-app': 'setTriggerApp',
				'click .fm-workflow-trigger-description': 'displayStepDescription',
				'click .webhook-url input': 'copyWebhookToClipboard',
				'change .form-control': 'updateOptions',
				'change .fm-simple-response': 'enableDisableSimpleResponse',
				'click .polling-input-add-more, .polling-input-add-more a': 'addDynamicInput',
				'click .dynamic-input-remove': 'removeDynamicInput',
				'change .data-parameter-fields input': 'changeDynamicFields',
				'change .data-parameter-fields textarea': 'changeDynamicFields',
				'change #captured-responses-select': 'updateCapturedResponseData'
			},

			initialize: function() {
				var currentTime = Date.now();

				// Set custom ID for this step.
				if ( 'undefined' === typeof this.model.get( 'stepID' ) ) {
					this.model.set( 'stepID', FlowMatticWorkflow.randomString( 5 ) + '-' + currentTime );
				}

				// Remove action hook reference if no plugin actions trigger.
				if ( 'action_trigger' !== this.model.get( 'action' ) ) {
					this.model.set( 'pluginAction', '' );
				}

				// Listen to webhook response.
				this.listenTo( FlowMatticWorkflowEvents, 'webhookResponseReceived', this.updateWebhookResponseData );

				// Listen to trigger app data update.
				this.listenTo( FlowMatticWorkflowEvents, 'triggerAppDataUpdated', this.updateTriggerAppData );

				// Listen to trigger app single attritube update.
				this.listenTo( FlowMatticWorkflowEvents, 'triggerAppDataUpdateSingleAttribute', this.updateTriggerAppDataSingleAttribute );

				// Listen to response capture dropdown update.
				this.listenTo( FlowMatticWorkflowEvents, 'updateCapturedResponsesDropdown', this.updateCapturedResponsesDropdown );

				this.listenTo( FlowMatticWorkflowEvents, 'capturePollingResponse', this.capturePollingResponse );

				// Listen to v2 trigger app data update.
				this.listenTo( FlowMatticWorkflowEvents, 'setWorkflowTriggerv2', this.setWorkflowTriggerv2 );
			},

			capturePollingResponse: function( response ) {
				this.pollingOptions = response.detail;
				var event = new Event( 'click' );
				this.pollAPI( event );
			},

			render: function() {
				var thisEl = this,
					currentStep;

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'undefined' !== typeof this.model.get( 'application' ) ) {
					currentStep = jQuery( this.$el ).find( '.workflow-trigger' );
					this.updateTriggerStepData( this.model.get( 'application' ), currentStep );
				}

				if ( 'undefined' !== typeof this.model.get( 'action' ) ) {
					this.setApplicationTrigger();
				}

				this.updateWebhookResponseData( this.model.toJSON() );

				this.$el.find( 'select' ).selectpicker();
				this.$el.find( '[data-toggle="tooltip"], .webhook-url-wrapper input' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

				this.initializeData();

				if ( 'webhook' === this.model.get( 'application' ) ) {
					jQuery( window ).on( 'load', function() {
						document.getElementById( 'fm-custom-response-text' ).addEventListener( 'input', function() {
							thisEl.saveWebhookCustomResponse();
						}, false );
					} );

					this.toggleWebhookCustomResponse();
					this.toggleWebhookSecurity();
				}

				// Process autosave on any input change.
				jQuery( this.$el ).find( 'input, select, checkbox, radio, textarea' ).on( 'change', function() {
					setTimeout( function() {
						FlowMatticWorkflowEvents.trigger( 'processAutosave' );
					}, 200 );
				} );

				setTimeout( function() {
					// Add the copy icon for webhook url.
					if ( ! jQuery( 'body' ).find( '.webhook-url-wrapper' ).length ) {
						jQuery( thisEl.$el ).find( '.webhook-url input' ).wrap( '<div class="webhook-url-wrapper"></div>' );
						jQuery( thisEl.$el ).find( '.webhook-url input' ).attr( 'title', 'Click to copy' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );
					}
				}, 500 );

				this.delegateEvents();

				return this;
			},

			handleTriggerAPIChangeEvent: function( event ) {
				var connectID = jQuery( event.target ).val();

				// Set the connect id as model attritube.
				this.model.set( 'trigger_connect_id', connectID );

				// Trigger custom event to alert the relative apps.
				FlowMatticWorkflowEvents.trigger( 'triggerAPIChangeEvent', connectID );

				// Trigger it after 500ms again to avoid conflict with selectpicker.
				setTimeout( function() {
					FlowMatticWorkflowEvents.trigger( 'triggerAPIChangeEvent', connectID );
				}, 500 );
			},

			initializeData: function() {
				var fmAuthKey = jQuery( 'body' ).find( '.workflow-input.workflow-auth-key' ).val();

				// Set webhook url.
				this.model.set( 'webhook_url', webhookURL );

				// Set authentication key.
				this.model.set( 'workflow_auth_key', fmAuthKey );
			},

			addTrigger: function( event ) {
				var elementCID,
					action,
					thisEl,
					index;

				thisEl = this;
				action = new FlowMatticWorkflow.Action();

				index = this.collection.indexOf( this.model );

				action.set( 'insertAfter', index );

				setTimeout( function() {
					thisEl.collection.add( [action], {at: index + 1 } );

					// Add the copy icon for webhook url.
					if ( ! jQuery( 'body' ).find( '.webhook-url-wrapper' ).length ) {
						jQuery( this.$el ).find( '.webhook-url input' ).wrap( '<div class="webhook-url-wrapper"></div>' );
						jQuery( this.$el ).find( '.webhook-url input' ).attr( 'title', 'Click to copy' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );
					}
				}, 50 );
			},

			setWorkflowTriggerv2: function( appEvent ) {
				// Reset the action to avoid conflict with action naming.
				this.model.set( 'action', '' )
				this.model.set( 'action', appEvent );

				window.currentTriggerAction = appEvent;
			},

			setWorkflowTrigger: function( event ) {
				var application = jQuery( event.target ).val();

				// Set the selected trigger as model attritube.
				this.model.set( 'application', application );

				// Reset the action to avoid conflict with action naming.
				this.model.set( 'action', '' );

				// Get the application data and update the step.
				this.updateTriggerStepData( application, event.target );

				if ( 'undefined' !== typeof this.model.get( 'applicationSettings' ) ) {
					this.setApplicationTrigger();
				}

				// Add the copy icon for webhook url.
				if ( ! jQuery( 'body' ).find( '.webhook-url-wrapper' ).length ) {
					jQuery( this.$el ).find( '.webhook-url input' ).wrap( '<div class="webhook-url-wrapper"></div>' );
					jQuery( this.$el ).find( '.webhook-url input' ).attr( 'title', 'Click to copy' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setTriggerApp: function( event ) {
				var application = jQuery( event.currentTarget ).attr( 'data-trigger' );

				// Set the selected trigger as model attritube.
				this.model.set( 'application', application );

				// Reset the action to avoid conflict with action naming.
				this.model.set( 'action', '' );

				// Get the application data and update the step.
				this.updateTriggerStepData( application, event.currentTarget );

				if ( 'undefined' !== typeof this.model.get( 'applicationSettings' ) ) {
					this.setApplicationTrigger();
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setApplicationTrigger: function( event ) {
				var appAction = jQuery( this.$el ).find( 'select.workflow-trigger-events' ).val(),
					appActionTitle = jQuery( this.$el ).find( 'select.workflow-trigger-events' ).find( ':selected' ).text(),
					appDataTemplate,
					application = this.model.get( 'application' ),
					appData,
					appView,
					viewSettings,
					usingAPIPolling = false,
					action = new FlowMatticWorkflow.Action();

				if ( 'undefined' === typeof appAction || '' === appAction ) {
					appAction = this.model.get( 'action' );
				}

				if ( 'undefined' !== typeof event ) {
					// Reset the captured data.
					this.model.unset( 'capturedData' );
					this.model.unset( 'captureData' );
					this.model.unset( 'applicationSettings' );

					FlowMatticWorkflowEvents.trigger( 'triggerEventChanged' );
				}

				appData = ( 'undefined' !== typeof triggerApps[ application ] ) ? triggerApps[ application ] : otherTriggerApps[ application ];

				// Set the selected application action as model attritube.
				this.model.set( 'action', appAction );

				if ( 'undefined' === typeof this.model.get( 'stepTitle' ) ) {
					jQuery( 'select.workflow-trigger-events' ).closest( '.fm-workflow-step' ).find( '.fm-application-action' ).text( appActionTitle );
				}

				action.set( 'action', appAction );

				if ( 'undefined' !== typeof this.model.get( 'applicationSettings' ) ) {
					action.set( 'applicationSettings', this.model.get( 'applicationSettings' ) );
				}

				// Set each step options as per database records.
				_.each( this.model.toJSON(), function( value, option ) {
					action.set( option, value );
				} );

				viewSettings = {
					model: action,
					collection: FlowMatticWorkflowSteps
				};

				appView = FlowMatticWorkflow.UCWords( application ).replaceAll( ' ', '_' ) + 'View';

				if ( 'undefined' !== typeof FlowMatticWorkflow[ appView ] ) {
					appView = new FlowMatticWorkflow[ appView ]( viewSettings );

					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-trigger-app-data' ).html( appView.render().el );
				}

				// If custom trigger app, load the webhooks template.
				if ( 'undefined' !== typeof otherTriggerApps[ application ] && otherTriggerApps[ application ].custom_app ) {
					appTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-custom-webhook-app-data-template' ).html() );
					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-trigger-app-data' ).html( appTemplate( this.model.toJSON() ) );
				}

				// Check if the trigger is api_polling.
				if ( 'undefined' !== typeof appAction && '' !== appAction && 'undefined' !== typeof appData.triggers && 'undefined' !== typeof appData.triggers[ appAction ] ) {
					if ( ( 'schedule' !== application && 'plugin_actions' !== application ) && 'undefined' !== typeof appData.triggers[ appAction ].api_polling ) {
						usingAPIPolling = true;
					}
				}

				if ( usingAPIPolling ) {
					this.model.set( 'api_polling', 'Yes' );

					// Set the basic fields for API polling.
					appDataTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-api-polling-basic-fields-template' ).html() );

					// Add the basic fields for API polling.
					if ( ! jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-trigger-app-data, .fm-workflow-action-data' ).find( '.api-polling-basic-fields' ).length ) {
						jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-trigger-app-data, .fm-workflow-action-data' ).prepend( appDataTemplate( this.model.toJSON() ) );
					}

					if ( ! this.model.get( 'isVisualWorkflowBuilder' ) ) {
						// Fix the selectpicker for the new fields.
						this.$el.find( 'select' ).selectpicker();
					} else {
						return appDataTemplate( this.model.toJSON() );
					}
				}

				// Add the copy icon for webhook url.
				if ( ! jQuery( 'body' ).find( '.webhook-url-wrapper' ).length ) {
					jQuery( this.$el ).find( '.webhook-url input' ).wrap( '<div class="webhook-url-wrapper"></div>' );
					jQuery( this.$el ).find( '.webhook-url input' ).attr( 'title', 'Click to copy' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );
				}

				// Set option for simple response where webhooks are used.
				if ( jQuery( this.$el ).find( '.fm-webhook-capture-button' ).length && 'webhook' !== this.model.get( 'application' ) ) {
					jQuery( this.simpleResponseTemplate( this.model.toJSON() ) ).insertBefore( jQuery( this.$el ).find( '.fm-webhook-capture-button' ) );
				}

				FlowMatticWorkflowEvents.trigger( 'appTriggerChanged', appAction );
				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

			},

			setWorkflowEndAction: function( event ) {
				var endAction = jQuery( event.target ).val();

				// Set the selected trigger as model attritube.
				this.model.set( 'workflowEndAction', endAction );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			toggleWebhookCustomResponse: function() {
				var webhookResponse = ( jQuery( this.$el ).find( '[name="webhook_response"]' ).is( ':checked' ) ) ? 'Yes' : 'No',
					webhookResponseWrap = jQuery( this.$el ).find( '.webhook-response-wrap' );

				if ( 'Yes' === webhookResponse ) {
					webhookResponseWrap.removeClass( 'hidden' );
				} else {
					webhookResponseWrap.addClass( 'hidden' );
				}

				// Set webhook responce option.
				this.model.set( 'webhook_response', webhookResponse );
			},

			saveWebhookCustomResponse: function() {
				var webhookCustomResponse = jQuery( this.$el ).find( '.fm-custom-response' ).text();

				// Set webhook responce text.
				this.model.set( 'webhook_custom_responce', webhookCustomResponse );
			},

			toggleWebhookSecurity: function() {
				var webhookSecurity = ( jQuery( this.$el ).find( '.fm-webhook-security' ).is( ':checked' ) ) ? 'Yes' : 'No',
					webhookSecurityWrap = jQuery( this.$el ).find( '.webhook-security-wrap' );

				if ( 'Yes' === webhookSecurity ) {
					webhookSecurityWrap.removeClass( 'hidden' );
				} else {
					webhookSecurityWrap.addClass( 'hidden' );
				}

				// Set webhook security option.
				this.model.set( 'webhook_security', webhookSecurity );
			},

			updateTriggerStepData: function( application, el ) {
				var thisEl = this,
					data,
					currentStep = jQuery( el ).closest( '.fm-workflow-step' ),
					action = '',
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					applicationData = {},
					webhookResponse = {},
					appData,
					appTemplate = '',
					stepTitle;

				if ( this.model.get( 'action' ) ) {
					action = this.model.get( 'action' );
				}

				if ( jQuery( '#flowmattic-application-' + application + '-template' ).length ) {
					appTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-application-' + application + '-template' ).html() );
				}

				appData = ( 'undefined' !== typeof triggerApps[ application ] ) ? triggerApps[ application ] : otherTriggerApps[ application ];

				if ( 'undefined' === typeof appData ) {
					return false;
				}

				// Check if step title is changed.
				stepTitle = appData.name + ':';
				if ( 'undefined' !== typeof this.model.get( 'stepTitle' ) ) {
					stepTitle = this.model.get( 'stepTitle' );
					applicationData.customStepTitle = true;
				}

				applicationData.application         = application;
				applicationData.applicationName     = stepTitle;
				applicationData.applicationTriggers = ( 'undefined' !== typeof appData.triggers ) ? appData.triggers : appData.actions;
				applicationData.triggerAction       = action;
				applicationData.webhookURL          = webhookURL;

				if ( 'webhook' === this.model.get( 'application' ) ) {
					if ( 'undefined' !== typeof this.model.get( 'webhook_security' ) ) {
						applicationData.webhook_security = this.model.get( 'webhook_security' );
					}

					if ( 'undefined' !== typeof this.model.get( 'simple_response' ) ) {
						applicationData.simple_response = this.model.get( 'simple_response' );
					}

					if ( 'undefined' !== typeof this.model.get( 'webhook_custom_responce' ) ) {
						applicationData.webhook_response = this.model.get( 'webhook_response' );
						applicationData.webhook_custom_responce = this.model.get( 'webhook_custom_responce' );
					}

					if ( 'undefined' !== typeof this.model.get( 'workflowEndAction' ) ) {
						applicationData.workflowEndAction = this.model.get( 'workflowEndAction' );
					}
				}

				currentStep.html( thisEl.dataTemplate( applicationData ) ).removeClass( 'step-new' );

				if ( '' !== appTemplate ) {
					currentStep.find( '.fm-workflow-step-body' ).html( appTemplate( applicationData ) ).addClass( 'fm-flex-column' );
				}

				currentStep.find( 'select' ).selectpicker();

				if ( window.captureData ) {
					webhookResponse.webhook_capture = window.captureData;
					thisEl.model.set( 'capturedData', window.captureData );
					FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', webhookResponse );
				}
			},

			captureWebhook: function( event ) {
				var thisEl = this,
					webhookURL,
					captureButton = jQuery( event.target ),
					webhookCapture = setInterval( captureWebhookData, 2000 ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					fmAuthKey = ( jQuery( this.$el ).find( '.fm-webhook-security' ).is( ':checked' ) ) ? jQuery( 'body' ).find( '.workflow-input.workflow-auth-key' ).val() : '',
					webhookCustomResponse = ( jQuery( this.$el ).find( '.fm-webhook-response' ).is( ':checked' ) ) ? jQuery( 'body' ).find( '.fm-custom-response' ).text() : '',
					application = this.model.get( 'application' ),
					appAction = this.model.get( 'action' ),
					captureResponse = 1;

				event.preventDefault();

				// Disable the capture button.
				captureButton.addClass( 'disabled' );

				// Set request initializing text.
				captureButton.text( 'Initializing request...' );

				// Reset the previous captured data.
				this.$el.find( '.fm-webhook-capture-data' ).html('');

				function captureWebhookData() {

					captureButton.text( 'Waiting for response' ).prepend( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="32px" height="32px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\
						<circle cx="50" cy="50" r="30" stroke="#ffffff" stroke-width="10" fill="none"></circle>\
						<circle cx="50" cy="50" r="30" stroke="#007bff" stroke-width="8" stroke-linecap="round" fill="none">\
	  					<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>\
	  					<animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1s" values="5.654866776461627 182.84069243892594;94.2477796076938 94.24777960769377;5.654866776461627 182.84069243892594" keyTimes="0;0.5;1"></animate>\
						</circle></svg>'
					);

					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_capture_data', application: application, appAction: appAction, 'webhook-id': workflowId, workflow_auth_key: fmAuthKey, webhook_response: webhookCustomResponse, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								var capturedResponses = window.capturedResponses,
									responseData = {};
								response = JSON.parse( response );
								if ( 'pending' !== response.status ) {
									clearInterval( webhookCapture );
									window.webhook_captured = true;
									FlowMatticWorkflowApp.webhookResponse = response;
									captureButton.text( 'Re-capture Response' );
									FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', response );
									thisEl.model.set( 'capturedData', response );

									// Enable the capture button.
									captureButton.removeClass( 'disabled' );

									// Set the captured response empty array if it is empty.
									if ( _.isEmpty( capturedResponses ) ) {
										capturedResponses = [];
									}

									responseData.letter          = thisEl.getLetterForNumber( capturedResponses.length );
									responseData.captured_at     = response.captured_at;
									responseData.webhook_capture = response.webhook_capture;
									capturedResponses.push( responseData );
									
									// Update the captured responses array.
									window.capturedResponses = capturedResponses;

									// Update the selected response letter.
									window.selectedResponse = responseData.letter;

									// Trigger an event to update the captured responses dropdown.
									FlowMatticWorkflowEvents.trigger( 'updateCapturedResponsesDropdown', capturedResponses );

									// Autosave response data.
									FlowMatticWorkflowEvents.trigger( 'processAutosave' );
								}

								captureResponse = '0';
							},
							error: function( error ) {
								clearInterval( webhookCapture );
								captureButton.text( 'Re-capture Response' );
								captureButton.removeClass( 'disabled' );
							}
						}
					);
				}
			},

			updateCapturedResponsesDropdown: function( responses ) {
				var capturedResponses = responses,
					capturedResponsesDropdown = jQuery( this.$el ).find( '#captured-responses-select' ),
					capturedResponsesHTML = '';

				capturedResponsesDropdown.html( '' );

				if ( 'undefined' !== typeof capturedResponses ) {
					_.each( capturedResponses, function( response ) {
						capturedResponsesHTML += '<option value="' + response.letter + '" data-subtext="Captured at: ' + response.captured_at + '">Response ' + response.letter + '</option>';
					} );
				}

				setTimeout( function() {
					capturedResponsesDropdown.html( capturedResponsesHTML );
					capturedResponsesDropdown.selectpicker( 'refresh' );
					capturedResponsesDropdown.selectpicker( 'val', capturedResponses[ capturedResponses.length - 1 ].letter );

					setTimeout( function() {
						// Autosave response data.
						FlowMatticWorkflowEvents.trigger( 'processAutosave' );
					}, 500 );
				}, 500 );

				// Remove the display none class from the dropdown.
				capturedResponsesDropdown.closest( '.workflow-responses-dropdown' ).removeClass( 'd-none' );
			},

			updateCapturedResponseData: function( event ) {
				var capturedResponses = window.capturedResponses,
					selectedResponse = jQuery( event.target ).val(),
					responseData = {};

				if ( '' !== capturedResponses ) {
					_.each( capturedResponses, function( response ) {
						if ( response.letter === selectedResponse ) {
							responseData = response;
							window.selectedResponse = selectedResponse;
						}
					} );

					window.webhook_captured = true;
					this.model.set( 'capturedData', responseData );
					this.updateWebhookResponseData( responseData );
				}
			},

			pollAPI: function( event ) {
				var thisEl = this,
					pollButton = jQuery( event.target ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					application = this.model.get( 'application' ),
					appAction = this.model.get( 'action' ),
					pollButtonTitle = pollButton.text(),
					options = {};

				_.each( jQuery( this.$el ).find( '.fm-workflow-trigger .form-control' ), function( el ) {
					if ( 'undefined' !== typeof jQuery( el ).attr( 'name' ) ) {
						options[ jQuery( el ).attr( 'name' ) ] = jQuery( el ).val();
					}
				} );

				if ( this.pollingOptions ) {
					options = this.pollingOptions;
				}

				event.preventDefault();

				// Disable the poll button.
				pollButton.addClass( 'disabled' );

				// Set request initializing text.
				pollButton.text( 'Fetching response..' ).prepend( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="32px" height="32px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\
					<circle cx="50" cy="50" r="30" stroke="#ffffff" stroke-width="10" fill="none"></circle>\
					<circle cx="50" cy="50" r="30" stroke="#007bff" stroke-width="8" stroke-linecap="round" fill="none">\
					<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>\
					<animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1s" values="5.654866776461627 182.84069243892594;94.2477796076938 94.24777960769377;5.654866776461627 182.84069243892594" keyTimes="0;0.5;1"></animate>\
					</circle></svg>'
				);

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_poll_api', options: options, application: application, appAction: appAction, 'workflow_id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							FlowMatticWorkflowApp.capturedData = response;
							pollButton.text( pollButtonTitle );

							window.webhook_captured = true;
							FlowMatticWorkflowApp.webhookResponse = response;
							FlowMatticWorkflowEvents.trigger( 'webhookResponseReceived', response );
							thisEl.model.set( 'capturedData', response );

							// Enable the poll button.
							pollButton.removeClass( 'disabled' );

							// Autosave response data.
							FlowMatticWorkflowEvents.trigger( 'processAutosave' );

							// Fire custom event to alert the relative apps.
							var customEvent = new CustomEvent( 'pollingResponseReceived', { detail: response } );
							document.dispatchEvent( customEvent );
						},
						error: function( error ) {
							pollButton.text( pollButtonTitle );
							pollButton.removeClass( 'disabled' );
						},
						complete: function() {
							pollButton.text( pollButtonTitle );
							pollButton.removeClass( 'disabled' );
						}
					}
				);
			},


			toggleResponseData: function( event ) {
				var toggleLink = jQuery( this.$el ).find( '.webhook-data-toggle' ),
					toggleDataWrap = toggleLink.next( '.webhook-response-body' );

				if ( window.webhook_captured || event ) {
					toggleLink.toggleClass( 'toggle' );
					toggleDataWrap.toggle( 'slideTop' );
				}
			},

			updateWebhookResponseData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-webhook-response-template' ).html() ),
					captureData = {};

				if ( 'undefined' !== typeof response.capturedData ) {
					if ( 'undefined' !== typeof response.capturedData.webhook_capture ) {
						captureData.webhook_capture = response.capturedData.webhook_capture;
					} else {
						captureData.webhook_capture = response.capturedData;
					}

					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( captureData ) );
				} else if ( 'undefined' !== typeof response.webhook_capture ) {
					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( response ) );
				}

				this.toggleResponseData();
			},

			updateTriggerAppData: function( appData ) {
				this.model.set( 'applicationSettings', appData );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateTriggerAppDataSingleAttribute: function( attr, value ) {
				// Trigger custom event to alert the relative apps.
				var customEvent = new CustomEvent( 'triggerAppDataUpdateSingleAttribute', { detail: { attr: attr, value: value } } );
				document.dispatchEvent( customEvent );

				this.model.set( attr, value );
			},

			renameStepTitle: function( event ) {
				var thisEl = this,
					currentStepTitle = jQuery( event.currentTarget ).closest( '.fm-workflow-step-header' ).find( '.workflow-trigger-title' ).text().replace( /\s\s+/g, ' ' ).trim();

				event.preventDefault();

				const swalWithBootstrapButtons = window.Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-primary shadow-none me-xxl-3',
						cancelButton: 'btn btn-danger shadow-none'
					},
					buttonsStyling: false
				} );

				swalWithBootstrapButtons.fire( {
					title: 'Rename Step',
					input: 'textarea',
					inputLabel: 'Enter Step Title',
					inputValue: currentStepTitle,
					inputPlaceholder: 'Enter step title',
					inputAttributes: {
						autocapitalize: 'on',
						autocorrect: 'off'
					},
					preConfirm: ( stepTitle ) => {
						if ( stepTitle ) {
							thisEl.model.set( 'stepTitle', `${stepTitle}` );
							jQuery( event.currentTarget ).closest( '.fm-workflow-step-header' ).find( '.fm-application-title' ).text( `${stepTitle}` );
							jQuery( event.currentTarget ).closest( '.fm-workflow-step-header' ).find( '.fm-application-action' ).html( '' );
							FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
						}
					}
				} );
			},

			displayStepDescription: function( event ) {
				var thisEl = this,
					stepDescription = this.model.get( 'stepDescriptionText' ),
					descriptionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-trigger-description-template' ).html() );

				event.preventDefault();

				// Delete the existing modal instance.
				jQuery( '#triggerStepDescription' ).modal( 'hide' );

				// Create new modal instance.
				jQuery( 'body' ).find( '.fm-step-description-wrapper' ).html( descriptionTemplate( { stepDescriptionText: stepDescription } ) );

				// Show the modal.
				jQuery( '#triggerStepDescription' ).modal( 'show' );

				// When modal is closed, remove the template.
				jQuery( '#triggerStepDescription' ).on( 'hide.bs.modal', function () {
					// Delete the existing modal instance.
					jQuery( 'body' ).find( '.fm-step-description-wrapper' ).html( '' );
					jQuery( 'body' ).find( '.modal-backdrop' ).remove();
				} );

				// On save button click, save the description text.
				jQuery( 'body' ).find( '.btn-save-step-description' ).on( 'click', function() {
					thisEl.saveDescriptionText();
				} );
			},

			saveDescriptionText: function() {
				var descriptionText = jQuery( 'body' ).find( '#fm-trigger-description-text' ).text();

				// Save the description text.
				this.model.set( 'stepDescriptionText', descriptionText );

				// Hide the modal.
				jQuery( '#triggerStepDescription' ).modal( 'hide' );
				jQuery( 'body' ).find( '.modal-backdrop' ).remove();

				setTimeout( function() {
					FlowMatticWorkflowEvents.trigger( 'processAutosave' );
				}, 200 );
			},

			copyWebhookToClipboard: function( event ) {
				/* Get the text field */
				var sampleTextarea = document.createElement("textarea"),
					value = jQuery( event.target ).val(),
					type = 'Webhook URL';

				const Toast = window.Swal.mixin( {
					toast: true,
					position: 'bottom-end',
					showConfirmButton: false,
					timer: 1500,
					timerProgressBar: true,
					didOpen: (toast) => {
					  	toast.addEventListener('mouseenter', window.Swal.stopTimer)
						toast.addEventListener('mouseleave', window.Swal.resumeTimer)
					}
			  	} );

				if ( 'mailhook' === jQuery( event.target ).attr( 'data-type' ) ) {
					type = 'Email';
				}

			    document.body.appendChild( sampleTextarea );
			    sampleTextarea.value = value; // Save webhook url to it.
			    sampleTextarea.select(); // Select textarea.
			    document.execCommand( 'copy' );
			    document.body.removeChild( sampleTextarea );
				navigator.clipboard.writeText( value );

				Toast.fire( {
					icon: 'success',
					title: type + ' Copied to Clipboard'
				} );
			},

			updateOptions: function( event ) {
				var elementName = jQuery( event.target ).attr( 'name' ),
					elementValue = jQuery( event.target ).val();

				if ( 'trigger' === this.model.get( 'type' ) ) {
					this.model.set( elementName, elementValue );

					// Set parent model attribute.
					FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', elementName, elementValue, this );
				}
			},

			enableDisableSimpleResponse: function ( event ) {
				var input = jQuery( event.target ),
					value;

				if ( jQuery( input ).is( ':checked' ) ) {
					value = 'Yes';
				} else {
					value = 'No';
				}

				// Set the sample response model attritube.
				this.model.set( 'simple_response', value );

				FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'simple_response', value );
				FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'simple_reponse', value );
			},

			addDynamicInput: function( event ) {
				var addMoreButton = jQuery( event.target ),
					dynamicInputWrap = '';

				// Close existing select dropdowns if any.
				jQuery( '#fm-dynamic-select' ).selectpicker( 'destroy' ).remove();

				dynamicInputWrap = addMoreButton.parent( '.polling-input-add-more' ).prev( '.fm-dynamic-input-wrap' ).clone();

				dynamicInputWrap.find( 'input' ).val( '' ).bind( 'change' );
				dynamicInputWrap.find( 'textarea' ).val( '' ).bind( 'change' );
				addMoreButton.parent( '.polling-input-add-more' ).before( dynamicInputWrap );
			},

			removeDynamicInput: function( event ) {
				var removeButton = jQuery( event.target ),
					sibling = removeButton.closest( '.fm-dynamic-input-wrap' ).siblings( '.fm-dynamic-input-wrap' );

				if ( 0 !== removeButton.closest( '.fm-dynamic-input-wrap' ).siblings( '.fm-dynamic-input-wrap' ).length ) {
					removeButton.closest( '.fm-dynamic-input-wrap' ).remove();
				} else { 
					// Reset the input and textarea value.
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'input' ).val( '' ).bind( 'change' );
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'textarea' ).val( '' ).bind( 'change' );
				}

				event.target = sibling;
				this.changeDynamicFields( event );
			},

			changeDynamicFields: function( event ) {
				var dynamicField = {},
					dynamicFieldKeys,
					dynamicFieldValues,
					dynamicFieldName = jQuery( event.target ).parents( '.data-parameter-fields' ).attr( 'data-field-name' );

				dynamicFieldKeys = jQuery( event.target ).parents( '.data-parameter-fields' ).find( 'input[name="dynamic-field-key[]"]' ).map( function() {
					return jQuery( this ).val();
				} ).get();

				dynamicFieldValues = jQuery( event.target ).parents( '.data-parameter-fields' ).find( '[name="dynamic-field-value[]"]' ).map( function() {
					return jQuery( this ).val();
				} ).get();

				// If not empty, set the dynamic field as model attritube.
				if ( ! _.isEmpty( dynamicFieldKeys ) ) {
					_.each( dynamicFieldKeys, function( parameterKey, index ) {
						parameterKey = ( '' === parameterKey ) ? index : parameterKey;
						dynamicField[ parameterKey ] = dynamicFieldValues[ index ];
					} );

					// Set the dynamic field as model attritube.
					this.model.set( dynamicFieldName, dynamicField );

					// Trigger custom event to alert the relative apps.
					var customEvent = new CustomEvent( 'triggerAppDataUpdateSingleAttribute', { detail: { attr: dynamicFieldName, value: dynamicField } } );
					document.dispatchEvent( customEvent );
				} else {
					this.model.set( dynamicFieldName, {} );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

				// Autosave workflow.
				FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
			},

			getLetterForNumber: function( number ) {
				var order_A = 'A'.charCodeAt( 0 ),
					order_Z = 'Z'.charCodeAt( 0 ),
					length  = order_Z - order_A + 1,
					string = '';

				while( number >= 0 ) {
					string = String.fromCharCode( number % length  + order_A ) + string;
					number = Math.floor( number / length ) - 1;
				}

				return string;
			}
		} );
	} );
}( jQuery ) );
