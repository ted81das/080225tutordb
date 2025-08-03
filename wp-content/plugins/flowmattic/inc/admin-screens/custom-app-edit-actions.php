<?php
/**
 * Create and manage actions for custom apps.
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
$app_actions  = isset( $app_data->app_actions ) ? maybe_unserialize( $app_data->app_actions ) : array();
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
						<a href="javascript:void(0);" class="btn btn-md btn-primary d-inline-flex align-items-center create-new-action justify-content-center" data-toggle="modal" data-target="#custom-app-new-action-modal">
							<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
							<?php echo esc_attr__( 'Create New Action', 'flowmattic' ); ?>
						</a>
					</div>
				</div>
				<div class="custom-apps-nav navbar mt-3 mb-3 bg-light">
					<span class="navbar-text ps-3">
						<?php esc_html_e( 'Create and manage your Custom App actions here.', 'flowmattic' ); ?> <a href="https://flowmattic.com/features/custom-apps/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
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
							<button href="javascript:void(0);" data-toggle="tab" data-target="#nav-actions" class="nav-link d-flex align-items-center active" id="nav-actions-tab" type="button" role="tab" aria-controls="nav-actions" aria-selected="false">
								<svg width="26px" height="26px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="currentColor" fill="none" d="M16 2L6 13.5H10L8 22L18 11.5H14L16 2Z"></path>
								</svg>
								<span class="ms-2"><?php esc_html_e( 'Actions', 'flowmattic' ); ?></span>
							</button>
						</div>
					</nav>
					<div class="tab-content bg-white border border-top-0" id="nav-tabContent">
						<input type="hidden" class="fm-app-id" value="<?php echo esc_attr( $app_id ); ?>" name="app_id">
						<div class="tab-pane fade show active" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
							<div class="container p-4">
								<div class="custom-apps-nav navbar mb-3 bg-light">
									<span class="navbar-text ps-3">
										<?php esc_html_e( 'Configure your app action details like name, description and API details.', 'flowmattic' ); ?>
										<!-- <a href="#" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?>  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a> -->
									</span>
								</div>
								<div class="action-list">
									<?php
									if ( empty( $app_actions ) ) {
										?>
										<div class="card border-light mw-100 p-3">
											<div class="card-body">
												<h5 class="card-title"><?php esc_html_e( 'Create and manage actions', 'flowmattic' ); ?></h5>
												<p class="card-text">
													<?php esc_html_e( 'Start configuring your custom app actions which executes the workflow.', 'flowmattic' ); ?> 
													<?php esc_html_e( 'No coding skills required. What you need is just your API documentation.', 'flowmattic' ); ?>
												</p>
												<ul class="fs-6" style="list-style: disc;">
													<li><?php esc_html_e( 'Provide action name and short description', 'flowmattic' ); ?></li>
													<li><?php esc_html_e( 'Configure the API endpoints including URL path variables', 'flowmattic' ); ?></li>
													<li><?php esc_html_e( 'Set the required headers, request body and URL params', 'flowmattic' ); ?></li>
												</ul>
												<a href="javascript:void(0);" class="btn btn-md btn-primary d-inline-flex align-items-center create-new-action justify-content-center mt-2" data-toggle="modal" data-target="#custom-app-new-action-modal">
													<?php esc_html_e( 'Create New Action', 'flowmattic' ); ?>
												</a>
											</div>
										</div>
										<?php
									} else {
										?>
										<table class="table bg-white align-middle mb-0 table-hover">
											<thead>
												<tr>
													<th scope="col">Action</th>
													<th scope="col" class="text-center">Actions</th>
												</tr>
											</thead>
											<tbody>
											<?php
											foreach ( $app_actions as $action_key => $action_item ) {
												$action_name        = $action_item['action_name'];
												$action_description = $action_item['action_description'];
												?>
												<tr class="action-item-<?php echo esc_attr( $action_key ); ?>">
													<td class="action">
														<div class="d-flex">
															<span class="action-name d-block"><?php echo esc_attr( $action_name ); ?></div>
															<small>
																<?php echo esc_attr( $action_description ); ?>
															</small>
														</div>
													</td>
													<td>
														<div class="d-flex justify-content-center">
															<a href="javascript:void(0);" data-action-id="<?php echo esc_attr( $action_key ); ?>" data-action-name="<?php echo esc_attr( $action_name ); ?>" data-app-id="<?php echo esc_attr( $app_id ); ?>" class="btn-rename-action btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Rename Action', 'flowmattic' ); ?>">
																<span class="screen-reader-text"><?php echo esc_html__( 'Rename action', 'flowmattic' ); ?></span>
																<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path></svg>
															</a>
															<a href="javascript:void(0);" data-action-id="<?php echo esc_attr( $action_key ); ?>"  class="btn-edit-action btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?>">
																<span class="screen-reader-text"><?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?></span>
																<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
															</a>
															<a href="javascript:void(0);" data-action-id="<?php echo esc_attr( $action_key ); ?>"  class="btn-delete-action btn btn-danger-outline btn-sm text-danger d-inline-flex align-items-center justify-content-center p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete Action', 'flowmattic' ); ?>">
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
				<!-- New Action Modal -->
				<div class="modal fade" id="custom-app-new-action-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="custom-app-new-action-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="custom-app-new-action-modal-label"><?php esc_html_e( 'Action Settings', 'flowmattic' ); ?></h5>
								<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
							</div>
							<div class="modal-body px-4">
								<form id="fm-new-action-form" novalidate>
									<div class="mb-3">
										<label for="action_name" class="form-label"><?php esc_html_e( 'Action Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="action_name" class="form-control fm-textarea input-border" id="action_name" required>
										<div class="form-text"><?php esc_html_e( 'Name for this action. Eg. Create user.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-0">
										<label for="action_description" class="form-label"><?php esc_html_e( 'Action Description', 'flowmattic' ); ?></label>
										<input type="search" name="action_description" class="form-control fm-textarea input-border" id="action_description">
										<div class="form-text"><?php esc_html_e( 'Describe this action in a few words Eg. Create new user.', 'flowmattic' ); ?></div>
									</div>
									<hr class="bg-secondary my-3">
									<h4 class="fm-group-title fw-bold"><?php esc_html_e( 'Action Event API Configuration', 'flowmattic' ); ?></h4>
									<div class="form-text"><?php esc_html_e( 'Set the API endpoint, body and header parameters as required.', 'flowmattic' ); ?></div>
									<div class="mb-3 bg-light import-curl-button-wrap ps-3 py-1 mt-3">
										<p class="mt-3 mb-1"><button type="button" onclick="opencURLInputPopup();" class="btn btn-import-from-curl btn-outline-success"><?php esc_html_e( 'Import cURL Request', 'flowmattic' ); ?></button></p>
										<p class="mb-2"><small><?php esc_html_e( 'Import cURL request to auto-fill the API details.', 'flowmattic' ); ?></small></p>
									</div>
									<nav class="mt-3">
										<div class="nav nav-tabs" id="nav-tab" role="tablist">
											<button class="nav-link active" id="nav-basic-tab" data-toggle="tab" data-target="#nav-basic" type="button" role="tab" aria-controls="nav-basic" aria-selected="true">Basic</button>
											<button class="nav-link" id="nav-headers-tab" data-toggle="tab" data-target="#nav-headers" type="button" role="tab" aria-controls="nav-headers" aria-selected="false">Headers</button>
											<button class="nav-link" id="nav-body-tab" data-toggle="tab" data-target="#nav-body" type="button" role="tab" aria-controls="nav-body" aria-selected="false">Body</button>
										</div>
									</nav>
									<div class="tab-content pt-3 border border-top-0 px-3" id="nav-tabContent">
										<div class="tab-pane fade show active" id="nav-basic" role="tabpanel" aria-labelledby="nav-basic-tab">
											<div class="mb-3">
												<label for="fm-http-method" class="form-label"><?php esc_html_e( 'HTTP Method', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
												<select id="fm-http-method" name="http_method" class="form-control fm-select input-border w-100" title="<?php esc_html_e( 'Choose HTTP Method', 'flowmattic' ); ?>" required>
													<option value="get"><?php esc_html_e( 'GET', 'flowmattic' ); ?></option>
													<option value="post"><?php esc_html_e( 'POST', 'flowmattic' ); ?></option>
													<option value="put"><?php esc_html_e( 'PUT', 'flowmattic' ); ?></option>
													<option value="delete"><?php esc_html_e( 'DELETE', 'flowmattic' ); ?></option>
													<option value="patch"><?php esc_html_e( 'PATCH', 'flowmattic' ); ?></option>
												</select>
												<div class="mt-2 mb-3 form-text">
													<?php esc_html_e( 'Select how the API request to be sent.', 'flowmattic' ); ?>
												</div>
											</div>
											<div class="mb-3">
												<label for="endpoint_url" class="form-label"><?php esc_html_e( 'API Endpoint URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
												<textarea type="search" name="endpoint_url" class="form-control fm-textarea input-border" rows="1" id="endpoint_url" required placeholder="https://api.domain.com/v1/subscribers/{{subscriber_id}}/update"></textarea>
												<div class="form-text"><?php esc_html_e( 'URL that accepts the API request. For dynamic URL params, wrap the path params in {{ and }}. Eg. https://api.domain.com/v1/subscribers/{{subscriber_id}}/update', 'flowmattic' ); ?></div>
											</div>
											<div class="mb-3">
												<label for="fm-content-type" class="form-label"><?php esc_html_e( 'Request Content Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
												<select id="fm-content-type" name="content_type" class="form-control fm-select input-border w-100" title="<?php esc_html_e( 'Choose HTTP Method', 'flowmattic' ); ?>" data-live-search="true" required>
													<option value="json"><?php esc_html_e( 'JSON', 'flowmattic' ); ?></option>
													<option value="form_data"><?php esc_html_e( 'Form-data', 'flowmattic' ); ?></option>
													<option value="encoded_form_data"><?php esc_html_e( 'Encoded Form Data', 'flowmattic' ); ?></option>
													<option value="text"><?php esc_html_e( 'Text', 'flowmattic' ); ?></option>
													<option value="html"><?php esc_html_e( 'HTML', 'flowmattic' ); ?></option>
													<option value="xml"><?php esc_html_e( 'XML', 'flowmattic' ); ?></option>
												</select>
												<div class="mt-2 mb-3 form-text">
													<?php esc_html_e( 'Refer your API documentation to find the correct content type for this request.', 'flowmattic' ); ?>
												</div>
											</div>
										</div>
										<div class="tab-pane fade" id="nav-headers" role="tabpanel" aria-labelledby="nav-headers-tab">
											<div class="mb-3">
												<div class="form-check form-switch">
													<input id="fm-checkbox-headers" class="form-check-input fm-checkbox" onchange="toggleDynamicParams( this, 'headers');" name="add_headers" type="checkbox" value="yes"/>
													<label for="fm-checkbox-headers" class="form-check-label form-label"><?php esc_attr_e( 'Set Headers', 'flowmattic' ); ?></label>
												</div>
												<div class="d-none fm-api-request-headers-body data-headers data-dynamic-fields bg-light p-3" data-field-name="api_headers">
													<div class="fm-dynamic-input-wrap fm-api-request-headers">
														<div class="data-dynamic-field d-flex align-items-center mb-3 w-100">
															<div class="fm-dynamic-input-field w-100 me-3">
																<div class="input-group">
																	<?php
																	$uid = flowmattic_random_string( 6 );
																	?>
																	<input type="hidden" class="dynamic-field-options" name="dynamic-headers-options[<?php echo esc_attr( $uid ); ?>]" value=""/>
																	<input class="fm-dynamic-inputs input-border" name="dynamic-headers-key[<?php echo esc_attr( $uid ); ?>]" type="text" placeholder="Enter header key" value="" style="width: calc( 100% - 45px );"/>
																	<a href="javascript:void(0);" onclick="openFieldEditSettings( this, 'headers' );" data-field-id="<?php echo esc_attr( $uid ); ?>" class="dynamic-field-settings input-group-text"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg></a>
																</div>
															</div>
															<a href="javascript:void(0);" onclick="removeDynamicField( this );" class="dynamic-input-remove btn-remove-header text-danger btn btn-primary-outline btn-sm p-0" style="width: 26px; height: 26px;">
																<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
															</a>
														</div>
													</div>
													<div class="dynamic-input-add-more fm-api-headers-add-more">
														<a href="javascript:void(0);" onclick="addDynamicField('headers');" class="btn flowmattic-button btn-sm btn-success btn-add-more-headers"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
													</div>
												</div>
											</div>
										</div>
										<div class="tab-pane fade" id="nav-body" role="tabpanel" aria-labelledby="nav-body-tab">
											<div class="mb-3">
											<div class="mb-3 bg-light import-curl-button-wrap ps-3 py-1 mt-3">
												<p class="mt-3 mb-1"><button type="button" onclick="openJsonInputPopup();" class="btn btn-import-from-curl btn-outline-success"><?php esc_html_e( 'Import JSON', 'flowmattic' ); ?></button></p>
												<p class="mb-2"><small><?php esc_html_e( 'Import JSON data to auto-fill the Body Parameters and Request Body.', 'flowmattic' ); ?></small></p>
											</div>
												<div class="form-check form-switch">
													<input id="fm-checkbox-params" class="form-check-input fm-checkbox" onchange="toggleDynamicParams( this, 'params');" name="add_params" type="checkbox" value="yes"/>
													<label for="fm-checkbox-params" class="form-check-label form-label"><?php esc_attr_e( 'Set Body/Query/Path Parameters', 'flowmattic' ); ?></label>
												</div>
												<div class="d-none fm-api-request-params-body data-params data-dynamic-fields bg-light p-3" data-field-name="api_params">
													<div class="fm-dynamic-input-wrap fm-api-request-params">
														<div class="data-dynamic-field d-flex align-items-center mb-3 w-100">
															<div class="fm-dynamic-input-field w-100 me-3">
																<div class="input-group">
																	<?php
																	$uid = flowmattic_random_string( 6 );
																	?>
																	<input type="hidden" class="dynamic-field-options" name="dynamic-params-options[<?php echo esc_attr( $uid ); ?>]" value=""/>
																	<input class="fm-dynamic-inputs input-border" name="dynamic-params-key[<?php echo esc_attr( $uid ); ?>]" type="text" placeholder="Enter api parameter key" value="" style="width: calc( 100% - 45px );"/>
																	<a href="javascript:void(0);" onclick="openFieldEditSettings( this, 'params' );" data-field-id="<?php echo esc_attr( $uid ); ?>" class="dynamic-field-settings input-group-text"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg></a>
																</div>
															</div>
															<a href="javascript:void(0);" onclick="removeDynamicField( this );" class="dynamic-input-remove btn-remove-param text-danger btn btn-primary-outline btn-sm p-0" style="width: 26px; height: 26px;">
																<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
															</a>
														</div>
													</div>
													<div class="dynamic-input-add-more fm-api-params-add-more">
														<a href="javascript:void(0);" onclick="addDynamicField('params');" class="btn flowmattic-button btn-sm btn-success btn-add-more-params"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
													</div>
													<div class="form-text"><?php esc_html_e( 'Make sure the field name matches with key in your API documentation. eg. first_name', 'flowmattic' ); ?></div>
												</div>
											</div>
											<div class="mb-3">
												<label for="raw_data" class="form-label"><?php esc_html_e( 'Request Body ( Raw JSON / XML )', 'flowmattic' ); ?></label>
												<textarea class="form-control fm-textarea w-100" name="raw_data" rows="4" type="search" placeholder='{
	"name": "{{subscriber_name}}",
	"email": "{{subscriber_email}}"
}'></textarea>
												<div class="form-text">
													<?php esc_html_e( 'Enter the JSON code to be sent as API request. For dynamic values based on the above params, wrap the params in {{ and }} for the value in JSON.', 'flowmattic' ); ?>
													<?php echo sprintf( __( 'Make sure to <a href="%s" class="text-decoration-none" target="_blank">validate the JSON</a> code to avoid failing of task.', 'flowmattic' ), 'https://jsonlint.com/' ); ?>
												</div>
											</div>
										</div>
									</div>
								</form>
								<p class="mt-3 mb-1"><button type="submit" class="btn btn-save-action btn-primary"><?php esc_html_e( 'Save Action', 'flowmattic' ); ?></button></p>
							</div>
						</div>
					</div>
				</div>
				<!-- Rename action Modal -->
				<div class="modal fade" id="fm-action-rename-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-action-rename-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered">
						<form id="fm-action-rename" class="w-100" novalidate>
							<input class="hidden" name="action_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="fm-action-rename-modal-label"><?php esc_html_e( 'Rename Action', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="action_name" class="form-label"><?php esc_html_e( 'Action Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="action_name" class="form-control fm-textarea input-border" id="action_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the new action. Eg. Webhook received.', 'flowmattic' ); ?></div>
									</div>
									<p><button type="submit" class="btn btn-rename-auth btn-primary"><?php esc_html_e( 'Rename', 'flowmattic' ); ?></button></p>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- Edit header field Modal -->
				<div class="modal fade" id="fm-action-headers-field-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-action-headers-field-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered" style="width: 600px;max-width: 600px;">
						<form id="fm-action-edit-headers-field" class="w-100" novalidate>
							<input class="hidden" name="field_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="fm-action-headers-field-modal-label"><?php esc_html_e( 'Header Field Settings', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="field_label" class="form-label"><?php esc_html_e( 'Field Label', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="field_label" class="form-control fm-textarea input-border" id="field_label" required>
										<div class="form-text"><?php esc_html_e( 'Field label will be displayed in the action step against this field.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="help_text" class="form-label"><?php esc_html_e( 'Help Text', 'flowmattic' ); ?></label>
										<textarea type="search" name="help_text" class="form-control fm-textarea input-border" rows="2" id="help_text"></textarea>
										<div class="form-text"><?php esc_html_e( 'What this field is all about. HTML allowed. Make sure to set the links to be opened in new tab.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="default_value" class="form-label"><?php esc_html_e( 'Default Value', 'flowmattic' ); ?></label>
										<input type="search" name="default_value" class="form-control fm-textarea input-border" id="default_value">
										<div class="form-text"><?php esc_html_e( 'If set, this value will be set as the default value for the input field when loaded in the action step.', 'flowmattic' ); ?></div>
									</div>
									<p><button type="submit" class="btn btn-save-field btn-primary"><?php esc_html_e( 'Save Field', 'flowmattic' ); ?></button></p>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- Edit params field Modal -->
				<div class="modal fade" id="fm-action-params-field-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-action-params-field-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered" style="width: 600px;max-width: 600px;">
						<form id="fm-action-edit-params-field" class="w-100" novalidate>
							<input class="hidden" name="field_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="fm-action-params-field-modal-label"><?php esc_html_e( 'Body Field Settings', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<div class="mb-3">
										<label for="field_label" class="form-label"><?php esc_html_e( 'Field Label', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="field_label" class="form-control fm-textarea input-border" id="field_label" required>
										<div class="form-text"><?php esc_html_e( 'Field label will be displayed in the action step against this field.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="field_type" class="form-label"><?php esc_html_e( 'Field Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<select id="field_type" name="field_type" class="form-control fm-select input-border w-100" title="<?php esc_html_e( 'Choose input field type', 'flowmattic' ); ?>" required>
											<option value="string" data-subtext="<?php esc_attr_e( 'Simple input text.', 'flowmattic' ); ?>"><?php esc_html_e( 'String', 'flowmattic' ); ?></option>
											<option value="number" data-subtext="<?php esc_attr_e( 'Input field with number selection.', 'flowmattic' ); ?>"><?php esc_html_e( 'Number', 'flowmattic' ); ?></option>
											<option value="boolean" data-subtext="<?php esc_attr_e( 'Dropdown field with Yes/No options.', 'flowmattic' ); ?>"><?php esc_html_e( 'Boolean', 'flowmattic' ); ?></option>
											<option value="select" data-subtext="<?php esc_attr_e( 'Dropdown populated with options set.', 'flowmattic' ); ?>"><?php esc_html_e( 'Select', 'flowmattic' ); ?></option>
										</select>
										<div class="form-text"><?php esc_html_e( 'Displays the input field accordingly.', 'flowmattic' ); ?></div>
									</div>
									<div class="form-group bg-light p-3 mb-3 d-none dynamic-select-options">
										<div class="fm-select-input-headers d-flex mb-2">
											<label class="header-title w-50 me-3"><?php esc_html_e( 'Option Label', 'flowmattic' ); ?></label>
											<label class="header-title w-50 me-3"><?php esc_html_e( 'Option Value', 'flowmattic' ); ?></label>
											<span class="p-2 py-0">&nbsp;</span>
										</div>
										<div class="select-options">
											<div class="fm-select-input-wrap d-flex mb-2">
												<div class="fm-select-input-field w-50 me-2">
													<input class="fm-select-inputs w-100" name="select-field-key[]" type="search" placeholder="key" value="" />
												</div>
												<div class="fm-select-input-field w-50 me-2">
													<input class="fm-select-inputs w-100" name="select-field-value[]" type="search" placeholder="value" value="" />
												</div>
												<a href="javascript:void(0);" onclick="removeSelectOption( this );" class="select-input-remove btn-remove-option text-danger">
													<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
												</a>
											</div>
										</div>
										<div class="select-input-add-more mt-3">
											<a href="javascript:void(0);" onclick="addSelectOption();" class="btn flowmattic-button btn-sm btn-success btn-add-more-option"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
										</div>
									</div>
									<div class="mb-3">
										<label for="field_required" class="form-label"><?php esc_html_e( 'Field Required', 'flowmattic' ); ?></label>
										<select id="field_required" name="field_required" class="form-control fm-select input-border w-100" title="<?php esc_html_e( 'Choose option', 'flowmattic' ); ?>">
											<option value="yes" data-subtext="<?php esc_attr_e( 'Field is required.', 'flowmattic' ); ?>"><?php esc_html_e( 'Yes', 'flowmattic' ); ?></option>
											<option value="no" data-subtext="<?php esc_attr_e( 'Field is optional.', 'flowmattic' ); ?>"><?php esc_html_e( 'No', 'flowmattic' ); ?></option>
										</select>
										<div class="form-text"><?php esc_html_e( 'Is this a required field?', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="path_variable" class="form-label"><?php esc_html_e( 'Is this a path variable?', 'flowmattic' ); ?></label>
										<select id="path_variable" name="path_variable" class="form-control fm-select input-border w-100" title="<?php esc_html_e( 'Choose option', 'flowmattic' ); ?>">
											<option value="yes" data-subtext="<?php esc_attr_e( 'Field used in endpoint URL as path or query string.', 'flowmattic' ); ?>"><?php esc_html_e( 'Yes', 'flowmattic' ); ?></option>
											<option value="no" data-subtext="<?php esc_attr_e( 'Field is not used in endpoint URL.', 'flowmattic' ); ?>"><?php esc_html_e( 'No', 'flowmattic' ); ?></option>
										</select>
										<div class="form-text"><?php esc_html_e( 'Is this a field used in endpoint URL as path or query string? If yes, it will be automatically excluded from the request body, to avoid conflicts.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="help_text" class="form-label"><?php esc_html_e( 'Help Text', 'flowmattic' ); ?></label>
										<textarea type="search" name="help_text" class="form-control fm-textarea input-border" rows="2" id="help_text"></textarea>
										<div class="form-text"><?php esc_html_e( 'What this field is all about. HTML allowed. Make sure to set the links to be opened in new tab.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3">
										<label for="default_value" class="form-label"><?php esc_html_e( 'Default Value', 'flowmattic' ); ?></label>
										<input type="search" name="default_value" class="form-control fm-textarea input-border" id="default_value">
										<div class="form-text"><?php esc_html_e( 'If set, this will be used as default value if no value is submitted for this field in the action step.', 'flowmattic' ); ?></div>
									</div>
									<p><button type="submit" class="btn btn-save-field btn-primary"><?php esc_html_e( 'Save Field', 'flowmattic' ); ?></button></p>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- cURL request input Modal -->
			<div class="modal fade" id="fm-curl-request-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-curl-request-modal-label" aria-hidden="true">
				<div class="modal-dialog modal-xl modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="fm-curl-request-modal-label"><?php esc_html_e( 'cURL Request', 'flowmattic' ); ?></h5>
							<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="curl_request" class="form-label"><?php esc_html_e( 'cURL Request Code', 'flowmattic' ); ?></label>
								<form id="fm-curl-request-form" class="w-100" novalidate>
								<textarea class="form-control fm-textarea w-100" name="curl_request" rows="16" placeholder='curl https://api.openai.com/v1/chat/completions \
-H "Content-Type: application/json" \
-H "Authorization: Bearer $OPENAI_API_KEY" \
-d `{
	"model": "gpt-4o",
	"messages": [
		{
			"role": "system",
			"content": "You are a helpful assistant."
		},
		{
			"role": "user",
			"content": "Hello!"
		}
	]
}`
'></textarea>
								</form>
								<div class="form-text"><?php esc_html_e( 'Copy the cURL request code here and click on the "Process Request" button to import as action.', 'flowmattic' ); ?></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
							<button type="button" class="btn btn-primary" onclick="processcURLRequest( this );" data-type="curl"><?php esc_html_e( 'Process Request', 'flowmattic' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<!-- JSON request input Modal -->
			<div class="modal fade" id="fm-json-request-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-json-request-modal-label" aria-hidden="true">
				<div class="modal-dialog modal-xl modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="fm-json-request-modal-label"><?php esc_html_e( 'json Request', 'flowmattic' ); ?></h5>
							<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="json_request" class="form-label"><?php esc_html_e( 'JSON Code', 'flowmattic' ); ?></label>
								<form id="fm-json-request-form" class="w-100" novalidate>
								<textarea class="form-control fm-textarea w-100" name="curl_request" rows="16" placeholder='{
	"model": "gpt-4o",
	"messages": [
		{
			"role": "system",
			"content": "You are a helpful assistant."
		},
		{
			"role": "user",
			"content": "Hello!"
		}
	]
}
'></textarea>
								</form>
								<div class="form-text"><?php esc_html_e( 'Copy the JSON code here from your API documentation, and click on the "Process Request" button to import.', 'flowmattic' ); ?></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
							<button type="button" class="btn btn-primary" onclick="processcURLRequest( this );" data-type="json"><?php esc_html_e( 'Process Request', 'flowmattic' ); ?></button>
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

	// Show/hide dynamic select options.
	jQuery( '#field_type' ).on( 'change', function() {
		var value = jQuery( this ).val(),
			selectGroup = jQuery( '.dynamic-select-options' );

		if ( 'select' === value ) {
			selectGroup.removeClass( 'd-none' );
		} else {
			selectGroup.addClass( 'd-none' );
		}
	} );
} );

jQuery( '#custom-app-new-action-modal' ).on( 'hide.bs.modal', function( event ) {
	jQuery( this ).find( 'button[data-action-id]' ).removeAttr( 'data-action-id' );
	jQuery( this ).find( 'form' )[0].reset();
	jQuery( this ).find( 'select' ).selectpicker( 'val', '' );
	jQuery( this ).find( '.fm-checkbox' ).removeAttr( 'checked' );
	jQuery( this ).find( '.fm-checkbox' ).trigger( 'change' );
	jQuery( this ).find( 'form' ).removeClass( 'was-validated' );
	// Show the import from cURL div.
	jQuery( '.import-curl-button-wrap' ).removeClass( 'd-none' );

	// Enable the import from cURL button.
	jQuery( '.btn-import-from-curl' ).removeAttr( 'disabled' );
} );

jQuery( '#fm-action-headers-field-modal, #fm-action-params-field-modal' ).on( 'hide.bs.modal', function( event ) {
	// Reset the form.
	jQuery( this ).find( 'form' )[0].reset();

	// Set the value for selectpicker fields to blank.
	jQuery( this ).find( 'form select' ).selectpicker( 'val', '' );

	// Restore the parent modal visibility.
	jQuery( '#custom-app-new-action-modal' ).css( 'opacity', '1' );
} );

// Generate a random string.
function fmRandomString( length ) {
	var result           = [],
		characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
		charactersLength = characters.length;

	for ( var i = 0; i < length; i++ ) {
		result.push( characters.charAt( Math.floor( Math.random() * charactersLength ) ) );
	}

	return result.join('');
}

// Function to toggle the dynamic fields for header and body.
function toggleDynamicParams( checkBox, type ) {
	if ( jQuery( checkBox ).is( ':checked' ) ) {
		jQuery( '.data-' + type ).removeClass( 'd-none' );
	} else {
		jQuery( '.data-' + type ).addClass( 'd-none' );
	}
}

// Function to add more dynamic fields to header and body.
function addDynamicField( type, id, value, optionsValue ) {
	var dataItem = jQuery( '.data-' + type ),
		idString = ( 'undefined' !== typeof id ) ? id : fmRandomString( 6 ),
		value = ( 'undefined' !== typeof value ) ? value : '',
		dataWrapper = dataItem.find( '.data-dynamic-field:first-child' ).clone();

	dataWrapper.find( 'input.fm-dynamic-inputs' ).val( value );
	dataWrapper.find( '.dynamic-field-settings' ).attr( 'data-field-id', idString );
	dataWrapper.find( '.fm-dynamic-inputs' ).attr( 'name', 'dynamic-' + type + '-key[' + idString + ']' );
	dataWrapper.find( '.dynamic-field-options' ).attr( { name: 'dynamic-' + type + '-options[' + idString + ']' } );

	if ( 'undefined' !== optionsValue ) {
		dataWrapper.find( '[name="dynamic-' + type +'-options[' + idString + ']"' ).val( optionsValue );
	}

	dataItem.find( '.fm-dynamic-input-wrap' ).append( dataWrapper );
}

// Function to remove dynamic fields to header and body.
function removeEmptyDynamicField( type ) {
	var dataItem = jQuery( '.data-' + type ),
		inputFields = dataItem.find( 'input.fm-dynamic-inputs' );

	// Loop through all input fields.
	_.each( inputFields, function( input ) {
		// Bail, if only one field is there.
		if ( 1 === dataItem.find( 'input.fm-dynamic-inputs' ).length ) {
			return false;
		}

		// Check if the input field is empty.
		if (input.value.trim() === '') {
			// Get the wrapper div.
			const wrapperDiv = input.closest('.data-dynamic-field');
			
			// Remove the wrapper div.
			if ( wrapperDiv ) {
				wrapperDiv.remove();
			}
		}
	} );
}

// Function to remove the dynamic field.
function removeDynamicField( removeBtn ) {
	if ( 1 !== jQuery( removeBtn ).closest( '.fm-dynamic-input-wrap' ).find( '.data-dynamic-field' ).length ) {
		jQuery( removeBtn ).closest( '.data-dynamic-field' ).remove();
	}
}

// Function to open the field settings.
function openFieldEditSettings( field, type ) {
	var fieldId = jQuery( field ).data( 'field-id' ),
		fieldOptions = jQuery( '[name="dynamic-' + type + '-options[' + fieldId + ']"' ).val(),
		fieldOptionsArray = [],
		parentModal = jQuery( '#custom-app-new-action-modal' ),
		editFieldModal = jQuery( '#fm-action-' + type + '-field-modal' );

	editFieldModal.find( '.btn-save-field' ).attr( 'data-type', type );

	// Set the field id.
	editFieldModal.find( '[name="field_id"]' ).val( fieldId );

	// Fade the parent modal.
	parentModal.css( 'opacity', '0.8' );

	// If field options available, set the option values.
	if ( '' !== fieldOptions ) {
		fieldOptionsArray = JSON.parse( atob( fieldOptions ) );

		// If select field options, set the dynamic fields.
		if ( 'undefined' !== typeof fieldOptionsArray['select-field-key[]'] ) {
			var selectOptionValues = fieldOptionsArray['select-field-value[]'];
			_.each ( fieldOptionsArray['select-field-key[]'], function( optionKey, optionIndex ) {
				var optionValue = selectOptionValues[ optionIndex ];
				addSelectOption( optionValue, optionKey );
			} );

			delete fieldOptionsArray['select-field-key[]'];
			delete fieldOptionsArray['select-field-value[]'];
		}

		// Set values for regular fields.
		_.each ( fieldOptionsArray, function( value, id ) {
			if ( editFieldModal.find( '[name="' + id + '"]' ).is( 'select' ) ) {
				editFieldModal.find( '[name="' + id + '"]' ).selectpicker( 'val', value );
				editFieldModal.find( '[name="' + id + '"]' ).trigger( 'change' );
			} else {
				editFieldModal.find( '[name="' + id + '"]' ).val( value );
			}
		} );
	}

	// Open the modal.
	editFieldModal.modal( 'show' );
}

// Function to add more options to select field.
function addSelectOption( optionValue, optionKey ) {
	var dataItem = jQuery( '.fm-select-input-wrap:first-child' ),
		dataWrapper = dataItem.clone(),
		selectFields = jQuery( '.select-options' );

	if ( 'undefined' !== typeof optionValue ) {
		if ( '' === dataItem.find( 'input' ).val() ) {
			dataWrapper = dataItem;
		}

		dataWrapper.find( '[name="select-field-key[]"]' ).val( optionKey );
		dataWrapper.find( '[name="select-field-value[]"]' ).val( optionValue );
	} else {
		dataWrapper.find( 'input' ).val('');
	}

	selectFields.append( dataWrapper );
}

// Function to remove the select option field.
function removeSelectOption( removeBtn ) {
	if ( 1 !== jQuery( removeBtn ).closest( '.select-options' ).find( '.fm-select-input-wrap' ).length ) {
		jQuery( removeBtn ).closest( '.fm-select-input-wrap' ).remove();
	}
}

// Function to open the cURL request input popup.
function opencURLInputPopup() {
	var curlRequestModal = jQuery( '#fm-curl-request-modal' ),
		parentModal = jQuery( '#custom-app-new-action-modal' );

	// Fade the parent modal.
	parentModal.css( 'opacity', '0.8' );

	// Open the modal.
	curlRequestModal.modal( 'show' );
}

// Function to open the JSON request input popup.
function openJsonInputPopup() {
	var jsonRequestModal = jQuery( '#fm-json-request-modal' ),
		parentModal = jQuery( '#custom-app-new-action-modal' );

	// Fade the parent modal.
	parentModal.css( 'opacity', '0.8' );

	// Open the modal.
	jsonRequestModal.modal( 'show' );
}

// Function to process the cURL request.
function processcURLRequest( importBtn ) {
	var parentModal = jQuery( '#custom-app-new-action-modal' );
		type = jQuery( importBtn ).data( 'type' ),
		curlRequestForm = ( 'curl' === type ) ? jQuery( '#fm-curl-request-form' ) : jQuery( '#fm-json-request-form' ),
		curlRequestModal = ( 'curl' === type ) ? jQuery( '#fm-curl-request-modal' ) : jQuery( '#fm-json-request-modal' ),
		curlRequest = curlRequestForm.find( '[name="curl_request"]' ).val(),
		actionData = new FormData();

	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// Add action name and nonce to the form data.
	actionData.append( 'action', 'flowmattic_custom_app_import_curl' );
	actionData.append( 'workflow_nonce', FMConfig.workflow_nonce );
	actionData.append( 'curl_request', curlRequest );

	// Show saving popup.
	swalPopup.fire(
		{
			title: 'Processing cURL Request',
			showConfirmButton: false,
			didOpen: function() {
				swalPopup.showLoading();
			}
		}
	);

	// Add processing animation for button.
	// jQuery( importBtn ).addClass( 'disabled' );
	jQuery( importBtn ).html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Processing...' );

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		processData: false,
		contentType: false,
		data: actionData,
		success: function( response ) {
			// Show finished popup.
			swalPopup.fire(
				{
					title: 'Processing Finished',
					text: 'Applying the cURL request data in configuration.',
					icon: 'success',
					showConfirmButton: false,
					timer: 1500
				}
			);

			// Remove animation for button and enable it.
			jQuery( importBtn ).html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Applying settings..' );

			let responseData = response.data;

			// Set all fields from the settings.
			jQuery.each( responseData, function( setting, value ) {
				if ( 'object' === typeof value ) {
					let type = ( 'dynamic-headers-key' === setting || 'dynamic-headers-options' === setting ) ? 'headers' : 'params';
					_.each( value, function( val, id ) {								
						var optionsValue = '';

						if ( 'dynamic-headers-key' === setting || 'dynamic-params-key' === setting ) {
							// Get field options.
							optionsValue = responseData['dynamic-' + type + '-options'][ id ];

							// Add fields.
							addDynamicField( type, id, val, optionsValue );

							// Remove any empty field.
							removeEmptyDynamicField( type );
						}
					} );
				} else {
					if ( '' !== value ) {
						jQuery( '#fm-new-action-form' ).find( '[name="' + setting + '"]' ).val( value );
					}
				}
			} );

			// Set the action name.
			if ( 'yes' === responseData.add_params ) {
				jQuery( '#fm-new-action-form' ).find( '#fm-checkbox-params' )[0].checked = true;
				jQuery( '.data-params' ).removeClass( 'd-none' );
			}

			// Set the action description.
			if ( 'yes' === responseData.add_headers ) {
				jQuery( '#fm-new-action-form' ).find( '#fm-checkbox-headers' )[0].checked = true;
				jQuery( '.data-headers' ).removeClass( 'd-none' );
			}

			// Set raw data.
			let raw_data = JSON.stringify( responseData.raw_data );

			jQuery( '#fm-new-action-form' ).find( '[name="raw_data"]' ).text( raw_data ).trigger( 'change' );

			if ( 'curl' === type ) {
					// Set the action http method.
				jQuery( '#fm-new-action-form' ).find( 'select[name="http_method"]' ).selectpicker( 'val', responseData.http_method );

				// Set the action content type.
				jQuery( '#fm-new-action-form' ).find( 'select[name="content_type"]' ).selectpicker( 'val', responseData.content_type );
			}

			// Set the form as non-validated.
			jQuery( '#fm-new-action-form' ).removeClass( 'was-validated' );

			// Close the cURL request modal.
			curlRequestModal.modal( 'hide' );

			// Reset the form.
			curlRequestForm[0].reset();

			// Set the parent modal visibility.
			parentModal.css( 'opacity', '1' );

			// Remove animation for button and enable it.
			jQuery( importBtn ).html( 'Process Request' );

			// Disable the import button.
			jQuery( '.btn-import-from-curl' ).addClass( 'disabled' );
		}
	} );
}

document.addEventListener('DOMContentLoaded', function() {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// Save the form info settings.
	jQuery( '.btn-save-action' ).on( 'click', function() {
		var thisButton = jQuery( this ),
			actionID = jQuery( this ).attr( 'data-action-id' ),
			actionForm = jQuery( '#fm-new-action-form' );

		actionForm.addClass( 'was-validated' );

		if ( ! actionForm[0].checkValidity() ) {
			return false;
		}

		const actionData = new FormData( actionForm[0] );

		// Get the add_params and add_headers values.
		actionData.delete( 'add_params' );
		actionData.delete( 'add_headers' );
		actionData.append( 'add_params', actionForm.find( '#fm-checkbox-params' )[0].checked ? 'yes' : 'no' );
		actionData.append( 'add_headers', actionForm.find( '#fm-checkbox-headers' )[0].checked ? 'yes' : 'no' );

		// Add action name and nonce to the form data.
		actionData.append( 'action', 'flowmattic_custom_app_save_action' );
		actionData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		actionData.append( 'app_id', '<?php echo esc_attr( $app_id ); ?>' );

		if ( 'undefined' !== typeof actionID ) {
			actionData.append( 'action_id', actionID );
		}

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving Action',
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
			data: actionData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Action Saved!',
						text: 'Action data is saved successfully. Reloading the page.',
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

	// Edit action settings.
	jQuery( '.btn-edit-action' ).on( 'click', function() {
		var actionID = jQuery( this ).attr( 'data-action-id' );

		// Hide the import from cURL div.
		jQuery( '.import-curl-button-wrap' ).addClass( 'd-none' );

		// Show preparing popup.
		swalPopup.fire(
			{
				title: 'Preparing to edit',
				text: 'Please wait while we fetch the action data.',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Fetch the action settings.
		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: { action: 'flowmattic_custom_app_get_action', workflow_nonce: FMConfig.workflow_nonce, action_id: actionID, app_id: '<?php echo esc_attr( $app_id ); ?>' },
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
					// Set all fields from the settings.
					jQuery.each( response, function( setting, value ) {
						if ( 'object' === typeof value ) {
							let type = ( 'dynamic-headers-key' === setting || 'dynamic-headers-options' === setting ) ? 'headers' : 'params';
							_.each( value, function( val, id ) {								
								var optionsValue = '';

								if ( 'dynamic-headers-key' === setting || 'dynamic-params-key' === setting ) {
									// Get field options.
									optionsValue = response['dynamic-' + type + '-options'][ id ];

									// Add fields.
									addDynamicField( type, id, val, optionsValue );

									// Remove any empty field.
									removeEmptyDynamicField( type );
								}
							} );
						} else {
							jQuery( '#fm-new-action-form' ).find( '[name="' + setting + '"]' ).val( value );
						}
					} );

					// Set the action ID.
					jQuery( '#custom-app-new-action-modal' ).find( '.btn-save-action' ).attr( 'data-action-id', actionID );

					// Set the action name.
					if ( 'yes' === response.add_params ) {
						jQuery( '#fm-new-action-form' ).find( '#fm-checkbox-params' )[0].checked = true;
						jQuery( '.data-params' ).removeClass( 'd-none' );
					}

					// Set the action description.
					if ( 'yes' === response.add_headers ) {
						jQuery( '#fm-new-action-form' ).find( '#fm-checkbox-headers' )[0].checked = true;
						jQuery( '.data-headers' ).removeClass( 'd-none' );
					}

					// Set the action webhook instructions.
					jQuery( '#fm-new-action-form' ).find( 'select[name="http_method"]' ).selectpicker( 'val', response.http_method );

					// Set the action webhook instructions.
					jQuery( '#fm-new-action-form' ).find( 'select[name="content_type"]' ).selectpicker( 'val', response.content_type );

					// Set the form as non-validated.
					jQuery( '#fm-new-action-form' ).removeClass( 'was-validated' );

					// Close the popup.
					swalPopup.close();

					// Show the modal.
					jQuery( '#custom-app-new-action-modal' ).modal( 'show' );
				}
			}
		} );
	} );

	// Rename action form.
	jQuery( '.btn-rename-action' ).on( 'click', function( e ) {
		var actionID   = jQuery( this ).attr( 'data-action-id' ),
			actionName = jQuery( this ).attr( 'data-action-name' );

		// Set the action name.
		jQuery( '#fm-action-rename-modal' ).find( '[name="action_name"]' ).val( actionName );

		// Set the action ID.
		jQuery( '#fm-action-rename-modal' ).find( '[name="action_id"]' ).val( actionID );

		// Show the modal.
		jQuery( '#fm-action-rename-modal' ).modal( 'show' );
	} );

	// Rename action.
	jQuery( '#fm-action-rename' ).on( 'submit', function(e) {
		e.preventDefault();

		const form            = jQuery( this );
		const thisButton      = form.find( '.btn-rename-auth' );
		const actionID       = form.find( '[name="action_id"]' ).val();
		const actionName     = form.find( '[name="action_name"]' ).val();
		const actionFormData = new FormData( form[0] );
		const actionRow      = jQuery( '.action-item-' + actionID );

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving action',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Add action name and nonce to the form data.
		actionFormData.append( 'action', 'flowmattic_custom_app_rename_action' );
		actionFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		actionFormData.append( 'action_rename', true );
		actionFormData.append( 'app_id', '<?php echo esc_attr( $app_id ); ?>' );

		// Add saving animation for button.
		thisButton.addClass( 'disabled' );
		thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Renaming...' );

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			processData: false,
			contentType: false,
			data: actionFormData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Action Renamed!',
						text: 'New name for the action is saved successfully.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				// Update the name on the page.
				actionRow.find( '.action-name' ).html( actionName );
				actionRow.find( '.btn-rename-action' ).attr( 'data-action-name', actionName );

				// Hide the modal.
				jQuery( '#fm-action-rename-modal' ).modal( 'hide' );

				// Remove animation for button and enable it.
				thisButton.html( 'Rename' );
				thisButton.removeClass( 'disabled' );
			}
		} );
	} );

	// Save field action.
	jQuery( '#fm-action-edit-headers-field, #fm-action-edit-params-field' ).on( 'submit', function(e) {
		e.preventDefault();

		const form       = jQuery( this );
		const thisButton = form.find( '.btn-save-field' );
		let fieldID    = form.find( '[name="field_id"]' ).val();
		const type       = thisButton.attr( 'data-type' );
		const fieldData  = form.serializeArray();

		form.addClass( 'was-validated' );

		if ( ! form[0].checkValidity() ) {
			return false;
		}

		// Convert form data array to JSON
		var fieldDataJSON = fieldData.reduce((jsonObj, { name, value }) => {
			var value = value.replace(/[\r\n\t]+/g, "");

			jsonObj[name] = jsonObj[name] ? [].concat(jsonObj[name], value) : value;

			return jsonObj;
		}, {});

		var fieldDataEncoded = btoa( JSON.stringify( fieldDataJSON ) );

		if ( ! jQuery( '[name="dynamic-' + type + '-options[' + fieldID + ']"]' ).length ) {
			// Lowercase the field id.
			fieldID = fieldID.toLowerCase();
		}

		jQuery( '[name="dynamic-' + type + '-options[' + fieldID + ']"]' ).val( fieldDataEncoded );

		jQuery( '#fm-action-' + type + '-field-modal' ).modal( 'hide' );

		form.removeClass( 'was-validated' );
		form[0].reset();

		// Show finished popup.
		swalPopup.hideLoading();
		swalPopup.fire(
			{
				title: 'Field Settings Saved!',
				text: 'Please make sure to save the action.',
				icon: 'success',
				showConfirmButton: false,
				timer: 2000
			}
		);
	} );

	// Delete action.
	jQuery( '.btn-delete-action' ).on( 'click', function( e ) {
		var actionID = jQuery( this ).attr( 'data-action-id' );

		swalPopup.fire( {
			title: 'Are you sure?',
			text: "Once the selected action is deleted, your workflows using this action will not work until you manually inspect and update them with new action event.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
		} ).then( function( result ) {
				if ( result.isConfirmed ) {
				// Show loading.
				swalPopup.fire(
					{
						title: 'Deleting Selected Action',
						text: 'Please wait while we delete the selected action. Page will be refreshed once its done.',
						showConfirmButton: false,
						didOpen: function() {
							swalPopup.showLoading();
						}
					}
				);

				// Process delete action ajax.
				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_custom_app_delete_action', workflow_nonce: FMConfig.workflow_nonce, action_id: actionID, app_id: '<?php echo esc_attr( $app_id ); ?>' },
					success: function( response ) {
						// Show success popup.
						swalPopup.fire(
							{
								title: 'Action Deleted!',
								text: 'Selected action is deleted successfully. Reloading the page.',
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
.action-list .table tbody tr:last-child {
	border-bottom-color: transparent;
}
</style>
<?php FlowMattic_Admin::footer(); ?>
