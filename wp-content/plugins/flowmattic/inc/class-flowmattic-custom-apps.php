<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Apps main class.
 *
 * @since 3.0
 */
class FlowMattic_Custom_Apps {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'flowmattic_load_custom_apps', array( $this, 'load_custom_apps' ), 99 );

		// Ajax to save app.
		add_action( 'wp_ajax_flowmattic_custom_app_save_info', array( $this, 'save_info' ) );

		// Ajax to delete app.
		add_action( 'wp_ajax_flowmattic_custom_app_delete_app', array( $this, 'delete_app' ) );

		// Ajax to save trigger.
		add_action( 'wp_ajax_flowmattic_custom_app_save_trigger', array( $this, 'save_trigger' ) );

		// Ajax to rename trigger.
		add_action( 'wp_ajax_flowmattic_custom_app_rename_trigger', array( $this, 'rename_trigger' ) );

		// Ajax to delete trigger.
		add_action( 'wp_ajax_flowmattic_custom_app_delete_trigger', array( $this, 'delete_trigger' ) );

		// Ajax to get the trigger.
		add_action( 'wp_ajax_flowmattic_custom_app_get_trigger', array( $this, 'get_trigger' ) );

		// Ajax to save action.
		add_action( 'wp_ajax_flowmattic_custom_app_save_action', array( $this, 'save_action' ) );

		// Ajax to get the action.
		add_action( 'wp_ajax_flowmattic_custom_app_get_action', array( $this, 'get_action' ) );

		// Ajax to handle action delete request.
		add_action( 'wp_ajax_flowmattic_custom_app_delete_action', array( $this, 'delete_action' ) );

		// Ajax to handle app export.
		add_action( 'wp_ajax_flowmattic_export_custom_app', array( $this, 'export_app' ) );

		// Ajax to handle the app import.
		add_action( 'wp_ajax_flowmattic_import_custom_app', array( $this, 'import_app' ) );

		// Ajax to handle the cURL import.
		add_action( 'wp_ajax_flowmattic_custom_app_import_curl', array( $this, 'import_curl_action' ) );
	}

	/**
	 * Process the ajax to save the app info.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function save_info() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$app_name       = $data['app_name'];
			$app_id         = $data['app_id'];
			$app_settings   = array(
				'description' => isset( $data['app_description'] ) ? $data['app_description'] : '',
				'connect_id'  => isset( $data['app_connect_id'] ) ? $data['app_connect_id'] : '',
				'app_logo'    => isset( $data['app_logo'] ) ? $data['app_logo'] : '',
			);

			// Generate the array data to store in database.
			$args = array(
				'app_id'       => esc_attr( $app_id ),
				'app_name'     => esc_attr( $app_name ),
				'app_settings' => maybe_serialize( $app_settings ),
			);

			// Check if app already exists.
			$exists = $custom_apps_db->get( $args );

			if ( ! $exists ) {
				$args['app_actions']  = '';
				$args['app_triggers'] = '';

				// Create a new app.
				$custom_apps_db->insert( $args );
			} else {
				// Update existing app.
				$custom_apps_db->update( $args );
			}

			echo wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'App saved successfully!', 'flowmattic' ),
				)
			);
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to delete the app.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function delete_app() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Request to delete the app.
			$custom_apps_db->delete( $args );

			echo wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'App deleted successfully!', 'flowmattic' ),
				)
			);
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to save the app triggers.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function save_trigger() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$app_id         = $data['app_id'];

			// Generate custom trigger id.
			$trigger_id = ( isset( $data['trigger_id'] ) && '' !== $data['trigger_id'] ) ? $data['trigger_id'] : flowmattic_random_string( 8 );

			// Args to fetch from database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Remove the unwanted params from array.
			unset( $data['action'] );
			unset( $data['app_id'] );
			unset( $data['workflow_nonce'] );
			unset( $data['action'] );
			unset( $data['trigger_id'] );

			// Check if app already exists.
			$exists = $custom_apps_db->get( $args );

			if ( ! $exists ) {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid App ID.', 'flowmattic' ),
					)
				);
			} else {
				$app_triggers = isset( $exists->app_triggers ) ? maybe_unserialize( $exists->app_triggers ) : array();

				// For backward compatibility.
				if ( '' === $app_triggers ) {
					$app_triggers = array();
				}

				// Set the trigger to the main array.
				$app_triggers[ $trigger_id ] = $data;

				// Assign the triggers array as serialized data.
				$args['app_triggers'] = maybe_serialize( $app_triggers );

				// Update existing app.
				$custom_apps_db->update( $args );

				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => esc_html__( 'Trigger saved successfully!', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to rename the trigger.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function rename_trigger() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$trigger_id     = $data['trigger_id'];
			$trigger_name   = $data['trigger_name'];
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Get the trigger data.
			$app = $custom_apps_db->get( $args );

			if ( $app ) {
				$app_triggers = isset( $app->app_triggers ) ? maybe_unserialize( $app->app_triggers ) : array();

				// Set the trigger to the main array.
				$app_triggers[ $trigger_id ]['trigger_name'] = $trigger_name;

				// Assign the triggers array as serialized data.
				$args['app_triggers'] = maybe_serialize( $app_triggers );

				// Update existing app.
				$custom_apps_db->update( $args );

				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => esc_html__( 'Trigger renamed successfully!', 'flowmattic' ),
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid APP ID.', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to delete the trigger.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function delete_trigger() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$trigger_id     = $data['trigger_id'];
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Get the trigger data.
			$app = $custom_apps_db->get( $args );

			if ( $app ) {
				$app_triggers = isset( $app->app_triggers ) ? maybe_unserialize( $app->app_triggers ) : array();

				// Remove the trigger to the main array.
				unset( $app_triggers[ $trigger_id ] );

				// Assign the triggers array as serialized data.
				$args['app_triggers'] = maybe_serialize( $app_triggers );

				// Update existing app.
				$custom_apps_db->update( $args );

				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => esc_html__( 'Trigger deleted successfully!', 'flowmattic' ),
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid APP ID.', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to get the trigger.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function get_trigger() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$trigger_id     = $data['trigger_id'];
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Get the trigger data.
			$app = $custom_apps_db->get( $args );

			if ( $app ) {
				$app_triggers = isset( $app->app_triggers ) ? maybe_unserialize( $app->app_triggers ) : array();

				// Get the requested trigger.
				$requested_trigger = $app_triggers[ $trigger_id ];

				// Return the trigger data.
				echo wp_json_encode( $requested_trigger );
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid APP ID.', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to save the app actions.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function save_action() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$app_id         = $data['app_id'];

			// Generate custom action id.
			$action_id = ( isset( $data['action_id'] ) && '' !== $data['action_id'] ) ? $data['action_id'] : flowmattic_random_string( 8 );

			// Args to fetch from database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Remove the unwanted params from array.
			unset( $data['action'] );
			unset( $data['app_id'] );
			unset( $data['workflow_nonce'] );
			unset( $data['action'] );
			unset( $data['action_id'] );

			// Check if app already exists.
			$exists = $custom_apps_db->get( $args );

			if ( ! $exists ) {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid App ID.', 'flowmattic' ),
					)
				);
			} else {
				$app_actions = isset( $exists->app_actions ) ? maybe_unserialize( $exists->app_actions ) : array();

				// For backward compatibility.
				if ( '' === $app_actions ) {
					$app_actions = array();
				}

				// If contains raw data, encode it.
				if ( isset( $data['raw_data'] ) ) {
					$data['raw_data'] = stripslashes( $data['raw_data'] );
				}

				// Set the action to the main array.
				$app_actions[ $action_id ] = $data;

				// Assign the actions array as serialized data.
				$args['app_actions'] = maybe_serialize( $app_actions );

				// Update existing app.
				$custom_apps_db->update( $args );

				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => esc_html__( 'Action saved successfully!', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to get the action.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function get_action() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$action_id      = $data['action_id'];
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Get the action data.
			$app = $custom_apps_db->get( $args );

			if ( $app ) {
				$app_actions = isset( $app->app_actions ) ? maybe_unserialize( $app->app_actions ) : array();

				// Get the requested action.
				$requested_action = $app_actions[ $action_id ];

				// If contains raw data, encode it.
				if ( isset( $requested_action['raw_data'] ) ) {
					$requested_action['raw_data'] = stripslashes( $requested_action['raw_data'] );
				}

				// Return the action data.
				echo wp_json_encode( $requested_action );
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid APP ID.', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Process the ajax to delete the action.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function delete_action() {
		// Verify nonce.
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		if ( ! empty( $_POST ) ) {
			$custom_apps_db = wp_flowmattic()->custom_apps_db;
			$data           = $_POST;
			$action_id      = $data['action_id'];
			$app_id         = $data['app_id'];

			// Generate the array data to store in database.
			$args = array(
				'app_id' => esc_attr( $app_id ),
			);

			// Get the action data.
			$app = $custom_apps_db->get( $args );

			if ( $app ) {
				$app_actions = isset( $app->app_actions ) ? maybe_unserialize( $app->app_actions ) : array();

				// Remove the action to the main array.
				unset( $app_actions[ $action_id ] );

				// Assign the actions array as serialized data.
				$args['app_actions'] = maybe_serialize( $app_actions );

				// Update existing app.
				$custom_apps_db->update( $args );

				echo wp_json_encode(
					array(
						'status'  => 'success',
						'message' => esc_html__( 'Action deleted successfully!', 'flowmattic' ),
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid APP ID.', 'flowmattic' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No data received.', 'flowmattic' ),
				)
			);
		}

		wp_die();
	}

	/**
	 * Ajax to handle app export file download.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function export_app() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$app_id         = isset( $_POST['appID'] ) ? $_POST['appID'] : '';
		$custom_apps_db = wp_flowmattic()->custom_apps_db;

		// Get the app.
		$args = array(
			'app_id' => esc_attr( $app_id ),
		);

		// Get app.
		$app = $custom_apps_db->get( $args );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=flowmattic-app-' . $app_id . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $app );

		die();
	}

	/**
	 * Ajax to handle app import file download.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function import_app() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$app_data       = isset( $_POST['appData'] ) ? $_POST['appData'] : '';
		$custom_apps_db = wp_flowmattic()->custom_apps_db;

		$data = array(
			'app_id'       => flowmattic_random_string( 7 ),
			'app_name'     => $app_data['app_name'] . '_IMPORTED',
			'app_actions'  => stripslashes( $app_data['app_actions'] ),
			'app_triggers' => stripslashes( $app_data['app_triggers'] ),
			'app_settings' => stripslashes( $app_data['app_settings'] ),
		);

		// Create a new app.
		$custom_apps_db->insert( $data );

		echo wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'App imported.', 'flowmattic' ),
			)
		);

		die();
	}

	/**
	 * Ajax to handle app import from cURL.
	 *
	 * @access public
	 * @since 4.3.0
	 * @return void
	 */
	public function import_curl_action() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$curl_request = isset( $_POST['curl_request'] ) ? $_POST['curl_request'] : ''; // @codingStandardsIgnoreLine

		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'FlowMattic',
			),
			'body'    => base64_encode( $curl_request ), // @codingStandardsIgnoreLine
		);

		// Convert the cURL request.
		$curl_json_response = wp_remote_post( 'https://api.flowmattic.com/curl/', $args );
		$curl_json          = wp_remote_retrieve_body( $curl_json_response );

		// Decode the JSON data.
		$curl_data = json_decode( $curl_json, true );

		// Send the cURL request to the server.
		wp_send_json_success( $curl_data );

		die();
	}

	/**
	 * Load the custom apps into apps library in workflow.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function load_custom_apps() {
		// Get the db instance.
		$custom_apps_db = new FlowMattic_Database_Custom_Apps();

		// Get all custom apps.
		$all_custom_apps = $custom_apps_db->get_all();

		if ( ! empty( $all_custom_apps ) ) {
			foreach ( $all_custom_apps as $key => $app_item ) {
				self::add_custom_app( $app_item );
			}
		}
	}

	/**
	 * Add the custom app into apps library in workflow.
	 *
	 * @access public
	 * @since 3.0
	 * @param array $app_item Custom app settings.
	 * @return void
	 */
	public static function add_custom_app( $app_item ) {
		$custom_app_id = $app_item->app_id;
		$app_actions   = isset( $app_item->app_actions ) ? maybe_unserialize( $app_item->app_actions ) : array();
		$app_triggers  = isset( $app_item->app_triggers ) ? maybe_unserialize( $app_item->app_triggers ) : array();
		$app_settings  = isset( $app_item->app_settings ) ? maybe_unserialize( $app_item->app_settings ) : array();
		$needs_connect = isset( $app_item->needs_connect ) ? true : false;

		// Do not proceed if custom app has no actions or triggers.
		if ( ! empty( $app_triggers ) || ! empty( $app_actions ) ) {
			$custom_app = array(
				'name'         => $app_item->app_name,
				'icon'         => $app_settings['app_logo'],
				'version'      => isset( $app_item->version ) ? $app_item->version : '1.0',
				'instructions' => 'Copy the Webhook URL and send your request to this url from your application or website.',
				'custom_app'   => true,
				'app_settings' => $app_settings,
			);

			$app_slug = 'custom_app_';

			// If custom app template needs to show the connect selection.
			if ( $needs_connect ) {
				$custom_app['needs_connect'] = true;

				// Remove the connect ID from settings.
				unset( $custom_app['app_settings']['connect_id'] );

				// Set slug for external apps.
				$app_slug = 'app_';
			}

			$types = array();

			// Register triggers for the custom app.
			if ( ! empty( $app_triggers ) ) {
				$triggers = array();
				$types[]  = 'trigger';

				foreach ( $app_triggers as $trigger_key => $trigger_item ) {
					$trigger_name         = $trigger_item['trigger_name'];
					$trigger_description  = $trigger_item['trigger_description'];
					$webhook_instructions = $trigger_item['webhook_instructions'];

					$triggers[ $trigger_key ] = array(
						'title'        => $trigger_name,
						'description'  => $trigger_description,
						'instructions' => $webhook_instructions,
					);
				}

				$custom_app['triggers'] = $triggers;
			}

			// Register actions for the custom app.
			if ( ! empty( $app_actions ) ) {
				$actions = array();
				$types[] = 'action';

				foreach ( $app_actions as $action_key => $action_item ) {
					$action_name        = $action_item['action_name'];
					$action_description = $action_item['action_description'];

					// Remove the title and description fields.
					unset( $action_item['action_name'] );
					unset( $action_item['action_description'] );

					$actions[ $action_key ] = array(
						'title'       => $action_name,
						'description' => $action_description,
						'action_data' => $action_item,
					);
				}

				$custom_app['actions'] = $actions;
			}

			$custom_app['type'] = implode( ',', $types );

			// Set all applications array.
			flowmattic_add_application( $app_slug . $custom_app_id, $custom_app );
		}
	}
}
