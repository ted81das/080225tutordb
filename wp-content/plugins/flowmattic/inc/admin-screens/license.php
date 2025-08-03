<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="fm-setting-header d-flex mt-4 justify-content-between">
						<h3 class="fm-setting-heading m-0 ms-2">
							<?php echo esc_attr__( 'FlowMattic License', 'flowmattic' ); ?>
						</h3>
					</div>
					<div class="navbar-text bg-light w-100 ps-3 py-3 ms-2 mt-2">
						<?php esc_html_e( 'Register your license key to receive automatic updates, unlock features and receive support.', 'flowmattic' ); ?>
					</div>
				</div>
				<div class="flowmattic-license-registration ms-2">
					<?php
					if ( current_user_can( 'manage_options' ) ) {
						$license = wp_flowmattic()->check_license();

						if ( $license ) {
							flowmattic_license_activated_form( $license );
						} else {
							flowmattic_license_form();
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php FlowMattic_Admin::footer(); ?>
