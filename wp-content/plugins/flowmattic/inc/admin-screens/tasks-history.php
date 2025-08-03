<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				$workflow_content = '';
				$workflow_filters = array();
				$args             = array();

				$filter_workflow_name = '';
				$filter_workflow_id   = ( isset( $_GET['workflow-id'] ) ) ? $_GET['workflow-id'] : '';
				$date_range           = ( isset( $_GET['date_range'] ) ) ? $_GET['date_range'] : '';
				$from_date            = '';
				$to_date              = '';

				// Split the date range.
				if ( '' !== $date_range ) {
					$date_split = explode( ' - ', $date_range );
					$from_date  = $date_split[0];
					$to_date    = $date_split[1];

					// Assign the date range to get filtered task.
					$args['from_date'] = $from_date;
					$args['to_date']   = $to_date;
				}

				// Check if workflow id is filtered.
				if ( '' !== $filter_workflow_id ) {
					$args['workflow_id'] = $filter_workflow_id;
				}

				// Get all tasks.
				$offset      = 0;
				$page        = 1;
				$search_term = '';

				$items_count_per_page = isset( $_COOKIE['fm_items_per_page'] ) ? $_COOKIE['fm_items_per_page'] : 10;

				if ( isset( $_GET['task-page'] ) && '1' !== $_GET['task-page'] ) {
					$items_per_page = $items_count_per_page;
					$page           = isset( $_GET['task-page'] ) ? abs( (int) $_GET['task-page'] ) : 1;
					$offset         = ( $page * $items_per_page ) - $items_per_page;
				}

				if ( isset( $_GET['fm-query'] ) && '' !== $_GET['fm-query'] ) {
					$search_term         = sanitize_text_field( $_GET['fm-query'] );
					$args['search_term'] = $search_term;
				}

				if ( isset( $_GET['task_status'] ) ) {
					$task_status    = sanitize_text_field( $_GET['task_status'] );
					$args['status'] = $task_status;
				}

				if ( ! empty( $args ) ) {
					$args['per_page'] = $items_count_per_page;
					$all_tasks  = wp_flowmattic()->tasks_db->get_tasks_by_search( $args, $offset );
					$task_count = wp_flowmattic()->tasks_db->get_search_results_without_offset( $args );
				} else {
					$all_tasks  = wp_flowmattic()->tasks_db->get_all( $offset, $items_count_per_page );
					$task_count = ( ! empty( $all_tasks ) ) ? wp_flowmattic()->tasks_db->get_tasks_count() : 0;
				}

				// If task count is less than $items_count_per_page, don't show pagination.
				if ( 1 === $page && is_array( $all_tasks ) ) {
					$task_count = ( $items_count_per_page > count( $all_tasks ) ) ? count( $all_tasks ) : $task_count;
				}

				$logged_in_user = wp_get_current_user();
				$wp_user_email  = $logged_in_user->user_email;

				$all_workflows = wp_flowmattic()->workflows_db->get_all();

				$all_workflow_data = array();
				foreach ( $all_workflows as $wk => $workflow_item ) {
					$all_workflow_data[ $workflow_item->workflow_id ] = $workflow_item;
				}

				// Get tasks count.
				$total_tasks = $task_count;

				if ( empty( $all_tasks ) ) {
					ob_start();
					?>
					<tr class="empty-rows">
						<td colspan="4"><?php echo esc_attr__( 'No tasks found.', 'elegant-elements' ); ?></td>
					</tr>
					<?php
					$workflow_content .= ob_get_clean();
				} else {
					$nonce = wp_create_nonce( 'flowmattic-workflow-edit' );

					$flowmattic_apps  = wp_flowmattic()->apps;
					$all_applications = $flowmattic_apps->get_all_applications();

					foreach ( $all_tasks as $task_key => $task ) {
						$applications = array();
						$task_status  = array(
							'success' => 0,
						);

						$json = str_replace( '\r\n', '\\r\\n', $task->task_data ); // escape new line characters.
						$data = json_decode( $json, true );

						$workflow_re_executed = false;
						$re_execution_time    = '';

						// Check if task is re-executed.
						if ( isset( $data['workflow_re_executed'] ) && $data['workflow_re_executed'] ) {
							$workflow_re_executed = true;
							$re_execution_time    = date_i18n( 'd-m-Y h:i:s A', strtotime( $data['re_execution_time'] ) );

							// Remove the workflow re-execution time from the task data.
							unset( $data['re_execution_time'] );
							unset( $data['workflow_re_executed'] );
						}

						if ( json_last_error() ) {
							echo json_last_error_msg();
						} else {
							foreach ( $data as $key => $value ) {
								if ( ! isset( $data[ $key ] ) ) {
									continue;
								}

								// Check if `captured_data` is a JSON string.
								if ( is_string( $data[ $key ]['captured_data'] ) ) {
									$data[ $key ]['captured_data'] = json_decode( $data[ $key ]['captured_data'], true );
								}
							}
						}

						$task_data    = $data; // json_decode( $task->task_data );
						$task_details = $task_data;
						$task_id      = $task->task_id;
						$workflow_id  = $task->workflow_id;
						$task_time    = date_i18n( 'd-m-Y h:i:s A', strtotime( $task->task_time ) );

						$task_history_id = wp_rand();

						$args     = array(
							'workflow_id' => $workflow_id,
						);
						$workflow = ( ! isset( $all_workflow_data[ $workflow_id ] ) ) ? wp_flowmattic()->workflows_db->get( $args ) : $all_workflow_data[ $workflow_id ];
						$settings = json_decode( $workflow->workflow_settings, true );

						$logged_in_user   = wp_get_current_user();
						$wp_user_email    = $logged_in_user->user_email;
						$workflow_manager = isset( $settings['user_email'] ) ? $settings['user_email'] : '';
						if ( ( ! current_user_can( 'manage_options' ) ) ) {
							if ( $workflow_manager !== $wp_user_email ) {
								$total_tasks = $total_tasks - 1;
								continue;
							}
						}

						if ( ! isset( $workflow->workflow_name ) ) {
							continue;
						}

						if ( ! empty( $task_data ) ) {
							$is_router     = false;
							$task_data_dup = $task_data;
							unset( $task_data_dup[0] );
							foreach ( $task_data_dup as $k => $data ) {
								if ( ! isset( $data['application'] ) ) {
									continue;
								}

								if ( 'schedule' === $data['application'] ) {
									$task_status['success'] = 1;
									continue;
								}

								if ( isset( $data['captured_data'] ) ) {
									if ( isset( $data['captured_data']['status'] ) ) {
										if ( 'error' !== trim( strtolower( $data['captured_data']['status'] ) ) ) {
											$task_status['success'] = ( isset( $task_status['success'] ) ) ? $task_status['success'] + 1 : 1;
										} else {
											$task_status['failed'] = ( isset( $task_status['failed'] ) ) ? $task_status['failed'] + 1 : 1;
										}
									} elseif ( '1' === $data['captured_data'] ) {
											$task_status['success'] = ( isset( $task_status['success'] ) ) ? $task_status['success'] + 1 : 1;
									} elseif ( isset( $data['capture_data'] ) && '0' === $data['capture_data'] ) {
										$task_status['failed'] = ( isset( $task_status['failed'] ) ) ? $task_status['failed'] + 1 : 1;
									} else {
										$task_status['success'] = ( isset( $task_status['success'] ) ) ? $task_status['success'] + 1 : 1;
									}

									if ( 'router' === $data['application'] ) {
										if ( 'success' === trim( strtolower( $data['captured_data']['status'] ) ) ) {
											$is_router = true;
										}
									}

									if ( $is_router ) {
										unset( $task_status['failed'] );
										$task_status['success'] = 1;
										continue;
									}
								}
							}

							$count      = count( $task_data );
							$popup_apps = array();
							$i          = ( 3 >= $count ) ? 0 : 1;

							$re_executed_workflow = false;
							if ( isset( $task_data[0]['captured_data']['workflow_re_executed_at'] ) ) {
								$re_executed_workflow = true;
							}

							foreach ( $task_data as $index => $step ) {
								++$i;

								if ( ! isset( $step['application'] ) || ! isset( $all_applications[ $step['application'] ] ) ) {
									continue;
								}

								$application_icon = $all_applications[ $step['application'] ]['icon'];
								$application_name = $all_applications[ $step['application'] ]['name'];

								if ( 3 >= $i ) {
									$applications[] = '<div class="workflow-image" style="width: 30px; height: 30px;" data-toggle="tooltip" title="' . $application_name . '"><img src="' . $application_icon . '"></div>
													<span class="svg-icon svg-icon--step-arrow"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="16" height="16" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
								} else {
									$popup_apps[] = '<div class="workflow-image" style="width: 30px; height: 30px;" data-toggle="tooltip" title="' . $application_name . '"><img src="' . $application_icon . '"></div>
														<span class="svg-icon svg-icon--step-arrow"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="16" height="16" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
								}
							}

							// If count is greater than 3, show the remaining numbers.
							if ( 3 < $count ) {
								$applications[] = '<div class="workflow-image d-flex align-items-center justify-content-center fm-workflow-popup-trigger" data-toggle="tooltip" title="' . esc_html__( 'Click to expand', 'flowmattic' ) . '" style="min-width: 30px;width: auto; height: 30px;">+' . ( $count - 2 ) . '</div>';
							}
						}
						ob_start();
						?>
						<tr data-workflow-id="<?php echo $task->workflow_id; ?>" class="all">
							<td class="ps-3 py-3 bulk-select-input">
								<input type="checkbox" class="bulk-select-checkbox" data-task-id="<?php echo esc_attr( $task->task_id ); ?>" data-workflow-id="<?php echo esc_attr( $task->workflow_id ); ?>">
							</td>
							<td class="ps-3 py-3">
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=edit&workflow-id=' . $task->workflow_id . '&nonce=' . $nonce ); ?>" class="text-reset text-decoration-none">
									<span class="mb-1 d-inline-flex"><?php echo rawurldecode( $workflow->workflow_name ); ?></span>
									<div class="abbr text-muted"><small><?php echo sprintf( __( 'Recorded on %s', 'flowmattic' ), $task_time ); ?></small></div>
								</a>
							</td>
							<td>
								<div class="workflow-applications task-applications d-flex align-items-center position-relative">
									<?php echo implode( '', $applications ); ?>
									<?php
									if ( ! empty( $popup_apps ) ) {
										echo '<div class="fm-workflow-apps-popup"><span class="svg-icon svg-icon--step-arrow"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="16" height="16" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>' . implode( '', $popup_apps ) . '</div>';
									}
									?>
								</div>
							</td>
							<td>
								<div class="workflow-task-status d-flex align-items-center">
									<?php
									if ( isset( $task_status['failed'] ) && isset( $task_status['success'] ) && 0 !== $task_status['success'] ) {
										echo '<span class="badge bg-warning">' . __( 'Partial Failed', 'flowmattic' ) . '</span>';
									} elseif ( isset( $task_status['failed'] ) ) {
											echo '<span class="badge bg-danger">' . __( 'Failed', 'flowmattic' ) . '</span>';
									} else {
										echo '<span class="badge bg-success">' . __( 'Success', 'flowmattic' ) . '</span>';
									}

									// Check if task is re-executed.
									if ( $workflow_re_executed ) {
										echo '<span class="dashicons dashicons-update text-warning ms-2" data-toggle="tooltip" title="' . sprintf( __( 'Re-executed on %s', 'flowmattic' ), $re_execution_time ) . '"></span>';
									} elseif ( $re_executed_workflow ) {
										echo '<span class="dashicons dashicons-update text-secondary ms-2" data-toggle="tooltip" title="' . esc_html__( 'Re-executed workflow', 'flowmattic' ) . '"></span>';
									}
									?>
								</div>
							</td>
							<td>
								<div class="flowmattic-task-details pe-3">
									<button class="btn btn-outline-secondary shadow-none btn-sm flowmattic-task-view-details" data-workflow-id="<?php echo esc_attr( $task->workflow_id ); ?>" data-task-id="<?php echo esc_attr( $task_history_id ); ?>"  data-toggle="modal" data-target="#task-details-modal-<?php echo esc_attr( $task_history_id ); ?>">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="#333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
											<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333" fill="none" d="M12 18C6.48 18 2 12 2 12C2 12 6.48 6 12 6C17.52 6 22 12 22 12C22 12 17.52 18 12 18Z"></path>
											<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333" fill="none" d="M12 14.5C13.3807 14.5 14.5 13.3807 14.5 12C14.5 10.6193 13.3807 9.5 12 9.5C10.6193 9.5 9.5 10.6193 9.5 12C9.5 13.3807 10.6193 14.5 12 14.5Z"></path>
										</svg>&nbsp;
										<?php echo esc_html__( 'View Details', 'flowmattic' ); ?>&nbsp;
									</button>
								</div>
								<div class="col-12 fm-workflows-filters modal fade" id="task-details-modal-<?php echo esc_attr( $task_history_id ); ?>" aria-hidden="true" data-backdrop="static">
									<div class="task-history-modal modal-dialog modal-dialog-centered modal-lg">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="fm-workflow-heading m-0">
													<?php echo rawurldecode( $workflow->workflow_name ); ?>
													<div class="abbr text-muted py-1"><small><?php echo sprintf( __( 'Recorded on %s', 'flowmattic' ), $task_time ); ?></small></div>
												</h5>
												<button type="button" class="btn-close shadow-none" data-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<div class="flowmattic-workflow-task-details">
													<div class="accordion" id="accordion-<?php echo esc_attr( $task->task_id ); ?>">
														<?php
														$has_iterator = false;
														$iterator_end = false;
														$has_router   = false;
														$step_number  = 1;
														$router_steps = array();
														$router_step  = 1;
														$route_letter = '';
														$step_title   = '';

														$workflow        = (array) $workflow;
														$workflow_decode = json_decode( $workflow['workflow_steps'], true );
														$workflow_decode = isset( $workflow_decode['steps'] ) ? $workflow_decode['steps'] : $workflow_decode;

														foreach ( $task_details as $key => $task_data ) {
															if ( ! isset( $task_data['application'] ) ) {
																continue;
															}

															// Check if task has iterator.
															if ( 'iterator' === $task_data['application'] ) {
																$has_iterator = true;
															}

															// Check if task has router.
															if ( 'router' === $task_data['application'] ) {
																$has_router   = true;
																$route_letter = $task_data['route_letter'];
																$router_step  = 1;
															}

															if ( ! isset( $all_applications[ $task_data['application'] ] ) ) {
																continue;
															}

															$application      = $all_applications[ $task_data['application'] ];
															$application_name = $application['name'];
															$application_icon = $application['icon'];
															$application_icon = '<div class="workflow-image me-3" style="width: 24px; height: 24px;"><img src="' . $application_icon . '"></div>';

															// Set the step title.
															$step_title = '<div class="d-flex flex-column align-items-start">';
															$step_title .= '<span class="fw-bold">' . $application_name . '</span>';

															if ( isset( $task_data['step_title'] ) ) {
																$step_title .= '<span class="text-muted">' . $task_data['step_title'] . '</span>';
															}

															$step_title .= '</div>';

															// Check if task is failed or success.
															$current_task_status = 'success';

															$task_data['captured_data']['status'] = isset( $task_data['captured_data']['status'] ) ? $task_data['captured_data']['status'] : 'Success';

															if ( isset( $task_data['captured_data']['status'] ) && ! is_array( $task_data['captured_data']['status'] ) ) {
																if ( is_bool( $task_data['captured_data']['status'] ) && $task_data['captured_data']['status'] ) {
																	$current_task_status = 'success';
																} elseif ( is_numeric( $task_data['captured_data']['status'] ) && 0 === (int) $task_data['captured_data']['status'] ) {
																	$current_task_status = 'fail';
																} elseif ( 'success' !== strtolower( $task_data['captured_data']['status'] ) ) {
																	$current_task_status = 'fail';
																}

																if ( 'pending' === strtolower( $task_data['captured_data']['status'] ) ) {
																	$current_task_status = 'success';
																}

																if ( 'error' !== strtolower( $task_data['captured_data']['status'] ) && 'fail' !== strtolower( $task_data['captured_data']['status'] ) ) {
																	$current_task_status = 'success';
																}

																if ( false !== strpos( strtolower( $task_data['captured_data']['status'] ), 'success' ) ) {
																	$current_task_status = 'success';
																}
															}

															$next_scheduled = false;
															if ( 'delay' === $task_data['application'] && isset( $task_data['cron_data'] ) ) {
																$cron_data       = $task_data['cron_data'];
																$next_step_id    = $cron_data['next_step_id'];
																$task_history_id = $cron_data['task_history_id'];
																$workflow_id     = $task->workflow_id;

																// Get the cron event data.
																$next_scheduled = wp_next_scheduled( 'flowmattic_delay_workflow_step', array( $task_history_id, $next_step_id, $workflow_id ) );

																if ( $next_scheduled ) {
																	$current_task_status = 'pending';
																}
															}
															?>
															<div class="card mw-100 p-0">
																<div class="card-header bg-light border-light" id="heading-<?php echo esc_attr( $task->task_id ); ?>-<?php echo esc_attr( $step_number ); ?>">
																	<h2 class="mb-0 mt-0 w-100 d-flex">
																		<button class="btn btn-link btn-block text-left text-reset text-decoration-none shadow-none collapsed d-flex align-items-center w-100 ps-0" type="button" data-toggle="collapse" data-target="#collapse-<?php echo esc_attr( $task->task_id ); ?>-<?php echo esc_attr( $step_number ); ?>" aria-expanded="true" aria-controls="collapse-<?php echo esc_attr( $task->task_id ); ?>-<?php echo esc_attr( $step_number ); ?>">
																			<?php echo $application_icon . ' ' . $step_title; ?>
																		</button>
																		<div class="task-status-icon align-items-center d-flex">
																			<?php
																			switch ( $current_task_status ) {
																				case 'success':
																					echo '<span class="fs-6 badge bg-success rounded-circle p-0"><span class="dashicons dashicons-yes-alt"></span></span>';
																					break;

																				case 'fail':
																					echo '<span class="fs-6 badge bg-danger rounded-circle p-0"><span class="dashicons dashicons-warning"></span></span>';
																					break;

																				case 'pending':
																					echo '<span class="fs-6 delay-badge badge bg-warning rounded-circle p-0"><span class="dashicons dashicons-clock"></span></span>';
																					break;
																			}
																			?>
																		</div>
																	</h2>
																</div>
																<?php
																$task_step_id = esc_attr( $task->task_id ) . '-' . esc_attr( $step_number );
																$request_data = array();
																$task_request_data = isset( $task_data['request_data'] ) ? $task_data['request_data'] : '';
																if ( ! empty( $task_request_data ) && is_string( $task_request_data ) ) {
																	if ( is_array( json_decode( stripslashes( $task_data['request_data'] ), true ) ) ) {
																		$request_data = json_decode( stripslashes( $task_data['request_data'] ), true );
																	} else {
																		$request_data = json_decode( $task_data['request_data'], true );
																	}
																}
																?>
																<div id="collapse-<?php echo esc_attr( $task_step_id ); ?>" class="collapse" aria-labelledby="heading-<?php echo esc_attr( $task_step_id ); ?>" data-parent="#accordion-<?php echo esc_attr( $task->task_id ); ?>">
																	<div class="card-body p-1">
																		<ul class="nav nav-tabs border-bottom-0 <?php echo ( empty( $request_data ) ? 'd-none' : '' ); ?>" role="tablist" id="nav-tab">
																			<li class="nav-item m-0">
																				<a class="nav-link active" aria-current="page" data-toggle="tab" data-target="#response-data-<?php echo esc_attr( $task_step_id ); ?>" href="#response-data-<?php echo esc_attr( $task_step_id ); ?>"><?php esc_html_e( 'Response Data', 'flowmattic' ); ?></a>
																			</li>
																			<li class="nav-item m-0">
																				<a class="nav-link" data-toggle="tab" data-target="#request-payload-<?php echo esc_attr( $task_step_id ); ?>" href="#request-payload-<?php echo esc_attr( $task_step_id ); ?>"><?php esc_html_e( 'Request Payload', 'flowmattic' ); ?></a>
																			</li>
																		</ul>
																		<div class="tab-content p-2 <?php echo ( ! empty( $request_data ) ? 'border' : '' ); ?>" id="nav-tabContent">
																			<div class="tab-pane fade active show" id="response-data-<?php echo esc_attr( $task_step_id ); ?>" role="tabpanel" aria-labelledby="response-data-<?php echo esc_attr( $task_step_id ); ?>">
																				<?php
																				if ( 'schedule' === $task_data['application'] ) {
																					echo esc_html__( 'Scheduled workflow executed, there\'s no data captured at this step.', 'flowmattic' );
																				}

																				$data_captured = (array) $task_data['captured_data'];

																				if ( ! empty( $data_captured ) ) {
																					?>
																					<table class="fm-task-data-table w-100">
																						<tbody>
																						<?php
																						$response_array = array();

																						// Normalize response.
																						foreach ( $data_captured as $response_key => $value ) {
																							if ( is_array( $value ) && ! empty( $value ) ) {
																								// $response_array = flowmattic_recursive_array( $response_array, $response_key, (array) $value );
																								$response_array[ $response_key ] = wp_json_encode( $value );
																							} else {
																								if ( is_array( $value ) && empty( $value ) ) {
																									continue;
																								}

																								$response_array[ $response_key ] = $value;
																							}
																						}

																						// Loop through the response content.
																						foreach ( $response_array as $data_key => $data_value ) {
																							$data_key = ucwords( str_replace( array( '_', '-' ), ' ', $data_key ) );
																							?>
																							<tr class="border-bottom">
																								<th class="py-2 w-35" valign="top">
																									<?php
																									echo esc_html( $data_key );
																									?>
																								</th>
																								<td>
																									<?php
																									if ( is_bool( $data_value ) ) {
																										if ( $data_value ) {
																											echo 'true';
																										} else {
																											echo 'false';
																										}
																									} else {
																										echo stripslashes( esc_html( $data_value ) );
																									}
																									?>
																								</td>
																							</tr>
																							<?php
																						}

																						// If is delay, add a button to cancel the delay and stop the workflow.
																						if ( 'delay' === $task_data['application'] && isset( $task_data['cron_data'] ) ) {
																							if ( $next_scheduled ) {
																								?>
																								<tr class="border-bottom">
																									<td>
																										<button type="button" class="btn btn-sm btn-outline-danger shadow-none btn-cancel-delay mt-3" data-task-id="<?php echo esc_attr( $task_history_id ); ?>" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>" data-next-step-id="<?php echo esc_attr( $next_step_id ); ?>">
																											<?php echo esc_html__( 'Cancel Delay', 'flowmattic' ); ?>
																										</button>
																									</td>
																									<td>
																										<?php echo esc_html__( 'Click the button to cancel the delay and stop the workflow.', 'flowmattic' ); ?>
																									</td>
																								</tr>
																								<?php
																							}
																						}
																						?>
																						</tbody>
																					</table>
																					<?php
																				}
																				?>
																			</div>
																			<div class="tab-pane fade" id="request-payload-<?php echo esc_attr( $task_step_id ); ?>" role="tabpanel" aria-labelledby="request-payload-<?php echo esc_attr( $task_step_id ); ?>">
																				<?php
																				if ( ! empty( $request_data ) ) {
																					?>
																					<table class="fm-task-data-table w-100">
																						<tbody>
																						<?php
																						$response_array = array();

																						// Normalize response.
																						foreach ( $request_data as $key => $value ) {
																							if ( is_array( $value ) && ! empty( $value ) ) {
																								$response_array = flowmattic_recursive_array( $response_array, $key, (array) $value );
																							} else {
																								if ( is_array( $value ) && empty( $value ) ) {
																									continue;
																								}

																								$response_array[ $key ] = $value;
																							}
																						}

																						// Loop through the response content.
																						foreach ( $response_array as $data_key => $data_value ) {
																							$data_key = ucwords( str_replace( array( '_', '-' ), ' ', $data_key ) );
																							?>
																							<tr class="border-bottom">
																								<th class="py-2 w-35" valign="top">
																									<?php
																									echo esc_html( $data_key );
																									?>
																								</th>
																								<td>
																									<?php
																									if ( is_bool( $data_value ) ) {
																										if ( $data_value ) {
																											echo 'true';
																										} else {
																											echo 'false';
																										}
																									} else {
																										echo stripslashes( esc_html( $data_value ) );
																									}
																									?>
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
																			</div>
																		</div>
																	</div>
																</div>
															</div>
															<?php
															$step_number = $step_number + 1;
														}
														?>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<script type="text/javascript">
												<?php
												$data_captured   = (array) $task_details[0]['captured_data'];
												$re_execute_data = array();
												foreach ( $data_captured as $key => $value ) {
													if ( is_array( $value ) ) {
														$re_execute_data[ $key ] = wp_json_encode( $value );
													} else {
														$re_execute_data[ $key ] = ( $value ) ? stripslashes( $value ) : $value;
													}
												}
												echo 'var task_' . $task_id . '_trigger=' . wp_json_encode( $re_execute_data ) . ';';
												?>
												</script>
												<button type="button" class="btn-re-execute-task shadow-none btn btn-primary" data-trigger-app="<?php echo esc_html( $task_details[0]['application'] ); ?>" data-task-id="<?php echo esc_attr( $task_id ); ?>" data-workflow-id="<?php echo esc_attr( $task->workflow_id ); ?>">
													<?php echo esc_html__( 'Re-execute', 'flowmattic' ); ?>
												</button>
												<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
											</div>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<?php
						$workflow_content .= ob_get_clean();
					}
				}
				?>
				<div class="flowmattic-container flowmattic-dashboard m-0 mt-4">
					<div class="row flex-row-reverse">
						<div class="col-12 fm-workflows-filters modal fade" id="task-filters-modal" aria-hidden="true" data-backdrop="static">
							<div class="modal-dialog modal-dialog-scrollable modal-dialog-centereds modal-lg">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="fm-workflow-heading m-0">
											<?php echo esc_attr__( 'Filters', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close shadow-none" data-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<div class="flowmattic-workflow-filters">
											<ul class="p-0">
												<li class="fm-task-filter-date-range">
													<h4><?php echo esc_html__( 'Date Range', 'flowmattic' ); ?></h4>
													<div class="input-group mb-3 border">
														<span class="dashicons dashicons-calendar-alt input-group-text p-3 align-items-center justify-content-center bg-light border-0" id="task-date-range"></span>
														<input type="text" class="form-control task-date-ranges border-0 bg-light" aria-label="Date Range" aria-describedby="task-date-range" value="<?php esc_attr( $date_range ); ?>" data-start-date="<?php echo esc_attr( $from_date ); ?>" data-end-date="<?php echo esc_attr( $to_date ); ?>">
													</div>
												</li>
												<li class="fm-task-filter-workflow">
													<h4><?php echo esc_html__( 'Workflow', 'flowmattic' ); ?></h4>
													<div class="input-group mb-3 border">
														<select class="form-select task-workflow-filter mw-100 p-0 border-0" aria-label="Select workflow" data-live-search="true">
															<option value="" readonly disabled selected><?php echo esc_html__( 'Select Workflow', 'flowmattic' ); ?></option>
															<?php
															if ( ! empty( $all_workflows ) ) {
																// Sort workflows.
																arsort( $all_workflows );

																foreach ( $all_workflows as $key => $workflow ) {
																	if ( $filter_workflow_id === $workflow->workflow_id ) {
																		$filter_workflow_name = rawurldecode( $workflow->workflow_name );
																	}

																	echo '<option ' . ( ( $filter_workflow_id === $workflow->workflow_id ) ? 'selected' : '' ) . ' value="' . $workflow->workflow_id . '">' . rawurldecode( $workflow->workflow_name ) . '</option>';
																}
															}
															?>
														</select>
													</div>
												</li>
											</ul>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
										<button type="button" class="btn btn-primary btn-apply-changes"><?php echo esc_html__( 'Apply Filters', 'flowmattic' ); ?></button>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12 fm-workflows-list ps-4 pe-4">
							<div class="fm-workflow-task-header d-flex mb-4 justify-content-between">
								<h3 class="fm-workflow-heading m-0">
									<?php echo esc_attr__( 'Task History', 'flowmattic' ); ?>
								</h3>
								<form class="d-flex w-50 mb-3 mb-lg-0 me-lg-3 history-search" action="<?php echo admin_url(); ?>admin.php">
									<input type="hidden" name="page" value="flowmattic-task-history" />
									<?php
									// If task status is selected.
									if ( isset( $_GET['task_status'] ) ) {
										echo '<input type="hidden" name="task_status" value="' . $_GET['task_status'] . '" />';
									}

									// If date range is selected.
									if ( isset( $_GET['date_range'] ) ) {
										echo '<input type="hidden" name="date_range" value="' . $_GET['date_range'] . '" />';
									}

									// If workflow is selected.
									if ( isset( $_GET['workflow-id'] ) ) {
										echo '<input type="hidden" name="workflow-id" value="' . $_GET['workflow-id'] . '" />';
									}
									?>
									<input type="search" name="fm-query" class="form-control search-history" placeholder="Search..." aria-label="Search" value="<?php echo esc_attr( $search_term ); ?>" style="height: 38px;">
									<button type="submit" class="ms-3 btn btn-md btn-outline-primary d-flex align-items-center px-3">
										<span class="dashicons dashicons-search me-2"></span>
										<?php echo esc_attr__( 'Search', 'flowmattic' ); ?>
									</button>
								</form>
								<button class="btn btn-md btn-outline-primary d-flex align-items-center px-3" data-toggle="modal" data-target="#task-filters-modal">
									<span class="dashicons dashicons-filter me-2"></span>
									<?php echo esc_attr__( 'Filters', 'flowmattic' ); ?>
								</button>
							</div>
							<?php
							// Check if filters are applied.
							if ( '' !== $filter_workflow_name || '' !== $date_range ) {
								?>
								<div class="history-filters-applied navbar mt-3 mb-3 bg-light px-3">
									<?php
									echo '<div class="history-filter-item">' . esc_attr__( 'Filters Applied:', 'flowmattic' ) . '</div>';

									if ( '' !== $filter_workflow_name ) {
										echo '<div class="history-filter-item">' . esc_attr__( 'Workflow:', 'flowmattic' ) . ' <strong>' . $filter_workflow_name . '</strong></div>';
									}
									if ( '' !== $date_range ) {
										echo '<div class="history-filter-item">' . esc_attr__( 'Date Range:', 'flowmattic' ) . ' <strong>' . $date_range . '</strong></div>';
									}
									?>
									<a href="<?php echo admin_url( 'admin.php?page=flowmattic-task-history' ); ?>" class="btn btn-md btn-outline-primary d-flex align-items-center px-3">
										<?php echo esc_attr__( 'Remove Filters', 'flowmattic' ); ?>
									</a>
								</div>
								<?php
							}
							?>
							<div class="bulk-execute-nav navbar navbar-light bg-light justify-content-between mb-2 ps-2">
								<div class="d-flex">
									<span class="pe-3 nav-link disabled"><?php echo esc_attr( 'Bulk Options: ', 'flowmattic' ); ?></span>
									<button class="btn btn-primary btn-sm me-2 disabled" id="bulk-execute-tasks">
										<?php echo esc_html__( 'Execute Selected', 'flowmattic' ); ?>
									</button>
									<button class="btn btn-outline-danger btn-sm me-2 disabled" id="bulk-delete-tasks">
										<?php echo esc_html__( 'Delete Selected', 'flowmattic' ); ?>
									</button>
								</div>
							</div>
							<div class="fm-workflow-table">
								<table class="table table-hover align-middle mb-0">
									<thead class="table-light">
										<tr>
											<th width="4%" class="ps-3 bulk-select"><input type="checkbox" class="bulk-select-all"></th>
											<th width="45%" class="ps-3"><?php echo esc_html__( 'Workflow Name', 'flowmattic' ); ?></th>
											<th><?php echo esc_html__( 'Applications', 'flowmattic' ); ?></th>
											<th>
												<div class="dropdown">
													<a class="text-decoration-none dropdown-toggle" href="#" id="taskStatusDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
														<?php echo esc_html__( 'Status', 'flowmattic' ); ?>
													</a>
													<ul class="dropdown-menu dropdown-menu-light" aria-labelledby="taskStatusDropdown">
														<?php
														$url_params = $_GET;
														unset( $url_params['task_status'] );
														unset( $url_params['page'] );
														$url_query = http_build_query( $url_params );
														?>
														<li><h6 class="dropdown-header"><?php esc_html_e( 'Filter by Status', 'flowmattic' ); ?></h6></li>
														<li><a class="dropdown-item" href="<?php echo esc_attr( admin_url( 'admin.php?page=flowmattic-task-history&task_status=success&' ) ) . $url_query; ?>"><span class="badge bg-success"><?php esc_html_e( 'Success', 'flowmattic' ); ?></span></a></li>
														<li><a class="dropdown-item" href="<?php echo esc_attr( admin_url( 'admin.php?page=flowmattic-task-history&task_status=failed&' ) ) . $url_query; ?>"><span class="badge bg-danger"><?php esc_html_e( 'Failed', 'flowmattic' ); ?></span> / <span class="badge bg-warning"><?php esc_html_e( 'Partial Failed', 'flowmattic' ); ?></span></a></li>
													</ul>
												</div>
											</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php
											echo $workflow_content;
										?>
									</tbody>
								</table>
							</div>
							<div class="bg-white py-3 px-3 border-bottom d-flex flex-col justify-content-between">
								<div class="fm-workflow-pagination d-flex justify-content-center align-items-center gap-2">
									<div class="fm-workflow-pagination-list">
										<?php
										$pagination = paginate_links(
											array(
												'base'      => add_query_arg( 'task-page', '%#%' ),
												'format'    => '',
												'total'     => ceil( $total_tasks / $items_count_per_page ),
												'current'   => $page,
												'type'      => 'array',
												'prev_text' => esc_html__( 'Previous', 'flowmattic' ),
												'next_text' => esc_html__( 'Next', 'flowmattic' ),
											)
										);

										if ( $pagination ) {
											$page_links  = "<ul class='pagination mb-0'>\n\t<li class='page-item mb-0'>";
											$page_links .= implode( "</li>\n\t<li class='page-item mb-0'>", $pagination );
											$page_links .= "</li>\n</ul>\n";

											$page_links = str_replace( 'Previous', '&laquo;', $page_links );
											$page_links = str_replace( 'Next', '&raquo;', $page_links );

											echo str_replace( 'page-numbers', 'page-link', $page_links );
										}
										?>
									</div>
									<div class="fm-pagination-count-select" style="width: 80px;">
										<select class="form-select form-select-sm py-1 text-primary border" id="task-history-per-page" aria-label="Select number of items per page">
											<option value="10" <?php selected( $items_count_per_page, 10 ); ?>>10</option>
											<option value="20" <?php selected( $items_count_per_page, 20 ); ?>>20</option>
											<option value="30" <?php selected( $items_count_per_page, 30 ); ?>>30</option>
											<option value="40" <?php selected( $items_count_per_page, 40 ); ?>>40</option>
											<option value="50" <?php selected( $items_count_per_page, 50 ); ?>>50</option>
											<option value="100" <?php selected( $items_count_per_page, 100 ); ?>>100</option>
										</select>
									</div>
									<span class="text-muted">
										<small><?php echo esc_html__( 'items per page', 'flowmattic' ); ?></small>
									</span>
								</div>
								<div class="fm-workflow-pagination-info d-flex justify-content-center align-items-center">
									<div class="text-muted">
										<small>
											<?php
											if ( $total_tasks > 0 ) {
												echo sprintf( __( 'Showing %1$d to %2$d of %3$s items', 'flowmattic' ), $offset + 1, min( $offset + $items_count_per_page, $total_tasks ), number_format( $total_tasks ) );
											} else {
												echo esc_html__( 'No items found', 'flowmattic' );
											}
											?>
										</small>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php FlowMattic_Admin::footer(); ?>
<div class="flowmattic-re-execute-workflow-modal flowmattic-route-editor hidden" style="left:0;right:0;margin:0 auto;height: calc(75% - 32px);">
	<div class="fm-re-execute-modal-heading d-flex justify-content-between border border-start-0 bg-light">
		<div class="fm-modal-heading-left d-flex">
			<h4 class="m-0 p-3 fs-5">
				<?php echo esc_html__( 'Re-execute Workflow', 'flowmattic' ); ?>
			</h4>
		</div>
		<div class="fm-modal-heading-right d-flex">
			<a href="javascript:void(0);" class="btn-dismis-modal router-editor-close btn text-center align-items-center d-inline-flex px-4">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
					<path d="M22.7071 1.29289C23.0976 1.68342 23.0976 2.31658 22.7071 2.70711L2.70711 22.7071C2.31658 23.0976 1.68342 23.0976 1.29289 22.7071C0.902369 22.3166 0.902369 21.6834 1.29289 21.2929L21.2929 1.29289C21.6834 0.902369 22.3166 0.902369 22.7071 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
					<path d="M1.29289 1.29289C1.68342 0.902369 2.31658 0.902369 2.70711 1.29289L22.7071 21.2929C23.0976 21.6834 23.0976 22.3166 22.7071 22.7071C22.3166 23.0976 21.6834 23.0976 21.2929 22.7071L1.29289 2.70711C0.902369 2.31658 0.902369 1.68342 1.29289 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
				</svg>
			</a>
		</div>
	</div>
	<div class="fm-re-execute-modal-body router-steps p-4 border-end">
		<div class="alert alert-primary" role="alert">
			<span class="fs-6"><?php echo esc_html__( 'Here is the response of your trigger app. You can use the same data again or you can change it manually to re-execute your workflow.', 'flowmattic' ); ?></span>
		</div>
		<div class="fm-response-table w-100">
			<table class="fm-response-data-table w-100">
				<thead>
					<tr>
						<th class="w-50">Key</th>
						<th class="w-50">Value</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<div class="fm-re-execute-modal-footer router-footer m-0 px-4 py-3 border border-start-0 bg-light text-end">
		<button type="button" class="btn-re-execute shadow-none btn btn-primary">
			<?php echo esc_html__( 'Re-execute now', 'flowmattic' ); ?>
		</button>
		<button type="button" class="btn btn-danger btn-dismis-modal" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
	</div>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		const swalPopup = window.Swal.mixin({
			customClass: {
				confirmButton: 'btn btn-primary shadow-none me-xxl-3',
				cancelButton: 'btn btn-danger shadow-none'
			},
			buttonsStyling: false
		} );

		// If bulk select checkbox checked, select all checkboxes.
		jQuery( '.bulk-select-all' ).on( 'change', function() {
			jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]' ).prop( 'checked', this.checked ).trigger( 'change' );
		} );

		// If any of the bulk select checkbox is unchecked, uncheck the select all checkbox.
		jQuery( '.bulk-select-input' ).on( 'change', 'input[type="checkbox"]', function() {
			if ( ! this.checked ) {
				jQuery( '.bulk-select-all' ).prop( 'checked', false );
			}
		} );

		// If any of the bulk select checkbox is checked, enable the bulk execute button.
		jQuery( '.bulk-select-input' ).on( 'change', 'input[type="checkbox"]', function() {
			if ( jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]:checked' ).length > 0 ) {
				jQuery( '.bulk-execute-nav' ).find( 'button' ).removeClass( 'disabled' );
			} else {
				jQuery( '.bulk-execute-nav' ).find( 'button' ).addClass( 'disabled' );
			}
		} );

		// If all the bulk select checkboxes are checked, check the select all checkbox.
		jQuery( '.bulk-select-input' ).on( 'change', 'input[type="checkbox"]', function() {
			if ( jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]:checked' ).length === jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]' ).length ) {
				jQuery( '.bulk-select-all' ).prop( 'checked', true );
			}
		} );

		// If bulk execute button is clicked, execute the selected tasks.
		jQuery( '#bulk-execute-tasks' ).on( 'click', function() {
			var selected_tasks = [];

			// Show confirmation message with swal.
			swalPopup.fire( {
				title: '<?php echo esc_attr( __( 'Are you sure?', 'flowmattic' ) ); ?>',
				text: '<?php echo esc_attr( __( 'You are about to execute the selected tasks.', 'flowmattic' ) ); ?>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<?php echo esc_attr( __( 'Yes, execute', 'flowmattic' ) ); ?>',
				cancelButtonText: '<?php echo esc_attr( __( 'No, cancel', 'flowmattic' ) ); ?>'
			} ).then( ( result ) => {
				if ( result.isConfirmed ) {
					// Show loading popup.
					swalPopup.fire(
						{
							title: 'Re-executing workflows...',
							text: 'Please do not close this window till the execution is not completed. You can switch to another tab, if required.',
							showConfirmButton: false,
							didOpen: function() {
								swalPopup.showLoading();
							}
						}
					);

					jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]:checked' ).each( function() {
						var workflowID = jQuery( this ).data( 'workflow-id' ),
							taskID = jQuery( this ).data( 'task-id' ),
							taskCapture = window['task_' + taskID + '_trigger'],
							taskData = {};

						taskData = {
							workflow_id: workflowID,
							captured_data: taskCapture,
							task_id: taskID
						}

						selected_tasks.push( taskData );
					} );

					if ( selected_tasks.length > 0 ) {
						jQuery.ajax( {
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'flowmattic_bulk_execute_tasks',
								workflows: selected_tasks,
								workflow_nonce: FMConfig.workflow_nonce
							},
							success: function( response ) {
								response = JSON.parse( response );
								if ( 'success' === response.status ) {
									swalPopup.fire( {
										title: '<?php echo esc_attr( __( 'Success!', 'flowmattic' ) ); ?>',
										icon: 'success',
										showConfirmButton: false,
										timer: 1500
									} ).then( function() {
										location.reload();
									} );
								} else {
									swalPopup.fire( {
										title: '<?php echo esc_attr( __( 'Error!', 'flowmattic' ) ); ?>',
										text: '<?php echo esc_attr( __( 'Something went wrong, please try again', 'flowmattic' ) ); ?>',
										icon: 'error',
										showConfirmButton: false,
										timer: 1500
									} );
								}
							}
						} );
					}
				}
			} );
		} );

		// If delete selected button is clicked, process the request.
		jQuery( '#bulk-delete-tasks' ).on( 'click', function() {
			var selected_tasks = [];

			// Show confirmation message with swal.
			swalPopup.fire( {
				title: '<?php echo esc_attr( __( 'Are you sure?', 'flowmattic' ) ); ?>',
				text: '<?php echo esc_attr( __( 'You are about to delete the selected tasks.', 'flowmattic' ) ); ?>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<?php echo esc_attr( __( 'Yes, delete', 'flowmattic' ) ); ?>',
				cancelButtonText: '<?php echo esc_attr( __( 'No, cancel', 'flowmattic' ) ); ?>'
			} ).then( ( result ) => {
				if ( result.isConfirmed ) {
					// Show loading popup.
					swalPopup.fire(
						{
							title: 'Deleting Selected Tasks...',
							text: 'Please do not close this window till the execution is not completed. You can switch to another tab, if required.',
							showConfirmButton: false,
							didOpen: function() {
								swalPopup.showLoading();
							}
						}
					);

					jQuery( '.bulk-select-input' ).find( 'input[type="checkbox"]:checked' ).each( function() {
						var taskID = jQuery( this ).data( 'task-id' ),
							taskData = {};

						taskData = {
							task_id: taskID
						}

						selected_tasks.push( taskData );
					} );

					if ( selected_tasks.length > 0 ) {
						jQuery.ajax( {
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'flowmattic_bulk_delete_tasks',
								tasks: selected_tasks,
								workflow_nonce: FMConfig.workflow_nonce
							},
							success: function( response ) {
								response = JSON.parse( response );
								if ( 'success' === response.status ) {
									swalPopup.fire( {
										title: '<?php echo esc_attr( __( 'Success!', 'flowmattic' ) ); ?>',
										icon: 'success',
										showConfirmButton: false,
										timer: 1500
									} ).then( function() {
										location.reload();
									} );
								} else {
									swalPopup.fire( {
										title: '<?php echo esc_attr( __( 'Error!', 'flowmattic' ) ); ?>',
										text: '<?php echo esc_attr( __( 'Something went wrong, please try again', 'flowmattic' ) ); ?>',
										icon: 'error',
										showConfirmButton: false,
										timer: 1500
									} );
								}
							}
						} );
					}
				}
			} );
		} );

		// Cancel delay button click event.
		jQuery( '.btn-cancel-delay' ).on( 'click', function() {
			var thisBtn = jQuery( this ),
				taskID = jQuery( this ).data( 'task-id' ),
				workflowID = jQuery( this ).data( 'workflow-id' ),
				nextStepID = jQuery( this ).data( 'next-step-id' );

			// Show confirmation message with swal.
			swalPopup.fire( {
				title: '<?php echo esc_attr( __( 'Are you sure?', 'flowmattic' ) ); ?>',
				text: '<?php echo esc_attr( __( 'You are about to cancel the delay and stop the workflow.', 'flowmattic' ) ); ?>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<?php echo esc_attr( __( 'Yes, cancel', 'flowmattic' ) ); ?>',
				cancelButtonText: '<?php echo esc_attr( __( 'No, cancel', 'flowmattic' ) ); ?>'
			} ).then( ( result ) => {
				if ( result.isConfirmed ) {
					// Show loading popup.
					swalPopup.fire(
						{
							title: 'Cancelling Delay...',
							text: 'Please do not close this window till the execution is not completed. You can switch to another tab, if required.',
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
							action: 'flowmattic_cancel_delay',
							task_id: taskID,
							workflow_id: workflowID,
							next_step_id: nextStepID,
							workflow_nonce: FMConfig.workflow_nonce
						},
						success: function( response ) {
							response = JSON.parse( response );
							if ( 'success' === response.status ) {
								// Add a new row with the message.
								thisBtn.closest( 'tr' ).after( '<tr><th class="py-2 w-35" valign="top">' + '<?php echo esc_attr( __( 'Update', 'flowmattic' ) ); ?>' + '</th><td>' + '<?php echo esc_html__( 'Delay was cancelled at ', 'flowmattic' ) . date_i18n( 'd-m-Y H:i:s' ); ?>' + '</td></tr>' );

								// Change the status in the first row second column to 'Cancelled'.
								thisBtn.closest( 'tbody' ).find( 'tr:first-child td' ).text( '<?php echo esc_html__( 'Cancelled', 'flowmattic' ); ?>' );

								// Remove the button row.
								thisBtn.closest( 'tr' ).remove();

								// Show success message.
								swalPopup.fire( {
									title: '<?php echo esc_attr( __( 'Success!', 'flowmattic' ) ); ?>',
									icon: 'success',
									showConfirmButton: false,
									timer: 1500
								} );
							} else {
								swalPopup.fire( {
									title: '<?php echo esc_attr( __( 'Error!', 'flowmattic' ) ); ?>',
									text: '<?php echo esc_attr( __( 'Something went wrong, please try again', 'flowmattic' ) ); ?>' + '<br/> Error Message: <span class="text-danger">' + response.error + '</span>',
									icon: 'error',
									showConfirmButton: false,
									timer: 1500
								} );
							}
						}
					} );
				}
			} );
		} );

		// On change of the select box, change the number of items per page and reload the page.
		jQuery( '#task-history-per-page' ).on( 'change', function() {
			var itemsPerPage = jQuery( this ).val();

			// Set the cookie for items per page.
			document.cookie = 'fm_items_per_page=' + itemsPerPage;

			var url = new URL( window.location.href );
			window.location.href = url.href;
		} );
	});
</script>
<style type="text/css">
	.badge {
		padding: 8px 10px !important;
	}
	.badge.bg-warning:not(.delay-badge) {
		background-color: #ffc107 !important;
		color: #604904ff !important;
	}
</style>