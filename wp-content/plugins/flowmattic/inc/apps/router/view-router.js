/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Router View.
		FlowMatticWorkflow.RouterView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-router-data-template' ).html() ),
			dataTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-action-data-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),
			routeNumber: 0,

			events: {
				'click .flowmattic-add-route-button': 'addBlankRoute',
				'click .flowmattic-edit-route': 'editRoute',
				'click .flowmattic-delete-route': 'deleteRoute',
				'click .flowmattic-rename-route': 'renameRoute',
				'click .flowmattic-clone-route': 'cloneRoute'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Set the route in draft mode.
				window.routeSaved = false;

				// Set the router action by default.
				this.model.set( 'action', 'conditionally_run' );

				// Listem to dynamic field popup, and append the custom data from routes.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicRouteOptionsHTML', this.generateDynamicFieldsHTML );

				this.listenTo( FlowMatticWorkflowEvents, 'pasteRouteSettings', this.pasteRouteSettings );

				this.listenTo( FlowMatticWorkflowEvents, 'refreshRouterViewsOnAddRemoveAction', this.refreshViews );
				this.listenTo( FlowMatticWorkflowEvents, 'removeRouterStepAction', this.updateStepNumbers );
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					submissionData = {},
					routerSteps = this.model.get( 'routerSteps' );

				this.$el.html( this.template( this.model.toJSON() ) );

				// Set router editor flag to false.
				window.routerEditorOpen = false;

				// Initialize the router action steps.
				window.routerActions = [];

				if ( ! _.isEmpty( routerSteps ) ) {
					_.each( routerSteps, function( stepData, routeLetter ) {
						var stepsCount = stepData.length;
						thisEl.addBlankRoute( 'route', stepsCount, routeLetter );
					} );
				} else {
					this.model.set( 'routerSteps', {} );
					thisEl.addBlankRoute( 'new' );
					thisEl.addBlankRoute( 'new' );
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				this.$el.find( 'select' ).selectpicker();

				// Clear tooltips.
				jQuery( 'body' ).find( '.tooltip.show' ).remove();

				return this;
			},

			refreshViews: function( routeActionView ) {
				var viewEl = routeActionView.$el,
					// prevStepIndex = ( 'undefined' !== typeof viewEl.prev().find( '.fm-workflow-step-application-title > strong' ) ) ? viewEl.prev().find( '.fm-workflow-step-application-title > strong' ).text().trim().replace( '.', '' ) : '',
					// nextSteps = viewEl.prev().nextAll(),
					// stepIndex,
					firstStepIndex = jQuery( '.router-steps' ).find( '.fm-workflow-steps > .flowmattic-action-step:first-child' ).find( '.fm-workflow-step-application-title > strong' ).text().trim().replace( '.', '' ),
					allSteps = jQuery( '.router-steps' ).find( '.fm-workflow-steps > .flowmattic-action-step' );

				if ( '' === firstStepIndex ) {
					return false;
				}

				// prevStepIndex = parseInt( prevStepIndex );
				// stepIndex = prevStepIndex + 1;

				window.refreshViewsAction = 'add';

				// jQuery( viewEl ).find( '.fm-workflow-step-application-title > strong' ).html( ( stepIndex + 1 ) + '. ' );

				// // Iterate over the next siblings and perform actions on them.
				// nextSteps.each( function( index, step ) {
				// 	jQuery( step ).find( '.fm-workflow-step-application-title > strong' ).html( stepIndex + '. ' );
				// 	stepIndex++;
				// } );

				firstStepIndex = parseInt( firstStepIndex );

				// Update the step numbers.
				setTimeout( function() {
					allSteps = jQuery( '.router-steps' ).find( '.fm-workflow-steps > .flowmattic-action-step' );

					allSteps.each( function( index, step ) {
						jQuery( step ).find( '.fm-workflow-step-application-title > strong' ).html( ( firstStepIndex + index ) + '. ' );
					} );
				}, 200 );
			},

			updateStepNumbers: function( routeActionView ) {
				this.refreshViews( routeActionView );

				// var viewEl = ( jQuery( routeActionView.$el ).closest( '.fm-workflow-step' ).length ) ? jQuery( routeActionView.$el ).closest( '.fm-workflow-step' ) : jQuery( routeActionView.$el ),
				// 	prevStepIndex = ( 'undefined' !== typeof viewEl.find( '.fm-workflow-step-application-title > strong' ) ) ? viewEl.find( '.fm-workflow-step-application-title > strong' ).text().trim().replace( '.', '' ) : '',
				// 	nextSteps = viewEl.nextAll(),
				// 	stepIndex;

				// prevStepIndex = parseInt( prevStepIndex );
				// stepIndex = prevStepIndex + 1;

				window.refreshViewsAction = 'remove';

				// jQuery( viewEl ).find( '.fm-workflow-step-application-title > strong' ).html( ( stepIndex + 1 ) + '. ' );

				// // Iterate over the next siblings and perform actions on them.
				// nextSteps.each( function( index, step ) {
				// 	jQuery( step ).find( '.fm-workflow-step-application-title > strong' ).html( stepIndex + '. ' );
				// 	stepIndex++;
				// } );
			},

			addBlankRoute: function( type, count, routeLetter ) {
				var thisEl = this,
					routerSteps = this.model.get( 'routerSteps' ),
					blankRouteTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-router-blank-route-data-template' ).html() ),
					routeCount = this.routeNumber,
					routeLetter = ( 'undefined' !== typeof routeLetter ) ? routeLetter : this.getRouteLetter( routeCount ),
					templateData,
					filtersView,
					modelAction,
					currentTitle,
					viewSettings;

				// Set the router route.
				window.routerRoute = routeLetter;

				if ( 'route' !== type ) {
					currentTitle = 'Route ' + routeLetter;
					templateData = blankRouteTemplate( { routeLetter: routeLetter, routeTitle: currentTitle, stepsCount: '1' } );

					modelAction = new FlowMatticWorkflow.Action();
					modelAction.set( 'action', 'continue_if' );
					modelAction.set( 'application', 'filter' );
					modelAction.set( 'routerRoute', routeLetter );

					viewSettings = {
						model: modelAction,
						collection: FlowMatticWorkflowSteps
					};

					filtersView = new FlowMatticWorkflow.FilterView( viewSettings );

					if ( 'undefined' === routerSteps[routeLetter] ) {
						routerSteps[routeLetter] = {};
					}

					// Assign the filter step as first action.
					window.routerActions.push( filtersView );

					routerSteps[routeLetter] = [filtersView.model.toJSON()];

					this.model.set( 'routerSteps', routerSteps );
					FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routerSteps', routerSteps, thisEl );
				} else {
					this.routeNumber = routeLetter.charCodeAt(0) - 65;
					currentTitle = this.model.get( 'routeTitle_' + routeLetter );
					currentTitle = ( 'undefined' === typeof currentTitle || '' === currentTitle ) ? 'Route ' + routeLetter : currentTitle;
					templateData = blankRouteTemplate( { routeLetter: routeLetter, routeTitle: currentTitle, stepsCount: count } );
				}

				jQuery( this.$el ).find( '.flowmattic-routers-data' ).append( templateData );

				this.routeNumber++;
			},

			editRoute: function( event ) {
				var thisEl = this,
					currentRoute = jQuery( event.target ).closest( '.flowmattic-router-unit' ),
					routers = this.model.get( 'routerSteps' ),
					routerStep,
					routeLetter = jQuery( currentRoute ).data( 'route' ),
					routerEditorWrap = jQuery( 'body' ).find( '.flowmattic-route-editor' ),
					currentTitle = this.model.get( 'routeTitle_' + routeLetter ),
					stepIndex = this.model.get('stepIndex');

				currentTitle = ( 'undefined' === typeof currentTitle || '' === currentTitle ) ? 'Route ' + routeLetter : currentTitle;

				// Get the step.
				routerSteps = routers[ routeLetter ];

				// Set the router view for global access.
				window.routerEl = this;

				// Set router editor flag to true.
				window.routerEditorOpen = true;

				// Set the router route.
				window.routerRoute = routeLetter;

				// Empty the global router actions.
				window.routerActions = [];

				// Set the route in draft.
				window.routeSaved = false;

				_.each ( routerSteps, function( routerStep, key ) {
					var modelAction,
						viewSettings,
						appClass,
						appView,
						routerStepView,
						routerHTML;

					modelAction = new FlowMatticWorkflow.Action();
					modelAction.set( 'action', routerStep.action );
					modelAction.set( 'application', routerStep.application );
					modelAction.set( 'routerRoute', routeLetter );

					// Set each step options as per database records.
					_.each( routerStep, function( value, option ) {
						if ( 'string' === typeof value ) {
							value = FlowMatticWorkflow.FlowMatticStripslashes( value );
						}

						modelAction.set( option, value );
					} );

					// If the eid is not present, generate one.
					if ( 'undefined' === typeof modelAction.get( 'eid' ) ) {
						modelAction.set( 'eid', FlowMatticWorkflow.randomString( 5 ) );
					}

					if ( 'api' === routerStep.application ) {
						var authSelected = routerStep.authentication,
							contentTypeSelected = routerStep.content_type,
							apiEndpoint = routerStep.api_endpoint,
							customJSON = routerStep.custom_json,
							conditional_execution = routerStep.conditional_execution,
							simple_response = routerStep.simple_response;

						modelAction.set( 'contentType', ( 'undefined' !== typeof contentTypeSelected ) ? contentTypeSelected : 'json' );
						modelAction.set( 'authType', ( 'undefined' !== typeof authSelected ) ? authSelected : 'no' );
						modelAction.set( 'endpointURL', ( 'undefined' !== typeof apiEndpoint ) ? apiEndpoint : '' );
						modelAction.set( 'customJSON', ( 'undefined' !== typeof customJSON ) ? customJSON : '' );
						modelAction.set( 'conditional_execution', ( 'undefined' !== typeof conditional_execution ) ? conditional_execution : '' );
						modelAction.set( 'simple_response', ( 'undefined' === typeof simple_response ) ? 'Yes' : simple_response );
					}

					viewSettings = {
						model: modelAction,
						collection: FlowMatticWorkflowSteps
					};

					appClass = ( 'api' === routerStep.application ) ? 'action' : routerStep.application;
					appView = FlowMatticWorkflow.UCWords( appClass ).replaceAll( ' ', '_' ) + 'View';

					if ( 'undefined' !== typeof routerStep.application ) {
						// routerStepView = new FlowMatticWorkflow[ appView ]( viewSettings );
						routerStepView = new FlowMatticWorkflow.ActionView( viewSettings );

						// Assign the filter step as first action.
						window.routerActions.push( routerStepView );

						// Update the route heading.
						routerEditorWrap.find( '.router-heading h3' ).html( currentTitle );

						// Set the Router editor active.
						routerEditorWrap.addClass( 'editor-active' );

						// Append the action step.
						routerHTML = routerStepView.render().el;
						jQuery( routerHTML ).find( '.fm-workflow-step-application-title > strong' ).text( ( stepIndex + key ) + '. ' );
						routerEditorWrap.find( '.fm-workflow-steps' ).append( routerHTML );

						// If first step in router, remove the action and app selector and adjust spacing.
						if ( 0 === key ) {
							routerEditorWrap.find( '.fm-workflow-steps > .flowmattic-action-step' ).find( '.fm-workflow-action-select' ).remove();
							routerEditorWrap.find( '.fm-workflow-steps > .flowmattic-action-step' ).find( '.fm-workflow-step-close' ).remove();
							routerEditorWrap.find( '.fm-workflow-steps > .flowmattic-action-step' ).find( '.fm-workflow-action-data' ).addClass('border-0 mt-0 pt-0' );
						}

						// Set the background scrolling off.
						jQuery( 'body' ).addClass( 'router-editor-active' );

						// Handle the editor closing.
						jQuery( 'body' ).find( '.router-editor-close' ).on( 'click', function( e ) {
							thisEl.closeRouteEditor();
						} );
					}
				} );

				// Clear tooltips.
				jQuery( 'body' ).find( '.tooltip.show' ).remove();
			},

			deleteRoute: function( event ) {
				var thisEl = this,
					currentRoute = jQuery( event.target ).closest( '.flowmattic-router-unit' ),
					routers = this.model.get( 'routerSteps' ),
					routeLetter = jQuery( currentRoute ).data( 'route' ),
					routeCount = jQuery( this.$el ).find( '.flowmattic-router-unit' ).length;

				if ( 1 === routeCount ) {
					this.swalWithBootstrapButtons.fire( {
						title: 'Deleting last route will also remove the Router step. Are you sure, you want to delete?',
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
							currentRoute.slideUp( 'slow' ).remove();
							FlowMatticWorkflowEvents.trigger( 'silentRemoveStep', thisEl );

							// Autosave workflow.
							FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
						}
					} );
				} else {
					this.swalWithBootstrapButtons.fire( {
						title: 'Are you sure?',
						html: "You're about to delete the route <strong>" + routeLetter + "</strong>.<br> You won't be able to revert this!",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: 'Yes, delete it!',
						showLoaderOnConfirm: true,
						preConfirm: () => {
							return []
						}
					} ).then( ( result ) => {
						if ( result.isConfirmed ) {
							currentRoute.slideUp( 'slow' ).remove();
							delete routers[routeLetter];

							// Update routes.
							thisEl.model.set( 'routerSteps', routers );
							FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routerSteps', routers, thisEl );

							// Update route title.
							thisEl.model.set( 'routeTitle_' + routeLetter, '' );
							FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routeTitle_' + routeLetter, '', thisEl );

							// Autosave workflow.
							FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
						}
					} );
				}

				// Clear tooltips.
				jQuery( 'body' ).find( '.tooltip.show' ).remove();
			},

			renameRoute: function( event ) {
				var thisEl = this,
					currentRoute = jQuery( event.target ).closest( '.flowmattic-router-unit' ),
					routers = this.model.get( 'routerSteps' ),
					routeLetter = jQuery( currentRoute ).data( 'route' ),
					currentTitle = this.model.get( 'routeTitle_' + routeLetter );

				currentTitle = ( 'undefined' === typeof currentTitle || '' === currentTitle ) ? 'Route ' + routeLetter : currentTitle;

				this.swalWithBootstrapButtons.fire( {
					title: 'Rename Route ' + routeLetter,
					input: 'textarea',
					inputLabel: 'Enter Route Title',
					inputValue: currentTitle,
					inputPlaceholder: 'Enter route title',
					inputAttributes: {
						autocapitalize: 'on',
						autocorrect: 'off'
					},
					preConfirm: ( routeTitle ) => {
						if ( routeTitle ) {
							thisEl.model.set( 'routeTitle_' + routeLetter, `${routeTitle}` );
							FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routeTitle_' + routeLetter, `${routeTitle}`, thisEl );

							jQuery( event.currentTarget ).closest( 'div[data-route="' + routeLetter + '"]' ).find( '.router-unit-title' ).text( `${routeTitle}` );
						}
					}
				} );
			},

			cloneRoute: function( event ) {
				var thisEl = this,
					currentRoute = jQuery( event.target ).closest( '.flowmattic-router-unit' ),
					routerSteps = thisEl.model.get( 'routerSteps' ),
					routeLetter = jQuery( currentRoute ).data( 'route' ),
					currentTitle = thisEl.model.get( 'routeTitle_' + routeLetter ),
					routeTitle,
					routeStepToClone = routerSteps[ routeLetter ],
					newRouteLetter = '',
					newRoute;

				// Add a new blank route.
				thisEl.addBlankRoute( 'new' );

				// Get the new route letter.
				newRouteLetter = window.routerRoute;

				// Get the new route steps.
				routerSteps = thisEl.model.get( 'routerSteps' );
				newRoute = routerSteps[ newRouteLetter ];

				// Rename the new route title.
				routeTitle = currentTitle + ' [CLONED] ';
				thisEl.model.set( 'routeTitle_' + newRouteLetter, `${routeTitle}` );
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routeTitle_' + newRouteLetter, `${routeTitle}`, thisEl );

				// Do the required replacements for route letters.
				_.each( routeStepToClone, function( routeStep, index ) {
					var tempStep = JSON.stringify( routeStep );

					tempStep = tempStep.replaceAll( '{route' + routeLetter, '{route' + newRouteLetter );
					tempStep = JSON.parse( tempStep );
					routeStep = tempStep;

					routeStep.routerRoute = newRouteLetter;

					newRoute[ index ] = routeStep;
				} );

				// Assign the new route actions.
				routerSteps[newRouteLetter] = newRoute;

				// Update the new route to view.
				thisEl.model.set( 'routerSteps', routerSteps );
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routerSteps', routerSteps, thisEl );

				// Auto save the route in view.
				FlowMatticWorkflowEvents.trigger( 'autoSaveRouterSteps' );

				// Update the new route title in template.
				jQuery( thisEl.$el ).find( 'div[data-route="' + newRouteLetter + '"]' ).find( '.router-unit-title' ).text( `${routeTitle}` );

				// Update steps count.
				jQuery( thisEl.$el ).find( '[data-route="' + window.routerRoute + '"]' ).find( '.router-unit-step-counter' ).html( 'Contain ' + newRoute.length + ' steps');

				// Announce the news!
				this.swalWithBootstrapButtons.fire( {
					title: 'Route is cloned!',
					html: "Your route <strong>" + routeLetter + "</strong> is cloned as route <strong>" + newRouteLetter + "</strong>.<br> Please make sure to edit and save the route once.",
					icon: 'success',
					showConfirmButton: false,
					timer: 3500,
					timerProgressBar: true
				} );
			},

			closeRouteEditor: function() {
				var routerEditorWrap = jQuery( 'body' ).find( '.flowmattic-route-editor' );

				// Clear tooltips.
				jQuery( 'body' ).find( '.tooltip.show' ).remove();

				jQuery( 'body' ).removeClass( 'router-editor-active' );
				routerEditorWrap.removeClass( 'editor-active' );
				routerEditorWrap.find( '.router-heading h3, .fm-workflow-steps' ).html( '' );

				// Set router editor flag to false.
				window.routerEditorOpen = false;

				// Empty the global router actions.
				window.routerActions = [];
			},

			generateDynamicFieldsHTML: function( application, currentInput, stepEl, lastIndex ) {
				var routeOptions = '',
					routerActions = window.routerActions,
					routeLetter = window.routerRoute,
					capturedData = [],
					selectTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-select-router-dropdown-template' ).html() ),
					optionsHTML = '',
					thisStepID = stepEl.model.get( 'stepID' ),
					models = routerActions,
					stopLoop = false;

				if ( ! window.routerEditorOpen ) {
					return false;
				}

				// Loop through all models till the current step is reached.
				_.each ( models, function( child, index ) {
					if ( 'undefined' === typeof child ) {
						return false;
					}

					var model = ( 'undefined' !== typeof child.model ) ? child.model : child,
						stepID = model.get( 'stepID' ),
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
					if ( thisStepID === stepID ) {
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

					// If title is empty, get the action.
					var currentAppAction = model.get( 'action' );
					if ( '' === stepTitle && '' !== currentAppAction ) {
						if ( 'action' === model.get( 'type' ) ) {
							stepTitle = ( 'undefined' !== typeof actionApps[ appType ] ) ? actionApps[ appType ].actions[ currentAppAction ].title : otherActionApps[ appType ].actions[ currentAppAction ].title;
						} else {
							stepTitle = ( 'undefined' !== typeof triggerApps[ appType ] ) ? triggerApps[ appType ].triggers[ currentAppAction ].title : otherTriggerApps[ appType ].triggers[ currentAppAction ].title;
						}
					}

					capturedData[ stepID ] = { index: lastIndex + index + 1, name: appType, title: stepTitle, data: objectData, routeLetter: routeLetter };
					optionsHTML += selectTemplate( { options: capturedData[ stepID ] } );
				} );

				// Set the dynamic tags for router actions.
				window.dynamicRouteOptionsHTML = optionsHTML;
			},

			pasteRouteSettings: function( stepID ) {
				var appAction = '',
					appActionTitle = '';

				_.each( window.routerActions, function( routeAction ) {
					var actionModel,
						routerStepView,
						stepTitle,
						routerStepViewEl,
						paramsData,
						actionData;

					if ( 'undefined' !== typeof routeAction ) {
						actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

						if ( stepID === actionModel.get( 'stepID' ) ) {
							routerStepViewEl = jQuery( '.fm-workflow-step[step-id="' + stepID + '"]' );
							routerStepView = routeAction;

							paramsData = jQuery( routerStepView.render().$el ).find( '.fm-api-request-parameters-body' );
							actionData = jQuery( routerStepView.render().$el ).closest( '.fm-workflow-action-data' ).html();
							routerStepView.render();

							appAction = routerStepView.model.get( 'action' );
							routerStepViewEl.closest( '.fm-workflow-step' ).find( '.workflow-application-events' ).selectpicker( 'val', appAction );
							routerStepViewEl.closest( '.fm-workflow-step' ).find( '.workflow-application-events' ).trigger( 'change' );

							appActionTitle = routerStepViewEl.closest( '.fm-workflow-step' ).find( '.workflow-application-events:selected' ).text();
							stepTitle      = ( 'undefined' !== typeof routerStepView.model.get( 'stepTitle' ) ) ? routerStepView.model.get( 'stepTitle' ) : '';

							if ( '' !== stepTitle ) {
								routerStepViewEl.closest( '.fm-workflow-step' ).find( '.fm-application-title' ).text( stepTitle );
								routerStepViewEl.closest( '.fm-workflow-step' ).find( '.fm-application-action' ).text( '' );
							} else {
								routerStepView.model.set( 'stepTitle', '' );
								routerStepViewEl.closest( '.fm-workflow-step' ).find( '.fm-application-action' ).text( appActionTitle );
							}

							if ( routerStepViewEl.find( '.fm-api-request-parameters-body' ).length ) {
								routerStepViewEl.find( '.fm-api-request-parameters-body' ).html('').html( paramsData );
								routerStepViewEl.find( '.fm-api-request-parameters-body' ).find( 'input' ).trigger( 'change' );
							} else {
								routerStepViewEl.find( '.fm-workflow-action-data' ).html( actionData );
								routerStepViewEl.find( '.fm-workflow-action-data' ).find( 'input' ).trigger( 'change' );
							}

							return false;
						}
					}
				} );
			},

			getRouteLetter: function( number ) {
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
