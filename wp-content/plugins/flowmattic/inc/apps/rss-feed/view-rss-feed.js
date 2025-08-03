/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger RSS Feed View.
		FlowMatticWorkflow.Rss_FeedView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-rss-feed-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-rss-feed-action-data-template' ).html() ),

			events: {
				'keyup .rss-field-slug-input': 'updateSlugPlaceholder',
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var appAction = this.model.get( 'action' ),
					appActionTemplate = '';

				// Set the current time.
				this.model.set( 'current_time', new Date().getTime() );

				// If rss_feed_url is not available then set the default value.
				if ( ! this.model.get( 'rss_feed_url' ) ) {
					this.model.set( 'rss_feed_url', '' );
				}

				if ( 'trigger' === this.model.get( 'type' ) ) {
					this.$el.html( this.template( this.model.toJSON() ) );
				} else {
					jQuery( this.$el ).html( this.actionTemplate( this.model.toJSON() ) );

					if ( jQuery( '#flowmattic-rss-feed-action-' + appAction + '-data-template' ).length ) {
						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-rss-feed-action-' + appAction + '-data-template' ).html() );
						jQuery( this.$el ).find( '.flowmattic-rss-feed-action-data' ).html( appActionTemplate( this.model.toJSON() ) );
					}

					this.setActionOptions();
				}

				this.$el.find( 'select' ).selectpicker();
				return this;
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-rss-feed-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-rss-feed-action-data' ).show();
				}
			},

			updateSlugPlaceholder: function( e ) {
				var $this = jQuery( e.currentTarget ),
					$slug = jQuery( this.$el ).find(  '.slug-placeholder' );

				$slug.text( $this.val() );
			}
		} );
	} );
}( jQuery ) );
