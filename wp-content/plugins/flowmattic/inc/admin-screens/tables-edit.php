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
				<?php echo esc_html__( 'License key not registered. Please register your license first to edit this table.', 'flowmattic' ); ?>
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
				<?php echo esc_html__( 'License key not valid. Please register your license first to edit this table.', 'flowmattic' ); ?>
				</div>
			</div>
		</div>
	<?php
	wp_die();
}

$license = wp_flowmattic()->check_license();

$table_name = isset( $_GET['table'] ) ? sanitize_text_field( wp_unslash( $_GET['table'] ) ) : '';
$database   = isset( $_GET['database'] ) ? sanitize_text_field( wp_unslash( $_GET['database'] ) ) : '';

if ( '' === $table_name || '' === $database ) {
	wp_safe_redirect( admin_url( 'admin.php?page=flowmattic-tables' ) );
	exit;
}

$is_local_db = false;
if ( 'local' === $database ) {
	$is_local_db = true;
	$connection_data = array();
} else {
	// Get database connection data.
	$connection_data_db = wp_flowmattic()->database_connections_db->get( array( 'database_id' => $database ) );
	$connection_data    = maybe_unserialize( $connection_data_db->connection_data );
}

// Create a new instance of the FlowMattic_DB_Connector class.
$fm_db_connector = new FlowMattic_DB_Connector( $connection_data, $is_local_db );

// Get the table data.
$table_data = $fm_db_connector->get_db()->get_results( 'SELECT * FROM `' . $table_name . '`' );

// Get the table columns.
$table_columns = $fm_db_connector->get_db()->get_results( 'SHOW COLUMNS FROM `' . $table_name . '`' );
?>
<script type="text/javascript">
	var table_name  = '<?php echo esc_js( $table_name ); ?>';
	var database_id = '<?php echo esc_js( $database ); ?>';
	var table_data  = <?php echo wp_json_encode( $table_data ); ?>;
	var security	= '<?php echo wp_create_nonce( 'flowmattic_table_nonce' ); ?>';
	var empty_icon  = '<?php echo esc_url( FLOWMATTIC_PLUGIN_URL . 'tables/src/img/emptyRecordTemplate_light.svg' ); ?>';
</script>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<div class="flowmattic-container flowmattic-tables-list-container w-100">
			<div class="flowmattic-dashboard-content m-2 me-4 ps-3">
				<div class="row">
					<div id="flowmattic-tables">
						<div class="flowmattic-tables-list">
							<div class="fm-table-task-header d-flex mb-3 mt-4 justify-content-between">
								<div class="table-title-wrapper d-flex align-items-center">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=flowmattic-tables' ) ); ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center py-2 me-3" data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Back to Tables', 'flowmattic' ); ?>">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="16" width="16" size="16" name="arrowBigLeft"><path fill="currentColor" d="M7.14 13H20v-2H7.14l5.04-6H9.56l-5.87 7 5.87 7h2.62l-5.04-6Z"></path><path fill="currentColor" d="M7.14 13H20v-2H7.14l5.04-6H9.56l-5.87 7 5.87 7h2.62l-5.04-6Z"></path></svg>
									</a>
									<h3 class="fm-table-heading m-0 d-flex align-items-center">
										<?php esc_html_e( 'Table: ', 'flowmattic' ); ?>
										<?php echo esc_html( $table_name ); ?>
									</h3>
								</div>
								<div class="flowmattic-tables-header-actions">
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="modal" data-target="#helpModal">
										<span class="dashicons dashicons-editor-help d-inline-block fs-3 d-flex align-items-center justify-content-center"></span>
									</a>
								</div>
							</div>
							<div class="tables-nav navbar mt-0 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Edit the table data, add new data rows or new columns to the table.', 'flowmattic' ); ?>
									<a href="https://help.flowmattic.com/docs/features/tables-by-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="flowmattic-tables-data">
								<div id="tablesApp"></div>
							</div>
						</div>
					</div> <!-- #flowmattic-tables -->
				</div> <!-- .row -->
			</div> <!-- .flowmattic-dashboard-content -->
		</div> <!-- .flowmattic-container -->
	</div> <!-- .flowmattic-wrapper -->
</div> <!-- .flowmattic-dashboard-content -->

<!-- Help modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content border-0">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModal-label"><?php esc_html_e( 'Table FAQs', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="accordion" id="helpAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingOne">
							<button class="accordion-button" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								<?php esc_html_e( 'How to add columns in FlowMattic table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To create a new column in the table, click the "New Column" button located at the top-left corner of the toolbar. A prompt will appear asking you to provide the column name and select the column type. Enter a meaningful name for the column and choose the appropriate column type based on your requirements, such as text, number, or date. Once done, confirm your selections to add the new column to the table.	', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTwo">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php esc_html_e( 'How to add records in FlowMattic table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To add new records to the table, click the "New Row" button located at the top-left corner of the toolbar. A blank row will be added to the table, allowing you to input the required data into each field. Fill in the details as per your requirements. Once you have entered the details, click the "Update" button to save the changes and include the new record in the table.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingThree">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<?php esc_html_e( 'How to update records in the FlowMattic table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To update table records, double-click on the specific record you want to edit. Enter the updated data in the relevant fields and then click the "Update" button to save the changes. You can also update multiple records at the same time by editing multiple fields and clicking the "Update" button once to apply all changes.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFour">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<?php esc_html_e( 'How to delete rows a FlowMattic Table?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To delete rows, select the checkbox located at the beginning of each row you want to delete, then click the "Delete Row" button. A confirmation popup will appear, requiring you to confirm the deletion. You can delete multiple rows at once by selecting multiple checkboxes. Additionally, if you wish to undo a previous action before saving, you can use the "Cancel Edit" button to discard the changes. ', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFive">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<?php esc_html_e( 'How to apply filter on a specific column?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To apply a filter to a specific column, click on the three dots located next to the column name. From the dropdown menu, select the "Filter" option and choose the desired condition, such as "Starts With," "Equals," "Is Empty," and so on. After selecting the condition and specifying the filter criteria, the table will automatically update to display only the rows that match the applied filter. To remove the filter, use the "Clear Filter" button, which will reset the table and display all the rows again.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSix">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								<?php esc_html_e( 'How to do sorting in a specific colomn?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To sort the records in a specific column, click on the three dots located next to the column name. From the dropdown menu, select the "Sort Ascending" option to arrange the records in ascending order or choose "Sort Descending" to display the records in descending order. Alternatively, you can click directly on the column name to toggle between ascending, descending, and clearing the sorting. This provides a quick and efficient way to organize the table data.', 'flowmattic' ); ?>
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
</style>
<?php 
// FlowMattic_Admin::footer();
add_filter( 'admin_footer_text', function( $text ) {
	return '';
} );

add_filter( 'update_footer', function( $text ) {
	return '';
} );
