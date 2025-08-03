<?php
/**
 * Template importer
 *
 * @package TutorPro\TemplateImporter
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.6.0
 */

namespace TutorPro\TemplateImport;

use Droip\ExportImport\TemplateImport;
use Tutor\Helpers\TemplateImportHelper;
use TUTOR\Input;
use Tutor\Traits\JsonResponse;

defined( 'ABSPATH' ) || exit;

/**
 * Template import handler class
 *
 * @since 3.6.0
 */
class TemplateImporter {
	use JsonResponse;

	/**
	 * Template dependent plugin list transient
	 *
	 * @var string
	 */
	const DEPENDENT_PLUGINS_TRANSIENT = 'tutor_template_import_dependency_plugins';

	/**
	 * Instance of template import helper.
	 *
	 * @var TemplateImportHelper
	 */
	public $template_helper_cls;

	/**
	 * Template dependency endpoint
	 *
	 * @var string
	 */
	public $template_import_dependency_api;


	/**
	 * Register default hooks and actions for WordPress
	 */
	public function __construct() {
		$this->template_helper_cls            = new TemplateImportHelper();
		$this->template_import_dependency_api = $this->template_helper_cls->make_url( 'template-import-dependencies' );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_tutor_template_required_plugin_install', array( $this, 'tutor_template_required_plugin_install' ) );
		add_action( 'wp_ajax_import_droip_template', array( $this, 'import_droip_template' ) );
		add_action( 'wp_ajax_process_droip_template', array( $this, 'process_droip_template' ) );
		add_action( 'wp_ajax_tutor_template_import_list', array( $this, 'tutor_template_import_list' ) );
	}

	/**
	 * Enqueue scripts for admin
	 *
	 * @param string $page install plugin details.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function admin_scripts( $page ) {
		if ( 'tutor-lms-pro_page_tutor-themes' === $page ) {
			wp_enqueue_script( 'tutor-template-import', tutor_pro()->url . 'assets/js/template-import.js', array( 'wp-i18n' ), TUTOR_PRO_VERSION, true );
		}
	}

	/**
	 * AJAX callback to install a plugin.
	 *
	 *  @since 3.6.0
	 *
	 * @return array response array
	 */
	public function tutor_template_required_plugin_install() {
		if ( current_user_can( 'manage_options' ) === false ) {
			return $this->response( false, __( 'Permission denied!', 'tutor-pro' ), 'error' );
		}

		tutor_utils()->check_nonce();
		$plugin_name = Input::post( 'plugin_name' );
		try {
			$required_plugins = $this->template_dependency();
			$plugin_info      = $required_plugins[ $plugin_name ];
			if ( empty( $plugin_info ) ) {
				return $this->response( false, __( 'Required plugin info missing!', 'tutor-pro' ), 'error' );
			}
			$this->installing_plugin( $plugin_info );
		} catch ( \Throwable $th ) {
			return $this->response( false, __( 'Something went wrong!', 'tutor-pro' ), 'error' );
		}
	}

