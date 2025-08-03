<?php
// AJAX handler to delete a topic
add_action( 'wp_ajax_bbp_delete_topic', 'delete_topic' );
function delete_topic() {

		// Check the nonce
	check_ajax_referer( 'bbpc-admin-nonce', 'bbpc_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized request.' );
    }
	
	$topic_id = ! empty ( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;	
    if ( empty( $topic_id ) ) {
        wp_send_json_error( 'Topic ID cannot be empty.' );
    }
	
	$replies = get_children( [
		'post_parent' 	=> $topic_id,
		'post_type'   	=> 'reply',
		'orderby'     	=> 'menu_order',
		'order'       	=> 'asc',
	] );

	$reply_ids = '';
	if ( is_array( $replies ) ) :
		foreach ( $replies as $reply ) :
			$reply_ids .= $reply->ID . ',';
		endforeach;
	endif;

	$topic_posts		= $topic_id . ',' . $reply_ids;
	$topic_posts		= explode( ',', $topic_posts );
	$topic_posts_int	= array_map( 'intval', $topic_posts );
	foreach ( $topic_posts_int as $delete_topic ) {
		wp_trash_post( $delete_topic, true ); 
	}
	
	wp_send_json_success( 'Topic Deleted successfully.' );
	wp_die();
}