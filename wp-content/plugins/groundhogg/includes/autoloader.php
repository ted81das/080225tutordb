<?php

namespace Groundhogg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Groundhogg autoloader.
 *
 * Groundhogg autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 *
 * Borrowed from Elementor, thanks guys...
 *
 * @since 2.0
 */
class Autoloader {

	/**
	 * Classes map.
	 *
	 * Maps Groundhogg classes to file names.
	 *
	 * @since  1.6.0
	 * @access private
	 * @static
	 *
	 * @var array Classes used by groundhogg.
	 */
	private static $classes_map = [
		'Notices'                           => 'includes/notices.php',
		'Scripts'                           => 'includes/scripts.php',
		'Preferences'                       => 'includes/preferences.php',
		'Tracking'                          => 'includes/tracking.php',
		'Rewrites'                          => 'includes/rewrites.php',
		'Replacements'                      => 'includes/replacements.php',
		'Settings'                          => 'includes/settings.php',
		'Tag_Mapping'                       => 'includes/tag-mapping.php',
		'Modal'                             => 'includes/modal.php',
		'Main_Updater'                      => 'includes/main-updater.php',
		'Old_Updater'                       => 'includes/old-updater.php',
		'Main_Roles'                        => 'includes/main-roles.php',
		'Main_Installer'                    => 'includes/main-installer.php',
		'Compliance'                        => 'includes/compliance.php',
		'Contact_Query'                     => 'includes/contact-query.php',
		'Shortcodes'                        => 'includes/shortcodes.php',
		'Supports_Errors'                   => 'includes/supports-errors.php',
		'Sending_Service'                   => 'includes/sending-service.php',
		'Template_Loader'                   => 'includes/template-loader.php',
		'GH_SS_Mailer'                      => 'includes/gh-ss-mailer.php',
		'GH_Mailer'                         => 'includes/mailer/gh-mailer.php',
		'HTML'                              => 'includes/utils/html.php',
		'Utils'                             => 'includes/utils/utils.php',
		'Email_Parser'                      => 'includes/utils/email-parser.php',
		'Files'                             => 'includes/utils/files.php',
		'Location'                          => 'includes/utils/location.php',
		'Updater'                           => 'includes/utils/updater.php',
		'Roles'                             => 'includes/utils/roles.php',
		'Installer'                         => 'includes/utils/installer.php',
		'Date_Time'                         => 'includes/utils/date-time.php',
		'Base_Object'                       => 'includes/classes/base-object.php',
		'DB_Object'                         => 'includes/classes/db-object.php',
		'Base_Object_With_Meta'             => 'includes/classes/base-object-with-meta.php',
		'DB_Object_With_Meta'               => 'includes/classes/db-object-with-meta.php',
		'Contact'                           => 'includes/classes/contact.php',
		'Campaign'                          => 'includes/classes/campaign.php',
		'Email_Log_Item'                    => 'includes/classes/email-log-item.php',
		'Submission'                        => 'includes/classes/submission.php',
		'Reports'                           => 'includes/reporting/reports.php',
		'Email'                             => 'includes/classes/email.php',
		'Library_Email'                     => 'includes/classes/library-email.php',
		'Event'                             => 'includes/classes/event.php',
		'Event_Queue_Item'                  => 'includes/classes/event-queue-item.php',
		'Tag'                               => 'includes/classes/tag.php',
		'Broadcast'                         => 'includes/classes/broadcast.php',
		'Funnel'                            => 'includes/classes/funnel.php',
		'Step'                              => 'includes/classes/step.php',
//		'Background_Task'                   => 'includes/classes/background-task.php',
		'Temp_Step'                         => 'includes/classes/temp-step.php',
		'Bulk_Jobs\Bulk_Job'                => 'includes/bulk-jobs/bulk-job.php',
		'Bulk_Jobs\Broadcast_Bulk_Job'      => 'includes/bulk-jobs/broadcast-bulk-job.php',
		'Event_Process'                     => 'includes/interfaces/event-process.php',
		// Form
		'Form\Form'                         => 'includes/form/form.php',
		'Form\Fields\Field'                 => 'includes/form/fields/field.php',
		'Form\Fields\Column'                => 'includes/form/fields/column.php',
		'Form\Fields\Row'                   => 'includes/form/fields/row.php',
		'Form\Fields\Input'                 => 'includes/form/fields/input.php',
		'Form\Fields\Text'                  => 'includes/form/fields/text.php',
		'Form\Fields\Textarea'              => 'includes/form/fields/textarea.php',
		'Form\Fields\First'                 => 'includes/form/fields/first.php',
		'Form\Fields\Last'                  => 'includes/form/fields/last.php',
		'Steps\Benchmarks\Form_Integration' => 'includes/steps/benchmarks/base/form-integration.php',
		//
		'Block_Registry'                    => 'includes/block-registry.php',
		'Background_Tasks'                  => 'includes/background-tasks.php',
		'Big_File_Uploader'                 => 'includes/big-file-uploader.php',
	];

	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @since  1.6.0
	 * @access public
	 * @static
	 */
	public static function run() {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @since  1.6.0
	 * @access private
	 * @static
	 *
	 * @param string $relative_class_name Class name.
	 *
	 */
	private static function load_class( $relative_class_name ) {

		if ( isset( self::$classes_map[ $relative_class_name ] ) ) {
			$filename = GROUNDHOGG_PATH . '/' . self::$classes_map[ $relative_class_name ];
		} else {
			$filename = strtolower(
				preg_replace(
					[ '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
					[ '$1-$2', '-', DIRECTORY_SEPARATOR ],
					$relative_class_name
				)
			);

			$is_filename = GROUNDHOGG_PATH . $filename . '.php';

			if ( ! file_exists( $is_filename ) ) {
				$filename = wp_normalize_path( GROUNDHOGG_PATH . 'includes/' . $filename . '.php' );
			} else {
				$filename = $is_filename;
			}
		}

		if ( is_readable( $filename ) ) {
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @since  1.6.0
	 * @access private
	 * @static
	 *
	 * @param string $class Class name.
	 *
	 */
	private static function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}

		$relative_class_name = preg_replace( '/^' . __NAMESPACE__ . '\\\/', '', $class );

		$final_class_name = __NAMESPACE__ . '\\' . $relative_class_name;

		if ( ! class_exists( $final_class_name ) ) {
			self::load_class( $relative_class_name );
		}
	}
}
