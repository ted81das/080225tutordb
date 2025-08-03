
<?php 
if ( $settings['is_keywords'] == 'yes' && !empty($settings['keywords']) ) : 
	$keywords_align = $settings['keywords_align'] ?? 'center';
	?>
	<div class="bbpc-search-keyword <?php echo esc_attr( $keywords_align ); ?>">
	<?php if ( !empty($settings['keywords_label']) ) : ?>
			<span class="bbpc-search-keywords-label"> <?php echo $settings['keywords_label'] ?> </span>
		<?php endif;
		
		if ( !empty($settings['keywords']) ) : ?>
			<ul class="list-unstyled">
				<?php
				foreach ( $settings['keywords'] as $keyword ) :
					?>
					<li class="wow fadeInUp" data-wow-delay="0.2s">
						<a href="#"> <?php echo esc_html($keyword['title']); ?> </a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
<?php endif; ?>