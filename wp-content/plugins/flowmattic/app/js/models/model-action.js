var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {

		// Workflow action model
		FlowMatticWorkflow.Action = Backbone.Model.extend( {

			defaults: {
				type: 'action'
			}

		} );

	} );

}( jQuery ) );
