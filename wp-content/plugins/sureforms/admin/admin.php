<?php
/**
 * Admin Class.
 *
 * @package sureforms.
 */

namespace SRFM\Admin;

use SRFM\Admin\Views\Entries_List_Table;
use SRFM\Admin\Views\Single_Entry;
use SRFM\Inc\AI_Form_Builder\AI_Helper;
use SRFM\Inc\Database\Tables\Entries;
use SRFM\Inc\Helper;
use SRFM\Inc\Onboarding;
use SRFM\Inc\Post_Types;
use SRFM\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Admin handler class.
 *
 * @since 0.0.1
 */
class Admin {
	use Get_Instance;

	/**
	 * Dashboard widget entries data.
	 *
	 * @var array
	 * @since 1.9.1
	 */
	private $dashboard_widget_data = [];

	/**
	 * Class constructor.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_menu', [ $this, 'add_new_form' ] );
		add_action( 'admin_menu', [ $this, 'add_suremail_page' ] );
		if ( ! Helper::has_pro() ) {
			add_action( 'admin_menu', [ $this, 'add_upgrade_to_pro' ] );
			add_action( 'admin_footer', [ $this, 'add_upgrade_to_pro_target_attr' ] );
		}

		add_filter( 'plugin_action_links', [ $this, 'add_settings_link' ], 10, 2 );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_head', [ $this, 'enqueue_header_styles' ] );
		add_filter( 'admin_body_class', [ $this, 'admin_template_picker_body_class' ] );

		// this action is used to restrict Spectra's quick action bar on SureForms CPTS.
		add_action( 'uag_enable_quick_action_sidebar', [ $this, 'restrict_spectra_quick_action_bar' ] );

		add_action( 'current_screen', [ $this, 'enable_gutenberg_for_sureforms' ], 100 );
		add_action( 'admin_notices', [ $this, 'srfm_pro_version_compatibility' ] );
		add_action( 'admin_notices', [ $this, 'add_smtp_warning_notice' ] );

		// Handle entry actions.
		add_action( 'admin_init', [ $this, 'handle_entry_actions' ] );
		add_action( 'admin_notices', [ Entries_List_Table::class, 'display_bulk_action_notice' ] );

		// This action enqueues translations for NPS Survey library.
		// A better solution will be required from library to resolve plugin conflict.
		add_action(
			'admin_footer',
			static function() {
				Helper::register_script_translations( 'nps-survey-script' );
			},
			1000
		);
		// Enfold theme compatibility to enable block editor for SureForms post type.
		add_filter( 'avf_use_block_editor_for_post', [ $this, 'enable_block_editor_in_enfold_theme' ] );

		// Add action links to the plugin page.
		add_filter( 'plugin_action_links_' . SRFM_BASENAME, [ $this, 'add_action_links' ] );
		// Check if admin notification is enabled and add entries badge.
		$general_options       = get_option( 'srfm_general_settings_options', [] );
		$admin_notification_on = isset( $general_options['srfm_admin_notification'] ) ? (bool) $general_options['srfm_admin_notification'] : true;

		if ( $admin_notification_on ) {
			add_action( 'admin_menu', [ $this, 'maybe_add_entries_badge' ], 99 );
		}
		add_filter( 'wpforms_current_user_can', [ $this, 'disable_wpforms_capabilities' ], 10, 3 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_pointer' ] );
		// Ajax callbacks for wp-pointer functionality.
		add_action( 'wp_ajax_should_show_pointer', [ $this, 'pointer_should_show' ] );
		add_action( 'wp_ajax_sureforms_dismiss_pointer', [ $this, 'pointer_dismissed' ] );
		add_action( 'wp_ajax_sureforms_accept_cta', [ $this, 'pointer_accepted_cta' ] );

		// Register dashboard widget only if there are recent entries.
		add_action( 'admin_init', [ $this, 'maybe_register_dashboard_widget' ] );
	}

	/**
	 * Show action on plugin page.
	 *
	 * @param  array $links links.
	 * @return array
	 * @since 1.4.2
	 */
	public function add_action_links( $links ) {
		if ( ! Helper::has_pro() ) {
			// Display upsell link if SureForms Pro is not installed.
			$upsell_link = add_query_arg(
				[
					'utm_medium' => 'plugin-list',
				],
				Helper::get_sureforms_website_url( 'pricing' )
			);
			$links[]     = '<a href="' . esc_url( $upsell_link ) . '" target="_blank" rel="noreferrer" class="sureforms-plugins-go-pro">' . esc_html__( 'Get SureForms Pro', 'sureforms' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Enable block editor in Enfold theme for SureForms post type.
	 *
	 * @param bool $use_block_editor Whether to use block editor.
	 * @since 1.3.1
	 */
	public function enable_block_editor_in_enfold_theme( $use_block_editor ) {
		// if SureForms form post type then return true.
		if ( SRFM_FORMS_POST_TYPE === get_current_screen()->post_type ) {
			return true;
		}
		return $use_block_editor;
	}

	/**
	 * Enable Gutenberg for SureForms associated post types.
	 *
	 * @since 0.0.10
	 */
	public function enable_gutenberg_for_sureforms() {
		/**
		 * Check if the classic editor is enabled from Classic Editor plugin settings or Divi settings.
		 */
		if ( 'block' === get_option( 'classic-editor-replace' ) || 'on' === get_option( 'et_enable_classic_editor' ) ) {
			return;
		}

		$srfm_post_types = apply_filters( 'srfm_enable_gutenberg_post_types', [ SRFM_FORMS_POST_TYPE ] );

		if ( in_array( get_current_screen()->post_type, $srfm_post_types, true ) ) {
			add_filter( 'use_block_editor_for_post_type', '__return_true', 110 );
			add_filter( 'gutenberg_can_edit_post_type', '__return_true', 110 );
		}
	}

	/**
	 * Sureforms editor header styles.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_header_styles() {
		$current_screen = get_current_screen();
		$file_prefix    = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? '' : '.min';
		$dir_name       = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? 'unminified' : 'minified';

		$css_uri = SRFM_URL . 'assets/css/' . $dir_name . '/';

		/* RTL */
		if ( is_rtl() ) {
			$file_prefix .= '-rtl';
		}

		if ( 'sureforms_form' === $current_screen->id ) {
			wp_enqueue_style( SRFM_SLUG . '-editor-header-styles', $css_uri . 'header-styles' . $file_prefix . '.css', [], SRFM_VER );
		}
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function add_menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$capability = 'manage_options';
		$menu_slug  = 'sureforms_menu';

		$logo = file_get_contents( plugin_dir_path( SRFM_FILE ) . 'images/icon.svg' );
		add_menu_page(
			__( 'SureForms', 'sureforms' ),
			__( 'SureForms', 'sureforms' ),
			'edit_others_posts',
			$menu_slug,
			static function () {
			},
			'data:image/svg+xml;base64,' . base64_encode( $logo ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			30
		);

		// Add the Dashboard Submenu.
		add_submenu_page(
			$menu_slug,
			__( 'Dashboard', 'sureforms' ),
			__( 'Dashboard', 'sureforms' ),
			$capability,
			$menu_slug,
			[ $this, 'render_dashboard' ]
		);
	}

	/**
	 * Add Settings page.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function settings_page() {
		$callback = [ $this, 'settings_page_callback' ];
		add_submenu_page(
			'sureforms_menu',
			__( 'Settings', 'sureforms' ),
			__( 'Settings', 'sureforms' ),
			'edit_others_posts',
			'sureforms_form_settings',
			$callback
		);

		// Get the current submenu page.
		$submenu_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $_GET['page'] does not provide nonce.

		if ( ! isset( $_GET['tab'] ) && 'sureforms_form_settings' === $submenu_page ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $_GET['page'] does not provide nonce.
			wp_safe_redirect( admin_url( 'admin.php?page=sureforms_form_settings&tab=general-settings' ) );
			exit;
		}
	}

	/**
	 * Open to Upgrade to Pro submenu link in new tab.
	 *
	 * @return void
	 * @since 1.6.1
	 */
	public function add_upgrade_to_pro_target_attr() {
		?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function () {
				// Upgrade link handler.
				// IMPORTANT: If this URL changes, also update it in the `add_upgrade_to_pro` function.
				const upgradeLink = document.querySelector('a[href*="https://sureforms.com/upgrade"]');
				if (upgradeLink) {
					upgradeLink.addEventListener('click', e => {
						e.preventDefault();
						window.open(upgradeLink.href, '_blank');
					});
				}
			});
		</script>
		<?php
	}

	/**
	 * Add Upgrade to pro menu item.
	 *
	 * @return void
	 * @since 1.6.1
	 */
	public function add_upgrade_to_pro() {
		// The url used here is used as a selector for css to style the upgrade to pro submenu.
		// If you are changing this url, please make sure to update the css as well.
		$upgrade_url = add_query_arg(
			[
				'utm_medium' => 'submenu_link_upgrade',
			],
			Helper::get_sureforms_website_url( 'upgrade' )
		);

		add_submenu_page(
			'sureforms_menu',
			__( 'Upgrade', 'sureforms' ),
			__( 'Upgrade', 'sureforms' ),
			'edit_others_posts',
			$upgrade_url
		);
	}

	/**
	 * Add SMTP promotional submenu page.
	 *
	 * @return void
	 * @since 1.7.1
	 */
	public function add_suremail_page() {
		add_submenu_page(
			'sureforms_menu',
			__( 'SMTP', 'sureforms' ),
			__( 'SMTP', 'sureforms' ),
			'edit_others_posts',
			'sureforms_smtp',
			[ $this, 'suremail_page_callback' ]
		);

		// Get the current submenu page.
		$submenu_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $_GET['page'] does not provide nonce.

		// Check if SureMail is installed and active.
		if ( 'sureforms_smtp' === $submenu_page && file_exists( WP_PLUGIN_DIR . '/suremails/suremails.php' ) && is_plugin_active( 'suremails/suremails.php' ) ) {
			// Plugin is installed and active - redirect to SureMail dashboard.
			wp_safe_redirect( admin_url( 'options-general.php?page=suremail#/dashboard' ) );
			exit;
		}
	}

	/**
	 * SMTP promotional page callback.
	 *
	 * @return void
	 * @since 1.7.1
	 */
	public function suremail_page_callback() {
		echo '<div id="srfm-suremail-container" class="srfm-admin-wrapper"></div>';
	}

	/**
	 * Render Admin Dashboard.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function render_dashboard() {
		echo '<div id="srfm-dashboard-container" class="srfm-admin-wrapper"></div>';
	}

	/**
	 * Settings page callback.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function settings_page_callback() {
		echo '<div id="srfm-settings-container" class="srfm-admin-wrapper"></div>';
	}

	/**
	 * Add new form menu item.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function add_new_form() {
		add_submenu_page(
			'sureforms_menu',
			__( 'New Form', 'sureforms' ),
			__( 'New Form', 'sureforms' ),
			'edit_others_posts',
			'add-new-form',
			[ $this, 'add_new_form_callback' ],
			2
		);
		$entries_hook = add_submenu_page(
			'sureforms_menu',
			__( 'Entries', 'sureforms' ),
			__( 'Entries', 'sureforms' ),
			'edit_others_posts',
			SRFM_ENTRIES,
			[ $this, 'render_entries' ],
			3
		);

		if ( $entries_hook ) {
			add_action( 'load-' . $entries_hook, [ $this, 'mark_entries_page_visit' ] );
		}
	}

	/**
	 * Add new form mentu item callback.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function add_new_form_callback() {
		echo '<div id="srfm-add-new-form-container" class="srfm-admin-wrapper"></div>';
	}

	/**
	 * Entries page callback.
	 *
	 * @since 0.0.13
	 * @return void
	 */
	public function render_entries() {
		// Render single entry view.
		// Adding the phpcs ignore nonce verification as no database operations are performed in this function, it is used to display the single entry view.
		if ( isset( $_GET['entry_id'] ) && is_numeric( $_GET['entry_id'] ) && isset( $_GET['view'] ) && 'details' === $_GET['view'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not needed here and explained in the comments above as well.
			$single_entry_view = new Single_Entry();
			$single_entry_view->render();
			return;
		}

		// Render all entries view.
		$entries_table = new Entries_List_Table();
		$entries_table->prepare_items();
		echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__( 'Entries', 'sureforms' ) . '</h1>';
		if ( empty( $entries_table->all_entries_count ) && empty( $entries_table->trash_entries_count ) ) {
			$instance = Post_Types::get_instance();
			$instance->sureforms_render_blank_state( SRFM_ENTRIES );
			$instance->get_blank_state_styles();
			return;
		}
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="sureforms_entries">';
		$entries_table->display();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Add notification badge to SureForms menu when there are new entries.
	 *
	 * @since 1.7.3
	 * @return void
	 */
	public function maybe_add_entries_badge() {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}

		// If currently viewing the entries listing page, mark it as visited and skip the badge.
		if ( isset( $_GET['page'] ) && SRFM_ENTRIES === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking the page slug.
			$this->mark_entries_page_visit();
			return;
		}

		$srfm_options = get_option( 'srfm_options', [] );
		$last_visit   = isset( $srfm_options['entries_last_visited'] ) ? absint( $srfm_options['entries_last_visited'] ) : 0;
		$new_entries  = Entries::get_entries_count_after( $last_visit );

		if ( $new_entries <= 0 ) {
			return;
		}

		global $menu;
		foreach ( $menu as $index => $item ) {
			if ( isset( $item[2] ) && 'sureforms_menu' === $item[2] ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Adding notifications for menu item.
				$menu[ $index ][0] .= ' <span class="srfm-update-dot"></span>';
				break;
			}
		}

		global $submenu;
		if ( isset( $submenu['sureforms_menu'] ) ) {
			foreach ( $submenu['sureforms_menu'] as $index => $sub_item ) {
				if ( isset( $sub_item[2] ) && SRFM_ENTRIES === $sub_item[2] ) {
					$submenu['sureforms_menu'][ $index ][0] .= sprintf( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Adding notifications for submenu item.
						' <span class="update-plugins count-%1$d"><span class="plugin-count">%1$d</span></span>',
						absint( $new_entries )
					);
					break;
				}
			}
		}
	}

	/**
	 * Mark the user's visit to the entries page.
	 *
	 * @since 1.7.3
	 * @return void
	 */
	public function mark_entries_page_visit() {
		if ( current_user_can( 'edit_others_posts' ) ) {
			$srfm_options                         = get_option( 'srfm_options', [] );
			$srfm_options['entries_last_visited'] = time();
			\SRFM\Inc\Helper::update_admin_settings_option( 'srfm_options', $srfm_options );
		}
	}

	/**
	 * Adds a settings link to the plugin action links on the plugins page.
	 *
	 * @param array  $links An array of plugin action links.
	 * @param string $file The plugin file path.
	 * @return array The updated array of plugin action links.
	 * @since 0.0.1
	 */
	public function add_settings_link( $links, $file ) {
		if ( 'sureforms/sureforms.php' === $file ) {
			$plugin_links = apply_filters(
				'sureforms_plugin_action_links',
				[
					'sureforms_settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=sureforms_form_settings&tab=general-settings' ) ) . '">' . esc_html__( 'Settings', 'sureforms' ) . '</a>',
				]
			);
			$links        = array_merge( $plugin_links, $links );
		}
		return $links;
	}

	/**
	 * Sureforms block editor styles.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_styles() {
		$current_screen = get_current_screen();
		global $wp_version;

		$file_prefix = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? '' : '.min';
		$dir_name    = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? 'unminified' : 'minified';

		$css_uri        = SRFM_URL . 'assets/css/' . $dir_name . '/';
		$vendor_css_uri = SRFM_URL . 'assets/css/minified/deps/';

		/* RTL */
		if ( is_rtl() ) {
			$file_prefix .= '-rtl';
		}

		// Enqueue editor styles for post and page.
		if ( SRFM_FORMS_POST_TYPE === $current_screen->post_type ) {
			wp_enqueue_style( SRFM_SLUG . '-editor', $css_uri . 'backend/editor' . $file_prefix . '.css', [], SRFM_VER );
			wp_enqueue_style( SRFM_SLUG . '-backend-blocks', $css_uri . 'blocks/default/backend' . $file_prefix . '.css', [], SRFM_VER );
			wp_enqueue_style( SRFM_SLUG . '-intl', $vendor_css_uri . 'intl/intlTelInput-backend.min.css', [], SRFM_VER );
			wp_enqueue_style( SRFM_SLUG . '-common', $css_uri . 'common' . $file_prefix . '.css', [], SRFM_VER );
			wp_enqueue_style( SRFM_SLUG . '-reactQuill', $vendor_css_uri . 'quill/quill.snow.css', [], SRFM_VER );
			wp_enqueue_style( SRFM_SLUG . '-single-form-modal', $css_uri . 'single-form-setting' . $file_prefix . '.css', [], SRFM_VER );

			// if version is equal to or lower than 6.6.2 then add compatibility css.
			if ( version_compare( $wp_version, '6.6.2', '<=' ) ) {
				$srfm_inline_css = '.srfm-settings-modal .srfm-setting-modal-container .components-toggle-control .components-base-control__help{
					margin-left: 4em;
				}';
				wp_add_inline_style( SRFM_SLUG . '-single-form-modal', $srfm_inline_css );
			}
		}

