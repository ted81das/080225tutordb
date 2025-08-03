<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				$wp_current_user = wp_get_current_user();
				$wp_user_email   = $wp_current_user->user_email;
				$license         = wp_flowmattic()->check_license();

				if ( current_user_can( 'manage_options' ) ) {
					$all_workflows = wp_flowmattic()->workflows_db->get_all_with_pagination();
				} else {
					$all_workflows = wp_flowmattic()->workflows_db->get_user_workflows( $wp_user_email );
				}

				$all_workflows_count = wp_flowmattic()->workflows_db->get_all_count();

				// Get all integrations.
				$workflow_integrations = (array) flowmattic_get_integrations();
				$license_key           = get_option( 'flowmattic_license_key', '' );
				$settings              = get_option( 'flowmattic_settings', array() );

				// Get the default workflow builder.
				$default_workflow_builder = $settings['workflow_builder'] ?? 'visual';

				$workflow_content = '';
				$workflow_folders = array();

				// Initial load settings - only show first 20 workflows
				$initial_load_count = 20;
				$total_workflows = $all_workflows_count;

				if ( empty( $all_workflows ) ) {
					ob_start();
					?>
						<tr class="empty-rows">
							<td colspan="4"><?php echo esc_attr__( 'No workflows created. Click the Create Workflow button and create a new workflow to show it here.', 'flowmattic' ); ?></td>
						</tr>
						<?php
						$workflow_content = ob_get_clean();
				} else {
					$nonce = wp_create_nonce( 'flowmattic-workflow-edit' );

					$flowmattic_apps  = wp_flowmattic()->apps;
					$all_applications = $flowmattic_apps->get_all_applications();

					// Sort workflows.
					arsort( $all_workflows );

					$workflow_statuses = array(
						'live'  => 0,
						'draft' => 0,
					);

					$workflow_index = 0;

					// Only process first 20 workflows for initial load
					foreach ( $all_workflows as $key => $workflow ) {
						$workflow_index++;
						
						// Store all workflows data in JavaScript for later loading
						if ($workflow_index > $initial_load_count) {
							break; // Stop processing after initial count
						}
						
						$hidden_step_apps  = array();
						$applications      = array();
						$workflow_steps    = json_decode( $workflow->workflow_steps, true );
						$workflow_settings = json_decode( $workflow->workflow_settings );
						$workflow_time     = $workflow_settings->time;
						$workflow_manager  = ( isset( $workflow_settings->user_email ) && '' !== $workflow_settings->user_email ) ? $workflow_settings->user_email : $wp_user_email;

						if ( isset( $workflow_settings->folder ) ) {
							if ( ! isset( $workflow_folders[ $workflow_settings->folder ] ) ) {
								$workflow_folders[ $workflow_settings->folder ] = 1;
							} else {
								$workflow_folders[ $workflow_settings->folder ] = $workflow_folders[ $workflow_settings->folder ] + 1;
							}
						}

						$builder_version = isset( $workflow_settings->builder_version ) ? $workflow_settings->builder_version : '1.0';

						$steps_count = 0;

						// If builder version is v2, then get the workflow steps from the new builder.
						if ( 'v2' === $builder_version ) {
							$workflow_steps_new = isset( $workflow_steps['steps'] ) ? $workflow_steps['steps'] : array();

							if ( ! empty( $workflow_steps_new ) ) {
								$workflow_steps_cleaned = array();

								// Remove the placeholder type steps.
								foreach ( $workflow_steps_new as $step ) {
									if ( 'placeholder' !== $step['type'] && 'routerSwitch' !== $step['type'] && 'routerRoute' !== $step['type'] ) {
										$workflow_steps_cleaned[] = $step;

										if ( isset( $step['application'] ) && '' !== $step['application'] ) {
											$step_app           = $step['application'];
											$hidden_step_apps[] = str_replace( ' by FlowMattic', '', $all_applications[ $step_app ]['name'] );
										}
									}
								}

								$workflow_steps_v2 = array();
								$steps_count    = count( $workflow_steps_cleaned );

								if ( 1 <= $steps_count ) {
									$workflow_steps_v2[] = $workflow_steps_cleaned[0];
									if ( 1 !== $steps_count ) {
										$workflow_steps_v2[] = $workflow_steps_cleaned[ $steps_count - 1 ];
									}
								}

								$workflow_steps = $workflow_steps_v2;
							}
						} else {
							$steps_count = count( $workflow_steps );

							$hidden_step_apps = array();

							foreach ( $workflow_steps as $step ) {
								if ( isset( $step['application'] ) && '' !== $step['application'] ) {
									$step_app           = $step['application'];
									$hidden_step_apps[] = str_replace( ' by FlowMattic', '', $all_applications[ $step_app ]['name'] );
								}
							}

							if ( 1 < $steps_count ) {
								$workflow_steps = array( $workflow_steps[0], $workflow_steps[ $steps_count - 1 ] );
							}
						}

						if ( ! empty( $workflow_steps ) ) {
							foreach ( $workflow_steps as $index => $step ) {
								if ( ! isset( $step['application'] ) && ! isset( $step['data']['app']['key'] ) ) {
									continue;
								}

								$step_app = 'v2' === $builder_version ? $step['data']['app']['key'] : $step['application'];

								$application_icon = $all_applications[ $step_app ]['icon'];
								$application_name = $all_applications[ $step_app ]['name'];

								$applications[] = '<div class="workflow-image" data-toggle="tooltip" title="' . $application_name . '"><span class="visually-hidden">' . $application_name . '</span><img src="' . $application_icon . '"></div>
								<span class="svg-icon svg-icon--step-arrow"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="16" height="16" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
							}

							// If count is greater than 2, show the remaining numbers.
							if ( 2 < $steps_count ) {
								// Remove the first and last step from the hidden_step_apps array.
								array_shift( $hidden_step_apps );
								array_pop( $hidden_step_apps );
								$steps_count_remaining = count( $hidden_step_apps );

								// Create better formatted tooltip content
								$tooltip_content = '<strong>' . $steps_count_remaining . ' Steps more:</strong><br>' . implode(', ', $hidden_step_apps);
								$count_display = '<div class="workflow-image d-flex align-items-center justify-content-center fm-workflow-popup-trigger" data-toggle="tooltip" data-html="true" data-placement="top" title="' . htmlspecialchars($tooltip_content) . '">+' . $steps_count_remaining . '</div>';
								$count_display .= '<span class="svg-icon svg-icon--step-arrow"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="16" height="16" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';

								// Insert the count_display at the second last position.
								array_splice( $applications, 1, 0, $count_display );
							}
						}

						ob_start();
						$workflow_status = isset( $workflow_settings->status ) ? $workflow_settings->status : 'off';
						$status_name     = 'draft';

						if ( 'off' === $workflow_status ) {
							$workflow_statuses['draft'] += 1;
						} else {
							$workflow_statuses['live'] += 1;
							$status_name                = 'live';
						}

						$workflow_edit_url = admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=edit&workflow-id=' . $workflow->workflow_id . '&nonce=' . $nonce );
						$workflow_version_badge = '';

						if ( 'v2' === $builder_version ) {
							$workflow_version_badge = '<span class="me-2 badge bg-primary" data-toggle="tooltip" title="Created with Visual Workflow Builder">v2</span>';
						}

						if ( 'v2' === $builder_version || 'visual' === $default_workflow_builder ) {
							$workflow_edit_url = admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=edit&workflow-id=' . $workflow->workflow_id . '&builder-version=v2&nonce=' . $nonce );
						}
						?>
						<tr data-workflow-id="<?php echo $workflow->workflow_id; ?>" class="workflow-row all <?php echo esc_attr( strtolower( str_replace( array( ' ', '_' ), '-', $workflow_settings->folder ) ) ); ?> <?php echo esc_attr( $status_name ); ?>">
							<td class="ps-3 py-3">
								<div class="form-check form-switch">
									<input class="form-check-input fm-workflow-status" type="checkbox" id="workflow-status-<?php echo $workflow->workflow_id; ?>" data-workflow-id="<?php echo $workflow->workflow_id; ?>" <?php echo ( 'on' === $workflow_status ? 'checked' : '' ); ?>>
									<label class="form-check-label" for="workflow-status-<?php echo esc_attr( $workflow->workflow_id ); ?>"></label>
								</div>
							</td>
							<td class="ps-3 py-3">
								<a href="<?php echo $workflow_edit_url; ?>" class="text-reset text-decoration-none position-relative">
									<span class="mb-1 d-inline-flex workflow-title" data-toggle="tooltip" title="<?php echo rawurldecode( $workflow->workflow_name ); ?>" data-placement="top"><?php echo $workflow_version_badge; ?> <?php echo rawurldecode( $workflow->workflow_name ); ?></span>
									<div class="abbr text-muted"><small><?php echo sprintf( __( 'Created on %s', 'flowmattic' ), $workflow_time ); ?></small></div>
								</a>
							</td>
							<td>
								<div class="workflow-applications d-flex align-items-center position-relative">
									<?php echo implode( '', $applications ); ?>
								</div>
							</td>
							<td>
								<div class="d-flex">
									<?php
									if ( current_user_can( 'manage_options' ) ) {
										?>
										<a href="javascript:void(0)" class="me-4" data-toggle="tooltip" title="<?php echo esc_html__( 'Workflow Access: ', 'flowmattic' ) . $workflow_manager; ?>">
											<span class="screen-reader-text"><?php echo esc_html__( 'Workflow Manager', 'flowmattic' ); ?></span>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
												<path stroke-width="1" stroke="#262626" fill="none" d="M12 12C6.48 12 2 16.48 2 22C2.02 22 22 22 22 22C22 16.48 17.52 12 12 12Z"></path>
												<path stroke-width="1" stroke="#262626" fill="none" d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"></path>
											</svg>
										</a>
										<?php
									}
									?>
									<a href="<?php echo $workflow_edit_url; ?>" data-toggle="tooltip" title="<?php echo esc_html__( 'Edit workflow', 'flowmattic' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html__( 'Edit', 'flowmattic' ); ?></span>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path stroke-width="1" stroke="#262626" fill="none" d="M17.82 2.29L5.01 15.11L2 22L8.89 18.99L21.71 6.18C22.1 5.79 22.1 5.16 21.71 4.77L19.24 2.3C18.84 1.9 18.21 1.9 17.82 2.29Z" clip-rule="evenodd" fill-rule="evenodd"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M5.01 15.11L8.89 18.99L2 22L5.01 15.11Z"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M19.23 8.65999L15.34 4.76999L17.81 2.29999C18.2 1.90999 18.83 1.90999 19.22 2.29999L21.69 4.76999C22.08 5.15999 22.08 5.78999 21.69 6.17999L19.23 8.65999Z"></path>
										</svg>
									</a>
									<a href="<?php echo admin_url( 'admin.php?page=flowmattic-task-history&workflow-id=' . $workflow->workflow_id ); ?>" class="flowmattic-workflow-history ms-4" target="_blank" data-toggle="tooltip" title="Show history">
										<svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="#262626"><path d="M198.46-621.54v-166.15h40v95.54q46.39-50.93 108.73-79.39Q409.54-800 478.46-800q133.08 0 226.54 93.46 93.46 93.46 93.46 226.54v10.77h-40V-480q0-117-81.5-198.5T478.46-760q-62.08 0-116.69 26.23-54.62 26.23-96.39 72.23h99.24v40H198.46ZM161.23-440h40.46q14.31 94.54 81.12 160.19 66.81 65.66 159.88 77.35L467.62-160q-118-3.85-205.08-83.04Q175.46-322.23 161.23-440Zm395.85 64.62-96.31-96.31V-680h40v191.69l77.54 77.54-21.23 35.39ZM757.46-20l-7.38-48.46q-18.93-3.46-35.96-12.81-17.04-9.35-30.35-24.27l-46.46 14.92-16.93-28 37.54-30q-7.38-19.15-7.38-39.84 0-20.69 7.38-41.39l-39.07-31.53 18.46-29.54 47.23 17.23q13.31-14.16 29.58-23.12 16.26-8.96 35.96-13.19l7.38-48.46h33.85l7.38 48.46q20.46 4.23 37.62 14.19 17.15 9.96 29.46 24.89l48.77-18.46 16.92 31.53-39.84 31.54q6.61 20.69 6.61 39.62 0 18.92-6.61 35.77l41.38 32.3-16.92 28-50.31-16.46q-12.54 14.93-29.58 25.04-17.04 10.12-37.5 13.58L791.31-20h-33.85Zm16.92-83.08q35.31 0 60.74-25.42 25.42-25.42 25.42-60.73 0-35.31-25.42-60.73-25.43-25.42-60.74-25.42-35.3 0-60.73 25.42-25.42 25.42-25.42 60.73 0 35.31 25.42 60.73 25.43 25.42 60.73 25.42Z"/></svg>
									</a>
									<a href="javascript:void(0);" class="flowmattic-delete-task-history ms-4" data-workflow-id="<?php echo esc_html( $workflow->workflow_id ); ?>" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete task history', 'flowmattic' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html__( 'Delete task history', 'flowmattic' ); ?></span>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path stroke-width="1" stroke="#262626" d="M12 7V13"></path>
											<path stroke-width="1" stroke="#262626" d="M12 13H17"></path>
											<path stroke-width="1" stroke="#262626" d="M12 13.5C12.2761 13.5 12.5 13.2761 12.5 13C12.5 12.7239 12.2761 12.5 12 12.5C11.7239 12.5 11.5 12.7239 11.5 13C11.5 13.2761 11.7239 13.5 12 13.5Z"></path>
											<path stroke-width="1" stroke="#262626" d="M19.71 7.29C17.59 4.68 15.25 3 12 3C10.55 3 9.17 3.35 7.96 3.96C5.6 5.15 3.83 7.35 3.22 10"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M21 6V7.4V9H19.4H18L21 6Z"></path>
											<path stroke-width="1" stroke="#262626" d="M4.29001 16.71C6.41001 19.32 8.75001 21 12 21C13.45 21 14.83 20.65 16.04 20.04C18.4 18.85 20.17 16.65 20.78 14"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M3 18V16.6V15H4.6H6L3 18Z"></path>
											<path stroke-width="1" stroke="#262626" d="M3 3L21 21"></path>
										</svg>
									</a>
									<a class="flowmattic-export-workflow ms-4" href="javascript:void(0);" data-workflow-id="<?php echo $workflow->workflow_id; ?>" data-toggle="tooltip" title="<?php echo esc_html__( 'Export workflow', 'flowmattic' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html__( 'Export workflow', 'flowmattic' ); ?></span>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path fill="none" d="M22 14V21C22 21.55 21.55 22 21 22H3C2.45 22 2 21.55 2 21V14"></path>
											<path stroke-width="1" stroke="#262626" d="M22 14V21C22 21.55 21.55 22 21 22H3C2.45 22 2 21.55 2 21V14"></path>
											<path stroke-width="1" stroke="#262626" d="M12 2V17"></path>
											<path stroke-width="1" stroke="#262626" d="M17 12L12 17L7 12"></path>
										</svg>
									</a>
									<a class="flowmattic-clone-workflow ms-4" href="javascript:void(0);" data-workflow-id="<?php echo $workflow->workflow_id; ?>" data-toggle="tooltip" title="<?php echo esc_html__( 'Clone workflow', 'flowmattic' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html__( 'Clone workflow', 'flowmattic' ); ?></span>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path stroke-width="1" stroke="#262626" fill="none" d="M8 6V3C8 2.45 8.45 2 9 2H20C20.55 2 21 2.45 21 3V17C21 17.55 20.55 18 20 18H16V7C16 6.45 15.55 6 15 6H8Z" clip-rule="evenodd" fill-rule="evenodd"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M15 22H4C3.45 22 3 21.55 3 21V7C3 6.45 3.45 6 4 6H15C15.55 6 16 6.45 16 7V21C16 21.55 15.55 22 15 22Z" clip-rule="evenodd" fill-rule="evenodd"></path>
										</svg>
									</a>
									<a href="javascript:void(0);" class="flowmattic-delete-workflow ms-4" data-workflow-id="<?php echo esc_html( $workflow->workflow_id ); ?>" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete workflow', 'flowmattic' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html__( 'Delete', 'flowmattic' ); ?></span>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path stroke-width="1" stroke="#262626" fill="none" d="M16.13 22H7.87C7.37 22 6.95 21.63 6.88 21.14L5 8H19L17.12 21.14C17.05 21.63 16.63 22 16.13 22Z"></path>
											<path stroke-width="1" stroke="#262626" d="M3.5 8H20.5"></path>
											<path stroke-width="1" stroke="#262626" d="M10 12V18"></path>
											<path stroke-width="1" stroke="#262626" d="M14 12V18"></path>
											<path stroke-width="1" stroke="#262626" fill="none" d="M16 5H8L9.7 2.45C9.89 2.17 10.2 2 10.54 2H13.47C13.8 2 14.12 2.17 14.3 2.45L16 5Z"></path>
											<path stroke-width="1" stroke="#262626" d="M3 5H21"></path>
										</svg>
									</a>
								</div>
							</td>
						</tr>
						<?php
						$workflow_content .= ob_get_clean();
					}

					// Count total folder workflows for all workflows (not just displayed ones)
					foreach ( $all_workflows as $key => $workflow ) {
						$workflow_settings = json_decode( $workflow->workflow_settings );
						if ( isset( $workflow_settings->folder ) ) {
							if ( ! isset( $workflow_folders[ $workflow_settings->folder ] ) ) {
								$workflow_folders[ $workflow_settings->folder ] = 1;
							} else {
								$workflow_folders[ $workflow_settings->folder ] = $workflow_folders[ $workflow_settings->folder ] + 1;
							}
						}
					}
				}
				?>
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="row flex-row-reverse flex-xl-row-reverse flex-sm-column">
						<div class="col-sm-12 col-xl-12 fm-workflows-list ps-4 pe-4">
							<div class="fm-workflow-task-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-workflow-heading m-0 w-25">
									<?php echo esc_attr__( 'Workflows', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-workflows-header-actions w-75 d-flex align-items-center justify-content-end">
									<form class="col-12 col-xl-7 col-lg-5 col-md-3 mb-3 mb-lg-0 me-lg-3 workflow-search w-50 mw-50" style="">
										<input type="search" class="form-control search-workflows" placeholder="Search workflows..." aria-label="Search" style="height: 38px;">
									</form>
									<a href="javascript:void(0);" class="flowmattic-import-workflow btn btn-md btn-secondary me-2" data-toggle="modal" data-target="#workflow-import-modal">
										<span class="dashicons dashicons-cloud-upload" style="width: 28px;height: 21px;font-size: 28px; color: #fff; line-height: .85em;"></span>
									</a>
									<?php
									$button_type  = '';
									$button_class = '';
									$new_workflow_url   = admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=new&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );

									if ( ! $license || '' === $license_key ) {
										$button_type      = 'disabled';
										$new_workflow_url = 'javascript:void(0)';
										$button_class     = 'needs-registration';
									}

									if ( 'visual' === $default_workflow_builder ) {
										$new_workflow_url = admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=new&builder-version=v2&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );
									}
									?>
									<a href="<?php echo $new_workflow_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>">
										<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
										<?php echo esc_attr__( 'New Workflow', 'flowmattic' ); ?>
									</a>
								</div>
							</div>
							<div class="workflows-nav navbar mt-3 mb-3 bg-light">
								<ul class="nav nav-pills align-items-center">
									<li class="nav-item m-0">
										<a class="nav-link disabled" href="javascript:void(0);" tabindex="-1" aria-disabled="true"><?php echo esc_html__( 'Filters:', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-workflow active" data-filter="all" href="javascript:void(0);"><?php echo esc_html__( 'All', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-workflow" data-filter="live" href="javascript:void(0);"><?php echo esc_html__( 'Live', 'flowmattic' ); ?></a>
									</li>
									<li class="nav-item m-0">
										<a class="nav-link fm-filter-workflow" data-filter="draft" href="javascript:void(0);"><?php echo esc_html__( 'Draft', 'flowmattic' ); ?></a>
									</li>
								</ul>
								<div class="navbar-text pe-3">
									<div class="workflow-folders fw-bold">
										<span class="d-flex align-center align-items-center justify-content-center">
											<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M172.31-180Q142-180 121-201q-21-21-21-51.31v-455.38Q100-738 121-759q21-21 51.31-21h219.61l80 80h315.77Q818-700 839-679q21 21 21 51.31v375.38Q860-222 839-201q-21 21-51.31 21H172.31Zm0-60h615.38q5.39 0 8.85-3.46t3.46-8.85v-375.38q0-5.39-3.46-8.85t-8.85-3.46H447.38l-80-80H172.31q-5.39 0-8.85 3.46t-3.46 8.85v455.38q0 5.39 3.46 8.85t8.85 3.46ZM160-240v-480 480Z"/></svg>
											<span class="ps-2 pe-2"><?php echo esc_html__( 'Folders: ', 'flowmattic' ); ?></span><span class="selected-folder-text"><?php echo esc_html__( 'All', 'flowmattic' ); ?></span>
										</span>
										<div class="flowmattic-workflow-folders position-absolute mt-3" style="width: 300px;box-shadow: 0 0px 0px 2px #ddd; z-index: 11;">
											<ul>
												<li class="folders-header">
													<h4><?php echo esc_html__( 'Name', 'flowmattic' ); ?></h4>
													<span><?php echo esc_html__( 'Count', 'flowmattic' ); ?></span>
												</li>
												<?php
												echo '<li data-folder-name="all" class="workflow-folder selected-folder"><h4>' . esc_html__( 'All', 'flowmattic' ) . '</h4><span>' . $all_workflows_count . '</span></li>';
												if ( ! empty( $workflow_folders ) ) {
													ksort( $workflow_folders );
													foreach ( $workflow_folders as $folder_name => $count ) {
														echo '<li class="workflow-folder" data-folder-name="' . esc_attr( strtolower( str_replace( array( ' ', '_' ), '-', $folder_name ) ) ) . '"><h4>' . $folder_name . '</h4><span>' . $count . '</span></li>';
													}
												}
												?>
											</ul>
										</div>
									</div>
								</div>
								<span class="navbar-text pe-3">
									<span class="workflow-count fw-bold"><?php echo min($initial_load_count, $all_workflows_count); ?></span> 
									<?php echo esc_html__( 'of', 'flowmattic' ); ?> 
									<span class="total-workflow-count"><?php echo $all_workflows_count; ?></span> 
									<?php echo esc_html__( 'Workflows', 'flowmattic' ); ?>
								</span>
							</div>

							<div class="fm-workflow-table">
								<table class="table table-hover align-middle bg-white">
									<thead class="table-light">
										<tr>
											<th width="30px" class="column-status"><?php echo esc_html__( 'Status', 'flowmattic' ); ?></th>
											<th width="45%" class="ps-3"><?php echo esc_html__( 'Workflow Name', 'flowmattic' ); ?></th>
											<th width="25%"><?php echo esc_html__( 'Applications', 'flowmattic' ); ?></th>
											<th><?php echo esc_html__( 'Action', 'flowmattic' ); ?></th>
										</tr>
									</thead>
									<tbody class="fm-workflow-rows">
										<?php
										$license_key = get_option( 'flowmattic_license_key', '' );
										if ( '' === $license_key ) {
											?>
											<tr class="empty-rows">
												<td colspan="4">
													<div class="alert alert-primary" role="alert">
														<?php echo esc_html__( 'License key not registered. Please register your license first in order to create and manage workflows.', 'flowmattic' ); ?>
													</div>
												</td>
											</tr>
											<?php
										} elseif ( '' === $license_key ) {
											?>
											<tr class="empty-rows">
												<td colspan="4">
													<div class="card border-light mw-100">
														<div class="card-body text-center">
															<div class="alert alert-primary p-4 m-5 text-center" role="alert">
																<?php echo $workflow_integrations['message']; ?>
															</div>
														</div>
													</div>
												</td>
											</tr>
											<?php
										} else {
											echo $workflow_content;
										}
										?>
									</tbody>
								</table>
							</div>

							<!-- Load More Button -->
							<?php if ($total_workflows > $initial_load_count): ?>
							<div class="load-more-container">
								<div class="load-more-divider">
									<span class="load-more-divider-text"><?php echo $total_workflows - $initial_load_count; ?> more workflows</span>
								</div>
								<button id="load-more-workflows" class="load-more-btn" data-loaded="<?php echo $initial_load_count; ?>" data-total="<?php echo $total_workflows; ?>">
									<span class="load-more-text">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<polyline points="6,9 12,15 18,9"></polyline>
										</svg>
										<span><?php echo esc_html__( 'Load More Workflows', 'flowmattic' ); ?></span>
										<span class="remaining-count"><?php echo $total_workflows - $initial_load_count; ?></span>
									</span>
									<span class="loading-text">
										<div class="loading-spinner"></div>
										<span><?php echo esc_html__( 'Loading...', 'flowmattic' ); ?></span>
									</span>
								</button>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<!-- Import workflow modal -->
				<div class="col-12 fm-workflow-import modal fade" id="workflow-import-modal" aria-hidden="true" data-backdrop="static">
					<div class="modal-dialog modal-dialog-centered modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="fm-workflow-heading m-0">
									<?php echo esc_attr__( 'Import Workflow', 'flowmattic' ); ?>
								</h5>
								<button type="button" class="btn-close shadow-none" data-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="flowmattic-workflow-import-field">
									<h4 class="fw-bold"><?php echo esc_html__( 'Upload Workflow JSON File', 'flowmattic' ); ?></h4>
									<div class="input-group mb-3 mt-3 border">
										<input class="form-control form-control-lg" type="file" id="workflow_import_file" style="min-height: auto;padding-left: 10px;" accept="application/json">
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
/* Enhanced Workflow Table Styles */
.fm-workflow-table {
	box-shadow: 0 0 0 1px rgba(0,0,0,0.05);
	border-radius: 8px;
	overflow: hidden;
}

.fm-workflow-table table {
	margin-bottom: 0;
}

.fm-workflow-table thead th {
	background-color: #f8f9fa;
	font-weight: 600;
	color: #495057;
	border-bottom: 2px solid #dee2e6 !important;
	padding: 1rem 0.75rem;
}

.workflow-row {
	transition: all 0.2s ease;
	border-bottom: 1px solid #f0f0f0;
}

.workflow-row:hover {
	background-color: #f8f9fa;
	transform: translateY(-1px);
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.workflow-title {
	font-weight: 500;
	color: #333;
	font-size: 14px;
}

/* Workflow Status Indicators */
.workflow-row.live {
	border-left: 4px solid #28a745;
}

.workflow-row.draft {
	border-left: 4px solid #ffc107;
}

/* Applications Column Enhancement */
.workflow-applications .workflow-image {
	border-radius: 6px;
	overflow: hidden;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	transition: transform 0.2s ease;
}

.workflow-applications .workflow-image:hover {
	transform: scale(1.1);
}

/* Action Icons Enhancement */
.workflow-row td:last-child a {
	transition: all 0.2s ease;
	padding: 0.25rem;
	border-radius: 4px;
}

.workflow-row td:last-child a:hover {
	background-color: #e9ecef;
	transform: scale(1.1);
}

/* Professional Load More Button */
.load-more-container {
	margin: 2rem 0;
	position: relative;
}

.load-more-divider {
	position: relative;
	text-align: center;
	margin-bottom: 1.5rem;
}

.load-more-divider::before {
	content: '';
	position: absolute;
	top: 50%;
	left: 0;
	right: 0;
	height: 1px;
	background: linear-gradient(to right, transparent, #e9ecef 20%, #e9ecef 80%, transparent);
}

.load-more-divider-text {
	background: #f8f9fa;
	padding: 0 1rem;
	color: #6c757d;
	font-size: 14px;
	font-weight: 500;
	position: relative;
	z-index: 1;
}

.load-more-btn {
	display: block;
	margin: 0 auto;
	background: #fff;
	border: 1px solid #e9ecef;
	border-radius: 8px;
	padding: 0.875rem 1.5rem;
	font-size: 14px;
	font-weight: 500;
	color: #495057;
	cursor: pointer;
	transition: all 0.2s ease;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	min-width: 200px;
}

.load-more-btn:hover {
	background: #f8f9fa;
	border-color: #007bff;
	color: #007bff;
	box-shadow: 0 2px 8px rgba(0,123,255,0.15);
	transform: translateY(-1px);
}

.load-more-btn:active {
	transform: translateY(0);
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.load-more-btn:disabled {
	opacity: 0.7;
	cursor: not-allowed;
	transform: none !important;
}

.load-more-text {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
}

.load-more-text svg {
	transition: transform 0.2s ease;
}

.load-more-btn:hover .load-more-text svg {
	transform: translateY(1px);
}

.remaining-count {
	background: #e9ecef;
	color: #6c757d;
	padding: 0.25rem 0.5rem;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	margin-left: 0.25rem;
}

.load-more-btn:hover .remaining-count {
	background: #007bff;
	color: white;
}

.loading-text {
	display: none;
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
}

.loading-spinner {
	width: 16px;
	height: 16px;
	border: 2px solid #e9ecef;
	border-top: 2px solid #007bff;
	border-radius: 50%;
	animation: spin 1s linear infinite;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

/* Show/hide states */
.load-more-btn.loading .load-more-text {
	display: none;
}

.load-more-btn.loading .loading-text {
	display: flex;
}

/* Search Enhancement */
.search-workflows {
	border-radius: 25px;
	border: 2px solid #e9ecef;
	transition: border-color 0.2s ease;
}

.search-workflows:focus {
	border-color: #007bff;
	box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

/* Badge Improvements */
.badge {
	font-size: 11px;
	padding: 0.25em 0.5em;
	border-radius: 12px;
}

/* Empty state styling */
.empty-rows td {
	text-align: center;
	padding: 3rem 1rem;
	color: #6c757d;
	font-style: italic;
}

/* Filter tabs enhancement */
.workflows-nav .nav-link {
	transition: all 0.2s ease;
	border-radius: 20px;
	margin: 0 0.25rem;
}

.workflows-nav .nav-link:hover {
	background-color: #e9ecef;
}

.workflows-nav .nav-link.active {
	background-color: #007bff;
	color: white;
}

/* Count display improvement */
.workflow-count, .total-workflow-count {
	font-weight: 600;
	color: #007bff;
}

/* Enhanced tooltip styling for workflow applications */
.workflow-applications .tooltip {
    max-width: 450px !important;
    z-index: 9999 !important;
}

.workflow-applications .tooltip-inner {
    max-width: 430px;
    text-align: left;
    white-space: normal;
    word-wrap: break-word;
    font-size: 13px;
    line-height: 1.5;
    padding: 12px 16px;
    border-radius: 8px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
}

/* Style the tooltip content better */
.workflow-applications .tooltip-inner::before {
    content: "🔧 ";
    font-size: 14px;
    margin-right: 4px;
}

/* Improve comma-separated list appearance */
.workflow-applications .tooltip-inner {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Better arrow styling */
.workflow-applications .tooltip.bs-tooltip-top .arrow::before {
    border-top-color: #2c3e50 !important;
}

.workflow-applications .tooltip.bs-tooltip-bottom .arrow::before {
    border-bottom-color: #2c3e50 !important;
}

.workflow-applications .tooltip.bs-tooltip-left .arrow::before {
    border-left-color: #2c3e50 !important;
}

.workflow-applications .tooltip.bs-tooltip-right .arrow::before {
    border-right-color: #2c3e50 !important;
}

/* Ensure proper positioning */
.workflow-applications .fm-workflow-popup-trigger {
    position: relative;
}

/* Alternative: Format the tooltip content in a more readable way */
.workflow-steps-tooltip {
    max-width: 400px;
}

.workflow-steps-tooltip .tooltip-inner {
    text-align: left;
    padding: 15px;
    background: #ffffff;
    color: #333;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    font-size: 13px;
    line-height: 1.6;
}

.workflow-steps-tooltip .tooltip-inner strong {
    color: #007bff;
    font-weight: 600;
}
.tooltip.show .arrow {
	display: none;
}
</style>

<script type="text/javascript">
	// Store all workflows data for AJAX loading
	var allWorkflowsData = <?php echo json_encode(array_values($all_workflows)); ?>;
	var currentlyLoaded = <?php echo $initial_load_count; ?>;
	var loadMoreStep = 20;
</script>

<?php FlowMattic_Admin::footer(); ?>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		let searchWorkflowsInput = jQuery( '.search-workflows' );
		let workflowsSearchForm = jQuery( '.workflow-search' );
		const swalPopup = window.Swal.mixin({
			customClass: {
				confirmButton: 'btn btn-primary shadow-none me-xxl-3',
				cancelButton: 'btn btn-danger shadow-none'
			},
			buttonsStyling: false
		} );

		// Display the folders popup on click.
		jQuery( '.workflow-folders' ).on( 'click', function() {
			jQuery( this ).find( '.flowmattic-workflow-folders' ).toggleClass( 'd-block' );
		} );

		// Enhanced search with AJAX for all workflows
		let searchTimeout;
		searchWorkflowsInput.keyup(function() {
			var filter = jQuery(this).val().trim();
			
			// Clear previous timeout
			clearTimeout(searchTimeout);
			
			// Debounce search requests
			searchTimeout = setTimeout(function() {
				if (filter === '') {
					// Reset to initial state
					resetToInitialState();
				} else {
					// Perform AJAX search
					performWorkflowSearch(filter);
				}
			}, 300); // 300ms delay
		});

		// Function to perform AJAX search
		function performWorkflowSearch(searchTerm) {
			// Show loading state
			jQuery('.fm-workflow-rows').html('<tr><td colspan="4" class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Searching...</span></div><p class="mt-2 mb-0">Searching workflows...</p></td></tr>');
			
			// Hide load more button during search
			jQuery('.load-more-container').hide();
			
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_search_workflows',
					search_term: searchTerm,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_search_workflows' ) ); ?>'
				},
				success: function(response) {
					if (response.success) {
						jQuery('.fm-workflow-rows').html(response.data.html);
						jQuery('.workflow-count').text(response.data.count);
						
						// Remove active filters during search
						jQuery('.workflows-nav .active').removeClass('active');
						jQuery('.selected-folder').removeClass('selected-folder');
						
						if (response.data.count === 0) {
							jQuery('.fm-workflow-rows').html('<tr class="empty-rows"><td colspan="4" class="text-center p-4">No workflows found matching "' + searchTerm + '"</td></tr>');
						}
					} else {
						jQuery('.fm-workflow-rows').html('<tr class="empty-rows"><td colspan="4" class="text-center p-4">Error searching workflows. Please try again.</td></tr>');
					}
				},
				error: function() {
					jQuery('.fm-workflow-rows').html('<tr class="empty-rows"><td colspan="4" class="text-center p-4">Error searching workflows. Please try again.</td></tr>');
				}
			});
		}

		// Function to reset to initial state
		function resetToInitialState() {
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_reset_workflows',
					nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_reset_workflows' ) ); ?>'
				},
				success: function(response) {
					if (response.success) {
						jQuery('.fm-workflow-rows').html(response.data.html);
						jQuery('.workflow-count').text(response.data.loaded_count);
						jQuery('.total-workflow-count').text(response.data.total_count);
						
						// Reset load more button
						let remaining = response.data.total_count - response.data.loaded_count;
						if (remaining > 0) {
							jQuery('#load-more-workflows').data('loaded', response.data.loaded_count);
							jQuery('.remaining-count').text(remaining);
							jQuery('.load-more-divider-text').text(remaining + ' more workflows');
							jQuery('.load-more-container').show();
						} else {
							jQuery('.load-more-container').hide();
						}
						
						// Reset filters
						jQuery('.fm-filter-workflow[data-filter="all"]').addClass('active');
						jQuery('.workflow-folder[data-folder-name="all"]').addClass('selected-folder');
						jQuery('.selected-folder-text').text('All');
					}
				}
			});
		}

		// Filter workflows
		jQuery('.fm-filter-workflow').on('click', function(e) {
			e.preventDefault();
			
			let filter = jQuery(this).data('filter');
			
			// Remove active class from all filters
			jQuery('.fm-filter-workflow').removeClass('active');
			jQuery(this).addClass('active');
			
			// Filter workflows
			let count = 0;
			if (filter === 'all') {
				jQuery('.workflow-row').each(function() {
					jQuery(this).show();
					count++;
				});
			} else {
				jQuery('.workflow-row').each(function() {
					if (jQuery(this).hasClass(filter)) {
						jQuery(this).show();
						count++;
					} else {
						jQuery(this).hide();
					}
				});
			}
			
			jQuery('.workflow-count').text(count);
		});

		// Folder filter
		jQuery(document).on('click', '.workflow-folder', function() {
			let folderName = jQuery(this).data('folder-name');
			
			// Update UI
			jQuery('.workflow-folder').removeClass('selected-folder');
			jQuery(this).addClass('selected-folder');
			jQuery('.selected-folder-text').text(jQuery(this).find('h4').text());
			jQuery('.flowmattic-workflow-folders').removeClass('d-block');
			
			// Filter workflows
			let count = 0;
			if (folderName === 'all') {
				jQuery('.workflow-row').each(function() {
					jQuery(this).show();
					count++;
				});
			} else {
				jQuery('.workflow-row').each(function() {
					if (jQuery(this).hasClass(folderName)) {
						jQuery(this).show();
						count++;
					} else {
						jQuery(this).hide();
					}
				});
			}
			
			jQuery('.workflow-count').text(count);
		});

		// Load More Workflows functionality
		jQuery('#load-more-workflows').on('click', function() {
			let button = jQuery(this);
			let loaded = parseInt(button.data('loaded'));
			let total = parseInt(button.data('total'));
			
			// Show loading state
			button.prop('disabled', true);
			button.addClass('loading');
			
			// Load next batch of workflows via AJAX
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_load_more_workflows',
					offset: loaded,
					limit: loadMoreStep,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_load_more_workflows' ) ); ?>'
				},
				success: function(response) {
					if (response.success && response.data.html) {
						// Append new workflows to the table
						jQuery('.fm-workflow-rows').append(response.data.html);
						
						// Update loaded count
						let newLoaded = loaded + response.data.count;
						button.data('loaded', newLoaded);
						currentlyLoaded = newLoaded;
						
						// Update display counts
						updateWorkflowCount();
						
						// Update remaining count
						let remaining = total - newLoaded;

						if (remaining > 0) {
							button.find('.remaining-count').text(remaining);
							button.closest('.load-more-container').find('.load-more-divider-text').text(remaining + ' more workflows');
						} else {
							// All workflows loaded
							button.closest('.load-more-container').find('.load-more-divider-text').text('All workflows loaded');
							button.fadeOut();
						}
						
						// Initialize tooltips for new elements
						if (typeof jQuery().tooltip === 'function') {
							jQuery('[data-toggle="tooltip"]').tooltip();
						}
					}
				},
				complete: function() {
					// Hide loading state
					button.prop('disabled', false);
					button.removeClass('loading');
				}
			});
		});

		// Function to update workflow count display
		function updateWorkflowCount() {
			let visibleCount = jQuery('.workflow-row:visible').length;
			jQuery('.workflow-count').text(visibleCount);
		}

		// Update the workflow status on toggle.
		jQuery(document).on('change', '.fm-workflow-status', function() {
			var workflow_id = jQuery( this ).data( 'workflow-id' );
			var status     = jQuery( this ).is( ':checked' ) ? 'on' : 'off';

			// Show loading popup.
			swalPopup.fire(
				{
					title: 'Updating workflow status...',
					showConfirmButton: false,
					didOpen: function() {
						swalPopup.showLoading();
					}
				}
			);

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_update_workflow_status',
					workflow_id: workflow_id,
					status: status,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_workflow_update_nonce' ) ); ?>'
				},
				success: function( response ) {
					response = JSON.parse( response );
					if ( 'success' === response.status ) {
						jQuery( '.fm-workflow-status' ).each( function() {
							if ( jQuery( this ).data( 'workflow-id' ) === workflow_id ) {
								jQuery( this ).prop( 'checked', status === 'on' );
							}
						} );

						swalPopup.fire(
							{
								title: 'Workflow status updated successfully',
								icon: 'success',
								showConfirmButton: false,
								timer: 1500
							}
						);
					} else {
						swalPopup.fire(
							{
								title: 'Failed to update workflow status',
								text: response.message,
								icon: 'error',
								showConfirmButton: false,
								timer: 2500
							}
						);
					}
				}
			} );
		} );

		// Initialize tooltips
		if (typeof jQuery().tooltip === 'function') {
			jQuery('[data-toggle="tooltip"]').tooltip();
		}
	} );
</script>