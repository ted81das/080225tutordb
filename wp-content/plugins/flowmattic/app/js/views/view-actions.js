/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Action View.
		FlowMatticWorkflow.ActionView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-action-template' ).html() ),
			dataTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-action-data-template' ).html() ),
			customAppTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-custom-app-action-data-template' ).html() ),
			className: 'flowmattic-action-step',

			events: {
				'click .fm-add-action': 'addNewAction',
				'change select.workflow-application': 'setWorkflowApplication',
				'change select.workflow-application-events': 'setApplicationAction',
				'change select.workflow-api-authentication-type': 'changeAuthenticationType',
				'change select.workflow-api-connect': 'changeConnectID',
				'change select.workflow-api-content-type': 'changeContentType',
				'change .api-url textarea': 'changeAPIEndpoint',
				'change textarea.fm-custom-json': 'changeCustomJSON',
				'change .fm-api-authentication input': 'changeAuthenticationValues',
				'change .fm-api-authentication textarea': 'changeAuthenticationValues',
				'click .flowmattic-save-test-action-button': 'saveTestAction',
				'change input[name="add-headers"]': 'changeSetHeaders',
				'change input[name="add-parameters"]': 'changeSetParameters',
				'change .data-parameters input': 'updateAPIParametersField',
				'change .data-dynamic-fields input': 'changeDynamicFields',
				'change .data-dynamic-fields textarea': 'changeDynamicFields',
				'click .api-response-toggle': 'toggleAPIResponseData',
				'click .fm-response-data-toggle': 'toggleResponseData',
				'click .dynamic-input-add-more, .dynamic-input-add-more a': 'addDynamicInput',
				'click .dynamic-input-remove': 'removeDynamicInput',
				'click .dynamic-field-button': 'generateDynamicFieldsPopup',
				'click .dynamic-field-button': 'openDynamicFieldsPopup',
				'click .dynamic-field-input': 'generateDynamicFieldsPopup',
				'keyup .dynamic-field-input': 'generateDynamicFieldsPopupReact',
				'change input[name="dynamic-field-key[]"]': 'correctFieldKey',
				'click .fm-workflow-step-rename': 'renameStepTitle',
				'click .fm-workflow-step-description': 'displayStepDescription',
				'click .fm-workflow-step-close': 'closeStep',
				'click .flowmattic-action-app': 'setActionApp',
				'change .workflow-api-content-type': 'updateContentTypeFields',

				'change .fm-workflow-action-data .form-control': 'updateActionAppArgs',
				'change .form-control.content-editor-input': 'updateActionAppArgs',

				'change .fm-simple-response': 'enableDisableSimpleResponse',
				'change select[name="response_format"]': 'changeResponseFormat',

				'change .fm-conditional-execution': 'enableDisableConditionalLogic',
				'change .fm-ignore-errors': 'enableDisableIgnoreErrors',
				'click .btn-add-and-condition': 'addAndConditionFields',
				'click .btn-add-or-condition': 'addOrConditionFields',
				'change select[name="filter-field-condition"]': 'checkFilterValueRequirement',
				'change .filter-condition-input': 'buildFilterConditionArray',
				'click .btn-remove-condition': 'removeCondition',
				'change .dynamic-map-toggle': 'toggleDynamicMap',
				'change .map-field': 'updateMapField',

				// Prevent the toggle when clicked on the drag handle.
				'click .drag-action-step': 'dragActionStep',

				// Show context menu.
				'contextmenu .fm-workflow-step': 'showContextMenu'
			},

			initialize: function() {
				var currentTime = Date.now(),
					stepID = FlowMatticWorkflow.randomString( 5 ) + '-' + currentTime;

				window.actionView = this;

				// Set custom ID for this step.
				if ( 'undefined' === typeof this.model.get( 'stepID' ) ) {
					this.model.set( 'stepID', stepID );
				}

				if ( window.isVisualWorkflowBuilder && 'undefined' !== typeof window.currentAppAction ) {
					this.model.set( 'action', window.currentAppAction );
					window.currentAppAction = undefined;
				}

				// Listen to action data patch event.
				this.listenTo( FlowMatticWorkflowEvents, 'actionDataPatched', this.updateActionDataDependencies );

				// Listen to api response.
				this.listenTo( FlowMatticWorkflowEvents, 'apiResponseReceived', this.updateFormSubmissionData );

				// Listen to action app single attritube update.
				this.listenTo( FlowMatticWorkflowEvents, 'actionAppDataUpdateSingleAttribute', this.updateActionAppDataSingleAttribute );

				// Listen to action event response.
				this.listenTo( FlowMatticWorkflowEvents, 'eventResponseReceived', this.updateFormSubmissionData );

				// This action fires just before the test event is submitted to Ajax.
				this.listenTo( FlowMatticWorkflowEvents, 'beforeTestSubmit', this.updateAPIParametersField );

				// Capture the authdata for app and save.
				this.listenTo( FlowMatticWorkflowEvents, 'updateAppAuthData', this.updateAppAuthData );

				// Silently remove the step.
				this.listenTo( FlowMatticWorkflowEvents, 'silentRemoveStep', this.silentRemoveStep );

				// Listen to paste settings.
				this.listenTo( FlowMatticWorkflowEvents, 'pasteSettings', this.render );

				// Listen to clone step.
				this.listenTo( FlowMatticWorkflowEvents, 'cloneStep', this.cloneStep );

				this.listenTo( FlowMatticWorkflowEvents, 'deleteStep', this.deleteStep );

				// Listen to insert step before.
				this.listenTo( FlowMatticWorkflowEvents, 'addStepBefore', this.addStepBefore );

				// Listen to insert step after.
				this.listenTo( FlowMatticWorkflowEvents, 'addStepAfter', this.addStepAfter );

				// Auto save router steps.
				this.listenTo( FlowMatticWorkflowEvents, 'autoSaveRouterSteps', this.autoSaveRouterSteps );
			},

			openDynamicFieldsPopup: function( event ) {
				if ( window.isVisualWorkflowBuilder ) {
					var event = new CustomEvent( 'openDynamicTag', { detail: { target: event.target } } );
					document.dispatchEvent( event );
				} else {
					this.generateDynamicFieldsPopup( event );
				}
			},

			generateDynamicFieldsPopupReact: function( event ) {
				var application = this.model.get( 'application' ),
					currentInput = jQuery( event.target ).parents( '.fm-dynamic-input-field' ).find( '.dynamic-field-input' );

				window.dynamicFieldOptionsHTML = '';
				FlowMatticWorkflowEvents.trigger( 'generateDynamicFieldsHTML', application, currentInput );
				optionsHTML = window.dynamicFieldOptionsHTML;

				return optionsHTML;
			},

			render: function() {
				var currentStep,
					submissionData = {},
					capturedData;

				this.$el.html( this.template( this.model.toJSON() ) );
				this.$el.attr( 'data-cid', this.model.get( 'eid' ) );

				if ( 'undefined' !== typeof this.model.get( 'application' ) ) {
					jQuery( this.$el ).find( '.fm-workflow-step' ).removeClass( 'step-new' ).addClass( 'collapsed' );
					currentStep = jQuery( this.$el ).find( '.workflow-action' );
					this.updateActionStepData( this.model.get( 'application' ) );

					// If router, no action event should be displayed.
					if ( 'router' === this.model.get( 'application' ) ) {
						jQuery( this.$el ).find( '.fm-workflow-actions' ).hide();
						jQuery( this.$el ).find( '.fm-form-capture-button' ).remove();
					}

					if ( ! window.newStepByFrequent ) {
						jQuery( this.$el ).find( '.fm-workflow-step .fm-workflow-step-body' ).toggle( 'slideTop' );
					}

					window.newStepByFrequent = false;
				} else if( 'undefined' !== typeof this.model.get( 'newAction' ) ) {
					jQuery( this.$el ).find( '.fm-workflow-step .fm-workflow-step-body' ).toggle( 'slideTop' );
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.$el.find( 'select' ).selectpicker();
				this.$el.find( '[data-toggle="tooltip"], .dynamic-field-button' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

				return this;
			},

			dragActionStep: function( event ) {
				event.preventDefault();
				return false;
			},

			handleEditorCommands: function( actionView ) {
				var thisView = this,
					thisEl = actionView;

				const toolbar = thisEl.$el.find('.flowmattic-editor-toolbar');
				const editor = thisEl.$el.find('.flowmattic-editor');
				const contentEditor = thisEl.$el.find('.content-editor-input');

				// Add resize icons to images in the editor
				editor[0].querySelectorAll('img').forEach(addResizeIcon);

				function debounce(func, wait) {
					let timeout;
					return function(...args) {
						const context = this;
						clearTimeout(timeout);
						timeout = setTimeout(() => func.apply(context, args), wait);
					};
				}

				function execCmd(command, value) {
					editor.focus();
	
					let range;
					let selection = window.getSelection();
					if (selection.rangeCount > 0) {
						range = selection.getRangeAt(0);
					} else {
						range = document.createRange();
						range.selectNodeContents(editor);
						range.collapse(false);
					}
			
					const span = document.createElement('span');
					if (command === 'bold') {
						span.style.fontWeight = 'bold';
					} else if (command === 'italic') {
						span.style.fontStyle = 'italic';
					}
		
					if (span.style.length > 0) {
						const selectedText = range.extractContents();
						span.appendChild(selectedText);
						range.insertNode(span);
						selection.removeAllRanges();
						range.setStartAfter(span);
						range.setEndAfter(span);
						selection.addRange(range);
					} else {
						document.execCommand(command, false, value);
					}

					syncContent();
				}

				// Function to sync the editor content with the textarea.
				function syncContent() {
					// contentEditor.value = editor.innerHTML;
					let content = jQuery( editor ).html();

					if ( content ) {
						// Replace empty divs with br tag.
						content = content.replace(/<div><\/div>/g, '<br>');

						// Check if the content starts with a <pre> tag.
						if ( content.startsWith( '<pre>' ) ) {
							// If so, remove the <pre> and </pre> tags.
							content = content.substring( 5, content.length - 6 );
						}

						contentEditor.value = content;
						jQuery( contentEditor ).val( content ).trigger( 'change' );
					}
				}

				function addResizeIcon() {
					let img, startX, startY, startWidth, startHeight;

					editor[0].addEventListener('mousedown', (e) => {
						if (e.target.tagName === 'IMG') {
							e.preventDefault(); // Prevent text selection
							img = e.target;
							startX = e.clientX;
							startY = e.clientY;
							startWidth = parseInt(document.defaultView.getComputedStyle(img).width, 10);
							startHeight = parseInt(document.defaultView.getComputedStyle(img).height, 10);
							document.documentElement.addEventListener('mousemove', doResize);
							document.documentElement.addEventListener('mouseup', stopResize);
						}
					});

					function doResize(e) {
						if (!img) return;

						const widthDiff = e.clientX - startX;
						const heightDiff = e.clientY - startY;
						const ratio = startWidth / startHeight;

						let newWidth = startWidth + widthDiff;
						let newHeight = startHeight + heightDiff;

						if (newWidth / newHeight > ratio) {
							newWidth = newHeight * ratio;
						} else {
							newHeight = newWidth / ratio;
						}

						img.style.width = newWidth + 'px';
						img.style.height = newHeight + 'px';
					}

					function stopResize() {
						img = null;
						document.documentElement.removeEventListener('mousemove', doResize);
						document.documentElement.removeEventListener('mouseup', stopResize);
						syncContent();
					}
				}
				
				// Call syncContent initially to set the initial value of the textarea.
				syncContent();

				// Add an input event listener to the editor to update the textarea
				if ( editor ) {
					editor[0].addEventListener('input', debounce(syncContent, 500));
				}

				let codeView = false;
				toolbar.on('click', (event) => {
					const command = event.target.closest('button').getAttribute('data-command');
					const value = event.target.closest('button').getAttribute('data-value') || null;
					const button = event.target.closest('button');
		
					if (!command) return;
		
					let range;
					let selection = window.getSelection();

					if (command === 'createLink') {
						const url = prompt('Enter a URL', 'https://');
						if (url) {
							execCmd(command, url);
						}
					} else if ( 'dynamicTag' === command ) {
						if (selection.rangeCount > 0) {
							range = selection.getRangeAt(0);
						}
						thisView.generateDynamicFieldsPopup( event, editor, range );
					} else if ( 'createImage' === command ) {
						const url = prompt('Enter the image URL:', 'https://');
						if (url) {
							const img = document.createElement('img');
							img.src = url;
							img.alt = 'Inserted image';
							img.width = 100; // Set default width
							img.height = 100; // Set default height
							document.execCommand('insertHTML', false, img.outerHTML);
						}
					} else if ( 'toggleCode' === command ) {
						codeView = !codeView;

						if (codeView) {
							// Switch to code view
							button.innerHTML = '<span class="dashicons dashicons-visibility"></span>';  // change icon to 'preview' mode.
							const html = editor[0].innerHTML;
							const textNode = document.createTextNode(html);
							const pre = document.createElement('pre');
							pre.appendChild(textNode);
							editor[0].innerHTML = '';
							editor[0].appendChild(pre);
						} else {
							// Switch to normal (WYSIWYG) view
							button.innerHTML = '<span class="dashicons dashicons-editor-code"></span>';  // change icon back to 'code' mode.
							const html = editor[0].querySelector('pre').innerText;
							editor[0].innerHTML = html;
						}
					} else {
						execCmd(command, value);
					}

					syncContent();
				});
			},

			addNewAction: function( event ) {
				var elementCID,
					action,
					thisEl,
					index,
					routerActions = [];

				thisEl = this;
				action = new FlowMatticWorkflow.Action();
				action.set( 'view', this );

				index = this.collection.indexOf( this.model );

				if ( window.routerEditorOpen ) {
					var curStepID = jQuery( event.currentTarget ).closest( '.flowmattic-action-step' ).find( '.fm-workflow-step:not(.step-new)' ).attr( 'step-id' );

					if ( 'undefined' === typeof curStepID ) {
						curStepID = jQuery( event.currentTarget ).closest( '.flowmattic-action-step' ).prev( '.flowmattic-action-step:not(:empty)' ).find( '.fm-workflow-step:not(.step-new)' ).attr( 'step-id' );
					}

					var index2 = window.routerEl.collection.length;
					action.set( 'routerRoute', window.routerRoute );

					_.each( window.routerActions, function( routeAction ) {
						var actionModel;

						if ( 'undefined' !== typeof routeAction ) {
							actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

							if ( 'undefined' !== typeof actionModel.get('application') ) {
								routerActions.push( routeAction );

								if ( curStepID === actionModel.get( 'stepID' ) ) {
									routerActions.push( action );
								}
							}
						}
					});

					// Assign the actions to router.
					window.routerActions = routerActions;

					setTimeout( function() {
						window.routerEl.collection.add( [action], {at: index2 + 1 } );
					}, 50 );
				} else {
					setTimeout( function() {
						thisEl.collection.add( [action], {at: index + 1 } );
					}, 50 );
				}
			},

			setWorkflowApplication: function( event ) {
				var thisEl = this,
					application = jQuery( event.target ).val(),
					appData,
					appAction,
					stepID = this.model.get( 'stepID' ),
					$continue = false,
					actionView = FlowMatticWorkflowViewManager.getView( this.model.get('eid') );

				// Set the selected application as model attritube.
				this.model.set( 'application', application );

				appData = ( 'undefined' !== typeof actionApps[ application ] ) ? actionApps[ application ] : otherActionApps[ application ];

				if ( 1 === Object.keys( appData.actions ).length ) {
					// Get the action from apps  that has only one action.
					appAction = Object.keys( appData.actions )[0];

					// Set the selected application action as model attritube.
					this.model.set( 'action', appAction );
				}

				// Update dynamic tags.
				var stepIndex = this.collection.indexOf( this.model );

				if ( ! window.routerEditorOpen ) {
					// Refresh views after this step.
					FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', stepIndex + 1 );
				}

				if ( 'router' === application ) {
					// Check if is router editor.
					if ( window.routerEditorOpen ) {
						this.model.set( 'application', '' );
						const swalWithBootstrapButtons = window.Swal.mixin({
							customClass: {
								confirmButton: 'btn btn-primary shadow-none me-xxl-3',
								cancelButton: 'btn btn-danger shadow-none'
							},
							buttonsStyling: false
						} );

						swalWithBootstrapButtons.fire(
							{
								title: 'Warning!',
								text: 'Router cannot be nested.',
								icon: 'warning',
								showConfirmButton: true
							}
						);
					} else {
						this.model.set( 'action', 'conditionally_run' );
						this.updateActionStepData( application, actionView );
					}
				} else {
					// Get the application data and update the step.
					this.updateActionStepData( application, actionView );
				}

				// If router, no action event should be displayed.
				if ( 'router' === application ) {
					jQuery( this.$el ).find( '.fm-workflow-actions' ).hide();
					jQuery( this.$el ).find( '.fm-form-capture-button' ).remove();
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setActionApp: function( event ) {
				var application = jQuery( event.currentTarget ).attr( 'data-action' );

				// Set flag to avoid step settings collapse.
				window.newStepByFrequent = true;

				// Set the selected application as model attritube.
				this.model.set( 'application', application );

				// Get the application data and update the step.
				this.updateActionStepData( application );

				var stepIndex = this.collection.indexOf( this.model );

				// Refresh views after this step.
				FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', stepIndex );

				if ( window.routerEditorOpen ) {
					// Update step numbers in router.
					FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', this );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setApplicationAction: function( event ) {
				var thisEl = this,
					appAction = jQuery( event.target ).val(),
					appActionTitle = jQuery( event.target ).find( ':selected' ).text(),
					appTemplate = '',
					application = this.model.get( 'application' ),
					actionApp = ( 'undefined' !== typeof actionApps[ application ] ) ? actionApps[ application ] : otherActionApps[ application ],
					applicationData = this.model.toJSON(),
					authSelected = this.model.get( 'authentication' ),
					connectSelected = this.model.get( 'connect_id' ),
					contentTypeSelected = this.model.get( 'content_type' ),
					apiEndpoint = this.model.get( 'api_endpoint' ),
					customJSON = this.model.get( 'custom_json' ),
					conditional_execution = this.model.get( 'conditional_execution' ),
					ignore_errors = this.model.get( 'ignore_errors' ),
					simple_response = this.model.get( 'simple_response' ),
					response_format = this.model.get( 'response_format' ),
					appView,
					viewSettings,
					action = new FlowMatticWorkflow.Action(),
					stepTitle;

				if ( jQuery( '#flowmattic-application-' + application + '-template' ).length ) {
					appTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-application-' + application + '-template' ).html() );
				}

				jQuery( this.$el ).off();

				// Set the selected application action as model attritube.
				this.model.set( 'action', appAction );

				// If custom app, load the custom app template.
				if ( 'undefined' !== typeof otherActionApps[ application ] && otherActionApps[ application ].custom_app ) {
					appTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-custom-app-action-data-template' ).html() );
					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-action-data' ).html( appTemplate( { appData: this.model.toJSON() } ) );

					// Apply selectpicker to select fields.
					this.$el.find( 'select' ).selectpicker();

					var dynamicMapTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-dynamic-map-toggle' ).html() );

					// Append the dynamic map toggle to workflow action data for each select field.
					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-action-data select' ).each( function() {
						var fieldName = jQuery( this ).attr( 'name' );
						// Avoid adding dynamic map toggle to select fields that are not meant to be dynamic.
						var dynamicFields = [ 'authType', 'connectID', 'connect_id', 'contentType', 'api_key', 'api_token', 'auth-api-key', 'auth-api-secret', 'filter-field-condition', 'workflow-api-authentication-type', 'workflow_api_connect', 'workflow-api-content-type' ];
						if ( 'undefined' !== typeof fieldName && ! dynamicFields.includes( fieldName ) ) {
							var thisView = FlowMatticWorkflowViewManager.getView( thisEl.model.get('eid') ),
								dynamicFieldValue = ( 'undefined' !== typeof thisView ) ? thisView.model.get( 'map-field-' + fieldName ) : '',
								checked = ( fieldName === dynamicFieldValue ) ? 'checked' : '',
								actionAppArgs = ( 'undefined' !== typeof thisView ) ? thisView.model.get( 'actionAppArgs' ) : '',
								inputFieldValue = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs[ fieldName ] ) ? actionAppArgs[ fieldName ] : '';

							if ( jQuery( this ).closest( '.form-group' ).find( 'h4' ).parent( 'div' ).find( 'select' ).length ) {
								var selectInputHolder = jQuery( this ).closest( '.form-group' ).find( 'h4' ).parent( 'div' ).has( 'select' );
								jQuery( dynamicMapTemplate( { fieldName: fieldName, fieldValue: inputFieldValue, checked: checked } ) ).insertAfter( selectInputHolder.find( 'h4' ) );
							}

							// Wrap the select field.
							if ( jQuery( this ).closest( '.form-group' ).find( '.btn-refresh' ).length ) {
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).closest( '.d-flex, .fm-form-control' ).addClass( 'w-100' );
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).closest( '.d-flex, .fm-form-control' ).wrap( '<div class="fm-dynamic-map-wrap d-flex w-100"></div>' );
							} else {
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).wrap( '<div class="fm-dynamic-map-wrap d-flex w-100"></div>' );
							}
							
							// Hide the select field.
							if ( '' !== checked ) {
								jQuery( this ).closest( '.fm-select-input-field' ).hide();
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex' ).addClass( 'd-flex-temp d-none' );
							} else {
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-toggle-input' ).hide();
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex-temp d-none' ).addClass( 'd-flex' );
							}

							// Remove the required attribute from the select field.
							jQuery( this ).removeAttr( 'required' );
						}
					} );
				}

				appView = FlowMatticWorkflow.UCWords( application ).replaceAll( ' ', '_' ) + 'View';

				if ( 'undefined' !== typeof FlowMatticWorkflow[ appView ] ) {

					action.set( 'action', appAction );

					if ( 'api' === application ) {
						action.set( 'applicationEvents', actionApp.actions );
						action.set( 'applicationAction', appAction );
						action.set( 'contentType', ( 'undefined' !== typeof contentTypeSelected ) ? contentTypeSelected : 'json' );
						action.set( 'authType', ( 'undefined' !== typeof authSelected ) ? authSelected : 'no' );
						action.set( 'connectID', ( 'undefined' !== typeof connectSelected ) ? connectSelected : '' );
						action.set( 'endpointURL', ( 'undefined' !== typeof apiEndpoint ) ? apiEndpoint : '' );
						action.set( 'customJSON', ( 'undefined' !== typeof customJSON ) ? customJSON : '' );
						action.set( 'conditional_execution', ( 'undefined' !== typeof conditional_execution ) ? conditional_execution : '' );
						action.set( 'ignore_errors', ( 'undefined' !== typeof ignore_errors ) ? ignore_errors : '' );
						action.set( 'simple_response', ( 'undefined' === typeof simple_response ) ? 'Yes' : simple_response );
						action.set( 'response_format', ( 'undefined' === typeof response_format ) ? 'simple' : response_format );
					}

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

					appView = new FlowMatticWorkflow[ appView ]( viewSettings );

					if ( window.routerEditorOpen ) {
						_.each( window.routerActions, function( routeAction, key ) {
							var actionModel;

							if ( 'undefined' !== typeof routeAction ) {
								actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

								if ( appView.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {
									// Set each step options as per database records.
									_.each( actionModel.toJSON(), function( value, option ) {
										appView.model.set( option, value );
									} );

									// Set the selected application action as model attritube.
									appView.model.set( 'action', appAction );

									window.routerActions[ key ] = appView;

									return false;
								}
							}
						} );
					}

					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-action-data' ).html( appView.render().el );

					// Apply selectpicker to select fields.
					this.$el.find( 'select' ).selectpicker();

					var dynamicMapTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-dynamic-map-toggle' ).html() );

					// Append the dynamic map toggle to workflow action data for each select field.
					jQuery( this.$el ).find( '.fm-workflow-step-body .fm-workflow-action-data select' ).each( function() {
						var fieldName = jQuery( this ).attr( 'name' );

						// Avoid adding dynamic map toggle to select fields that are not meant to be dynamic.
						var dynamicFields = [ 'authType', 'connectID', 'connect_id', 'contentType', 'api_key', 'api_token', 'auth-api-key', 'auth-api-secret', 'filter-field-condition', 'workflow-api-authentication-type', 'workflow_api_connect', 'workflow-api-content-type' ];
						if ( 'undefined' !== typeof fieldName && ! dynamicFields.includes( fieldName ) ) {
							var thisView = FlowMatticWorkflowViewManager.getView( thisEl.model.get('eid') ),
								dynamicFieldValue = ( 'undefined' !== typeof thisView ) ? thisView.model.get( 'map-field-' + fieldName ) : '',
								checked = ( fieldName === dynamicFieldValue ) ? 'checked' : '',
								actionAppArgs = ( 'undefined' !== typeof thisView ) ? thisView.model.get( 'actionAppArgs' ) : '',
								inputFieldValue = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs[ fieldName ] ) ? actionAppArgs[ fieldName ] : '';

							if ( jQuery( this ).closest( '.form-group' ).find( 'h4' ).parent( 'div' ).find( 'select' ).length ) {
								var selectInputHolder = jQuery( this ).closest( '.form-group' ).find( 'h4' ).parent( 'div' ).has( 'select' );
								jQuery( dynamicMapTemplate( { fieldName: fieldName, fieldValue: inputFieldValue, checked: checked } ) ).insertAfter( selectInputHolder.find( 'h4' ) );
							}

							// Wrap the select field.
							if ( jQuery( this ).closest( '.form-group' ).find( '.btn-refresh' ).length ) {
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).closest( '.d-flex, .fm-form-control' ).addClass( 'w-100' );
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).closest( '.d-flex, .fm-form-control' ).wrap( '<div class="fm-dynamic-map-wrap d-flex w-100"></div>' );
							} else {
								jQuery( this ).closest( '.form-group' ).find( '.bootstrap-select' ).wrap( '<div class="fm-dynamic-map-wrap d-flex w-100"></div>' );
							}
							
							// Hide the select field.
							if ( '' !== checked ) {
								jQuery( this ).closest( '.fm-select-input-field' ).hide();
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex' ).addClass( 'd-flex-temp d-none' );
							} else {
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-toggle-input' ).hide();
								jQuery( this ).closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex-temp d-none' ).addClass( 'd-flex' );
							}

							// Remove the required attribute from the select field.
							jQuery( this ).removeAttr( 'required' );
						}
					} );
				}

				stepTitle = ( 'undefined' !== typeof applicationData.stepTitle ) ? applicationData.stepTitle : '';

				if ( '' !== stepTitle && appAction === applicationData.action ) {
					jQuery( this.$el ).find( '.fm-workflow-step' ).find( '.fm-application-title' ).text( stepTitle );
					jQuery( this.$el ).find( '.fm-workflow-step' ).find( '.fm-application-action' ).text( '' );
				} else {
					this.model.set( 'stepTitle', '' );
					jQuery( this.$el ).find( '.fm-workflow-step' ).find( '.fm-application-title' ).text( actionApp.name + ':' );
					jQuery( this.$el ).find( '.fm-workflow-step' ).find( '.fm-application-action' ).text( appActionTitle );
				}

				FlowMatticWorkflowEvents.trigger( 'actionDataPatched' );

				this.$el.find( 'select' ).selectpicker();

				this.delegateEvents();

				jQuery( 'body' ).find( '.flowmattic-sidebar-outline.fm-active' ).trigger( 'click' );
				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			changeAuthenticationType: function( event ) {
				var authSelected = jQuery( event.target ).val();

				// Set the selected authentication type as model attritube.
				this.model.set( 'authentication', authSelected );

				FlowMatticWorkflowEvents.trigger( 'actionDataPatched' );
			},

			changeConnectID: function( event ) {
				var connectSelected = jQuery( event.target ).val();

				// Set the selected connect as model attritube.
				this.model.set( 'connect_id', connectSelected );

				FlowMatticWorkflowEvents.trigger( 'actionDataPatched' );
			},

			changeContentType: function( event ) {
				var typeSelected = jQuery( event.target ).val();

				// Set the selected content type as model attritube.
				this.model.set( 'content_type', typeSelected );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			changeAPIEndpoint: function( event ) {
				var apiURL = jQuery( event.target ).val();

				// Set the api url as model attritube.
				this.model.set( 'api_endpoint', apiURL );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			changeCustomJSON: function() {
				var customJSON = jQuery( this.$el ).find( 'textarea.fm-custom-json' ).val();

				// Set the custom json as model attritube.
				this.model.set( 'custom_json', customJSON );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			changeSetHeaders: function() {
				var setHeaders = jQuery( event.target ).is( ':checked' );

				// Set the headers option as model attritube.
				this.model.set( 'set_headers', setHeaders );

				FlowMatticWorkflowEvents.trigger( 'actionDataPatched' );
			},

			changeSetParameters: function() {
				var setParameters = jQuery( event.target ).is( ':checked' );

				// Set the parameters option as model attritube.
				this.model.set( 'set_parameters', setParameters );

				FlowMatticWorkflowEvents.trigger( 'actionDataPatched' );
			},

			updateAPIParametersField: function() {
				var apiParameters = this.model.get( 'api_parameters' );

				// Set this modal attribute.
				if ( 'api' === this.model.get( 'application' ) ) {
					this.model.set( 'actionAppArgs', apiParameters );
				}
			},

			updateAppAuthData: function( application, authData ) {
				var workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_save_app_authentication', 'workflow_id': workflowId, 'application': application, workflow_nonce: flowMatticAppConfig.workflow_nonce, authData: authData.webhook_capture },
						success: function( response ) {
							// Auth data stored successfully!
							// Autosave workflow.
							FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
						}
					}
				);
			},

			changeDynamicFields: function( event ) {
				var dynamicField = {},
					dynamicFieldKeys,
					dynamicFieldValues,
					dynamicFieldName = jQuery( event.target ).parents( '.data-dynamic-fields' ).attr( 'data-field-name' );

				dynamicFieldKeys = jQuery( event.target ).parents( '.data-dynamic-fields' ).find( 'input[name="dynamic-field-key[]"]' ).map( function() {
					return jQuery( this ).val();
				} ).get();

				dynamicFieldValues = jQuery( event.target ).parents( '.data-dynamic-fields' ).find( '[name="dynamic-field-value[]"]' ).map( function() {
					return jQuery( this ).val();
				} ).get();

				_.each( dynamicFieldKeys, function( parameterKey, index ) {
					parameterKey = ( '' === parameterKey ) ? index : parameterKey;
					dynamicField[ parameterKey ] = dynamicFieldValues[ index ];
				} );

				// Set the dynamic field as model attritube.
				this.model.set( dynamicFieldName, dynamicField );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

				// Autosave workflow.
				FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
			},

			updateActionDataDependencies: function() {
				var dependentElements = jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( 'div[class*="data-event-"], div[class*="data-auth-"], div[class*="data-headers"], div[class*="data-parameters"]' ),
					action = this.model.get( 'action' ),
					authSelected = this.model.get( 'authentication' ),
					setHeaders = this.model.get( 'set_headers' ),
					setParameters = this.model.get( 'set_parameters' );

				// Hide all dependent elements first.
				jQuery( dependentElements ).hide();

				if ( 'custom_json' === this.model.get( 'content_type' ) && ( 'put' === action || 'post' === action || 'patch' === action ) ) {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).removeClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).addClass( 'hidden' );
				} else {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).addClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).removeClass( 'hidden' );
				}

				jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-event-' + action ).show();
				jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-auth-' + authSelected ).show();

				if ( 'undefined' !== typeof setHeaders && setHeaders ) {
					jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-headers' ).show();
				} else {
					jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-headers' ).hide();
				}

				if ( 'undefined' !== typeof setParameters && setParameters ) {
					jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-parameters' ).show();
				} else {
					jQuery( this.$el ).find( '.fm-workflow-action-data' ).find( '.data-parameters' ).hide();
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateActionStepData: function( application, actionView ) {
				var thisEl = this,
					data,
					currentStep = jQuery( this.$el ).find( '.fm-workflow-step' ),
					action = '',
					applicationData = {},
					appData,
					stepIndex,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				if ( this.model.get( 'action' ) ) {
					action = this.model.get( 'action' );
				}

				appData = ( 'undefined' !== typeof actionApps[ application ] ) ? actionApps[ application ] : otherActionApps[ application ];

				stepIndex = this.collection.indexOf( this.model );
				stepIndex = ( 'undefined' !== typeof window.refreshViewsAction && 'remove' !== window.refreshViewsAction ) ? stepIndex + 1 : stepIndex;

				if ( 'undefined' === typeof appData ) {
					return false;
				}

				this.model.set( 'stepIndex', stepIndex );

				applicationData.stepIndex         = stepIndex;
				applicationData.application       = application;
				applicationData.applicationName   = appData.name;
				applicationData.applicationEvents = appData.actions;
				applicationData.applicationAction = action;
				applicationData.conditional_execution = this.model.get( 'conditional_execution' );
				applicationData.ignore_errors = this.model.get( 'ignore_errors' );
				applicationData.simple_response   = ( 'undefined' === this.model.get( 'simple_response' ) ) ? 'Yes' : this.model.get( 'simple_response' );
				applicationData.response_format   = ( 'undefined' === this.model.get( 'response_format' ) ) ? 'simple' : this.model.get( 'response_format' );

				currentStep.html( thisEl.dataTemplate( applicationData ) ).removeClass( 'step-new' );
				currentStep.find( 'select' ).selectpicker();

				currentStep.find( 'select.workflow-application-events' ).trigger( 'change' );
				currentStep.find( '.fm-conditional-execution' ).trigger( 'change' );
				jQuery( 'body' ).find( '.flowmattic-sidebar-outline.fm-active' ).trigger( 'click' );

				if ( window.routerEditorOpen && 'undefined' !== typeof actionView ) {
					// Update step numbers in router.
					FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', actionView );
				}
			},

			changeAuthenticationValues: function() {
				var thisEl = this,
					authenticationInputs = jQuery( this.$el ).find( '.fm-api-authentication input, .fm-api-authentication textarea' );

				_.each( authenticationInputs, function( input, index ) {
					var authInput = jQuery( input );

					if ( authInput.is( '.fm-dynamic-inputs' ) ) {
						thisEl.model.set( authInput.attr('name').replaceAll( '-', '_' ), authInput.val() );
					}
				} );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			toggleAPIResponseData: function() {
				var toggleLink = jQuery( this.$el ).find( '.api-response-toggle' ),
					toggleDataWrap = toggleLink.next( '.api-response-body' );

				toggleLink.toggleClass( 'toggle' );
				toggleDataWrap.toggle( 'slideTop' );
			},

			updateAPIResponseData: function( response, cid ) {
				var apiResponseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-api-response-template' ).html() );

				if ( this.model.get( 'eid' ) === cid ) {

					// Patch the template with response data.
					this.$el.find( '.fm-api-capture-data' ).html( apiResponseTemplate( response ) );

					// Set the api response as model attritube.
					this.model.set( 'capturedData', response );
				}
			},

			addDynamicInput: function( event ) {
				var addMoreButton = jQuery( event.target ),
					dynamicInputWrap = '';

				// Close existing select dropdowns if any.
				jQuery( '#fm-dynamic-select' ).selectpicker( 'destroy' ).remove();

				dynamicInputWrap = addMoreButton.parent( '.dynamic-input-add-more' ).prev( '.fm-dynamic-input-wrap' ).clone();

				dynamicInputWrap.find( 'input' ).val( '' ).bind( 'change' );
				dynamicInputWrap.find( 'textarea' ).val( '' ).bind( 'change' );
				addMoreButton.parent( '.dynamic-input-add-more' ).before( dynamicInputWrap );

				// Add event listener to dynamic field button.
				if ( dynamicInputWrap.find( '.dynamic-field-button' )[0] ) {
					dynamicInputWrap.find( '.dynamic-field-button' )[0].addEventListener( 'click', function( e ) {
						// Fire custom event openDynamicTag on click of dynamic field button.
						var event = new CustomEvent( 'openDynamicTag', { detail: { target: e.target } } );
						document.dispatchEvent( event );
					} );
				}
			},

			removeDynamicInput: function( event ) {
				var removeButton = jQuery( event.target ),
					sibling = removeButton.closest( '.fm-dynamic-input-wrap' ).siblings( '.fm-dynamic-input-wrap' );

				if ( 0 !== removeButton.closest( '.fm-dynamic-input-wrap' ).siblings( '.fm-dynamic-input-wrap' ).length ) {
					// Remove the dynamic input field values.
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'input' ).val( '' );
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'textarea' ).val( '' );

					const selectChangeEvent = new Event('change', { bubbles: true });
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'textarea' )[0].dispatchEvent( selectChangeEvent );
					removeButton.closest( '.fm-dynamic-input-wrap' ).find( 'input' )[0].dispatchEvent( selectChangeEvent );
					
					// Remove the dynamic input field.
					removeButton.closest( '.fm-dynamic-input-wrap' ).remove();
				}

				event.target = sibling;
				this.changeDynamicFields( event );
			},

			generateDynamicFieldsPopup: function( event, editor, range ) {
				if ( window.isVisualWorkflowBuilder ) {
					return;
				}

				var thisEl = this,
					capturedData = [],
					currentInput = jQuery( event.target ).parents( '.fm-dynamic-input-field' ).find( '.dynamic-field-input' ),
					currentInputWrapper = '',
					selectTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-select-dropdown-template' ).html() ),
					optionsHTML = '',
					thisEid = this.model.get( 'eid'),
					stepID = this.model.get( 'stepID' ),
					application = this.model.get( 'application' ),
					models = this.collection ? this.collection.models : [],
					stopLoop = false,
					selectionStart = 'false',
					lastIndex = 0;

				if ( jQuery( event.currentTarget ).is( '.flowmattic-editor-toolbar' ) ) {
					currentInputWrapper = jQuery( event.target ).closest( '.fm-dynamic-input-field' );
				}

				// Close existing select dropdowns if any.
				jQuery( '#fm-dynamic-select' ).selectpicker( 'destroy' ).remove();

				if ( jQuery( event.target ).is( 'input' ) || jQuery( event.target ).is( 'textarea' ) ) {
					currentInput = jQuery( event.target );
					selectionStart = event.target.selectionStart;
				}

				// Get the variables object.
				if ( 'undefined' !== typeof window.FMConfig.variables ) {
					var variableOptions = '';
					_.each ( window.FMConfig.variables, function( varDetails, variableName ) {
						variableOptions += '<option value="{{' + variableName + '}}" data-subtext="' + varDetails.variable_value + '"> ' + variableName + '</option>';
					} );

					optionsHTML += '<optgroup label="FlowMattic Variables" data-max-options="1">' + variableOptions + '</optgroup>';
				}

				if ( 'wordpress' === application && currentInput.hasClass( 'user-role-field' ) ) {
					var userRoles = window.FMWPConfig.user_roles,
						user_roles_output = '';

					_.each ( userRoles, function( name, role ) {
						user_roles_output += '<option value="' + role + '">' + name + '</option>';;
					} );

					optionsHTML += '<optgroup label="WordPress User Roles" data-max-options="1">' + user_roles_output + '</optgroup>';
				}

				if ( 'wordpress' === application && currentInput.hasClass( 'user-role-capabilities' ) ) {
					var userRoleCapabilities = window.FMWPConfig.user_role_capabilities,
						capabilities_output = '';

					_.each ( userRoleCapabilities, function( name, role ) {
						capabilities_output += '<option value="' + role + '">' + name + '</option>';;
					} );

					optionsHTML += '<optgroup label="WordPress User Role Capabilities" data-max-options="1">' + capabilities_output + '</optgroup>';
				}

				// Loop through all models till the current step is reached.
				_.each ( models, function( model, index ) {
					var eid = model.get( 'eid' ),
						appType = model.get( 'application' ),
						storedData,
						stepTitle,
						objectData,
						objectDataIterator = {};

					// If new step is inserted before, skip it.
					if ( ! appType ) {
						return false;
					}

					// Check if the current step is being evaluated.
					if ( thisEid === eid ) {
						stopLoop = true;
					}

					// Check if is router.
					if ( window.routerEditorOpen && window.routerEl.model.get( 'eid' ) === eid ) {
						stopLoop = true;
					}

					// If stop loop, then stop it!
					if ( stopLoop ) {
						return false;
					}

					// Get captured data from the previous steps.
					storedData = model.get( 'capturedData' );

					// If is webhook, get the webhook capture.
					if ( 'undefined' !== typeof storedData && 'undefined' !== typeof storedData.webhook_capture ) {
						storedData = storedData.webhook_capture;
					}

					if ( 'undefined' === typeof storedData ) {
						storedData = [];
					}

					objectData = ( 'undefined' !== typeof storedData.responseData ) ? storedData.responseData : storedData;

					// Get values with only objects for iterator.
					if ( 'iterator' === application ) {
						_.each ( objectData, function( value, name ) {
							var isObject = false;

							if ( value ) {
								try {
									isObject = JSON.parse( value );
									if ( 'object' === typeof isObject ) {
										objectDataIterator[ name ] = value;
									}
								} catch (e) {
									return;
								}
							}
						} );

						if ( ! _.isEmpty( objectDataIterator ) ) {
							objectData = objectDataIterator;
						}
					}

					// Get the step title.
					stepTitle = ( 'undefined' !== typeof model.get( 'stepTitle' ) && '' !== model.get( 'stepTitle' ) ) ? model.get( 'stepTitle' ) : '';

					let appName = appType;

					// If title is empty, get the action.
					var currentAppAction = model.get( 'action' );
					if ( '' === stepTitle && '' !== currentAppAction ) {
						if ( 'action' === model.get( 'type' ) ) {
							stepTitle = '';
							if ( 'undefined' !== typeof actionApps[ appType ] ) {
								stepTitle = actionApps[ appType ].actions[ currentAppAction ].title;
							} else {
								if ( 'undefined' === typeof otherActionApps[ appType ].actions[ currentAppAction ] ) {
									_.each ( otherActionApps[ appType ].actions, function( action ) {
										var actionData = action[0];
										if ( 'undefined' !== typeof actionData[ currentAppAction ] ) {
											stepTitle = actionData[ currentAppAction ].title;
											return false;
										}
									} );
								} else {
									stepTitle = otherActionApps[ appType ].actions[ currentAppAction ].title;
								}
							}

							if ( 'undefined' !== typeof otherActionApps[ appType ] && 'undefined' !== typeof otherActionApps[ appType ].custom_app ) {
								appName = otherActionApps[ appType ].name;
							}
						} else {
							// For internal triggers.
							if ( 'webhook' !== appType && 'schedule' !== appType && 'undefined' !== typeof triggerApps[ appType ] ) {
								stepTitle = triggerApps[ appType ].triggers[ currentAppAction ].title;
							}

							// For external triggers.
							if ( 'undefined' !== typeof otherTriggerApps[ appType ] ) {
								// If the trigger is regular array.
								if ( 'undefined' !== typeof otherTriggerApps[ appType ].triggers[ currentAppAction ] ) {
									stepTitle = otherTriggerApps[ appType ].triggers[ currentAppAction ].title;
								} else {
									// If the trigger is nested array.
									_.each ( otherTriggerApps[ appType ].triggers, function( trigger ) {
										var triggerData = trigger[0];
										if ( 'undefined' !== typeof triggerData[ currentAppAction ] ) {
											stepTitle = triggerData[ currentAppAction ].title;
											return false;
										}
									} );
								}
							}

							if ( 'undefined' !== typeof otherTriggerApps[ appType ] && 'undefined' !== typeof otherTriggerApps[ appType ].custom_app ) {
								appName = otherTriggerApps[ appType ].name;
							}
						}
					}

					capturedData[ eid ] = { index: index + 1, name: appType, title: stepTitle, data: objectData, appName: appName };

					jQuery( '#fm-dynamic-select' ).html( selectTemplate( { options: capturedData[ eid ] } ) );

					optionsHTML += selectTemplate( { options: capturedData[ eid ] } );

					lastIndex++;
				} );

				// Check if is router.
				if ( window.routerEditorOpen ) {
					window.dynamicRouteOptionsHTML = '';
					FlowMatticWorkflowEvents.trigger( 'generateDynamicRouteOptionsHTML', application, currentInput, this, lastIndex );
					optionsHTML = optionsHTML + window.dynamicRouteOptionsHTML;
				}

				window.dynamicFieldOptionsHTML = optionsHTML;
				FlowMatticWorkflowEvents.trigger( 'generateDynamicFieldsHTML', application, currentInput, stepID );
				optionsHTML = window.dynamicFieldOptionsHTML;

				if ( '' !== currentInputWrapper ) {
					jQuery( currentInputWrapper ).append( '<select id="fm-dynamic-select" data-live-search="true" title="Choose Data" data-live-search-placeholder="Select data to map"/>' ).selectpicker();
				} else {
					jQuery( currentInput ).after( '<select id="fm-dynamic-select" data-live-search="true" title="Choose Data" data-live-search-placeholder="Select data to map"/>' ).selectpicker();
				}

				jQuery( '#fm-dynamic-select' ).html( optionsHTML );
				jQuery( '#fm-dynamic-select' ).selectpicker( 'refresh' );
				jQuery( '#fm-dynamic-select' ).selectpicker( 'toggle' );

				// Destroy and remove the select box so it can be reloaded with the fresh data.
				jQuery( '#fm-dynamic-select' ).on( 'hide.bs.select', function ( e, clickedIndex, isSelected, previousValue ) {
					jQuery( '#fm-dynamic-select' ).selectpicker( 'destroy' ).remove();
				} );

				// Insert title before search box.
				jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( '.bs-searchbox' ).prepend( '<strong class="fm-search-title">Select Captured Data</strong>' );
				jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( '.dropdown-header' ).append( '<span class="dashicons dashicons-arrow-down-alt2"></span>' );
				jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( 'li' ).addClass( 'list-option' );

				jQuery( '#fm-dynamic-select' ).on( 'loaded.bs.select', function ( e ) {
					jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( '.bs-searchbox input' ).blur();
					currentInput.focus();
					currentInput.selectionStart = selectionStart;
				} );

				// If value is changed, set it to the input box.
				jQuery( '#fm-dynamic-select' ).on( 'changed.bs.select', function ( e, clickedIndex, isSelected, previousValue ) {
					var currentValue = jQuery( currentInput ).val(),
						valueToUpdate = jQuery( '#fm-dynamic-select' ).selectpicker( 'val' ),
						updatedValue;

					if ( '' !== currentInputWrapper ) {
						const selection = window.getSelection();
						const textNode = document.createTextNode(valueToUpdate);
						range.deleteContents();
						range.insertNode(textNode);
						range.setStartAfter(textNode);
						range.setEndAfter(textNode);
						selection.removeAllRanges();
						range.collapse(false);
						selection.addRange(range);

						jQuery( currentInput ).val( jQuery( editor ).html() ).trigger( 'change' );

						if ( jQuery( editor ).is( '.flowmattic-editor' ) ) {
							jQuery( editor )[0].dispatchEvent(new Event('input', { bubbles: true }));
						}
					} else {
						updatedValue = currentValue.slice( 0, selectionStart ) + valueToUpdate + currentValue.slice( selectionStart );
						if ( 'false' === selectionStart ) {
							jQuery( currentInput ).val( valueToUpdate ).trigger( 'change' );
						} else {
							jQuery( currentInput ).val( updatedValue ).trigger( 'change' );
						}
					}
				} );

				// If option group is clicked, toggle the accordion view.
				jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( 'li.dropdown-header' ).on( 'click', function() {
					var thisDropdown = jQuery( this ),
						optionClasses = thisDropdown[0].className.split(' '),
						classes = '';

					thisDropdown.siblings('li').removeClass( 'open' );
					thisDropdown.toggleClass( 'open' );

					_.each( optionClasses, function( v ) {
						classes += ( '' !== v && 'dropdown-header' !== v ) ? "." + v : '';
					} );

					jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( 'li:not(.dropdown-header):not(.dropdown-divider)' ).hide();
					jQuery( '#fm-dynamic-select' ).parent( '.dropdown' ).find( 'li' + classes ).show();

				} );
			},

			renameStepTitle: function( event ) {
				var thisEl = this,
					currentStepHeader = jQuery( event.currentTarget ).closest( '.fm-workflow-step-header' );
					currentStepTitle = currentStepHeader.find( '.workflow-step-title' ).text().replace( /\s\s+/g, ' ' ).trim();

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
							currentStepHeader.find( '.fm-application-title' ).text( `${stepTitle}` );
							currentStepHeader.find( '.fm-application-action' ).html( '' );

							// If is router, save the settings to the route action.
							if ( window.routerEditorOpen ) {
								_.each( window.routerActions, function( routeAction, key ) {
									var actionModel;

									if ( 'undefined' !== typeof routeAction ) {
										actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

										if ( thisEl.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {
											// Set step title for the route action.
											if ( 'undefined' === typeof window.routerActions[ key ].model ) {
												window.routerActions[ key ].set( 'stepTitle', stepTitle );
											} else {
												window.routerActions[ key ].model.set( 'stepTitle', stepTitle );
											}

											return false;
										}
									}
								} );
							}

							FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

							// Autosave workflow.
							FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
						}
					}
				} );
			},

			displayStepDescription: function( event ) {
				var thisEl = this,
					stepDescription = this.model.get( 'stepDescriptionText' ),
					descriptionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-step-description-template' ).html() );

				event.preventDefault();

				// Delete the existing modal instance.
				jQuery( '#stepDescription' ).modal( 'hide' );

				// Create new modal instance.
				jQuery( 'body' ).find( '.fm-step-description-wrapper' ).html( descriptionTemplate( { stepDescriptionText: stepDescription } ) );

				// Show the modal.
				jQuery( '#stepDescription' ).modal( 'show' );

				// When modal is closed, remove the template.
				jQuery( '#stepDescription' ).on( 'hide.bs.modal', function () {
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
				var descriptionText = jQuery( 'body' ).find( '#fm-step-description-text' ).text();

				// Save the description text.
				this.model.set( 'stepDescriptionText', descriptionText );

				// Hide the modal.
				jQuery( '#stepDescription' ).modal( 'hide' );
				jQuery( 'body' ).find( '.modal-backdrop' ).remove();

				// Autosave workflow.
				FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
			},

			closeStep: function() {
				var thisEl = this,
					routerActions;

				const swalWithBootstrapButtons = window.Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-primary shadow-none me-xxl-3',
						cancelButton: 'btn btn-danger shadow-none'
					},
					buttonsStyling: false
				} );

				swalWithBootstrapButtons.fire( {
					title: 'Are you sure?',
					text: "You won't be able to revert this!",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, delete it!',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return []
					}
				} ).then( ( result ) => {
					if ( result.isConfirmed ) {
						// Update dynamic tags.
						var stepIndex = this.collection.indexOf( this.model );

						// Refresh views after this step.
						FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', stepIndex, true );

						// Check if is router.
						if ( window.routerEditorOpen ) {
							routerActions = [];
							_.each( window.routerActions, function( routeAction ) {
								var actionModel;

								if ( 'undefined' !== typeof routeAction ) {
									actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

									if ( thisEl.model.get( 'stepID' ) !== actionModel.get( 'stepID' ) ) {
										routerActions.push( routeAction );
									} else {
										FlowMatticWorkflowEvents.trigger( 'removeRouterStepAction', routeAction );
									}
								}
							});

							window.routerActions = routerActions;
						}

						// Remove view.
						FlowMatticWorkflowViewManager.removeView( thisEl.model.get( 'cid' ) );

						// Destroy backbone model.
						thisEl.model.destroy();

						// Remove the HTML element.
						thisEl.remove();

						FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

						// Autosave workflow.
						FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );

						swalWithBootstrapButtons.fire(
							{
								title: 'Deleted!',
								text: 'Selected action has been deleted.',
								icon: 'success',
								showConfirmButton: false,
								timer: 1500
							}
						);
					}
				} );
			},

			updateActionAppDataSingleAttribute: function( attr, value, view ) {
				if ( 'undefined' !== typeof view.model ) {
					if ( view.model.get( 'stepID' ) === this.model.get( 'stepID' ) ) {
						this.model.set( attr, value );
					}
				} else {
					if ( view.get( 'stepID' ) === this.model.get( 'stepID' ) ) {
						this.model.set( attr, value );
					}
				}

				// Trigger custom event to alert the relative apps.
				var customEvent = new CustomEvent( 'actionAppDataUpdateSingleAttribute', { detail: { attr: attr, value: value } } );
				document.dispatchEvent( customEvent );
			},

			correctFieldKey: function( event ) {
				var field = jQuery( event.target ),
					value = field.val();

				value = value.trim().replaceAll( ' ', '-' );

				field.val( value ).trigger( 'change' );
			},

			saveTestAction: function( event ) {
				var thisEl = this,
					captureButton = jQuery( event.target ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					settings = this.model.toJSON(),
					stepIDs = {},
					eventResponse = {},
					eventInputFields,
					eventInputFieldValues = {},
					validated = true,
					eventAction = this.model.get( 'action' ),
					application = this.model.get( 'application' );

				event.preventDefault();

				// Fire event before saving the data.
				FlowMatticWorkflowEvents.trigger( 'beforeTestSubmit' );

				// Fire save workflow at first.
				FlowMatticWorkflowEvents.trigger( 'saveWorkflow' );

				_.each( jQuery( this.$el ).find( '.fm-workflow-step-body input, .fm-workflow-step-body select, .fm-workflow-step-body textarea' ), function( field, index ) {
					jQuery( field ).closest( '.form-group' ).addClass( 'validate-form' );

					if ( ! field.checkValidity() ) {
						validated = false;
					}
				} );

				if ( validated ) {

					captureButton.text( 'Waiting for response' ).prepend( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="32px" height="32px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\
						<circle cx="50" cy="50" r="30" stroke="#ffffff" stroke-width="10" fill="none"></circle>\
						<circle cx="50" cy="50" r="30" stroke="#007bff" stroke-width="8" stroke-linecap="round" fill="none">\
						<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>\
						<animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1s" values="5.654866776461627 182.84069243892594;94.2477796076938 94.24777960769377;5.654866776461627 182.84069243892594" keyTimes="0;0.5;1"></animate>\
						</circle></svg>'
					);

					// Reset the previous captured data.
					this.$el.find( '.fm-response-capture-data' ).html('');

					// Delete existing api response.
					delete settings.capturedData;
					delete settings.rowData;
					delete settings.sheetsData;
					delete settings.view;

					_.each( this.collection.models, function( model, index ) {
						var appType = model.get( 'application' ),
							appIndex = appType + ( index + 1 );

						stepIDs[ appIndex ] = model.get( 'stepID' );
					} );

					// Get the settings.
					eventInputFieldValues = this.model.get( 'actionAppArgs' );

					// If is router, get the settings from the route action.
					if ( window.routerEditorOpen ) {
						_.each( window.routerActions, function( routeAction, key ) {
							var actionModel;

							if ( 'undefined' !== typeof routeAction ) {
								actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

								if ( thisEl.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {

									settings = thisEl.model.toJSON();
									eventInputFieldValues = ( 'undefined' === typeof settings.actionAppArgs ) ? settings : settings.actionAppArgs;

									delete settings.applicationEvents;

									return false;
								}
							}
						} );
					}

					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_save_test_action_step', fields: eventInputFieldValues, application: application, event: eventAction, stepIDs: stepIDs, settings: settings, 'workflow_id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce },
							success: function( response ) {
								var stepID = jQuery( thisEl.$el ).find( '.fm-workflow-step' ).attr( 'step-id' );

								response = JSON.parse( response );
								captureButton.text( 'Save & Test Action' );
								thisEl.model.set( 'capturedData', response );

								// If is router, get the settings from the route action.
								if ( window.routerEditorOpen ) {
									_.each( window.routerActions, function( routeAction, key ) {
										var actionModel;

										if ( 'undefined' !== typeof routeAction ) {
											actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

											if ( thisEl.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {

												actionModel.set( 'capturedData', response );

												_.each( actionModel.toJSON(), function( value, option ) {
													if ( 'undefined' === typeof window.routerActions[ key ].model ) {
														window.routerActions[ key ].set( option, value );
													} else {
														window.routerActions[ key ].model.set( option, value );
													}
												} );

												return false;
											}
										}
									} );
								}

								eventResponse.capturedData = response;
								FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', eventResponse, stepID );
								thisEl.toggleResponseData();

								// Autosave workflow.
								FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
							}
						}
					);

					// If is router, save the settings to the route action.
					if ( window.routerEditorOpen ) {
						_.each( window.routerActions, function( routeAction, key ) {
							var actionModel;

							if ( 'undefined' !== typeof routeAction ) {
								actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

								if ( thisEl.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {
									// Set each step options as per database records.
									_.each( thisEl.model.toJSON(), function( value, option ) {
										if ( 'undefined' === typeof window.routerActions[ key ].model ) {
											window.routerActions[ key ].set( option, value );
										} else {
											window.routerActions[ key ].model.set( option, value );
										}
									} );

									return false;
								}
							}
						} );
					}
				}
			},

			updateActionAppArgs: function() {
				var actionAppArgs = {};

				_.each( jQuery( this.$el ).find( '.form-control' ), function( field, index ) {
					var inputName = jQuery( field ).attr( 'name' ),
						inputSubName = jQuery( field ).attr( 'sub-name' ),
						inputValue = jQuery( field ).val();

					if ( jQuery( field ).is( ':checkbox' ) ) {
						inputValue = ( jQuery( field ).is( ':checked' ) ) ? 'Yes' : 'No';
					}

					if ( jQuery( field ).is( ':radio' ) ) {
						inputValue = jQuery( 'input[name="' + inputName + '"]:checked' ).val();
					}

					if ( 'undefined' !== typeof inputSubName ) {
						if ( 'undefined' === typeof actionAppArgs[ inputName ] ) {
							actionAppArgs[ inputName ] = {};
							actionAppArgs[ inputName ][ inputSubName ] = inputValue;
						} else {
							actionAppArgs[ inputName ][ inputSubName ] = inputValue;
						}
					} else {
						if ( 'undefined' !== typeof inputName ) {
							actionAppArgs[ inputName ] = inputValue;
						}
					}
				} );

				// Set this modal attribute.
				this.model.set( 'actionAppArgs', actionAppArgs );

				// Fire custom event to alert the react builder.
				var event = new CustomEvent( 'updateActionAppArgs', { detail: { actionAppArgs: actionAppArgs } } );
				document.dispatchEvent( event );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateContentTypeFields: function( event ) {
				var contentType = jQuery( event.target ).val();

				if ( 'custom_json' === contentType ) {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).removeClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).addClass( 'hidden' );
				} else {
					jQuery( this.$el ).find( '.custom-json-wrapper' ).addClass( 'hidden' );
					jQuery( this.$el ).find( '.form-group.api-parameters' ).removeClass( 'hidden' );
				}
			},

			updateFormSubmissionData: function( response, stepID ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-app-request-response-template' ).html() );

				jQuery( this.$el ).find( '.fm-workflow-action[step-id="' + stepID + '"]' ).find( '.fm-response-capture-data' ).html( responseTemplate( response ) );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			toggleResponseData: function() {
				var toggleLink = jQuery( this.$el ).find( '.fm-response-data-toggle' ),
					toggleDataWrap = toggleLink.next( '.fm-response-body' );

				toggleLink.toggleClass( 'toggle' );
				toggleDataWrap.toggle( 'slideTop' );
			},


			silentRemoveStep: function( view ) {
				if ( view.model.get( 'stepID' ) === this.model.get( 'stepID' ) ) {
					FlowMatticWorkflowViewManager.removeView( view.model.get( 'cid' ) );
					view.model.destroy();
					view.remove();

					FlowMatticWorkflowViewManager.removeView( this.model.get( 'cid' ) );
					this.model.destroy();
					this.remove();
				}
			},

			showContextMenu: function( event ) {
				var thisEl = this,
					contextMenuView,
					contextMenu,
					model_id = thisEl.model.get( 'eid' ),
					top = event.pageY,
					left = event.pageX;

				if ( window.routerEditorOpen ) {
					model_id = this.model.get( 'stepID' );
				}

				// If this is route, context menu should not displayed.
				if ( jQuery( event.target ).is( '.flowmattic-router-unit' ) || jQuery( event.target ).closest( '.flowmattic-routers-data' ).length ) {
					console.log( 'This is route. Use the individual route action buttons.' );
					return false;
				}

				contextMenuView = new FlowMatticContextMenuView( {
					model_id: model_id
				} );

				contextMenu = contextMenuView.render().el;
				top = jQuery( contextMenu ).outerHeight() + top;
				jQuery( contextMenu ).find( '.flowmattic-context-menu' ).css( { top: top, left: left } );

				jQuery( 'body' ).append( contextMenu );

				FlowMatticWorkflowViewManager.addView( contextMenuView.cid, contextMenuView );
			},

			cloneStep: function( eid ) {
				var elementCID,
					action,
					thisEl,
					index,
					routerActions = [],
					currentTime = Date.now(),
					stepID = FlowMatticWorkflow.randomString( 5 ) + '-' + currentTime;

				if ( window.routerEditorOpen ) {
					if ( eid !== this.model.get( 'stepID' ) ) {
						return false;
					}
				} else {
					if ( eid !== this.model.get( 'eid' ) ) {
						return false;
					}
				}

				thisEl = this;
				action = new FlowMatticWorkflow.Action();
				action.set( 'view', this );

				// Set step options for cloned step.
				step = this.model.toJSON();
				_.each( step, function( value, option ) {
					action.set( option, value );
				} );

				// Set new step ID to avoid duplicate step ID.
				action.set( 'stepID', stepID );

				action.set( 'capturedData', '' );

				index = this.collection.indexOf( this.model );

				if ( window.routerEditorOpen ) {
					var index2 = window.routerEl.collection.length;
					action.set( 'routerRoute', window.routerRoute );

					_.each( window.routerActions, function( routeAction ) {
						var actionModel;

						if ( 'undefined' !== typeof routeAction ) {
							actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

							if ( 'undefined' !== typeof actionModel.get('application') ) {
								routerActions.push( routeAction );

								if ( eid === actionModel.get( 'stepID' ) ) {
									routerActions.push( action );
								}
							}
						}
					});

					// Assign the actions to router.
					window.routerActions = routerActions;

					setTimeout( function() {
						window.routerEl.collection.add( [action], {at: index2 + 1 } );

						// Update step numbers in router.
						FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', thisEl );
					}, 50 );
				} else {
					setTimeout( function() {
						thisEl.collection.add( [action], {at: index + 1 } );

						var stepIndex = thisEl.collection.indexOf( action );
	
						// Refresh views after this step.
						FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', stepIndex );
					}, 50 );
				}
			},

			deleteStep: function( eid ) {
				var thisEl = this,
					routerActions = [];

				if ( window.routerEditorOpen ) {
					if ( eid !== this.model.get( 'stepID' ) ) {
						return false;
					}
				} else {
					if ( eid !== this.model.get( 'eid' ) ) {
						return false;
					}
				}

				const swalWithBootstrapButtons = window.Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-primary shadow-none me-xxl-3',
						cancelButton: 'btn btn-danger shadow-none'
					},
					buttonsStyling: false
				} );

				swalWithBootstrapButtons.fire( {
					title: 'Are you sure?',
					text: "You won't be able to revert this!",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, delete it!',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return []
					}
				} ).then( ( result ) => {
					if ( result.isConfirmed ) {
						// Update dynamic tags.
						var stepIndex = this.collection.indexOf( thisEl.model );

						// Refresh views after this step.
						FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', stepIndex, true );

						// Check if is router.
						if ( window.routerEditorOpen ) {
							_.each( window.routerActions, function( routeAction ) {
								var actionModel;

								if ( 'undefined' !== typeof routeAction ) {
									actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

									if ( thisEl.model.get( 'stepID' ) !== actionModel.get( 'stepID' ) ) {
										routerActions.push( routeAction );
									} else {
										FlowMatticWorkflowEvents.trigger( 'removeRouterStepAction', routeAction );
									}
								}
							} );

							window.routerActions = routerActions;

							// Update step numbers in router.
							FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', thisEl );
						}

						// Remove view.
						FlowMatticWorkflowViewManager.removeView( thisEl.model.get( 'cid' ) );

						// Destroy backbone model.
						thisEl.model.destroy();

						// Remove the HTML element.
						thisEl.remove();

						FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );

						// Autosave workflow.
						FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
					}
				} );
			},


			addStepBefore: function( eid, copiedSettings ) {
				var thisEl = this,
					index = this.collection.indexOf( this.model ),
					action,
					routerActions = [],
					currentTime = Date.now(),
					stepID = FlowMatticWorkflow.randomString( 5 ) + '-' + currentTime;

				if ( window.routerEditorOpen ) {
					if ( eid !== this.model.get( 'stepID' ) ) {
						return false;
					}
				} else {
					if ( eid !== this.model.get( 'eid' ) ) {
						return false;
					}
				}

				action = new FlowMatticWorkflow.Action();
				action.set( 'view', this );

				// Set step options for cloned step.
				_.each( copiedSettings, function( value, option ) {
					action.set( option, value );
				} );

				// Set new step ID to avoid duplicate step ID.
				action.set( 'stepID', stepID );

				// Remove captured data.
				action.set( 'capturedData', '' );

				// Set insert before flag.
				action.set( 'insertBefore', true );

				if ( window.routerEditorOpen && ! window.insertBeforeProcessed ) {
					var index2 = window.routerEl.collection.length;
					action.set( 'routerRoute', window.routerRoute );

					_.each( window.routerActions, function( routeAction ) {
						var actionModel;

						if ( 'undefined' !== typeof routeAction ) {
							actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

							if ( 'undefined' !== typeof actionModel.get('application') ) {
								if ( eid === actionModel.get( 'stepID' ) ) {
									routerActions.push( action );
									routerActions.push( routeAction );
								} else {
									routerActions.push( routeAction );
								}
							}
						}
					});

					// Assign the actions to router.
					window.routerActions = routerActions;

					// Set the insert flag to true to avoid further duplication.
					window.insertBeforeProcessed = true;

					setTimeout( function() {
						window.routerEl.collection.add( [action], {at: index2 + 1 } );

						// Update step numbers in router.
						FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', thisEl );
					}, 50 );
				} else {
					setTimeout( function() {
						thisEl.collection.add( [action], {at: index } );
					}, 50 );
				}
			},

			addStepAfter: function( eid, copiedSettings ) {
				var thisEl = this,
					index = this.collection.indexOf( this.model ),
					action,
					newRouterActions = [],
					currentTime = Date.now(),
					stepID = FlowMatticWorkflow.randomString( 5 ) + '-' + currentTime;

				if ( window.routerEditorOpen ) {
					if ( eid !== this.model.get( 'stepID' ) ) {
						return false;
					}
				} else {
					if ( eid !== this.model.get( 'eid' ) ) {
						return false;
					}
				}

				action = new FlowMatticWorkflow.Action();
				action.set( 'view', this );

				// Set step options for cloned step.
				_.each( copiedSettings, function( value, option ) {
					action.set( option, value );
				} );

				// Set new step ID to avoid duplicate step ID.
				action.set( 'stepID', stepID );

				// Remove captured data.
				action.set( 'capturedData', '' );

				// Set insert after flag.
				action.set( 'insertAfter', true );

				if ( window.routerEditorOpen && ! window.insertAfterProcessed ) {
					var index2 = window.routerEl.collection.length;
					action.set( 'routerRoute', window.routerRoute );

					_.each( window.routerActions, function( routeAction ) {
						var actionModel;

						if ( 'undefined' !== typeof routeAction ) {
							actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

							if ( 'undefined' !== typeof actionModel.get('application') ) {
								newRouterActions.push( routeAction );

								if ( eid === actionModel.get( 'stepID' ) ) {
									newRouterActions.push( action );
								}
							}
						}
					} );

					// Assign the actions to router.
					window.routerActions = newRouterActions;

					// Set the insert flag to true to avoid further duplication.
					window.insertAfterProcessed = true;

					setTimeout( function() {
						window.routerEl.collection.add( [action], {at: index2 + 1 } );

						// Update step numbers in router.
						FlowMatticWorkflowEvents.trigger( 'refreshRouterViewsOnAddRemoveAction', thisEl );
					}, 50 );
				} else {
					setTimeout( function() {
						thisEl.collection.add( [action], {at: index + 1 } );
					}, 50 );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			enableDisableSimpleResponse: function ( event ) {
				var input = jQuery( event.currentTarget ),
					value;

				if ( jQuery( input ).is( ':checked' ) ) {
					value = 'Yes';
				} else {
					value = 'No';
				}

				// Set the sample response model attritube.
				this.model.set( 'simple_response', value );
			},

			changeResponseFormat: function( e ) {
				var responseFormat = jQuery( e.currentTarget ).val();

				this.model.set( 'response_format', responseFormat );
				this.model.set( 'simple_response', responseFormat );
			},

			enableDisableConditionalLogic: function( event ) {
				var input = jQuery( event.currentTarget ),
					value;

				if ( jQuery( input ).is( ':checked' ) ) {
					jQuery( this.$el ).find( '.action-conditions-wrap' ).removeClass( 'hidden' );
					value = 'Yes';

					if ( ! jQuery( this.$el ).find( '.action-conditions-wrap .fm-filter-condition' ).length ) {
						// Build the conditions.
						this.buildFilterConditions( this.model.toJSON() );
					}
				} else {
					jQuery( this.$el ).find( '.action-conditions-wrap' ).addClass( 'hidden' );
					value = 'No';
				}

				// Set the conditional model attritube.
				this.model.set( 'conditional_execution', value );
			},

			enableDisableIgnoreErrors: function( event ) {
				var input = jQuery( event.currentTarget ),
					value;

				if ( jQuery( input ).is( ':checked' ) ) {
					value = 'Yes';
				} else {
					value = 'No';
				}

				// Set the ignore errors model attritube.
				this.model.set( 'ignore_errors', value );
			},

			addAndConditionFields: function( event ) {
				var previousConditionField = jQuery( event.target ).closest( '.dynamic-input-add-conditions' ),
					conditionsTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-conditions-template' ).html() ),
					andOrButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-or-template' ).html() ),
					andButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-template' ).html() ),
					conditionsBody = jQuery( this.$el ).find( '.fm-filter-conditions-body' );

				jQuery( conditionsTemplate() ).insertBefore( previousConditionField ).wrap( '<div class="fm-filter-condition" />' );

				if ( ! window.isVisualWorkflowBuilder ) {
					this.$el.find( 'select' ).selectpicker();
				}
			},

			addOrConditionFields: function( event ) {
				var previousConditionField = jQuery( event.target ).closest( '.dynamic-input-add-conditions' ),
					conditionsTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-conditions-template' ).html() ),
					andOrButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-or-template' ).html() ),
					andButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-template' ).html() ),
					conditionsBody = jQuery( this.$el ).find( '.fm-filter-conditions-body' ),
					orConditionBlock = '<hr/><div class="fm-or-condition-block justify-content-center mt-0 mb-0"><span class="form-text fw-bold">Or continue if</span></div>';

				// Add and condition button to previous condition block.
				jQuery( andButtonTemplate() ).insertBefore( previousConditionField );

				jQuery( conditionsTemplate() ).insertBefore( previousConditionField ).before( orConditionBlock ).wrap( '<div class="fm-filter-condition" />' );

				if ( ! window.isVisualWorkflowBuilder ) {
					this.$el.find( 'select' ).selectpicker();
				}
			},

			checkFilterValueRequirement: function( event ) {
				var selectedFilterCondition = jQuery( event.target ).val();

				switch ( selectedFilterCondition ) {
					case 'is_true':
					case 'is_false':
					case 'exists':
					case 'does_not_exists':
					case 'is_empty':
					case 'is_not_empty':
					case 'is_blank':
					case 'is_not_blank':
					case 'is_email_valid':
					case 'is_valid_json':
						jQuery( event.target ).closest( '.fm-dynamic-input-wrap' ).find( '.filter-condition-value' ).css( 'opacity', '0' );
						break;

					default:
						jQuery( event.target ).closest( '.fm-dynamic-input-wrap' ).find( '.filter-condition-value' ).css( 'opacity', '1' );
						break;

				}
			},

			removeCondition: function( event ) {
				var removeButton = event.srcElement ? jQuery( event.srcElement ) : jQuery( event.currentTarget ),
					currentCondition = removeButton.closest( '.fm-filter-condition' ),
					previousCondition = currentCondition.prev( '.fm-filter-condition' ),
					nextCondition = currentCondition.next( '.fm-filter-condition' ),
					previousBadge = currentCondition.prev( '.fm-or-condition-block' ),
					conditionsTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-conditions-template' ).html() );

				// If previous condition not exists, check if badge exists.
				if ( ! previousCondition.length ) {
					// If previous badge exists, check if next condition exists.
					if ( previousBadge.length ) {
						// If next condition is not exists, remove the badge and horizontal line.
						if ( ! nextCondition.length ) {
							previousBadge.prev( 'hr' ).prev( '.fm-flowmattic-filter-add-more' ).remove();
							previousBadge.prev( 'hr' ).remove();
							previousBadge.remove();
						}
					}
				}

				// Remove the current condition.
				currentCondition.remove();

				// Check if there's no condition left and add an empty one.
				if ( ! jQuery( this.$el ).find( '.fm-filter-conditions-body' ).find( '.fm-filter-condition' ).length ) {
					jQuery( conditionsTemplate() ).insertBefore( jQuery( this.$el ).find( '.fm-flowmattic-filter-add-more' ) ).wrap( '<div class="fm-filter-condition" />' );
					this.$el.find( 'select' ).selectpicker();
				}

				// Update the filter conditions in model.
				this.buildFilterConditionArray();
			},

			toggleDynamicMap: function( event ) {
				var mapButton = jQuery( event.currentTarget ),
					fieldName = mapButton.attr( 'name' ),
					thisView = FlowMatticWorkflowViewManager.getView( this.model.get('eid') );

				// If button is checked, set the value to the value of the button.
				if ( mapButton.is( ':checked' ) ) {
					mapValue = mapButton.val();

					// Hide the select field, and show the input field.
					mapButton.closest( '.form-group' ).find( '.fm-select-input-field' ).hide();
					mapButton.closest( '.form-group' ).find( '.fm-dynamic-map-toggle-input' ).show();
					mapButton.closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex' ).addClass( 'd-flex-temp d-none' );
				} else {
					mapValue = '';

					// Show the select field, and hide the input field.
					mapButton.closest( '.form-group' ).find( '.fm-select-input-field' ).show();
					mapButton.closest( '.form-group' ).find( '.fm-dynamic-map-wrap' ).removeClass( 'd-flex-temp d-none' ).addClass( 'd-flex w-100' );
					mapButton.closest( '.form-group' ).find( '.fm-dynamic-map-toggle-input' ).hide();
				}

				// Set the value to the model.
				thisView.model.set( fieldName, mapValue );
			},

			updateMapField: function( event ) {
				var mapField = jQuery( event.currentTarget ),
					fieldName = mapField.attr( 'name' ),
					fieldValue = mapField.val();
					thisView = FlowMatticWorkflowViewManager.getView( this.model.get('eid') ),
					actionAppArgs = thisView.model.get( 'actionAppArgs' ),

				// Update the field value.
				actionAppArgs[ fieldName ] = fieldValue;

				// Update the select field value with same name.
				jQuery( this.$el ).find( 'select[name="' + fieldName + '"]' ).val( fieldValue );

				// Remove the required attribute from the select field.
				jQuery( this.$el ).find( 'select[name="' + fieldName + '"]' ).removeAttr( 'required' );

				// Set the value to the model.
				thisView.model.set( 'actionAppArgs', actionAppArgs );
			},

			buildFilterConditionArray: function( event ) {
				var thisEl = this,
					filterConditionBlocks = jQuery( this.$el ).find( '.fm-filter-condition' ),
					filterConditions = {},
					filterCondition = {},
					filterConditionIndex = 1;

				filterConditions[ filterConditionIndex ] = [];

				_.each ( filterConditionBlocks, function( field, index ) {
					var key = jQuery( field ).find( 'input[name="filter-field-key"]' ).val(),
						condition = jQuery( field ).find( 'select[name="filter-field-condition"]' ).val(),
						value = jQuery( field ).find( '[name="filter-field-value"]' ).val();

					// If value is undefined, skip it.
					if ( 'undefined' === typeof value ) {
						return false;
					}

					// If value contains double quotes, urlencode it.
					if ( value && value.includes( '"' ) ) {
						value = encodeURIComponent( value );
					}

					if ( 'undefined' !== typeof key ) {
						filterCondition = {
							'key': key,
							'condition': condition,
							'value': value
						};

						if ( 1 === filterConditionBlocks.length ) {
							filterConditions[ filterConditionIndex ].push( filterCondition );
						} else {
							if ( jQuery( field ).next().is( '.fm-filter-condition' ) ) {
								filterConditions[ filterConditionIndex ].push( filterCondition );
							} else if ( jQuery( field ).next().is( '.fm-flowmattic-filter-add-more' ) && jQuery( field ).next().next().is( 'hr' ) ) {
								filterConditions[ filterConditionIndex ].push( filterCondition );

								filterConditionIndex++;

								if ( 'undefined' === typeof filterConditions[ filterConditionIndex ] ) {
									filterConditions[ filterConditionIndex ] = [];
								}
							} else {
								filterConditions[ filterConditionIndex ].push( filterCondition );
							}
						}

						thisEl.model.set( 'filterConditions', filterConditions );
					}
				} );

				// For visual workflow builder.
				var customEvent = new CustomEvent( 'fmUpdateFilterConditions', { detail: { filterConditions: filterConditions } } );
				document.dispatchEvent( customEvent, { bubbles: true, cancelable: true });
			},

			buildFilterConditions: function( settings ) {
				var thisEl = this,
					conditionsTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-conditions-template' ).html() ),
					andOrButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-or-template' ).html() ),
					andButtonTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-filter-and-template' ).html() ),
					conditionsBody = jQuery( this.$el ).find( '.action-conditions-wrap' );
					orConditionBlock = '<hr/><div class="fm-or-condition-block justify-content-center mt-0 mb-0"><span class="form-text fw-bold">Or continue if</span></div>';

				if ( 'undefined' !== typeof settings.filterConditions ) {
					_.each ( settings.filterConditions, function( conditions, idx ) {
						_.each ( conditions, function( condition, index ) {
							var wrapper = conditionsBody.append( '<div class="fm-filter-condition"></div>' );
							wrapper.find( '.fm-filter-condition:empty' ).append( conditionsTemplate( condition ) );
						} );

						if ( ( parseInt( idx ) ) !== Object.keys( settings.filterConditions ).length ) {
							conditionsBody.append( andButtonTemplate() );
							conditionsBody.append( orConditionBlock );
						}
					} );

					conditionsBody.append( andOrButtonTemplate() );
				} else {
					conditionsBody.append( '<div class="fm-filter-condition"></div>' );
					conditionsBody.find( '.fm-filter-condition' ).append( conditionsTemplate() );
					conditionsBody.append( andOrButtonTemplate() );
				}

				this.$el.find( 'select' ).selectpicker();
			},

			autoSaveRouterSteps: function( event ) {
				var thisEl = this,
					routerSteps = {},
					i = 0,
					routeCount = 0;

				_.each( window.routerActions, function( routeAction, key ) {
					var actionModel;

					if ( 'undefined' !== typeof routeAction ) {
						actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

						if ( thisEl.model.get( 'stepID' ) === actionModel.get( 'stepID' ) ) {
							// Set each step options as per database records.
							_.each( thisEl.model.toJSON(), function( value, option ) {
								if ( 'undefined' === typeof window.routerActions[ key ].model ) {
									window.routerActions[ key ].set( option, value );
								} else {
									window.routerActions[ key ].model.set( option, value );
								}
							} );

							FlowMatticWorkflowEvents.trigger( 'saveRouterSteps', '', true );

							return false;
						}
					}
				} );
			},
		} );
	} );
}( jQuery ) );
