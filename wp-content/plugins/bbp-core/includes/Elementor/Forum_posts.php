<?php

namespace admin\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use WP_Query;
use WP_Post;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Forum_posts
 *
 * @package amaCore\Widgets
 */
class Forum_posts extends Widget_Base {

	public function get_name() {
		return 'ama_forum_posts';
	}

	public function get_title() {
		return __( 'BBPC Forum Topics', 'bbp-core' );
	}

	public function get_icon() {
		return 'bbpc_icon_ama_forum_posts';
	}

	public function get_keywords() {
		return [ 'topics', 'replies' ];
	}

	// style dependency
	public function get_style_depends() {
		return [ 'bbpc-el-widgets' ];
	}

	public function get_categories() {
		return [ 'bbp-core' ];
	}

	protected function register_controls() {

		// --- Filter Options
		$this->start_controls_section(
			'filter_opt', [
				'label' => __( 'Filter Options', 'bbp-core' ),
			]
		);

		$this->add_control(
			'ppp', [
				'label'       => esc_html__( 'Show Forum Topics', 'bbp-core' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 5
			]
		);

		$this->add_control(
			'order', [
				'label'   => esc_html__( 'Order', 'bbp-core' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'ASC'  => 'ASC',
					'DESC' => 'DESC'
				],
				'default' => 'ASC'
			]
		);

		$this->end_controls_section();
		// end Document Setting Section
	}

	protected function render() {
		$settings    = $this->get_settings();
		$forum_posts = new WP_Query( array(
			'post_type'      => 'topic',
			'posts_per_page' => ! empty( $settings['ppp'] ) ? $settings['ppp'] : - 1,
			'order'          => $settings['order'] ? $settings['order'] : 'ASC',
		) );
		?>
        <div class="community-posts-wrapper">
			<?php
			while ( $forum_posts->have_posts() ) : $forum_posts->the_post();
				$favoriters     = bbp_get_topic_favoriters();
				$favorite_count = ! empty( $favoriters ) ? $favoriters[0] : '0';
				?>
                <div class="community-post wow fadeInUp" data-wow-delay="0.5s">
                    <div class="post-content">
                        <div class="author-avatar">
							<?php 
							echo bbp_get_topic_author_link( 
								array( 
									'post_id' 	=> get_the_ID(), 
									'size' 		=> 40, 
									'type' 		=> 'avatar' 
								)
							);
							?>
                        </div>
                        <div class="entry-content">
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>"> <?php the_title(); ?> </a>
                            </h3>

							<?php 
							esc_html_e( 'Last active: ', 'bbp-core' );
							echo bbp_get_forum_last_active_time( get_the_ID() );
							?>

                        </div>
                    </div>
                    <div class="post-meta-wrapper">
                        <ul class="post-meta-info">
                            <li>
                                <a href="<?php bbp_topic_permalink(); ?>">
                                    <i class="icon_chat_alt"></i>
									<?php bbp_show_lead_topic() ? bbp_topic_reply_count( get_the_ID() ) : bbp_topic_post_count( get_the_ID() ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php bbp_topic_permalink(); ?>">
                                    <i class="icon_star_alt"></i> <?php echo esc_html( $favorite_count ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
			    <?php
			endwhile;
			?>
        </div>
		<?php
	}
}