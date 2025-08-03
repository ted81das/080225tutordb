<?php
namespace BBPCorePro\inc\template_library\templates;

defined('ABSPATH') || die();

class Init {
    private static $instance = null;

    public static function url(){
       return trailingslashit(plugin_dir_url( __FILE__ ));
    }

    public static function dir(){
        return trailingslashit(plugin_dir_path( __FILE__ ));
    }

    public static function version(){
        return '1.0.0';
    }

    public function init()
    {
        
        add_action( 'wp_enqueue_scripts', function() {       
            wp_enqueue_style( "bbpc_template-front", self::url() . 'assets/css/template-frontend.min.css' , ['elementor-frontend'], self::version() );
            } 
        );
        
        add_action( 'elementor/editor/after_enqueue_scripts', function() {     
            wp_enqueue_style( "bbpc-load-template-editor", self::url() . 'assets/css/template-library.min.css' , ['elementor-editor'], self::version() );
            wp_enqueue_script("bbpc-load-template-editor", self::url() . 'assets/js/template-library.min.js', ['elementor-editor'], self::version(), true);
            $pro = get_option('__validate_author_dtaddons__', false);
            
            $localize_data = [
                'hasPro'                          => !$pro ? false : true,
                'templateLogo'                    => BBPC_TEMPLATE_LOGO_SRC,
                'i18n' => [
                    'templatesEmptyTitle'       => esc_html__( 'No Templates Found', 'bbp-core-pro' ),
                    'templatesEmptyMessage'     => esc_html__( 'Try different category or sync for new templates.', 'bbp-core-pro' ),
                    'templatesNoResultsTitle'   => esc_html__( 'No Results Found', 'bbp-core-pro' ),
                    'templatesNoResultsMessage' => esc_html__( 'Please make sure your search is spelled correctly or try a different words.', 'bbp-core-pro' ),
                ],
                'tab_style' => json_encode(self::get_tabs()),
                'default_tab' => 'page'
            ];
            wp_localize_script(
                'bbpc-load-template-editor',
                'bbpcEditor',
                $localize_data
            );

            },
            999
        );

        add_action( 'elementor/preview/enqueue_styles', function(){
            $data = '.elementor-add-new-section .bbpc_templates_add_button {
                background-color: #6045bc;
                margin-left: 5px;
                font-size: 18px;
                vertical-align: bottom;
            }
            ';
            wp_add_inline_style( 'bbpc-template-front', $data );
        }, 999 );
    }

    public static function get_tabs() {
        return apply_filters('bbpc_editor/templates_tabs', [
            'section' => [ 'title' => 'BBP Core Blocks'],
            'page' => [ 'title' => 'BBP Core Pages'],
        ]);
    }
    public static function instance(){
        if( is_null(self::$instance) ){
            self::$instance = new self();
        }
        return self::$instance;
    }
}

