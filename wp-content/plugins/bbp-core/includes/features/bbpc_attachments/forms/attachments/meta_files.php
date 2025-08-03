<?php
$attachments = bbpc_get_post_attachments( $post_ID );
if ( empty( $attachments ) ) {
	_e( 'No attachments here.', 'bbp-core' );
} else {
	echo '<ul style="list-style: decimal outside; margin-left: 1.5em;">';
	foreach ( $attachments as $attachment ) {
		$file     = get_attached_file( $attachment->ID );
		$filename = pathinfo( $file, PATHINFO_BASENAME );
		echo '<li>' . $filename;
		echo ' - <a href="' . admin_url( 'media.php?action=edit&attachment_id=' . $attachment->ID ) . '">' . __( 'edit', 'bbp-core' ) . '</a>';
		echo '</li>';
	}
	echo '</ul>';
}

if ( ( bbpc_is_user_admin() || bbpc_is_user_moderator() ) ) {
	$errors = get_post_meta( $post_ID, '_bbp_attachment_upload_error' );
	if ( ! empty( $errors ) ) {
		echo '<h4>' . __( 'Upload Errors', 'bbp-core' ) . ':</h4>';
		echo '<ul style="list-style: decimal outside; margin-left: 1.5em;">';
		foreach ( $errors as $error ) {
			echo '<li><strong>' . esc_html( $error['file'] ) . '</strong>:<br/>' . __( $error['message'], 'bbp-core' ) . '</li>';
		}
		echo '</ul>';
	}
}
