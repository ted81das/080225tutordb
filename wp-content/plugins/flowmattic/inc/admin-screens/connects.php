<?php
/**
 * Enhanced Admin page template for connects.
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
				$this_user = wp_get_current_user();
				$email     = $this_user->user_email;

				$license_key = get_option( 'flowmattic_license_key', '' );
				$license     = wp_flowmattic()->check_license();

				// Get all sources.
				$connect_sources = (array) flowmattic_get_integrations( 'connects' );

				// Get external connects.
				$external_connects = flowmattic_get_connects();

				$all_connects = array();
				if ( current_user_can( 'manage_options' ) ) {
					$all_connects = wp_flowmattic()->connects_db->get_all();
				}

				$connects_html = '';

				if ( empty( $all_connects ) ) {
					ob_start();
					?>
					<!-- Enhanced Empty State -->
					<div class="fm-empty-state text-center py-5">
						<div class="fm-empty-icon mb-4">
							<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted">
								<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
							</svg>
						</div>
						<h3 class="h4 mb-3 text-dark"><?php esc_html_e( 'Connect and manage your API authentications', 'flowmattic' ); ?></h3>
						<p class="text-muted mb-4 fs-6" style="max-width: 500px; margin: 0 auto;">
							<?php esc_html_e( 'FlowMattic connects is built to keep your API authentications centralized. You can use these connects in API module or the app integration or custom app. No coding skills required.', 'flowmattic' ); ?>
						</p>
						<div class="fm-action-buttons">
							<?php
							$button_type  = '';
							$button_attr  = 'data-toggle="modal" data-target="#choose-connect-settings-modal"';
							$button_attr2 = 'data-toggle="modal" data-target="#choose-connect-external-modal"';
							$button_class = '';
							$button_url   = 'javascript:void(0)';

							if ( ( empty( $connect_sources ) || ( isset( $connect_sources['status'] ) || isset( $connect_sources['message'] ) ) ) || '' === $license_key ) {
								$button_type  = 'disabled';
								$button_class = 'needs-registration';
								$button_attr  = '';
							} else {
								if ( ! empty( $external_connects ) ) {
									?>
									<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?> class="btn btn-success btn-lg me-3 px-4 py-2 <?php echo $button_class; ?>" <?php echo $button_attr2; ?>>
										<span class="dashicons dashicons-admin-plugins me-2"></span>
										<?php echo esc_attr__( 'Connect Integration', 'flowmattic' ); ?>
									</a>
									<?php
								}
								?>
								<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?> class="btn btn-primary btn-lg px-4 py-2 <?php echo $button_class; ?>" <?php echo $button_attr; ?>>
									<span class="dashicons dashicons-plus-alt me-2"></span>
									<?php echo esc_attr__( 'Connect New API', 'flowmattic' ); ?>
								</a>
								<?php
							}
							?>
						</div>
					</div>
					<?php
					$connects_html = ob_get_clean();
				} else {
					$nonce = wp_create_nonce( 'flowmattic-connect-edit' );

					$connect_statuses = array(
						'live'  => 0,
						'draft' => 0,
					);

					$auth_types = array(
						'basic'  => esc_attr__( 'Basic Auth', 'flowmattic' ),
						'api'    => esc_attr__( 'API Key', 'flowmattic' ),
						'bearer' => esc_attr__( 'Bearer Token', 'flowmattic' ),
						'oauth'  => esc_attr__( 'OAuth 2.0', 'flowmattic' ),
						'oauth_custom'  => esc_attr__( 'OAuth 2.0', 'flowmattic' ),
					);

					ob_start();
					?>
					<!-- Enhanced Connects Display -->
					<div class="fm-connects-container">
						<!-- View and Filter Controls -->
						<div class="fm-view-controls mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
							<div class="fm-controls-left d-flex align-items-center gap-3">								
								<!-- Source Filter -->
								<div class="fm-source-filter">
									<div class="btn-group" role="group">
										<input type="radio" class="btn-check" name="source-filter" id="filter-all" checked>
										<label class="btn btn-outline-primary btn-sm" for="filter-all">All</label>
										<input type="radio" class="btn-check" name="source-filter" id="filter-integration">
										<label class="btn btn-outline-primary btn-sm" for="filter-integration">Integration</label>
										<input type="radio" class="btn-check" name="source-filter" id="filter-custom">
										<label class="btn btn-outline-primary btn-sm" for="filter-custom">Custom</label>
									</div>
								</div>
							</div>
							
							<div class="fm-search-filter">
								<div class="input-group" style="width: 300px;">
									<span class="input-group-text bg-light border-end-0">
										<span class="dashicons dashicons-search text-muted"></span>
									</span>
									<input type="text" class="form-control border-start-0 bg-light search-input shadow-none" placeholder="Search connects..." id="connects-search">
								</div>
							</div>
						</div>

						<!-- Table View (Default) -->
						<div id="table-view-container" class="fm-connects-table">
							<div class="table-responsive">
								<table class="table table-hover bg-white border rounded-3 overflow-hidden">
									<thead class="table-light">
										<tr>
											<th scope="col" class="ps-4">#</th>
											<th scope="col"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Auth Type', 'flowmattic' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Source', 'flowmattic' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Created At', 'flowmattic' ); ?></th>
											<th scope="col" class="text-center"><?php esc_html_e( 'Actions', 'flowmattic' ); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									foreach ( $all_connects as $key => $connect_item ) {
										$connect_id       = $connect_item->id;
										$connect_settings = json_decode( base64_decode( $connect_item->connect_settings ) );

										// Check if external connect.
										$external_connect = ( isset( $connect_settings->is_external ) ) ? flowmattic_get_connects( $connect_settings->external_slug ) : false;

										$fm_auth_type = isset( $connect_settings->fm_auth_type ) ? $connect_settings->fm_auth_type : '';
										$auth_type    = ! empty( $external_connect ) ? $external_connect['fm_auth_type'] : $fm_auth_type;
										$is_external  = ! empty( $external_connect );
										$badge        = $is_external ? '<span class="badge bg-primary">Integration</span>' : '<span class="badge bg-secondary">Custom</span>';
										$db_auth_type = isset( $auth_types[ $auth_type ] ) ? $auth_types[ $auth_type ] : esc_html__( 'Unknown', 'flowmattic' );
										$source_class = $is_external ? 'source-integration' : 'source-custom';
										?>
										<tr class="connect-item-<?php echo esc_attr( $connect_id ); ?> <?php echo $source_class; ?>">
											<td scope="row" class="ps-4 align-middle">
												<span class="fw-medium"><?php echo esc_attr( $connect_id ); ?></span>
											</td>
											<td class="connect-name align-middle">
												<div class="d-flex align-items-center">
													<?php if ( $is_external && isset( $external_connect['icon'] ) ) : ?>
														<img src="<?php echo esc_url( $external_connect['icon'] ); ?>" class="me-2 rounded" width="24" height="24" alt="" style="object-fit: contain;">
													<?php else : ?>
														<span class="dashicons dashicons-admin-network text-muted me-2"></span>
													<?php endif; ?>
													<span class="fw-medium"><?php echo esc_html( $connect_item->connect_name ); ?></span>
												</div>
											</td>
											<td class="align-middle">
												<span class="badge bg-light text-dark border"><?php echo esc_html( $db_auth_type ); ?></span>
											</td>
											<td class="align-middle"><?php echo $badge; ?></td>
											<td class="align-middle">
												<small class="text-muted" title="<?php echo esc_attr( date_i18n( 'd-m-Y h:i A', strtotime( $connect_item->connect_time ) ) ); ?>">
													<?php echo esc_html( human_time_diff( strtotime( $connect_item->connect_time ), current_time( 'timestamp' ) ) ); ?> ago
												</small>
											</td>
											<td class="align-middle">
												<div class="d-flex justify-content-center">
													<a href="javascript:void(0);" data-connect-id="<?php echo esc_attr( $connect_id ); ?>" data-connect-name="<?php echo esc_attr( $connect_item->connect_name ); ?>" class="btn-rename-connect btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Rename Connect', 'flowmattic' ); ?>">
														<span class="screen-reader-text"><?php echo esc_html__( 'Rename Connect', 'flowmattic' ); ?></span>
														<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path stroke-width="1" stroke="#221b38" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path></svg>
													</a>
													<a href="javascript:void(0);" data-connect-id="<?php echo esc_attr( $connect_id ); ?>"  class="btn-edit-connect btn btn-primary-outline btn-sm d-inline-flex align-items-center justify-content-center me-3 p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?>">
														<span class="screen-reader-text"><?php echo esc_html__( 'Change Settings', 'flowmattic' ); ?></span>
														<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
													</a>
													<a href="javascript:void(0);" data-connect-id="<?php echo esc_attr( $connect_id ); ?>"  class="btn-delete-connect btn btn-primary-outline text-danger btn-sm d-inline-flex align-items-center justify-content-center p-0" style="width: 28px; height: 28px;" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete Connect', 'flowmattic' ); ?>">
														<span class="screen-reader-text"><?php echo esc_html__( 'Delete', 'flowmattic' ); ?></span>
														<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path stroke-width="1" stroke="currentColor" fill="none" d="M16.13 22H7.87C7.37 22 6.95 21.63 6.88 21.14L5 8H19L17.12 21.14C17.05 21.63 16.63 22 16.13 22Z"></path><path stroke-width="1" stroke="currentColor" d="M3.5 8H20.5"></path><path stroke-width="1" stroke="currentColor" d="M10 12V18"></path><path stroke-width="1" stroke="currentColor" d="M14 12V18"></path><path stroke-width="1" stroke="currentColor" fill="none" d="M16 5H8L9.7 2.45C9.89 2.17 10.2 2 10.54 2H13.47C13.8 2 14.12 2.17 14.3 2.45L16 5Z"></path><path stroke-width="1" stroke="currentColor" d="M3 5H21"></path></svg>
													</a>
												</div>
											</td>
										</tr>
										<?php
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<?php
					$connects_html .= ob_get_clean();
				}
				?>
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="row flex-row-reverse flex-xl-row-reverse flex-sm-column">
						<div class="col-sm-12 fm-connects-list-container ps-4 pe-4">
							<!-- Enhanced Header -->
							<div class="fm-connect-task-header d-flex mb-4 mt-4 justify-content-between align-items-center">
								<div class="fm-header-content">
									<h2 class="fm-connect-heading fs-4 m-0 d-flex align-items-center fw-bold">
										<?php echo esc_attr__( 'FlowMattic Connects', 'flowmattic' ); ?>
									</h2>
									<p class="text-muted mb-0 mt-1">Manage your API connections and integrations</p>
								</div>
								<div class="flowmattic-connects-header-actions">
									<?php
									$button_type  = '';
									$button_attr  = 'data-toggle="modal" data-target="#choose-connect-settings-modal"';
									$button_attr2 = 'data-toggle="modal" data-target="#choose-connect-external-modal"';
									$button_class = '';
									$button_url   = 'javascript:void(0)';

									if ( ! $license || '' === $license_key ) {
										$button_type  = 'disabled';
										$button_class = 'needs-registration';
										$button_attr  = '';
									} else {
										if ( ! empty( $external_connects ) ) {
											?>
											<a href="<?php echo esc_attr( $button_url ); ?>" <?php echo esc_attr( $button_type ); ?> class="btn btn-success me-2 px-3 py-2 <?php echo $button_class; ?>" <?php echo $button_attr2; ?>>
												<span class="dashicons dashicons-admin-plugins me-2"></span>
												<?php echo esc_attr__( 'Connect Integration', 'flowmattic' ); ?>
											</a>
											<?php
										}
										?>
										<a href="<?php echo esc_attr( $button_url ); ?>" <?php echo esc_attr( $button_type ); ?> class="btn btn-primary px-3 py-2 <?php echo $button_class; ?>" <?php echo $button_attr; ?>>
											<span class="dashicons dashicons-plus-alt2 me-2"></span>
											<?php echo esc_attr__( 'Connect New API', 'flowmattic' ); ?>
										</a>
										<?php
									}
									?>
								</div>
							</div>

							<!-- Enhanced Info Bar -->
							<div class="connects-nav bg-light border rounded-3 p-3 mb-4">
								<div class="d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center">
										<span class="dashicons dashicons-info text-primary me-2"></span>
										<span class="text-dark">
											<?php esc_html_e( 'Connect and manage your custom API connections here.', 'flowmattic' ); ?>
										</span>
									</div>
									<a href="https://flowmattic.com/features/flowmattic-connects/" target="_blank" class="btn btn-sm btn-outline-primary">
										<?php esc_html_e( 'Learn more', 'flowmattic' ); ?> 
										<span class="dashicons dashicons-external ms-1"></span>
									</a>
								</div>
							</div>

							<div class="fm-connects-list">
								<?php
								if ( '' === $license_key ) {
									?>
									<div class="alert alert-warning border-0 rounded-3" role="alert">
										<div class="d-flex align-items-center">
											<span class="dashicons dashicons-warning text-warning me-3"></span>
											<div>
												<strong>License Required</strong><br>
												<small><?php echo esc_html__( 'Please register your license first to use connects.', 'flowmattic' ); ?></small>
											</div>
										</div>
									</div>
									<?php
								} elseif ( isset( $connect_sources['message'] ) ) {
									?>
									<div class="alert alert-info border-0 rounded-3" role="alert">
										<div class="d-flex align-items-center">
											<span class="dashicons dashicons-info text-info me-3"></span>
											<div><?php echo $connect_sources['message']; ?></div>
										</div>
									</div>
									<?php
								} else {
									echo $connects_html;
								}
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Integration Connect Modal -->
				<div class="modal fade" id="choose-connect-external-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="choose-connect-external-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-xl modal-dialog-centered">
						<div class="modal-content border-0 shadow-lg" style="height: 70vh;overflow: auto;">
							<div class="modal-header bg-light border-bottom-0">
								<h5 class="modal-title w-25 fw-bold d-flex align-items-center" id="choose-connect-external-modal-label">
									<span class="dashicons dashicons-admin-plugins text-primary me-2"></span>
									<?php esc_html_e( 'Connect Integration', 'flowmattic' ); ?>
								</h5>
								<input type="search" class="form-control w-50 border-0 bg-white shadow-sm" id="connect-search" placeholder="<?php esc_html_e( 'Search for integration...', 'flowmattic' ); ?>">
								<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
							</div>
							<div class="modal-body">
								<div class="row row-cols-1 row-cols-md-6 g-4 mb-4">
									<?php
									if ( ! empty( $external_connects ) ) {
										foreach ( $external_connects as $slug => $connect_app ) {
											$name      = $connect_app['name'];
											$icon      = $connect_app['icon'];
											$auth_type = $connect_app['fm_auth_type'];
											$custom    = isset( $connect_app['custom'] ) ? 'data-custom="true"' : '';
											?>
											<div class="col integration-app">
												<a href="javascript:void(0);" class="text-decoration-none text-reset external-connect-auth-button connect-<?php echo esc_attr( $auth_type ); ?>-button" <?php echo $custom; ?> data-connect-slug="<?php echo esc_attr( $slug ); ?>">
													<div class="card h-100 border-0 shadow-sm hover-shadow-lg p-0 transition-all">
														<div class="card-body px-4 py-4 text-center">
															<img src="<?php echo esc_attr( $icon ); ?>" class="img-fluid" style="max-height: 48px;width: auto;">
														</div>
														<div class="card-footer text-center bg-light border-0 py-2">
															<small class="fw-medium text-dark app-name"><?php echo esc_attr( $name ); ?></small>
														</div>
													</div>
												</a>
											</div>
											<?php
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- All other existing modals remain the same as original -->
				<!-- New Connect Modal -->
				<div class="modal fade" id="choose-connect-settings-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="choose-connect-settings-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<form id="fm-connect-form" novalidate>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="choose-connect-settings-modal-label"><?php esc_html_e( 'New Connect', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="connect_name" class="form-label"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="connect_name" class="form-control fm-textarea" id="connect_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the connection, prefer API provider name for better understanding.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3 border-bottom">
										<label for="fm-auth-type" class="form-label"><?php esc_html_e( 'Authentication Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<select id="fm-auth-type" name="fm_auth_type" class="form-control fm-select border w-100" title="<?php esc_html_e( 'Authentication type', 'flowmattic' ); ?>" data-live-search="true" required>
											<option value="basic" data-subtext="<?php esc_html_e( 'Username-password based access using HTTP headers.', 'flowmattic' ); ?>">Basic</option>
											<option value="api" data-subtext="<?php esc_html_e( 'Single secret token for API request authorization.', 'flowmattic' ); ?>">API Key</option>
											<option value="bearer" data-subtext="<?php esc_html_e( 'Authenticate API using an access key, such as a JSON Web Token (JWT).', 'flowmattic' ); ?>">Bearer Token</option>
											<option value="oauth" data-subtext="<?php esc_html_e( 'Protocol for granting scoped API access via tokens.', 'flowmattic' ); ?>">OAuth 2.0</option>
										</select>
										<div class="mt-2 mb-3 form-text">
											<?php esc_html_e( 'The authorization header will be automatically generated when you send the request. Learn more about', 'flowmattic' ); ?>
											<a href="https://learning.postman.com/docs/sending-requests/authorization/" target="_blank" class="text-decoration-none">
												<?php esc_html_e( 'authorization', 'flowmattic' ); ?>
												<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="#6B6B6B"></path></svg>
											</a>
										</div>
									</div>
									<div class="auth-details-form">
										<div class="alert alert-primary p-4 m-5 text-center" role="alert">
											<?php esc_html_e( 'Select the Authentication Type to get started', 'flowmattic' ); ?>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- New Connect Modal -->
				<div class="modal fade" id="choose-connect-settings-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="choose-connect-settings-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<form id="fm-connect-form" novalidate>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="choose-connect-settings-modal-label"><?php esc_html_e( 'New Connect', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="connect_name" class="form-label"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="connect_name" class="form-control fm-textarea" id="connect_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the connection, prefer API provider name for better understanding.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3 border-bottom">
										<label for="fm-auth-type" class="form-label"><?php esc_html_e( 'Authentication Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<select id="fm-auth-type" name="fm_auth_type" class="form-control fm-select border w-100" title="<?php esc_html_e( 'Authentication type', 'flowmattic' ); ?>" data-live-search="true" required>
											<option value="basic" data-subtext="<?php esc_html_e( 'Username-password based access using HTTP headers.', 'flowmattic' ); ?>">Basic</option>
											<option value="api" data-subtext="<?php esc_html_e( 'Single secret token for API request authorization.', 'flowmattic' ); ?>">API Key</option>
											<option value="bearer" data-subtext="<?php esc_html_e( 'Authenticate API using an access key, such as a JSON Web Token (JWT).', 'flowmattic' ); ?>">Bearer Token</option>
											<option value="oauth" data-subtext="<?php esc_html_e( 'Protocol for granting scoped API access via tokens.', 'flowmattic' ); ?>">OAuth 2.0</option>
										</select>
										<div class="mt-2 mb-3 form-text">
											<?php esc_html_e( 'The authorization header will be automatically generated when you send the request. Learn more about', 'flowmattic' ); ?>
											<a href="https://learning.postman.com/docs/sending-requests/authorization/" target="_blank" class="text-decoration-none">
												<?php esc_html_e( 'authorization', 'flowmattic' ); ?>
												<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="#6B6B6B"></path></svg>
											</a>
										</div>
									</div>
									<div class="auth-details-form">
										<div class="alert alert-primary p-4 m-5 text-center" role="alert">
											<?php esc_html_e( 'Select the Authentication Type to get started', 'flowmattic' ); ?>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- Rename Connect Modal -->
				<div class="modal fade" id="fm-connect-rename-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-connect-rename-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered">
						<form id="fm-connect-rename" class="w-100" novalidate>
							<input class="hidden" name="connect_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="fm-connect-rename-modal-label"><?php esc_html_e( 'Rename Connect', 'flowmattic' ); ?></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="connect_name" class="form-label"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="connect_name" class="form-control fm-textarea" id="connect_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the connection, prefer API provider name for better understanding.', 'flowmattic' ); ?></div>
									</div>
									<p><button type="submit" class="btn btn-rename-auth btn-primary"><?php esc_html_e( 'Rename', 'flowmattic' ); ?></button></p>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- Edit Connect Modal -->
				<div class="modal fade" id="edit-connect-settings-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="edit-connect-settings-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<form id="fm-connect-edit-form" novalidate>
							<input class="hidden" name="connect_id" type="hidden" value=""/>
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="edit-connect-settings-modal-label"><?php esc_html_e( 'Edit Connect:', 'flowmattic' ); ?> <span class="connect-name"></span></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="connect_name" class="form-label"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<input type="search" name="connect_name" class="form-control fm-textarea" id="connect_name" required>
										<div class="form-text"><?php esc_html_e( 'Name the connection, prefer API provider name for better understanding.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-3 border-bottom">
										<label for="fm-auth-type" class="form-label"><?php esc_html_e( 'Authentication Type', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
										<select disabled id="fm-auth-type" name="fm_auth_type" class="form-control fm-select border w-100" title="<?php esc_html_e( 'Authentication type', 'flowmattic' ); ?>" data-live-search="true" required>
											<option value="basic" data-subtext="<?php esc_html_e( 'Username-password based access using HTTP headers.', 'flowmattic' ); ?>">Basic</option>
											<option value="api" data-subtext="<?php esc_html_e( 'Single secret token for API request authorization.', 'flowmattic' ); ?>">API Key</option>
											<option value="bearer" data-subtext="<?php esc_html_e( 'Authenticate API using an access key, such as a JSON Web Token (JWT).', 'flowmattic' ); ?>">Bearer Token</option>
											<option value="oauth" data-subtext="<?php esc_html_e( 'Protocol for granting scoped API access via tokens.', 'flowmattic' ); ?>">OAuth 2.0</option>
										</select>
										<div class="mt-2 mb-3 form-text">
											<?php esc_html_e( 'The authorization header will be automatically generated when you send the request. Learn more about', 'flowmattic' ); ?>
											<a href="https://learning.postman.com/docs/sending-requests/authorization/" target="_blank" class="text-decoration-none">
												<?php esc_html_e( 'authorization', 'flowmattic' ); ?>
												<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="#6B6B6B"></path></svg>
											</a>
										</div>
									</div>
									<div class="auth-details-form">
										<div class="alert alert-primary p-4 m-5 text-center" role="alert">
											<?php esc_html_e( 'Loading settings...', 'flowmattic' ); ?>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				<!-- Connect Basic -->
				<script type="text/html" id="connect-type-basic-form">
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 auth_api_key">
							<h4 class="fm-input-title auth_api_key"><span class="auth_api_key"><?php esc_attr_e( 'API Key / Username', 'flowmattic' ); ?></span> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_key" type="search" autocomplete="new-password" required/>
						</div>
						<div class="mb-3 auth_api_secret">
							<h4 class="fm-input-title auth_api_secret"><span class="auth_api_secret"><?php esc_attr_e( 'API Secret / Password', 'flowmattic' ); ?></span> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_secret" type="search" autocomplete="new-password" required/>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-primary"><?php esc_html_e( 'Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Connect API Key -->
				<script type="text/html" id="connect-type-api-form">
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 auth_api_key">
							<h4 class="fm-input-title"><?php esc_attr_e( 'API Key Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_key" type="search" autocomplete="new-password" placeholder="apikey" required/>
						</div>
						<div class="mb-3 auth_api_value">
							<h4 class="fm-input-title"><?php esc_attr_e( 'API Key Value', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_value" type="search" autocomplete="new-password" placeholder="Eg: 338c5d58ffe9ea713c3e52ad1443e749" required/>
						</div>
						<div class="mb-3 auth_api_addto">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Add API Key to', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<select class="form-control fm-select border bg-white w-100 mw-100" name="auth_api_addto" required>
								<option value="query">Query Param</option>
								<option value="header">Header</option>
							</select>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-primary"><?php esc_html_e( 'Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Connect API Key - External -->
				<script type="text/html" id="connect-type-api-form-external">
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 auth_api_value">
							<h4 class="fm-input-title"><?php esc_attr_e( 'API Key Value', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_value" type="search" autocomplete="new-password" placeholder="Eg: 338c5d58ffe9ea713c3e52ad1443e749" required/>
						</div>
						<div class="mb-3 auth_api_base">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Base URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_base" type="search" autocomplete="new-password" placeholder="company.saasapp.com"/>
							<div class="form-text"><?php esc_html_e( 'Enter the base URL for your app API endpoint, if required by your app.', 'flowmattic' ); ?></div>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-primary"><?php esc_html_e( 'Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Connect Bearer Token -->
				<script type="text/html" id="connect-type-bearer-form">
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 auth_bearer_token">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Bearer Token / API Key', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_bearer_token" type="search" autocomplete="new-password" placeholder="Enter token here" required/>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-primary"><?php esc_html_e( 'Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Connect OAuth -->
				<script type="text/html" id="connect-type-oauth-form">
					<input class="hidden" name="grant_type" type="hidden" value="authorization_code"/>
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 callback_url">
							<h4 class="fm-input-title">
								<?php esc_attr_e( 'Callback URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small>
								<a href="#" data-toggle="tooltip" data-placement="top" title="This is the callback URL that you will be redirected to, after your application is authorized. FlowMattic uses this to extract the authorization code or access token. The callback URL should match the one you use during the application registration process.">
									<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 4.5C6.41421 4.5 6.75 4.16421 6.75 3.75C6.75 3.33579 6.41421 3 6 3C5.58579 3 5.25 3.33579 5.25 3.75C5.25 4.16421 5.58579 4.5 6 4.5Z" fill="#6B6B6B"></path><path d="M4.5 9V8H5.5V6H4.5V5H6.5V8H7.5V9H4.5Z" fill="#6B6B6B"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6C12 9.31371 9.31371 12 6 12C2.68629 12 0 9.31371 0 6C0 2.68629 2.68629 0 6 0C9.31371 0 12 2.68629 12 6ZM11 6C11 8.76142 8.76142 11 6 11C3.23858 11 1 8.76142 1 6C1 3.23858 3.23858 1 6 1C8.76142 1 11 3.23858 11 6Z" fill="#6B6B6B"></path></svg>
								</a>
							</h4>
							<div class="fm-form-control">
								<input class="fm-textarea callback-url w-100" readonly name="callback_url" type="search" value="https://api.flowmattic.com/oauth2/callback">
							</div>
						</div>
						<div class="mb-3 authUrl">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Auth URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="authUrl" rows="1" required type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 tokenUrl">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Access Token URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="tokenUrl" rows="1" required type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 client_id">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Client ID', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="client_id" rows="1" required type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 client_secret">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Client Secret', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="client_secret" rows="1" required type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 scopes">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Scopes', 'flowmattic' ); ?></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="scopes" rows="1" type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 state">
							<h4 class="fm-input-title"><?php esc_attr_e( 'State', 'flowmattic' ); ?></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="state" rows="1" type="search"></textarea>
							</div>
						</div>
						<div class="mb-3 auth_name">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Authorization Token Name', 'flowmattic' ); ?></h4>
							<div class="fm-form-control">
								<textarea class="form-control fm-textarea w-100" name="auth_name" rows="1" type="search" placeholder="Bearer"></textarea>
							</div>
						</div>
						<div class="mb-3 auth_api_addto">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Client Authentication', 'flowmattic' ); ?></h4>
							<select class="fm-select border bg-white w-100 mw-100" name="auth_api_addto">
								<option value="header"><?php esc_attr_e( 'Send as Basic Auth header', 'flowmattic' ); ?></option>
								<option value="body"><?php esc_attr_e( 'Send client credentials in body', 'flowmattic' ); ?></option>
							</select>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-connect-auth btn-primary"><?php esc_html_e( 'Save Connect & Authenticate', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Connect Oauth 2.0 - External -->
				<script type="text/html" id="external-connect-oauth_custom-form">
					<div class="mb-3 callback_url">
						<h4 class="fm-input-title">
							<?php esc_attr_e( 'Callback URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small>
							<a href="#" data-toggle="tooltip" data-placement="top" title="This is the callback URL that you will be redirected to, after your application is authorized. FlowMattic uses this to extract the authorization code or access token. The callback URL should match the one you use during the application registration process.">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 4.5C6.41421 4.5 6.75 4.16421 6.75 3.75C6.75 3.33579 6.41421 3 6 3C5.58579 3 5.25 3.33579 5.25 3.75C5.25 4.16421 5.58579 4.5 6 4.5Z" fill="#6B6B6B"></path><path d="M4.5 9V8H5.5V6H4.5V5H6.5V8H7.5V9H4.5Z" fill="#6B6B6B"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6C12 9.31371 9.31371 12 6 12C2.68629 12 0 9.31371 0 6C0 2.68629 2.68629 0 6 0C9.31371 0 12 2.68629 12 6ZM11 6C11 8.76142 8.76142 11 6 11C3.23858 11 1 8.76142 1 6C1 3.23858 3.23858 1 6 1C8.76142 1 11 3.23858 11 6Z" fill="#6B6B6B"></path></svg>
							</a>
						</h4>
						<div class="fm-form-control">
							<input class="fm-textarea callback-url w-100" readonly name="callback_url" type="search" value="https://api.flowmattic.com/oauth2/callback">
						</div>
					</div>
					<div class="mb-3 client_id">
						<h4 class="fm-input-title"><?php esc_attr_e( 'Client ID', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
						<div class="fm-form-control">
							<textarea class="form-control fm-textarea w-100" name="client_id" rows="1" required type="search"></textarea>
						</div>
					</div>
					<div class="mb-3 client_secret">
						<h4 class="fm-input-title"><?php esc_attr_e( 'Client Secret', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
						<div class="fm-form-control">
							<textarea class="form-control fm-textarea w-100" name="client_secret" rows="1" required type="search"></textarea>
						</div>
					</div>
					<div class="mb-3 auth_api_base">
						<h4 class="fm-input-title"><?php esc_attr_e( 'Base URL', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
						<input class="form-control fm-textarea w-100" name="auth_api_base" type="search" autocomplete="new-password" placeholder="company.saasapp.com"/>
						<div class="form-text"><?php esc_html_e( 'Enter the base URL for your app API endpoint, if required by your app.', 'flowmattic' ); ?></div>
					</div>
					<input type="hidden" name="endpoint_url" class="form-control" id="endpoint_url">
					<input type="hidden" name="token_url" class="form-control" id="token_url">
					<p><button type="submit" class="btn btn-save-auth btn-connect-auth btn-primary"><?php esc_html_e( 'Authenticate & Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- Edit Connect Modal -->
				<div class="modal fade" id="external-connect-settings-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="external-connect-settings-modal-label" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<div class="modal-content">
							<form id="external-connect-edit-form" class="connect-external" novalidate>
								<div class="modal-header">
									<h5 class="modal-title" id="external-connect-settings-modal-label"><?php esc_html_e( 'Connect App:', 'flowmattic' ); ?> <span class="connect-name"></span></h5>
									<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
								</div>
								<div class="modal-body">
									<div class="form-group bg-light p-3 pb-1 mb-3 w-100">
										<div class="mb-3">
											<label for="external_connect_name" class="form-label"><?php esc_html_e( 'Connect Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
											<input type="search" name="connect_name" class="form-control fm-textarea" id="external_connect_name" required>
											<input type="hidden" name="connect_id" class="form-control" id="connect_id" required>
											<input type="hidden" name="external_slug" class="form-control" id="external_slug" required>
											<div class="form-text"><?php esc_html_e( 'Name the connection, prefer API provider name for better understanding.', 'flowmattic' ); ?></div>
										</div>
									</div>
									<div class="external-connect-auth-form"></div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<!-- External Connect - OAuth -->
				<script type="text/html" id="external-connect-oauth-form">
					<input type="hidden" name="endpoint_url" class="form-control" id="endpoint_url" required>
					<p><button type="submit" class="btn btn-save-auth btn-connect-auth btn-primary"><?php esc_html_e( 'Authenticate & Save Connect', 'flowmattic' ); ?></button></p>
				</script>
				<!-- External Connect - API Key -->
				<script type="text/html" id="external-connect-api-form">
					<div class="form-group bg-light p-3 pb-1 mb-3 api-url w-100">
						<div class="mb-3 auth_api_value">
							<h4 class="fm-input-title"><?php esc_attr_e( 'API Key Value', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_value" type="search" autocomplete="new-password" placeholder="Eg: 338c5d58ffe9ea713c3e52ad1443e749" required/>
						</div>
						<div class="mb-3 auth_api_base">
							<h4 class="fm-input-title"><?php esc_attr_e( 'Base URL', 'flowmattic' ); ?></h4>
							<input class="form-control fm-textarea w-100" name="auth_api_base" type="search" autocomplete="new-password" placeholder="company.saasapp.com"/>
							<div class="form-text"><?php esc_html_e( 'Enter the base URL for your app API endpoint, if required by your app.', 'flowmattic' ); ?></div>
						</div>
					</div>
					<p><button type="submit" class="btn btn-save-auth btn-primary"><?php esc_html_e( 'Save Connect', 'flowmattic' ); ?></button></p>
				</script>
			</div>
		</div>
	</div>
</div>

<style>
/* Enhanced CSS Styles */
.fm-empty-state {
	padding: 4rem 2rem;
}

