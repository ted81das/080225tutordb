<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Process a vote.
add_action( 'wp_ajax_bbpress_post_vote_link_clicked', 'bbpress_post_add_vote' );
add_action( 'wp_ajax_nopriv_bbpress_post_vote_link_clicked', 'bbpress_post_add_vote' );

function bbpress_post_add_vote() {
	header( 'Content-type: application/json' );
	$post_id = (int) $_POST['post_id'];

	// View only for visitors.
	if ( ! is_user_logged_in() && apply_filters( 'bbp_voting_disable_voting_for_visitors', false ) ) {
		die( json_encode( [ 'error' => 'Voting is disabled for visitors.' ] ) );
	}

	// View only for closed topics
	$topic_id     = bbp_get_reply_topic_id( $post_id );
	$topic_status = get_post_status( $topic_id );
	if ( $topic_status == 'closed' && apply_filters( 'bbp_voting_disable_voting_on_closed_topic', false ) ) {
		die( json_encode( [ 'error' => 'Voting is disabled on closed topics.' ] ) );
	}
	// View only for author of post
	if ( is_user_logged_in() && apply_filters( 'bbp_voting_disable_author_vote', false ) && get_post_field( 'post_author', $post_id ) == get_current_user_ID() ) {
		die( json_encode( [ 'error' => 'Voting is disabled on for the author.' ] ) );
	}
	// Direction
	$direction = (int) $_POST['direction'];
	$direction = in_array( $direction, [ 1, -1 ] ) ? $direction : 0; // Enforce 1 or -1
	// $voting_cookie = unserialize($_COOKIE['bbp_voting']);
	$voting_log   = get_post_meta( $post_id, 'bbp_voting_log', true );
	$voting_log   = is_array( $voting_log ) ? $voting_log : []; // Set up new array
	$client_ip    = $_SERVER['REMOTE_ADDR'];
	$identifier   = is_user_logged_in() ? get_current_user_id() : $client_ip;
	$admin_bypass = current_user_can( 'administrator' ) && apply_filters( 'bbp_voting_admin_bypass', false );
	$remove_vote  = false;
	$reverse_vote = false;
	// Admin bypass skips the restriction checks
	if ( ! $admin_bypass ) {
		// Catch user voted already (by cookie)
		// if(isset($voting_cookie[$post_id])) {
		// echo 'Not allowed to vote twice';
		// exit;
		// } else {
			// Catch user voted already (by user ID or IP)
		if ( array_key_exists( $identifier, $voting_log ) ) {
			// Identifier found
			if ( $voting_log[ $identifier ] == $direction ) {
				// Voting again in the same direction
				$remove_vote = true;
			} elseif ( $voting_log[ $identifier ] == $direction * -1 ) {
				// Changing the vote in different direction
				$reverse_vote = true;
			} else {
				// Changing vote from 0
			}
		}
		// }
	}
	// All good, add the user's vote
	// But first get all the data
	$score = (int) get_post_meta( $post_id, 'bbp_voting_score', true );
	$ups   = $ups_og = (int) get_post_meta( $post_id, 'bbp_voting_ups', true );
	$downs = $downs_og = (int) get_post_meta( $post_id, 'bbp_voting_downs', true );
	if ( $direction > 0 ) {
		// Up vote
		if ( $remove_vote ) {
			$ups   = $ups - 1;
			$score = $score - 1;
		} else {
			$ups   = $ups + 1;
			$score = $score + 1;
		}
		if ( $reverse_vote ) {
			$downs = $downs + 1;
			$score = $score + 1;
		}
	} elseif ( $direction < 0 ) {
		// Down vote
		if ( $remove_vote ) {
			$downs = $downs + 1;
			$score = $score + 1;
		} else {
			$downs = $downs - 1;
			$score = $score - 1;
		}
		if ( $reverse_vote ) {
			$ups   = $ups - 1;
			$score = $score - 1;
		}
	}
	// Update the score
	update_post_meta( $post_id, 'bbp_voting_score', $score );
	// Update the ups and downs if needed
	if ( $ups !== $ups_og ) {
		update_post_meta( $post_id, 'bbp_voting_ups', $ups );
	}
	if ( $downs !== $downs_og ) {
		update_post_meta( $post_id, 'bbp_voting_downs', $downs );
	}
	// Hook for additional features like weighted score calculation
	do_action( 'bbp_voting_process_score_on_vote', $post_id, $ups, $downs );
	// Log the user's ID or IP
	$real_direction            = $remove_vote ? 0 : $direction;
	$voting_log[ $identifier ] = $real_direction;
	update_post_meta( $post_id, 'bbp_voting_log', $voting_log );
	// Set the cookie
	// $voting_cookie[$post_id] = true;
	// setcookie('bbp_voting', serialize($voting_cookie), time() + (86400 * 30 * 365), '/');
	do_action( 'bbp_voting_voted', $post_id, $real_direction, $score, $identifier );
	echo json_encode(
		[
			'score'     => $score,
			'direction' => $real_direction,
			'ups'       => $ups,
			'downs'     => $downs,
		]
	);
	exit;
}
