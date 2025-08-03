<?php
namespace admin\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use WP_Query;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Forum_Ajax extends Widget_Base {
	public function get_name(): string {
		return 'ama_ajax_forum';
	}

	public function get_title(): string {
		return esc_html__( 'BBPC Ajax Forums', 'bbp-core' );
	}

	public function get_icon(): string {
		return 'bbpc_icon_ama_ajax_forum';
	}

	public function get_keywords(): array {
		return [ 'forum', 'ajax' ];
	}

	public function get_categories(): array {
		return [ 'bbp-core' ];
	}

	public function get_style_depends(): array {
		return [ 'bbpc-el-widgets' ];
	}

	public function get_script_depends(): array {
		return [ 'bbpc-ajax' ];
	}

	protected function register_controls(): void {
		//========================== Filter Options =====================//
		$this->start_controls_section(
			'filter_sec', [
				'label' => esc_html__( 'Filter Options', 'bbp-core' ),
			]
		);

		$this->add_control(
			'ppp2', [
				'label'       => esc_html__( 'Show Forums', 'bbp-core' ),
				'description' => esc_html__( 'Show the forums count at the initial view. Default is 9 forums in a row.', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 9
			]
		);

		$this->add_control(
			'order', [
				'label'   => esc_html__( 'Order', 'bbp-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'ASC'  => 'ASC',
					'DESC' => 'DESC'
				],
				'default' => 'ASC'
			]
		);

		// button show hide switcher
		$this->add_control(
			'filter_btns',
			[
				'label'        => esc_html__( 'Tab Filter', 'bbp-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'bbp-core' ),
				'label_off'    => esc_html__( 'Hide', 'bbp-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		/**
		 * Styling section starts
		 */
		$this->start_controls_section(
			'styling_sec', [
				'label' => esc_html__( 'Title Style', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		//forum title
		$this->add_control(
			'forum_heading',
			[
				'label'     => esc_html__( 'Forum Title', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);


		$this->add_control(
			'forum_title_color',
			[
				'label'     => esc_html__( 'Color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .single-forum-post-widget .post-title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'forum_title_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .single-forum-post-widget .post-title a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'forum_title_typography',
				'selector' => '{{WRAPPER}} .single-forum-post-widget .post-title a',
			]
		);

		// Forum meta
		$this->add_control(
			'forum_meta',
			[
				'label'     => esc_html__( 'Forum Meta', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'forum_meta_color',
			[
				'label'     => esc_html__( 'Color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .single-forum-post-widget .post-info .author,{{WRAPPER}} .single-forum-post-widget .post-info .post-time' => 'color: {{VALUE}}',
				],
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'forum_meta_typography',
				'selector' => '{{WRAPPER}} .single-forum-post-widget .post-info .author,{{WRAPPER}} .single-forum-post-widget .post-info .post-time',
			]
		);

		//parent forum 
		$this->add_control(
			'parent_forum',
			[
				'label'     => esc_html__( 'Parent Forum', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'parent_forum_color',
			[
				'label'     => esc_html__( 'Color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-content .post-category a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'parent_forum_color_hover',
			[
				'label'     => esc_html__( 'Hover color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-content .post-category a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'parent_forum_typo',
				'selector' => '{{WRAPPER}} .post-content .post-category a',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings    = $this->get_settings();
		$filter_btns = $settings['filter_btns'] ?? true;

		$topics = new WP_Query( array(
			'post_type'      => 'topic',
			'posts_per_page' => ! empty( $settings['ppp2'] ) ? $settings['ppp2'] : 9,
			'order'          => $settings['order'] ? $settings['order'] : 'DESC',
		) );
		?>

        <div class="forum-post-widget" data_id="<?php echo esc_attr( $this->get_id() ); ?>">

			<?php
			if ( $filter_btns ) :
				?>
                <div class="post-filter-widget mb-20 wow fadeInUp">
                    <div class="single-filter-item">
                        <a href="#" id="all_filt" data-forum="all" class="data-active">
                            <i class="icon_grid-2x2"></i><?php esc_html_e( 'All', 'bbp-core' ) ?>
                        </a>
                    </div>
                    <div class="single-filter-item">
                        <a href="#" id="populer_filt" data-forum="popular">
                            <i class="icon_easel"></i><?php esc_html_e( 'Popular', 'bbp-core' ) ?>
                        </a>
                    </div>
                    <div class="single-filter-item">
                        <a href="#" id="featured_filt" data-forum="featured">
                            <i class="icon_ribbon_alt"></i><?php esc_html_e( 'Featured', 'bbp-core' ) ?>
                        </a>
                    </div>
                    <div class="single-filter-item">
                        <a href="#" id="recent_filt" data-forum="recent">
                            <i class="icon_clock_alt"></i><?php esc_html_e( 'Recent', 'bbp-core' ) ?>
                        </a>
                    </div>
                    <div class="single-filter-item">
                        <a href="#" id="unsolved_filt" data-forum="unsolved">
                            <i class="icon_close_alt2"></i><?php esc_html_e( 'Unsolved', 'bbp-core' ) ?>
                        </a>
                    </div>
                    <div class="single-filter-item">
                        <a href="#" id="solved_filt" data-forum="solved">
                            <i class="icon_check_alt2"></i><?php esc_html_e( 'Solved', 'bbp-core' ) ?>
                        </a>
                    </div>
                </div>
			<?php
			endif;
			?>

            <div id="aj-post-filter-widget">
				<?php
				$delay = 0.0;
				$i     = 0;
				while ( $topics->have_posts() ) : $topics->the_post();
					$topic_id   = $topics->posts[ $i ]->ID;
					$vote_count = get_post_meta( $topic_id, "bbpv-votes", true );
					$forum_id   = bbp_get_topic_forum_id();
					?>
                    <div class="single-forum-post-widget wow fadeInUp" data-wow-delay="<?php echo $delay ?>s">
                        <div class="post-content">
                            <div class="post-title">
                                <h6><a href="<?php the_permalink(); ?>"> <?php the_title() ?> </a></h6>
                            </div>
                            <div class="post-info">
                                <div class="author">
                                    <img src="<?php echo BBPC_IMG . '/forum_tab/user-circle-alt.svg' ?>" alt="<?php esc_attr_e( 'User circle alt icon',
										'bbpc-core' ); ?>">
									<?php
									echo bbp_get_topic_author_link(
										array(
											'post_id' => $topic_id,
											'type'    => 'name'
										)
									);
									?>
                                </div>

                                <div class="post-time">
                                    <img src="<?php echo BBPC_IMG . '/forum_tab/time-outline.svg' ?>"
                                         alt="<?php esc_attr_e( 'Time outline icon', 'bbpc-core' ); ?>">
									<?php bbp_forum_last_active_time( get_the_ID() ); ?>
                                </div>
                            </div>

                            <div class="post-category">
                                <a href="<?php echo get_the_permalink( $forum_id ) ?>">
									<?php echo get_the_post_thumbnail( $forum_id ); ?>
									<?php echo bbp_get_topic_forum_title(); ?>
                                </a>
                            </div>
                        </div>
                        <div class="post-reach">
                            <div class="post-view">
                                <img src="<?php echo BBPC_IMG . '/forum_tab/eye-outline.svg' ?>" alt="<?php esc_attr_e( 'Eye outline icon', 'bbpc-core' ); ?>">

								<?php
								bbp_topic_view_count( $topic_id );
								echo '&nbsp;';
								esc_html_e( 'Views', 'bbp-core' );
								?>
                            </div>
                            <div class="post-like">
                                <img src="<?php echo BBPC_IMG . '/forum_tab/thumbs-up-outline.svg' ?>"
                                     alt="<?php esc_attr_e( 'Thumbs-up outline icon', 'bbpc-core' ); ?>">

								<?php
								if ( $vote_count ) {
									echo $vote_count;
								} else {
									echo "0";
								}

								echo '&nbsp;';
								esc_html_e( 'Likes', 'bbp-core' );
								?>
                            </div>
                            <div class="post-comment">
                                <img src="<?php echo BBPC_IMG . '/forum_tab/chatbubbles-outline.svg' ?>"
                                     alt="<?php esc_attr_e( 'Chat bubbles icon', 'bbpc-core' ); ?>">
								<?php
								bbp_topic_reply_count( $topic_id );
								echo '&nbsp;';
								esc_html_e( 'Replies', 'bbp-core' );
								?>
                            </div>
                        </div>
                    </div>
					<?php
					$delay += 0.2;
					if ( $delay > 0.6 ) {
						$delay = 0.0;
					}
					$i ++;
				endwhile;
				unset( $delay );
				unset( $i );
				wp_reset_postdata();
				?>
            </div>
        </div>
		<?php
	}
}