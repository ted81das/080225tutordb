<?php
/**
 * Create and manage custom apps.
 *
 * @package flowmattic
 * @since 3.0
 */

// If App id is not available, create a new one.
$app_id = ( isset( $_GET['app_id'] ) ) ? $_GET['app_id'] : flowmattic_random_string( 7 );

// Check if app already exists.
$args = array(
	'app_id' => esc_attr( $app_id ),
);

$custom_apps_db = wp_flowmattic()->custom_apps_db;
$app_data       = $custom_apps_db->get( $args );

$app_name     = isset( $app_data->app_name ) ? $app_data->app_name : '';
$app_actions  = isset( $app_data->app_actions ) ? maybe_unserialize( $app_data->app_actions ) : array();
$app_triggers = isset( $app_data->app_triggers ) ? maybe_unserialize( $app_data->app_triggers ) : array();
$app_settings = isset( $app_data->app_settings ) ? maybe_unserialize( $app_data->app_settings ) : array();

// Display the admin loader.
FlowMattic_Admin::loader();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 p-0">
			<div class="col-sm-12 fm-connects-list-container ps-4 pe-4">
				<div class="fm-connect-task-header d-flex mb-4 mt-4 justify-content-between">
					<h3 class="fm-connect-heading m-0 d-flex align-items-center">
						<?php echo esc_attr__( 'Custom Apps', 'flowmattic' ); ?>
					</h3>
					<div class="flowmattic-custom-apps-header-actions">
						<?php
						$license_key = get_option( 'flowmattic_license_key', '' );

						$button_type  = '';
						$button_class = '';
						$button_url   = admin_url( 'admin.php?page=flowmattic-custom-apps&app=new' );

						if ( '' === $license_key ) {
							$button_type  = 'disabled';
							$button_class = 'needs-registration';
						}
						?>
						<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>" target="_blank">
							<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
							<?php echo esc_attr__( 'Create New App', 'flowmattic' ); ?>
						</a>
					</div>
				</div>
				<div class="connects-nav navbar mt-3 mb-3 bg-light">
					<span class="navbar-text ps-3">
						<?php esc_html_e( 'Create and manage your Custom App here.', 'flowmattic' ); ?> <a href="https://flowmattic.com/features/custom-apps/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
					</span>
				</div>
				<div class="fm-connects-list">
					<nav>
						<div class="nav nav-tabs" id="nav-tab" role="tablist">
							<button class="nav-link d-flex align-items-center active" id="nav-app-info-tab" data-toggle="tab" data-target="#nav-app-info" type="button" role="tab" aria-controls="nav-app-info" aria-selected="true">
								<svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
									<path d="M0 0h24v24H0V0z" fill="none"/>
									<path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'App Info', 'flowmattic' ); ?></span>
							</button>
							<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=flowmattic-custom-apps&app=edit&tab=triggers&app_id=' . $app_id ), 'customAppsNonce' ) ); ?>" class="nav-link d-flex align-items-center" id="nav-triggers-tab" type="button" role="tab" aria-controls="nav-triggers" aria-selected="false">
								<svg width="26px" height="26px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M16.24 7L15.1201 7"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M14.3629 3.5L13.6558 4.20711"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M10.1201 2V3"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M6.62012 4.20711L5.91301 3.5"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" d="M5.12012 7L4 7"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="currentColor" fill="none" d="M11.3825 14.8V10.47C11.3825 9.77 11.9525 9.2 12.6525 9.2C13.3525 9.2 13.9225 9.77 13.9225 10.47V14.8V12.07C13.9225 11.37 14.4925 10.8 15.1925 10.8C15.8925 10.8 16.4625 11.37 16.4625 12.07V14.8V13.62C16.4625 12.95 17.0325 12.4 17.7325 12.4C18.4325 12.4 19.0025 12.95 19.0025 13.62V17.6C19.0025 20.04 16.9725 22 14.4425 22H13.3625C12.2825 22 11.3325 21.63 10.5725 21.08L5.3825 16.13C4.8725 15.64 4.8725 14.8 5.3825 14.31C5.8925 13.82 6.6525 13.93 7.1525 14.42L8.8525 16.01V7.22C8.8525 6.55 9.4225 6 10.1225 6C10.8225 6 11.3925 6.55 11.3925 7.22L11.3825 14.8Z"></path>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'Triggers', 'flowmattic' ); ?></span>
							</a>
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
						<div class="tab-pane fade show active" id="nav-app-info" role="tabpanel" aria-labelledby="nav-app-info-tab">
							<div class="container p-4">
								<div class="custom-apps-nav navbar mb-3 bg-light">
									<span class="navbar-text ps-3">
										<?php esc_html_e( 'Configure basic details associated with your app like App Name, Description, Logo and Authentication method.', 'flowmattic' ); ?>
										<!-- <a href="#" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a> -->
									</span>
								</div>
								<form id="app-info" novalidate>
									<div class="form-group mb-4">
										<label for="app_name_input" class="form-label"><h4 class="fm-input-title m-0"><?php esc_attr_e( 'App Name', 'flowmattic' ); ?> <span class="badge outline bg-light text-danger">Required</span></h4></label>
										<input class="form-control w-100" id="app_name_input" required name="app_name" value="<?php echo esc_html( $app_name ); ?>">
										<small class="form-text text-muted"><?php esc_attr_e( 'This will be used to display the App name in your workflow.', 'flowmattic' ); ?></small>
									</div>
									<div class="form-group mb-4">
										<label for="app_description_input" class="form-label"><h4 class="fm-input-title m-0"><?php esc_attr_e( 'Description', 'flowmattic' ); ?></h4></label>
										<div class="fm-form-control">
											<textarea class="form-control fm-textarea w-100" name="app_description" rows="1"><?php echo isset( $app_settings['description'] ) ? esc_html( $app_settings['description'] ) : ''; ?></textarea>
										</div>
										<small class="form-text text-muted"><?php esc_attr_e( 'Description is for your internal use only.', 'flowmattic' ); ?></small>
									</div>
									<div class="form-group mb-4 d-flex flex-column gap-2">
										<?php
											$current_connect_id = isset( $app_settings['connect_id'] ) ? esc_html( $app_settings['connect_id'] ) : '';
											$all_connects       = wp_flowmattic()->connects_db->get_all();
										?>
										<label for="app_connect_input" class="form-label"><h4 class="fm-input-title m-0"><?php esc_attr_e( 'Choose Default Authentication', 'flowmattic' ); ?> <span class="badge outline bg-light text-danger">Required</span></h4></label>
										<select id="app_connect_input" name="app_connect_id" required class="app-connect fm-select border mw-100 w-100" title="<?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?>" data-live-search="true">
											<option value="none" <?php echo ( 'none' === $current_connect_id ) ? 'selected' : ''; ?>><?php esc_html_e( 'No Authentication', 'flowmattic' ); ?></option>
											<?php
											foreach ( $all_connects as $key => $connect_item ) {
												$connect_id   = $connect_item->id;
												$connect_name = $connect_item->connect_name;
												$selected     = ( $connect_id === $current_connect_id ) ? 'selected' : '';
												?>
												<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
												<?php
											}
											?>
										</select>
										<small class="form-text text-muted"><?php esc_attr_e( 'Choose the connect account you want to use as default for this app as authentication provider. You can use the different connect in the workflow.', 'flowmattic' ); ?></small>
									</div>
									<div class="form-group mb-4">
										<label for="app_logo_input" class="form-label"><h4 class="fm-input-title"><?php esc_attr_e( 'App Logo', 'flowmattic' ); ?> <span class="badge outline bg-light text-danger">Required</span></h4></label>
										<input type="hidden" class="form-control w-100" id="app_logo_input" name="app_logo" value="<?php echo isset( $app_settings['app_logo'] ) ? $app_settings['app_logo'] : ''; ?>">
										<div class="d-flex align-items-center">
											<div class="app-logo-display me-3 p-1 bg-light border">
												<?php echo isset( $app_settings['app_logo'] ) ? '<img src="' . esc_html( $app_settings['app_logo'] ) . '">' : ''; ?>
											</div>
											<div>
												<button class="btn btn-secondary btn-sm fm-upload-app-logo mb-1"><?php esc_attr_e( 'Choose logo', 'flowmattic' ); ?></button><br>
												<small class="form-text text-muted"><?php esc_attr_e( 'This will be used to display the App logo in your workflow.', 'flowmattic' ); ?></small>
											</div>
										</div>
									</div>
								</form>
								<button class="btn btn-primary fm-save-app mt-3 mb-1"><?php esc_attr_e( 'Save App Info', 'flowmattic' ); ?></button>
							</div>
						</div>
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
document.addEventListener('DOMContentLoaded', function() {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// Function to handle the selection of an image from the Media Library.
	function fm_upload_app_logo_image(mediaUploader) {
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			var imageUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

			document.getElementById('app_logo_input').value = imageUrl;

			var appLogoDisplay = document.querySelector('.app-logo-display');
			appLogoDisplay.innerHTML = '<img src="' + imageUrl + '" alt="App Logo">';
		});

		mediaUploader.open();
	}

	var fmUploadAppLogoButton = document.querySelector('.fm-upload-app-logo');

	if (fmUploadAppLogoButton) {
		fmUploadAppLogoButton.addEventListener('click', function(e) {
			e.preventDefault();

			var mediaUploader = wp.media.frames.file_frame = wp.media({
				title: 'Select an App Logo',
				button: {
					text: 'Select App Logo'
				},
				multiple: false
			});

			fm_upload_app_logo_image(mediaUploader);
		});
	}

	// Save the form info settings.
	jQuery( '.fm-save-app' ).on( 'click', function() {
		var thisButton = jQuery( this ),
			appInfoForm = jQuery( '#app-info' );

		appInfoForm.addClass( 'was-validated' );

		if ( ! appInfoForm[0].checkValidity() ) {
			return false;
		}

		const appFormData = new FormData( appInfoForm[0] );

		// Add action name and nonce to the form data.
		appFormData.append( 'action', 'flowmattic_custom_app_save_info' );
		appFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		appFormData.append( 'app_id', '<?php echo esc_attr( $app_id ); ?>' );

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving App',
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
			data: appFormData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'App Saved!',
						text: 'Your custom app is saved successfully.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				// Remove animation for button and enable it.
				thisButton.html( 'Save App Info' );
				thisButton.removeClass( 'disabled' );
			}
		} );
	} );
});
</script>
<style type="text/css">
.app-logo-display {
	width: 64px;
	height: 64px;
}
#app-info .dropdown-menu.show {
	transform: none !important;
	margin-top: 39px;
	border-top: none;
	border-top-left-radius: 0;
	border-top-right-radius: 0;
}
</style>
<?php FlowMattic_Admin::footer(); ?>
