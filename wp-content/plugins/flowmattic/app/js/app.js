/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps, FlowMatticWorkflowViewManager */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

// Events
var FlowMatticWorkflowEvents = _.extend( {}, Backbone.Events );

( function( $ ) {

	jQuery( document ).ready( function() {
		const getCircularReplacer = () => {
			const seen = new WeakSet();
			return ( key, value ) => {
				if ( typeof value === 'object' && value !== null ) {
					if ( seen.has( value ) ) {
						return;
					}
					seen.add(value);
				}
				return value;
			};
		};

		// Workflow App View.
		FlowMatticWorkflow.AppView = Backbone.View.extend( {
			el: jQuery( '#flowmattic-workflow-container' ),

			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-app-template' ).html() ),
			dataTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-workflow-trigger-data-template' ).html() ),
			webhookResponse: [],
			apiResponse: [],

			events: {
				'click .flowmattic-workflow-save-button': 'saveWorkflow',
				'click .fm-workflow-step-header': 'toggleStepAccordion',
				'click .flowmattic-router-save-button': 'saveRouterSteps',
				'change *': 'autosaveWorkflow',
				'click .btn-do-test-execution': 'openTestRunModal'
			},

			autosaveWorkflow: function() {
				if ( window.routerEditorOpen ) {
					FlowMatticWorkflowEvents.trigger( 'autoSaveRouterSteps' );
				} else {
					setTimeout( function() {
						FlowMatticWorkflowEvents.trigger( 'processAutosave' );
					}, 500 );
				}
			},

			initialize: function() {
				this.stepsData;
				this.model = new FlowMatticWorkflow.Action( {
					type: 'trigger'
				} );

				window.autosaveInProcess = false;

				// Listen for new steps.
				this.listenTo( this.collection, 'add', this.addWorkflowStep );

				// Trigger autosave.
				this.listenTo( FlowMatticWorkflowEvents, 'triggerAutosave', this.autosaveWorkflow );

				// Autosave.
				// this.listenTo( FlowMatticWorkflowEvents, 'processAutosave', this.processAutosave );

				// Listen to save workflow draft.
				this.listenTo( FlowMatticWorkflowEvents, 'saveWorkflowDraft', this.saveWorkflowDraft );

				// Listen to save workflow.
				this.listenTo( FlowMatticWorkflowEvents, 'saveWorkflow', this.saveWorkflow );

				// Autosave router steps data.
				this.listenTo( FlowMatticWorkflowEvents, 'saveRouterSteps', this.saveRouterSteps );

				// Update the dynamic tags on add or remove action steps.
				this.listenTo( FlowMatticWorkflowEvents, 'refreshViewsOnAddRemoveAction', this.refreshViewsOnAddRemoveAction );

				if ( 'undefined' !== typeof window.workflowSteps ) {
					this.stepsData = window.workflowSteps;
				}

				if ( 'undefined' !== typeof this.el ) {
					this.render();
				}
			},

			render: function() {
				var thisEl = this,
					model,
					prevModel,
					stepView,
					allSteps = [],
					triggerSet = false;

				window.refreshViewsAction = 'add';

				if ( this.stepsData ) {
					_.each( this.stepsData, function( step, index ) {
						if ( ! triggerSet && 'trigger' !== step.type ) {
							// This is action before trigger. Do nothing.
						} else {
							if ( 'action' === step.type && 'undefined' === typeof step.application ) {
								// Action with no application selected. Do nothing.
							} else if ( triggerSet && 'trigger' === step.type ) {
								// This is trigger inbetween actions. Do nothing.
							} else {
								triggerSet = true;
								allSteps.push( step );
							}
						}
					} );

					_.each( allSteps, function( step, index ) {
						model = new FlowMatticWorkflow.Action();

						// Set each step options as per database records.
						_.each( step, function( value, option ) {
							if ( 'string' === typeof value ) {
								value = FlowMatticWorkflow.FlowMatticStripslashes( value );
							}

							model.set( option, value );
						} );

						if ( 'undefined' !== typeof thisEl.collection.models[ index - 1 ] ) {
							prevModel = thisEl.collection.models[ index - 1 ];
							stepView  = FlowMatticWorkflowViewManager.getView( prevModel.get( 'eid' ) );

							if ( 'undefined' !== stepView ) {
								model.set( 'view', stepView );
							}
						}

						if ( 'undefined' !== typeof model.get( 'application' ) ) {
							thisEl.collection.add( [model] );
						} else {
							thisEl.collection.add( [thisEl.model] );
						}
					} );
				} else {
					// Add trigger
					this.collection.add( [this.model] );
					
					// Add blank action.
					model = new FlowMatticWorkflow.Action();
					model.set( 'newAction', true );
					this.collection.add( [model] );
					stepView  = FlowMatticWorkflowViewManager.getView( model.get( 'eid' ) );
				}

				this.$el.find( 'select[name="workflow-application"]' ).selectpicker();
				this.$el.find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

				return this;
			},

			refreshViewsOnAddRemoveAction: function( stepIndex, removeAction ) {
				var replaceTags = {},
					replaceViews = [],
					reversedObject;

				if ( window.routerEditorOpen ) {
					return false;
				}

				_.each( this.collection.models, function( model, index ) {
					var appType = model.get( 'application' ),
						currentStepIndex = index + 1,
						replaceTag,
						replaceWithTag;

					if ( currentStepIndex >= ( stepIndex + 1 ) ) {
						replaceTag     = appType + stepIndex;
						replaceWithTag = appType + currentStepIndex;

						if ( 'undefined' !== typeof removeAction ) {
							replaceTags[ replaceWithTag ] = replaceTag;
							window.refreshViewsAction = 'remove';
						} else {
							replaceTags[ replaceTag ] = replaceWithTag;
							window.refreshViewsAction = 'add';
						}

						replaceViews.push( model.get( 'eid' ) );

						stepIndex = currentStepIndex;
					}
				} );

				// Assign the replace tags according to step index.
				reversedObject = replaceTags;

				// If refresh action is triggered with add action, start replacing tags from the last action step to avoid the misplacement.
				if ( 'undefined' === typeof removeAction ) {
					reversedObject = Object.entries(replaceTags).reverse().reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {});
				}

				// Loop through the views after added/removed action step.
				_.each( replaceViews, function( stepViewID ) {
					var stepView  = FlowMatticWorkflowViewManager.getView( stepViewID ),
						actionData = stepView.model.toJSON();

					// Remove the captured data as it has no dynamic tags to replace.
					delete actionData.capturedData;

					// Remove other standard object items, that has no dynamic tags to replace.					
					delete actionData.action;
					delete actionData.application;
					delete actionData.conditional_execution;
					delete actionData.eid;
					delete actionData.stepID;
					delete actionData.stepTitle;
					delete actionData.type;
					delete actionData.view;
					delete actionData.stepIndex;
					delete actionData.set_parameters;
					delete actionData.set_headers;

					// Loop through the tags to be replaced.
					_.each( reversedObject, function( replaceWithTag, replaceTag ) {

						// Loop through the action step data.
						_.each ( actionData, function( value, key ) {
							var newValue = '';

							if ( 'undefined' !== typeof value ) {
								if ( 'string' === typeof value && '' !== value ) {
									if ( -1 !== value.indexOf( replaceTag ) ) {
										// Replace old dynamic tag with new one.
										newValue = value.replaceAll( replaceTag, replaceWithTag );

										// Update data to model.
										stepView.model.set( key, newValue );

										// Set parent model attribute.
										FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', key, newValue, stepView.model );

										// Set flag to true.
										modelUpdated = true;
									}
								}

								// If value is object.
								if ( 'object' === typeof value ) {
									if ( 'routerSteps' === key ) {
										// Loop through router steps.
										_.each ( value, function( routeSteps, routeLetter ) {
											var tempValue = JSON.stringify( routeSteps );

											tempValue = tempValue.replaceAll( '{' + replaceTag, '{' + replaceWithTag );
											value[ routeLetter ] = JSON.parse( tempValue );
										} );
									} else {
										_.each ( value, function( objectValue, objectKey ) {
											if ( 'object' === typeof objectValue ) {
												var tempValue = JSON.stringify( objectValue );
												tempValue = tempValue.replaceAll( replaceTag, replaceWithTag );
												value[ objectKey ] = JSON.parse( tempValue );
											} else if ( '' !== objectValue && -1 !== objectValue.indexOf( replaceTag ) ) {
												// Replace old dynamic tag with new one.
												value[ objectKey ] = objectValue.replaceAll( replaceTag, replaceWithTag );
											}
										} );
									}

									// Update data to model.
									stepView.model.set( key, value );

									// Set parent model attribute.
									FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', key, value, stepView.model );

									// Set flag to true.
									modelUpdated = true;
								}
							}
						} );
					} );

					// Once all the replacements are done, re-render the view once.
					stepView.render();
				} );
			},

			processAutosave: function() {
				var thisEl = this,
					workflowSteps = {},
					workflowSettings = {},
					name,
					workflowId,
					folder,
					description,
					workflowStatus,
					userEmail,
					webhookQueue,
					capturedResponses,
					selectedResponse;

				if ( window.autosaveInProcess ) {
					return false;
				}

				window.autosaveInProcess = true;

				if ( ! jQuery( 'body' ).hasClass( 'flowmattic-loaded' ) ) {
					return false;
				}

				if ( 'undefined' !== typeof window.autosaveAjaxInProgress && false !== window.autosaveAjaxInProgress ) {
					window.autosaveAjaxInProgress.abort();
				}

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

				// Get the Workflow name.
				name = jQuery( 'body' ).find( '.workflow-input.workflow-name' ).val();

				// Fix the workflow name if it has special characters.
				name = encodeURIComponent( name );

				// Get the Workflow folder.
				folder = jQuery( 'body' ).find( '.sidebar-workflow-folder' ).val();

				// Get the Workflow description.
				description = jQuery( 'body' ).find( '.sidebar-workflow-description' ).val();

				// Get workflow ID.
				workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				// Get the authentication key.
				fmAuthKey = jQuery( 'body' ).find( '.workflow-input.workflow-auth-key' ).val();

				// Get user email.
				userEmail = jQuery( 'body' ).find( '.workflow-input.workflow-manager' ).val();

				// Get webhook queue option.
				webhookQueue = jQuery( 'body' ).find( '#sidebar-workflow-request-queue' ).val();

				// Get captured responses.
				capturedResponses = window.capturedResponses;

				// Get selected response.
				selectedResponse = window.selectedResponse;

				// Check workflow status.
				workflowStatus = ( jQuery( 'body' ).find( '.workflow-onoff-switch' ).is( ':checked' ) ) ? 'on' : 'off';

				// Add to settings.
				workflowSettings.name = name;
				workflowSettings.folder = folder;
				workflowSettings.description = description;
				workflowSettings.workflow_id = workflowId;
				workflowSettings.workflow_auth_key = fmAuthKey;
				workflowSettings.status = workflowStatus;
				workflowSettings.user_email = userEmail;
				workflowSettings.webhook_queue = webhookQueue;
				workflowSettings.capturedResponses = FlowMatticWorkflow.base64EncodeUnicode( capturedResponses );
				workflowSettings.selectedResponse = selectedResponse;

				this.collection.each( function( model, index ) {
					var settings = model.toJSON();

					if ( 'undefined' !== typeof settings.routerRoute ) {
						return false;
					}

					delete settings.eid;
					delete settings.view;
					delete settings.applicationEvents;

					workflowSteps[ index ] = settings;
				} );

				workflowSteps    = JSON.stringify( workflowSteps, getCircularReplacer() );
				workflowSettings = JSON.stringify( workflowSettings );

				window.autosaveAjaxInProgress = Backbone.ajax( {
					url: ajaxurl,
					data: { workflow: btoa( unescape( encodeURIComponent( workflowSteps ) ) ), settings: btoa( workflowSettings ), action: 'flowmattic_save_workflow', workflow_nonce: flowMatticAppConfig.workflow_nonce },
					type: 'POST',
					dataType: 'json',
					success: function( result ) {
						if ( 1 === result.status ) {
							Toast.fire( {
								icon: 'success',
								title: 'Autosave successful'
							} );

							window.autosaveAjaxInProgress = false;
							window.draftSaved = false;

							setTimeout( function() {
								window.autosaveInProcess = false;
							}, 500 );
						}
					}
				} );
			},

			addWorkflowStep: function( step ) {
				var view,
					viewSettings = {
						model: step,
						collection: FlowMatticWorkflowSteps
					},
					type,
					viewEl,
					thisEl = this,
					cid,
					stepView;

				type = step.get( 'type' );

				switch ( type ) {

				case 'trigger':

					cid = 'el' + this.collection.length + 1;
					view = new FlowMatticWorkflow.TriggerView( viewSettings );
					view.model.set( 'eid', cid );

					this.$el.append( view.render().el );

					// Add the copy icon for webhook url.
					if ( ! jQuery( 'body' ).find( '.webhook-url-wrapper' ).length ) {
						jQuery( this.$el ).find( '.webhook-url input' ).wrap( '<div class="webhook-url-wrapper"></div>' );
						jQuery( this.$el ).find( '.webhook-url input' ).attr( 'title', 'Click to copy' );
					}

					this.$el.find( '[data-toggle="tooltip"], .webhook-url-wrapper input' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

					break;

				case 'action':
					view = new FlowMatticWorkflow.ActionView( viewSettings );
					cid = 'el' + this.collection.length + 1;

					FlowMatticWorkflowViewManager.addView( cid, view );

					view.model.set( 'eid', cid );

					if ( this.$el.find( '.step-new' ).length ) {
						this.$el.find( '.step-new' ).find( '.fm-workflow-step-close' ).click();
					}

					if ( step.get( 'view' ) ) {
						viewEl = step.get( 'view' );
						if ( 'undefined' !== typeof step.get( 'insertBefore' ) ) {
							viewEl.$el.before( view.render().el );
							step.unset( 'insertBefore' );
						} else {
							viewEl.$el.after( view.render().el );
						}
						step.unset( 'view' );
					} else if ( 'undefined' !== step.get( 'insertAfter' ) ) {
						this.$el.find( '.fm-workflow-trigger' ).after( view.render().el );
						step.unset( 'insertAfter' );
					} else {
						this.$el.find( '.fm-workflow-steps' ).append( view.render().el );
					}

					this.$el.find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

					// Enable the drag-n-drop interface for action steps.
					if ( ! window.routerEditorOpen ) {
						let draggableActionSteps = this.$el.find( '.fm-workflow-steps' );
						let startPosition;
						let startStepId;
						let oldSortedIds;

						draggableActionSteps.sortable({
							placeholder: {
								element: function() {
									const placeholderElement = jQuery('<div style="height:106px;width: 100%;z-index: 1;border-style: dashed !important;background-color: #e4efff!important;" class="flowmattic-action-step-placeholder ui-sortable-placeholder border border-2 border-primary position-relative d-flex align-items-center justify-content-center fs-6 text-center flex-column text-primary">DROP HERE</div>');
									return placeholderElement[0];
								},
								update: function(container, p) {
									return;
								},
							},
							handle: '.drag-action-step',
							axis: 'y',
							start: (event, ui) => {
								startPosition = ui.item.index();
								startStepId = ui.item.prev().attr('data-cid');
								oldSortedIds = draggableActionSteps.sortable('toArray', { attribute: 'data-cid' });
							},
							update: (event, ui) => {
								var droppedStepId = ui.item.prev().attr('data-cid'),
									currentStepId = ui.item.attr('data-cid' ),
									finalStepId,
									startFrom,
									updateUntil,
									updateSteps = {};

								// Get all the cid's after dragging complete.
								let sortedIds = draggableActionSteps.sortable('toArray', { attribute: 'data-cid' });

								// Get the index of dropped step.
								const endPosition = ui.item.index();

								// Check the direction.
								const dragDirection = startPosition > endPosition ? 'up' : 'down';

								if ( 'up' === dragDirection ) {
									// Get the finalStepId to render after.
									finalStepId = droppedStepId;

									startFrom   = endPosition + 1;
									updateUntil = startPosition + 1;

									// Refresh views after this step.
									FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', endPosition, true );

									// Create an array of steps to update.
									updateSteps[ oldSortedIds[ startPosition ] ] = startFrom;

									for ( var i = startPosition; i >= startFrom; i-- ) {
										let index = i - 1;
										updateSteps[ oldSortedIds[ index ] ] = i + 1;
									}
								} else {
									// Get the finalStepId to render after.
									finalStepId = startStepId;

									updateUntil = endPosition + 1;
									startFrom   = startPosition + 1;

									// Refresh views after this step.
									FlowMatticWorkflowEvents.trigger( 'refreshViewsOnAddRemoveAction', startPosition, true );

									// Create an array of steps to update.
									updateSteps[ oldSortedIds[ startPosition ] ] = updateUntil;

									for ( var i = startFrom; i < updateUntil; i++ ) {
										let index = i;
										updateSteps[ oldSortedIds[ index ] ] = i;
									}
								}

								// If the first step is dragged, set the current previous id as final.
								if ( 'undefined' === typeof startStepId ) {
									finalStepId = droppedStepId;
								}

								// If the step is dropped after trigger, set the current step id as final.
								if ( 1 === endPosition && 'undefined' === typeof droppedStepId ) {
									finalStepId = currentStepId;
								}

								// Re-order the actions.
								this.reorderCollection( sortedIds, finalStepId, updateSteps );
							}
						});
					}

					break;
				}
			},

			reorderCollection: function( sortedIds, finalStepId, updateSteps ) {
				var thisEl = this,
					stepView,
					oldCollection = [],
					newCollection = [];

				// Keep the old collection for backup.
				oldCollection = thisEl.collection.models;

				// Get the trigger view and add it at top.
				const trigger = this.collection.findWhere( { type: 'trigger' } );
				newCollection.push( trigger );

				// Remove the trigger from the sorted actions.
				sortedIds = sortedIds.filter((index) => index !== '');

				// Do the dynamic tags adjustments.
				// Loop through the views after re-ordered action step.
				_.each( sortedIds, function( eid ) {
					var stepView   = FlowMatticWorkflowViewManager.getView( eid ),
						actionData = stepView.model.toJSON();

					// Remove the captured data as it has no dynamic tags to replace.
					delete actionData.capturedData;

					// Remove other standard object items, that has no dynamic tags to replace.					
					delete actionData.action;
					delete actionData.application;
					delete actionData.conditional_execution;
					delete actionData.eid;
					delete actionData.stepID;
					delete actionData.stepTitle;
					delete actionData.type;
					delete actionData.view;
					delete actionData.stepIndex;
					delete actionData.set_parameters;
					delete actionData.set_headers;

					// Loop through the tags to be replaced.
					// _.each( reversedObject, function( replaceWithTag, replaceTag ) {
					_.each( updateSteps, function( replaceIndex, replaceStepID ) {
						var replaceStepView = FlowMatticWorkflowViewManager.getView( replaceStepID ),
							application = replaceStepView.model.get( 'application' ),
							replaceTagIndex = oldCollection.indexOf( replaceStepView.model ),
							replaceTag = '{' + application + ( replaceTagIndex + 1 ),
							replaceWithTag = '{' + application + replaceIndex;

						// Loop through the action step data.
						_.each ( actionData, function( value, key ) {
							var newValue = '';

							if ( 'undefined' !== typeof value ) {
								if ( 'string' === typeof value && '' !== value ) {
									if ( -1 !== value.indexOf( replaceTag ) ) {
										// Replace old dynamic tag with new one.
										newValue = value.replaceAll( replaceTag, replaceWithTag );

										// Update data to model.
										stepView.model.set( key, newValue );

										// Set parent model attribute.
										FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', key, newValue, stepView.model );

										// Set flag to true.
										modelUpdated = true;
									}
								}

								// If value is object.
								if ( 'object' === typeof value ) {
									if ( 'routerSteps' === key ) {
										// Loop through router steps.
										_.each ( value, function( routeSteps, routeLetter ) {
											var tempValue = JSON.stringify( routeSteps );

											tempValue = tempValue.replaceAll( '{' + replaceTag, '{' + replaceWithTag );
											value[ routeLetter ] = JSON.parse( tempValue );
										} );
									} else {
										_.each ( value, function( objectValue, objectKey ) {
											if ( 'object' === typeof objectValue ) {
												var tempValue = JSON.stringify( objectValue );
												tempValue = tempValue.replaceAll( replaceTag, replaceWithTag );
												value[ objectKey ] = JSON.parse( tempValue );
											} else if ( '' !== objectValue && -1 !== objectValue.indexOf( replaceTag ) ) {
												// Replace old dynamic tag with new one.
												value[ objectKey ] = objectValue.replaceAll( replaceTag, replaceWithTag );
											}
										} );
									}

									// Update data to model.
									stepView.model.set( key, value );

									// Set parent model attribute.
									FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', key, value, stepView.model );

									// Set flag to true.
									modelUpdated = true;
								}
							}
						} );
					} );

					// Once all the replacements are done, re-build the collections.
					newCollection.push( stepView.model );
				} );

				// Assign the new collection back to reset indexes.
				thisEl.collection.models = newCollection;

				// Render all the models affected.
				let startRender = false;
				_.each( sortedIds, function( eid ) {
					startRender = startRender ? startRender : finalStepId === eid;
					// Re-render only steps affected.
					if ( startRender ) {
						window.refreshViewsAction = 'add';

						stepView = FlowMatticWorkflowViewManager.getView( eid );
						stepView.render();
					}
				} );
			},

			saveWorkflowDraft: function() {
				var workflowSteps = [];

				this.collection.each( function( model ) {
					var settings = model.toJSON();
					delete settings.eid;
					workflowSteps.push( settings );
				} );

				// TODO: for debugging.
				// console.log( workflowSteps );
			},

			saveWorkflow: function( event ) {
				var workflowSteps = {},
					workflowSettings = {},
					name,
					workflowId,
					folder,
					description,
					workflowStatus,
					userEmail,
					webhookQueue,
					capturedResponses,
					selectedResponse;

				// Get the Workflow name.
				name = jQuery( 'body' ).find( '.workflow-input.workflow-name' ).val();

				// Fix the workflow name if it has special characters.
				name = encodeURIComponent( name );

				// Get the Workflow folder.
				folder = jQuery( 'body' ).find( '.sidebar-workflow-folder' ).val();

				// Get the Workflow description.
				description = jQuery( 'body' ).find( '.sidebar-workflow-description' ).val();

				// Get workflow ID.
				workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				// Get the authentication key.
				fmAuthKey = jQuery( 'body' ).find( '.workflow-input.workflow-auth-key' ).val();

				// Get user email.
				userEmail = jQuery( 'body' ).find( '.workflow-input.workflow-manager' ).val();

				// Get webhook queue option.
				webhookQueue = jQuery( 'body' ).find( '#sidebar-workflow-request-queue' ).val();

				// Get captured responses.
				capturedResponses = window.capturedResponses;

				// Get selected response.
				selectedResponse = window.selectedResponse;

				// Check workflow status.
				workflowStatus = ( jQuery( 'body' ).find( '.workflow-onoff-switch' ).is( ':checked' ) ) ? 'on' : 'off';

				// Add to settings.
				workflowSettings.name = name;
				workflowSettings.folder = folder;
				workflowSettings.description = description;
				workflowSettings.workflow_id = workflowId;
				workflowSettings.workflow_auth_key = fmAuthKey;
				workflowSettings.status = workflowStatus;
				workflowSettings.user_email = userEmail;
				workflowSettings.webhook_queue = webhookQueue;
				workflowSettings.capturedResponses = FlowMatticWorkflow.base64EncodeUnicode( capturedResponses );
				workflowSettings.selectedResponse = selectedResponse;

				this.collection.each( function( model, index ) {
					var settings = model.toJSON();

					if ( 'undefined' !== typeof settings.routerRoute ) {
						return false;
					}

					delete settings.eid;
					delete settings.view;
					delete settings.applicationEvents;

					workflowSteps[ index ] = settings;
				} );

				workflowSteps    = JSON.stringify( workflowSteps, getCircularReplacer() );
				workflowSettings = JSON.stringify( workflowSettings );

				const swalWithBootstrapButtons = window.Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-primary shadow-none me-xxl-3',
						cancelButton: 'btn btn-danger shadow-none'
					},
					buttonsStyling: false
				} );

				Backbone.ajax( {
					url: ajaxurl,
					data: { workflow: btoa( unescape( encodeURIComponent( workflowSteps ) ) ), settings: btoa( workflowSettings ), action: 'flowmattic_save_workflow', workflow_nonce: flowMatticAppConfig.workflow_nonce },
					type: 'POST',
				    dataType: 'json',
				    success: function( result ) {
						swalWithBootstrapButtons.fire(
							{
								title: 'Success!',
								text: 'The workflow settings are saved successfully',
								icon: 'success',
								showConfirmButton: false,
								timer: 1500,
								timerProgressBar: true
							}
						);
				    },
					error: function( response ) {
						swalWithBootstrapButtons.fire(
							{
								title: 'Error!',
								text: 'Something went wrong!' + response,
								icon: 'warning',
								showConfirmButton: false,
								timer: 1500,
								timerProgressBar: true
							}
						);
					}
				} );
			},

			saveRouterSteps: function( event, silent ) {
				var thisEl = this,
					routerSteps = {},
					i = 0,
					routeCount = 0;

				const swalWithBootstrapButtons = window.Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-primary shadow-none me-xxl-3',
						cancelButton: 'btn btn-danger shadow-none'
					},
					buttonsStyling: false
				} );

				if ( 'undefined' !== typeof window.routerActions ) {
						routerSteps = window.routerEl.model.get( 'routerSteps' );
						routerSteps[window.routerRoute] = [ routerSteps[window.routerRoute][0] ];

					_.each( window.routerActions, function( child, key ) {
						var settings;

						if ( 'undefined' !== typeof child ) {
							if ( 'undefined' !== typeof child.model ) {
								settings = child.model.toJSON();
							} else {
								settings = child.toJSON();
							}

							if ( ( 'undefined' !== typeof settings.application && '' !== settings.application ) && 'undefined' !== typeof settings.routerRoute && window.routerRoute === settings.routerRoute ) {

								// Remove the eid to avoid conflicts.
								delete settings.eid;
								delete settings.view;
								delete settings.applicationEvents;

								routerSteps[settings.routerRoute][ i ] = settings;
								i++;
								routeCount++;
							}
						}
					} );

					if ( ! silent ) {
						swalWithBootstrapButtons.fire(
							{
								title: 'Success!',
								text: 'The route actions are saved successfully',
								icon: 'success',
								showConfirmButton: false,
								timer: 1500,
								timerProgressBar: true
							}
						);
					}
				}

				setTimeout( function() {
					window.routerEl.model.set( 'routerSteps', routerSteps );
					FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'routerSteps', routerSteps, window.routerEl );

					// Update steps count.
					jQuery( window.routerEl.$el ).find( '[data-route="' + window.routerRoute + '"]' ).find( '.router-unit-step-counter' ).html( 'Contain ' + routeCount + ' steps');

					setTimeout( function() {
						FlowMatticWorkflowEvents.trigger( 'processAutosave' );
					}, 200 );
				}, 50 );

				// Clear tooltips.
				jQuery( 'body' ).find( '.tooltip.show' ).remove();

				// Close route editor.
				if ( ! silent ) {
					window.routerEl.closeRouteEditor();
				}
			},

			toggleStepAccordion: function( event ) {
				var targetStep = jQuery( event.target ).closest( '.fm-workflow-step' ),
					stepID = targetStep.attr( 'step-id' );

				if ( jQuery( event.target ).is( '.fm-workflow-step-rename' ) || jQuery( event.target ).parent( 'div' ).is( '.fm-workflow-step-rename' ) ) {
					return;
				}

				if ( jQuery( event.target ).is( '.fm-workflow-step-close' ) || jQuery( event.target ).parent( 'div' ).is( '.fm-workflow-step-close' ) ) {
					return;
				}

				if ( jQuery( event.target ).is( '.fm-workflow-step-description' ) || jQuery( event.target ).parent( 'div' ).is( '.fm-workflow-step-description' ) ) {
					return;
				}

				if ( jQuery( event.target ).is( '.fm-workflow-trigger-description' ) || jQuery( event.target ).parent( 'div' ).is( '.fm-workflow-trigger-description' ) ) {
					return;
				}

				targetStep.toggleClass( 'collapsed' );
				targetStep.find( '.fm-workflow-step-body' ).toggle( 'slideTop' );

				if ( ! jQuery( 'body' ).find( '.fm-workflow-step[step-id="' + stepID + '"]' ).hasClass( 'collapsed' ) ) {
					jQuery( 'html, body' ).animate( {
						scrollTop: jQuery( 'body' ).find( '.fm-workflow-step[step-id="' + stepID + '"]' ).offset().top - 120
					}, 500 );
				}
			},

			openTestRunModal: function() {
				var thisEl = this,
					thisModel = this.collection.at(0),
					captureData = thisModel.get( 'capturedData' ),
					captureResponse = captureData.webhook_capture,
					responseVariables = '',
					testRunModal = jQuery( '#testRunModal' );

				if ( 'undefined' !== typeof captureResponse ) {
					jQuery.each( captureResponse, function( key, value ) {
						var displayKey = key;
						if ( 'number' !== typeof displayKey ) {
							displayKey = displayKey.replaceAll( '_', ' ' ).replace(/\w\S*/g, function( txt ) {
								return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
							} );
						}
	
						responseVariables += '<tr>\
							<td>\
								<input class="fm-response-form-input w-100" type="text" value="' + displayKey + '" readonly />\
							</td>\
							<td>\
								<textarea class="fm-response-form-input w-100" rows="1" name="' + key + '">' + value + '</textarea>\
							</td>';
					} );
	
					// Open the modal.
					testRunModal.modal( 'show' );
		
					// Add the variables.
					testRunModal.find( '.fm-response-data-table' ).html( responseVariables );
				}
			}
		} );

		FlowMatticWorkflow.UCWords = function( str ) {
			if ( 'number' !== typeof str ) {
				return str.replaceAll( '_', ' ' ).replace(/\w\S*/g, function( txt ) {
					return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
				} );
			} else {
				return str;
			}
		}

		FlowMatticWorkflow.randomString = function( length ) {
			var result           = [],
			    characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
			    charactersLength = characters.length;

			for ( var i = 0; i < length; i++ ) {
				result.push( characters.charAt( Math.floor( Math.random() * charactersLength ) ) );
			}

			return result.join('');
		},

		FlowMatticWorkflow.FlowMatticStripslashes = function( str ) {
			return ( str + '' ).replace(/\\(.?)/g, function( s, n1 ) {
				switch( n1 ) {
					case '\\':
						return '\\';
					case '0':
						return '\u0000';
					case '':
						return '';
					default:
						return n1;
				}
			});
		},

		FlowMatticWorkflow.escapeHTML = function( string ) {
			var entityMap = {
				"&": "&amp;",
				"<": "&lt;",
				">": "&gt;",
				'"': '&quot;',
				"'": '&#39;',
				"/": '&#x2F;'
			};

			return String( string ).replace(/[&<>"'\/]/g, function (s) {
				return entityMap[s];
			});
		}

		// Function to encode a string to Base64 in a UTF-8 safe way
		FlowMatticWorkflow.base64EncodeUnicode = function(str) {
			var string = JSON.stringify(str);
			// First we escape the string using encodeURIComponent to get the UTF-8 encoded binary string
			// Then we convert the binary string to a base64 encoded string
			return btoa(encodeURIComponent(string).replace(/%([0-9A-F]{2})/g, function(match, p1) {
				return String.fromCharCode('0x' + p1);
			}));
		}

		// Function to decode a Base64 encoded string back to the original UTF-8 string
		FlowMatticWorkflow.base64DecodeUnicode = function(str) {
			return decodeURIComponent(atob(str).split('').map(function(c) {
				return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
			}).join(''));
		}

		FlowMatticWorkflow.decodeEntities = function( encodedString ) {
			var textArea = document.createElement( 'textarea' );
			textArea.innerHTML = encodedString;
			return textArea.value;
		}

		FlowMatticWorkflow.encodeEntities = function( decodedString ) {
			var textArea = document.createElement( 'textarea' );
			textArea.innerText = decodedString;
			return textArea.innerHTML;
		}

		// Instantiate Workflow App.
		FlowMatticWorkflowApp = new FlowMatticWorkflow.AppView( { // jshint ignore:line
			model: FlowMatticWorkflow.Action,
			collection: FlowMatticWorkflowSteps
		} );

		// Destroy the selectpicker on given HTML element.
		FlowMatticWorkflow.destroySelectPicker = function( element ) {
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = element;

			// Move the select element outside the bootstrap-select div.
			jQuery( tempDiv ).find( '.bootstrap-select' ).each( function() {
				jQuery( this ).find( '.dropdown-toggle' ).remove();
				jQuery( this ).find( '.dropdown-menu' ).remove();
				jQuery( this ).find( 'select' ).unwrap( this );
			} );

			const updatedHTML = tempDiv.innerHTML;

			// Remove the temporary div.
			tempDiv.remove();

			return updatedHTML;
		}
	} );
}( jQuery ) );
