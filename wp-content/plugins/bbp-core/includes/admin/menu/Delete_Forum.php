<?php
// AJAX handler to delete a forum
add_action( 'wp_ajax_bbp_delete_forum', 'bbp_delete_forums' );
function bbp_delete_forums() {
	
	// Check the nonce
	check_ajax_referer( 'bbpc-admin-nonce', 'bbpc_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized request.' );
    }

	$forum_id = ! empty ( $_POST['forum_id'] ) ? absint( $_POST['forum_id'] ) : 0;	
    if ( empty( $forum_id ) ) {
        wp_send_json_error( 'Forum ID cannot be empty.' );
    }

	$children = get_children( [
		'post_parent' => $forum_id,
		'post_type'   => 'topic',
		'orderby'     => 'menu_order',
		'order'       => 'asc',
	] );

	$topics        = '';
	$topic_replies = '';

	if ( is_array( $children ) ) :
		foreach ( $children as $child ) :
			$replies = get_children( [
				'post_parent' => $child->ID,
				'post_type'   => 'reply',
				'post_status' => [ 'publish', 'draft' ],
			] );

			$topics .= $child->ID . ',';
			if ( is_array( $replies ) ) :
				foreach ( $replies as $reply ) :
					$topic_replies .= $reply->ID . ',';
				endforeach;
			endif;
		endforeach;
	endif;

	$forum_posts		= $forum_id . ',' . $topic_replies . $topics;
	$forum_posts		= explode( ',', $forum_posts );
	$forum_posts_int	= array_map( 'intval', $forum_posts );
	foreach ( $forum_posts_int as $deletes ) {
		wp_trash_post( $deletes, true );
	}
	wp_send_json_success( 'Forum Deleted successfully.' );
	wp_die();
}