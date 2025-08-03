<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<?php
				// Enhanced server configuration checks
				$permalink_structure = get_option( 'permalink_structure' );
				$permalink_ok = ! empty( $permalink_structure ) ? true : false;

				$site_url    = get_option( 'siteurl' );
				$site_url_ok = ( strpos( $site_url, 'https://' ) === 0 ) ? true : false;

				$php_version    = phpversion();
				$php_version_ok = ( version_compare( $php_version, '7.4', '>' ) ) ? true : false;

				$memory_limit = ini_get( 'memory_limit' );
				$memory_limit_value = intval( $memory_limit );
				$memory_limit_ok = ( $memory_limit_value >= 256 ) ? true : false;

				$timeout_limit    = ini_get( 'max_execution_time' );
				$timeout_limit_ok = ( $timeout_limit == 0 || $timeout_limit >= 300 ) ? true : false;

				// Additional important checks for workflow automation
				$max_input_vars = ini_get( 'max_input_vars' );
				$max_input_vars_ok = ( $max_input_vars >= 3000 ) ? true : false;

				$upload_max_filesize = ini_get( 'upload_max_filesize' );
				$upload_max_filesize_value = intval( $upload_max_filesize );
				$upload_max_filesize_ok = ( $upload_max_filesize_value >= 32 ) ? true : false;

				$post_max_size = ini_get( 'post_max_size' );
				$post_max_size_value = intval( $post_max_size );
				$post_max_size_ok = ( $post_max_size_value >= 32 ) ? true : false;

				$allow_url_fopen = ini_get( 'allow_url_fopen' );
				$allow_url_fopen_ok = ( $allow_url_fopen == 1 ) ? true : false;

				$curl_enabled = extension_loaded( 'curl' );
				$json_enabled = extension_loaded( 'json' );
				$openssl_enabled = extension_loaded( 'openssl' );

				global $wp_version;
				$wp_version_ok = ( version_compare( $wp_version, '5.0', '>' ) ) ? true : false;

				// Calculate overall health score
				$checks = array(
					$permalink_ok, $site_url_ok, $php_version_ok, $memory_limit_ok, 
					$timeout_limit_ok, $max_input_vars_ok, $upload_max_filesize_ok, 
					$post_max_size_ok, $allow_url_fopen_ok, $curl_enabled, 
					$json_enabled, $openssl_enabled, $wp_version_ok
				);
				$total_checks = count( $checks );
				$passed_checks = count( array_filter( $checks ) );
				$health_percentage = round( ( $passed_checks / $total_checks ) * 100 );

				// Determine health status
				$health_status = 'excellent';
				$health_color = 'success';
				$health_message = 'All systems operational';
				
				if ( $health_percentage < 70 ) {
					$health_status = 'critical';
					$health_color = 'danger';
					$health_message = 'Critical issues detected';
				} elseif ( $health_percentage < 85 ) {
					$health_status = 'warning';
					$health_color = 'warning';
					$health_message = 'Some issues need attention';
				} elseif ( $health_percentage < 100 ) {
					$health_status = 'good';
					$health_color = 'info';
					$health_message = 'Minor optimizations recommended';
				}
				?>
				<div class="flowmattic-status-page">
					<div class="status-header">
						<h3 class="status-title">
							<i class="dashicons dashicons-admin-tools" style="color: #667eea;"></i>
							<?php esc_html_e( 'FlowMattic System Status', 'flowmattic' ); ?>
						</h3>
						<p class="status-subtitle">
							<?php esc_html_e( 'Monitor your server configuration and ensure optimal performance for workflow automation', 'flowmattic' ); ?>
						</p>
					</div>

					<div class="health-overview">
						<div class="health-score">
							<div class="health-circle">
								<span class="health-percentage"><?php echo $health_percentage; ?>%</span>
							</div>
							<div class="health-status-text text-<?php echo $health_color; ?>"><?php echo $health_message; ?></div>
							<small class="text-muted"><?php echo $passed_checks; ?> of <?php echo $total_checks; ?> checks passed</small>
						</div>
					</div>

					<div class="checks-grid">
						<!-- WordPress & Core Settings -->
						<div class="check-card">
							<div class="category-title">
								<i class="dashicons dashicons-wordpress" style="margin-right: 8px;"></i>
								WordPress Configuration
							</div>
							
							<div class="check-item">
								<div class="check-icon <?php echo $permalink_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $permalink_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Permalink Structure', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $permalink_structure ? $permalink_structure : 'Default'; ?></div>
								</div>
								<?php if ( ! $permalink_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Custom Required', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Custom Set', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $site_url_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $site_url_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Secure Site URL', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $site_url; ?></div>
								</div>
								<?php if ( ! $site_url_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'HTTPS Required', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Secure', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $wp_version_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $wp_version_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'WordPress Version', 'flowmattic' ); ?></div>
									<div class="check-detail">Version <?php echo $wp_version; ?></div>
								</div>
								<?php if ( ! $wp_version_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Update Required', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Up to Date', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<!-- PHP Configuration -->
						<div class="check-card">
							<div class="category-title">
								<i class="dashicons dashicons-admin-generic" style="margin-right: 8px;"></i>
								PHP Configuration
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $php_version_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $php_version_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'PHP Version', 'flowmattic' ); ?></div>
									<div class="check-detail">Version <?php echo $php_version; ?></div>
								</div>
								<?php if ( ! $php_version_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 7.4+', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Compatible', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $memory_limit_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $memory_limit_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Memory Limit', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $memory_limit; ?></div>
								</div>
								<?php if ( ! $memory_limit_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 256M', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Sufficient', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $timeout_limit_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $timeout_limit_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Execution Timeout', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $timeout_limit == 0 ? 'Unlimited' : $timeout_limit . 's'; ?></div>
								</div>
								<?php if ( ! $timeout_limit_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 300s', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Adequate', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $max_input_vars_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $max_input_vars_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Max Input Vars', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo number_format( $max_input_vars ); ?> variables</div>
								</div>
								<?php if ( ! $max_input_vars_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 3000', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Good', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<!-- File Upload & Processing -->
						<div class="check-card">
							<div class="category-title">
								<i class="dashicons dashicons-upload" style="margin-right: 8px;"></i>
								File Handling
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $upload_max_filesize_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $upload_max_filesize_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Upload Max Filesize', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $upload_max_filesize; ?></div>
								</div>
								<?php if ( ! $upload_max_filesize_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 32M', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Good', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $post_max_size_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $post_max_size_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Post Max Size', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $post_max_size; ?></div>
								</div>
								<?php if ( ! $post_max_size_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Min: 32M', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Good', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $allow_url_fopen_ok ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $allow_url_fopen_ok ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'Allow URL fopen', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $allow_url_fopen ? 'Enabled' : 'Disabled'; ?></div>
								</div>
								<?php if ( ! $allow_url_fopen_ok ) : ?>
									<span class="check-requirement danger"><?php esc_html_e( 'Required', 'flowmattic' ); ?></span>
								<?php else : ?>
									<span class="check-requirement success"><?php esc_html_e( 'Enabled', 'flowmattic' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<!-- Extensions & Security -->
						<div class="check-card">
							<div class="category-title">
								<i class="dashicons dashicons-shield" style="margin-right: 8px;"></i>
								Extensions & Security
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $curl_enabled ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $curl_enabled ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'cURL Extension', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $curl_enabled ? 'Available' : 'Not Available'; ?></div>
								</div>
								<span class="check-requirement <?php echo $curl_enabled ? 'success' : 'danger'; ?>">
									<?php echo $curl_enabled ? 'Loaded' : 'Required'; ?>
								</span>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $json_enabled ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $json_enabled ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'JSON Extension', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $json_enabled ? 'Available' : 'Not Available'; ?></div>
								</div>
								<span class="check-requirement <?php echo $json_enabled ? 'success' : 'danger'; ?>">
									<?php echo $json_enabled ? 'Loaded' : 'Required'; ?>
								</span>
							</div>

							<div class="check-item">
								<div class="check-icon <?php echo $openssl_enabled ? 'success' : 'danger'; ?>">
									<i class="dashicons dashicons-<?php echo $openssl_enabled ? 'yes-alt' : 'warning'; ?>"></i>
								</div>
								<div class="check-content">
									<div class="check-title"><?php esc_html_e( 'OpenSSL Extension', 'flowmattic' ); ?></div>
									<div class="check-detail"><?php echo $openssl_enabled ? 'Available' : 'Not Available'; ?></div>
								</div>
								<span class="check-requirement <?php echo $openssl_enabled ? 'success' : 'danger'; ?>">
									<?php echo $openssl_enabled ? 'Loaded' : 'Required'; ?>
								</span>
							</div>
						</div>
					</div>

					<?php if ( $health_percentage < 100 ) : ?>
					<div class="health-overview" style="background: #fff3cd; border: 1px solid #ffeaa7;">
						<h5 style="color: #856404; margin-bottom: 15px;">
							<i class="dashicons dashicons-info" style="margin-right: 8px;"></i>
							<?php esc_html_e( 'Recommendations', 'flowmattic' ); ?>
						</h5>
						<div style="color: #856404; line-height: 1.6;">
							<?php esc_html_e( 'Some server configurations need attention for optimal workflow performance. Contact your hosting provider to resolve any issues marked in red. These settings are crucial for:', 'flowmattic' ); ?>
							<ul style="margin: 10px 0; padding-left: 20px; list-style-type: disc;">
								<li><?php esc_html_e( 'Ensuring smooth execution of automated workflows', 'flowmattic' ); ?></li>
								<li><?php esc_html_e( 'Processing large data sets in workflows', 'flowmattic' ); ?></li>
								<li><?php esc_html_e( 'Handling API integrations and external connections', 'flowmattic' ); ?></li>
								<li><?php esc_html_e( 'Managing file uploads and data processing', 'flowmattic' ); ?></li>
								<li><?php esc_html_e( 'Ensuring secure communication with third-party services', 'flowmattic' ); ?></li>
							</ul>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	.flowmattic-status-page {
		border-radius: 20px;
		padding: 0;
		margin: 20px 0;
		overflow: hidden;
	}
	
	.status-header {
		backdrop-filter: blur(10px);
		padding: 0 30px 20px;
		border-bottom: 1px solid rgba(0,0,0,0.1);
	}
	
	.status-title {
		font-size: 28px;
		font-weight: 700;
		color: #2c3e50;
		margin-bottom: 10px;
		display: flex;
		align-items: center;
		gap: 15px;
	}
	
	.status-subtitle {
		color: #6c757d;
		font-size: 16px;
		margin: 0;
	}
	
	.health-overview {
		background: white;
		margin: 25px 15px;
		border-radius: 15px;
		padding: 25px;
		box-shadow: 0 8px 25px rgba(0,0,0,0.08);
		border: 1px solid rgba(0,0,0,0.05);
	}
	
	.health-score {
		text-align: center;
		margin-bottom: 20px;
	}
	
	.health-circle {
		width: 120px;
		height: 120px;
		border-radius: 50%;
		margin: 0 auto 15px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24px;
		font-weight: 700;
		color: white;
		position: relative;
		background: conic-gradient(var(--health-color) <?php echo $health_percentage; ?>%, #e9ecef 0%);
	}
	
	.health-circle::before {
		content: '';
		position: absolute;
		width: 90px;
		height: 90px;
		background: white;
		border-radius: 50%;
		z-index: 1;
	}
	
	.health-percentage {
		position: relative;
		z-index: 2;
		color: #2c3e50;
	}
	
	.health-status-text {
		font-size: 18px;
		font-weight: 600;
		margin-bottom: 5px;
	}
	
	.checks-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(350px, 2fr));
		gap: 20px;
		margin: 25px 15px;
	}
	
	.check-card {
		background: white;
		border-radius: 12px;
		padding: 20px;
		box-shadow: 0 4px 15px rgba(0,0,0,0.08);
		border: 1px solid rgba(0,0,0,0.05);
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}
	
	.check-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 25px rgba(0,0,0,0.12);
	}
	
	.check-item {
		display: flex;
		align-items: center;
		padding: 15px 0;
		border-bottom: 1px solid #f8f9fa;
	}
	
	.check-item:last-child {
		border-bottom: none;
	}
	
	.check-icon {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 15px;
		font-size: 18px;
	}
	
	.check-icon.success {
		background: linear-gradient(135deg, #28a745, #20c997);
		color: white;
	}
	
	.check-icon.danger {
		background: linear-gradient(135deg, #dc3545, #fd7e14);
		color: white;
	}
	
	.check-content {
		flex: 1;
	}
	
	.check-title {
		font-weight: 600;
		color: #2c3e50;
		margin-bottom: 2px;
	}
	
	.check-detail {
		font-size: 13px;
		color: #6c757d;
	}
	
	.check-requirement {
		font-size: 12px;
		padding: 4px 8px;
		border-radius: 12px;
		margin-left: auto;
	}
	
	.check-requirement.danger {
		background: #f8d7da;
		color: #721c24;
	}
	
	.check-requirement.success {
		background: #d4edda;
		color: #155724;
	}
	
	.category-title {
		font-size: 18px;
		font-weight: 600;
		color: #2c3e50;
		margin-bottom: 15px;
		padding-bottom: 8px;
		border-bottom: 2px solid #e9ecef;
	}
	
	:root {
		--health-color: <?php echo $health_color === 'success' ? '#28a745' : ($health_color === 'warning' ? '#ffc107' : ($health_color === 'info' ? '#17a2b8' : '#dc3545')); ?>;
	}
	
	@media (max-width: 768px) {
		.checks-grid {
			grid-template-columns: 1fr;
		}
		
		.status-header {
			padding: 20px;
		}
		
		.health-overview {
			margin: 15px;
			padding: 20px;
		}
	}
</style>
<?php FlowMattic_Admin::footer(); ?>