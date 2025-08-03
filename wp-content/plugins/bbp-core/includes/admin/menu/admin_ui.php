<?php
if ( ! class_exists( 'bbPress' ) ) {
	return;
}

$parent_forums = [];
$fcount        = wp_count_posts( bbp_get_forum_post_type() );
$forum_count   = (int) ( $fcount->publish + $fcount->hidden + $fcount->spam );
$bbpc_opt      = get_option( 'bbp_core_settings' );
?>
<div class="wrap">
<div class="body-dark">
	<?php
	if ( $forum_count > 0 ) :
		include __DIR__ . '/admin_ui/header.php';
		?>
		<main>
			<div class="easydocs-sidebar-menu">
				<div class="tab-container">
					<?php
					$forum_query = new WP_Query( [
						'post_type'      => bbp_get_forum_post_type(),
						'posts_per_page' => -1,
						'post_parent'    => 0,
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					] );
					$count = $forum_query->found_posts;
					// Left Sidebar Forums.
					include __DIR__ . '/admin_ui/forums.php';
					?>
					<div class="easydocs-tab-content">
						<?php
						$ids       = 0;
						$container = 1;
						if ( is_array( $parent_forums ) ) :
							foreach ( $parent_forums as $item ) :
								$ids ++;
								$container ++;
								$active = $ids == 1 ? ' tab-active' : '';

								$children = new WP_Query(
									[
										'post_parent'    => $item,
										'post_type'      => bbp_get_topic_post_type(),
										'orderby'        => 'menu_order',
										'order'          => 'asc',
										'posts_per_page' => -1,
										'post_status'    => [ 'any', 'spam' ],
									]
								);

								$count_open     = 0;
								$count_closed   = 0;
								$count_hidden   = 0;
								$count_no_reply = 0;
								$count_solved   = 0;
								$count_unsolved = 0;
								$count_trash    = 0;

								while ( $children->have_posts() ) : $children->the_post();
									$topic_id = get_the_ID();
									$replies = get_children( [
										'post_parent' => $topic_id,
										'post_type'   => bbp_get_reply_post_type(),
										'post_status' => [ 'publish', 'draft', 'pending' ],
									] );

									// Count open/closed topics.
									if ( bbp_is_topic_closed( $topic_id ) ) {
										$count_closed++;
									} else {
										$count_open++;
									}

									// Count spam( hidden ) topics.
									if ( bbp_is_topic_spam( $topic_id ) || bbp_is_topic_pending( $topic_id ) ) {
										$count_hidden++;
									}

									// Replies count.
									if ( 0 == count( $replies ) ) {
										$count_no_reply++;
									}

									// Count solved.
									// Before using $GLOBALS['bbp_solved_topic'], check if it is set
									if (isset($GLOBALS['bbp_solved_topic']) && $GLOBALS['bbp_solved_topic']->is_solved($topic_id)) {
										$count_solved++;
									} else {
										$count_unsolved++;
									}
								endwhile; 
								wp_reset_postdata();

								$trash_topic = new WP_Query( [
									'post_parent'    => $item,
									'post_type'      => bbp_get_topic_post_type(),
									'posts_per_page' => -1,
									'post_status'    => [ 'trash' ],
								] );
								
								while ( $trash_topic->have_posts() ) :
									$trash_topic->the_post();
									$trash_topic_id = get_the_ID();
									// Count trash.
									if ( bbp_is_topic_trash( $trash_topic_id ) ) {
										$count_trash++;
									}
								endwhile;
								wp_reset_postdata();
								?>
								<div class="easydocs-tab <?php echo esc_attr( $active ); ?>" id="tab-<?php echo esc_attr( $item ); ?>">

									<!-- Tab filters. -->
									<?php include __DIR__ . '/admin_ui/tab_filters.php'; ?>

									<!-- Children topics. -->
									<?php include __DIR__ . '/admin_ui/topics.php'; ?>

									<a class="easydocs-btn easydocs-btn-outline-blue easydocs-btn-sm easydocs-btn-round button button-info section-doc" id="bbpc-topic" target="_blank" name="submit" href="javascript:void(0)" bbp_forum_id="<?php echo esc_attr( $item ); ?>">
										<?php esc_html_e( 'Add Topic', 'bbp-core' ); ?>
									</a>
								</div>
								<?php
								endforeach;
							endif;
							?>
						</div>
					</div>
				</div>
			</main>
			<?php
		else :
			?>
			<div class="eazydocs-no-content">
				<img src="<?php echo BBPC_IMG; ?>/icon/folder-open.png" alt="<?php esc_attr_e( 'Folder Open', 'bbp-core' ); ?>">
				<p class="big-p"> <?php esc_html_e( 'No forum has been found . Perhaps', 'bbp-core' ); ?> </p>
				<p> <br>
					<a href="javascript:void(0)" type="button" id="bbpc-forum" class="button button-primary ezd-btn btn-lg">
						<?php esc_html_e( 'Create Forum', 'bbp-core' ); ?>
					</a>
				</p>
			</div>
			<?php
		endif;
		?>
	</div>
</div>

<script>
	(function ($) {
		$(document).ready(function () {
			let docContainer = document.querySelectorAll('.easydocs-tab');
			var config = {
				controls: {
					scope: 'local',
				},
				animation: {
					enable: false,
				},
				load: {
					filter: '<?php echo esc_js( $bbpc_opt['default_filter'] ?? '.open-topics' ); ?>'
				}
			};
			for (let i = 0; i < docContainer.length; i++) {
			var mixer1 = mixitup(docContainer[i], config);
		}
	});
})(jQuery);
</script>