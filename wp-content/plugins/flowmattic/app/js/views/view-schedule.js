/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Schedule View.
		FlowMatticWorkflow.ScheduleView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-schedule-data-template' ).html() ),

			events: {
				'change input': 'triggerParentDataUpdate',
				'change select': 'triggerParentDataUpdate',
				'click .flowmattic-schedule-save-button': 'saveSchedule'
			},

			initialize: function() {
			},

			render: function() {
				var thisEl = this,
					applicationSettings;

				if ( 'undefined' !== typeof this.model.get( 'applicationSettings' ) ) {
					applicationSettings = this.model.get( 'applicationSettings' );
					_.each( applicationSettings, function( fieldData ) {
						thisEl.model.set( fieldData.name, fieldData.value );
					} );
				}

				if ( 'undefined' === typeof this.model.get( 'week_day' ) ) {
					this.model.set( 'week_day', 'monday' );
				}

				if ( 'undefined' === typeof this.model.get( 'month_day' ) ) {
					this.model.set( 'month_day', '10' );
				}

				if ( 'undefined' === typeof this.model.get( 'day_time' ) ) {
					this.model.set( 'day_time', '14:30:00' );
				}

				// Set week day, month day and day time with dash options.
				if ( window.isVisualWorkflowBuilder ) {
					if ( 'undefined' !== typeof this.model.get( 'week-day' ) ) {
						this.model.set( 'week_day', this.model.get( 'week-day' ) );
					}

					if ( 'undefined' !== typeof this.model.get( 'month-day' ) ) {
						this.model.set( 'month_day', this.model.get( 'month-day' ) );
					}

					if ( 'undefined' !== typeof this.model.get( 'day-time' ) ) {
						this.model.set( 'day_time', this.model.get( 'day-time' ) );
					}
				}

				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();
				this.triggerParentDataUpdate();

				// Process autosave on any input change.
				jQuery( this.$el ).find( 'input, select, checkbox, radio, textarea' ).on( 'change', function() {
					thisEl.triggerParentDataUpdate();
				} );

				return this;
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( 'div[class*="fm-schedule-trigger-"]' ),
					currentScheduleAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentScheduleAction ) {
					jQuery( this.$el ).find( 'div[class*="fm-schedule-trigger-' + currentScheduleAction + '"]' ).show();
				}
			},

			triggerParentDataUpdate: function() {
				var inputFields = [],
					inputFieldName = '';

				inputFields = jQuery( this.$el ).find( 'div[class*="fm-schedule-trigger-"]' ).map( function() {
					var obj = [];

					if ( jQuery( this ).find( 'input' ).length ) {
						inputFieldName = jQuery( this ).find( 'input' ).attr( 'name' ).replace( '-', '_' );

						if ( jQuery( this ).find( 'input' ).is( ':checked' ) ) {
							 obj.push( { name: inputFieldName, value: jQuery( this ).find( 'input' ).is( ':checked' ) } );
                             return obj;
						} else {
							obj.push( { name: inputFieldName, value: jQuery( this ).find( 'input' ).val() } );
							return obj;
						}
					} else if ( jQuery( this ).find( 'select' ).length ) {
						inputFieldName = jQuery( this ).find( 'select' ).attr( 'name' ).replace( '-', '_' );
						obj.push( { name: inputFieldName, value: jQuery( this ).find( 'select' ).val() } );
						return obj;
					}
				} ).get();

				setTimeout( function() {
					FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdated', inputFields );
				}, 200 );
			},

			saveSchedule: function() {
				var thisEl = this;

				// Fire save workflow at first.
				FlowMatticWorkflowEvents.trigger( 'saveWorkflow' );

				setTimeout( function() {
					jQuery( thisEl.$el ).closest( '.fm-workflow-trigger' ).find( '.fm-workflow-step-header' ).trigger( 'click' );
				}, 500 );
			}
		} );
	} );
}( jQuery ) );
