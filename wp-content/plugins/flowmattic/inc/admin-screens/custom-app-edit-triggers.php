<?php
/**
 * Create and manage triggers for custom apps.
 *
 * @package flowmattic
 * @since 3.0
 */

// If App id is not available, create a new one.
$app_id = ( isset( $_GET['app_id'] ) ) ? $_GET['app_id'] : '';

// If no app id, throw an error.
if ( '' === $app_id ) {
	?>
	<div class="notice notice-error settings-error is-dismissible">
		<p>
			<?php esc_html_e( 'Invalid App ID.', 'flowmattic' ); ?> <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps' ), 'customAppsNonce' ) ); ?>" class="text-decoration-none"><?php esc_html_e( 'Get back to Custom Apps', 'flowmattic' ); ?></a>
		</p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'flowmattic' ); ?></span></button>
	</div>
	<?php
	wp_die();
}

// Check if app already exists.
$args           = array(
	'app_id' => esc_attr( $app_id ),
);
$custom_apps_db = wp_flowmattic()->custom_apps_db;
$app_data       = $custom_apps_db->get( $args );

$app_name     = isset( $app_data->app_name ) ? $app_data->app_name : '';
$app_triggers = isset( $app_data->app_triggers ) ? maybe_unserialize( $app_data->app_triggers ) : array();
$app_settings = isset( $app_data->app_settings ) ? maybe_unserialize( $app_data->app_settings ) : array();