	/**
	 * Template Dependency
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public function template_dependency() {
		$dependent_plugins = get_transient( self::DEPENDENT_PLUGINS_TRANSIENT );
		if ( false === $dependent_plugins ) {
			$response = wp_remote_get(
				$this->template_import_dependency_api,
				array(
					'headers' => array(
						'Secret-Key' => 't344d5d71sae7dcb546b8cf55e594808',
					),
				)
			);
			if ( is_wp_error( $response ) ) {
				return array();
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! empty( $data ) && 200 === $data['status'] ) {
				$dependent_plugins = $data['body_response'] ?? array();
				set_transient( self::DEPENDENT_PLUGINS_TRANSIENT, $dependent_plugins, 0 );
			}
		}
		return $dependent_plugins;
	}

	/**
	 * Install plugin.
	 *
	 * @param array $plugin_info installed plugin details.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public function installing_plugin( $plugin_info ) {
		try {
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( 'plugin' === $plugin_info['type'] && ! empty( $plugin_info['src'] ) ) {
				if ( ! function_exists( 'plugins_api' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				}

				$is_install_plugin = $this->is_plugin_installed( $plugin_info['path'] );
				if ( ! $is_install_plugin ) {
					$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );

					$installed = $upgrader->install( $plugin_info['src'] );
					if ( is_wp_error( $installed ) ) {
						return $this->response( false, __( 'Plugin installation error!', 'tutor-pro' ), 'error' );
					}
				}

				$activate = activate_plugin( $plugin_info['path'], '', false, false );
				return $this->response( true, __( 'Plugin installed successfully!', 'tutor-pro' ), 'success' );
			} elseif ( 'theme' === $plugin_info['type'] && ! empty( $plugin_info['src'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/theme-install.php';

				$is_theme_installed = wp_get_theme( $plugin_info['slug'] )->exists();

				if ( ! $is_theme_installed ) {
					$upgrader = new \Theme_Upgrader( new \WP_Ajax_Upgrader_Skin() );

					$installed = $upgrader->install( $plugin_info['src'] );
					if ( is_wp_error( $installed ) ) {
						return $this->response( false, __( 'Theme installation error!', 'tutor-pro' ), 'error' );
					}
				}
					switch_theme( $plugin_info['slug'] );
				if ( wp_get_theme()->get_stylesheet() !== $plugin_info['slug'] ) {
					return $this->response( false, __( 'Theme activation error!', 'tutor-pro' ), 'error' );
				}

				return $this->response( true, __( 'Theme installed and activated successfully.', 'tutor-pro' ), 'success' );
			} else {
				return $this->response( true, __( 'Plugin or theme nothing installed!', 'tutor-pro' ), 'success' );
			}
		} catch ( \Throwable $th ) {
			return $this->response( false, __( 'Something went wrong!', 'tutor-pro' ), 'error' );
		}
	}

	/**
	 * Add comment or reply
	 *
	 * @since 3.6.0
	 *
	 * @return Array
	 */
	public function import_droip_template() {
		try {

			if ( current_user_can( 'manage_options' ) === false ) {
				return $this->response( false, __( 'Permission denied!', 'tutor-pro' ), 'error' );
			}
			tutor_utils()->check_nonce();
			$template_id          = Input::post( 'template_id' );
			$template_to_download = $this->template_helper_cls->get_template_download_url( $template_id );
			$template_import      = new TemplateImport();
			$is_import            = $template_import->import( $template_to_download, 'tutor_' . $template_id, true );

			if ( $is_import ) {
				return self::json_response( __( 'Content imported', 'tutor-pro' ), null, 200 );
			} else {
				return self::json_response( __( 'Content importing error!', 'tutor-pro' ), null, 400 );
			}
		} catch ( \Throwable $th ) {
			return self::json_response( __( 'Something went wrong!', 'tutor-pro' ), null, 400 );
		}
	}

	/**
	 * Process_droip_template description
	 *
	 * @since 3.6.0
	 *
	 * @return  array  description
	 */
	public function process_droip_template() {
		$template_import = new TemplateImport();
		$is_process      = $template_import->process();
		return $this->response( $is_process );
	}

	/**
	 * Check plugin is install or not
	 *
	 * @param   string $plugin_path plugin-slug.
	 *
	 * @since 3.6.0
	 *
	 * @return  bool
	 */
	private function is_plugin_installed( $plugin_path ) {
		$installed_plugins = get_plugins();
		foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
			if ( $plugin_path === $plugin_file ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get Template list.
	 *
	 * @since 3.6.0
	 */
	public function tutor_template_import_list() {
		ob_start();
		require_once tutor()->path . 'views/templates/templates-list.php';
		$contents = ob_get_clean();
		$this->json_response( __( 'Successfully fetched!', 'tutor-pro' ), $contents );
	}

	/**
	 * Response method.
	 *
	 * @param   boolean $success     status of isPlugin installed or not.
	 * @param   string  $message     message after installation of plugin.
	 * @param   string  $status      status of plugin.
	 *
	 * @since 3.6.0
	 *
	 * @return  void
	 */
	public function response( $success, $message = '', $status = '' ) {
		wp_send_json(
			array(
				'message' => $message,
				'status'  => $status,
				'success' => $success,
			)
		);
	}
}
