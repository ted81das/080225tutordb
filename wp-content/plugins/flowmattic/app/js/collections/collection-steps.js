var FlowMatticWorkflow = FlowMatticWorkflow || {};
( function() {

	jQuery( document ).ready( function() {

		// Action collection
		FlowMatticWorkflow.Collection = Backbone.Collection.extend( {
			model: FlowMatticWorkflow.Action,
			comparator: (model) => model.eid
		} );

		window.FlowMatticWorkflowSteps = new FlowMatticWorkflow.Collection();
	} );
}( jQuery ) );
