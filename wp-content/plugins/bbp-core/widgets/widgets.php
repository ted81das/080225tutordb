<?php
// Require widget files
require plugin_dir_path(__FILE__) . '/forum-info/Forum_Information.php';

// Register Widgets
add_action( 'widgets_init', function() {
    register_widget( 'BBPCore\WpWidgets\Forum_Information');
    
    add_action('admin_enqueue_scripts', function($hook){
        if ( $hook === 'widgets.php' ) {
            wp_enqueue_style( 'bbp-core-wp-widget', BBPC_ASSETS . 'admin/css/wp-widget.css' );
        }
    });
        
});