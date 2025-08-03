<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Applications {

	/**
	 * All registered applications.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected static $all_applications = array();

	/**
	 * The Core Triggers.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected static $core_triggers = array();

	/**
	 * The Core Actions.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected static $core_actions = array();

	/**
	 * The Application Triggers.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected static $app_triggers = array();

	/**
	 * The Application Actions.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected static $app_actions = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {

		// Include all core applications.
		$this->include();

		// add_action( 'wp_loaded', array( $this, 'include' ) );

		// Ajax to install and update applications.
		add_action( 'wp_ajax_flowmattic_install_app', array( $this, 'install_update_app' ) );

		// Ajax to delete application.
		add_action( 'wp_ajax_flowmattic_delete_app', array( $this, 'delete_app' ) );

		// Ajax to disable core app.
		add_action( 'wp_ajax_flowmattic_disable_core_app', array( $this, 'disable_core_app' ) );

		// Ajax to enable core app.
		add_action( 'wp_ajax_flowmattic_enable_core_app', array( $this, 'enable_core_app' ) );
	}

	/**
	 * Include the applications.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function include() {
		$installed_apps = 0;

		foreach ( glob( FLOWMATTIC_PLUGIN_DIR . '/inc/apps/*/*.php', GLOB_NOSORT ) as $filename ) {
			include $filename;
			++$installed_apps;
		}

		foreach ( glob( WP_CONTENT_DIR . '/flowmattic-apps/*/*.php', GLOB_NOSORT ) as $filename ) {
			include $filename;
			++$installed_apps;
		}

		// Fire an action to allow custom apps to be registered.
		do_action( 'flowmattic_load_custom_apps' );

		update_option( 'flowmattic_installed_apps', $installed_apps, false );
	}

	/**
	 * Add applications including triggers and actions.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $slug        Application slug.
	 * @param array  $application Application settings.
	 * @return void
	 */
	public static function add_application( $slug, $application ) {
		// Add the slug to the application.
		$application['fm_app'] = $slug;

		// If application type contains trigger.
		if ( isset( $application['base'] ) && false !== strpos( $application['type'], 'trigger' ) ) {
			self::$core_triggers[ $slug ] = $application;
		}

		// If application type contains action.
		if ( isset( $application['base'] ) && false !== strpos( $application['type'], 'action' ) ) {
			self::$core_actions[ $slug ] = $application;
		}

		if ( ! isset( $application['base'] ) && isset( $application['actions'] ) ) {
			self::$app_actions[ $slug ] = $application;
		}

		if ( ! isset( $application['base'] ) && isset( $application['triggers'] ) ) {
			self::$app_triggers[ $slug ] = $application;
		}

		// Set all applications array.
		self::$all_applications[ $slug ] = $application;
	}

	/**
	 * Return all applications.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_all_applications() {
		return self::$all_applications;
	}

	/**
	 * Return applications including triggers and actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_trigger_applications() {
		return self::$core_triggers;
	}

	/**
	 * Return applications including triggers and actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_action_applications() {
		return self::$core_actions;
	}

	/**
	 * Return other applications including triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_other_trigger_applications() {
		return self::$app_triggers;
	}

	/**
	 * Return other applications including actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_other_action_applications() {
		return self::$app_actions;
	}

	/**
	 * Install or update the application.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function install_update_app() {
		global $wp_filesystem;

		check_ajax_referer( 'flowmattic-integration-nonce', 'security' );

		$license = wp_flowmattic()->check_license();

		if ( ! $license ) {
			$license_key = get_option( 'flowmattic_license_Key', '' );

			if ( '' === $license_key ) {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'License not activated. Please activate your license in order to install the app integration.', 'flowmattic' ),
					)
				);
			} else {
				$license_message = get_option( 'flowmattic_license_message', '' );
				if ( '' === $license_message ) {
					$license_message = esc_html__( 'License not activated. Please activate your license in order to install the app integration.', 'flowmattic' );
				}

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => $license_message,
					)
				);
			}

			die();
		} elseif ( ! $license->is_valid ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'License not valid. Please make sure your license is activated and is valid.', 'flowmattic' ),
				)
			);

			die();
		}

		if ( empty( $wp_filesystem ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$app_url = isset( $_POST['appURL'] ) ? sanitize_text_field( $_POST['appURL'] ) : '';
		$slug    = isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';

		if ( '' === $app_url || '' === $slug ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Something went wrong. Please contact support.', 'flowmattic' ),
				)
			);

			die();
		}

		$app_dir       = WP_CONTENT_DIR . '/flowmattic-apps/';
		$local_app_dir = $app_dir;
		$local_zip     = $local_app_dir . $slug . '.zip';

		$request  = wp_remote_get( $app_url );
		$request  = wp_remote_retrieve_body( $request );
		$app_data = json_decode( $request, true );
		$zip_file = $app_data['zip'];

		if ( ! $wp_filesystem->is_dir( $app_dir ) ) {
			// Directory didn't exist, so let's create it.
			$wp_filesystem->mkdir( $app_dir );
		}

		// Get file content from the server.
		$data = $wp_filesystem->get_contents( $zip_file );

		if ( ! $wp_filesystem->put_contents( $local_zip, $data, FS_CHMOD_FILE ) ) {

			// @codingStandardsIgnoreStart
			// If the attempt to write to the file failed, then fallback to fwrite.
			@unlink( $local_zip );
			$fp = @fopen( $local_zip, 'w' );

			$written = @fwrite( $fp, $data );
			@fclose( $fp );
			if ( false === $written ) {
				return false;
			}
		}

		// @codingStandardsIgnoreEnd

		// If file is 0 bytes, try downloading with alternate method.
		if ( 0 === filesize( $local_zip ) ) {
			@unlink( $local_zip ); // @codingStandardsIgnoreLine

			// fetch the remote url and write it to the placeholder file.
			$response = wp_remote_get(
				$zip_file,
				array(
					'stream'   => true,
					'filename' => $local_zip,
				)
			);

			// request failed.
			if ( is_wp_error( $response ) ) {
				unlink( $local_zip );
				die( $response );
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			// make sure the fetch was successful.
			if ( 200 !== $code ) {
				unlink( $local_zip );
				$error = new WP_Error(
					'import_file_error',
					sprintf(
						__( 'Remote server returned %1$d %2$s', 'Creativo' ),
						$code,
						get_status_header_desc( $code )
					)
				);

				die( $error );
			}
		}

		if ( filesize( $local_zip ) > 0 ) {
			// Unzip.
			if ( class_exists( 'ZipArchive', false ) ) {
				$zip = new ZipArchive();
				if ( $zip->open( $local_zip ) != 'true' ) {
					die( 'Unable to open the Zip File' );
				}

				// Extract Zip File.
				$zip->extractTo( $local_app_dir );

				$zip->close();
			} else {
				WP_Filesystem();
				unzip_file( $local_zip, $local_app_dir );
			}
		} else {
			// If the filesize is 0 bytes, remove the directory and the empty file.
			@unlink( $local_zip ); // @codingStandardsIgnoreLine
			@unlink( $local_app_dir ); // @codingStandardsIgnoreLine
			die( "Couldn't download the ZIP file." );
		}

		@unlink( $local_zip ); // @codingStandardsIgnoreLine

		echo wp_json_encode(
			array(
				'status' => 'success',
			)
		);

		die();
	}

	/**
	 * Delete the application.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function delete_app() {
		global $wp_filesystem;

		check_ajax_referer( 'flowmattic-integration-nonce', 'security' );

		if ( empty( $wp_filesystem ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$data_slug = isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';
		$slug      = str_replace( '_', '-', $data_slug );

		$app_dir       = WP_CONTENT_DIR . '/flowmattic-apps/';
		$installed_app = $app_dir . $slug;

		$wp_filesystem->rmdir( $installed_app, true );

		// Get the workflows having this app.
		$workflows = wp_flowmattic()->workflows_db->get_workflow_by_trigger_application( $data_slug );

		if ( ! empty( $workflows ) ) {
			foreach ( $workflows as $key => $workflow ) {
				$workflow_steps     = json_decode( $workflow->workflow_steps, true );
				$settings           = json_decode( $workflow->workflow_settings, true );
				$settings['status'] = 'off';

				$workflow_data = array(
					'workflow_id'       => $workflow->workflow_id,
					'workflow_name'     => $workflow->workflow_name,
					'workflow_steps'    => $workflow_steps,
					'workflow_settings' => $settings,
				);

				$workflow_db = wp_flowmattic()->workflows_db;
				$status      = $workflow_db->update( $workflow_data, $workflow->workflow_id );
			}
		}

		echo wp_json_encode(
			array(
				'status' => 'success',
			)
		);

		die();
	}

	/**
	 * Disable the core app.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function disable_core_app() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$app_slug = $_POST['appSlug'];
		$settings = get_option( 'flowmattic_settings', array() );

		// Set the app disabled.
		$settings[ 'disable-app-' . $app_slug ] = $app_slug;

		// Update in database.
		update_option( 'flowmattic_settings', $settings );

		// Get the workflows having this app.
		$workflows = wp_flowmattic()->workflows_db->get_workflow_by_trigger_application( $app_slug );

		if ( ! empty( $workflows ) ) {
			foreach ( $workflows as $key => $workflow ) {
				$workflow_steps     = json_decode( $workflow->workflow_steps, true );
				$settings           = json_decode( $workflow->workflow_settings, true );
				$settings['status'] = 'off';

				$workflow_data = array(
					'workflow_id'       => $workflow->workflow_id,
					'workflow_name'     => $workflow->workflow_name,
					'workflow_steps'    => $workflow_steps,
					'workflow_settings' => $settings,
				);

				$workflow_db = wp_flowmattic()->workflows_db;
				$status      = $workflow_db->update( $workflow_data, $workflow->workflow_id );
			}
		}

		echo wp_json_encode(
			array(
				'status' => 'success',
			)
		);

		die();
	}

	/**
	 * Enable the core app.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function enable_core_app() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$app_slug = $_POST['appSlug'];
		$settings = get_option( 'flowmattic_settings', array() );

		// Set the app enabled.
		unset( $settings[ 'disable-app-' . $app_slug ] );

		// Update in database.
		update_option( 'flowmattic_settings', $settings );

		echo wp_json_encode(
			array(
				'status' => 'success',
			)
		);

		die();
	}
}