		wp_enqueue_style( SRFM_SLUG . '-form-selector', $css_uri . 'srfm-form-selector' . $file_prefix . '.css', [], SRFM_VER );
		wp_enqueue_style( SRFM_SLUG . '-common-editor', SRFM_URL . 'assets/build/common-editor.css', [], SRFM_VER, 'all' );
	}

	/**
	 * Get Breadcrumbs for current page.
	 *
	 * @since 0.0.1
	 * @return array Breadcrumbs Array.
	 */
	public function get_breadcrumbs_for_current_page() {
		global $post, $pagenow;
		$breadcrumbs = [];

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- We don't need nonce verification here.
			$page_title    = get_admin_page_title();
			$breadcrumbs[] = [
				'title' => $page_title,
				'link'  => '',
			];
		} elseif ( $post && in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ], true ) ) {
			$post_type_obj = get_post_type_object( get_post_type() );
			if ( $post_type_obj ) {
				$post_type_plural = $post_type_obj->labels->name;
				$breadcrumbs[]    = [
					'title' => $post_type_plural,
					'link'  => admin_url( 'edit.php?post_type=' . $post_type_obj->name ),
				];

				if ( 'edit.php' === $pagenow && ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- We don't need nonce verification here.
					$breadcrumbs[ count( $breadcrumbs ) - 1 ]['link'] = '';
				} else {
					$breadcrumbs[] = [
						/* Translators: Post Title. */
						'title' => sprintf( __( 'Edit %1$s', 'sureforms' ), get_the_title() ),
						'link'  => get_edit_post_link( $post->ID ),
					];
				}
			}
		} else {
			$current_screen = get_current_screen();
			if ( $current_screen && 'sureforms_form' === $current_screen->post_type ) {
				$breadcrumbs[] = [
					'title' => 'Forms',
					'link'  => '',
				];
			} else {
				$breadcrumbs[] = [
					'title' => '',
					'link'  => '',
				];
			}
		}

		return $breadcrumbs;
	}

	/**
	 * Enqueue Admin Scripts.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {
		$current_screen = get_current_screen();
		global $wp_version;

		$file_prefix = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? '' : '.min';
		$dir_name    = defined( 'SRFM_DEBUG' ) && SRFM_DEBUG ? 'unminified' : 'minified';
		$js_uri      = SRFM_URL . 'assets/js/' . $dir_name . '/';
		$css_uri     = SRFM_URL . 'assets/css/' . $dir_name . '/';
		$is_rtl      = is_rtl();
		$rtl         = $is_rtl ? '-rtl' : '';

		/**
		 * List of the handles in which we need to add translation compatibility.
		 */
		$script_translations_handlers = [];
		$onboarding_instance          = Onboarding::get_instance();

		$localization_data = [
			'site_url'                => get_site_url(),
			'breadcrumbs'             => $this->get_breadcrumbs_for_current_page(),
			'sureforms_dashboard_url' => admin_url( '/admin.php?page=sureforms_menu' ),
			'plugin_version'          => SRFM_VER,
			'global_settings_nonce'   => current_user_can( 'manage_options' ) ? wp_create_nonce( 'wp_rest' ) : '',
			'is_pro_active'           => Helper::has_pro(),
			'pro_plugin_version'      => Helper::has_pro() ? SRFM_PRO_VER : '',
			'pro_plugin_name'         => Helper::has_pro() && defined( 'SRFM_PRO_PRODUCT' ) ? SRFM_PRO_PRODUCT : 'SureForms Pro',
			'sureforms_pricing_page'  => Helper::get_sureforms_website_url( 'pricing' ),
			'field_spacing_vars'      => Helper::get_css_vars(),
			'is_ver_lower_than_6_7'   => version_compare( $wp_version, '6.6.2', '<=' ),
			'integrations'            => Helper::sureforms_get_integration(),
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'sf_plugin_manager_nonce' => wp_create_nonce( 'sf_plugin_manager_nonce' ),
			'plugin_installer_nonce'  => wp_create_nonce( 'updates' ),
			'plugin_activating_text'  => __( 'Activating...', 'sureforms' ),
			'plugin_activated_text'   => __( 'Activated', 'sureforms' ),
			'plugin_activate_text'    => __( 'Activate', 'sureforms' ),
			'plugin_installing_text'  => __( 'Installing...', 'sureforms' ),
			'plugin_installed_text'   => __( 'Installed', 'sureforms' ),
			'is_rtl'                  => $is_rtl,
			'onboarding_completed'    => method_exists( $onboarding_instance, 'get_onboarding_status' ) ? $onboarding_instance->get_onboarding_status() : false,
			'onboarding_redirect'     => isset( $_GET['srfm-activation-redirect'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not required for the activation redirection.
			'pointer_nonce'           => wp_create_nonce( 'sureforms_pointer_action' ),
			'srfm_ai_details'         => AI_Helper::get_current_usage_details(),
		];

		$is_screen_sureforms_menu          = Helper::validate_request_context( 'sureforms_menu', 'page' );
		$is_screen_add_new_form            = Helper::validate_request_context( 'add-new-form', 'page' );
		$is_screen_sureforms_form_settings = Helper::validate_request_context( 'sureforms_form_settings', 'page' );
		$is_screen_sureforms_entries       = Helper::validate_request_context( SRFM_ENTRIES, 'page' );
		$is_post_type_sureforms_form       = SRFM_FORMS_POST_TYPE === $current_screen->post_type;

		if ( $is_screen_sureforms_menu || $is_post_type_sureforms_form || $is_screen_add_new_form || $is_screen_sureforms_form_settings || $is_screen_sureforms_entries ) {
			$asset_handle = '-dashboard';

			wp_enqueue_style( SRFM_SLUG . $asset_handle . '-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap', [], SRFM_VER );

			$script_asset_path = SRFM_DIR . 'assets/build/dashboard.asset.php';
			$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SRFM_VER,
			];
			wp_enqueue_script( SRFM_SLUG . $asset_handle, SRFM_URL . 'assets/build/dashboard.js', $script_info['dependencies'], SRFM_VER, true );

			wp_localize_script( SRFM_SLUG . $asset_handle, 'scIcons', [ 'path' => SRFM_URL . 'assets/build/icon-assets' ] );

			$script_translations_handlers[] = SRFM_SLUG . $asset_handle;

			if ( class_exists( 'SRFM_PRO\Admin\Licensing' ) ) {
				$license_active                         = \SRFM_PRO\Admin\Licensing::is_license_active();
				$localization_data['is_license_active'] = $license_active;

				// Updating current licensing status.
				$srfm_pro_license_status = get_option( 'srfm_pro_license_status', '' );
				$current_license_status  = $license_active ? 'licensed' : 'unlicensed';
				if ( $current_license_status !== $srfm_pro_license_status ) {
					update_option( 'srfm_pro_license_status', $current_license_status );
				}
			}

			$localization_data['security_settings_url']    = admin_url( '/admin.php?page=sureforms_form_settings&tab=security-settings' );
			$localization_data['integration_settings_url'] = admin_url( '/admin.php?page=sureforms_form_settings&tab=integration-settings' );
			wp_localize_script(
				SRFM_SLUG . $asset_handle,
				SRFM_SLUG . '_admin',
				apply_filters(
					SRFM_SLUG . '_admin_filter',
					$localization_data
				)
			);
			wp_enqueue_style( SRFM_SLUG . '-dashboard', SRFM_URL . 'assets/build/dashboard.css', [], SRFM_VER, 'all' );

		}

		if ( $is_screen_sureforms_form_settings ) {
			wp_enqueue_style( SRFM_SLUG . '-settings', $css_uri . 'backend/settings' . $file_prefix . $rtl . '.css', [], SRFM_VER );
		}

		// Enqueue styles for the entries page.
		if ( $is_screen_sureforms_entries ) {
			$asset_handle = '-entries';
			wp_enqueue_script( SRFM_SLUG . $asset_handle, SRFM_URL . 'assets/build/entries.js', $script_info['dependencies'], SRFM_VER, true );

			$script_translations_handlers[] = SRFM_SLUG . $asset_handle;
		}

		// Enqueue scripts for the SureMail promotional page.
		$is_screen_sureforms_smtp = Helper::validate_request_context( 'sureforms_smtp', 'page' );
		if ( $is_screen_sureforms_smtp ) {
			$asset_handle = 'suremail';

			$script_asset_path = SRFM_DIR . 'assets/build/' . $asset_handle . '.asset.php';
			$script_info       = file_exists( $script_asset_path )
				? include $script_asset_path
				: [
					'dependencies' => [],
					'version'      => SRFM_VER,
				];

			wp_enqueue_script( SRFM_SLUG . '-suremail', SRFM_URL . 'assets/build/' . $asset_handle . '.js', $script_info['dependencies'], SRFM_VER, true );
			wp_enqueue_style( SRFM_SLUG . '-suremail', SRFM_URL . 'assets/build/suremail.css', [], SRFM_VER, 'all' );

			// Localize script for SureMail page.
			$suremail_localization_data = [
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'admin_url'              => admin_url(),
				'suremail_url'           => 'https://sureforms.com/suremail/',
				'plugin_installer_nonce' => wp_create_nonce( 'updates' ),
				'sfPluginManagerNonce'   => wp_create_nonce( 'sf_plugin_manager_nonce' ),
				'suremail_status'        => file_exists( WP_PLUGIN_DIR . '/suremails/suremails.php' )
					? ( is_plugin_active( 'suremails/suremails.php' ) ? 'active' : 'installed' )
					: 'not_installed',
			];

			wp_localize_script(
				SRFM_SLUG . '-suremail',
				SRFM_SLUG . '_admin',
				apply_filters(
					SRFM_SLUG . '_suremail_admin_filter',
					$suremail_localization_data
				)
			);

			$script_translations_handlers[] = SRFM_SLUG . '-suremail';
		}

		// Admin Submenu Styles.
		wp_enqueue_style( SRFM_SLUG . '-admin', $css_uri . 'backend/admin' . $file_prefix . $rtl . '.css', [], SRFM_VER );

		if ( 'edit-' . SRFM_FORMS_POST_TYPE === $current_screen->id ) {
			$asset_handle = 'page_header';

			$script_asset_path = SRFM_DIR . 'assets/build/' . $asset_handle . '.asset.php';
			$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SRFM_VER,
			];
			wp_enqueue_script( SRFM_SLUG . '-form-page-header', SRFM_URL . 'assets/build/' . $asset_handle . '.js', $script_info['dependencies'], SRFM_VER, true );
			wp_enqueue_style( SRFM_SLUG . '-form-archive-styles', $css_uri . 'form-archive-styles' . $file_prefix . $rtl . '.css', [], SRFM_VER );

			$script_translations_handlers[] = SRFM_SLUG . '-form-page-header';
		}

		if ( $is_screen_sureforms_form_settings ) {
			$asset_handle = 'settings';

			$script_asset_path = SRFM_DIR . 'assets/build/' . $asset_handle . '.asset.php';
			$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SRFM_VER,
			];

			wp_enqueue_script( SRFM_SLUG . '-settings', SRFM_URL . 'assets/build/' . $asset_handle . '.js', $script_info['dependencies'], SRFM_VER, true );
			wp_localize_script(
				SRFM_SLUG . '-settings',
				SRFM_SLUG . '_admin',
				$localization_data
			);

			$script_translations_handlers[] = SRFM_SLUG . '-settings';
		}
		if ( 'edit-' . SRFM_FORMS_POST_TYPE === $current_screen->id ) {
			wp_enqueue_script( SRFM_SLUG . '-form-archive', $js_uri . 'form-archive' . $file_prefix . '.js', [], SRFM_VER, true );
			wp_enqueue_script( SRFM_SLUG . '-export', $js_uri . 'export' . $file_prefix . '.js', [ 'wp-i18n' ], SRFM_VER, true );
			wp_localize_script(
				SRFM_SLUG . '-export',
				SRFM_SLUG . '_export',
				[
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'srfm_export_nonce' => wp_create_nonce( 'export_form_nonce' ),
					'site_url'          => get_site_url(),
					'import_form_nonce' => current_user_can( 'edit_posts' ) ? wp_create_nonce( 'wp_rest' ) : '',
					'import_btn_string' => __( 'Import Form', 'sureforms' ),
				]
			);

			wp_enqueue_script( SRFM_SLUG . '-backend', $js_uri . 'backend' . $file_prefix . '.js', [], SRFM_VER, true );
			wp_localize_script(
				SRFM_SLUG . '-backend',
				SRFM_SLUG . '_backend',
				[
					'site_url' => get_site_url(),
				]
			);

			$script_translations_handlers[] = SRFM_SLUG . '-form-archive';
			$script_translations_handlers[] = SRFM_SLUG . '-export';
			$script_translations_handlers[] = SRFM_SLUG . '-backend';
		}

		if ( $is_screen_add_new_form ) {
			wp_enqueue_style( SRFM_SLUG . '-template-picker', $css_uri . 'template-picker' . $file_prefix . $rtl . '.css', [], SRFM_VER );

			$sureforms_admin = 'templatePicker';

			$script_asset_path = SRFM_DIR . 'assets/build/' . $sureforms_admin . '.asset.php';
			$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => SRFM_VER,
			];
			wp_enqueue_script( SRFM_SLUG . '-template-picker', SRFM_URL . 'assets/build/' . $sureforms_admin . '.js', $script_info['dependencies'], SRFM_VER, true );

			wp_localize_script(
				SRFM_SLUG . '-template-picker',
				SRFM_SLUG . '_admin',
				[
					'site_url'                     => get_site_url(),
					'plugin_url'                   => SRFM_URL,
					'preview_images_url'           => SRFM_URL . 'images/template-previews/',
					'admin_url'                    => admin_url( 'admin.php' ),
					'new_template_picker_base_url' => admin_url( 'post-new.php?post_type=sureforms_form' ),
					'capability'                   => current_user_can( 'edit_posts' ),
					'template_picker_nonce'        => current_user_can( 'edit_posts' ) ? wp_create_nonce( 'wp_rest' ) : '',
					'is_pro_active'                => Helper::has_pro(),
					'srfm_ai_usage_details'        => AI_Helper::get_current_usage_details(),
					'is_pro_license_active'        => AI_Helper::is_pro_license_active(),
					'srfm_ai_auth_user_email'      => get_option( 'srfm_ai_auth_user_email' ),
					'pricing_page_url'             => Helper::get_sureforms_website_url( 'pricing' ),
					'licensing_nonce'              => wp_create_nonce( 'srfm_pro_licensing_nonce' ),
				]
			);

			$script_translations_handlers[] = SRFM_SLUG . '-template-picker';
		}
		// Quick action sidebar.
		$default_allowed_quick_sidebar_blocks = apply_filters(
			'srfm_quick_sidebar_allowed_blocks',
			[
				'srfm/input',
				'srfm/email',
				'srfm/textarea',
				'srfm/checkbox',
				'srfm/number',
				'srfm/inline-button',
				'srfm/advanced-heading',
			]
		);
		if ( ! is_array( $default_allowed_quick_sidebar_blocks ) ) {
			$default_allowed_quick_sidebar_blocks = [];
		}

		$srfm_enable_quick_action_sidebar = get_option( 'srfm_enable_quick_action_sidebar' );
		if ( ! $srfm_enable_quick_action_sidebar ) {
			$srfm_enable_quick_action_sidebar = 'disabled';
		}
		$quick_sidebar_allowed_blocks = get_option( 'srfm_quick_sidebar_allowed_blocks' );
		$quick_sidebar_allowed_blocks = ! empty( $quick_sidebar_allowed_blocks ) && is_array( $quick_sidebar_allowed_blocks ) ? $quick_sidebar_allowed_blocks : $default_allowed_quick_sidebar_blocks;
		$srfm_ajax_nonce              = wp_create_nonce( 'srfm_ajax_nonce' );
		wp_enqueue_script( SRFM_SLUG . '-quick-action-siderbar', SRFM_URL . 'assets/build/quickActionSidebar.js', [], SRFM_VER, true );
		wp_localize_script(
			SRFM_SLUG . '-quick-action-siderbar',
			SRFM_SLUG . '_quick_sidebar_blocks',
			[
				'allowed_blocks'                   => $quick_sidebar_allowed_blocks,
				'srfm_enable_quick_action_sidebar' => $srfm_enable_quick_action_sidebar,
				'srfm_ajax_nonce'                  => $srfm_ajax_nonce,
				'srfm_ajax_url'                    => admin_url( 'admin-ajax.php' ),
			]
		);

		$script_translations_handlers[] = SRFM_SLUG . '-quick-action-siderbar';

		/**
		 * Enqueuing SureTriggers Integration script.
		 * This script loads suretriggers iframe in Intergations tab.
		 */
		if ( $is_post_type_sureforms_form ) {
			wp_enqueue_script( SRFM_SLUG . '-suretriggers-integration', SRFM_SURETRIGGERS_INTEGRATION_BASE_URL . 'js/v2/embed.js', [], SRFM_VER, true );
		}

		// Check $script_translations_handlers is not empty before calling the function.
		if ( ! empty( $script_translations_handlers ) ) {
			// Remove duplicates values from the array.
			$script_translations_handlers = array_unique( $script_translations_handlers );

			foreach ( $script_translations_handlers as $script_handle ) {
				Helper::register_script_translations( $script_handle );
			}
		}
	}

	/**
	 * Form Template Picker Admin Body Classes
	 * WordPress sometimes translates class names in the admin body tag, which can result in
	 * incorrect or missing class names when rendering the admin pages. This function ensures
	 * that essential class names are manually added to the body tag to maintain proper functionality.
	 *
	 * @since 0.0.1
	 * @param string $classes Space separated class string.
	 */
	public function admin_template_picker_body_class( $classes = '' ) {
		// Define an associative array of class names and their corresponding conditions.
		// Each condition checks whether a specific request context matches.
		$srfm_classes = [
			'sureforms_page_sureforms_entries'       => Helper::validate_request_context( SRFM_ENTRIES, 'page' ),
			'sureforms_page_sureforms_form_settings' => Helper::validate_request_context( 'sureforms_form_settings', 'page' ),
			'srfm-template-picker'                   => Helper::validate_request_context( 'add-new-form', 'page' ),
		];

		$add_srfm_classes = '';

		// Loop through the defined classes and conditions.
		foreach ( $srfm_classes as $class => $condition ) {
			// Check if the condition evaluates to true.
			if ( $condition ) {
				// Append the class to the existing classes string, followed by a space.
				$add_srfm_classes .= empty( $add_srfm_classes ) ? $class : ' ' . $class;
			}
		}

		// Append the new classes to the existing classes string.
		if ( ! empty( $add_srfm_classes ) ) {
			$classes .= ' ' . $add_srfm_classes;
		}

		// Return the updated list of classes.
		return $classes;
	}

	/**
	 * Disable spectra's quick action bar in sureforms CPT.
	 *
	 * @param string $status current status of the quick action bar.
	 * @since 0.0.2
	 * @return string
	 */
	public function restrict_spectra_quick_action_bar( $status ) {
		$screen = get_current_screen();
		if ( 'disabled' !== $status && isset( $screen->id ) && 'sureforms_form' === $screen->id ) {
			$status = 'disabled';
		}

		return $status;
	}

	// Entries methods.

	/**
	 * Handle entry actions.
	 *
	 * @since 0.0.13
	 * @return void
	 */
	public function handle_entry_actions() {
		Entries_List_Table::process_bulk_actions();

		if ( ! isset( $_GET['page'] ) || SRFM_ENTRIES !== $_GET['page'] ) {
			return;
		}
		if ( ! isset( $_GET['entry_id'] ) || ! isset( $_GET['action'] ) ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'srfm_entries_action' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'sureforms' ) );
		}
		$action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$entry_id = Helper::get_integer_value( sanitize_text_field( wp_unslash( $_GET['entry_id'] ) ) );
		$view     = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '';
		if ( $entry_id > 0 ) {
			if ( 'read' === $action && 'details' === $view ) {
				$entry_status = Entries::get( $entry_id )['status'];
				if ( 'trash' === $entry_status ) {
					wp_die( esc_html__( 'You cannot view this entry because it is in trash.', 'sureforms' ) );
				}
			}
			Entries_List_Table::handle_entry_status( $entry_id, $action, $view );
		}
	}

	/**
	 * Admin Notice Callback if sureforms pro is out of date.
	 *
	 * Hooked - admin_notices
	 *
	 * @return void
	 * @since 1.0.4
	 */
	public function srfm_pro_version_compatibility() {
		if ( ! Helper::has_pro() ) {
			return;
		}

		if ( empty( get_current_screen() ) ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$srfm_pro_license_status = get_option( 'srfm_pro_license_status', '' );
		/**
		 * If the license status is not set then get the license status and update the option accordingly.
		 * This will be executed only once. Subsequently, the option status is updated by the licensing class on license activation or deactivation.
		 */
		if ( empty( $srfm_pro_license_status ) && class_exists( 'SRFM_PRO\Admin\Licensing' ) ) {
			$srfm_pro_license_status = \SRFM_PRO\Admin\Licensing::is_license_active() ? 'licensed' : 'unlicensed';
			update_option( 'srfm_pro_license_status', $srfm_pro_license_status );
		}

		$pro_plugin_name = defined( 'SRFM_PRO_PRODUCT' ) ? SRFM_PRO_PRODUCT : 'SureForms Pro';
		$message         = '';
		$url             = admin_url( 'admin.php?page=sureforms_form_settings&tab=account-settings' );
		if ( 'unlicensed' === $srfm_pro_license_status ) {
			$message = '<p>' . sprintf(
				// translators: %1$s: Opening anchor tag with URL, %2$s: Closing anchor tag, %3$s: SureForms Pro Plugin Name.
				esc_html__( 'Please %1$sactivate%2$s your copy of %3$s to get new features, access support, receive update notifications, and more.', 'sureforms' ),
				'<a href="' . esc_url( $url ) . '">',
				'</a>',
				'<i>' . esc_html( $pro_plugin_name ) . '</i>'
			) . '</p>';
		}

		if ( ! version_compare( SRFM_PRO_VER, SRFM_PRO_RECOMMENDED_VER, '>=' ) ) {
			$message .= '<p>' . sprintf(
				// translators: %1$s: SureForms version, %2$s: SureForms Pro Plugin Name, %3$s: SureForms Pro Version, %4$s: Anchor tag open, %5$s: Closing anchor tag.
				esc_html__( 'SureForms %1$s requires minimum %2$s %3$s to work properly. Please update to the latest version from %4$shere%5$s.', 'sureforms' ),
				esc_html( SRFM_VER ),
				esc_html( $pro_plugin_name ),
				esc_html( SRFM_PRO_RECOMMENDED_VER ),
				'<a href=' . esc_url( admin_url( 'update-core.php' ) ) . '>',
				'</a>'
			) . '</p>';
		}

		if ( ! empty( $message ) ) {
			// Phpcs ignore comment is required as $message variable is already escaped.
			echo '<div class="notice notice-warning">' . wp_kses_post( $message ) . '</div>';
		}
	}

	/**
	 * Add SMTP warning admin notice if no SMTP plugin is active
	 *
	 * Hooked - admin_notices
	 *
	 * @return void
	 * @since 1.9.1
	 */
	public function add_smtp_warning_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Only show on dashboard or SureForms admin pages.
		$screen = get_current_screen();
		if ( ! $screen || ( false === strpos( $screen->id, 'sureforms' ) && 'dashboard' !== $screen->id ) ) {
			return;
		}
		// Dismiss logic.
		if ( isset( $_GET['srfm_dismiss_smtp_notice'] ) && '1' === $_GET['srfm_dismiss_smtp_notice'] ) {
			// Verify nonce for security.
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'srfm_dismiss_smtp_notice' ) ) {
				return;
			}
			update_user_meta( get_current_user_id(), 'srfm_dismiss_smtp_notice', 1 );
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'srfm_dismiss_smtp_notice', true ) ) {
			return;
		}
		// Determine SureMail plugin status and link.
		$plugin_file = 'suremails/suremails.php';
		if ( file_exists( WP_PLUGIN_DIR . '/suremails/suremails.php' ) && is_plugin_active( $plugin_file ) ) {
			// SureMail is active, do not show the notice.
			return;
		}
		if ( ! Helper::is_any_smtp_plugin_active() ) {
			if ( file_exists( WP_PLUGIN_DIR . '/suremails/suremails.php' ) ) {
				if ( is_plugin_active( $plugin_file ) ) {
					$suremail_url = admin_url( 'options-general.php?page=suremail#/dashboard' );
				} else {
					$suremail_url = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file );
				}
			} else {
				$suremail_url = admin_url( 'plugin-install.php?s=suremail&tab=search&type=term' );
			}
			$dismiss_url = wp_nonce_url( add_query_arg( 'srfm_dismiss_smtp_notice', '1' ), 'srfm_dismiss_smtp_notice' );
			printf(
				'<div class="notice notice-warning is-dismissible srfm-smtp-warning" data-dismiss-url="%1$s"><p>%2$s</p></div>',
				esc_url( $dismiss_url ),
				sprintf(
					/* translators: 1: line break, 2: SureMail link opening tag, 3: SureMail link closing tag */
					esc_html__( 'It looks like there\'s no SMTP plugin running on your site. That means emails sent from SureForms might not go through.%1$sYou can use %2$sSureMail%3$s to get email delivery working.', 'sureforms' ),
					'<br />',
					'<a href="' . esc_url( $suremail_url ) . '" target="_blank">',
					'</a>'
				)
			);
			?>
			<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				// Handle SMTP notice dismissal using event delegation
				// This works even if the dismiss button is added dynamically by WordPress
				document.addEventListener('click', function(e) {
					// Check if clicked element is a dismiss button within our SMTP notice
					if (e.target.classList.contains('notice-dismiss') && e.target.closest('.srfm-smtp-warning')) {
						const smtpNotice = e.target.closest('.srfm-smtp-warning');
						const dismissUrl = smtpNotice.dataset.dismissUrl;

						if (dismissUrl) {
							// Security: Validate URL is safe before redirecting
							try {
								const url = new URL(dismissUrl, window.location.origin);
								// Only allow same-origin URLs for security
								if (url.origin === window.location.origin && url.protocol === window.location.protocol) {
									window.location.href = url.href;
								}
							} catch (error) {
								// Invalid URL - ignore the redirect for security
								console.warn('Invalid dismiss URL detected:', dismissUrl);
							}
							e.preventDefault();
							e.stopPropagation();
						}
					}
				});
			});
			</script>
			<?php
		}
	}

	/**
	 * Disables the capabilities for WPForms to avoid conflicts when enqueueing
	 * scripts and styles for WPForms.
	 *
	 * This function is intended to prevent any potential conflicts that may arise
	 * when WPForms scripts and styles are enqueued. By disabling certain capabilities,
	 * it ensures that WPForms does not interfere with other functionalities.
	 *
	 * @param bool $user_can A boolean indicating whether the user has the capability.
	 * @return bool Returns true if the capabilities are successfully disabled, false otherwise.
	 * @since 1.4.2
	 */
	public function disable_wpforms_capabilities( $user_can ) {
		// Note: Nonce verification is intentionally omitted here as no database operations are performed.
		// The values of the $_REQUEST variables are strictly validated, ensuring security without the need for nonce verification.

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['action'] ) ? absint( $_REQUEST['post'] ) : 0;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = $post_id ? get_post_type( $post_id ) : sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ?? '' ) );
		return SRFM_FORMS_POST_TYPE === $post_type ? false : $user_can;
	}

	/**
	 * Enqueueus the admin pointer script and styles.
	 *
	 * @return void
	 * @since 1.8.0
	 */
	public function enqueue_admin_pointer() {
		if ( ! $this->is_admin_pointer_visible() ) {
			return;
		}
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script(
			'sureforms-admin-pointer',
			plugins_url( 'admin/assets/js/sureforms-pointer.js', SRFM_FILE ),
			[ 'wp-pointer', 'jquery' ],
			SRFM_VER,
			true
		);
		wp_localize_script(
			'sureforms-admin-pointer',
			'sureformsPointerData',
			[
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'pointer_nonce' => wp_create_nonce( 'sureforms_pointer_action' ),
			]
		);
	}

	/**
	 * Ajax handler for pointer popup visibility.
	 *
	 * @return void
	 * @since 1.8.0
	 */
	public function pointer_should_show() {
		// Security: Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized user.', 'sureforms' ) ], 403 );
		}
		// Security: Nonce check.
		if ( empty( $_POST['pointer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pointer_nonce'] ) ), 'sureforms_pointer_action' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'sureforms' ) ], 403 );
		}

		$content_markup = sprintf(
			/* translators: 1: opening span, 2: opening strong (inline), 3: closing strong, 4: closing span, 5: opening strong (block), 6: closing strong */
			__( '%1$sGet started by %2$sbuilding your first form%3$s.%4$s%5$sExperience the power of our intuitive AI Form Builder%6$s', 'sureforms' ),
			'<span>',
			'<strong>',
			'</strong>',
			'</span><br/>',
			'<strong style="font-size:1.1em;">',
			'</strong>'
		);
		wp_send_json(
			[
				'show'        => true,
				'title'       => esc_html( __( 'SureForms is waiting for you!', 'sureforms' ) ),
				'content'     => wp_kses_post( $content_markup ),
				'button_text' => esc_html( __( 'Build My First Form', 'sureforms' ) ),
				'dismiss'     => esc_html( __( 'Dismiss', 'sureforms' ) ),
				'button_url'  => admin_url( 'admin.php?page=add-new-form' ),
			]
		);
	}

	/**
	 * Ajax callback for pointer popup dismissed action.
	 *
	 * @return void
	 * @since 1.8.0
	 */
	public function pointer_dismissed() {
		// Security: Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized user.', 'sureforms' ) ], 403 );
		}
		// Security: Nonce check.
		if ( empty( $_POST['pointer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pointer_nonce'] ) ), 'sureforms_pointer_action' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'sureforms' ) ], 403 );
		}
		// Use Helper to update srfm_options key.
		Helper::update_srfm_option( 'pointer_popup_dismissed', time() );

		wp_send_json_success();
	}

	/**
	 * Ajax pointer accepted CTA callback.
	 *
	 * @return void
	 * @since 1.8.0
	 */
	public function pointer_accepted_cta() {
		// Security: Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized user.', 'sureforms' ) ], 403 );
		}
		// Security: Nonce check.
		if ( empty( $_POST['pointer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pointer_nonce'] ) ), 'sureforms_pointer_action' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce.', 'sureforms' ) ], 403 );
		}
		// Use Helper to update srfm_options key.
		Helper::update_srfm_option( 'pointer_popup_accepted', time() );

		wp_send_json_success();
	}

	/**
	 * Maybe register the dashboard widget based on entries.
	 *
	 * @return void
	 * @since 1.9.1
	 */
	public function maybe_register_dashboard_widget() {
		// Quick check if there are any entries in the last 7 days.
		$seven_days_ago = strtotime( '-7 days' );
		$total_entries  = Entries::get_entries_count_after( $seven_days_ago );

		// Only add the dashboard setup hook if there are entries.
		if ( $total_entries > 0 ) {
			// Get forms with entries (limit 4 for dashboard widget).
			$this->dashboard_widget_data = Helper::get_forms_with_entry_counts( $seven_days_ago, 4 );

			// Only show dashboard widget if there are forms with entries.
			if ( ! empty( $this->dashboard_widget_data ) ) {
				add_action( 'wp_dashboard_setup', [ $this, 'register_dashboard_widget' ] );
			}
		}
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @return void
	 * @since 1.9.1
	 */
	public function register_dashboard_widget() {
		// Add the widget with high priority to position it at the top.
		wp_add_dashboard_widget(
			'sureforms_recent_entries',
			__( 'SureForms', 'sureforms' ),
			[ $this, 'render_dashboard_widget' ],
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the dashboard widget content.
	 *
	 * @return void
	 * @since 1.9.1
	 */
	public function render_dashboard_widget() {
		// Use the pre-fetched data to avoid duplicate queries.
		$entries_data = $this->dashboard_widget_data;

		// Display the widget content.
		?>
		<div class="srfm-dashboard-widget">
			<div class="srfm-widget-header">
				<h3 class="srfm-widget-title">
					<?php esc_html_e( 'Recent Entries', 'sureforms' ); ?>
					<span class="srfm-widget-subtitle"><?php esc_html_e( '( Last 7 days )', 'sureforms' ); ?></span>
				</h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sureforms_entries' ) ); ?>" class="srfm-widget-view-link">
					<?php esc_html_e( 'View', 'sureforms' ); ?>
				</a>
			</div>

			<div class="srfm-table-wrapper">
				<table class="srfm-entries-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Form Name', 'sureforms' ); ?></th>
							<th><?php esc_html_e( 'Entries', 'sureforms' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $entries_data as $form_data ) { ?>
							<tr>
								<td class="form-name"><?php echo esc_html( $form_data['title'] ); ?></td>
								<td class="entry-count"><?php echo esc_html( $form_data['count'] ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>

			<?php
			// Render footer if applicable.
			$this->render_dashboard_widget_footer( $entries_data );
			?>
		</div>
		<?php
	}

	/**
	 * Get random premium feature text.
	 *
	 * @return string Random feature text.
	 * @since 1.9.1
	 */
	private function get_random_premium_feature_text() {
		$features = [
			__( 'Use Conditional Logic to show only what matters', 'sureforms' ),
			__( 'Split your form into steps to keep it easy', 'sureforms' ),
			__( 'Let people upload files directly to your form', 'sureforms' ),
			__( 'Turn responses into downloadable PDFs automatically', 'sureforms' ),
			__( 'Let users sign with a simple signature field', 'sureforms' ),
			__( 'Connect your form to other tools using webhooks', 'sureforms' ),
			__( 'Use Conversational Forms for a chat-like experience', 'sureforms' ),
			__( 'Let users register or log in through your form', 'sureforms' ),
			__( 'Build forms that create WordPress user accounts', 'sureforms' ),
			__( 'Add calculations to auto-total scores or prices', 'sureforms' ),
		];

		// Get a random feature.
		$random_key = array_rand( $features );
		return $features[ $random_key ];
	}

	/**
	 * Render the dashboard widget footer for upsell.
	 *
	 * @param array $entries_data The entries data array.
	 * @return void
	 * @since 1.9.1
	 */
	private function render_dashboard_widget_footer( $entries_data ) {
		// Only show footer if Pro is not active.
		if ( Helper::has_pro() ) {
			return;
		}

		// Count total entries in last 7 days.
		$total_entries = 0;
		foreach ( $entries_data as $form_data ) {
			$total_entries += $form_data['count'];
		}

		// Count total published forms.
		$published_forms_count = wp_count_posts( SRFM_FORMS_POST_TYPE )->publish;

		// Show footer only if 3+ entries received OR 3+ forms published.
		if ( $total_entries >= 3 || $published_forms_count >= 3 ) {
			?>
			<div class="srfm-widget-footer">
				<div class="srfm-upgrade-content">
					<svg class="srfm-logo-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect width="20" height="20" fill="#D54407"/>
						<path d="M5.7139 4.2854H14.2853V7.1425H7.1424L5.7139 8.5711V7.1425V4.2854Z" fill="white"/>
						<path d="M5.7139 4.2854H14.2853V7.1425H7.1424L5.7139 8.5711V7.1425V4.2854Z" fill="white"/>
						<path d="M5.7148 8.5713H12.8577V11.4284H7.1434L5.7148 12.857V11.4284V8.5713Z" fill="white"/>
						<path d="M5.7148 8.5713H12.8577V11.4284H7.1434L5.7148 12.857V11.4284V8.5713Z" fill="white"/>
						<path d="M5.7148 12.8569H10.0006V15.7141H5.7148V12.8569Z" fill="white"/>
						<path d="M5.7148 12.8569H10.0006V15.7141H5.7148V12.8569Z" fill="white"/>
					</svg>
					<span><?php echo esc_html( $this->get_random_premium_feature_text() ); ?></span>
				</div>
				<?php
				$upgrade_url = add_query_arg(
					[
						'utm_medium' => 'dashboard-widget',
					],
					Helper::get_sureforms_website_url( 'pricing' )
				);
				?>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="srfm-upgrade-link" target="_blank">
					<?php esc_html_e( 'Upgrade', 'sureforms' ); ?>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * Determine if the admin pointer should be visible on this page.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	private function is_admin_pointer_visible() {
		global $pagenow;
		$allowed_pages = [ 'index.php', 'options-general.php' ];

		// Do not show if pointer dismissed, accepted, or more than 1 form exists.
		if (
			! empty( Helper::get_srfm_option( 'pointer_popup_dismissed' ) )
			|| ! empty( Helper::get_srfm_option( 'pointer_popup_accepted' ) )
			|| (int) ( wp_count_posts( SRFM_FORMS_POST_TYPE )->publish ?? 0 ) > 1
		) {
			return false;
		}

		if ( in_array( $pagenow, $allowed_pages, true ) ) {
			return true;
		}

		return false;
	}

}
