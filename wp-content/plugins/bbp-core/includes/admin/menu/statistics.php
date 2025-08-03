<?php
if ( ! class_exists( 'bbPress' ) ) {
	return;
}
?>
<div class="bbp-core-main">
	<div class="bbpc-heading">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'BBP Core', 'bbp-core' ); ?></h1>
		<p class="bbp-core-intro">
			<?php esc_html_e( 'Expand bbPress powered forums with useful features.', 'bbp-core' ); ?>
		</p>
	</div>

	<div class="bbpc-dashboard">
	<div class="bbpc-panel">
		<div class="bbpc-logo">
			<img src="<?php echo esc_url( BBPC_ASSETS . '/img/logo.svg' ); ?>" alt="<?php esc_attr_e( 'BBP Core Logo', 'bbp-core' ); ?>">
		</div>
		<ul class="bbpc-group-menu">
			<li><a href="admin.php?page=bbp-core-settings"><?php esc_html_e( 'Settings', 'bbp-core' ); ?></a></li>
		</ul>
	</div>

	<div class="bbpc-stats">
		<?php
		$stat = bbp_get_statistics();

		// TODO: Add another panel for user statistics.

		$forum_total     = $stat['forum_count_int'] ?? 0;
		$topic_total     = ( $stat['topic_count_int'] ?? 0 ) - ( $stat['topic_count_hidden_int'] ?? 0 );
		$reply_posts     = $stat['topic_tag_count_int'] ?? 0;
		$reply_total     = ( $stat['reply_count_int'] ?? 0 ) - ( $stat['reply_count_hidden'] ?? 0 );
		$topic_tag_count = $stat['topic_tag_count'] ?? 0;

		if ( current_user_can( 'edit_topic_tags' ) ) {
			$empty_topic_tag_count = $stat['empty_topic_tag_count_int'] ?? 0;
		}
		?>
		<div class="bbpc-stat-cards">
			<div class="bbpc-stat-card forum-stats">
				<div class="bbpc-card-heading">
					<h2><?php esc_html_e( 'User Stats', 'bbp-core' ); ?></h2>
				</div>

				<div class="bbpc-card-data">
					<ul class="forum-info">
						<li>
							<i class="dashicons dashicons-buddicons-forums"></i>
							<?php echo esc_html( $forum_total ) . esc_html__( ' Forums', 'bbp-core' ); ?> </i>
						</li>

						<li>
							<i class="dashicons dashicons-buddicons-topics"></i>
							<?php echo esc_html( $topic_total ) . esc_html__( ' Topics', 'bbp-core' ); ?> </i>
						</li>

						<li>
							<i class="dashicons dashicons-buddicons-replies"></i>
							<?php echo esc_html( $reply_total ) . esc_html__( ' Replies', 'bbp-core' ); ?> </i>
						</li>

						<li>
							<i class="dashicons dashicons-tag"></i>
							<?php echo esc_html( $topic_tag_count ) . esc_html__( ' Topic Tags', 'bbp-core' ); ?> </i>
						</li>

						<?php if ( isset( $empty_topic_tag_count ) ) : ?>
							<li>
								<i class="dashicons dashicons-tag"></i>
								<?php echo esc_html( $empty_topic_tag_count ) . esc_html__( ' Empty Topic Tags', 'bbp-core' ); ?> </i>
							</li>
						<?php endif; ?>

					</ul>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