// Display the admin loader.
FlowMattic_Admin::loader();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 p-0">
			<div class="col-sm-12 fm-custom-apps-list-container ps-4 pe-4">
				<div class="fm-custom-app-task-header d-flex mb-4 mt-4 justify-content-between">
					<h3 class="fm-custom-app-heading m-0 d-flex align-items-center">
						<?php echo esc_attr__( 'Custom Apps', 'flowmattic' ); ?>
					</h3>
					<div class="flowmattic-custom-apps-header-actions">
						<a href="javascript:void(0);" class="btn btn-md btn-primary d-inline-flex align-items-center create-new-trigger justify-content-center" data-toggle="modal" data-target="#custom-app-new-trigger-modal">
							<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
							<?php echo esc_attr__( 'Create New Trigger', 'flowmattic' ); ?>
						</a>
					</div>
				</div>
				<div class="custom-apps-nav navbar mt-3 mb-3 bg-light">
					<span class="navbar-text ps-3">
						<?php esc_html_e( 'Create and manage your Custom App triggers here.', 'flowmattic' ); ?> <a href="https://flowmattic.com/features/custom-apps/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
					</span>
				</div>
				<div class="fm-custom-apps-list">
					<nav>
						<div class="nav nav-tabs" id="nav-tab" role="tablist">
							<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=edit&app_id=' . $app_id ), 'customAppsNonce' ) ); ?>" class="nav-link d-flex align-items-center" id="nav-app-info-tab" type="button" role="tab" aria-controls="nav-app-info" aria-selected="true">
								<svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
									<path d="M0 0h24v24H0V0z" fill="none"/>
									<path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'App Info', 'flowmattic' ); ?></span>
							</a>
							<button class="nav-link d-flex align-items-center active" id="nav-triggers-tab" data-toggle="tab" data-target="#nav-triggers" type="button" role="tab" aria-controls="nav-triggers" aria-selected="false">
								<svg width="26px" height="26px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M16.24 7L15.1201 7"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M14.3629 3.5L13.6558 4.20711"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M10.1201 2V3"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M6.62012 4.20711L5.91301 3.5"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M5.12012 7L4 7"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" fill="none" d="M11.3825 14.8V10.47C11.3825 9.77 11.9525 9.2 12.6525 9.2C13.3525 9.2 13.9225 9.77 13.9225 10.47V14.8V12.07C13.9225 11.37 14.4925 10.8 15.1925 10.8C15.8925 10.8 16.4625 11.37 16.4625 12.07V14.8V13.62C16.4625 12.95 17.0325 12.4 17.7325 12.4C18.4325 12.4 19.0025 12.95 19.0025 13.62V17.6C19.0025 20.04 16.9725 22 14.4425 22H13.3625C12.2825 22 11.3325 21.63 10.5725 21.08L5.3825 16.13C4.8725 15.64 4.8725 14.8 5.3825 14.31C5.8925 13.82 6.6525 13.93 7.1525 14.42L8.8525 16.01V7.22C8.8525 6.55 9.4225 6 10.1225 6C10.8225 6 11.3925 6.55 11.3925 7.22L11.3825 14.8Z"></path>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'Triggers', 'flowmattic' ); ?></span>
							</button>
							<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=edit&tab=actions&app_id=' . $app_id ), 'customAppsNonce' ) ); ?>" class="nav-link d-flex align-items-center" id="nav-actions-tab" type="button" role="tab" aria-controls="nav-actions" aria-selected="false">
								<svg width="26px" height="26px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="currentColor" fill="none" d="M16 2L6 13.5H10L8 22L18 11.5H14L16 2Z"></path>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'Actions', 'flowmattic' ); ?></span>
							</a>
						</div>
					</nav>
					<div class="tab-content bg-white border border-top-0" id="nav-tabContent">
						<input type="hidden" class="fm-app-id" value="<?php echo esc_attr( $app_id ); ?>" name="app_id">
						<div class="tab-pane fade show active" id="nav-triggers" role="tabpanel" aria-labelledby="nav-triggers-tab">
							<div class="container p-4">
								<div class="custom-apps-nav navbar mb-3 bg-light">
									<span class="navbar-text ps-3">
										<?php esc_html_e( 'Configure your app trigger details like name, description and API details.', 'flowmattic' ); ?>
										<!-- <a href="#" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?>  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a> -->
									</span>
								</div>
								<div class="trigger-list">
									<?php
									if ( empty( $app_triggers ) ) {
										?>
										<div class="card border-light mw-100 p-3">
											<div class="card-body">
												<h5 class="card-title"><?php esc_html_e( 'Create and manage triggers', 'flowmattic' ); ?></h5>
												<p class="card-text">
													<?php esc_html_e( 'Start configuring your custom app triggers which executes the workflow.', 'flowmattic' ); ?>
													<?php esc_html_e( 'FlowMattic supports following trigger types for custom apps.', 'flowmattic' ); ?>
												</p>
												<ul style="list-style: disc;">
													<li><strong><?php esc_attr_e( 'Webhooks', 'flowmattic' ); ?></strong></li>
												</ul>
												<a href="javascript:void(0);" class="btn btn-md btn-primary d-inline-flex align-items-center create-new-trigger justify-content-center mt-2" data-toggle="modal" data-target="#custom-app-new-trigger-modal">
													<?php esc_html_e( 'Create New Trigger', 'flowmattic' ); ?>
												</a>
											</div>
										</div>
										<?php
									} else {
										?>
										<table class="table bg-white align-middle table-hover">
											<thead>
												<tr>
													<th scope="col">Trigger</th>
													<th scope="col" class="text-center">Actions</th>
												</tr>
											</thead>
											<tbody>
											<?php
											foreach ( $app_triggers as $trigger_key => $trigger_item ) {
												$trigger_name        = $trigger_item['trigger_name'];
												$trigger_description = $trigger_item['trigger_description'];
												?>
												<tr class="trigger-item-<?php echo esc_attr( $trigger_key ); ?>">
													<td class="trigger">
														<div class="d-flex">
															<span class="trigger-name d-block"><?php echo esc_attr( $trigger_name ); ?></div>
															<small>
																<?php echo esc_attr( $trigger_description ); ?>
															</small>
														</div>
													</td>
													<td>
														<div class="d-flex justify-content-center">
															<a href="javascript:void(0);" data-trigger-id="<?php echo esc_attr( $trigger_key ); ?>" data-trigger-name="<?php echo esc_attr( $trigger_name ); ?>" data-app-id="<?php echo esc_attr( $app_id ); ?>" class="btn-rename-trigger btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Rename Trigger', 'flowmattic' ); ?>">
																<span class="screen-reader-text"><?php echo esc_html__( 'Rename trigger', 'flowmattic' ); ?></span>
																<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path></svg>
															</a>
															<a href="javascript:void(0);" data-trigger-id="<?php echo esc_attr( $trigger_key ); ?>"  class="btn-edit-trigger btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?>">
																<span class="screen-reader-text"><?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?></span>
																<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
															</a>
															<a href="javascript:void(0);" data-trigger-id="<?php echo esc_attr( $trigger_key ); ?>"  class="btn-delete-trigger btn btn-danger-outline btn-sm text-danger d-inline-flex align-items-center justify-content-center p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete Trigger', 'flowmattic' ); ?>">
																<span class="screen-reader-text"><?php echo esc_html__( 'Delete', 'flowmattic' ); ?></span>
																<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path stroke-width="1" stroke="currentColor" fill="none" d="M16.13 22H7.87C7.37 22 6.95 21.63 6.88 21.14L5 8H19L17.12 21.14C17.05 21.63 16.63 22 16.13 22Z"></path><path stroke-width="1" stroke="currentColor" d="M3.5 8H20.5"></path><path stroke-width="1" stroke="currentColor" d="M10 12V18"></path><path stroke-width="1" stroke="currentColor" d="M14 12V18"></path><path stroke-width="1" stroke="currentColor" fill="none" d="M16 5H8L9.7 2.45C9.89 2.17 10.2 2 10.54 2H13.47C13.8 2 14.12 2.17 14.3 2.45L16 5Z"></path><path stroke-width="1" stroke="currentColor" d="M3 5H21"></path></svg>
															</a>
														</div>
													</td>
												</tr>
												<?php
											}
											?>
											</tbody>
										</table>
										<?php
									}
									?>
									<div class="d-none">
										<button id="addField" class="btn btn-primary mb-4"><?php esc_attr_e( 'Add New Field', 'flowmattic' ); ?></button>
										<div id="formPreview" class="mb-4"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- New Trigger Modal -->
				<div class="modal fade" id="custom-app-new-trigger-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="custom-app-new-trigger-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="custom-app-new-trigger-modal-label"><?php esc_html_e( 'Trigger Settings', 'flowmattic' ); ?></h5>
								<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
							</div>
							<div class="modal-body px-4">
								<form id="fm-new-trigger-form" novalidate>
									<div class="mb-3">
										<label for="trigger_name" class="form-label"><?php esc_html_e( 'Trigger Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="trigger_name" class="form-control fm-textarea input-border" id="trigger_name" required>
										<div class="form-text"><?php esc_html_e( 'Name for this trigger. Eg. Webhook received.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="trigger_description" class="form-label"><?php esc_html_e( 'Trigger Description', 'flowmattic' ); ?></label>
										<input type="search" name="trigger_description" class="form-control fm-textarea input-border" id="trigger_description">
										<div class="form-text"><?php esc_html_e( 'Describe this trigger in a few words Eg. Triggers when webhook data received.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="fm-trigger-type" class="form-label"><?php esc_html_e( 'Trigger Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<select id="fm-trigger-type" name="trigger_type" class="form-control fm-select input-border mw-100 w-100" title="<?php esc_html_e( 'Choose trigger type', 'flowmattic' ); ?>" required>
											<option value="webhooks" selected data-subtext="<?php esc_html_e( 'Setup webhook with instructions.', 'flowmattic' ); ?>"><?php esc_html_e( 'Webhooks with instructions', 'flowmattic' ); ?></option>
										</select>
										<div class="mt-2 mb-3 form-text">
											<?php esc_html_e( 'FlowMattic currently supports only webhooks trigger for custom apps.', 'flowmattic' ); ?>
										</div>
									</div>
									<div class="mb-3">
										<label for="webhook_instructions" class="form-label"><?php esc_html_e( 'Setup Instructions', 'flowmattic' ); ?></label>
										<textarea name="webhook_instructions" rows="3" class="form-control fm-textarea input-border" id="webhook_instructions"></textarea>
										<div class="form-text"><?php esc_html_e( 'Provide instructions to setup this webhook in your app. HTML is allowed.', 'flowmattic' ); ?></div>
									</div>
								</form>
								<p class="mt-3 mb-1"><button type="submit" class="btn btn-save-trigger btn-primary"><?php esc_html_e( 'Save Trigger', 'flowmattic' ); ?></button></p>
							</div>
						</div>
					</div>
				</div>
				<!-- Rename trigger Modal -->
				<div class="modal fade" id="fm-trigger-rename-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-trigger-rename-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered">
						<form id="fm-trigger-rename" class="w-100" novalidate>
							<input class="hidden" name="trigger_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="fm-trigger-rename-modal-label"><?php esc_html_e( 'Rename Trigger', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="trigger_name" class="form-label"><?php esc_html_e( 'Trigger Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="trigger_name" class="form-control fm-textarea form-control" id="trigger_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the new trigger. Eg. Webhook received.', 'flowmattic' ); ?></div>
									</div>
									<p><button type="submit" class="btn btn-rename-auth btn-primary"><?php esc_html_e( 'Rename', 'flowmattic' ); ?></button></p>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery( document ).ready( function() {
	if ( -1 === window.location.href.indexOf( 'app_id' ) ) {
		window.history.replaceState( null, null, window.location.href + '&app_id=<?php echo esc_attr( $app_id ); ?>' );
	}
} );

jQuery( '#custom-app-new-trigger-modal' ).on( 'hide.bs.modal', function( event ) {
	jQuery( this ).find( 'button[data-trigger-id]' ).removeAttr( 'data-trigger-id' );
	jQuery( this ).find( 'form' )[0].reset();
	jQuery( this ).find( 'form' ).removeClass( 'was-validated' );
} );

document.addEventListener('DOMContentLoaded', function() {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// Save the form info settings.
	jQuery( '.btn-save-trigger' ).on( 'click', function() {
		var thisButton = jQuery( this ),
			triggerID = jQuery( this ).attr( 'data-trigger-id' ),
			triggerForm = jQuery( '#fm-new-trigger-form' );

		triggerForm.addClass( 'was-validated' );

		if ( ! triggerForm[0].checkValidity() ) {
			return false;
		}

		const triggerData = new FormData( triggerForm[0] );

		// Add action name and nonce to the form data.
		triggerData.append( 'action', 'flowmattic_custom_app_save_trigger' );
		triggerData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		triggerData.append( 'app_id', '<?php echo esc_attr( $app_id ); ?>' );

		if ( 'undefined' !== typeof triggerID ) {
			triggerData.append( 'trigger_id', triggerID );
		}

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving Trigger',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Add saving animation for button.
		thisButton.addClass( 'disabled' );
		thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Saving...' );

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			processData: false,
			contentType: false,
			data: triggerData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Trigger Saved!',
						text: 'Trigger data is saved successfully. Reloading the page.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				// Remove animation for button and enable it.
				thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Reloading page..' );

				setTimeout( function() {
					window.location = window.location.href;
				}, 500 );
			}
		} );
	} );

	// Edit trigger settings.
	jQuery( '.btn-edit-trigger' ).on( 'click', function() {
		var triggerID = jQuery( this ).attr( 'data-trigger-id' );

		// Show preparing popup.
		swalPopup.fire(
			{
				title: 'Preparing to edit',
				text: 'Please wait while we fetch the trigger data.',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Fetch the trigger settings.
		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: { action: 'flowmattic_custom_app_get_trigger', workflow_nonce: FMConfig.workflow_nonce, trigger_id: triggerID, app_id: '<?php echo esc_attr( $app_id ); ?>' },
			success: function( response ) {
				response = JSON.parse( response );

				if ( 'undefined' !== typeof response.status ) {
					// Show error popup.
					swalPopup.fire(
						{
							title: 'Error',
							text: response.message,
							icon: 'warning',
							showConfirmButton: true,
						}
					);
				} else {
					// Set the trigger ID.
					jQuery( '#custom-app-new-trigger-modal' ).find( '.btn-save-trigger' ).attr( 'data-trigger-id', triggerID );

					// Set the trigger name.
					jQuery( '#custom-app-new-trigger-modal' ).find( '[name="trigger_name"]' ).val( response.trigger_name );

					// Set the trigger description.
					jQuery( '#custom-app-new-trigger-modal' ).find( '[name="trigger_description"]' ).val( response.trigger_description );

					// Set the trigger webhook instructions.
					jQuery( '#custom-app-new-trigger-modal' ).find( '[name="webhook_instructions"]' ).val( response.webhook_instructions );

					// Set the form as non-validated.
					jQuery( '#fm-new-trigger-form' ).removeClass( 'was-validated' );

					// Close the popup.
					swalPopup.close();

					// Show the modal.
					jQuery( '#custom-app-new-trigger-modal' ).modal( 'show' );
				}
			}
		} );
	} );

	// Rename trigger form.
	jQuery( '.btn-rename-trigger' ).on( 'click', function( e ) {
		var triggerID   = jQuery( this ).attr( 'data-trigger-id' ),
			triggerName = jQuery( this ).attr( 'data-trigger-name' );

		// Set the trigger name.
		jQuery( '#fm-trigger-rename-modal' ).find( '[name="trigger_name"]' ).val( triggerName );

		// Set the trigger ID.
		jQuery( '#fm-trigger-rename-modal' ).find( '[name="trigger_id"]' ).val( triggerID );

		// Show the modal.
		jQuery( '#fm-trigger-rename-modal' ).modal( 'show' );
	} );

	// Rename trigger.
	jQuery( '#fm-trigger-rename' ).on( 'submit', function(e) {
		e.preventDefault();

		const form            = jQuery( this );
		const thisButton      = form.find( '.btn-rename-auth' );
		const triggerID       = form.find( '[name="trigger_id"]' ).val();
		const triggerName     = form.find( '[name="trigger_name"]' ).val();
		const triggerFormData = new FormData( form[0] );
		const triggerRow      = jQuery( '.trigger-item-' + triggerID );

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving trigger',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Add action name and nonce to the form data.
		triggerFormData.append( 'action', 'flowmattic_custom_app_rename_trigger' );
		triggerFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		triggerFormData.append( 'trigger_rename', true );
		triggerFormData.append( 'app_id', '<?php echo esc_attr( $app_id ); ?>' );

		// Add saving animation for button.
		thisButton.addClass( 'disabled' );
		thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Renaming...' );

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			processData: false,
			contentType: false,
			data: triggerFormData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Trigger Renamed!',
						text: 'New name for the trigger is saved successfully.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				// Update the name on the page.
				triggerRow.find( '.trigger-name' ).html( triggerName );
				triggerRow.find( '.btn-rename-trigger' ).attr( 'data-trigger-name', triggerName );

				// Hide the modal.
				jQuery( '#fm-trigger-rename-modal' ).modal( 'hide' );

				// Remove animation for button and enable it.
				thisButton.html( 'Rename' );
				thisButton.removeClass( 'disabled' );
			}
		} );
	} );

	// Delete trigger.
	jQuery( '.btn-delete-trigger' ).on( 'click', function( e ) {
		var triggerID = jQuery( this ).attr( 'data-trigger-id' );

		swalPopup.fire( {
			title: 'Are you sure?',
			text: "Once the selected trigger is deleted, your workflows using this trigger will not work until you manually inspect and update them with new trigger event.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
		} ).then( function( result ) {
				if ( result.isConfirmed ) {
				// Show loading.
				swalPopup.fire(
					{
						title: 'Deleting Selected Trigger',
						text: 'Please wait while we delete the selected trigger. Page will be refreshed once its done.',
						showConfirmButton: false,
						didOpen: function() {
							swalPopup.showLoading();
						}
					}
				);

				// Process delete trigger ajax.
				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_custom_app_delete_trigger', workflow_nonce: FMConfig.workflow_nonce, trigger_id: triggerID, app_id: '<?php echo esc_attr( $app_id ); ?>' },
					success: function( response ) {
						// Show success popup.
						swalPopup.fire(
							{
								title: 'Trigger Deleted!',
								text: 'Selected trigger is deleted successfully. Reloading the page.',
								icon: 'success',
								showConfirmButton: false,
								timer: 1500
							}
						);

						setTimeout( function() {
							window.location = window.location.href;
						}, 500 );
					}
				} );
			}
		} );
	} );
} );
</script>
<style type="text/css">
.app-logo-display {
	width: 64px;
	height: 64px;
}
</style>
<?php FlowMattic_Admin::footer(); ?>
