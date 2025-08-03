<?php
namespace features;

class bbp_voting {
	function __construct() {
		$this->include_files();
		$this->load_functions();
	}

	public function include_files() {
		define( 'BBPC_VOTE_PATH', plugin_dir_path( __FILE__ ) . 'bbp_voting/' );

		// The plugin basename, "folder/file.php"
		$plugin = plugin_basename( __FILE__ );

		// Helpers are helpful.
		require_once BBPC_VOTE_PATH . 'helpers.php';

	}

	public function load_functions() {
		// Require only the appropriate files.
		if ( wp_doing_ajax() ) {
			// Ajax.
			require_once BBPC_VOTE_PATH . 'ajax.php';
		} elseif ( is_admin() ) {
			require_once BBPC_VOTE_PATH . 'metabox.php';
		} else {
			// Frontend.
			require_once BBPC_VOTE_PATH . 'frontend.php';
		}

	}
}


