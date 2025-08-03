/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

(function ($) {

	jQuery(document).ready(function () {
		// Workflow Trigger Webhook View.
		FlowMatticWorkflow.TablesView = Backbone.View.extend({
			template: FlowMatticWorkflow.template(jQuery('#flowmattic-application-tables-trigger-data-template').html()),
			actionTemplate: FlowMatticWorkflow.template(jQuery('#flowmattic-application-tables-action-data-template').html()),

			events: {
				'change select[name="database_id"]': 'getTables',
				'click .flowmattic-tables-refresh-button': 'getTables',
				'change select[name="table_name"]': 'getColumns',
				'click .flowmattic-column-refresh-button': 'getColumns',
			},

			initialize: function () {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function () {
				var thisEl = this,
					appAction = thisEl.model.get('action'),
					appActionTemplate;

				if ('trigger' === thisEl.model.get('type')) {
					if ('undefined' === typeof thisEl.model.get('database_id')) {
						thisEl.model.set('database_id', '');
					}

					if ('undefined' === typeof thisEl.model.get('table_name')) {
						thisEl.model.set('table_name', '');
					}

					if ('undefined' === typeof thisEl.model.get('column_name')) {
						thisEl.model.set('column_name', '');
					}

					this.$el.html(this.template(this.model.toJSON()));

					if (jQuery('#tables-trigger-' + appAction + '-data-template').length) {
						appActionTemplate = FlowMatticWorkflow.template(jQuery('#tables-trigger-' + appAction + '-data-template').html());
						jQuery(thisEl.$el).find('.tables-trigger-data').html(appActionTemplate(thisEl.model.toJSON()));
					}
				} else {
					thisEl.$el.html(thisEl.actionTemplate(thisEl.model.toJSON()));

					if (jQuery('#tables-action-' + appAction + '-data-template').length) {
						appActionTemplate = FlowMatticWorkflow.template(jQuery('#tables-action-' + appAction + '-data-template').html());
						jQuery(thisEl.$el).find('.tables-action-data').html(appActionTemplate(thisEl.model.toJSON()));
					}

					thisEl.setActionOptions();
				}

				// Get tables.
				if ('' !== thisEl.model.get('database_id')) {
					thisEl.getTables();
				}

				if ( ! window.isVisualWorkflowBuilder ) {
					this.$el.find('select').selectpicker();
				}

				return this;
			},

			setActionOptions: function () {
				var elements = jQuery(this.$el).find('.flowmattic-tables-action-data'),
					currentAction = this.model.get('action');

				elements.hide();

				if ('' !== currentAction) {
					jQuery(this.$el).find('.flowmattic-tables-action-data').show();
				}
			},

			getTables: function (e) {
				var thisEl = this,
					databaseId = jQuery(thisEl.$el).find('select[name="database_id"]').val(),
					$el = jQuery(thisEl.$el),
					appAction = thisEl.model.get('action');

				// If no database is selected, then return.
				if ('' === databaseId) {
					return;
				}

				// Clear the column fields.
				if ('trigger' !== thisEl.model.get('type')) {
					if ('new_record' === appAction || 'update_record' === appAction) {
						jQuery(thisEl.$el).find('.tables-action-data').html('<div class="alert alert-warning">Please select a table to fetch columns.</div>');
					}
				}

				// Set the window variable to cache tables for the current database.
				window.fmTableCache = window.fmTableCache || {};

				// If window.fmTables is already set, then don't fetch tables again.
				if ( 'undefined' !== typeof window.fmTableCache[databaseId] && window.fmTableCache[databaseId] ) {
					var dropdownHTML  = '<option value="">Select a table</option>';
					var selectedTable = $el.find('select[name="table_name"]').data('selected');
					jQuery.each(window.fmTableCache[databaseId], function (index, item) {
						var selected = item === selectedTable ? 'selected' : '';
						dropdownHTML += '<option value="' + item + '" ' + selected + '>' + item + '</option>';
					});

					$el.find('select[name="table_name"]').html(dropdownHTML);

					if ( ! window.isVisualWorkflowBuilder ) {
						$el.find('select[name="table_name"]').selectpicker('refresh');
						$el.find('select[name="table_name"]').selectpicker('val', selectedTable);
					}

					thisEl.getColumns();
					return;
				}

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'flowmattic_get_tables',
						database_id: databaseId,
						workflow_nonce: flowMatticAppConfig.workflow_nonce
					},
					success: function (response) {
						var dropdownHTML = '<option value="">Select a table</option>';
						var selectedTable = $el.find('select[name="table_name"]').data('selected');

						if (response.data && response.data.tables) {
							window.fmTables = response.data.tables;
							window.fmTableCache[databaseId] = response.data.tables;
							jQuery.each(window.fmTableCache[databaseId], function (index, item) {
								var selected = item === selectedTable ? 'selected' : '';
								dropdownHTML += '<option value="' + item + '" ' + selected + '>' + item + '</option>';
							});
						}

						$el.find('select[name="table_name"]').html(dropdownHTML);

						if ( ! window.isVisualWorkflowBuilder ) {
							$el.find('select[name="table_name"]').selectpicker('refresh');
							$el.find('select[name="table_name"]').selectpicker('val', selectedTable);
						}

						thisEl.getColumns();
					},
				});
			},

			getColumns: function (e) {
				var thisEl = this,
					databaseId = jQuery(thisEl.$el).find('select[name="database_id"]').val(),
					tableName = jQuery(thisEl.$el).find('select[name="table_name"]').val(),
					$el = jQuery(thisEl.$el),
					appAction = thisEl.model.get('action');

				if (!databaseId) {
					return false;
				}

				if (!tableName) {
					return false;
				}

				if ( 'undefined' !== typeof window.columnFields && window.columnTable === tableName ) {
					if ($el.find('select.table-columns').length) {
						var dropdownHTML = '<option value="">Select a column</option>';
						jQuery( $el.find('select.table-columns') ).each( function() {
							var selectedColumn = jQuery( this ).data('selected');
							jQuery.each(window.columnFields, function (column_name, column_title) {
								var selected = column_name === selectedColumn ? 'selected' : '';
								dropdownHTML += '<option value="' + column_name + '" data-subtext="ID: ' + column_name + '" ' + selected + '>' + column_title.title + '</option>';
							});

							jQuery( this ).html(dropdownHTML);

							if ( ! window.isVisualWorkflowBuilder ) {
								jQuery( this ).selectpicker('refresh');
								jQuery( this ).selectpicker('val', selectedColumn);
							}
						});
					} else {
						if ('new_record' === appAction || 'update_record' === appAction) {
							var appActionTemplate = FlowMatticWorkflow.template(jQuery('#tables-action-' + appAction + '-data-template').html());
							jQuery(thisEl.$el).find('.tables-action-data').html(appActionTemplate(thisEl.model.toJSON()));
						}
					}
					return;
				} else {
					window.columnFields = undefined;
					window.columnTable  = tableName;
				}

				// Show message as columns are being fetched, if appAction is new or update record.
				if ('trigger' !== thisEl.model.get('type')) {
					if ('new_record' === appAction || 'update_record' === appAction) {
						jQuery(thisEl.$el).find('.tables-action-data').html('<div class="alert alert-primary">Fetching columns...</div>');
					}
				}

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'flowmattic_get_columns',
						database_id: databaseId,
						table_name: tableName,
						workflow_nonce: flowMatticAppConfig.workflow_nonce
					},
					success: function (response) {
						var dropdownHTML = '<option value="">Select a column</option>';

						if (response.data && response.data.table_columns) {
							window.columnFields = response.data.table_columns;

							if ($el.find('select.table-columns').length) {
								jQuery( $el.find('select.table-columns') ).each( function() {
									var selectedColumn = jQuery( this ).data('selected');
									jQuery.each(window.columnFields, function (column_name, column_title) {
										var selected = column_name === selectedColumn ? 'selected' : '';
										dropdownHTML += '<option value="' + column_name + '" data-subtext="ID: ' + column_name + '" ' + selected + '>' + column_title.title + '</option>';
									});
		
									jQuery( this ).html(dropdownHTML);
		
									if ( ! window.isVisualWorkflowBuilder ) {
										jQuery( this ).selectpicker('refresh');
										jQuery( this ).selectpicker('val', selectedColumn);
									}
								});
							} else {
								if ('new_record' === appAction || 'update_record' === appAction) {
									var appActionTemplate = FlowMatticWorkflow.template(jQuery('#tables-action-' + appAction + '-data-template').html());
									jQuery(thisEl.$el).find('.tables-action-data').html(appActionTemplate(thisEl.model.toJSON()));
								}
							}
						}
					},
				});
			},
		});
	});
}(jQuery));
