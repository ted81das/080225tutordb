/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {},
	FlowMatticContextMenuView = FlowMatticContextMenuView || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Context Menu View.
		FlowMatticContextMenuView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-context-menu-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
				'click .flowmattic-menu-rename': 'renameStepTitle',
				'click .flowmattic-menu-toggle': 'toggleStepAccordion',
				'click .flowmattic-menu-copy': 'copyStepSettings',
				'click .flowmattic-menu-clone': 'cloneStep',
				'click .flowmattic-menu-paste-settings': 'pasteSettings',
				'click .flowmattic-menu-paste-before': 'addStepBefore',
				'click .flowmattic-menu-paste-after': 'addStepAfter',
				'click .flowmattic-menu-remove-step': 'deleteStep'
			},

			initialize: function( args ) {
				var thisEl = this;

				// Remove existing context menus.
				jQuery( 'body' ).find( '.flowmattic-context-menu' ).remove();

				this.args = args;

				if ( window.routerEditorOpen ) {
					_.each( window.routerActions, function( routeAction ) {
						var actionModel;

						if ( 'undefined' !== typeof routeAction ) {
							actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

							if ( args.model_id === actionModel.get( 'stepID' ) ) {
								thisEl.parentModel = routeAction;
								return false;
							}
						}
					} );
				} else {
					this.parentModel = FlowMatticWorkflowViewManager.getView( args.model_id );
				}

				// Remove context menu on side click.
				jQuery( 'body' ).on( 'click', function( event ) {
					if ( ! jQuery( event.target ).closest( '.flowmattic-context-menu' ).length ) {
						thisEl.removeExisting();
						thisEl.unbind();
					}
				} );
			},

			render: function() {
				var thisEl = this,
					contextMenu = jQuery( '<div class="flowmattic-context-menu"><ul class="flowmattic-context-menu-items"></ul></div>' );

				event.preventDefault();

				// Remove previous instance first.
				jQuery( 'body' ).find( '.flowmattic-context-menu' ).remove();

				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).prepend( '<span class="flowmattic-context-menu-heading">FlowMattic Action</span>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-rename">Rename Step Title</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-toggle">Toggle</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-copy">Copy</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-clone">Clone</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-paste-settings">Paste Settings</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-paste-before">Insert Before</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-paste-after">Insert After</li>' );
				jQuery( contextMenu ).find( '.flowmattic-context-menu-items' ).append( '<li class="flowmattic-context-menu-item flowmattic-menu-remove-step" style="color: #f44336;">Delete Step</li>' );

				this.$el.html( contextMenu );

				return this;
			},

			/**
			 * Removes the existing context menus.
			 *
			 * @since 1.0
			 * @return {void}
			 */
			removeExisting: function() {
				FlowMatticWorkflowViewManager.removeView( this.cid );
				this.remove();
				this.unbind();
				jQuery( 'body' ).find( '.flowmattic-context-menu' ).remove();
			},

			renameStepTitle: function( event ) {
				if ( jQuery( this.parentModel.$el ).is( '.flowmattic-action-step' ) ) {
					jQuery( this.parentModel.$el ).find( '.fm-workflow-step-rename' ).trigger( 'click' );
				} else {
					jQuery( this.parentModel.$el ).closest( '.fm-workflow-step' ).find( '.fm-workflow-step-rename' ).trigger( 'click' );
				}

				event.preventDefault();

				this.removeExisting();

				return false;
			},

			toggleStepAccordion: function( event ) {
				if ( jQuery( this.parentModel.$el ).is( '.flowmattic-action-step' ) ) {
					jQuery( this.parentModel.$el ).find( '.fm-workflow-step-header' ).trigger( 'click' );
				} else {
					jQuery( this.parentModel.$el ).closest( '.fm-workflow-step' ).find( '.fm-workflow-step-header' ).trigger( 'click' );
				}

				this.removeExisting();
			},

			copyStepSettings: function( event ) {
				var settings = JSON.stringify( this.parentModel.model.toJSON() ),
					data;

				// Remove captured data.
				delete settings.capturedData;

				// Remove Step ID.
				delete settings.stepID;

				// Remove EID.
				delete settings.eid;

				if ( 'undefined' !== typeof Storage ) {
					localStorage.setItem( 'FlowMatticStepSettings', settings );
				}

				this.removeExisting();
			},

			pasteSettings: function( event ) {
				var thisEl = this,
					parentModel = ( window.routerEditorOpen ) ? this.parentModel : FlowMatticWorkflowViewManager.getView( this.args.model_id ),
					stepSettings = ( 'undefined' !== typeof parentModel.model ) ? parentModel.model.toJSON() : parentModel.toJSON(),
					copiedSettings,
					attributes = {};

				if ( 'undefined' !== typeof Storage ) {
					if ( localStorage.getItem( 'FlowMatticStepSettings' ) ) {
						copiedSettings = JSON.parse( localStorage.getItem( 'FlowMatticStepSettings' ) );

						if ( copiedSettings.application === stepSettings.application ) {

							if ( window.routerEditorOpen && 'undefined' === typeof parentModel.model ) {
								parentModel.attributes = attributes;

								_.each( copiedSettings, function( value, key ) {
									if ( 'stepID' !== key && 'routerRoute' !== key ) {
										parentModel.set( key, value );
									}
								} );

								// Set step ID from copied step.
								parentModel.set( 'stepID', stepSettings.stepID );

								// Remove captured data.
								parentModel.set( 'capturedData', '' );

								// Set the route letter;
								parentModel.set( 'routerRoute', window.routerRoute );
							} else {
								parentModel.model.attributes = attributes;

								_.each( copiedSettings, function( value, key ) {
									if ( 'stepID' !== key && 'routerRoute' !== key ) {
										parentModel.model.set( key, value );
									}
								} );

								// Set step ID from copied step.
								parentModel.model.set( 'stepID', stepSettings.stepID );

								// Remove captured data.
								parentModel.model.set( 'capturedData', '' );

								if ( window.routerEditorOpen ) {
									// Set the route letter;
									parentModel.model.set( 'routerRoute', window.routerRoute );

									_.each( window.routerActions, function( routeAction, key ) {
										var actionModel;

										if ( 'undefined' !== typeof routeAction ) {
											actionModel = ( 'undefined' !== typeof routeAction.model ) ? routeAction.model : routeAction;

											if ( stepSettings.stepID === actionModel.get( 'stepID' ) ) {

												window.routerActions[ key ] = parentModel;

												return false;
											}
										}
									} );
								}
							}

							setTimeout( function() {
								if ( window.routerEditorOpen ) {
									FlowMatticWorkflowEvents.trigger( 'pasteRouteSettings', stepSettings.stepID );

									setTimeout( function() {
										// Autosave workflow.
										FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
									}, 2000 );
								} else {
									FlowMatticWorkflowEvents.trigger( 'pasteSettings' );
								}

								thisEl.swalWithBootstrapButtons.fire(
									{
										title: 'Settings Applied',
										text: 'Settings copied from other step are applied successfully!',
										icon: 'success',
										showConfirmButton: true,
										timer: 1500
									}
								);
							}, 100 );
						} else {
							// Alert! Application mismatch.
							thisEl.swalWithBootstrapButtons.fire(
								{
									title: 'Application mismatch!',
									text: 'The application you used to copy settings is - ' + copiedSettings.application,
									icon: 'error',
									showConfirmButton: true,
									timer: 5000
								}
							);
						}
					}
				}

				this.removeExisting();
			},

			cloneStep: function( event ) {
				FlowMatticWorkflowEvents.trigger( 'cloneStep', this.args.model_id );

				this.removeExisting();
			},

			addStepBefore: function() {
				var thisEl = this,
					copiedSettings,
					action;

				if ( window.routerEditorOpen ) {
					if ( jQuery( this.parentModel.$el ).closest( '.flowmattic-action-step' ).is( ':first-child' ) ) {
						// Alert! Can't insert before conditions step.
						thisEl.swalWithBootstrapButtons.fire(
							{
								title: 'Error!',
								text: 'You can\'t insert steps before the condition step',
								icon: 'error',
								showConfirmButton: true,
								timer: 5000
							}
						);

						return false;
					}
				}

				if ( 'undefined' !== typeof Storage ) {
					if ( localStorage.getItem( 'FlowMatticStepSettings' ) ) {
						copiedSettings = JSON.parse( localStorage.getItem( 'FlowMatticStepSettings' ) );

						if ( 'undefined' !== typeof copiedSettings.routerRoute ) {
							delete( copiedSettings.routerRoute );
						}

						if ( window.routerEditorOpen ) {
							window.insertBeforeProcessed = false;
						}

						setTimeout( function() {
							FlowMatticWorkflowEvents.trigger( 'addStepBefore', thisEl.args.model_id, copiedSettings );
						}, 100 );
					}
				}

				this.removeExisting();
			},

			addStepAfter: function() {
				var thisEl = this,
					copiedSettings,
					action;

				if ( 'undefined' !== typeof Storage ) {
					if ( localStorage.getItem( 'FlowMatticStepSettings' ) ) {
						copiedSettings = JSON.parse( localStorage.getItem( 'FlowMatticStepSettings' ) );

						if ( 'undefined' !== typeof copiedSettings.routerRoute ) {
							delete( copiedSettings.routerRoute );
						}

						if ( window.routerEditorOpen ) {
							window.insertAfterProcessed = false;
						}

						setTimeout( function() {
							FlowMatticWorkflowEvents.trigger( 'addStepAfter', thisEl.args.model_id, copiedSettings );
						}, 100 );
					}
				}

				this.removeExisting();
			},

			deleteStep: function( event ) {
				if ( window.routerEditorOpen ) {
					if ( jQuery( this.parentModel.$el ).is( '.flowmattic-action-step:first-child' ) || jQuery( this.parentModel.$el ).closest( '.flowmattic-action-step' ).is( ':first-child' ) ) {
						// Alert! Can't delete conditions step.
						this.swalWithBootstrapButtons.fire(
							{
								title: 'Error!',
								text: 'You can\'t delete the condition step',
								icon: 'error',
								showConfirmButton: true,
								timer: 5000
							}
						);

						return false;
					}
				}

				FlowMatticWorkflowEvents.trigger( 'deleteStep', this.args.model_id );

				// if ( jQuery( this.parentModel.$el ).is( '.flowmattic-action-step' ) ) {
				// 	jQuery( this.parentModel.$el ).find( '.fm-workflow-step-close' ).trigger( 'click' );
				// } else {
				// 	jQuery( this.parentModel.$el ).closest( '.fm-workflow-step' ).find( '.fm-workflow-step-close' ).trigger( 'click' );
				// }

				event.preventDefault();
				this.removeExisting();

				return false;
			},
		} );
	} );
}( jQuery ) );
