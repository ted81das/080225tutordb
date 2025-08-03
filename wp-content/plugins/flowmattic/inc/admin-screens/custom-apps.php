<?php
/**
 * Admin page template for custom apps.
 *
 * @package FlowMattic
 * @since 3.0
 */

FlowMattic_Admin::loader();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				$this_user   = wp_get_current_user();
				$email       = $this_user->user_email;
				$license_key = get_option( 'flowmattic_license_key', '' );
				$license     = wp_flowmattic()->check_license();

				// Get all custom app types.
				$custom_apps_types = (array) flowmattic_get_integrations( 'custom-apps' );

				// Get all connects.
				$all_connects  = wp_flowmattic()->connects_db->get_all();
				$connect_items = array();

				foreach ( $all_connects as $i => $connect ) {
					$connect_items[ $connect->id ] = $connect;
				}

				// Get the db instance.
				$custom_apps_db = wp_flowmattic()->custom_apps_db;

				// Get all custom apps.
				$all_custom_apps = array();
				if ( current_user_can( 'manage_options' ) ) {
					$all_custom_apps = $custom_apps_db->get_all();
				}

				$custom_apps_html = '';

				if ( empty( $all_custom_apps ) ) {
					ob_start();
					?>
					<div class="card border-light mw-100 p-3">
						<div class="card-body">
							<h5 class="card-title"><?php esc_html_e( 'Create and manage custom apps', 'flowmattic' ); ?></h5>
							<p class="card-text">
								<?php esc_html_e( 'With custom apps, you can create a new custom integration for your app inside FlowMattic.', 'flowmattic' ); ?>
								<?php esc_html_e( 'No coding skills required. You just need to get the API information such as API Endpoint URL, API Parameters to pass etc.', 'flowmattic' ); ?>
							</p>
							<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=new' ), 'customAppsNonce' ) ); ?>" class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center mt-2">
								<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
								<?php esc_html_e( 'Create New App', 'flowmattic' ); ?>
							</a>
						</div>
					</div>
					<?php
					$custom_apps_html = ob_get_clean();
				} else {
					$nonce = wp_create_nonce( 'flowmattic-connect-edit' );

					$custom_app_statuses = array(
						'live'  => 0,
						'draft' => 0,
					);

					ob_start();
					?>
					<table class="table bg-white align-middle table-hover">
						<thead>
							<tr>
								<th scope="col"><span class="ps-2"><?php esc_html_e( 'App Logo', 'flowmattic' ); ?></span></th>
								<th scope="col"><span class="ps-2"><?php esc_html_e( 'App ID', 'flowmattic' ); ?></span></th>
								<th scope="col"><?php esc_html_e( 'App Name', 'flowmattic' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Connect', 'flowmattic' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Created At', 'flowmattic' ); ?></th>
								<th scope="col" class="text-center"><?php esc_html_e( 'Actions', 'flowmattic' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
						foreach ( $all_custom_apps as $key => $app_item ) {
							$custom_app_id = $app_item->app_id;
							$app_settings  = maybe_unserialize( $app_item->app_settings );
							?>
							<tr class="app-item-<?php echo esc_attr( $custom_app_id ); ?>">
								<td><span class="ps-2"><?php echo isset( $app_settings['app_logo'] ) ? '<img style="width: 48px; height="48px" src="' . esc_html( $app_settings['app_logo'] ) . '">' : ''; ?></span></td>
								<td><span class="ps-2"><?php echo esc_attr( $custom_app_id ); ?></span></td>
								<td class="app-name"><?php echo esc_attr( $app_item->app_name ); ?></td>
								<td><small><?php echo ( isset( $app_settings['connect_id'] ) && isset( $connect_items[ $app_settings['connect_id'] ] ) ) ? esc_html( $connect_items[ $app_settings['connect_id'] ]->connect_name ) . ' <small>(ID:  ' . $app_settings['connect_id'] . ')</small>' : ''; ?></small></td>
								<td><small><?php echo esc_attr( date_i18n( 'd-m-Y h:i A', strtotime( $app_item->app_time ) ) ); ?></small></td>
								<td>
									<div class="d-flex justify-content-center">
										<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=edit&app_id=' . $custom_app_id ), 'customAppsNonce' ) ); ?>" data-app-id="<?php echo esc_attr( $custom_app_id ); ?>" class="btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php esc_html_e( 'Edit App', 'flowmattic' ); ?>">
											<span class="screen-reader-text"><?php esc_html_e( 'Edit App', 'flowmattic' ); ?></span>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path></svg>
										</a>
										<a href="javascript:void(0);" data-app-id="<?php echo esc_attr( $custom_app_id ); ?>"  class="btn-export-app btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php esc_html_e( 'Export App', 'flowmattic' ); ?>">
											<span class="screen-reader-text"><?php esc_html_e( 'Export App', 'flowmattic' ); ?></span>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
												<path fill="none" d="M22 14V21C22 21.55 21.55 22 21 22H3C2.45 22 2 21.55 2 21V14"></path>
												<path stroke-width="1" stroke="#221b38" d="M22 14V21C22 21.55 21.55 22 21 22H3C2.45 22 2 21.55 2 21V14"></path>
												<path stroke-width="1" stroke="#221b38" d="M12 2V17"></path>
												<path stroke-width="1" stroke="#221b38" d="M17 12L12 17L7 12"></path>
											</svg>
										</a>
										<a href="javascript:void(0);" data-app-id="<?php echo esc_attr( $custom_app_id ); ?>"  class="btn-delete-app btn btn-danger-outline btn-sm text-danger d-inline-flex align-items-center justify-content-center p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php esc_html_e( 'Delete App', 'flowmattic' ); ?>">
											<span class="screen-reader-text"><?php esc_html_e( 'Delete', 'flowmattic' ); ?></span>
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
					$custom_apps_html .= ob_get_clean();
				}
				?>
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="row flex-row-reverse flex-xl-row-reverse flex-sm-column">
						<div class="col-sm-12 fm-custom-apps-list-container ps-4 pe-4">
							<div class="fm-custom-app-task-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-custom-app-heading m-0 d-flex align-items-center">
									<?php esc_html_e( 'Custom Apps', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-custom-apps-header-actions">
									<?php
									$button_type  = '';
									$button_class = '';
									$button_url   = esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=new' ), 'customAppsNonce' ) );

									if ( ! $license || '' === $license_key ) {
										$button_type  = 'disabled';
										$button_class = 'needs-registration';
									} else {
										?>
										<a href="javascript:void(0);" class="flowmattic-import-app btn btn-md btn-secondary me-2" data-toggle="modal" data-target="#app-import-modal">
											<span class="dashicons dashicons-cloud-upload" style="width: 28px;height: 21px;font-size: 28px; color: #fff; line-height: .85em;"></span>
										</a>
										<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>">
											<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
											<?php esc_html_e( 'Create New App', 'flowmattic' ); ?>
										</a>
										<?php
									}
									?>
								</div>
							</div>
							<div class="custom-apps-nav navbar mt-3 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Connect and manage your custom API connections here.', 'flowmattic' ); ?>
									<a href="https://flowmattic.com/features/custom-apps/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="fm-custom-apps-list">
								<?php
								if ( '' === $license_key ) {
									?>
									<div class="card border-light mw-100">
										<div class="card-body text-center">
											<div class="alert alert-primary" role="alert">
												<?php echo esc_html__( 'License key not registered. Please register your license first to use custom apps.', 'flowmattic' ); ?>
											</div>
										</div>
									</div>
									<?php
								} elseif ( '' === $license_key ) {
									?>
										<div class="card border-light mw-100">
											<div class="card-body text-center">
												<div class="alert alert-primary p-4 m-5 text-center" role="alert">
													<?php echo $custom_apps_types['message']; ?>
												</div>
											</div>
										</div>
									<?php
								} else {
									echo $custom_apps_html;
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Import app modal -->
			<div class="col-12 fm-app-import modal fade" id="app-import-modal" aria-hidden="true" data-backdrop="static">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="fm-app-heading m-0">
								<?php esc_html_e( 'Import Custom App', 'flowmattic' ); ?>
							</h5>
							<button type="button" class="btn-close shadow-none" data-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="flowmattic-app-import-field">
								<h4 class="fw-bold"><?php esc_html_e( 'Upload App JSON File', 'flowmattic' ); ?></h4>
								<div class="input-group mb-3 mt-3 border">
									<input class="form-control form-control-lg" type="file" id="app_import_file" style="min-height: auto;padding-left: 10px;" accept="application/json">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// Delete app.
	jQuery( '.btn-delete-app' ).on( 'click', function( e ) {
		var appID = jQuery( this ).attr( 'data-app-id' );

		swalPopup.fire( {
			title: 'Are you sure?',
			text: "Once the selected app is deleted, your workflows using this app will not work until you manually inspect and update them with new app.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
		} ).then( function( result ) {
				if ( result.isConfirmed ) {
				// Show loading.
				swalPopup.fire(
					{
						title: 'Deleting Selected Custom App',
						text: 'Please wait while we delete the selected app. Page will be refreshed once its done.',
						showConfirmButton: false,
						didOpen: function() {
							swalPopup.showLoading();
						}
					}
				);

				// Process delete app ajax.
				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_custom_app_delete_app', workflow_nonce: FMConfig.workflow_nonce, app_id: appID },
					success: function( response ) {
						// Show success popup.
						swalPopup.fire(
							{
								title: 'App Deleted!',
								text: 'Selected app is deleted successfully. Reloading the page.',
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

	// Export the app.
	const exportBtn = jQuery('.btn-export-app');

	exportBtn.on( 'click', function() {
		let appID = jQuery( this ).attr( 'data-app-id' );
		let attributes = {
			action: 'flowmattic_export_custom_app',
			appID: appID,
			workflow_nonce: FMConfig.workflow_nonce
		};

		event.preventDefault();

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: attributes,
			success: function( result ) {
				var a = document.createElement( 'a' ),
					url;

				const str   = JSON.stringify( result );
				const bytes = new TextEncoder().encode( str );
				const blob  = new Blob( [bytes], {
					type: "application/json;charset=utf-8"
				});

				url = window.URL.createObjectURL( blob );

				a.href     = url;
				a.download = 'flowmattic-app-' + appID + '.json';
				document.body.append( a );

				a.click();
				a.remove();

				window.URL.revokeObjectURL( url );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				window.console.log( jqXHR + " :: " + textStatus + " :: " + errorThrown );
			}
		} );
	} );

	// Handle custom app import.
	var appImportFile = document.getElementById( 'app_import_file' );

	// Make sure the DOM element exists.
	if ( appImportFile ) {
		appImportFile.addEventListener( 'change', function() {
			// Make sure a file was selected.
			if ( appImportFile.files.length > 0 ) {
				var reader = new FileReader(); // File reader to read the file.

				// This event listener will happen when the reader has read the file.
				reader.addEventListener( 'load', function() {
					var appData = JSON.parse( reader.result ), // Parse the result into an object.
						attributes = {
							action: 'flowmattic_import_custom_app',
							appData: appData,
							workflow_nonce: FMConfig.workflow_nonce
						};

					if ( 'undefined' !== typeof appData.app_name ) {
						jQuery.ajax( {
							url: ajaxurl,
							data: attributes,
							type: 'POST',
							success: function( response ) {
								swalPopup.fire(
									{
										title: 'App Imported!',
										html: 'Your custom app has been imported successfully, and following changes were made to the imported custom app. Make sure create a new connect and update in this app in order to make it work on this site: <br>' +
												'<ul class="list-group list-group-flush text-start mt-3">' +
													'<li class="list-group-item">Connect related to this app is not imported, you need to create a new one and assign</li>' +
													'<li class="list-group-item">Custom App name will be appended with _IMPORTED to identify the imported App. You can change it easily.</li>' +
												'</ul>',
										icon: 'success',
										showConfirmButton: true,
										timer: 5000,
										timerProgressBar: true
									}
								);

								// Hide the import modal.
								jQuery( '#app-import-modal' ).modal( 'hide' );

								// Reload the page.
								setTimeout( function() {
									window.location = window.location;
								}, 5000 );
							},
							error: function( jqXHR, textStatus, errorThrown ) {
								swalPopup.fire(
									{
										title: 'Custom App Import Failed!',
										text: 'Something went wrong, and the app import was not succesful. Please try again.',
										icon: 'error',
										showConfirmButton: true,
										timer: 3000
									}
								);
							}
						} );
					} else {
						swalPopup.fire(
							{
								title: 'App Import Failed!',
								text: 'Uploaded file is not a valid App JSON file. Please try again with different JSON file.',
								icon: 'error',
								showConfirmButton: true,
								timer: 5000
							}
						);
					}
				} );

				reader.readAsText( appImportFile.files[0] ); // Read the uploaded file.

				// Reset the input.
				jQuery( appImportFile ).val( '' );
			}
		} );
	}
} );
</script>
<?php FlowMattic_Admin::footer(); ?>
