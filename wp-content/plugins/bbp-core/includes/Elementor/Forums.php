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
 * Class Forums
 *
 * @package amaCore\Widgets
 */
class Forums extends Widget_Base {

	public function get_name() {
		return 'ama_forums';
	}

	public function get_title() {
		return __( 'BBPC Forums', 'bbp-core' );
	}

	public function get_icon() {
		return 'bbpc_icon_ama_forums';
	}

	public function get_categories() {
		return [ 'bbp-core' ];
	}

	// style dependency
	public function get_style_depends() {
		return [ 'bbpc-el-widgets' ];
	}
	
	public function get_script_depends() {
		return [ 'bbpc-ajax' ];
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
				'label'       => esc_html__( 'Show Forums', 'bbp-core' ),
				'description' => esc_html__( 'Show the forums count at the initial view. Default is 5 forums in a row.', 'bbp-core' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => 5
			]
		);

		$this->add_control(
			'ppp2', [
				'label'       => esc_html__( 'Hidden Forums', 'bbp-core' ),
				'description' => esc_html__( 'Hidden forums will show on clicking on the More button.', 'bbp-core' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => 10
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

		$this->add_control(
			'more_txt', [
				'label'       => esc_html__( 'Read More Text', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'More Communities'
			]
		);

		$this->end_controls_section();
		// end Document Setting Section

		$this->start_controls_section(
			'forum_styling', [
				'label' => esc_html__( 'Forum Style', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'forum_title_typography',
				'label'    => esc_html__( 'Forum Title Typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .com-box-content .title a',
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'more_text_typography',
				'label'    => esc_html__( 'More text typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .collapse-btn',
				
			]
		);

		$this->add_control(
			'more_text_color',
			[
				'label'     => esc_html__( 'More text color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .collapse-btn' => 'color: {{VALUE}}',
					'{{WRAPPER}} .more-communities .collapse-btn svg path' => 'stroke: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings 				= $this->get_settings();
		$forums   				= new WP_Query( array(
			'post_type'      	=> 'forum',
			'posts_per_page' 	=> ! empty( $settings['ppp'] ) ? $settings['ppp'] : 5,
			'order'          	=> $settings['order'] ? $settings['order'] : 'ASC',
		) );
		?>
        <div class="communities-boxes">
			<?php
			while ( $forums->have_posts() ) : $forums->the_post();
				?>
                <div class="com-box wow fadeInRight" data-wow-delay="0.5s">
                    <div class="icon-container">
						<?php the_post_thumbnail( 'full' ); ?>
                    </div>
                    <div class="com-box-content">
                        <h3 class="title">
                            <a href="<?php the_permalink(); ?>"> <?php the_title() ?> </a>
                        </h3>
                        <p class="total-post"> <?php bbp_forum_topic_count( get_the_ID() ); ?> <?php esc_html_e( ' Posts', 'bbp-core' ) ?> </p>
                    </div>
                    <!-- /.ama-com-box-content -->
                </div>
                <!-- /.ama-com-box -->
				<?php
			endwhile;
			wp_reset_postdata();
			?>
        </div>
        <!-- /.communities-boxes -->

        <div class="more-communities" data_id="<?php echo esc_attr( $this->get_id() );?>">

            <a href="#more-category" class="collapse-btn-wrap">
				<?php echo esc_html( $settings['more_txt'] ); ?>
				 
				<svg fill="#000000" width="16px" height="16px" viewBox="0 0 24 24" id="minus" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="icon line-color icon_minus"> <line id="primary" x1="19" y1="12" x2="5" y2="12" style="fill: none;  stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line> </svg>
				
				<svg fill="#000000" width="16px" height="16px" viewBox="0 0 24 24" id="plus" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="icon line-color icon_plus"><path id="secondary" d="M5,12H19M12,5V19" style="fill: none; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></svg>
            </a>

            <div class="collapse-wrap" id="more-category" data_id="<?php echo esc_attr( $this->get_id() );?>">
                <div class="communities-boxes">
					<?php
					$forums2 = new WP_Query( array(
						'post_type'      => 'forum',
						'posts_per_page' => ! empty( $settings['ppp2'] ) ? $settings['ppp2'] : 10,
						'offset'         => ! empty( $settings['ppp'] ) ? $settings['ppp'] : 5,
						'order'          => $settings['order'],
					) );
					while ( $forums2->have_posts() ) : $forums2->the_post();
						?>
                        <div class="com-box">
                            <div class="icon-container">
								<?php the_post_thumbnail( 'full' ); ?>
                            </div>
                            <div class="com-box-content">
                                <h3 class="title">
                                    <a href="<?php the_permalink(); ?>"> <?php the_title() ?> </a>
                                </h3>
                                <p class="total-post"> <?php bbp_forum_topic_count( get_the_ID() ); ?> <?php esc_html_e( ' Posts', 'bbp-core' ); ?> </p>
                            </div>
                            <!-- /.ama-com-box-content -->
                        </div>
                        <!-- /.ama-com-box -->
						<?php
					endwhile;
					wp_reset_postdata();
					?>
                </div>
                <!-- /.communities-boxes -->
            </div>
            <!-- /.collapse-wrap -->
        </div>
        <!-- /.more-communities -->
		<?php
	}
}