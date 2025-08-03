<?php
namespace admin\Elementor;

use admin\Pro_Widget_Map;

class BBP_Widgets {

    public function __construct() {

        // Register Widgets
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

        // Register Category
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );        
	    add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_elementor_editor_assets' ] );

        // Register Elementor Preview Editor Scripts
        $currentTheme = wp_get_theme()->get( 'Name' )  == 'Ama' ? true : false;
        $returnType   = bbpc_is_premium() == true ? true : $currentTheme;
        if ( $returnType != 1 ) {
            add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        }
    }


    /**
     * @return void
     *
     */
    public function enqueue_editor_scripts() {

        wp_enqueue_script('bbpc-el-editor', BBPC_ASSETS . 'admin/js/bbpc-el-editor.js', [], '1.0.0', true);

        $localize_data = [];
        $pro_widget_map = new Pro_Widget_Map();
        $localize_data['promotional_widgets'] = $pro_widget_map->get_pro_widget_map();
        wp_localize_script('bbpc-el-editor', 'BbpcConfig', $localize_data);
    }


    // Register Widgets
    public function register_widgets( $widgets_manager ) {

        $theme = wp_get_theme();

        // Include Widget files
        if ( $theme == 'Ama' || bbpc_is_premium() ) {
            require_once( __DIR__ . '/Single_forum.php' );
            require_once( __DIR__ . '/Forum_Ajax.php' );
            require_once( __DIR__ . '/Forum_posts.php' );
            require_once( __DIR__ . '/Forums.php' );
            require_once( __DIR__ . '/Forum_Tab.php' );
            require_once( __DIR__ . '/Search.php' );

            $widgets_manager->register( new Single_forum() );
            $widgets_manager->register( new Forum_Ajax() );
            $widgets_manager->register( new Forum_posts() );
            $widgets_manager->register( new Forums() );
            $widgets_manager->register( new Forum_Tab() );
            $widgets_manager->register( new Search() );
        }
    }
    
    // Register category
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'bbp-core', [
                'title' => __( 'BBP Core', 'bbp-core' ),
            ]
        );
    }
    
	function register_elementor_editor_assets() {
		wp_enqueue_style( 'bbpc-el-editor', BBPC_ASSETS . 'css/elementor-editor.css' );
	}
}