<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GDATTAdmin {
	private $admin_plugin = false;

	function __construct() {
		add_action( 'after_setup_theme', [ $this, 'load' ] );
	}

	public static function instance() {
		static $instance = false;

		if ( $instance === false ) {
			$instance = new GDATTAdmin();
		}

		return $instance;
	}

	public function load() {
		add_filter( 'plugin_action_links', [ $this, 'plugin_actions' ], 10, 2 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_links' ], 10, 2 );
	}

	public function plugin_actions( $links, $file ) {
		if ( $file == 'gd-bbpress-attachments/gd-bbpress-attachments.php' ) {
			$settings_link = '<a href="edit.php?post_type=forum&page=gdbbpress_attachments">' . __( 'Settings', 'bbp-core' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function plugin_links( $links, $file ) {
		if ( $file == 'gd-bbpress-attachments/gd-bbpress-attachments.php' ) {
			$links[] = '<a target="_blank" style="color: #cc0000; font-weight: bold;" href="https://plugins.dev4press.com/gd-bbpress-toolbox/">' . __( 'Upgrade to GD bbPress Toolbox Pro', 'bbp-core' ) . '</a>';
		}

		return $links;
	}

	public function load_admin_page() {
		$screen = get_current_screen();

		$screen->set_help_sidebar(
			'
            <p><strong>Dev4Press:</strong></p>
            <p><a target="_blank" href="https://www.dev4press.com/">' . __( 'Website', 'bbp-core' ) . '</a></p>
            <p><a target="_blank" href="https://twitter.com/milangd">' . __( 'On Twitter', 'bbp-core' ) . '</a></p>
            <p><a target="_blank" href="https://facebook.com/dev4press">' . __( 'On Facebook', 'bbp-core' ) . '</a></p>'
		);

		$screen->add_help_tab(
			[
				'id'      => 'gdpt-screenhelp-help',
				'title'   => __( 'Get Help', 'bbp-core' ),
				'content' => '<h5>' . __( 'General Plugin Information', 'bbp-core' ) . '</h5>
                <p><a href="https://plugins.dev4press.com/gd-bbpress-attachments/" target="_blank">' . __( 'Home Page on Dev4Press.com', 'bbp-core' ) . '</a> | 
                <a href="https://wordpress.org/plugins/gd-bbpress-attachments/" target="_blank">' . __( 'Home Page on WordPress.org', 'bbp-core' ) . '</a></p> 
                <h5>' . __( 'Getting Plugin Support', 'bbp-core' ) . '</h5>
                <p><a href="https://support.dev4press.com/forums/forum/plugins-free/gd-bbpress-attachments/" target="_blank">' . __( 'Support Forum on Dev4Press.com', 'bbp-core' ) . '</a> | 
                <a href="https://wordpress.org/support/plugin/gd-bbpress-attachments" target="_blank">' . __( 'Support Forum on WordPress.org', 'bbp-core' ) . '</a> </p>',
			]
		);

		$screen->add_help_tab(
			[
				'id'      => 'gdpt-screenhelp-website',
				'title'   => 'Dev4Press',
				'sfc',
				'content' => '<p>' . __( 'On Dev4Press website you can find many useful plugins, themes and tutorials, all for WordPress. Please, take a few minutes to browse some of these resources, you might find some of them very useful.', 'bbp-core' ) . '</p>
                <p><a href="https://plugins.dev4press.com/plugins/" target="_blank"><strong>' . __( 'Plugins', 'bbp-core' ) . '</strong></a> - ' . __( 'We have more than 10 plugins available, some of them are commercial and some are available for free.', 'bbp-core' ) . '</p>
                <p><a href="https://support.dev4press.com/kb/" target="_blank"><strong>' . __( 'Knowledge Base', 'bbp-core' ) . '</strong></a> - ' . __( 'Premium and free tutorials for our plugins themes, and many general and practical WordPress tutorials.', 'bbp-core' ) . '</p>
                <p><a href="https://support.dev4press.com/forums/" target="_blank"><strong>' . __( 'Support Forums', 'bbp-core' ) . '</strong></a> - ' . __( 'Premium support forum for all with valid licenses to get help. Also, report bugs and leave suggestions.', 'bbp-core' ) . '</p>',
			]
		);
	}

	public function menu_attachments() {
		$options     = BBPCATTCore::instance()->o;
		$_user_roles = bbpc_bbpress_get_user_roles();

		include BBPCATTACHMENTS_PATH . 'forms/panels.php';
	}
}
