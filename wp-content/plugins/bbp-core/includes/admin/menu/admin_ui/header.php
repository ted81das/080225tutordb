<header class="easydocs-header-area">
	<div class="container-fluid">
		<div class="row alignment-center justify-content-between">
			<div class="navbar-left d-flex alignment-center">
				<div class="easydocs-logo-area">
					<a href="javascript:void(0);">
						<?php esc_html_e( 'Forums', 'bbp-core' ); ?>
					</a>
				</div>
				<a href="javascript:void(0)" type="button" id="bbpc-forum" class="easydocs-btn easydocs-btn-outline-blue easydocs-btn-sm easydocs-btn-round">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add Forum', 'bbp-core' ); ?>
				</a>
			</div>
			<form action="#" method="POST" class="easydocs-search-form">
				<div class="search-icon">
					<span class="dashicons dashicons-search"></span>
				</div>
				<input type="search" name="keyword" class="form-control" id="bbpc-search" placeholder="<?php esc_attr_e( 'Search on ...', 'bbp-core' ); ?>" onkeyup="fetch()"/>
			</form>
			<div class="navbar-right">
				<ul class="d-flex justify-content-end bbpc-right-nav">
					<li class="easydocs-settings">
						<div class="header-notify-icon bbpc-settings-icon">
							<a href="<?php echo admin_url( 'admin.php?page=bbp-core-settings' ); ?>">
								<img src="<?php echo BBPC_IMG; ?>/admin/admin-settings.svg" alt="<?php esc_html_e( 'Settings Icon', 'bbp-core' ); ?>">
							</a>
						</div>
					</li>
					<li>
						<div class="easydocs-settings">
						<?php if ( current_user_can( 'edit_posts' ) ) : ?>
							<div class="header-notify-icons">
								<select name="bbpc_classic_ui" id="bbpc_classic_ui">
									<option value="<?php echo admin_url( 'admin.php?page=bbp-core' ); ?>"><?php esc_html_e( 'Choose classic UI', 'bbp-core' ); ?></option>
									<option value="<?php echo admin_url( 'edit.php?post_type=forum' ); ?>"><?php esc_html_e( 'Forums', 'bbp-core' ); ?></option>
									<option value="<?php echo admin_url( 'edit.php?post_type=topic' ); ?>"><?php esc_html_e( 'Topics', 'bbp-core' ); ?></option>
									<option value="<?php echo admin_url( 'edit.php?post_type=reply' ); ?>"><?php esc_html_e( 'Replies', 'bbp-core' ); ?></option>
								</select>
							</div>
						<?php endif; ?>
						</div>
					</li>
					<?php
					if ( bbpc_is_premium() ) :
						do_action( 'bbpcorepro_notification' );
					else :
						?>
						<li class="easydocs-notification bbp-core-pro-notification" title="<?php esc_attr_e( 'Notifications', 'bbp-core' ); ?>">
							<div class="header-notify-icon">
								<img class="notify-icon" src="<?php echo BBPC_IMG; ?>/admin/notification.svg" alt="<?php esc_html_e( 'Notify Icon', 'bbp-core' ); ?>">
								<img class="settings-pro-icon" src="<?php echo BBPC_IMG; ?>/admin/pro-icon.png" alt="<?php esc_html_e( 'Pro Icon', 'bbp-core' ); ?>">
							</div>
						</li>
					<?php
					endif;
					?>
				</ul>
			</div>
		</div>
	</div>
</header>
