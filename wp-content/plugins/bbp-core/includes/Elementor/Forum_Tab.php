<?php
namespace admin\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use WP_Query;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Forum_Tab extends Widget_Base {
	public function get_name() {
		return 'ama_forum_tab';
	}

	public function get_title() {
		return esc_html__( 'BBPC Forum Tabs', 'bbp-core' );
	}

	public function get_icon() {
		return 'bbpc_icon_ama_forum_tab';
	}

	public function get_categories() {
		return [ 'bbp-core' ];
	}
	
	public function get_style_depends() {
		return['bbpc-el-widgets'];
	}

	// scripts dependencies
	public function get_script_depends() {
		return [ 'bbpc_js' ];
	}


	protected function register_controls(): void
    {


		//======================= Forum Filter Options ==================
		$this->start_controls_section(
			'forum_filter', [
				'label' => __( 'Forum Filter Options', 'bbp-core' ),
			]
		);

		$this->add_control(
			'forum_tab_title', [
				'label'       => __( 'Forum Tab Title', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Show Forums', 'bbp-core' ),
				'placeholder' => __( 'Enter the tab title', 'bbp-core' ),
			]
		);

		$this->add_control(
			'ppp', [
				'label'       => esc_html__( 'Show Forums', 'bbp-core' ),
				'description' => esc_html__( 'Show the forums count at the initial view. Default is 9 forums in a row.', 'bbp-core' ),
				'default'     => 9
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

        //==== View More Button
        $this->add_control(
            'more_txt_heading', [
                'label'       => esc_html__( 'View More Button', 'bbp-core' ),
                'type'        => Controls_Manager::HEADING,
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'is_forum_tab_btn', [
                'label' => esc_html__( 'Button (Show/Hide)', 'bbp-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'bbp-core' ),
                'label_off' => esc_html__( 'Hide', 'bbp-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

		$this->add_control(
			'more_txt', [
				'label'       => esc_html__( 'Button Label', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__('View More', 'bbp-core'),
                'condition'   => [
                    'is_forum_tab_btn' => 'yes'
                ]
			]
		);

		$this->add_control(
			'more_url', [
				'label'       => esc_html__( 'Button URL', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => [
					'url'         => get_post_type_archive_link( 'forum' ),
					'is_external' => true,
					'nofollow'    => true,
				],
                'condition'   => [
                    'is_forum_tab_btn' => 'yes'
                ]
			]
		); //End View More Button

		$this->end_controls_section(); // End Forum Filter Options


		//======================== Topic Filter Options =========================//
		$this->start_controls_section(
			'topic_filter', [
				'label' => __( 'Topic Filter Options', 'bbp-core' ),
			]
		);

		$this->add_control(
			'topics_tab_title', [
				'label'       => __( 'Topics Tab Title', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Show Topics', 'bbp-core' ),
				'placeholder' => __( 'Enter the tab title', 'bbp-core' ),
			]
		);

		$this->add_control(
			'ppp2', [
				'label'       => esc_html__( 'Show Forums', 'bbp-core' ),
				'description' => esc_html__( 'Show the forums count at the initial view. Default is 9 forums in a row.', 'bbp-core' ),
				'label_block' => true,
				'default'     => 6
			]
		);

		$this->add_control(
			'order2', [
				'label'   => esc_html__( 'Order', 'bbp-core' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'ASC'  => 'ASC',
					'DESC' => 'DESC'
				],
				'default' => 'ASC'
			]
		);

        //==== View More Button
        $this->add_control(
            'more_txt2_heading', [
                'label'       => esc_html__( 'View More Button', 'bbp-core' ),
                'type'        => Controls_Manager::HEADING,
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'is_topic_tab_btn', [
                'label' => esc_html__( 'Button (Show/Hide)', 'bbp-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'bbp-core' ),
                'label_off' => esc_html__( 'Hide', 'bbp-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );


		$this->add_control(
			'more_txt2', [
				'label'       => esc_html__( 'Button Label', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__('View More', 'bbp-core'),
                'condition'   => [
                    'is_topic_tab_btn' => 'yes'
                ]
			]
		);

		$this->add_control(
			'more_url2', [
				'label'       => esc_html__( 'Button URL', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => [
					'url'         => get_post_type_archive_link( 'topic' ),
					'is_external' => true,
					'nofollow'    => true,
				],
                'condition'   => [
                    'is_topic_tab_btn' => 'yes'
                ]
			]
		);

		$this->end_controls_section(); //End Topic Filter Options


        //===================== Forum Tab Title ======================//
		$this->start_controls_section(
			'forum_tab_style', [
				'label' => __( 'Forum Tab Title', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'forum_tab_title',
				'label'    => __( 'Tab Label Typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .community-area .nav-tabs .nav-item button',
			]
		);

		$this->add_control(
			'forum_tab_title_color',
			[
				'label'     => __( 'Tab Label Color', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .community-area .nav-tabs .nav-item button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'forum_tab_button', [
				'label' => esc_html__( 'Forum Tab Button', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'tab_btn_margin', [
				'label'      => esc_html__( 'Margin', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tab-content .show-more-btn.show-more-round' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tab_btn_padding', [
				'label'      => esc_html__( 'Tab button padding', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tab-content .show-more-btn.show-more-round' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings();

		$forums = new WP_Query( array(
			'post_type'      => 'forum',
			'posts_per_page' => ! empty( $settings['ppp'] ) ? $settings['ppp'] : 9,
			'order'          => $settings['order'],
		) );

		$topics = new WP_Query( array(
			'post_type'      => 'topic',
			'posts_per_page' => ! empty( $settings['ppp2'] ) ? $settings['ppp2'] : 9,
			'order'          => $settings['order'],
		) );

		include( "inc/forum/forum_tab.php" );
	}
}