.fm-empty-icon svg {
	opacity: 0.3;
}

div#choose-connect-external-modal {
    z-index: 9999;
}
.fm-view-controls .btn-check:checked + .btn {
	background-color: #0d6efd;
	border-color: #0d6efd;
	color: white;
}

.fm-source-filter .btn-check:checked + .btn {
	background-color: #0d6efd;
	border-color: #0d6efd;
	color: white;
}

.hover-shadow-lg:hover {
	box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.transition-all {
	transition: all 0.2s ease-in-out;
}

.table-hover tbody tr:hover {
	background-color: rgba(13, 110, 253, 0.05);
}

.badge {
	font-size: 0.75rem;
}

.min-width-0 {
	min-width: 0;
}

.fs-3 {
	font-size: 1.75rem !important;
}
.connect-name img {
    width: 24px !important;
    height: 24px !important;
}
.search-input {
	border: 1px solid #ced4da !important;
}
@media (max-width: 768px) {
	.fm-header-content h2 {
		font-size: 1.5rem;
	}
	
	.fm-view-controls {
		flex-direction: column;
		gap: 1rem;
	}
	
	.fm-search-filter {
		width: 100% !important;
	}
	
	.fm-search-filter .input-group {
		width: 100% !important;
	}
	
	.fm-controls-left {
		flex-direction: column;
		align-items: flex-start !important;
	}
}
</style>

<script type="text/javascript">
var externalConnects = <?php echo wp_json_encode( $external_connects ); ?>;

// Enhanced JavaScript functionality
jQuery(document).ready(function($) {
	// Source filter functionality
	$('input[name="source-filter"]').change(function() {
		const filter = $(this).attr('id');
		
		if (filter === 'filter-all') {
			$('#table-view-container tbody tr').show();
		} else if (filter === 'filter-integration') {
			$('#table-view-container tbody tr').hide();
			$('.source-integration').show();
		} else if (filter === 'filter-custom') {
			$('#table-view-container tbody tr').hide();
			$('.source-custom').show();
		}
	});

	// Enhanced search functionality
	$('#connects-search').on('input', function() {
		const searchTerm = $(this).val().toLowerCase();
		
		// Search in table view
		$('#table-view-container tbody tr').each(function() {
			const connectName = $(this).find('.connect-name').text().toLowerCase();
			const authType = $(this).find('.badge').text().toLowerCase();
			
			if (connectName.includes(searchTerm) || authType.includes(searchTerm)) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	// Initialize tooltips
	$('[data-toggle="tooltip"]').tooltip();
	
	// Enhanced modal search
	$('#connect-search').on('input', function() {
		const filter = $(this).val().toUpperCase();
		const connects = $('.integration-app');

		connects.each(function() {
			const connectName = $(this).find('.app-name').text().toUpperCase();
			if (connectName.indexOf(filter) > -1) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	// Handle the form content (keeping original functionality)
	const selectElement = document.getElementById('fm-auth-type');
	const targetElement = document.querySelector('.auth-details-form');

	if (selectElement) {
		selectElement.addEventListener('change', function () {
			const selectedOptionValue = this.value;
			const selectedContent = document.getElementById( 'connect-type-' + selectedOptionValue + '-form');
			targetElement.innerHTML = '';
			if ( selectedContent ) {
				targetElement.innerHTML = selectedContent.innerHTML;
				$('.fm-select').selectpicker();
				$('body').find('[data-toggle="tooltip"]').tooltip({ template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' });
				$('#fm-connect-form').removeClass('was-validated');
			}
		});
	}
});
var externalConnects = <?php echo wp_json_encode( $external_connects ); ?>;
// Handle the form content.
document.addEventListener('DOMContentLoaded', function () {
	const selectElement = document.getElementById('fm-auth-type');
	const targetElement = document.querySelector('.auth-details-form');

	selectElement.addEventListener('change', function () {
		const selectedOptionValue = this.value;
		const selectedContent = document.getElementById( 'connect-type-' + selectedOptionValue + '-form');
		targetElement.innerHTML = '';
		if ( selectedContent ) {
			// Replace the form.
			targetElement.innerHTML = selectedContent.innerHTML;

			// Set the select picker.
			jQuery( '.fm-select' ).selectpicker();

			// Show tooltips
			jQuery( 'body' ).find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

			// Set the form as non-validated.
			jQuery( '#fm-connect-form' ).removeClass( 'was-validated' );
		}
	});
});

jQuery( document ).ready( function ($) {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );
	
	// Handle the save and update auth actions.
	jQuery( '#fm-connect-form, #fm-connect-edit-form, #external-connect-edit-form' ).on( 'submit', function(e) {
		e.preventDefault();

		const form            = jQuery( this );
		const thisButton      = form.find( '.btn-save-auth' );
		const connectID       = form.find( '[name="connect_id"]' ).val();
		const connectFormData = new FormData( form[0] );
		const authConnect     = thisButton.hasClass( 'btn-connect-auth' );
		const isExternal      = form.hasClass( 'connect-external' );
		let authType          = form.find( 'select[name="fm_auth_type"]' ).val();

		form.addClass( 'was-validated' );

		if ( ! form[0].checkValidity() ) {
			return false;
		}

		// Add saving animation for button.
		thisButton.addClass( 'disabled' );
		thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Saving...' );

		// Add action name and nonce to the form data.
		connectFormData.append( 'action', 'flowmattic_connect_save_auth_data' );
		connectFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		connectFormData.append( 'oauth_webhook', FMConfig.oauth_webhook );

		if ( isExternal ) {
			let externalSlug = form.find( '[name="external_slug"]' ).val();
			let connectItem = window.externalConnects[ externalSlug ],
			authType = connectItem.fm_auth_type;
			connectFormData.append( 'is_external', true );
			connectFormData.append( 'external_slug', externalSlug );
		}

		if ( 'undefined' !== typeof connectID && '' !== connectID ) {
			connectFormData.append( 'connect_id', connectID );
		}

		if ( 'undefined' !== typeof authType ) {
			connectFormData.append( 'fm_auth_type', authType );
		}

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving Connect',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			processData: false,
			contentType: false,
			data: connectFormData,
			success: function( response ) {
				var authWindow;

				response = JSON.parse( response );
				const connectID = response.connect_id;

				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Connect Saved!',
						text: 'Your new connect is saved successfully.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				if ( authConnect ) {
					var authFormData = form.serializeArray(),
						authDataCapture = setInterval( captureConnectAuthData, 2000 ),
						captureResponse = 1,
						cancelDuration = 1000;

					let baseURL = form.find( '#endpoint_url' ).length ? form.find( '#endpoint_url' ).val() : form.find( '.callback-url' ).val();

					if ( 'undefined' === typeof baseURL ) {
						baseURL = form.find( '[name="auth_api_base"]' ).val();
					}

					// If endpoint is for twitter, set the cancel duration to 12 seconds.
					if ( -1 !== baseURL.indexOf( 'twitter' ) ) {
						cancelDuration = 12000;
					}

					// If endpoint is for calendly, set the cancel duration to 60 seconds.
					if ( -1 !== baseURL.indexOf( 'calendly' ) ) {
						cancelDuration = 60000;
					}

					// Convert form data array to JSON.
					var formDataJSON = authFormData.reduce((result, { name, value }) => {
						result[name] = value;
						return result;
					}, {});

					// Fix character encoding issue for connect name.
					if ( 'undefined' !== typeof formDataJSON.connect_name ) {
						formDataJSON.connect_name = encodeURIComponent( formDataJSON.connect_name );
					}

					// Add the required params.
					formDataJSON.connect_id    = connectID;
					formDataJSON.oauth_webhook = FMConfig.oauth_webhook;

					// Add current URL to the form data.
					formDataJSON.callback_url = window.location.href;

					// Convert Object to String.
					formDataJSON = btoa( JSON.stringify( formDataJSON ) );

					// Append it to the callback URL.
					const callbackURL = `${baseURL}?connect=${formDataJSON}`;

					// Set popup window dimensions.
					const left = (window.innerWidth / 2) - 400;
					const top = (window.innerHeight / 2) - 400;

					// Show authenticating text.
					thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Authenticating...' );

					// Show authenticating popup.
					swalPopup.fire(
						{
							title: 'Processing Authentication',
							text: "This dialog box will automatically close once the auth callback is received. Make sure your browser isn't blocking pop-up windows.",
							showConfirmButton: false,
							didOpen: function() {
								swalPopup.showLoading();
							}
						}
					);

					authWindow = window.open(
						callbackURL,
						'_blank',
						`width=800,height=800,top=${top},left=${left}`
					);
					authWindow.focus();

					function captureConnectAuthData() {
						jQuery.ajax( {
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_connect_capture_data', 'connect_id': connectID, workflow_nonce: FMConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								response = JSON.parse( response );

								if ( 'pending' !== response.status ) {
									clearInterval( authDataCapture );

									// Show reloading text.
									thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Refreshing page...' );

									// Show success popup.
									swalPopup.fire(
										{
											title: 'Authentication Successful!',
											text: 'Your new connect is authenticated successfully. You can now use it in your workflow.',
											icon: 'success',
											showConfirmButton: false,
											timer: 1500
										}
									);

									cancelDuration = 10000;

									setTimeout( function() {
										window.location = window.location.href;
									}, 100 );
								} else {
									if ( authWindow.closed ) {
										setTimeout( function() {
											clearInterval( authDataCapture );

											swalPopup.fire(
												{
													title: 'Authentication Cancelled!',
													text: 'You have cancelled the authentication. Please authenticate your connect to be able to use this in your workflow automation.',
													icon: 'warning',
													showConfirmButton: true,
													timer: 5000
												}
											);

											// Show reloading text.
											thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Refreshing page...' );

											setTimeout( function() {
												window.location = window.location.href;
											}, 500 );
										}, cancelDuration );
									}
								}

								captureResponse = '0';
							}
						} );
					}
				} else {
					// Show reloading text.
					thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Refreshing page...' );

					setTimeout( function() {
						window.location = window.location.href;
					}, 500 );
				}
			},
			error: function (xhr, status, error) {
				console.log('Error:', error);
			}
		});
	});

	// Edit connect settings.
	jQuery( '.btn-edit-connect' ).on( 'click', function() {
		var connectID = jQuery( this ).attr( 'data-connect-id' );

		// Show preparing popup.
		swalPopup.fire(
			{
				title: 'Preparing to edit',
				text: 'Please wait while we fetch the connect data.',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Fetch the connect settings.
		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: { action: 'flowmattic_connect_edit_settings', workflow_nonce: FMConfig.workflow_nonce, connect_id: connectID },
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
					var auth_type = response.connect_settings.fm_auth_type,
						targetElement = jQuery( '#edit-connect-settings-modal' ).find( '.auth-details-form' ),
						selectedContent = document.getElementById( 'connect-type-' + auth_type + '-form');

					if ( 'undefined' !== typeof response.connect_settings.is_external ) {
						var connectSlug   = response.connect_settings.external_slug,
							modal = jQuery( '#external-connect-settings-modal' ),
							connectItem = window.externalConnects[ connectSlug ],
							authType = connectItem.fm_auth_type,
							customOauth = ( 'undefined' !== typeof connectItem.custom ) ? true : false,
							authButton;

						if ( 'oauth' === authType || 'api' === authType ) {
							selectedContent = document.getElementById( 'external-connect-' + authType + '-form');
						}

						if ( 'bearer' === authType ) {
							selectedContent = document.getElementById( 'connect-type-bearer-form');
						}

						if ( 'basic' === authType ) {
							selectedContent = document.getElementById( 'connect-type-basic-form');
						}

						if ( customOauth ) {
							selectedContent = document.getElementById( 'connect-type-oauth-form');
						}

						if ( 'oauth_custom' === authType ) {
							selectedContent = document.getElementById( 'external-connect-oauth_custom-form');
						}

						targetElement = modal.find( '.external-connect-auth-form' );
						targetElement.innerHTML = '';

						// Replace the form.
						targetElement[0].innerHTML = selectedContent.innerHTML;

						// Set the connect name.
						modal.find( '[name="connect_name"]' ).val( response.connect_name );
						modal.find( '.connect-name' ).html( response.connect_name );

						if ( 'undefined' !== typeof response.connect_settings.token_url ) {
							// Set the token URL.
							modal.find( '[name="token_url"]' ).val( response.connect_settings.token_url );
						}

						if ( 'oauth' === authType ) { 
							// Set the connect endpoint URL.
							modal.find( '[name="endpoint_url"]' ).val( response.connect_settings.endpoint_url );
						} else if ( 'api' === authType ) {
							var baseUrl = 'undefined' !== typeof response.connect_settings.auth_api_base ? response.connect_settings.auth_api_base : '';

							// Set the external slug.
							modal.find( '[name="auth_api_value"]' ).val( response.connect_settings.auth_api_value );

							// If base URL is set, set it in the form.
							if ( '' !== baseUrl ) {
								modal.find( '[name="auth_api_base"]' ).val( baseUrl );
							} else {
								modal.find( '.auth_api_base' ).hide();
							}
						} else if ( 'basic' === authType ) {
							// Set the api key.
							modal.find( '[name="auth_api_key"]' ).val( response.connect_settings.auth_api_key );

							// Set the api secret.
							modal.find( '[name="auth_api_secret"]' ).val( response.connect_settings.auth_api_secret );
						} else if ( 'bearer' === authType ) {
							// Set the bearer token.
							modal.find( '[name="auth_bearer_token"]' ).val( response.connect_settings.auth_bearer_token );
						}

						if ( customOauth ) {
							// Set the connect callback URL.
							modal.find( '[name="callback_url"]' ).val( connectItem.endpoint );

							// Set all fields from the settings.
							jQuery.each( response.connect_settings, function( setting, value ) {
								modal.find( '[name="' + setting + '"]' ).val( value );
							} );

							// Hide unnecessary fields.
							modal.find( '.auth_api_addto, .authUrl, .tokenUrl, .state, .auth_name, .scopes' ).remove();
						}

						// Set the external slug.
						modal.find( '[name="external_slug"]' ).val( response.connect_settings.external_slug );

						// Set the connect ID.
						modal.find( '[name="connect_id"]' ).val( connectID );

						// Show the modal.
						modal.modal( 'show' );
					} else {
						// Replace the form.
						jQuery( targetElement ).html( selectedContent.innerHTML );

						// Set all fields from the settings.
						jQuery.each( response.connect_settings, function( setting, value ) {
							jQuery( '#edit-connect-settings-modal' ).find( '[name="' + setting + '"]' ).val( value );
						} );

						// Set the connect ID.
						jQuery( '#edit-connect-settings-modal' ).find( '[name="connect_id"]' ).val( connectID );

						// Set the connect name.
						jQuery( '#edit-connect-settings-modal' ).find( '[name="connect_name"]' ).val( response.connect_name );
						jQuery( '#edit-connect-settings-modal' ).find( '.connect-name' ).html( response.connect_name );

						// Set the auth type.
						jQuery( '#edit-connect-settings-modal' ).find( 'select[name="fm_auth_type"]' ).selectpicker( 'val', response.connect_settings.fm_auth_type );

						// Set the select picker.
						jQuery( '.fm-select' ).selectpicker();

						// Show tooltips
						jQuery( 'body' ).find( '[data-toggle="tooltip"]' ).tooltip( { template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' } );

						// Set the form as non-validated.
						jQuery( '#fm-connect-form' ).removeClass( 'was-validated' );

						
						// Show the modal.
						jQuery( '#edit-connect-settings-modal' ).modal( 'show' );
					}

					// Close the popup.
					swalPopup.close();
				}
			}
		} );
	} );

	// Rename Connect form.
	jQuery( '.btn-rename-connect' ).on( 'click', function( e ) {
		var connectID   = jQuery( this ).attr( 'data-connect-id' ),
			connectName = jQuery( this ).attr( 'data-connect-name' );

		// Set the connect name.
		jQuery( '#fm-connect-rename-modal' ).find( '[name="connect_name"]' ).val( connectName );

		// Set the connect ID.
		jQuery( '#fm-connect-rename-modal' ).find( '[name="connect_id"]' ).val( connectID );

		// Show the modal.
		jQuery( '#fm-connect-rename-modal' ).modal( 'show' );
	} );

	// Rename connect.
	jQuery( '#fm-connect-rename' ).on( 'submit', function(e) {
		e.preventDefault();

		const form            = jQuery( this );
		const thisButton      = form.find( '.btn-rename-auth' );
		const connectID       = form.find( '[name="connect_id"]' ).val();
		const connectName     = form.find( '[name="connect_name"]' ).val();
		const connectFormData = new FormData( form[0] );
		const connectRow      = jQuery( '.connect-item-' + connectID );

		// Show saving popup.
		swalPopup.fire(
			{
				title: 'Saving Connect',
				showConfirmButton: false,
				didOpen: function() {
					swalPopup.showLoading();
				}
			}
		);

		// Add action name and nonce to the form data.
		connectFormData.append( 'action', 'flowmattic_connect_save_auth_data' );
		connectFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
		connectFormData.append( 'connect_rename', true );

		// Add saving animation for button.
		thisButton.addClass( 'disabled' );
		thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Renaming...' );

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			processData: false,
			contentType: false,
			data: connectFormData,
			success: function( response ) {
				// Show finished popup.
				swalPopup.fire(
					{
						title: 'Connect Renamed!',
						text: 'New name for the connect is saved successfully.',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					}
				);

				// Update the name on the page.
				connectRow.find( '.connect-name' ).html( connectName );
				connectRow.find( '.btn-rename-connect' ).attr( 'data-connect-name', connectName );

				// Hide the modal.
				jQuery( '#fm-connect-rename-modal' ).modal( 'hide' );

				// Remove animation for button and enable it.
				thisButton.html( 'Rename' );
				thisButton.removeClass( 'disabled' );
			}
		} );
	} );

	// Delete Connect.
	jQuery( '.btn-delete-connect' ).on( 'click', function( e ) {
		var connectID = jQuery( this ).attr( 'data-connect-id' );

		swalPopup.fire( {
			title: 'Are you sure?',
			text: "Once the selected connect is deleted, your workflows using this connect will be paused until you manually inspect and publish them with new connect for authentication.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
		} ).then( function( result ) {
				if ( result.isConfirmed ) {
				// Show loading.
				swalPopup.fire(
					{
						title: 'Deleting Selected Connect',
						text: 'Please wait while we delete the selected connect. Page will be refreshed once its done.',
						showConfirmButton: false,
						didOpen: function() {
							swalPopup.showLoading();
						}
					}
				);

				// Process delete connect ajax.
				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_connect_delete', workflow_nonce: FMConfig.workflow_nonce, connect_id: connectID },
					success: function( response ) {
						// Show success popup.
						swalPopup.fire(
							{
								title: 'Connect Deleted!',
								text: 'Selected connect is deleted successfully.',
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

	// Process external connect authentication.
	jQuery( '.external-connect-auth-button' ).on( 'click', function( e ) {
		var connectSlug   = jQuery( this ).attr( 'data-connect-slug' ),
			modal = jQuery( '#external-connect-settings-modal' ),
			connectItem = window.externalConnects[ connectSlug ],
			connectSettings = ( 'undefined' !== typeof connectItem.settings ) ? connectItem.settings : '',
			authType = connectItem.fm_auth_type,
			baseUrl = '',
			customOauth = ( 'undefined' !== typeof connectItem.custom ) ? true : false,
			authButton;

		let selectedContent = document.getElementById( 'external-connect-' + authType + '-form');
		let targetElement = modal.find( '.external-connect-auth-form' )
		targetElement.innerHTML = '';

		if ( 'bearer' === authType ) {
			selectedContent = document.getElementById( 'connect-type-bearer-form');
		}

		if ( 'basic' === authType ) {
			selectedContent = document.getElementById( 'connect-type-basic-form');
		}

		if ( 'api' === authType ) {
			baseUrl = 'undefined' !== typeof connectItem.auth_api_base ? connectItem.auth_api_base : '';
			selectedContent = document.getElementById( 'connect-type-api-form-external');
		}

		if ( customOauth ) {
			selectedContent = document.getElementById( 'connect-type-oauth-form');
		}

		if ( 'oauth_custom' === authType ) {
			baseUrl = 'undefined' !== typeof connectItem.auth_api_base ? connectItem.auth_api_base : '';
			selectedContent = document.getElementById( 'external-connect-oauth_custom-form');
		}

		// Replace the form.
		targetElement[0].innerHTML = selectedContent.innerHTML;

		// Remove the connect id.
		modal.find( '[name="connect_id"]' ).val('');

		// Set the connect name.
		modal.find( '[name="connect_name"]' ).val( connectItem.name );
		modal.find( '.connect-name' ).html( connectItem.name );

		// Set the external slug.
		modal.find( '[name="external_slug"]' ).val( connectSlug );

		if ( 'oauth' === authType || 'oauth_custom' === authType ) {
			// Set the connect endpoint URL.
			modal.find( '[name="endpoint_url"]' ).val( connectItem.endpoint );
		}

		if ( 'undefined' !== typeof connectItem.token_url ) {
			// Set the api key.
			modal.find( '[name="token_url"]' ).val( connectItem.token_url );
		}

		// If Basic Auth, set the API Key and Secret field title text if available in the settings.
		if ( 'basic' === authType ) {
			if ( 'undefined' !== typeof connectSettings.auth_api_key ) {
				modal.find( 'h4.auth_api_key' ).find( 'span.auth_api_key' ).html( connectSettings.auth_api_key );
			}

			if ( 'undefined' !== typeof connectSettings.auth_api_secret ) {
				modal.find( 'h4.auth_api_secret' ).find( 'span.auth_api_secret' ).html( connectSettings.auth_api_secret );
			}
		}

		if ( customOauth ) {
			// Set the connect callback URL.
			modal.find( '[name="callback_url"]' ).val( connectItem.endpoint );

			// Hide unnecessary fields.
			modal.find( '.auth_api_addto, .authUrl, .tokenUrl, .state, .auth_name, .scopes' ).remove();
		}

		// If base URL is set, set it in the form.
		if ( '' !== baseUrl ) {
			modal.find( '[name="auth_api_base"]' ).val( baseUrl );
		} else {
			modal.find( '.auth_api_base' ).hide();
		}

		// Show the modal.
		modal.modal( 'show' );

		jQuery( '#choose-connect-external-modal' ).modal( 'hide' );
	} );

	// If user start typing in connect-search, filter the connects.
	jQuery( '#connect-search' ).on( 'input', function() {
		var filter = jQuery( this ).val().toUpperCase(),
			connects = jQuery( '.integration-app' );

		connects.each( function() {
			var connectName = jQuery( this ).find( '.app-name' ).text().toUpperCase();

			if ( connectName.indexOf( filter ) > -1 ) {
				jQuery( this ).show();
			} else {
				jQuery( this ).hide();
			}
		} );
	} );
});
</script>

<?php FlowMattic_Admin::footer(); ?>