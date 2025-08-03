<?php
/**
 * Admin page template for tables.
 *
 * @package FlowMattic
 * @since 5.0
 */

FlowMattic_Admin::loader();

$license_key = get_option( 'flowmattic_license_key', '' );

if ( '' === $license_key ) {
	?>
	<div class="card border-light mw-100">
		<div class="card-body text-center">
			<div class="alert alert-primary" role="alert">
				<?php echo esc_html__( 'License key not registered. Please register your license first to use tables.', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
} elseif ( '' === $license_key ) {
	?>
		<div class="card border-light mw-100">
			<div class="card-body text-center">
				<div class="alert alert-primary p-4 m-5 text-center" role="alert">
				<?php echo esc_html__( 'License key not valid. Please register your license first to use tables.', 'flowmattic' ); ?>
				</div>
			</div>
		</div>
	<?php
	wp_die();
}

$license  = wp_flowmattic()->check_license();
$settings = get_option( 'flowmattic_settings', array() );
$fetch_existing_tables = isset( $settings['fetch_existing_tables'] ) && 'yes' === $settings['fetch_existing_tables'] ? true : false;
$connection_data_dbs = wp_flowmattic()->database_connections_db->get_all();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-container flowmattic-tables-list-container w-100">
			<div class="flowmattic-dashboard-content container m-0 ps-3">
				<div class="row">
					<div id="flowmattic-tables">
						<div class="flowmattic-tables-list">
						<div class="fm-table-task-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-table-heading m-0 d-flex align-items-center">
									<?php esc_html_e( 'Tables', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-tables-header-actions">
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="modal" data-target="#helpModal">
										<span class="dashicons dashicons-editor-help d-inline-block fs-3 d-flex align-items-center justify-content-center"></span>
									</a>
									<?php
									$button_type  = '';
									$button_class = 'create-new-table';
									$button_url   = 'javascript:void(0);';

									if ( ! $license || '' === $license_key ) {
										$button_type  = 'disabled';
										$button_class = 'needs-registration';
									} else {
										?>
										<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>">
											<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
											<?php esc_html_e( 'Create New Table', 'flowmattic' ); ?>
										</a>
										<?php
									}
									?>
								</div>
							</div>
							<div class="tables-nav navbar mt-3 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Connect and manage your database tables and table data here.', 'flowmattic' ); ?>
									<a href="https://help.flowmattic.com/docs/features/tables-by-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="flowmattic-tables-list-body">
								<div class="flowmattic-tables-list-body-inner">
									<div class="flowmattic-tables-list-body-inner-table bg-white">
										<table class="table bg-white">
											<thead>
												<tr>
													<th class="p-3" style="width: 50%;"><?php echo esc_html__( 'Table Name', 'flowmattic' ); ?></th>
													<th class="p-3"><?php echo esc_html__( 'Database', 'flowmattic' ); ?></th>
													<th class="p-3" style="width: 200px;"><?php echo esc_html__( 'Rows', 'flowmattic' ); ?></th>
													<th class="p-3" style="width: 200px;"><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												// Get connection data.
												$db_connections   = $connection_data_dbs;
												$db_connections[] = (object) array(
													'id'              => 'local',
													'connection_data' => array(
														'name' => 'local',
													),
												);

												// Get the table schema.
												$table_schema_db = wp_flowmattic()->tables_schema_db->get_all();

												$tables        = array();
												$table_names   = array();
												$active_tables = array();
												$tables_info   = array();

												if ( ! empty( $table_schema_db ) ) {
													foreach ( $table_schema_db as $table_schema ) {
														$table_settings = maybe_unserialize( $table_schema->table_settings );
														$table_names[] = $table_schema->table_name;
														$tables_info[ $table_schema->table_name ] = array(
															'table_name'  => $table_schema->table_name,
															'description' => is_array( $table_settings ) ? $table_settings['description'] : '',
														);
													}
												}

												foreach ( $db_connections as $connection_data_db ) {
													$connection_id = $connection_data_db->id;
													$table_prefix  = '';

													$is_local_db = false;
													if ( 'local' === $connection_id ) {
														global $wpdb;
														$is_local_db     = true;
														$database_name   = DB_NAME;
														$connection_data = array();
														$table_prefix    = $wpdb->prefix;
													} else {
														$connection_data = maybe_unserialize( $connection_data_db->connection_data );
														$database_name   = $connection_data['name'];

														// Check if the database is connected.
														$is_database_connected = wp_flowmattic()->tables->is_database_connection_active( $connection_data_db->id );

														if ( ! $is_database_connected ) {
															continue;
														}

														// Add active tables.
														$active_tables[ $connection_data_db->id ] = $database_name;
													}

													// Prepare tables query.
													$set_session_query = 'SET SESSION information_schema_stats_expiry=0;';
													$prepare_query_with_count = 'SHOW TABLE STATUS FROM `' . $database_name . '`';

													$second_clause = $fetch_existing_tables ? ' WHERE ' : ' AND ';

													// If table prefix is set, it is local database, so add the prefix check.
													if ( ! empty( $table_prefix ) && $fetch_existing_tables ) {
														$prepare_query_with_count .= " WHERE ( name LIKE '" . $table_prefix . "%'";
														$second_clause = ' AND ';
													}

													// Check if table schema is not empty and fetch_existing_tables is enabled.
													if ( ! $fetch_existing_tables ) {
														if ( ! empty( $table_schema_db ) ) {
															$clause = ! empty( $table_prefix ) ? ' OR ' : ' WHERE ';
															if ( ! $fetch_existing_tables ) {
																$prepare_query_with_count .= ' WHERE name IN (' . "'" . implode( "', '", $table_names ) . "')";
															} else {
																$prepare_query_with_count .= ' ' . $clause . ' name IN (' . "'" . implode( "', '", $table_names ) . "')";
															}

															$second_clause = ' AND ';
														}
													} elseif ( ! empty( $table_prefix ) ) {
														$prepare_query_with_count .= ' OR name IN (' . "'" . implode( "', '", $table_names ) . "')";
													} else {
														if ( ! empty( $table_names ) ) {
															$prepare_query_with_count .= ' WHERE ( name IN (' . "'" . implode( "', '", $table_names ) . "')";
														}

														$second_clause = ' OR ';
													}

													// If table schema is empty and fetch_existing_tables is disabled.
													if ( empty( $table_schema_db ) && ! $fetch_existing_tables ) {
														// No tables to show.
														continue;
													}

													// Hide FlowMattic tables if fetch_existing_tables is enabled.
													if ( $fetch_existing_tables ) {
														$fm_tables = array(
															'flowmattic_chatbot',
															'flowmattic_chatbot_threads',
															'flowmattic_connects',
															'flowmattic_custom_apps',
															'flowmattic_data_connections',
															'flowmattic_data_tables',
															'flowmattic_tables_schema',
															'flowmattic_workflows',
														);

														// Prepare NOT LIKE conditions for flowmattic tables
														$not_like_conditions = array_map(function ($table) {
															return "name NOT LIKE '%" . $table . "%'";
														}, $fm_tables);

														// Combine the conditions with AND
														$not_like_clause = implode(' AND ', $not_like_conditions);

														// $prepare_query_with_count .= " $second_clause name NOT LIKE '%" . implode( "%' OR '%", $fm_tables ) . "%'";
														$prepare_query_with_count .= ") $second_clause $not_like_clause";
													}

													// Get tables with row count.
													$fm_db_connector   = new FlowMattic_DB_Connector( $connection_data, $is_local_db );
													$tables_with_count = array();

													if ( $fm_db_connector->get_db() ) {
														$fm_db_connector->get_db()->query( $set_session_query ); // Set session query to fetch live data.
														$tables_with_count = $fm_db_connector->get_db()->get_results( $prepare_query_with_count );
													}

													if ( ! empty( $tables_with_count ) ) {
														foreach ( $tables_with_count as $table ) {
															$table_name = $table->Name;
															$row_count  = $table->Rows;
															$table_edit_link = admin_url( 'admin.php?page=flowmattic-tables&table=' . $table_name . '&database=' . $connection_id . '&action=edit' );

															// Get record count.
															$record_count = $row_count;

															// Added by badge.
															$added_by = in_array( $table_name, $table_names ) ? 'FlowMattic' : 'System';
															
															// Get table description.
															$table_description = '';

															// Get the table description.
															if ( ! empty( $tables_info ) && isset( $tables_info[ $table_name ] ) ) {
																$table_description = $tables_info[ $table_name ]['description'];
															}
															?>
															<tr>
																<td class="p-3" valign="middle">
																	<div class="mb-1 mt-1">
																		<strong><?php echo esc_html( $table_name ); ?></strong>
																		<?php if ( 'FlowMattic' === $added_by ) : ?>
																			<span class="ms-2 badge hover-text-white btn-outline-primary btn text-primary" data-toggle="tooltip" title="Added by <?php echo esc_html( $added_by ); ?>"><?php echo esc_html( $added_by ); ?></span>
																		<?php endif; ?>
																		<?php if ( 'System' === $added_by ) : ?>
																			<span class="ms-2 badge hover-text-white btn-outline-secondary btn text-secondary" data-toggle="tooltip" title="Added by <?php echo esc_html( $added_by ); ?>"><?php echo esc_html( $added_by ); ?></span>
																		<?php endif; ?>
																		<div class="table-description text-mute text-small mt-2"><?php echo esc_html( $table_description ); ?></div>
																	</div>
																</td>
																<td class="p-3" valign="middle"><?php echo esc_html( $database_name ); ?></td>
																<td class="p-3" valign="middle"><?php echo esc_html( $record_count ); ?></td>
																<td class="p-3" valign="middle">
																	<a href="<?php echo esc_attr( $table_edit_link ); ?>" class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center fm-edit-table me-2" data-toggle="tooltip" title="<?php esc_html_e( 'Edit table data', 'flowmattic' ); ?>">
																		<?php echo esc_attr__( 'Edit', 'flowmattic' ); ?>
																	</a>
																	<?php
																	if ( 'System' === $added_by ) {
																		?>
																		<span data-toggle="tooltip" title="<?php esc_html_e( 'Can not Delete system tables', 'flowmattic' ); ?>"><a href="javascript:void(0);" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center disabled">
																			<?php echo esc_attr__( 'Delete', 'flowmattic' ); ?>
																		</a></span>
																		<?php
																	} else {
																		?>
																		<a href="javascript:void(0);" data-table-name="<?php echo $table_name; ?>" data-database-id="<?php echo $connection_id; ?>" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center fm-delete-table" data-toggle="tooltip" title="<?php esc_html_e( 'Delete table', 'flowmattic' ); ?>">
																			<?php echo esc_attr__( 'Delete', 'flowmattic' ); ?>
																		</a>
																		<?php
																	}
																	?>
																</td>
															</tr>
															<?php
														}
													}
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- #flowmattic-tables -->
				</div> <!-- .row -->
			</div> <!-- .flowmattic-dashboard-content -->
		</div> <!-- .flowmattic-container -->
	</div> <!-- .flowmattic-wrapper -->
</div> <!-- .flowmattic-dashboard-content -->

<!-- Create new table modal -->
<div class="modal fade" id="createNewTableModal" tabindex="-1" aria-labelledby="createNewTableModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createNewTableModalLabel"><?php echo esc_html__( 'Create New Table', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<form id="createNewTableForm" class="flowmattic-form">
					<div class="mb-3">
						<label for="table_name" class="form-label"><?php echo esc_html__( 'Table Name', 'flowmattic' ); ?></label>
						<input type="text" class="form-control" id="table_name" name="table_name" required>
					</div>
					<div class="mb-3">
						<label for="table_description" class="form-label"><?php echo esc_html__( 'Table Description', 'flowmattic' ); ?></label>
						<textarea class="form-control border-secondary" id="table_description" name="table_description" rows="3"></textarea>
					</div>
					<div class="mb-3">
						<label for="table_columns" class="form-label"><?php echo esc_html__( 'Table Columns', 'flowmattic' ); ?></label>
						<textarea class="form-control border-secondary" id="table_columns" name="table_columns" rows="3" placeholder="first_name,last_name,email"></textarea>
						<div class="form-text"><?php echo esc_html__( 'Enter the columns separated by commas. Eg: first_name,last_name,email', 'flowmattic' ); ?></div>
						<div class="form-text"><?php echo esc_html__( 'Note: ID column is automatically added to the table as the primary key.', 'flowmattic' ); ?></div>
					</div>					
					<div class="mb-3">
						<label for="table_database" class="form-label"><?php echo esc_html__( 'Table Database', 'flowmattic' ); ?></label>
						<select class="form-select w-100 mw-100" id="table_database" name="table_database" required>
							<option value="local"><?php echo esc_html__( 'Local', 'flowmattic' ); ?></option>
							<?php
							$database_connections = $active_tables;
							if ( ! empty( $database_connections ) ) {
								foreach ( $database_connections as $id => $table_name ) {
									?>
									<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $table_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<span class="spinner-border spinner-border-sm text-primary d-none create-table-spinner ms-2" role="status" aria-hidden="true"></span>
				<button type="submit" class="btn btn-primary fm-create-table"><?php echo esc_html__( 'Create Table', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Delete table confirmation modal -->
<div class="modal fade" id="deleteTableModal" tabindex="-1" aria-labelledby="deleteTableModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteTableModalLabel"><?php echo esc_html__( 'Delete Table?', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<p class="fs-6"><?php echo esc_html__( 'Are you sure you want to delete this table?', 'flowmattic' ); ?></p>
				<p class="fs-6"><?php echo esc_html__( 'This action cannot be undone.', 'flowmattic' ); ?></p>
				<p class="fs-6"><?php echo esc_html__( 'Please note that this will delete the table and all its data.', 'flowmattic' ); ?></p>
			</div>
			<div class="modal-footer">
				<span class="spinner-border spinner-border-sm text-primary d-none delete-table-spinner ms-2" role="status" aria-hidden="true"></span>
				<button type="button" class="btn btn-danger btn-sm fm-delete-table-confirm"><?php echo esc_html__( 'Delete Table', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Help modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content border-0">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModal-label"><?php esc_html_e( 'FlowMattic Tables', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="accordion" id="helpAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingOne">
							<button class="accordion-button" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								<?php esc_html_e( 'What are FlowMattic Tables?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'FlowMattic Tables are a structured way to store, manage, and organize data within the FlowMattic platform. They function as customizable data tables where you can add, edit, or delete records based on your workflow needs. These tables can be used to: Store Workflow Data, Organize Custom Data, Reference in Automations and so on.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTwo">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php esc_html_e( 'How to create a new FlowMattic Table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can create a new FlowMattic Table by clicking the "Create New Table" button above. You will be asked to enter the table name, table description and table database.', 'flowmattic' ); ?>
								<ul style="list-style: square;">
									<li><?php esc_html_e( 'Table name should be unique and should not contain any special characters.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Table name accepts only English letters or numbers.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Table description is optional and is used to describe the table.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Table database is by default Local. However, you can select your own database if available.', 'flowmattic' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingThree">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<?php esc_html_e( 'How to edit a FlowMattic Table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To edit a FlowMattic Table, click the "Edit" button next to the desired table. This will open the FlowMattic table editor interface, where you can modify the records as needed.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFour">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<?php esc_html_e( 'How to delete a FlowMattic Table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can delete a FlowMattic Table by clicking the "Delete" button next to the Table. You will be asked to confirm the deletion. Once deleted, the Table cannot be recovered.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFive">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<?php esc_html_e( 'Few things to note about FlowMattic Tables', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-parent="#helpAccordion">
							<div class="accordion-body">
								<ul style="list-style: square;">
									<li><?php esc_html_e( 'In system tables, adding new columns is not allowed.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'You cannot delete the system tables.', 'flowmattic' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="https://help.flowmattic.com/docs/features/tables-by-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Read more' );?></a>
				<button type="button" class="btn btn-secondary" id="close-help-modal" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	#wpfooter {
		display: none;
	}
	div#wpbody-content {
		padding-bottom: 0 !important;
	}
	.hover-text-white:hover {
    color: #fff !important;
}
</style>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		jQuery( '.create-new-table' ).on( 'click', function() {
			jQuery( '#createNewTableModal' ).modal( 'show' );
		} );

		function openCreateTableModal() {
			var table_name        = jQuery( '#table_name' ).val();
			var table_description = jQuery( '#table_description' ).val();
			var table_database    = jQuery( '#table_database' ).val();
			var column_names      = jQuery( '#table_columns' ).val();
			var table_edit_link   = '<?php echo admin_url( 'admin.php?page=flowmattic-tables&table={table_name}&database={table_database}&action=edit' ); ?>';
			var loadingSpinner    = jQuery( '.create-table-spinner' );

			// Disable the button.
			jQuery( '.fm-create-table' ).prop( 'disabled', true );

			// Show spinner.
			loadingSpinner.removeClass( 'd-none' );

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_create_table',
					security: '<?php echo wp_create_nonce( 'flowmattic_create_table' ); ?>',
					table_name: table_name,
					table_description: table_description,
					table_database: table_database,
					column_names: column_names,
				},
				success: function( response ) {
					if ( response.success ) {
						jQuery( '#createNewTableModal' ).modal( 'hide' );
						table_edit_link = table_edit_link.replace( '{table_name}', table_name ).replace( '{table_database}', table_database );
						window.location.href = table_edit_link;
					}
				},
				error: function( response ) {
					location.reload();
				}
			} );
		}

		jQuery( '.fm-create-table' ).on( 'click', function() {
			openCreateTableModal()
		} );

		// If query string is present, open the modal.
		var urlParams = new URLSearchParams( window.location.search );
		var openModal = urlParams.get( 'openModal' );

		if ( 'createTable' === openModal ) {
			jQuery( '#createNewTableModal' ).modal( 'show' );
			
			// Remove openModal from the URL.
			var newURL = urlParams.toString().replace( '&openModal=createTable', '' );
			window.history.pushState( '', '', '?' + newURL );
		}

		// Delete table.
		var table_name = '';
		var database_id = '';
		jQuery( '.fm-delete-table' ).on( 'click', function() {
			jQuery( '#deleteTableModal' ).modal( 'show' );
			table_name = jQuery( this ).data( 'table-name' );
			database_id = jQuery( this ).data( 'database-id' );
		} );

		jQuery( '.fm-delete-table-confirm' ).on( 'click', function() {
			var thisBtn = jQuery( this ),
				loadingSpinner = jQuery( '.delete-table-spinner' );

			loadingSpinner.removeClass( 'd-none' );

			// Disable the button.
			thisBtn.prop( 'disabled', true );

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_delete_table',
					security: '<?php echo wp_create_nonce( 'flowmattic_delete_table' ); ?>',
					table_name: table_name,
					table_database: database_id
				},
				success: function( response ) {
					if ( response.success ) {
						loadingSpinner.addClass( 'd-none' );
						thisBtn.text( 'Table Deleted' );
						jQuery( '#deleteTableModal' ).modal( 'hide' );
						location.reload();
					}
				}
			} );
		} );
	} );
</script>
<?php 
// FlowMattic_Admin::footer();
add_filter( 'admin_footer_text', function( $text ) {
	return '';
} );

add_filter( 'update_footer', function( $text ) {
	return '';
} );
