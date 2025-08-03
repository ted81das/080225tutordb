<?php
// AJAX handler to create a forum
add_action( 'wp_ajax_bbp_create_forum', 'bbp_create_forum' );
function bbp_create_forum() {
    
	// Check the nonce
	check_ajax_referer( 'bbpc-admin-nonce', 'bbpc_nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized request.' );
    }
    
    $bbp_forum_title = isset( $_POST['bbp_forum_title'] ) ? sanitize_text_field( $_POST['bbp_forum_title'] ) : '';
    if ( empty( $bbp_forum_title ) ) {
        wp_send_json_error( 'Forum title cannot be empty.' );
    }

    $args = [
        'post_type'   => 'forum',
        'post_parent' => 0
    ];

    $query = new \WP_Query( $args );
    $total = $query->found_posts;
    $add   = 2;
    $order = $total + $add;

    // Insert the post
    $post_id = wp_insert_post( array(
        'post_title'   => $bbp_forum_title,
        'post_parent'  => 0,
        'post_content' => '',
        'post_type'    => 'forum',
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id(),
        'menu_order'   => $order,
    ) );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( 'Failed to create the forum.' );
    }

    // Return success
    wp_send_json_success( 'Forum created successfully.' );
}