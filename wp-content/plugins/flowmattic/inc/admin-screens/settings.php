<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				$settings            = get_option( 'flowmattic_settings', array() );
				$integrations_access = isset( $settings['integration_page_access'] ) ? $settings['integration_page_access'] : 'yes';
				$delete_app_access   = isset( $settings['delete_app_access'] ) ? $settings['delete_app_access'] : 'yes';
				$webhook_url_base    = isset( $settings['webhook_url_base'] ) ? $settings['webhook_url_base'] : 'regular';

				// Set the default values for connect settings.
				$notification_email                       = isset( $settings['notification_email'] ) ? $settings['notification_email'] : '';
				$settings['enable_notifications_connect'] = isset( $settings['enable_notifications_connect'] ) ? $settings['enable_notifications_connect'] : 'yes';
				$settings['notification_email_connect']   = isset( $settings['notification_email_connect'] ) ? $settings['notification_email_connect'] : $notification_email;
				?>

				<style>
					:root {
						--fm-primary: #2563eb;
						--fm-primary-hover: #1d4ed8;
						--fm-success: #059669;
						--fm-warning: #d97706;
						--fm-danger: #dc2626;
						--fm-secondary: #6b7280;
						--fm-light: #f8fafc;
						--fm-dark: #1e293b;
						--fm-border: #e2e8f0;
						--fm-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
						--fm-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
					}

					.fm-settings-container {
						background: var(--fm-light);
						min-height: 100vh;
						padding: 20px 0;
					}

					.fm-settings-header {
						border-radius: 16px;
						padding: 0 30px;
						margin-bottom: 30px;
					}

					.fm-settings-title {
						font-size: 32px;
						font-weight: 700;
						color: var(--fm-dark);
						margin: 0 0 10px 0;
						display: flex;
						align-items: center;
						gap: 15px;
					}

					.fm-settings-subtitle {
						color: var(--fm-secondary);
						font-size: 16px;
						margin: 0;
					}

					.fm-section-card {
						background: white;
						border-radius: 16px;
						padding: 0;
						margin-bottom: 24px;
						box-shadow: var(--fm-shadow);
						border: 1px solid var(--fm-border);
						overflow: hidden;
						transition: all 0.3s ease;
					}

					.fm-section-card:hover {
						box-shadow: var(--fm-shadow-lg);
						transform: translateY(-2px);
					}

					.fm-section-header {
						background: #f8fafc;
						color: var(--fm-dark);
						padding: 20px 30px;
						border-bottom: 1px solid var(--fm-border);
					}

					.fm-section-title {
						font-size: 20px;
						font-weight: 600;
						margin: 0 0 5px 0;
						display: flex;
						align-items: center;
						gap: 12px;
						color: var(--fm-dark);
					}

					.fm-section-description {
						font-size: 14px;
						color: var(--fm-secondary);
						margin: 0;
					}

					.fm-section-content {
						padding: 30px;
					}

					.fm-form-group {
						margin-bottom: 24px;
					}

					.fm-form-group:last-child {
						margin-bottom: 0;
					}

					.fm-form-label {
						font-weight: 600;
						color: var(--fm-dark);
						margin-bottom: 8px;
						display: block;
						font-size: 14px;
					}

					.fm-form-control {
						width: 100%;
						padding: 12px 16px;
						border: 2px solid var(--fm-border);
						border-radius: 8px;
						font-size: 14px;
						transition: all 0.2s ease;
						background: white;
					}

					.fm-form-control:focus {
						outline: none;
						border-color: var(--fm-primary);
						box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
					}

					.fm-form-select {
						appearance: none;
						background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
						background-position: right 12px center;
						background-repeat: no-repeat;
						background-size: 16px;
						padding-right: 40px;
					}

					.fm-form-help {
						font-size: 13px;
						color: var(--fm-secondary);
						margin-top: 6px;
						line-height: 1.5;
					}

					.fm-row {
						display: grid;
						grid-template-columns: 1fr 1fr;
						gap: 24px;
					}

					@media (max-width: 768px) {
						.fm-row {
							grid-template-columns: 1fr;
						}
					}

					.fm-database-table {
						background: white;
						border-radius: 12px;
						overflow: hidden;
						border: 1px solid var(--fm-border);
						margin-bottom: 20px;
					}

					.fm-database-header {
						background: #f1f5f9;
						padding: 16px 20px;
						border-bottom: 1px solid var(--fm-border);
						display: grid;
						grid-template-columns: 80px 1fr 1fr 150px;
						gap: 20px;
						font-weight: 600;
						color: var(--fm-dark);
						font-size: 14px;
					}

					.fm-database-row {
						padding: 20px;
						border-bottom: 1px solid var(--fm-border);
						display: grid;
						grid-template-columns: 80px 1fr 1fr 150px;
						gap: 20px;
						align-items: center;
						transition: background-color 0.2s ease;
					}

					.fm-database-row:hover {
						background: #fafbfc;
					}

					.fm-database-row:last-child {
						border-bottom: none;
					}

					.fm-status-icon {
						width: 40px;
						height: 40px;
						border-radius: 50%;
						display: flex;
						align-items: center;
						justify-content: center;
						font-size: 18px;
					}

					.fm-status-icon.connected {
						background: #dcfce7;
						color: var(--fm-success);
					}

					.fm-status-icon.disconnected {
						background: #fef2f2;
						color: var(--fm-danger);
					}

					.fm-database-name {
						font-weight: 600;
						color: var(--fm-dark);
					}

					.fm-database-description {
						color: var(--fm-secondary);
						font-size: 14px;
					}

					.fm-database-actions {
						display: flex;
						gap: 8px;
					}

					.fm-btn {
						padding: 8px 16px;
						border-radius: 6px;
						font-size: 13px;
						font-weight: 500;
						text-decoration: none;
						display: inline-flex;
						align-items: center;
						justify-content: center;
						gap: 6px;
						transition: all 0.2s ease;
						border: none;
						cursor: pointer;
					}

					.fm-btn-primary {
						background: var(--fm-primary);
						color: white;
					}

					.fm-btn-primary:hover {
						background: var(--fm-primary-hover);
						color: white;
					}

					.fm-btn-outline {
						background: transparent;
						border: 1px solid var(--fm-border);
						color: var(--fm-secondary);
					}

					.fm-btn-outline:hover {
						background: var(--fm-light);
						color: var(--fm-dark);
					}

					.fm-btn-success {
						background: var(--fm-success);
						color: white;
						border: 1px solid var(--fm-success);
					}

					.fm-btn-success:hover {
						background: #047857;
						border-color: #047857;
						color: white;
					}

					.fm-btn-danger {
						background: transparent;
						color: var(--fm-danger);
						border: 1px solid var(--fm-danger);
					}

					.fm-btn-danger:hover {
						background: var(--fm-danger);
						color: white;
					}

					.fm-btn-lg {
						padding: 12px 24px;
						font-size: 16px;
						font-weight: 600;
					}

					.fm-toggle-switch {
						position: relative;
						width: 50px;
						height: 26px;
					}

					.fm-toggle-input {
						opacity: 0;
						width: 0;
						height: 0;
					}

					.fm-toggle-slider {
						position: absolute;
						cursor: pointer;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						background-color: #cbd5e1;
						transition: 0.3s;
						border-radius: 26px;
					}

					.fm-toggle-slider:before {
						position: absolute;
						content: "";
						height: 20px;
						width: 20px;
						left: 3px;
						bottom: 3px;
						background-color: white;
						transition: 0.3s;
						border-radius: 50%;
						box-shadow: 0 2px 4px rgba(0,0,0,0.2);
					}

					.fm-toggle-input:checked + .fm-toggle-slider {
						background-color: var(--fm-primary);
					}

					.fm-toggle-input:checked + .fm-toggle-slider:before {
						transform: translateX(24px);
					}

					.fm-integrations-grid {
						display: grid;
						grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
						gap: 16px;
						margin-top: 20px;
					}

					.fm-integration-card {
						background: white;
						border: 2px solid var(--fm-border);
						border-radius: 12px;
						padding: 14px 16px;
						display: flex;
						align-items: center;
						justify-content: space-between;
						transition: all 0.2s ease;
					}

					.fm-integration-card:hover {
						border-color: var(--fm-primary);
						box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
					}

					.fm-integration-info {
						display: flex;
						align-items: center;
						gap: 10px;
						flex: 1;
					}

					.fm-integration-icon {
						width: 24px !important;
						height: 24px !important;
						border-radius: 4px;
						flex-shrink: 0;
					}

					.fm-integration-name {
						font-weight: 600;
						color: var(--fm-dark);
						margin: 0;
						font-size: 13px;
						line-height: 1.3;
					}

					.fm-connect-db-header {
						display: flex;
						align-items: center;
						justify-content: space-between;
						margin-bottom: 20px;
					}

					.fm-save-actions {
						position: sticky;
						bottom: 20px;
						background: white;
						padding: 20px 30px;
						border-radius: 12px;
						box-shadow: var(--fm-shadow-lg);
						border: 1px solid var(--fm-border);
						display: flex;
						gap: 12px;
						justify-content: flex-end;
						margin-top: 30px;
					}

					.fm-checkbox-card {
						background: white;
						border: 1px solid var(--fm-border);
						border-radius: 8px;
						padding: 16px;
						display: flex;
						align-items: center;
						gap: 12px;
						margin-bottom: 16px;
						transition: all 0.2s ease;
					}

					.fm-checkbox-card:hover {
						border-color: var(--fm-primary);
						background: #f8faff;
					}

					.fm-checkbox {
						width: 18px;
						height: 18px;
						accent-color: var(--fm-primary);
					}

					.fm-checkbox-label {
						font-weight: 500;
						color: var(--fm-dark);
						margin: 0;
						cursor: pointer;
					}

					.fm-icon {
						font-size: 20px;
						color: var(--fm-primary);
					}

					@media (max-width: 768px) {						
						.fm-section-content {
							padding: 20px;
						}
						
						.fm-database-header,
						.fm-database-row {
							grid-template-columns: 1fr;
							gap: 10px;
							text-align: center;
						}
						
						.fm-integrations-grid {
							grid-template-columns: 1fr;
						}
					}
				</style>

				<div class="fm-settings-container w-100">
					<div class="fm-settings-header">
						<h3 class="fm-settings-title">
							<i class="dashicons dashicons-admin-settings fm-icon" style="color: var(--fm-primary);"></i>
							<?php echo esc_attr__( 'FlowMattic Settings', 'flowmattic' ); ?>
						</h3>
						<p class="fm-settings-subtitle">
							<?php esc_html_e( 'Configure your FlowMattic installation and customize workflow automation settings', 'flowmattic' ); ?>
						</p>
					</div>

					<form class="flowmattic-settings-form">
						<!-- Database Management Section -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-database fm-icon"></i>
									<?php esc_html_e( 'Database Management', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Manage custom database connections for advanced workflow operations', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-connect-db-header">
									<div>
										<h5 style="margin: 0; color: var(--fm-dark);"><?php esc_html_e( 'Connected Databases', 'flowmattic' ); ?></h5>
										<p style="margin: 5px 0 0 0; color: var(--fm-secondary); font-size: 14px;"><?php esc_html_e( 'Manage database connections for workflow data operations', 'flowmattic' ); ?></p>
									</div>
									<button type="button" class="fm-btn fm-btn-success add-new-database">
										<i class="dashicons dashicons-plus-alt2"></i>
										<?php esc_html_e( 'Connect Database', 'flowmattic' ); ?>
									</button>
								</div>

								<div class="fm-database-table">
									<div class="fm-database-header">
										<div><?php esc_html_e( 'Status', 'flowmattic' ); ?></div>
										<div><?php esc_html_e( 'Name', 'flowmattic' ); ?></div>
										<div><?php esc_html_e( 'Description', 'flowmattic' ); ?></div>
										<div><?php esc_html_e( 'Actions', 'flowmattic' ); ?></div>
									</div>
									
									<div class="fm-database-row">
										<div class="fm-status-icon connected">
											<i class="dashicons dashicons-database"></i>
										</div>
										<div class="fm-database-name">Local</div>
										<div class="fm-database-description">Current site database</div>
										<div class="fm-database-actions">
											<button type="button" class="fm-btn fm-btn-outline" disabled><?php esc_html_e( 'Edit', 'flowmattic' ); ?></button>
											<button type="button" class="fm-btn fm-btn-danger" disabled><?php esc_html_e( 'Delete', 'flowmattic' ); ?></button>
										</div>
									</div>

									<?php
									$db_connections = wp_flowmattic()->database_connections_db->get_all();
									if ( ! empty( $db_connections ) ) {
										foreach ( $db_connections as $db_connection ) {
											$db_settings = maybe_unserialize( $db_connection->connection_settings );
											$is_database_connected = wp_flowmattic()->tables->is_database_connection_active( $db_connection->id );
											$status_class = $is_database_connected ? 'connected' : 'disconnected';
											?>
											<div class="fm-database-row">
												<div class="fm-status-icon <?php echo esc_attr( $status_class ); ?>">
													<i class="dashicons dashicons-database"></i>
												</div>
												<div class="fm-database-name"><?php echo esc_html( $db_connection->connection_name ); ?></div>
												<div class="fm-database-description"><?php echo esc_html( $db_settings['description'] ); ?></div>
												<div class="fm-database-actions">
													<button type="button" class="fm-btn fm-btn-outline database-edit" data-id="<?php echo esc_attr( $db_connection->id ); ?>"><?php esc_html_e( 'Edit', 'flowmattic' ); ?></button>
													<button type="button" class="fm-btn fm-btn-danger database-connection-delete" data-id="<?php echo esc_attr( $db_connection->id ); ?>"><?php esc_html_e( 'Delete', 'flowmattic' ); ?></button>
												</div>
											</div>
											<?php
										}
									}
									?>
								</div>

								<div class="fm-checkbox-card">
									<input type="checkbox" class="fm-checkbox" id="fetch_existing_tables" name="fetch_existing_tables" value="yes" <?php echo ( isset( $settings['fetch_existing_tables'] ) ) ? checked( $settings['fetch_existing_tables'], 'yes', false ) : ''; ?>>
									<label for="fetch_existing_tables" class="fm-checkbox-label">
										<?php echo esc_attr__( 'Fetch existing tables from connected databases for editing', 'flowmattic' ); ?>
									</label>
								</div>
							</div>
						</div>

						<!-- General Settings Section -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-admin-generic fm-icon"></i>
									<?php esc_html_e( 'General Settings', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Configure basic FlowMattic functionality and interface options', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="admin_bar_menu" class="fm-form-label"><?php esc_html_e( 'Admin Bar Menu', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="admin_bar_menu" id="admin_bar_menu">
											<option value="yes" <?php echo ( ( isset( $settings['admin_bar_menu'] ) && 'yes' === $settings['admin_bar_menu'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Enabled', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( isset( $settings['admin_bar_menu'] ) && 'no' === $settings['admin_bar_menu'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Disabled', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Show or hide the FlowMattic menu in the WordPress admin bar', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="headers_capture" class="fm-form-label"><?php esc_html_e( 'Headers Capture', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="headers_capture" id="headers_capture">
											<option value="yes" <?php echo ( ( isset( $settings['headers_capture'] ) && 'yes' === $settings['headers_capture'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Enabled', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( isset( $settings['headers_capture'] ) && 'no' === $settings['headers_capture'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Disabled', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Capture HTTP headers in webhook responses for debugging and processing', 'flowmattic' ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<!-- Workflow Builder Settings -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-networking fm-icon"></i>
									<?php esc_html_e( 'Workflow Builder Settings', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Customize the workflow creation and editing experience', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="workflow_builder" class="fm-form-label"><?php esc_html_e( 'Default Workflow Builder', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="workflow_builder" id="workflow_builder">
											<option value="visual" <?php echo ( ( isset( $settings['workflow_builder'] ) && 'visual' === $settings['workflow_builder'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Visual Builder', 'flowmattic' ); ?></option>
											<option value="static" <?php echo ( ( isset( $settings['workflow_builder'] ) && 'static' === $settings['workflow_builder'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Static Builder', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Choose your preferred workflow builder interface for new workflows', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="default_folder" class="fm-form-label"><?php esc_html_e( 'Default Folder', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="default_folder" id="default_folder">
											<option value="all" <?php echo ( ( isset( $settings['default_folder'] ) && 'all' === $settings['default_folder'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'All Workflows', 'flowmattic' ); ?></option>
											<?php
											$all_workflows = wp_flowmattic()->workflows_db->get_all();
											$workflow_folders = array();
											if ( ! empty( $all_workflows ) ) {
												foreach ( $all_workflows as $workflow ) {
													$workflow_settings = json_decode( $workflow->workflow_settings );
													if ( isset( $workflow_settings->folder ) ) {
														$workflow_folders[ $workflow_settings->folder ] = true;
													}
												}
											}
											if ( ! empty( $workflow_folders ) ) {
												ksort( $workflow_folders );
												foreach ( array_keys( $workflow_folders ) as $folder_name ) {
													$selected = isset( $settings['default_folder'] ) && $settings['default_folder'] === $folder_name ? 'selected' : '';
													echo '<option value="' . esc_attr( $folder_name ) . '" ' . $selected . '>' . esc_html( $folder_name ) . '</option>';
												}
											}
											?>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Select the default folder view in the workflow builder interface', 'flowmattic' ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<!-- SMTP Configuration -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-email-alt fm-icon"></i>
									<?php esc_html_e( 'SMTP Configuration', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Configure SMTP settings for FlowMattic email module functionality', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="host" class="fm-form-label"><?php esc_html_e( 'SMTP Server', 'flowmattic' ); ?></label>
										<input type="text" class="fm-form-control" id="host" name="host" value="<?php echo ( isset( $settings['host'] ) ? esc_attr( $settings['host'] ) : '' ); ?>" placeholder="smtp.gmail.com">
										<div class="fm-form-help"><?php esc_html_e( 'Enter your SMTP server hostname (e.g., smtp.gmail.com)', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="port" class="fm-form-label"><?php esc_html_e( 'Port Number', 'flowmattic' ); ?></label>
										<input type="number" class="fm-form-control" id="port" name="port" value="<?php echo ( isset( $settings['port'] ) ? esc_attr( $settings['port'] ) : '' ); ?>" placeholder="587">
										<div class="fm-form-help"><?php esc_html_e( 'Common ports: 587 (TLS), 465 (SSL), 25 (unsecured)', 'flowmattic' ); ?></div>
									</div>
								</div>
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="username" class="fm-form-label"><?php esc_html_e( 'Username', 'flowmattic' ); ?></label>
										<input type="text" class="fm-form-control" id="username" name="username" value="<?php echo ( isset( $settings['username'] ) ? esc_attr( $settings['username'] ) : '' ); ?>" autocomplete="username">
										<div class="fm-form-help"><?php esc_html_e( 'Your SMTP authentication username', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="password" class="fm-form-label"><?php esc_html_e( 'Password', 'flowmattic' ); ?></label>
										<?php $password_value = isset( $settings['password'] ) ? $settings['password'] : ''; ?>
										<input type="password" class="fm-form-control" id="password" name="password" value="<?php echo esc_attr( $password_value ); ?>" autocomplete="current-password">
										<div class="fm-form-help"><?php esc_html_e( 'Your SMTP authentication password or app-specific password', 'flowmattic' ); ?></div>
									</div>
								</div>
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="smtpsecure" class="fm-form-label"><?php esc_html_e( 'Encryption Type', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="smtpsecure" id="smtpsecure">
											<option value="TLS" <?php echo ( ( isset( $settings['smtpsecure'] ) && 'TLS' === $settings['smtpsecure'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'TLS (Recommended)', 'flowmattic' ); ?></option>
											<option value="SSL" <?php echo ( ( isset( $settings['smtpsecure'] ) && 'SSL' === $settings['smtpsecure'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'SSL', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'TLS is recommended for modern SMTP servers', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<!-- Empty column for alignment -->
									</div>
								</div>
							</div>
						</div>

						<!-- Notifications Section -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-bell fm-icon"></i>
									<?php esc_html_e( 'Notification Settings', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Configure email notifications for workflow failures and authentication issues', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<h5 style="color: var(--fm-dark); margin: 0 0 16px 0; font-size: 16px; font-weight: 600;"><?php esc_html_e( 'Failed Task Notifications', 'flowmattic' ); ?></h5>
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="enable_notifications" class="fm-form-label"><?php esc_html_e( 'Enable Notifications', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="enable_notifications" id="enable_notifications">
											<option value="yes" <?php echo ( ( isset( $settings['enable_notifications'] ) && 'yes' === $settings['enable_notifications'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Enabled', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( isset( $settings['enable_notifications'] ) && 'no' === $settings['enable_notifications'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Disabled', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Receive notifications when workflows fail to execute', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="notification_email" class="fm-form-label"><?php esc_html_e( 'Notification Email', 'flowmattic' ); ?></label>
										<input type="email" class="fm-form-control" id="notification_email" name="notification_email" value="<?php echo ( isset( $settings['notification_email'] ) ? esc_attr( $settings['notification_email'] ) : '' ); ?>">
										<div class="fm-form-help"><?php esc_html_e( 'Email address to receive failure notifications', 'flowmattic' ); ?></div>
									</div>
								</div>

								<hr style="border: none; border-top: 1px solid var(--fm-border); margin: 24px 0;">

								<h5 style="color: var(--fm-dark); margin: 0 0 16px 0; font-size: 16px; font-weight: 600;"><?php esc_html_e( 'Authentication Expiry Notifications', 'flowmattic' ); ?></h5>
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="enable_notifications_connect" class="fm-form-label"><?php esc_html_e( 'Enable Notifications', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="enable_notifications_connect" id="enable_notifications_connect">
											<option value="yes" <?php echo ( ( isset( $settings['enable_notifications_connect'] ) && 'yes' === $settings['enable_notifications_connect'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Enabled', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( isset( $settings['enable_notifications_connect'] ) && 'no' === $settings['enable_notifications_connect'] ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Disabled', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Get notified when app connections need re-authentication', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="notification_email_connect" class="fm-form-label"><?php esc_html_e( 'Notification Email', 'flowmattic' ); ?></label>
										<input type="email" class="fm-form-control" id="notification_email_connect" name="notification_email_connect" value="<?php echo ( isset( $settings['notification_email_connect'] ) ? esc_attr( $settings['notification_email_connect'] ) : '' ); ?>">
										<div class="fm-form-help"><?php esc_html_e( 'Email address for authentication expiry alerts', 'flowmattic' ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<!-- Advanced Settings -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-admin-tools fm-icon"></i>
									<?php esc_html_e( 'Advanced Settings', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Advanced configuration options and system maintenance settings', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="task_clean_interval" class="fm-form-label"><?php esc_html_e( 'Task History Cleanup (Days)', 'flowmattic' ); ?></label>
										<input type="number" class="fm-form-control" id="task_clean_interval" name="task_clean_interval" value="<?php echo ( isset( $settings['task_clean_interval'] ) ? esc_attr( $settings['task_clean_interval'] ) : '90' ); ?>" min="1" max="365">
										<div class="fm-form-help"><?php esc_html_e( 'Automatically delete task history older than specified days to keep database clean', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="integration_page_access" class="fm-form-label"><?php esc_html_e( 'Integration Page Access', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="integration_page_access" id="integration_page_access">
											<option value="yes" <?php echo ( ( 'yes' === $integrations_access ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Allow Access', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( 'no' === $integrations_access ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Restrict Access', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Control access to integrations page for workflow managers', 'flowmattic' ); ?></div>
									</div>
								</div>
								<div class="fm-row">
									<div class="fm-form-group">
										<label for="delete_app_access" class="fm-form-label"><?php esc_html_e( 'Integration Deletion Rights', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="delete_app_access" id="delete_app_access">
											<option value="yes" <?php echo ( ( 'yes' === $delete_app_access ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Allow Deletion', 'flowmattic' ); ?></option>
											<option value="no" <?php echo ( ( 'no' === $delete_app_access ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Prevent Deletion', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Allow workflow managers to uninstall integrations', 'flowmattic' ); ?></div>
									</div>
									<div class="fm-form-group">
										<label for="webhook_url_base" class="fm-form-label"><?php esc_html_e( 'Webhook URL Structure', 'flowmattic' ); ?></label>
										<select class="fm-form-control fm-form-select" name="webhook_url_base" id="webhook_url_base">
											<option value="regular" <?php echo ( ( 'regular' === $webhook_url_base ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Permalink Based', 'flowmattic' ); ?></option>
											<option value="rest_api" <?php echo ( ( 'rest_api' === $webhook_url_base ) ? 'selected' : '' ); ?>><?php esc_html_e( 'REST API Based', 'flowmattic' ); ?></option>
										</select>
										<div class="fm-form-help"><?php esc_html_e( 'Use REST API structure if experiencing permalink-based webhook issues', 'flowmattic' ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<!-- Integration Management -->
						<div class="fm-section-card">
							<div class="fm-section-header">
								<h4 class="fm-section-title">
									<i class="dashicons dashicons-admin-plugins fm-icon"></i>
									<?php esc_html_e( 'Integration Management', 'flowmattic' ); ?>
								</h4>
								<p class="fm-section-description">
									<?php esc_html_e( 'Enable or disable core integrations to customize your workflow editor. Default enabled. Turn on only if you want to disable any core integration.', 'flowmattic' ); ?>
								</p>
							</div>
							<div class="fm-section-content">
								<div class="fm-integrations-grid">
									<?php
									$flowmattic_apps = wp_flowmattic()->apps;
									$installed_applications = $flowmattic_apps->get_all_applications();
									foreach ( $installed_applications as $app => $app_settings ) {
										if ( strtolower( 'WordPress' ) === $app ) {
											$app_settings['base'] = 'core';
										}
										if ( ! isset( $app_settings['base'] ) ) {
											continue;
										}
										$checked = ( isset( $settings[ 'disable-app-' . $app ] ) ) ? checked( $settings[ 'disable-app-' . $app ], $app, false ) : '';
										?>
										<div class="fm-integration-card">
											<div class="fm-integration-info">
												<img src="<?php echo esc_url( $app_settings['icon'] ); ?>" class="fm-integration-icon" alt="<?php echo esc_attr( $app_settings['name'] ); ?>">
												<h6 class="fm-integration-name"><?php echo esc_html( str_replace( 'by FlowMattic', '', $app_settings['name'] ) ); ?></h6>
											</div>
											<label class="fm-toggle-switch">
												<input type="checkbox" class="fm-toggle-input" name="disable-app-<?php echo esc_attr( $app ); ?>" value="<?php echo esc_attr( $app ); ?>" <?php echo $checked; ?>>
												<span class="fm-toggle-slider"></span>
											</label>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>

						<!-- Save Actions -->
						<div class="fm-save-actions">
							<button type="button" class="fm-btn fm-btn-primary fm-btn-lg flowmattic-save-settings">
								<i class="dashicons dashicons-saved"></i>
								<?php esc_html_e( 'Save All Settings', 'flowmattic' ); ?>
							</button>
							<?php
							$license_key = get_option( 'flowmattic_license_key', '' );
							$default_workflow_builder = $settings['workflow_builder'] ?? 'visual';
							$new_workflow_url = admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=new&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );
							if ( 'visual' === $default_workflow_builder ) {
								$new_workflow_url = admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=new&builder-version=v2&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) );
							}
							$button_class = ( '' === $license_key ) ? 'needs-registration' : '';
							$button_disabled = ( '' === $license_key ) ? 'disabled' : '';
							?>
							<a href="<?php echo esc_url( $new_workflow_url ); ?>" class="fm-btn fm-btn-success fm-btn-lg <?php echo esc_attr( $button_class ); ?>" <?php echo $button_disabled; ?>>
								<i class="dashicons dashicons-plus-alt2"></i>
								<?php esc_html_e( 'New Workflow', 'flowmattic' ); ?>
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Database Modal (keeping original modals with improved styling) -->
<div class="modal fade" id="newDatabaseModal" tabindex="-1" aria-labelledby="newDatabaseModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
		<div class="modal-content" style="border-radius: 16px; border: none; box-shadow: var(--fm-shadow-lg);">
			<div class="modal-header" style="background: #f8fafc; color: var(--fm-dark); border-radius: 16px 16px 0 0; border-bottom: 1px solid var(--fm-border);">
				<h5 class="modal-title" id="newDatabaseModalLabel" style="font-weight: 600;"><?php esc_html_e( 'Connect New Database', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4">
				<form class="flowmattic-new-database-form">
					<div class="mb-3">
						<label for="database-label" class="fm-form-label"><?php esc_html_e( 'Connection Label', 'flowmattic' ); ?></label>
						<input type="text" class="fm-form-control" id="database-label" name="database-label" required>
					</div>
					<div class="mb-3">
						<label for="database-description" class="fm-form-label"><?php esc_html_e( 'Description', 'flowmattic' ); ?></label>
						<input type="text" class="fm-form-control" id="database-description" name="database-description" required>
					</div>
					<hr style="border: none; border-top: 1px solid var(--fm-border); margin: 24px 0;">
					<div class="mb-3">
						<label for="database-name" class="fm-form-label"><?php esc_html_e( 'Database Name', 'flowmattic' ); ?></label>
						<input type="text" class="fm-form-control" id="database-name" name="database-name" required>
					</div>
					<div class="mb-3">
						<label for="database-host" class="fm-form-label"><?php esc_html_e( 'Database Host', 'flowmattic' ); ?></label>
						<input type="text" class="fm-form-control" id="database-host" name="database-host" required>
					</div>
					<div class="mb-3">
						<label for="database-user" class="fm-form-label"><?php esc_html_e( 'Database User', 'flowmattic' ); ?></label>
						<input type="search" class="fm-form-control" id="database-user" name="database-user" required autocomplete="off">
					</div>
					<div class="mb-3">
						<label for="database-password" class="fm-form-label"><?php esc_html_e( 'Database Password', 'flowmattic' ); ?></label>
						<input type="password" class="fm-form-control" id="database-password" name="database-password" required autocomplete="off">
					</div>
					<div class="test-connection" style="margin-bottom: 20px;">
						<button type="button" class="fm-btn fm-btn-outline test-database-connection"><?php esc_html_e( 'Test Connection', 'flowmattic' ); ?></button>
						<span class="spinner-border spinner-border-sm text-primary d-none test-connection-spinner ms-2" role="status"></span>
						<span class="test-connection-message ms-2"></span>
					</div>
				</form>
			</div>
			<div class="modal-footer" style="border-top: 1px solid var(--fm-border);">
				<input type="hidden" name="database_id" value="" id="cur_database_id">
				<button type="submit" class="fm-btn fm-btn-primary btn-add-database disabled"><?php esc_html_e( 'Add Database', 'flowmattic' ); ?></button>
				<button type="button" class="fm-btn fm-btn-outline" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="deleteDBConModal" tabindex="-1" aria-labelledby="deleteDBConModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content" style="border-radius: 16px; border: none; box-shadow: var(--fm-shadow-lg);">
			<div class="modal-header" style="background: #fef2f2; color: var(--fm-danger); border-radius: 16px 16px 0 0; border-bottom: 1px solid var(--fm-border);">
				<h5 class="modal-title" id="deleteDBConModalLabel" style="font-weight: 600;"><?php echo esc_html__( 'Delete Database Connection?', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal"></button>
			</div>
			<div class="modal-body" style="padding: 30px;">
				<div style="color: var(--fm-secondary); line-height: 1.6;">
					<p><?php echo esc_html__( 'Are you sure you want to delete this database connection? This action cannot be undone.', 'flowmattic' ); ?></p>
					<p><?php echo esc_html__( 'Note: This will only remove the connection settings, not the actual database tables.', 'flowmattic' ); ?></p>
				</div>
			</div>
			<div class="modal-footer" style="border-top: 1px solid var(--fm-border);">
				<span class="spinner-border spinner-border-sm text-primary d-none delete-table-spinner ms-2"></span>
				<button type="button" class="fm-btn fm-btn-danger fm-delete-db-confirm"><?php echo esc_html__( 'Delete Connection', 'flowmattic' ); ?></button>
				<button type="button" class="fm-btn fm-btn-outline" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
// Keep original JavaScript functionality with updated selectors
jQuery( document ).ready( function( $ ) {
	// Add new database
	jQuery( '.add-new-database' ).on( 'click', function() {
		var addBtn = jQuery( '.btn-add-database' );
		addBtn.addClass( 'disabled' );
		jQuery( '#cur_database_id' ).val('');
		jQuery( '#newDatabaseModalLabel' ).html( '<?php esc_html_e( 'Connect New Database', 'flowmattic' ); ?>' );
		jQuery( '.flowmattic-new-database-form' ).trigger( 'reset' );
		jQuery( '.btn-add-database' ).html( '<?php esc_html_e( 'Add Database', 'flowmattic' ); ?>' );
		jQuery( '#newDatabaseModal' ).modal( 'show' );
	} );

	// Test database connection
	jQuery( '.test-database-connection' ).on( 'click', function() {
		var thisBtn = jQuery( this ),
			addBtn = jQuery( '.btn-add-database' ),
			spinner = jQuery( '.test-connection-spinner' ),
			data = {
				'action': 'flowmattic_test_database_connection',
				'database_name': jQuery( '#database-name' ).val(),
				'database_host': jQuery( '#database-host' ).val(),
				'database_user': jQuery( '#database-user' ).val(),
				'database_password': jQuery( '#database-password' ).val(),
				'security': '<?php echo wp_create_nonce( 'flowmattic_test_db_connection' ); ?>'
			};

		if ( '' === data.database_name || '' === data.database_host || '' === data.database_user || '' === data.database_password ) {
			jQuery( '.test-connection-message' ).addClass( 'text-danger' );
			jQuery( '.test-connection-message' ).html( '<?php esc_html_e( 'Please fill all the database fields.', 'flowmattic' ); ?>' );
			return;
		}

		spinner.removeClass( 'd-none' );
		thisBtn.addClass( 'disabled' );

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				jQuery( '.test-connection-message' ).html( '' );
				jQuery( '.test-connection-spinner' ).removeClass( 'd-none' );
			},
			success: function( response ) {
				jQuery( '.test-connection-spinner' ).addClass( 'd-none' );
				if ( ! response.success ) {
					jQuery( '.test-connection-message' ).addClass( 'text-danger' );
					jQuery( '.test-connection-message' ).html( '<?php esc_html_e( 'Database connection failed.', 'flowmattic' ); ?>' );
				} else {
					jQuery( '.test-connection-message' ).removeClass( 'text-danger' ).addClass( 'text-success' );
					jQuery( '.test-connection-message' ).html( response.data.message );
					addBtn.removeClass( 'disabled' );
				}
				setTimeout( function() {
					thisBtn.removeClass( 'disabled' );
				}, 2000 );
			}
		} );
	} );

	// Add/Update database form submission
	jQuery( '.flowmattic-new-database-form' ).on( 'submit', function( e ) {
		e.preventDefault();
		var addBtn = jQuery( '.btn-add-database' ),
			action = jQuery( '#cur_database_id' ).val() ? 'flowmattic_update_database' : 'flowmattic_add_new_database';

		var data = {
			'action': action,
			'database_id': jQuery( '#cur_database_id' ).val(),
			'database_label': jQuery( '#database-label' ).val(),
			'database_description': jQuery( '#database-description' ).val(),
			'database_name': jQuery( '#database-name' ).val(),
			'database_host': jQuery( '#database-host' ).val(),
			'database_user': jQuery( '#database-user' ).val(),
			'database_password': jQuery( '#database-password' ).val(),
			'security': '<?php echo wp_create_nonce( 'flowmattic-add-new-database' ); ?>'
		};

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				jQuery( '.test-connection-message' ).html( '' );
				jQuery( '.test-connection-spinner' ).removeClass( 'd-none' );
			},
			success: function( response ) {
				jQuery( '.test-connection-spinner' ).addClass( 'd-none' );
				if ( response.success ) {
					addBtn.addClass( 'disabled' );
					jQuery( '.test-connection-message' ).removeClass( 'text-danger' ).addClass( 'text-success' ).html( response.data.message );
					setTimeout( function() {
						location.reload();
					}, 2000 );
				} else {
					jQuery( '.test-connection-message' ).addClass( 'text-danger' ).html( response.data.message );
				}
			}
		} );
	} );

	// Edit database
	jQuery( '.database-edit' ).on( 'click', function() {
		var database_id = jQuery( this ).data( 'id' ),
			data = {
				'action': 'flowmattic_get_database_connection',
				'database_id': database_id,
				'security': '<?php echo wp_create_nonce( 'flowmattic-get-database-connection' ); ?>'
			},
			addBtn = jQuery( '.btn-add-database' );

		addBtn.addClass( 'disabled' );

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function( response ) {
				if ( response.success ) {
					jQuery( '#newDatabaseModalLabel' ).html( '<?php esc_html_e( 'Edit Database Connection: ', 'flowmattic' ); ?>' + response.data.connection_name );
					jQuery( '#cur_database_id' ).val( database_id );
					jQuery( '#database-label' ).val( response.data.connection_name );
					jQuery( '#database-description' ).val( response.data.connection_settings.description );
					
					// Mask sensitive data
					var maskData = function(str) {
						if (str.length > 3) {
							return str.substring(0, 3) + str.substring(3).replace(/./g, '*');
						}
						return str.replace(/./g, '*');
					};
					
					jQuery( '#database-name' ).attr( 'placeholder', maskData(response.data.database_connection.name) );
					jQuery( '#database-host' ).attr( 'placeholder', maskData(response.data.database_connection.host) );
					jQuery( '#database-user' ).attr( 'placeholder', maskData(response.data.database_connection.user) );
					jQuery( '#database-password' ).attr( 'placeholder', response.data.database_connection.password.replace( /./g, '*' ) );
					
					jQuery( '.btn-add-database' ).html( '<?php esc_html_e( 'Update Database', 'flowmattic' ); ?>' );
					jQuery( '#newDatabaseModal' ).modal( 'show' );
				}
			}
		} );
	} );

	var database_id = '';
	// Delete database connection
	jQuery( '.database-connection-delete' ).on( 'click', function() {
		database_id = jQuery( this ).data( 'id' );
		jQuery( '#deleteDBConModal' ).modal( 'show' );
	} );

	// Confirm delete database connection
	jQuery( '.fm-delete-db-confirm' ).on( 'click', function() {
		var data = {
			'action': 'flowmattic_delete_database',
			'database_id': database_id,
			'security': '<?php echo wp_create_nonce( 'flowmattic_delete_database' ); ?>'
		};

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				jQuery( '.delete-table-spinner' ).removeClass( 'd-none' );
			},
			success: function( response ) {
				jQuery( '.delete-table-spinner' ).addClass( 'd-none' );
				if ( response.success ) {
					location.reload();
				}
			}
		} );
	} );
} );
</script>
<?php FlowMattic_Admin::footer(); ?>