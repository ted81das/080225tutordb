<?php

namespace admin\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Elementor\Group_Control_Border;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search extends Widget_Base {
	public function get_name() {
		return 'ama_search';
	}

	public function get_title() {
		return esc_html__( 'BBPC Search', 'bbp-core' );
	}

	public function get_icon() {
		return 'bbpc_icon_ama_search';
	}

	public function get_style_depends() {
		return [ 'ama-core-style', 'bbpc-el-widgets' ];
	}

	public function get_script_depends() {
		return [ 'bbpc-frontend-js', 'bbpc-frontend-js' ];
	}

	public function get_categories() {
		return [ 'bbp-core' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'search_form_sec', [
				'label' => esc_html__( 'Form', 'bbp-core' ),
			]
		);

		$this->add_control(
			'placeholder', [
				'label'       => esc_html__( 'Placeholder', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'Search for Topics....',
			]
		);

		// select controls
		$this->add_control(
			'submit_btn_type', [
				'label'   => __( 'Search Type', 'bbp-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => __( 'Icon', 'bbp-core' ),
					'text' => __( 'Text', 'bbp-core' )
				],
			]
		);

		$this->add_control(
			'submit_btn_icon', [
				'label'     => __( 'Submit Button Icon', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-search',
					'library' => 'solid',
				],
				'condition' => [
					'submit_btn_type' => 'icon'
				]
			]
		);

		$this->add_control(
			'submit_btn_text', [
				'label'       => esc_html__( 'Submit Button Text', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => __( 'Search', 'bbp-core' ),
				'condition'   => [
					'submit_btn_type' => 'text'
				]
			]
		);

		$this->add_control(
			'submit_btn_align', [
				'label'   => __( 'Submit Alignment', 'bbp-core' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'right' => [
						'title' => __( 'Left', 'bbp-core' ),
						'icon'  => 'fa fa-align-left',
					],
					'left'  => [
						'title' => __( 'Right', 'bbp-core' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'toggle'  => false,
				'default' => 'left'
			]
		);

		$this->add_control(
			'is_keywords', [
				'label'        => esc_html__( 'Keywords', 'bbp-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'keywords_label',
			[
				'label'       => esc_html__( 'Keywords Label', 'bbp-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'Popular:',
				'condition'   => [
					'is_keywords' => 'yes'
				]
			]
		);

		$this->add_control(
			'keywords_align', [
				'label'   => __( 'Keywords Alignment', 'bbp-core' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => __( 'Left', 'bbp-core' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'bbp-core' ),
						'icon'  => 'fa fa-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'bbp-core' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'toggle'  => false,
				'default' => 'center'
			]
		);

		$keywords = new \Elementor\Repeater();

		$keywords->add_control(
			'title', [
				'label'       => __( 'Title', 'bbp-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$this->add_control(
			'keywords',
			[
				'label'         => __( 'Repeater List', 'bbp-core' ),
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $keywords->get_controls(),
				'default'       => [
					[
						'title' => __( 'Keyword #1', 'bbp-core' ),
					],
					[
						'title' => __( 'Keyword #2', 'bbp-core' ),
					],
				],
				'title_field'   => '{{{ title }}}',
				'prevent_empty' => false,
				'condition'     => [
					'is_keywords' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		/**
		 * Style Keywords
		 * Global
		 */
		$this->start_controls_section(
			'style_form', [
				'label' => esc_html__( 'Form', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_heading_form', [
				'label' => esc_html__( 'Input', 'bbp-core' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$this->add_control(
			'color_text', [
				'label'     => esc_html__( 'Text Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input' => 'color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'color_placeholder', [
				'label'     => esc_html__( 'Placeholder Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'     => 'input_border',
				'label'    => esc_html__( 'Border', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input',
			]
		);

		$this->add_control(
			'input_bg_color', [
				'label'     => esc_html__( 'Background Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(), [
				'name'     => 'typography_placeholder',
				'label'    => esc_html__( 'Typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input::placeholder, .bbpc_search_form_wrapper .input-wrapper input',
			]
		);

		$this->add_responsive_control(
			'input-padding', [
				'label'      => esc_html__( 'Padding', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_responsive_control(
			'input-border-radius', [
				'label'      => __( 'Border Radius', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submit_heading_form', [
				'label'     => esc_html__( 'Submit', 'bbp-core' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'color_icon', [
				'label'     => esc_html__( 'Button Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button' => 'color: {{VALUE}};',
				],
			]
		);


		$this->add_control(
			'search_bg',
			[
				'label'     => __( 'Background', 'bbp-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button' => 'background: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'btn-padding', [
				'label'      => esc_html__( 'Padding', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_responsive_control(
			'btn-border-radius', [
				'label'      => __( 'Border Radius', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// slider controls
		$this->add_control(
			'typography_btn', [
				'label'     => esc_html__( 'Icon Size', 'bbp-core' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button i' => 'font-size: {{SIZE}}{{UNIT}};'
				],
				'condition' => [
					'submit_btn_type' => 'icon'
				],
			]
		);


		// slider controls
		$this->add_control(
			'submit_btn_align_left', [
				'label'     => esc_html__( 'Spacing', 'bbp-core' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper .submit-btn-left' => 'right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'submit_btn_align' => 'left'
				],
			]
		);


		// slider controls
		$this->add_control(
			'submit_btn_align_right', [
				'label'     => esc_html__( 'Spacing', 'bbp-core' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper .submit-btn-right' => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'submit_btn_align' => 'right'
				],
			]
		);

		// typography controls
		$this->add_group_control(
			Group_Control_Typography::get_type(), [
				'name'      => 'typography_btn',
				'label'     => esc_html__( 'Typography', 'bbp-core' ),
				'condition' => [
					'submit_btn_type' => 'text'
				],
				'selector'  => '{{WRAPPER}} .bbpc_search_form_wrapper .input-wrapper button',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_keywords', [
				'label' => esc_html__( 'Keywords', 'bbp-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'margin_keywords', [
				'label'       => __( 'Margin', 'bbp-core' ),
				'description' => __( 'Margin around the keywords block', 'bbp-core' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [ '{{WRAPPER}} .bbpc-search-keyword ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
				'separator'   => 'before',
				'default'     => [
					'unit' => 'px',
				],
			]
		);

		$this->add_control(
			'color_keywords_label', [
				'label'     => esc_html__( 'Label Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc-search-keyword span.bbpc-search-keywords-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'keyword_label_typography',
				'label'    => __( 'Label Typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .bbpc-search-keyword span.bbpc-search-keywords-label',
			]
		);

		$this->start_controls_tabs(
			'bbpc_tabs_keywords_style'
		);
		
		$this->start_controls_tab(
			'bbpc_keywords_style',
			[
				'label' => esc_html__( 'Normal', 'bbp-core' ),
			]
		);
		
		$this->add_control(
			'bbpc_color_keywords', [
				'label'     => esc_html__( 'Keyword Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .bbpc-search-keyword ul li a' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'bbpc_color_keywords_bg', [
				'label'     => esc_html__( 'Background Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'after',
				'selectors' => [
					'{{WRAPPER}} .bbpc-search-keyword ul li a' => 'background: {{VALUE}};',
				],
			]
		);
		
		$this->end_controls_tab();
		
		$this->start_controls_tab(
			'bbpc_keywords_style_hover',
			[
				'label' => esc_html__( 'Hover', 'bbp-core' ),
			]
		);
		
		$this->add_control(
			'bbpc_color_keywords_hover', [
				'label'     => esc_html__( 'Keyword Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc-search-keyword ul li a:hover' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'bbpc_color_keywords_bg_hover', [
				'label'     => esc_html__( 'Background Color', 'bbp-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bbpc-search-keyword ul li a:hover' => 'background: {{VALUE}};',
				],
			]
		);
		
		$this->end_controls_tabs();


		$this->add_group_control(
			Group_Control_Typography::get_type(), [
				'name'     => 'typography_keywords',
				'label'    => esc_html__( 'Typography', 'bbp-core' ),
				'selector' => '{{WRAPPER}} .bbpc-search-keyword ul li a',
			]
		);

		$this->add_control(
			'keywords_padding', [
				'label'      => __( 'Padding', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [ '{{WRAPPER}} .bbpc-search-keyword ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
				'default'    => [
					'unit' => 'px',
				],
			]
		);

		$this->add_control(
			'border_radius', [
				'label'      => __( 'Border Radius', 'bbp-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [ '{{WRAPPER}} .bbpc-search-keyword ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
				'default'    => [
					'unit' => 'px',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings  		= $this->get_settings();
		$title_tag 		= ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h2';
		$cross_position = $settings['submit_btn_align'] ?? 'right';
		if ( $cross_position == 'right' ) {
			$cross_position = 'left';
		} else {
			$cross_position = 'right';
		}
		?>
		
		<div class="bbpc-search-overlay"></div>
		
        <form action="<?php echo esc_url( home_url( '/' ) ) ?>" role="search" method="get" class="bbpc_search_form_wrapper">
            <div class="form-group">
                <div class="input-wrapper <?php echo esc_attr( $cross_position ); ?>">
					
					<span class="submit-btn-<?php echo esc_attr( $settings['submit_btn_align'] ?? 'left' ); ?>">
						<button type="submit">			
							<?php
							$submit_btn = $settings['submit_btn_type'] ?? 'icon';
							if ( $submit_btn == 'icon' ) {
								\Elementor\Icons_Manager::render_icon( $settings['submit_btn_icon'], [ 'aria-hidden' => 'true' ] );
							} else {
								echo esc_html( $settings['submit_btn_text'] );
							}
							?>
						</button>						 
					</span>

                    <input type='search' id="searchInput" autocomplete="off" name="s" placeholder="<?php echo esc_attr( $settings['placeholder'] ) ?>">

                    <!-- Ajax Search Loading Spinner -->
					<?php include( 'Search/search-spinner.php' ); ?>

                    <!-- WPML Language Code -->
					<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) : ?>
                        <input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>"/>
					<?php endif; ?>

                    <input type="hidden" id="hidden_post_type" name="post_type" value="docs"/>
                </div>
            </div>
			<?php include( 'Search/ajax-sarch-results.php' ); ?>
			<?php include( 'Search/keywords.php' ); ?>
        </form>
		<?php
	}
}