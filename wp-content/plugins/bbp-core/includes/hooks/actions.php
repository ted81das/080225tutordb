<?php
// Hooks.
add_action( 'bbpc-resolved-topics', 'bbpc_resolved_topics', 10, 2 );

// functions
function bbpc_resolved_topics( $content = '', $topic_id = 0 ) {

	if ( bbpc_get_opt( 'is_solved_topics', true ) ) {
		
		if ( empty( $topic_id ) ) {
			$topic_id = bbp_get_topic_id( $topic_id );
		}

		$open            = '';
		$close           = '';
		$resolved        = '';
		$has_best_answer = '';

		$obj = features\bbp_solved_topic();

		if ( $obj->is_solved( $topic_id ) || $obj->has_best_answer( $topic_id ) ) {
			$open  = '<div class="solved-topic-bar">';
			$close = '</div>';
		}

		if ( $obj->is_solved( $topic_id ) ) {
			$resolved = '<span class="accepted-ans-mark"><i class="icon_check_alt"></i> ' . esc_html__( 'Resolved', 'ama-core' ) . '</span>';
		}

		$content = $content . $open . $resolved . $has_best_answer . $close;

		$allowed_html = [
			'div'  => [ 'class' => [] ],
			'span' => [ 'class' => [] ],
			'i'    => [ 'class' => [] ],
		];

		echo wp_kses( $content, $allowed_html );
	}
}