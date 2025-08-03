<section class="forum-category-area pt-100 pb-115">
	<div class="container">
		<div class="row gy-lg-0 gy-4">
			<?php
			while ( $forums->have_posts() ) : $forums->the_post();
				?>
				<div class="col-custom wow fadeInUp" data-wow-delay="0.3s">
					<a href="<?php the_permalink(); ?>">
						<div class="single-category-widget">
							<?php the_post_thumbnail( 'ama_60x61' ); ?>
							<h5><?php the_title(); ?></h5>
						</div>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>