<?php
namespace features;

class bbp_attachments {
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'inclue_attachment_files'], 20 );
	}

	public function inclue_attachment_files() {
		if ( ! defined( 'BBPC_ATTACHMENT' ) ) {
			define( 'BBPC_ATTACHMENT', 'activate_plugins' );
		}

		$cfile = dirname( __FILE__ );
		require_once $cfile . '/bbpc_attachments/code/defaults.php';
		require_once $cfile . '/bbpc_attachments/code/shared.php';
		require_once $cfile . '/bbpc_attachments/code/sanitize.php';

		require_once $cfile . '/bbpc_attachments/code/class.php';
		require_once $cfile . '/bbpc_attachments/code/public.php';

		\BBPCATTCore::instance();
	}
}

//TODO: Make multiple file attachment features
//TODO: Show progress and cross button like gmail
//TODO: Show file size of each upload
//TODO: Add icon for every feature, like gmail
//TODO: Do caption feature, take caption from title
//TODO: Add lightbox in pro plugin, lock the feature lightbox