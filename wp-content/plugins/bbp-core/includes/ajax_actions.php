<?php

/**
 * All Search results
 */
add_action( 'wp_ajax_bbpc_search_data_fetch', 'bbpc_search_data_fetch' );
add_action( 'wp_ajax_nopriv_bbpc_search_data_fetch', 'bbpc_search_data_fetch' );
function bbpc_search_data_fetch() {
	
	$opt                = get_option( 'bbpc_opt' );
	$is_ajax_search_tab = $opt['is_ajax_search_tab'] ?? '';
	global $post;

	if ( class_exists( 'bbPress' ) ) {
		// All results query
		$all_results = new WP_Query( array(
			'post_type'      => ['forum', 'topic' ],
			's'              => $_POST['keyword'] ?? '',
		) );
		$all_results_count = $all_results->found_posts;
		if ( $all_results_count > 0 ) {
			$all_nsoresult = 'data-result=';
		} else {
			$all_nsoresult = 'data-noresult=';
		}

		// Forum query
		$forum_query = new WP_Query( array(
			'post_type'      => ['forum', 'topic'],
			's'              => $_POST['keyword'] ?? '',
		) );
		$forum_count = $forum_query->found_posts;
		if ( $forum_count > 0 ) {
			$forum_nsoresult = 'data-result=';
		} else {
			$forum_nsoresult = 'data-noresult=';
		}

		?>
		<div class="tab-item active all-active" <?php echo esc_attr( $all_nsoresult. 'No-Results-Found' ); ?>></div>	
		<?php 
		if ( class_exists( 'bbPress' ) ) : ?>
			<div id="search-forum" <?php echo esc_attr( $forum_nsoresult. 'No-Results-Found' ); ?> class="tab-item" ></div>
			<?php 
		endif;
	}	

	echo '<div class="bbpc-search-results-wrapper">';

	// Forums query
	if ( class_exists( 'bbPress' ) ) :

		echo '<div class="search-results-tab forum show" id="search-forum-results">';
		
		$forum = new WP_Query( array(
			'post_type'      => 'forum',
			's'              => $_POST['keyword'] ?? '',
			'posts_per_page' => 5,
		) );

		if ( $forum->have_posts() ) :
			echo '<h2 class="bbpc-search-title">' . esc_html__( 'Forums', 'bbp-core' ) . '</h2>';
			while ( $forum->have_posts() ) : $forum->the_post();
				?>
				<div class="search-result-item forum" onclick="document.location='<?php echo get_the_permalink(get_the_ID()); ?>'">					
					<a href="<?php echo get_the_permalink(get_the_ID()); ?>">
						<?php the_title(); ?>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;

		$topics = new WP_Query( array(
			'post_type'      => 'topic',
			's'              => $_POST['keyword'] ?? '',
			'posts_per_page' => 5,
		) );

		if ( $topics->have_posts() ) :
			echo '<h2 class="bbpc-search-title">' . esc_html__( 'Topics', 'bbp-core' ) . '</h2>';
			while ( $topics->have_posts() ) : $topics->the_post();
				?>
				<div class="search-result-item forum" onclick="document.location='<?php echo get_the_permalink(get_the_ID()); ?>'">					
					<a href="<?php echo get_the_permalink(get_the_ID()); ?>">
						<?php the_title(); ?>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;
		echo '</div>';		
	endif;

	echo '<h5 class="not-found-text">Not Found Result!</h5>';
	echo '</div>';
	die();
}

/**
 * Forum Search results
 */
add_action( 'wp_ajax_bbpc_search_data_forum', 'bbpc_search_data_forum' );
add_action( 'wp_ajax_nopriv_bbpc_search_data_forum', 'bbpc_search_data_forum' );
function bbpc_search_data_forum() {
	
	// Forums query
	if ( class_exists( 'bbPress' ) ) :

		echo '<div class="search-results-tab show" id="search-forum-results">';
		
		$forum = new WP_Query( array(
			'post_type'      => 'forum',
			's'              => $_POST['keyword'] ?? '',
			'posts_per_page' => 5,
		) );

		if ( $forum->have_posts() ) :
			echo '<h2 class="bbpc-search-title">' . esc_html__( 'Forums', 'bbp-core' ) . '</h2>';
			while ( $forum->have_posts() ) : $forum->the_post();
				?>
				<div class="search-result-item forum" onclick="document.location='<?php echo get_the_permalink(get_the_ID()); ?>'">					
					<a href="<?php echo get_the_permalink(get_the_ID()); ?>">
						<?php the_title(); ?>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;

		$topics = new WP_Query( array(
			'post_type'      => 'topic',
			's'              => $_POST['keyword'] ?? '',
			'posts_per_page' => 5,
		) );

		if ( $topics->have_posts() ) :
			echo '<h2 class="bbpc-search-title">' . esc_html__( 'Topics', 'bbp-core' ) . '</h2>';
			while ( $topics->have_posts() ) : $topics->the_post();
				?>
				<div class="search-result-item forum" onclick="document.location='<?php echo get_the_permalink(get_the_ID()); ?>'">					
					<a href="<?php echo get_the_permalink(get_the_ID()); ?>">
						<?php the_title(); ?>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;
		echo '</div>';
	endif;
	die();
}