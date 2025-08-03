<?php
// AJAX handler to create a topic
add_action( 'wp_ajax_bbp_create_topic', 'bbp_create_topic' );
function bbp_create_topic() {
	
	// Check the nonce
	check_ajax_referer( 'bbpc-admin-nonce', 'bbpc_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized request.' );
    }

	$bbp_topic_title = isset( $_POST['bbp_topic_title'] ) ? sanitize_text_field( $_POST['bbp_topic_title'] ) : '';
    if ( empty( $bbp_topic_title ) ) {
        wp_send_json_error( 'Topic title cannot be empty.' );
    }

	$forum_id 	 	= ! empty ( $_POST['forum_id'] ) ? absint( $_POST['forum_id'] ) : 0;	
	$topic_author 	= bbp_get_current_user_id();

	$parent_item = get_children( [
		'post_parent' => $forum_id,
		'post_type'   => 'topic'
	] );

	$add   = 2;
	$order = count( $parent_item );
	$order = $order + $add;

	// Create topic object
	$topic_data = apply_filters( 'bbp_new_topic_pre_insert', array(
		'post_author'  => $topic_author,
		'post_title'   => $bbp_topic_title,
		'post_parent'  => $forum_id,
		'post_content' => '',
		'post_type'	   => bbp_get_topic_post_type(),
		'post_status'  => 'publish',
		'menu_order'   => $order
	) );		
	
	$topic_id = wp_insert_post( $topic_data, true );

	if ( is_wp_error( $topic_id ) ) {
		wp_send_json_error( 'Failed to create the topic.' );
	}
	
	do_action( 'bbp_new_topic', $topic_id, $forum_id, '', $topic_author );
	wp_send_json_success( 'Topic created successfully.' );
}