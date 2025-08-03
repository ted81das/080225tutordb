<?php
namespace admin\menu;

defined( 'ABSPATH' ) || exit();

class Approve_Topic {
	public function __construct() {
		add_action( 'admin_init', [ $this, 'approve_spam_topic' ] );
		add_action( 'admin_init', [ $this, 'approve_pending_reply' ] );
	}

	public function approve_spam_topic() {
		$approval_request = $_GET['bbpc_approve_topic_id'] ?? false;

		if ( $approval_request ) {
			bbp_approve_topic( (int) $approval_request );
			wp_safe_redirect( admin_url( 'admin.php?page=bbp-core' ) );
		}
	}

	public function approve_pending_reply() {
		$approval_request = $_GET['bbpc_approve_reply_id'] ?? false;

		if ( $approval_request ) {
			bbp_approve_reply( (int) $approval_request );
			wp_safe_redirect( admin_url( 'admin.php?page=bbp-core' ) );
		}
	}
}

new Approve_Topic();


