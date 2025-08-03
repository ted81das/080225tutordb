<?php
namespace admin\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use WP_Query;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Single_forum extends Widget_Base
{

    public function get_name ()
    {
        return 'ama_single_forum';
    }

    public function get_title ()
    {
        return __('BBPC Single Forum', 'bbp-core');
    }

    public function get_icon ()
    {
        return 'bbpc_icon_ama_single_forum';
    }

    public function get_categories ()
    {
        return [ 'bbp-core' ];
    }

    public function get_style_depends ()
    {
        return [ 'bbpc-el-widgets' ];
    }

    protected function register_controls ()
    {

        //-------- Select Style ---------- //
        $this->start_controls_section(
            'style_sec',
            [
                'label' => esc_html__('Preset Skins', 'bbp-core'),
            ]
        );

        $this->add_control(
            'forum_id', [
                'label' => esc_html__('Select Forum', 'bbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => bbp_core_get_posts('forum')
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__('Forums Style', 'bbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    '1' => [
                        'icon' => 'single_forum_1',
                        'title' => esc_html__('01 : Single Forum With Topics', 'bbp-core'),
                    ],
                    '2' => [
                        'icon' => 'single_forum_2',
                        'title' => esc_html__('02 : Single Forum', 'bbp-core'),
                    ],
                ],
                'default' => '1',
                'toggle' => false,
            ]
        );

        $this->end_controls_section(); // End Style


        $this->start_controls_section(
            'forum_thumb', [
                'label' => __('Thumbnail', 'bbp-core'),
            ]
        );

        $this->add_control(
            'cover_image', [
                'label' => __('Custom Cover Image', 'bbp-core'),
                'type' => Controls_Manager::MEDIA,
                'description' => __('If this is not set, the featured image will be used by default', 'bbp-core')
            ]
        );

        $this->end_controls_section();

        // --- Filter Options
        $this->start_controls_section(
            'filter_opt', [
                'label' => __('Filter Options', 'bbp-core'),
            ]
        );

        $this->add_control(
            'ppp', [
                'label' => esc_html__('Topics', 'bbp-core'),
                'description' => esc_html__('Maximum number of topics.', 'bbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3
            ]
        );

        $this->add_control(
            'order', [
                'label' => esc_html__('Order', 'bbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'ASC' => 'ASC',
                    'DESC' => 'DESC'
                ],
                'default' => 'ASC'
            ]
        );

        $this->add_control(
            'word_length',
            [
                'label' => __('Number of Words', 'bbp-core'),
                'type' => Controls_Manager::NUMBER,
                'description' => __('Number of words to show as forum content', 'bbp-core'),
                'default' => 12
            ]
        );

        $this->add_control(
            'read_more',
            [
                'label' => esc_html__('Read More Text', 'bbp-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __('View All', 'bbp-core'),
            ]
        );

        $this->end_controls_section();

        /**============== Background shape Image =====================**/
        $this->start_controls_section(
            'single_forum_style', [
                'label' => __('Single Forum Style', 'bbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'selector' => '{{WRAPPER}} .forum-card, {{WRAPPER}} .forum-with-topics .topic-table .topic-heading, {{WRAPPER}} .forum-with-topics .topic-table .topic-contents',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'bbp-core'),
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .forum-with-topics .topic-table .topic-contents .title h3, {{WRAPPER}} .forum-card .card-title h3',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'bbp-core'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'after',
                'selectors' => [
                    '{{WRAPPER}} .forum-with-topics .topic-table .topic-contents .title h3' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .forum-card .card-title h3' => 'color: {{VALUE}}'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'label' => __('Excerpt Typography', 'bbp-core'),
                'selector' => '{{WRAPPER}} .forum-with-topics .topic-table .topic-contents .title p, {{WRAPPER}} .forum-card .card-body',
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Excerpt Color', 'bbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .forum-with-topics .topic-table .topic-contents .title p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .forum-card .card-body' => 'color: {{VALUE}}'
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render (): void
    {
        $settings = $this->get_settings();
        $forum_id = !empty($settings[ 'forum_id' ]) ? $settings[ 'forum_id' ] : '';
        $cover_image = $settings[ 'cover_image' ];
        $post_thumbnail_url = !empty($cover_image[ 'url' ]) ? $cover_image[ 'url' ] : get_the_post_thumbnail_url($forum_id);

        $topics = new WP_Query(array(
            'post_type' => bbp_get_topic_post_type(),
            'order' => !empty($settings['order']) ? $settings['order'] : 'DESC',
            'posts_per_page' => !empty($settings['ppp']) ? $settings['ppp'] : 3,
            'post_parent' => $forum_id,
        ));

        if ($forum_id) {
            include "inc/single-forum/single-forum-{$settings['style']}.php";
        } else { ?>
            <div class="alert alert-warning" role="alert">
                <?php _e('Please select a forum.', 'bbp-core'); ?>
            </div>
            <?php
        }
    }
}