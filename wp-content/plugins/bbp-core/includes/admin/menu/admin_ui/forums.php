<div class="tab-menu <?php echo $count > 12 ? '' : 'short'; ?>">
    <div class="tab-menu-left-layer"></div>
    <ul class="easydocs-navbar">
		<?php
		$i = 0; // Start with an integer instead of a string to prevent the deprecated warning
		while ( $forum_query->have_posts() ) :
			$forum_query->the_post();
			$i ++;
			$parent_forums[] = get_the_ID();
			$current_post    = get_the_ID();
			$is_active       = $i == 1 ? 'is-active' : '';
			$count_children  = get_children(
				[
					'post_parent' => $current_post,
					'post_type'   => bbp_get_topic_post_type(),
					'orderby'     => 'menu_order',
					'order'       => 'asc',
					'post_status' => [ 'any', 'spam' ],
				]
			);
			?>
            <li class="easydocs-navitem <?php echo esc_attr( $is_active ); ?>" data-rel="tab-<?php the_ID(); ?>" data-id="<?php the_ID(); ?>">
                <div class="title">
                    <div class="featured-image">
	                    <?php
	                    if ( get_the_post_thumbnail( $current_post ) ) :
		                    echo get_the_post_thumbnail( $current_post, 'bbpc_32x32' );
	                    else :
		                    ?>
                            <span class="dashicons dashicons-buddicons-forums"></span>
	                    <?php endif; ?>
                    </div>

                    <span class="easydocs-forums-title"><?php the_title(); ?></span>
                </div>
                <div class="total-page">
					<span>
						<?php echo count( $count_children ) > 0 ? count( $count_children ) : ''; ?>
					</span>
                </div>
                <div class="link-wrapper link">
					<?php if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) : ?>
                        <a href="<?php echo get_edit_post_link( get_the_ID() ); ?>" class="link edit" target="_blank"
                           title="<?php esc_attr_e( 'Edit this forum.', 'bbp-core' ); ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
					<?php
					endif;
					?>

                    <a href="<?php the_permalink(); ?>" class="link external-link" target="_blank" data-id="tab-<?php the_ID(); ?>"
                       title="<?php esc_attr_e( 'View this forum in new tab', 'bbp-core' ); ?>">
                        <span class="dashicons dashicons-external"></span>
                    </a>
					<?php
					if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) :
						?>
                        <a href="javascript:void(0);" bbp_forum_id="<?php echo get_the_ID(); ?>" class="link forum-delete"
                           title="<?php esc_attr_e( 'Move this forum to the Trash', 'bbp-core' ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
					<?php endif; ?>
                </div>
            </li>
			<?php
			$forum_parent = get_the_ID();
		endwhile;
		wp_reset_postdata();
		?>
    </ul>
</div>