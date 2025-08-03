<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				$nonce = wp_create_nonce( 'flowmattic-integration-nonce' );

				$options           = get_option( 'flowmattic_settings', array() );
				$delete_app_access = isset( $options['delete_app_access'] ) ? $options['delete_app_access'] : 'yes';

				$license_key      = get_option( 'flowmattic_license_key', '' );
				$license          = wp_flowmattic()->check_license();
				$flowmattic_apps  = wp_flowmattic()->apps;
				$all_applications = $flowmattic_apps->get_all_applications();
				$all_integrations = '' !== $license_key ? flowmattic_get_integrations() : array();

				$button_type       = '';
				$button_class      = '';
				$new_workflow_url = admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=new&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );

				$default_workflow_builder = $settings['workflow_builder'] ?? 'visual';
				if ( 'visual' === $default_workflow_builder ) {
					$new_workflow_url = admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=new&builder-version=v2&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );
				}

				if ( ! $license || '' === $license_key ) {
					$button_type       = 'disabled';
					$new_workflow_url = 'javascript:void(0)';
					$button_class      = 'needs-registration';
				}

				// Check if integrations have an update available.
				$is_integration_update = flowmattic_is_integration_update_available();
				$update_bubble         = $is_integration_update ? '  <span class="ms-2 p-2 bg-danger border border-light rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 20px;height: 20px;font-size: 12px;">' . $is_integration_update . '<span class="visually-hidden">' . $is_integration_update . '</span></span>' : '';
				?>
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="row flex-row-reverse flex-xl-row-reverse flex-sm-column">
						<div class="col-sm-12 col-xl-12 fm-integrations-list ps-4 pe-4">
							<div class="fm-integration-header d-flex mt-4 justify-content-between">
								<h3 class="fm-integration-heading m-0">
									<?php echo esc_attr__( 'Integrations', 'flowmattic' ); ?>
								</h3>
								<form class="col-12 col-xl-7 col-lg-5 col-md-3 mb-3 mb-lg-0 me-lg-3 integration-search">
									<input type="search" class="form-control search-integrations" placeholder="Search..." aria-label="Search" style="height: 38px;">
								</form>
								<div class="header-actions">
									<a href="<?php echo admin_url( '/admin.php?page=flowmattic-integrations&sync-integrations=true&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) ); ?>" class="btn btn-md btn-secondary me-2" title="<?php echo esc_attr__( 'Refresh Integrations', 'flowmattic' ); ?>" <?php echo esc_attr( $button_type ); ?>>
										<span class="dashicons dashicons-image-rotate"></span>
										<span class="hidden"><?php echo esc_attr__( 'Refresh', 'flowmattic' ); ?></span>
									</a>
									<a href="<?php echo $new_workflow_url; ?>" <?php echo esc_attr( $button_type ); ?> class="btn btn-md btn-primary d-inline-flex align-items-center <?php echo $button_class; ?>">
										<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
										<?php echo esc_attr__( 'New Workflow', 'flowmattic' ); ?>
									</a>
								</div>
							</div>
							<div class="integrations-nav navbar mt-3 bg-light">
								<ul class="nav nav-pills align-items-center">
									<li class="nav-item m-0">
										<a class="nav-link disabled" href="javascript:void(0);" tabindex="-1" aria-disabled="true"><?php echo esc_html__( 'Filters:', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-integration <?php echo ( ! isset( $_GET['tab'] ) ) ? 'active' : ''; ?>" data-filter="all" href="javascript:void(0);"><?php echo esc_html__( 'All', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-integration" data-filter="core-app" href="javascript:void(0);"><?php echo esc_html__( 'Core', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-integration" data-filter="external" href="javascript:void(0);"><?php echo esc_html__( 'External', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-integration" data-filter="delete" href="javascript:void(0);"><?php echo esc_html__( 'Installed', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-integration <?php echo ( isset( $_GET['tab'] ) ) ? 'active' : ''; ?>" data-filter="update" href="javascript:void(0);"><?php echo esc_html__( 'Updates', 'flowmattic' ) . $update_bubble; ?></a>
									</li>
								</ul>
								<span class="navbar-text pe-3 text-end">
									<span class="integrations-count fw-bold"><?php echo count( $all_applications ); ?></span> <?php echo esc_html__( 'Integrations', 'flowmattic' ); ?>
								</span>
							</div>
							<div class="fm-integrations container p-0 mt-4">
								<?php
								if ( '' === $license_key ) {
									?>
									<div class="card border-light mw-100 w-100 d-block">
										<div class="card-body text-center">
											<div class="alert alert-primary ms-3 mt-3" role="alert">
												<?php echo esc_html__( 'License key not registered. Please register your license first in order to install apps.', 'flowmattic' ); ?>
											</div>
										</div>
									</div>
									<?php
								} elseif ( ( empty( $all_integrations ) || ( isset( $all_integrations['status'] ) || isset( $all_integrations['message'] ) ) ) || '' === $license_key ) {
									$license_message = $all_integrations['message'];
									$alert_type      = ( false === strpos( $license_message, 'expired' ) ) ? 'alert-primary' : 'alert-danger';
									?>
									<div class="border-light mw-100 w-100 d-block" style="display: block !important;background-color: #fff;background-clip: border-box;border: 1px solid rgba(0,0,0,.125);border-radius: 0.25rem;margin-top: 20px;padding: 0.7em 2em 1em;padding-bottom: 10px !important;">
										<div class="card-body text-center">
											<div class="alert <?php echo esc_attr( $alert_type ); ?> ms-3 mt-3" role="alert">
												<?php echo $license_message; ?>
											</div>
										</div>
									</div>
									<?php
								} else {
									?>
									<div class="row row-cols-1 row-cols-md-5 row-cols-lg-6 g-2">
										<?php
										if ( isset( $_GET['sync-integrations'] ) ) {
											delete_transient( 'flowmattic_integrations' );
										}

										$flowmattic_apps        = wp_flowmattic()->apps;
										$installed_applications = $flowmattic_apps->get_all_applications();
										$app_settings           = get_option( 'flowmattic_settings', array() );

										ksort( $installed_applications );

										foreach ( $installed_applications as $app => $settings ) {
											if ( strtolower( 'WordPress' ) === $app ) {
												$settings['base'] = 'core';
											}

											if ( ! isset( $settings['base'] ) ) {
												continue;
											}

											$disabled = ( isset( $app_settings[ 'disable-app-' . $app ] ) ) ? true : false;

											if ( ! $disabled ) {
												$button  = '<div class="app-action-buttons w-100 btn-double d-flex">';
												$button .= '<a href="javascript:void(0);" class="flowmattic-core-app mb-2 btn w-100 btn-secondary btn-sm disabled">' . esc_html__( 'Enabled', 'flowmattic' ) . '</a>';
												$button .= '<a href="javascript:void(0);" class="flowmattic-disable-core-app flowmattic-core-app btn w-100 btn-danger btn-sm mb-2 ms-2" data-app="' . $app . '">' . esc_html__( 'Disable', 'flowmattic' ) . '</a>';
												$button .= '</div>';
											} else {
												$button  = '<div class="app-action-buttons w-100 btn-single">';
												$button .= '<a href="javascript:void(0);" class="flowmattic-enable-core-app flowmattic-core-app btn w-100 btn-primary btn-sm" data-app="' . $app . '">' . esc_html__( 'Enable', 'flowmattic' ) . '</a>';
												$button .= '</div>';
											}
											?>
											<div class="col">
												<div class="card core-app w-100 p-0 m-0 core-<?php echo esc_attr( $app ); ?> overflow-hidden">
													<img src="<?php echo esc_url( $settings['icon'] ); ?>" class="card-img-top" alt="<?php echo esc_attr( $settings['name'] ); ?>">
													<h5 class="card-title integration-title m-0 form-text p-2 w-100 text-center" style="-webkit-box-orient: vertical;"><?php echo esc_attr( str_replace( 'by FlowMattic', '', $settings['name'] ) ); ?></h5>
													<?php echo $button; ?>
													<span class="hidden"><?php echo esc_html__( 'core, core-app, all', 'flowmattic' ); ?></span>
													<img src="<?php echo FLOWMATTIC_PLUGIN_URL . 'assets/admin/img/icon.svg'; ?>" title="Core App" style="width: 18px;height: 18px;position: absolute;top: 0;left: 0;margin: 2px; filter: grayscale(1); opacity: 0.8;">
												</div>
											</div>
											<?php
										}

										if ( is_array( $all_integrations ) && 1 < count( $all_integrations ) ) {
											foreach ( $all_integrations as $key => $integration ) {
												$app     = $integration->slug;
												$app_dir = str_replace( ' ', '-', strtolower( $integration->title ) );
												$button  = '<a href="javascript:void(0);" data-url="' . $integration->url . '" data-integration="' . $app_dir . '" data-security="' . $nonce . '" class="flowmattic-install-app btn w-100 btn-primary btn-sm mb-2">' . esc_html__( 'Install', 'flowmattic' ) . '</a>';
												$btn_cls = 'btn-single';

												if ( isset( $installed_applications[ $app ] ) ) {
													// If application is installed with same version number, show installed button.
													if ( version_compare( $installed_applications[ $app ]['version'], $integration->version, '=' ) ) {
														$button = '<a href="javascript:void(0);" class="btn w-100 btn-secondary btn-sm disabled mb-2" aria-disabled="true">' . esc_html__( 'Installed', 'flowmattic' ) . '</a>';
													}

													// If application is installed with different version number, show update button.
													if ( version_compare( $installed_applications[ $app ]['version'], $integration->version, '!=' ) ) {
														$button = '<a href="javascript:void(0);" data-url="' . $integration->url . '" data-integration="' . $app_dir . '" data-security="' . $nonce . '" class="flowmattic-install-app btn w-100 btn-warning btn-sm mb-2">' . esc_html__( 'Update', 'flowmattic' ) . '</a>';
													}

													if ( current_user_can( 'manage_options' ) || 'no' !== $delete_app_access ) {
														$button .= '<a href="javascript:void(0);" class="btn w-100 btn-danger btn-sm mb-2 ms-2 flowmattic-delete-app" data-integration="' . $app . '" data-security="' . $nonce . '" aria-disabled="true">' . esc_html__( 'Delete', 'flowmattic' ) . '</a>';
														$btn_cls = 'btn-double';
													}
												}
												?>
												<div class="col">
													<div class="card w-100 p-0 m-0 <?php echo esc_attr( $app_dir ); ?> overflow-hidden">
														<img src="<?php echo esc_url( $integration->preview ); ?>" class="card-img-top" alt="<?php echo esc_attr( $integration->title ); ?>">
														<h5 class="card-title integration-title m-0 form-text p-2 w-100 text-center" style="-webkit-box-orient: vertical;"><?php echo esc_attr( $integration->title ); ?></h5>
														<?php
															if ( isset( $installed_applications[ $app ]['version'] ) ) {
																?>
																<span class="text-sm form-text integration-version">
																	<?php echo esc_html__( 'v', 'flowmattic' ); ?><?php echo esc_attr( $installed_applications[ $app ]['version'] ); ?>
																</span>
																<?php
															}
														?>
														<div class="app-action-buttons w-100 d-flex <?php echo esc_attr( $btn_cls ); ?>">
															<?php echo $button; ?>
														</div>
														<span class="hidden"><?php echo esc_html__( 'external, all', 'flowmattic' ); ?></span>
													</div>
												</div>
												<?php
											}
										}
										?>
									</div>
									<?php
								}
								?>
							</div>
							<?php
							if ( is_array( $all_integrations ) && 1 === count( $all_integrations ) && '' !== $license_key ) {
								?>
								<div class="card border-light mw-100 w-100 d-block">
									<div class="card-body text-center">
										<div class="alert alert-danger ms-3 mt-3" role="alert">
											<?php echo $all_integrations[0]; ?>
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php FlowMattic_Admin::footer(); ?>
<!-- Remove the integration sync param to avoid server requests. -->
<script type="text/javascript">
jQuery( document ).ready( function() {
	if ( ( -1 === window.location.href.indexOf( 'tab=update' ) ) ) {
		window.history.replaceState( null, null, window.location.pathname + '?page=flowmattic-integrations' );
	}
} );
</script>
<style type="text/css">
.app-action-buttons {background: #fff; position: absolute;bottom: -5px;padding: 5px;left: 0;transform: translateY(41px);transition: all 0.2s ease-in-out;}
.fm-integrations .card {padding-bottom: 10px !important; align-items: center;}
.fm-integrations .card:hover .app-action-buttons {transform: translateY(0px);}
.integration-version {
    position: absolute;
    top: 0;
    right: 10px;
    font-size: 12px;
}
</style>
