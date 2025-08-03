<?php
/**
 * Application Name: WordPress
 * Description: Add WordPress integration to FlowMattic.
 * Version: 1.2
 * Author: InfiWebs
 * Author URI: https://www.infiwebs.com
 * Textdomain: flowmattic
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress integration class.
 *
 * @since 1.0
 */
class FlowMattic_Wordpress { // @codingStandardsIgnoreLine

	/**
	 * Variable to store if test event is running.
	 *
	 * @access protected
	 * @since 1.0
	 * @var bool
	 */
	protected $is_doing_test = false;

	/**
	 * Application name.
	 *
	 * @access protected
	 * @since 1.0
	 * @var bool
	 */
	protected $app_name = 'WordPress';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for WordPress.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			strtolower( $this->app_name ),
			array(
				'name'         => esc_attr__( 'WordPress', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/wordpress/icon.svg',
				'instructions' => 'WordPress is the world\'s most popular Content Management System.',
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'type'         => 'trigger, action',
			)
		);

		// Listen to WordPress user registration.
		add_action( 'user_register', array( $this, 'capture_user_registration' ), 10 );

		// Listen to WordPress user login.
		add_action( 'wp_login', array( $this, 'capture_user_login' ), 10, 2 );

		// Listen to new post creation.
		add_action( 'transition_post_status', array( $this, 'capture_post_publish' ), 10, 3 );

		// Listen to trash post action.
		add_action( 'draft_to_trash', array( $this, 'capture_post_trash' ), 10 );
		add_action( 'pending_to_trash', array( $this, 'capture_post_trash' ), 10 );
		add_action( 'publish_to_trash', array( $this, 'capture_post_trash' ), 10 );

		// Listen to existing post update.
		add_action( 'post_updated', array( $this, 'capture_post_update' ), 99, 3 );

		// Listen to page create/update.
		add_action( 'publish_page', array( $this, 'capture_page_update' ), 10, 3 );

		// Listen to new media upload.
		add_action( 'add_attachment', array( $this, 'capture_media_upload' ), 10 );

		// Listen to new comment post.
		add_action( 'comment_post', array( $this, 'capture_new_comment' ), 10, 3 );

		// Listen to comment approved.
		add_action( 'comment_approved', array( $this, 'capture_comment_approved' ), 10, 3 );

		// Listen to singular page view.
		add_action( 'wp_footer', array( $this, 'capture_singular_view' ), 99 );

		// Listen to post meta update.
		add_action( 'updated_post_meta', array( $this, 'capture_post_meta_update' ), 11, 4 );

		// Listen to post meta field update.
		add_action( 'update_post_meta', array( $this, 'capture_post_meta_field_update' ), 10, 4 );

		// Listen to user profile update.
		add_action( 'profile_update', array( $this, 'capture_user_profile_update' ), 10, 2 );

		// Listen to user profile field update.
		add_action( 'update_user_meta', array( $this, 'capture_user_profile_field_update' ), 10, 4 );

		// Listen to user password reset.
		add_action( 'password_reset', array( $this, 'capture_user_password_reset' ), 10, 2 );

		// Listen to user deletion.
		add_action( 'delete_user', array( $this, 'capture_deleted_user' ), 10, 3 );

		// Listen to category creation.
		add_action( 'created_category', array( $this, 'capture_term_create' ), 10, 3 );

		// Listen to tag creation.
		add_action( 'created_post_tag', array( $this, 'capture_term_create' ), 10, 3 );

		// Listen to any term creation.
		add_action( 'create_term', array( $this, 'capture_term_create' ), 10, 3 );

		// Listen to user role addition.
		add_action( 'add_user_role', array( $this, 'capture_user_role_add' ), 10, 2 );

		// Listen to user role removal.
		add_action( 'remove_user_role', array( $this, 'capture_user_role_remove' ), 15, 2 );

		// Listen to user role change.
		add_action( 'set_user_role', array( $this, 'capture_user_role_change' ), 10, 3 );

		// Listen to user role change from specific to set.
		add_action( 'set_user_role', array( $this, 'capture_user_role_from_specific_to_set' ), 10, 3 );

		// Ajax to fetch the record from database list.
		add_action( 'wp_ajax_flowmattic_wp_capture_from_database', array( $this, 'fetch_from_database' ) );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-wordpress', FLOWMATTIC_PLUGIN_URL . 'inc/apps/wordpress/view-wordpress.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );

		// Localize script for the flowmattic admin pages.
		wp_localize_script(
			'flowmattic-app-view-wordpress',
			'FMWPConfig',
			array(
				'user_roles'             => $this->get_all_user_roles(),
				'user_role_capabilities' => $this->get_all_user_role_capabilities(),
			)
		);
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'new_user'                       => array(
				'title'       => esc_attr__( 'New User', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new user is registered', 'flowmattic' ),
			),
			'new_post'                       => array(
				'title'       => esc_attr__( 'New Post', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new post is published', 'flowmattic' ),
			),
			'new_page'                       => array(
				'title'       => esc_attr__( 'New Page', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new page is published', 'flowmattic' ),
			),
			'new_media'                      => array(
				'title'       => esc_attr__( 'New Media', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new media is uploaded', 'flowmattic' ),
			),
			'new_comment'                    => array(
				'title'       => esc_attr__( 'New Comment', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new comment is added', 'flowmattic' ),
			),
			'comment_approved'               => array(
				'title'       => esc_attr__( 'Comment Approved', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a comment is approved', 'flowmattic' ),
			),
			'post_updated'                   => array(
				'title'       => esc_attr__( 'Update Post', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a published post is updated', 'flowmattic' ),
			),
			'page_updated'                   => array(
				'title'       => esc_attr__( 'Update Page', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a published page is updated', 'flowmattic' ),
			),
			'user_login'                     => array(
				'title'       => esc_attr__( 'User Login', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an user logs into the site', 'flowmattic' ),
			),
			'page_view'                      => array(
				'title'       => esc_attr__( 'User Visits a Page/Post', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an user visit page, post or any post type singular page.', 'flowmattic' ),
			),
			'trash_post'                     => array(
				'title'       => esc_attr__( 'User Deletes a Post', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an user delete post of any post type.', 'flowmattic' ),
			),
			'updated_post_meta'              => array(
				'title'       => esc_attr__( 'Post Meta Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when post meta is updated', 'flowmattic' ),
			),
			'updated_post_meta_field'        => array(
				'title'       => esc_attr__( 'Post Meta Field Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when post meta specific field is updated', 'flowmattic' ),
			),
			'updated_user_profile'           => array(
				'title'       => esc_attr__( 'User Profile Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user profile is updated', 'flowmattic' ),
			),
			'updated_profile_field'          => array(
				'title'       => esc_attr__( 'User Profile Field Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user profile specific field is updated', 'flowmattic' ),
			),
			'user_reset_password'            => array(
				'title'       => esc_attr__( 'User Resets Password', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user resets the password', 'flowmattic' ),
			),
			'user_deleted'                   => array(
				'title'       => esc_attr__( 'User Deleted', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user is deleted from the database', 'flowmattic' ),
			),
			'category_created'               => array(
				'title'       => esc_attr__( 'Category Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new category is created', 'flowmattic' ),
			),
			'tag_created'                    => array(
				'title'       => esc_attr__( 'Tag Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new tag is created', 'flowmattic' ),
			),
			'term_created'                   => array(
				'title'       => esc_attr__( 'Term Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new term is created', 'flowmattic' ),
			),
			'user_role_added'                => array(
				'title'       => esc_attr__( 'User Role Added', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new role is added to user', 'flowmattic' ),
			),
			'user_role_removed'              => array(
				'title'       => esc_attr__( 'User Role Removed', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a role is removed from user', 'flowmattic' ),
			),
			'user_role_changed'              => array(
				'title'       => esc_attr__( 'User Role Changed', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user role is changed', 'flowmattic' ),
			),
			'user_role_from_specific_to_set' => array(
				'title'       => esc_attr__( 'User Role Changed From Specific to Defined', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when user role is changed from specific role to defined role', 'flowmattic' ),
			),
		);
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'new_user'                  => array(
				'title'       => esc_attr__( 'New User', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new user', 'flowmattic' ),
			),
			'update_user'               => array(
				'title'       => esc_attr__( 'Update User', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing user details', 'flowmattic' ),
			),
			'add_user_role'             => array(
				'title'       => esc_attr__( 'Add Role to User', 'flowmattic' ),
				'description' => esc_attr__( 'Add role to existing user roles of an user', 'flowmattic' ),
			),
			'change_user_role'          => array(
				'title'       => esc_attr__( 'Change User Role', 'flowmattic' ),
				'description' => esc_attr__( 'Change user role of an existing user and set new role', 'flowmattic' ),
			),
			'remove_user_role'          => array(
				'title'       => esc_attr__( 'Remove User Role', 'flowmattic' ),
				'description' => esc_attr__( 'Remove user role of an existing user', 'flowmattic' ),
			),
			'delete_user'               => array(
				'title'       => esc_attr__( 'Delete User', 'flowmattic' ),
				'description' => esc_attr__( 'Delete user account from site', 'flowmattic' ),
			),
			'new_comment'               => array(
				'title'       => esc_attr__( 'New Comment', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new comment on post.', 'flowmattic' ),
			),
			'new_post'                  => array(
				'title'       => esc_attr__( 'New Post', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new post', 'flowmattic' ),
			),
			'update_post'               => array(
				'title'       => esc_attr__( 'Update Post', 'flowmattic' ),
				'description' => esc_attr__( 'Update existing post of any post type with custom fields', 'flowmattic' ),
			),
			'delete_post'               => array(
				'title'       => esc_attr__( 'Delete Post', 'flowmattic' ),
				'description' => esc_attr__( 'Delete post by ID from the site', 'flowmattic' ),
			),
			'new_media'                 => array(
				'title'       => esc_attr__( 'New Media', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new media', 'flowmattic' ),
			),
			'delete_media'              => array(
				'title'       => esc_attr__( 'Delete Media', 'flowmattic' ),
				'description' => esc_attr__( 'Delete media by ID from the media library', 'flowmattic' ),
			),
			'rename_media'              => array(
				'title'       => esc_attr__( 'Rename Media', 'flowmattic' ),
				'description' => esc_attr__( 'Rename media title in media library', 'flowmattic' ),
			),
			'update_user_meta'          => array(
				'title'       => esc_attr__( 'Update User Meta', 'flowmattic' ),
				'description' => esc_attr__( 'Update User Metadata', 'flowmattic' ),
			),
			'get_user_by_id'            => array(
				'title'       => esc_attr__( 'Get User by ID', 'flowmattic' ),
				'description' => esc_attr__( 'Get an user profile by ID', 'flowmattic' ),
			),
			'get_user_by_email'         => array(
				'title'       => esc_attr__( 'Get User by Email', 'flowmattic' ),
				'description' => esc_attr__( 'Get an user profile by Email', 'flowmattic' ),
			),
			'create_category'           => array(
				'title'       => esc_attr__( 'Create Category', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new category', 'flowmattic' ),
			),
			'create_tag'                => array(
				'title'       => esc_attr__( 'Create Tag', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new post tag', 'flowmattic' ),
			),
			'add_role'                  => array(
				'title'       => esc_attr__( 'Create Role', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new user role, if not exists', 'flowmattic' ),
			),
			'get_post_meta'             => array(
				'title'       => esc_attr__( 'Get Post Metadata', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all or custom metadata from any post', 'flowmattic' ),
			),
			'get_user_meta'             => array(
				'title'       => esc_attr__( 'Get User Metadata', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all or custom metadata from any user', 'flowmattic' ),
			),
			'get_posts_by_post_type'    => array(
				'title'       => esc_attr__( 'Get Posts by Post Type', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all posts of specific post type', 'flowmattic' ),
			),
			'get_posts_by_meta'         => array(
				'title'       => esc_attr__( 'Get Posts by Metadata', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all posts of specific post type by metadata or custom fields', 'flowmattic' ),
			),
			'get_post_taxonomies'       => array(
				'title'       => esc_attr__( 'Get Post Taxonomies', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all taxonomies of a post', 'flowmattic' ),
			),
			'get_the_terms'             => array(
				'title'       => esc_attr__( 'Get Post Taxonomy Terms', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieves the terms of the taxonomy that are attached to the post', 'flowmattic' ),
			),
			'get_post_by_id'            => array(
				'title'       => esc_attr__( 'Get Post by ID', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve the post details by ID', 'flowmattic' ),
			),
			'get_taxonomy_by_name'      => array(
				'title'       => esc_attr__( 'Get Taxonomy by Name', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve the taxonomy details by name', 'flowmattic' ),
			),
			'get_all_users_by_role'     => array(
				'title'       => esc_attr__( 'Get All Users by Role', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve all users by user role', 'flowmattic' ),
			),
			'add_tag_to_post'           => array(
				'title'       => esc_attr__( 'Add Tag to Post', 'flowmattic' ),
				'description' => esc_attr__( 'Add a tag to post', 'flowmattic' ),
			),
			'remove_tag_from_post'      => array(
				'title'       => esc_attr__( 'Remove Tag from Post', 'flowmattic' ),
				'description' => esc_attr__( 'Remove a tag from post', 'flowmattic' ),
			),
			'add_category_to_post'      => array(
				'title'       => esc_attr__( 'Add Category to Post', 'flowmattic' ),
				'description' => esc_attr__( 'Add a category to post', 'flowmattic' ),
			),
			'remove_category_from_post' => array(
				'title'       => esc_attr__( 'Remove Category from Post', 'flowmattic' ),
				'description' => esc_attr__( 'Remove a category from post', 'flowmattic' ),
			),
			'check_plugin_active'       => array(
				'title'       => esc_attr__( 'Check Plugin Active', 'flowmattic' ),
				'description' => esc_attr__( 'Check if a plugin is active', 'flowmattic' ),
			),
			'activate_plugin'           => array(
				'title'       => esc_attr__( 'Activate Plugin', 'flowmattic' ),
				'description' => esc_attr__( 'Activate a plugin if it\'s not already active', 'flowmattic' ),
			),
			'update_category'           => array(
				'title'       => esc_attr__( 'Update Category', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing category details', 'flowmattic' ),
			),
			'update_tag'                => array(
				'title'       => esc_attr__( 'Update Tag', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing tag details', 'flowmattic' ),
			),
			'update_term'               => array(
				'title'       => esc_attr__( 'Update Term', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing term ( Custom taxonomy ) details', 'flowmattic' ),
			),
			'search_media_by_title'     => array(
				'title'       => esc_attr__( 'Search Media by Title', 'flowmattic' ),
				'description' => esc_attr__( 'Search media by title in media library', 'flowmattic' ),
			),
			'add_taxonomy_to_post'       => array(
				'title'       => esc_attr__( 'Add Taxonomy to Post', 'flowmattic' ),
				'description' => esc_attr__( 'Add a taxonomy to post', 'flowmattic' ),
			),
			'remove_taxonomy_from_post'  => array(
				'title'       => esc_attr__( 'Remove Taxonomy from Post', 'flowmattic' ),
				'description' => esc_attr__( 'Remove a taxonomy from post', 'flowmattic' ),
			),
			'create_term'                => array(
				'title'       => esc_attr__( 'Create Term', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new taxonomy term', 'flowmattic' ),
			),
			'get_media_by_id'            => array(
				'title'       => esc_attr__( 'Get Media by ID', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve the media details by ID', 'flowmattic' ),
			),
		);
	}

	/**
	 * Check if live capturing is in process for this application.
	 *
	 * @access public
	 * @since 1.0
	 * @return bool|string False if not this application, workflow id if current application.
	 */
	public function is_capturing() {
		$workflow_id   = get_option( 'webhook-capture-live', false );
		$workflow_name = get_option( 'webhook-capture-application', false );

		if ( strtolower( $this->app_name ) !== $workflow_name ) {
			return false;
		} else {
			return $workflow_id;
		}
	}

	/**
	 * Run the workflow step.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $action       WordPress action.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_workflow_step( $action, $capture_data ) {
		// If doing test, skip the workflow execution.
		if ( $this->is_doing_test ) {
			return false;
		}

		// Get the workflows having WordPress as trigger.
		$workflows = wp_flowmattic()->workflows_db->get_workflow_by_trigger_application( strtolower( $this->app_name ) );

		if ( ! empty( $workflows ) ) {
			foreach ( $workflows as $key => $workflow ) {
				$workflow_steps  = json_decode( $workflow->workflow_steps );
				$settings        = json_decode( $workflow->workflow_settings, true );
				$workflow_status = ( isset( $settings['status'] ) && $settings['status'] ) ? $settings['status'] : 'off';

				// Do not execute workflows that are not turned ON.
				if ( 'on' !== $workflow_status ) {
					continue;
				}

				// Loop through each workflow to check the action event.
				if ( ! empty( $workflow_steps ) ) {
					foreach ( $workflow_steps as $step ) {
						if ( 'trigger' === $step->type && strtolower( $this->app_name ) === $step->application ) {
							$workflow_id = $workflow->workflow_id;

							if ( 'page_view' === $action ) {
								$trigger_post_id = ( isset( $step->trigger_post_id ) && '' !== $step->trigger_post_id ) ? $step->trigger_post_id : '';

								if ( '' !== $trigger_post_id && (int) $capture_data['id'] !== (int) $trigger_post_id ) {
									continue;
								}

								$trigger_post_type = ( isset( $step->trigger_post_type ) && '' !== $step->trigger_post_type ) ? $step->trigger_post_type : '';

								if ( '' !== $trigger_post_type && get_post_type( $capture_data['id'] ) !== $trigger_post_type ) {
									continue;
								}
							}

							// If action is either category_created or tag_created, then run the workflow.
							if ( in_array( $step->action, array( 'term_created', 'category_created', 'tag_created' ), true ) ) {
								$action = $step->action;
							}

							if ( $action === $step->action ) {
								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );

								break;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step   = (array) $step;
		$action = $step['action'];
		$fields = $step;

		// CS.
		$capture_data;

		switch ( $action ) {
			case 'new_user':
				$response = $this->create_new_user( $fields );
				break;

			case 'update_user':
				$response = $this->update_existing_user( $fields );
				break;

			case 'delete_post':
				$response = $this->delete_post( $fields );
				break;

			case 'new_media':
				$response = $this->create_new_media( $fields );
				break;

			case 'new_comment':
				$response = $this->create_new_comment( $fields );
				break;

			case 'new_post':
				$response = $this->create_new_post( $fields );
				break;

			case 'update_post':
				$response = $this->update_post( $fields );
				break;

			case 'update_user_meta':
				if ( isset( $step['actionAppArgs']['user_id'] ) ) {
					$fields['user_id'] = $step['actionAppArgs']['user_id'];
				}

				$fields['meta_fields'] = isset( $step['meta_fields'] ) ? $step['meta_fields'] : array();

				$response = $this->update_user_meta( $fields );
				break;

			case 'get_user_by_id':
				if ( isset( $step['actionAppArgs']['user_id'] ) ) {
					$fields['user_id'] = $step['actionAppArgs']['user_id'];
				}

				$response = $this->get_user_by_id( $fields );
				break;

			case 'get_user_by_email':
				if ( isset( $step['actionAppArgs']['user_email'] ) ) {
					$fields['user_email'] = $step['actionAppArgs']['user_email'];
				}

				$response = $this->get_user_by_email( $fields );
				break;

			case 'create_category':
				if ( isset( $step['actionAppArgs']['category_name'] ) ) {
					$fields['category_name'] = $step['actionAppArgs']['category_name'];
				}

				$response = $this->create_category( $fields );
				break;

			case 'create_tag':
				if ( isset( $step['actionAppArgs']['tag_name'] ) ) {
					$fields['tag_name'] = $step['actionAppArgs']['tag_name'];
				}

				$response = $this->create_tag( $fields );
				break;

			case 'add_user_role':
				$response = $this->add_user_role( $fields );
				break;

			case 'change_user_role':
				$response = $this->change_user_role( $fields );
				break;

			case 'remove_user_role':
				$response = $this->remove_user_role( $fields );
				break;

			case 'delete_user':
				$response = $this->delete_user( $fields );
				break;

			case 'add_role':
				if ( isset( $step['actionAppArgs'] ) ) {
					$fields = $step['actionAppArgs'];
				}

				$response = $this->add_role( $fields );
				break;

			case 'get_post_meta':
				$response = $this->get_post_meta( $fields );
				break;

			case 'get_user_meta':
				$response = $this->get_user_meta( $fields );
				break;

			case 'get_posts_by_meta':
				$response = $this->get_posts_by_meta( $fields );
				break;

			case 'get_post_taxonomies':
				$response = $this->get_post_taxonomies( $fields );
				break;

			case 'get_the_terms':
				$response = $this->get_the_terms( $fields );
				break;

			case 'get_posts_by_post_type':
				$response = $this->get_posts_by_post_type( $fields );
				break;

			case 'get_post_by_id':
				$response = $this->get_post_by_id( $fields );
				break;

			case 'get_taxonomy_by_name':
				$response = $this->get_taxonomy_by_name( $fields );
				break;

			case 'get_all_users_by_role':
				$response = $this->get_all_users_by_role( $fields );
				break;

			case 'delete_media':
				$response = $this->delete_media( $fields );
				break;

			case 'rename_media':
				$response = $this->rename_media( $fields );
				break;

			case 'add_tag_to_post':
				$response = $this->add_tag_to_post( $fields );
				break;

			case 'remove_tag_from_post':
				$response = $this->remove_tag_from_post( $fields );
				break;

			case 'add_category_to_post':
				$response = $this->add_category_to_post( $fields );
				break;

			case 'remove_category_from_post':
				$response = $this->remove_category_from_post( $fields );
				break;

			case 'check_plugin_active':
				$response = $this->check_plugin_active( $fields );
				break;

			case 'activate_plugin':
				$response = $this->activate_plugin( $fields );
				break;

			case 'update_category':
				$response = $this->update_category( $fields );
				break;

			case 'update_tag':
				$response = $this->update_tag( $fields );
				break;

			case 'update_term':
				$response = $this->update_term( $fields );
				break;

			case 'search_media_by_title':
				$response = $this->search_media_by_title( $fields );
				break;

			case 'add_taxonomy_to_post':
				$response = $this->add_taxonomy_to_post( $fields );
				break;

			case 'remove_taxonomy_from_post':
				$response = $this->remove_taxonomy_from_post( $fields );
				break;

			case 'create_term':
				$response = $this->create_term( $fields );
				break;

			case 'get_media_by_id':
				$response = $this->get_media_by_id( $fields );
				break;
		}

		return $response;
	}

	/**
	 * Action fires after WordPress user is registered.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $user_id Registered user ID.
	 * @return bool
	 */
	public function capture_user_registration( $user_id ) {
		$workflow_executed = get_user_meta( $user_id, 'workflow_executed', true );

		if ( '1' === $workflow_executed ) {
			return false;
		}

		$workflow_id = $this->is_capturing();

		$user_data = $this->get_user_details_by_id( $user_id );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'new_user' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $user_data, false );
			}
		}

		// Set workflow executed flag.
		update_user_meta( $user_id, 'workflow_executed', '1' );

		// Run the workflow.
		$this->run_workflow_step( 'new_user', $user_data );
	}

	/**
	 * Action fires after new post is published.
	 *
	 * @access public
	 * @since 1.0
	 * @param string  $new_status Transition to this post status.
	 * @param string  $old_status Previous post status.
	 * @param WP_Post $new_post   Post data.
	 * @return void
	 */
	public function capture_post_publish( $new_status, $old_status, $new_post ) {
		// Bail, if already published post is being updated.
		if ( 'publish' === $old_status ) {
			return;
		}

		// Bail, if current post is not being published.
		if ( 'publish' !== $new_status ) {
			return;
		}

		// Prevent if post type is page.
		if ( 'page' === $new_post->post_type || 'nav_menu_item' === $new_post->post_type ) {
			return;
		}

		// Get the workflow executed flag.
		$is_workflow_executed = get_post_meta( $new_post->ID, 'workflow_executed', true );

		if ( 'yes' === $is_workflow_executed ) {
			return;
		}

		// Set the workflow executed flag for this order.
		update_post_meta( $new_post->ID, 'workflow_executed', 'yes' );

		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $new_post;

		// Get the permalink of the post.
		$post_data['permalink'] = get_permalink( $new_post->ID );

		$current_taxonomies = get_object_taxonomies( $new_post, 'objects' );
		$taxonomies         = array();

		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $new_post, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$post_data['taxonomies'] = wp_json_encode( $taxonomies );

		// Get the post featured image.
		$post_thumbnail_id = get_post_thumbnail_id( $new_post->ID );
		$featured_image    = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

		if ( $featured_image ) {
			$post_data['featured_image']    = $featured_image[0];
			$post_data['featured_image_id'] = $post_thumbnail_id;
		}

		// Get post Meta.
		$post_meta_data = get_post_meta( $new_post->ID );

		foreach ( $post_meta_data as $key => $post_meta ) {
			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$post_meta = get_field( $key, $new_post->ID );
			}

			$post_meta_data[ $key ] = $post_meta;
		}

		$post_data['post_meta'] = wp_json_encode( $post_meta_data );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'new_post' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'new_post', $post_data );
	}

	/**
	 * Action fires after post is trashed.
	 *
	 * @access public
	 * @since 1.2
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function capture_post_trash( $post ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $post;

		$current_taxonomies = get_object_taxonomies( $post, 'objects' );
		$taxonomies         = array();

		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $post, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$post_data['taxonomies'] = wp_json_encode( $taxonomies );

		// User who trashed this post.
		$current_user                   = wp_get_current_user();
		$post_data['user_name']         = esc_html( $current_user->user_login );
		$post_data['user_email']        = esc_html( $current_user->user_email );
		$post_data['user_first_name']   = esc_html( $current_user->user_firstname );
		$post_data['user_last_name']    = esc_html( $current_user->user_lastname );
		$post_data['user_display_name'] = esc_html( $current_user->display_name );
		$post_data['user_ID']           = esc_html( $current_user->ID );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'trash_post' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'trash_post', $post_data );
	}

	/**
	 * Action fires after existing post is updated.
	 *
	 * @access public
	 * @since 1.0
	 * @param int     $post_id     ID of the post being updated.
	 * @param WP_Post $post_after  Post object.
	 * @param bool    $post_before Post object before update.
	 * @return void
	 */
	public function capture_post_update( $post_id, $post_after, $post_before ) {
		// Continue if existing post is being updated.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $post_after;

		// Prevent if post type is page.
		if ( 'page' === $post_after->post_type ) {
			return;
		}

		// Get the workflow executed flag.
		$workflow_executed_at = get_post_meta( $post_after->ID, 'workflow_executed_at', time() );

		if ( time() <= $workflow_executed_at ) {
			return;
		}

		// Set the workflow executed flag for this order.
		update_post_meta( $post_after->ID, 'workflow_executed_at', time() + 5 );

		// Add permalink of the post.
		$post_data['permalink'] = get_permalink( $post_after->ID );

		$current_taxonomies = get_object_taxonomies( $post_after, 'objects' );
		$taxonomies         = array();

		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $post_after, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$post_data['taxonomies'] = wp_json_encode( $taxonomies );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'post_updated' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'post_updated', $post_data );
	}

	/**
	 * Action fires after existing page is updated.
	 *
	 * @access public
	 * @since 4.0
	 * @param int     $post_id     ID of the post being updated.
	 * @param WP_Post $post_after  Post object.
	 * @param bool    $update      Whether this is an existing post being updated or not.
	 * @return void
	 */
	public function capture_page_update( $post_id, $post_after, $update ) {
		// Get last execution time for this workflow.
		$workflow_executed_at = get_transient( 'workflow_' . $post_id . '_execution_time' );

		// If workflow execution time is greater than current time, then skip the workflow.
		if ( false !== $workflow_executed_at && time() <= $workflow_executed_at ) {
			return;
		}

		// Continue if existing post is being updated.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $post_after;

		// Get the permalink of the post.
		$post_data['permalink'] = get_permalink( $post_after->ID );

		// Get page featured image.
		if ( has_post_thumbnail( $post_id ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			$featured_image    = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

			if ( $featured_image ) {
				$post_data['featured_image']    = $featured_image[0];
				$post_data['featured_image_id'] = $post_thumbnail_id;
			}
		}

		// Get post Meta.
		$post_meta_data = get_post_meta( $post_after->ID );

		foreach ( $post_meta_data as $key => $post_meta ) {
			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$post_meta = get_field( $key, $post_after->ID );
			}

			$post_meta_data[ $key ] = $post_meta;
		}

		$post_data['post_meta'] = wp_json_encode( $post_meta_data );

		$trigger_event_type = $update ? 'page_updated' : 'new_page';

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && $trigger_event_type === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Store the current execution time for this workflow in session.
		$execution_time = time() + 3; // 3 seconds buffer time.

		set_transient( 'workflow_' . $post_id . '_execution_time', $execution_time, 2 );

		// Run the workflow.
		$this->run_workflow_step( $trigger_event_type, $post_data );
	}

	/**
	 * Action fires after media is uploaded.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $post_id Attachment ID.
	 * @return void
	 */
	public function capture_media_upload( $post_id ) {
		$workflow_id = $this->is_capturing();

		$post_data = array();

		// Get the post featured image data.
		$post_data = flowmattic_get_attachment( $post_id );

		// Add attachment ID.
		$post_data['attachment_id'] = $post_id;

		// Get post Meta.
		$post_meta_data = get_post_meta( $post_id );

		foreach ( $post_meta_data as $key => $post_meta ) {
			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$post_meta = get_field( $key, $post_id );
			}

			$post_meta_data[ $key ] = $post_meta;
		}

		$post_data['attachment_meta'] = wp_json_encode( $post_meta_data );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
		}

		// Run the workflow.
		$this->run_workflow_step( 'new_media', $post_data );
	}

	/**
	 * Action fires after new comment is inserted into the database.
	 *
	 * @access public
	 * @since 1.0
	 * @param int        $comment_id       Comment ID.
	 * @param int|string $comment_approved If comment is approved or is spam.
	 * @param array      $comment_data     Comment data.
	 * @return void
	 */
	public function capture_new_comment( $comment_id, $comment_approved, $comment_data ) {
		$commented_post = get_post( $comment_data['comment_post_ID'] );
		$workflow_id    = $this->is_capturing();

		$post_data = $comment_data;

		// Add the comment id.
		$post_data['comment_id'] = $comment_id;

		$current_taxonomies = get_object_taxonomies( $commented_post, 'objects' );
		$taxonomies         = array();

		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $commented_post, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$post_data['taxonomies']                = wp_json_encode( $taxonomies );
		$post_data['commented_posts_post_type'] = get_post_type( $commented_post );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
		}

		// Run the workflow.
		$this->run_workflow_step( 'new_comment', $post_data );
	}

	/**
	 * Action fires after comment approved into the database.
	 *
	 * @access public
	 * @since 1.0
	 * @param int        $comment    Comment.
	 * @param int|string $new_status If comment is approved or is spam.
	 * @param array      $old_status Comment data.
	 * @return void
	 */
	public function capture_comment_approved( $comment, $new_status, $old_status ) {
		if ( 'approved' !== $new_status ) {
			return;
		}

		$commented_post = get_post( $comment->comment_post_ID );

		$post_data = array(
			'comment_id'   => $comment->comment_ID,
			'comment_post' => $commented_post,
		);

		$this->run_workflow_step( 'comment_approved', $post_data );
	}

	/**
	 * Capture singular view.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function capture_singular_view() {
		global $post;

		// Log the current URL.
		$referrer = $_SERVER['REQUEST_URI']; // @codingStandardsIgnoreLine

		if ( is_admin() ) {
			return;
		}

		// If the referrer is browser js file or favicon, then ignore.
		if ( false !== strpos( $referrer, '.js' ) || false !== strpos( $referrer, '.ico'  ) || false !== strpos( $referrer, 'wp-admin' ) || false !== strpos( $referrer, '.map' ) || false !== strpos( $referrer, 'wp-content' ) ) {
			return;
		}

		// Continue if post is published.
		$workflow_id = $this->is_capturing();

		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'page_view' !== $trigger_event ) {
				return;
			}
		} elseif ( ! get_option( 'wp_page_view_trigger', false ) ) {
			return;
		}

		$current_user = ( is_user_logged_in() ) ? wp_get_current_user() : 'anonymous';
		$user_info    = array();
		$post_data    = array();

		if ( 'anonymous' !== $current_user ) {
			$post_data['user_name']         = esc_html( $current_user->user_login );
			$post_data['user_email']        = esc_html( $current_user->user_email );
			$post_data['user_first_name']   = esc_html( $current_user->user_firstname );
			$post_data['user_last_name']    = esc_html( $current_user->user_lastname );
			$post_data['user_display_name'] = esc_html( $current_user->display_name );
			$post_data['user_ID']           = esc_html( $current_user->ID );
		} else {
			$post_data['user'] = $current_user;
		}

		$current_taxonomies = get_object_taxonomies( $post, 'objects' );
		$taxonomies         = array();

		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $post, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$post_data['post_type']    = get_post_type();
		$post_data['id']           = get_the_ID();
		$post_data['post_title']   = get_the_title();
		$post_data['taxonomies']   = wp_json_encode( $taxonomies );
		$post_data['http_referer'] = wp_get_referer();

		// Check if URL has any parameters.
		if ( ! empty( $_GET ) ) {
			$url_params     = $_GET;
			$url_param_data = array();

			foreach ( $url_params as $key => $value ) {
				$key = 'url_param_' . $key;

				if ( is_array( $value ) || is_object( $value ) ) {
					$url_param_data = flowmattic_recursive_array( $url_param_data, $key, $value );
				} else {
					$url_param_data[ $key ] = $value;
				}
			}

			$post_data = array_merge( $post_data, $url_param_data );
		}

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$args           = array(
				'workflow_id' => $workflow_id,
			);
			$workflow       = wp_flowmattic()->workflows_db->get( $args );
			$workflow_steps = json_decode( $workflow->workflow_steps, true );
			$settings       = $workflow_steps[0];

			// Prevent from executing workflow in test mode if not WordPress trigger.
			if ( strtolower( $this->app_name ) !== $settings['application'] ) {
				return;
			}

			$trigger_post_id = ( isset( $settings['trigger_post_id'] ) && '' !== $settings['trigger_post_id'] ) ? $settings['trigger_post_id'] : '';

			if ( '' !== $trigger_post_id && (int) $post_data['id'] !== (int) $trigger_post_id ) {
				return;
			}

			$trigger_post_type = ( isset( $settings['trigger_post_type'] ) && '' !== $settings['trigger_post_type'] ) ? $settings['trigger_post_type'] : '';

			if ( '' !== $trigger_post_type && get_post_type( $post_data['id'] ) !== $trigger_post_type ) {
				return;
			}

			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
		}

		// Run the workflow.
		$this->run_workflow_step( 'page_view', $post_data );
	}

	/**
	 * Capture post meta update.
	 *
	 * @access public
	 * @since 1.2
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $object_id   ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 * @return void
	 */
	public function capture_post_meta_update( $meta_id, $object_id, $meta_key, $_meta_value ) {
		if ( '_edit_lock' === $meta_key || 'workflow_executed' === $meta_key || '_edit_last' === $meta_key ) {
			return;
		}

		// Do not run if the meta key is workflow_executed_at.
		if ( 'workflow_executed_at' === $meta_key ) {
			return;
		}

		// Continue if post is published.
		$workflow_id = get_option( 'webhook-capture-live', false );
		$post_data   = array(
			'meta_key'   => $meta_key, // @codingStandardsIgnoreLine
			'meta_value' => $_meta_value, // @codingStandardsIgnoreLine
			'post_id'    => $object_id,
		);

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'updated_post_meta' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );

				// Do not execute workflow if capture data in process.
				return;
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'updated_post_meta', $post_data );
	}

	/**
	 * Capture post meta field update.
	 *
	 * @access public
	 * @since 4.2.2
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $object_id   ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 * @return void
	 */
	public function capture_post_meta_field_update( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$workflow_id = get_option( 'webhook-capture-live', false );
		$app_action  = get_option( 'webhook-capture-app-action', false );
		$post_data   = array(
			'meta_key'   => $meta_key, // @codingStandardsIgnoreLine
			'meta_value' => $_meta_value, // @codingStandardsIgnoreLine
			'post_id'    => $object_id,
		);

		// If edit_lock meta key is updated, return.
		if ( '_edit_lock' === $meta_key || 'workflow_executed' === $meta_key || '_edit_last' === $meta_key ) {
			return;
		}

		// Do not run if the meta key is workflow_executed_at.
		if ( 'workflow_executed_at' === $meta_key ) {
			return;
		}

		// Get the workflows having WordPress as trigger.
		$workflows = wp_flowmattic()->workflows_db->get_workflow_by_trigger_application( strtolower( $this->app_name ) );

		$trigger_matched = false;

		if ( ! empty( $workflows ) ) {
			foreach ( $workflows as $key => $workflow ) {
				$workflow_steps = json_decode( $workflow->workflow_steps );

				// Loop through each workflow to check the action event.
				if ( ! empty( $workflow_steps ) ) {
					foreach ( $workflow_steps as $step ) {
						if ( 'trigger' === $step->type && strtolower( $this->app_name ) === $step->application ) {
							$trigger_event = $step->action;

							if ( 'updated_post_meta_field' === $trigger_event && isset( $step->trigger_meta_field ) ) {
								$trigger_meta_field = $step->trigger_meta_field;

								// Check if the meta field is updated.
								if ( $meta_key !== $trigger_meta_field ) {
									return;
								}

								$meta_data = array(
									'post_id' => $object_id,
									$meta_key => $_meta_value,
								);

								// Update the capture data.
								$post_data = array_merge( $meta_data, $post_data );

								$trigger_matched = true;
							}
						}
					}
				}
			}
		}

		if ( ! $trigger_matched ) {
			return;
		}

		if ( $workflow_id ) {
			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			delete_option( 'webhook-capture-live' );

			// Do not execute workflow if capture data in process.
			return;
		}

		// Run the workflow.
		$this->run_workflow_step( 'updated_post_meta_field', $post_data );
	}

	/**
	 * Capture user profile update.
	 *
	 * @access public
	 * @since 1.2
	 * @param int    $user_id       ID of updated metadata entry.
	 * @param Object $old_user_data User data before update.
	 * @return bool
	 */
	public function capture_user_profile_update( $user_id, $old_user_data ) {
		$workflow_id = get_option( 'webhook-capture-live', false );
		$app_action  = get_option( 'webhook-capture-app-action', false );
		$post_data   = $this->get_user_details_by_id( $user_id );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id && 'updated_user_profile' !== $app_action ) {
			return false;
		}

		if ( $workflow_id ) {
			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );

			// Do not execute workflow if capture data in process.
			return;
		}

		// Run the workflow.
		$this->run_workflow_step( 'updated_user_profile', $post_data );
	}

	/**
	 * Capture user profile field update.
	 *
	 * @access public
	 * @since 4.2.2
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $user_id     ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 * @return bool
	 */
	public function capture_user_profile_field_update( $meta_id, $user_id, $meta_key, $_meta_value ) {
		$workflow_id = get_option( 'webhook-capture-live', false );
		$app_action  = get_option( 'webhook-capture-app-action', false );
		$post_data   = $this->get_user_details_by_id( $user_id );

		// If edit_lock meta key is updated, return.
		if ( '_edit_lock' === $meta_key || 'workflow_executed' === $meta_key || '_edit_last' === $meta_key ) {
			return;
		}

		// Get the workflows having WordPress as trigger.
		$workflows = wp_flowmattic()->workflows_db->get_workflow_by_trigger_application( strtolower( $this->app_name ) );

		$trigger_matched = false;

		if ( ! empty( $workflows ) ) {
			foreach ( $workflows as $key => $workflow ) {
				$workflow_steps = json_decode( $workflow->workflow_steps );

				// Loop through each workflow to check the action event.
				if ( ! empty( $workflow_steps ) ) {
					foreach ( $workflow_steps as $step ) {
						if ( 'trigger' === $step->type && strtolower( $this->app_name ) === $step->application ) {
							$trigger_event = $step->action;

							if ( 'updated_profile_field' === $trigger_event ) {
								$trigger_profile_field = $step->trigger_profile_field;

								// Check if the profile field is updated.
								if ( $meta_key !== $trigger_profile_field ) {
									return false;
								}

								$profile_data = array(
									'ID'      => $user_id,
									$meta_key => $_meta_value,
								);

								// Update the capture data.
								$post_data = array_merge( $profile_data, $post_data );

								$trigger_matched = true;
							}
						}
					}
				}
			}
		}

		if ( ! $trigger_matched ) {
			return false;
		}

		if ( $workflow_id ) {
			update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			delete_option( 'webhook-capture-live' );

			// Do not execute workflow if capture data in process.
			return;
		}

		// Run the workflow.
		$this->run_workflow_step( 'updated_profile_field', $post_data );
	}

	/**
	 * Capture user password reset.
	 *
	 * @access public
	 * @since 1.2
	 * @param Object $user     User resetting the password.
	 * @param string $new_pass New password reset by user.
	 * @return void
	 */
	public function capture_user_password_reset( $user, $new_pass ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $user->data;

		// Remove the user passwords from the received data for privacy concern.
		unset( $post_data['password'] );
		unset( $post_data['user_pass'] );
		unset( $post_data['userpass'] );
		unset( $post_data['user_password'] );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'user_reset_password' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'user_reset_password', $post_data );
	}

	/**
	 * Capture deleted user.
	 *
	 * @access public
	 * @since 1.2
	 * @param int      $id       Deleted user ID.
	 * @param int|null $reassign ID of the user reassigned.
	 * @param Object   $user     Deleted user object.
	 * @return void
	 */
	public function capture_deleted_user( $id, $reassign, $user ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = (array) $user->data;

		// Remove the user passwords from the received data for privacy concern.
		unset( $post_data['password'] );
		unset( $post_data['user_pass'] );
		unset( $post_data['userpass'] );
		unset( $post_data['user_password'] );

		$user_meta = $this->get_user_metadata( $id );
		$post_data = array_merge( $post_data, $user_meta );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'user_deleted' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'user_deleted', $post_data );
	}

	/**
	 * Capture term create.
	 *
	 * @access public
	 * @since 1.3
	 * @param int   $term_id Updated category ID.
	 * @param int   $tt_id   Term taxonomy ID.
	 * @param array $args    Arguments passed to the create term function.
	 * @return void
	 */
	public function capture_term_create( $term_id, $tt_id, $args ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = array();

		// Get the term details.
		$term = get_term( $term_id );

		foreach ( $term as $key => $value ) {
			if ( is_array( $value ) ) {
				$post_data = flowmattic_recursive_array( $post_data, $key, $value );
			} else {
				$post_data[ $key ] = $value;
			}
		}

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'term_created' === $trigger_event || 'category_created' === $trigger_event || 'tag_created' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $post_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'term_created', $post_data );
	}

	/**
	 * Capture user role add.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param int    $user_id User ID.
	 * @param string $role    New user role.
	 * @return void
	 */
	public function capture_user_role_add( $user_id, $role ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = array(
			'user_id' => $user_id,
			'role'    => $role,
		);

		// Check if the workflow is already executed.
		$workflow_executed = get_user_meta( $user_id, 'workflow_executed', true );

		if ( $workflow_executed ) {
			// Remove the user meta for future execution if time is less than 2 seconds.
			if ( time() >= $workflow_executed ) {
				delete_user_meta( $user_id, 'workflow_executed' );
			}
			return;
		}

		// Update user meta with current time to prevent infinite loop.
		update_user_meta( $user_id, 'workflow_executed', time() + 2 );

		// Get the user details.
		$user_data = flowmattic_get_user_data( $user_id );

		// Add user data to the post data.
		$post_data = array_merge( $post_data, $user_data );

		$conditions = array(
			'condition'         => 'AND',
			'trigger_user_role' => array(
				'operator' => 'equals',
				'field'    => 'role',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', strtolower( $this->app_name ), 'user_role_added', $post_data, $conditions );
	}

	/**
	 * Capture user role removed.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param int    $user_id User ID.
	 * @param string $role    New user role.
	 * @return void
	 */
	public function capture_user_role_remove( $user_id, $role ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = array(
			'user_id' => $user_id,
			'role'    => $role,
		);

		// Check if the workflow is already executed.
		$workflow_executed = get_user_meta( $user_id, 'workflow_executed', true );

		if ( $workflow_executed ) {
			// Remove the user meta for future execution if time is less than 2 seconds.
			if ( time() >= $workflow_executed ) {
				delete_user_meta( $user_id, 'workflow_executed' );
			}
			return;
		}

		// Update user meta with current time to prevent infinite loop.
		update_user_meta( $user_id, 'workflow_executed', time() + 2 );

		// Get the user details.
		$user_data = flowmattic_get_user_data( $user_id );

		// Add user data to the post data.
		$post_data = array_merge( $post_data, $user_data );

		$conditions = array(
			'condition'         => 'AND',
			'trigger_user_role' => array(
				'operator' => 'equals',
				'field'    => 'role',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', strtolower( $this->app_name ), 'user_role_removed', $post_data, $conditions );
	}

	/**
	 * Capture user role change.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param int   $user_id User ID.
	 * @param array $roles   New user roles.
	 * @param array $old_roles Old user roles.
	 * @return void
	 */
	public function capture_user_role_change( $user_id, $roles, $old_roles ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = array(
			'user_id'   => $user_id,
			'new_roles' => $roles,
			'old_roles' => $old_roles,
		);

		// Check if the workflow is already executed.
		$workflow_executed = get_user_meta( $user_id, 'workflow_executed', true );

		if ( $workflow_executed ) {
			// Remove the user meta for future execution if time is less than 2 seconds.
			if ( time() >= $workflow_executed ) {
				delete_user_meta( $user_id, 'workflow_executed' );
			}
			return;
		}

		// Update user meta with current time to prevent infinite loop.
		update_user_meta( $user_id, 'workflow_executed', time() + 2 );

		// Get the user details.
		$user_data = flowmattic_get_user_data( $user_id );

		$post_data = array_merge( $post_data, $user_data );

		$conditions = array(
			'condition'         => 'AND',
			'trigger_user_role' => array(
				'operator' => 'equals',
				'field'    => 'new_roles',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', strtolower( $this->app_name ), 'user_role_changed', $post_data, $conditions );
	}

	/**
	 * Capture user role change from specific role.
	 *
	 * @access public
	 * @since 4.3.0
	 * @param int   $user_id User ID.
	 * @param array $roles   New user roles.
	 * @param array $old_roles Old user roles.
	 * @return void
	 */
	public function capture_user_role_from_specific_to_set( $user_id, $roles, $old_roles ) {
		// Continue if post is published.
		$workflow_id = $this->is_capturing();
		$post_data   = array(
			'user_id'   => $user_id,
			'new_roles' => $roles,
			'old_roles' => $old_roles,
		);

		// Check if the workflow is already executed.
		$workflow_executed = get_user_meta( $user_id, 'workflow_executed', true );

		if ( $workflow_executed ) {
			// Remove the user meta for future execution if time is less than 2 seconds.
			if ( time() >= $workflow_executed ) {
				delete_user_meta( $user_id, 'workflow_executed' );
			}
			return;
		}

		// Update user meta with current time to prevent infinite loop.
		update_user_meta( $user_id, 'workflow_executed', time() + 2 );

		// Get the user details.
		$user_data = flowmattic_get_user_data( $user_id );

		$post_data = array_merge( $post_data, $user_data );

		$conditions = array(
			'condition'            => 'AND',
			'trigger_user_role'    => array(
				'operator' => 'equals',
				'field'    => 'old_roles',
			),
			'trigger_user_role_to' => array(
				'operator' => 'equals',
				'field'    => 'new_roles',
			),
		);

		// Trigger the workflow.
		do_action( 'flowmattic_workflow_execute', strtolower( $this->app_name ), 'user_role_from_specific_to_set', $post_data, $conditions );
	}

	/**
	 * Create new user.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_new_user( $data ) {
		$response = array();

		$user_roles = isset( $data['user_role'] ) ? explode( ',', $data['user_role'] ) : 'subscriber';

		$password = wp_generate_password();

		if ( isset( $data['auto_password_generation'] ) && 'Yes' === $data['auto_password_generation'] ) {
			$password = wp_generate_password();
		} elseif ( isset( $data['password'] ) && '' !== $data['password'] ) {
			$password = $data['password'];
		}

		$user_data = array(
			'user_login' => $data['username'],
			'user_pass'  => $password,
			'user_email' => $data['email'],
			'first_name' => isset( $data['first_name'] ) ? $data['first_name'] : '',
			'last_name'  => isset( $data['last_name'] ) ? $data['last_name'] : '',
			'role'       => is_array( $user_roles ) ? $user_roles[0] : $user_roles,
		);

		$notify = ( isset( $data['notification_type'] ) && 'none' !== $data['notification_type'] ) ? $data['notification_type'] : false;

		$user = get_user_by( 'email', $data['email'] );

		if ( ! $user ) {
			$user_id = wp_insert_user( $user_data );
		} else {
			$user_id = $user->ID;
		}

		if ( is_wp_error( $user_id ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $user_id->get_error_message(),
			);
		} else {
			$response = array(
				'status'  => 'success',
				'user_id' => $user_id,
			);

			if ( is_array( $user_roles ) ) {
				foreach ( $user_roles as $user_role ) {
					$this->add_user_role(
						array(
							'user_role' => trim( $user_role ),
							'user_id'   => $user_id,
						)
					);
				}
			}

			if ( $notify ) {
				wp_new_user_notification( $user_id, null, $notify );
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Updayte an existing user.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function update_existing_user( $data ) {
		$response = array();

		$user_roles = isset( $data['user_role'] ) ? explode( ',', $data['user_role'] ) : '';

		$user_data = array(
			'ID' => $data['user_id'], // User ID of the user to update.
		);

		// Update user email if available.
		if ( isset( $data['email'] ) && '' !== $data['email'] ) {
			$user_data['user_email'] = $data['email'];
		}

		// Update user first name if available.
		if ( isset( $data['first_name'] ) && '' !== $data['first_name'] ) {
			$user_data['first_name'] = $data['first_name'];
		}

		// Update user last name if available.
		if ( isset( $data['last_name'] ) && '' !== $data['last_name'] ) {
			$user_data['last_name'] = $data['last_name'];
		}

		$user = get_user_by( 'ID', $data['user_id'] );

		if ( $user ) {
			$user_id = wp_update_user( $user_data ); // Update the existing user.

			if ( isset( $data['user_role'] ) && '' !== $data['user_role'] ) {
				if ( is_array( $user_roles ) ) {
					foreach ( $user_roles as $user_role ) {
						// Check if the user role is already assigned.
						if ( ! in_array( $user_role, $user->roles, true ) ) {
							$this->add_user_role(
								array(
									'user_role' => trim( $user_role ),
									'user_id'   => $user_id,
								)
							);
						}
					}
				}
			}
		} else {
			$response = array(
				'status' => 'error',
				'error'  => 'User not found.', // User doesn't exist, so cannot update.
			);
			return wp_json_encode( $response );
		}

		if ( is_wp_error( $user_id ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $user_id->get_error_message(),
			);
		} else {
			$response = array(
				'status'  => 'success',
				'user_id' => $user_id,
			);

			if ( is_array( $user_roles ) ) {
				foreach ( $user_roles as $user_role ) {
					$this->add_user_role(
						array(
							'user_role' => trim( $user_role ),
							'user_id'   => $user_id,
						)
					);
				}
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create new comment.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_new_comment( $data ) {
		$response = array();

		// Create comment object.
		$comment_data = array(
			'comment_post_ID'      => $data['post_id'],
			'comment_content'      => $data['comment'],
			'comment_author'       => $data['author'],
			'comment_author_email' => $data['email'],
			'comment_status'       => 'approved', // Update the status field to approved.
			'comment_author_url'   => $data['author_url'], // The url of the author.
			'comment_type'         => '', // The type of comment (if any).
			'comment_parent'       => 0, // The parent comment (if any).
			'comment_author_IP'    => '', // The IP address of the author.
			'comment_agent'        => 'FlowMattic/' . FLOWMATTIC_VERSION, // The agent used by the author.
			'comment_date'         => gmdate( 'Y-m-d H:i:s' ), // The date of the comment.
			'comment_approved'     => 1, // If the comment needs to be approved by an admin.
		);

		// Insert the comment into the database.
		$comment_id = wp_new_comment( $comment_data, true );

		// Error response.
		if ( is_wp_error( $comment_id ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $comment_id->get_error_message(),
			);
		} else {
			$response = array(
				'status'     => 'success',
				'comment_id' => $comment_id,
			);
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create new post.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_new_post( $data ) {
		$response = array();

		// Create post object.
		$post_args = array(
			'post_title'   => wp_strip_all_tags( $data['post_title'] ),
			'post_content' => isset( $data['post_content'] ) ? $data['post_content'] : '',
			'post_status'  => isset( $data['post_status'] ) ? $data['post_status'] : '',
			'post_type'    => isset( $data['post_type'] ) ? $data['post_type'] : '',
			'meta_input'   => isset( $data['custom_fields'] ) ? $data['custom_fields'] : array(),
		);

		// If post date is available, update.
		if ( ! empty( $data['post_date'] ) ) {
			$post_args['post_date'] = $data['post_date'];
		}

		// If post date GMT is available, update.
		if ( ! empty( $data['post_date_gmt'] ) ) {
			$post_args['post_date_gmt'] = $data['post_date_gmt'];
		}

		// If post name is available, update.
		if ( ! empty( $data['post_name'] ) ) {
			$post_args['post_name'] = sanitize_title( $data['post_name'] );
		}

		// If post password is available, update.
		if ( ! empty( $data['post_password'] ) ) {
			$post_args['post_password'] = $data['post_password'];
		}

		// If categories are set, insert them.
		if ( isset( $data['post_category'] ) && ! empty( $data['post_category'] ) ) {
			$post_args['post_category'] = is_array( $data['post_category'] ) ? $data['post_category'] : explode( ',', $data['post_category'] );
		}

		// If tags are set, insert them.
		if ( isset( $data['post_tags'] ) && ! empty( $data['post_tags'] ) ) {
			$post_args['tags_input'] = is_array( $data['post_tags'] ) ? $data['post_tags'] : explode( ',', $data['post_tags'] );
		}

		// If post parent is set, update.
		if ( isset( $data['post_parent'] ) && ! empty( $data['post_parent'] ) ) {
			$post_args['post_parent'] = $data['post_parent'];
		}

		// If author id is set, update them.
		if ( isset( $data['post_author_id'] ) && ! empty( $data['post_author_id'] ) ) {
			$user_id = $data['post_author_id'];

			if ( is_email( $user_id ) ) {
				$user    = get_user_by( 'email', $fields['user_email'] );
				$user_id = $user ? $user->ID : 1;
			}

			$post_args['post_author'] = $user_id;
		}

		// If post excerpt is available, update.
		if ( ! empty( $data['post_excerpt'] ) ) {
			$post_args['post_excerpt'] = $data['post_excerpt'];
		}

		// Insert the post into the database.
		$post_id = wp_insert_post( $post_args, true );

		// Get custom taxonomies.
		if ( isset( $data['custom_taxonomies'] ) && ! empty( $data['custom_taxonomies'] ) ) {
			$custom_taxonomies = $data['custom_taxonomies'];

			foreach ( $custom_taxonomies as $taxonomy => $terms ) {
				$terms = array_map( 'trim', explode( ',', $terms ) );
				wp_set_object_terms( $post_id, $terms, $taxonomy );
			}
		}

		// Check if post thumbnail id is provided.
		$post_thumbnail_id = '';
		if ( isset( $data['post_thumbnail_id'] ) && '' !== $data['post_thumbnail_id'] ) {

			// Get the image ID.
			$post_thumbnail_id = $data['post_thumbnail_id'];

			// If image URL provided, import it first, and then use the image ID.
			if ( ! is_numeric( $post_thumbnail_id ) ) {
				$import_image      = $this->create_new_media( array( 'file' => $post_thumbnail_id ) );
				$import_image      = json_decode( $import_image, true );
				$post_thumbnail_id = isset( $import_image['media_id'] ) ? $import_image['media_id'] : '';
			}

			// Set the post featured image.
			set_post_thumbnail( $post_id, $post_thumbnail_id );
		}

		if ( is_wp_error( $post_id ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $post_id->get_error_message(),
			);
		} else {
			$response = array(
				'status'   => 'success',
				'post_id'  => $post_id,
				'post_url' => get_permalink( $post_id ),
			);
		}

		return wp_json_encode( $response );
	}

	/**
	 * Update post.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function update_post( $data ) {
		$response = array();

		// Create post object.
		$post_args = array(
			'ID' => $data['post_id'],
		);

		// If post title is available, update.
		if ( ! empty( $data['post_title'] ) ) {
			$post_args['post_title'] = wp_strip_all_tags( $data['post_title'] );
		}

		// If post content is available, update.
		if ( ! empty( $data['post_content'] ) ) {
			$post_args['post_content'] = $data['post_content'];
		}

		// If post status is available, update.
		if ( ! empty( $data['post_status'] ) ) {
			$post_args['post_status'] = $data['post_status'];
		}

		// If custom fields is available, update.
		if ( ! empty( $data['custom_fields'] ) ) {
			$post_args['meta_input'] = isset( $data['custom_fields'] ) ? $data['custom_fields'] : array();
		}

		// If post excerpt is available, update.
		if ( ! empty( $data['post_excerpt'] ) ) {
			$post_args['post_excerpt'] = $data['post_excerpt'];
		}

		// If post type is set, update it..
		if ( isset( $data['post_type'] ) && ! empty( $data['post_type'] ) ) {
			$post_args['post_type'] = $data['post_type'];
		}

		// If categories are set, update them.
		if ( isset( $data['post_category'] ) && ! empty( $data['post_category'] ) ) {
			$post_args['post_category'] = is_array( $data['post_category'] ) ? $data['post_category'] : explode( ',', $data['post_category'] );
		}

		// If tags are set, update them.
		if ( isset( $data['post_tags'] ) && ! empty( $data['post_tags'] ) ) {
			$post_args['tags_input'] = is_array( $data['post_tags'] ) ? $data['post_tags'] : explode( ',', $data['post_tags'] );
		}

		// If post parent is set, update.
		if ( isset( $data['post_parent'] ) && ! empty( $data['post_parent'] ) ) {
			$post_args['post_parent'] = $data['post_parent'];
		}

		// If post name is available, update.
		if ( ! empty( $data['post_name'] ) ) {
			$post_args['post_name'] = sanitize_title( $data['post_name'] );
		}

		// If author id is set, update them.
		if ( isset( $data['post_author_id'] ) && ! empty( $data['post_author_id'] ) ) {
			$user_id = $data['post_author_id'];

			if ( is_email( $user_id ) ) {
				$user    = get_user_by( 'email', $fields['user_email'] );
				$user_id = $user->ID;
			}

			$post_args['post_author'] = $user_id;
		}

		// Update the post into the database.
		$post_id = wp_update_post( $post_args, true );

		// Get custom taxonomies.
		if ( isset( $data['custom_taxonomies'] ) && ! empty( $data['custom_taxonomies'] ) ) {
			$custom_taxonomies = $data['custom_taxonomies'];

			foreach ( $custom_taxonomies as $taxonomy => $terms ) {
				$terms = array_map( 'trim', explode( ',', $terms ) );
				wp_set_object_terms( $post_id, $terms, $taxonomy );
			}
		}

		// Check if post thumbnail id is provided.
		$post_thumbnail_id = '';
		if ( isset( $data['post_thumbnail_id'] ) && '' !== $data['post_thumbnail_id'] ) {

			// Get the image ID.
			$post_thumbnail_id = $data['post_thumbnail_id'];

			// If image URL provided, import it first, and then use the image ID.
			if ( ! is_numeric( $post_thumbnail_id ) ) {
				$import_image      = $this->create_new_media( array( 'file' => $post_thumbnail_id ) );
				$import_image      = json_decode( $import_image, true );
				$post_thumbnail_id = isset( $import_image['media_id'] ) ? $import_image['media_id'] : '';
			}

			// Set the post featured image.
			set_post_thumbnail( $post_id, $post_thumbnail_id );
		}

		if ( is_wp_error( $post_id ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $post_id->get_error_message(),
			);
		} else {
			$response = array(
				'status'   => 'success',
				'post_id'  => $post_id,
				'post_url' => get_permalink( $post_id ),
			);
		}

		return wp_json_encode( $response );
	}

	/**
	 * Delete post.
	 *
	 * @access public
	 * @since 5.0.6
	 * @param array $data Request data.
	 * @return array
	 */
	public function delete_post( $data ) {
		$response = array();

		$post_id = $data['post_id'];

		// Delete the post from the database.
		$delete_post = wp_delete_post( $post_id, true );

		if ( is_wp_error( $delete_post ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $delete_post->get_error_message(),
			);
		} else {
			$response = array(
				'status' => 'success',
				'message' => esc_html__( 'Post deleted successfully.', 'flowmattic' ),
			);
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create new media.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_new_media( $data ) {
		$response = array();
		$file     = $data['file'];
		$alt_text = isset( $data['alt_text'] ) ? $data['alt_text'] : '';
		$caption  = isset( $data['caption'] ) ? $data['caption'] : '';
		$description = isset( $data['description'] ) ? $data['description'] : '';

		add_filter( 'https_local_ssl_verify', '__return_false' );
		add_filter( 'https_ssl_verify', '__return_false' );

		// Construct a new filename based on the URL and file extension.
		$url_parts       = wp_parse_url( $file );
		$constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
		$filename        = basename( $constructed_url );

		// Determine the file type based on the URL.
		$filetype = wp_check_filetype( $constructed_url );

		// Manually set the file extension if the URL doesn't have one.
		if ( empty( $filetype['ext'] ) ) {
			$filetype['ext'] = 'jpg'; // Manually set the extension to match the image type.
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => flowmattic_download_url( $file, 300, false ),
		);

		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $file_array['tmp_name']->get_error_message(),
			);
		} else {
			$upload_overrides = array(
				'test_form' => false,
				'test_size' => false,
				'test_type' => false,
				'action'    => 'flowmattic_handle_upload',
			);

			if ( ! function_exists( 'media_handle_sideload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$media = media_handle_sideload( $file_array, 0, null, $upload_overrides );

			if ( is_wp_error( $media ) ) {
				$response = array(
					'status' => 'error',
					'error'  => $media->get_error_message(),
				);
			} else {
				$response = array(
					'status'    => 'success',
					'media_id'  => $media,
					'media_url' => wp_get_attachment_url( $media ),
				);

				// Set the alt text, caption, and description.
				if ( '' !== $alt_text ) {
					update_post_meta( $media, '_wp_attachment_image_alt', $alt_text );
					$response['alt_text'] = $alt_text;
				}

				if ( '' !== $caption ) {
					$attachment = array(
						'ID'           => $media,
						'post_excerpt' => $caption,
					);
					wp_update_post( $attachment );

					$response['caption'] = $caption;
				}

				if ( '' !== $description ) {
					$attachment = array(
						'ID'           => $media,
						'post_content' => $description,
					);
					wp_update_post( $attachment );

					$response['description'] = $description;
				}
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Update user meta.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function update_user_meta( $data ) {
		$response = array();

		$meta_fields = $data['meta_fields'];
		$user_id     = $data['user_id'];

		foreach ( $meta_fields as $key => $value ) {
			$status = update_user_meta( $user_id, $key, $value );

			if ( $status ) {
				$response[ $key . ' - ID' ] = $status;
			} else {
				$response[ $key ] = esc_html__( 'No values updated.', 'flowmattic' );
			}
		}

		return wp_json_encode( $response );
	}

	/**
	 * Get user profile by ID.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $fields User data fields.
	 * @return array
	 */
	public function get_user_by_id( $fields ) {
		$user = get_user_by( 'id', $fields['user_id'] );

		if ( $user ) {
			// Get user data with meta.
			$post_data = $this->get_user_details_by_id( $user->ID );

			return wp_json_encode( $post_data );
		} else {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User not found for the given ID', 'flowmattic' ),
					'user_id' => $fields['user_id'],
				)
			);
		}
	}

	/**
	 * Get new user profile by email.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $fields User data fields.
	 * @return array
	 */
	public function get_user_by_email( $fields ) {
		$user = get_user_by( 'email', $fields['user_email'] );

		if ( $user ) {
			// Get user data with meta.
			$post_data = $this->get_user_details_by_id( $user->ID );

			return wp_json_encode( $post_data );
		} else {
			return wp_json_encode(
				array(
					'status'     => 'error',
					'message'    => esc_html__( 'User not found for the given email', 'flowmattic' ),
					'user_email' => $fields['user_email'],
				)
			);
		}
	}

	/**
	 * Create category.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_category( $data ) {
		$response = array();

		$category_name = $data['category_name'];

		// Create the main category.
		$category = wp_insert_term(
			// The name of the category.
			$category_name,
			// The taxonomy, which in this case if category (don't change).
			'category',
			array()
		);

		if ( is_wp_error( $category ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $category->get_error_message(),
			);
		} else {
			$response = $category;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create tag.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function create_tag( $data ) {
		$response = array();

		$tag_name = $data['tag_name'];

		// Create the main tag.
		$tag = wp_insert_term(
			// The name of the tag.
			$tag_name,
			// The taxonomy, which in this case if tag (don't change).
			'post_tag',
			array()
		);

		if ( is_wp_error( $tag ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $tag->get_error_message(),
			);
		} else {
			$response = $tag;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create role.
	 *
	 * @access public
	 * @since 2.1.0
	 * @param array $data Request data.
	 * @return array
	 */
	public function add_role( $data ) {
		$response = array();

		$role_name         = $data['role_name'];
		$role_display_name = $data['role_display_name'];
		$role_capabilities = ( isset( $data['role_capabilities'] ) && '' !== $data['role_capabilities'] ) ? explode( ',', $data['role_capabilities'] ) : array();

		// Add role to WordPress.
		$new_role = add_role( $role_name, $role_display_name, $role_capabilities );

		if ( ! $new_role ) {
			$response = array(
				'status' => 'error',
				'error'  => esc_html__( 'Looks like user role already exists', 'flowmattic' ),
			);
		} else {
			$response = $new_role;
		}

		return wp_json_encode( $response );
	}

	/**
	 * Add role to existing user.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function add_user_role( $data ) {
		$response = array();

		if ( ! isset( $data['user_role'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User role is not provided', 'flowmattic' ),
				)
			);
		}

		if ( ! isset( $data['user_id'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User ID is not provided', 'flowmattic' ),
				)
			);
		}

		$user_role = $data['user_role'];
		$user_id   = $data['user_id'];

		// Instantiate the WP User.
		$user = new WP_User( $user_id );

		// Add the new user role to the user.
		$user_roles = isset( $data['user_role'] ) ? explode( ',', $data['user_role'] ) : '';

		if ( is_array( $user_roles ) ) {
			foreach ( $user_roles as $key => $role ) {
				$user->add_role( $role );
			}
		} else {
			$user->add_role( $user_role );
		}

		return wp_json_encode(
			array(
				'status'  => 'status',
				'message' => esc_html__( 'User role is successfully added to the user', 'flowmattic' ),
			)
		);
	}

	/**
	 * Change user role to existing user.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function change_user_role( $data ) {
		$response = array();

		if ( ! isset( $data['user_role'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User role is not provided', 'flowmattic' ),
				)
			);
		}

		if ( ! isset( $data['user_id'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User ID is not provided', 'flowmattic' ),
				)
			);
		}

		$user_role = $data['user_role'];
		$user_id   = $data['user_id'];

		// Instantiate the WP User.
		$user = new WP_User( $user_id );

		// Change the user role from the user.
		$user_roles = isset( $data['user_role'] ) ? explode( ',', $data['user_role'] ) : '';

		if ( is_array( $user_roles ) ) {
			foreach ( $user_roles as $key => $role ) {
				$user->set_role( $role );
			}
		} else {
			$user->set_role( $user_role );
		}

		return wp_json_encode(
			array(
				'status'  => 'status',
				'message' => esc_html__( 'User role is successfully changed', 'flowmattic' ),
			)
		);
	}

	/**
	 * Remove user role to existing user.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function remove_user_role( $data ) {
		$response = array();

		if ( ! isset( $data['user_role'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User role is not provided', 'flowmattic' ),
				)
			);
		}

		if ( ! isset( $data['user_id'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User ID is not provided', 'flowmattic' ),
				)
			);
		}

		$user_role = $data['user_role'];
		$user_id   = $data['user_id'];

		// Instantiate the WP User.
		$user = new WP_User( $user_id );

		// Remove the user role from the user.
		$user_roles = isset( $data['user_role'] ) ? explode( ',', $data['user_role'] ) : '';

		if ( is_array( $user_roles ) ) {
			foreach ( $user_roles as $key => $role ) {
				$user->remove_role( $role );
			}
		} else {
			$user->remove_role( $user_role );
		}

		return wp_json_encode(
			array(
				'status'     => 'status',
				'message'    => esc_html__( 'User role is successfully removed', 'flowmattic' ),
				'id'         => $user_id,
				'user_roles' => $user_roles,
			)
		);
	}

	/**
	 * Delete user account.
	 *
	 * @access public
	 * @since 1.2
	 * @param array $data Request data.
	 * @return array
	 */
	public function delete_user( $data ) {
		$response = array();

		if ( ! isset( $data['user_id'] ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User ID is not provided', 'flowmattic' ),
				)
			);
		}

		$user_id     = $data['user_id'];
		$reassign_id = isset( $data['reassign_id'] ) ? $data['reassign_id'] : null;

		// Make sure wp_delete_user function exists.
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// Delete the WP User.
		wp_delete_user( $user_id, $reassign_id );

		return wp_json_encode(
			array(
				'status'  => 'status',
				'message' => esc_html__( 'User account successfully deleted', 'flowmattic' ),
			)
		);
	}

	/**
	 * Trigger: User login.
	 *
	 * @access public
	 * @since 1.2
	 * @param string $user_login Username of the logged in user.
	 * @param object $user       WP User object of the logged in user.
	 * @return void
	 */
	public function capture_user_login( $user_login, $user ) {
		$workflow_id = $this->is_capturing();

		$user_data = (array) $user->data;

		// Remove the user passwords from the received data for privacy concern.
		unset( $user_data['password'] );
		unset( $user_data['user_pass'] );
		unset( $user_data['userpass'] );
		unset( $user_data['user_password'] );

		// If workflow ID is set, the webhook capture event is in progress.
		if ( $workflow_id ) {
			$trigger_event = get_option( 'webhook-capture-app-action', false );
			if ( $trigger_event && 'user_login' === $trigger_event ) {
				update_option( 'webhook-capture-' . $workflow_id, $user_data, false );
			}
		}

		// Run the workflow.
		$this->run_workflow_step( 'user_login', $user_data );
	}

	/**
	 * Get post metadata.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function get_post_meta( $fields ) {
		$post_id       = (int) $fields['post_id'];
		$post_meta_key = isset( $fields['post_meta_key'] ) ? $fields['post_meta_key'] : '';

		$simple_entry = array();

		// If no Post ID, return default message.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID required. Check incoming data or dynamic tag values.', 'flowmattic' ),
				)
			);
		}

		// If single post meta.
		if ( '' !== $post_meta_key ) {

			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$post_meta_data = get_field( $post_meta_key, $post_id );

				$simple_entry = array(
					'post_meta' => $post_meta_key,
				);

				if ( $post_meta_data ) {
					if ( is_array( $post_meta_data ) ) {
						$array_value = wp_json_encode( $post_meta_data );

						$simple_entry['acf_array_value'] = $array_value;
					} else {
						$simple_entry['value'] = $post_meta_data;
					}
				} else {
					$post_meta_data = get_post_meta( $post_id, $post_meta_key, true );

					$simple_entry['value'] = $post_meta_data;
				}
			} else {
				$post_meta_data = get_post_meta( $post_id, $post_meta_key, true );

				$simple_entry = array(
					'post_meta' => $post_meta_key,
					'value'     => $post_meta_data,
				);
			}
		} else {
			$post_meta_data = get_post_meta( $post_id );
			$post_meta_data = is_array( $post_meta_data ) ? array_combine( array_keys( $post_meta_data ), array_column( $post_meta_data, '0' ) ) : array();

			foreach ( $post_meta_data as $key => $post_meta ) {
				// If ACF is installed, try getting the data from ACF fields.
				if ( function_exists( 'get_field' ) ) {
					$post_meta = get_field( $key, $post_id );

					if ( is_array( $post_meta ) ) {
						$post_meta = wp_json_encode( $post_meta );
					}
				}
				$simple_entry[ $key ] = $post_meta;
			}
		}

		// get the post object by ID.
		$post = get_post( $post_id );

		$post_title     = $post->post_title;  // get the post title.
		$post_permalink = get_permalink( $post_id );  // get the post permalink.
		$post_type      = $post->post_type;  // get the post type.
		$post_author_id = $post->post_author;  // get the author ID.
		$post_author    = get_the_author_meta( 'display_name', $post_author_id );  // get the author name.

		if ( '' === $post_meta_key ) {
			$simple_entry['post_title']     = $post_title;
			$simple_entry['post_permalink'] = $post_permalink;
			$simple_entry['post_type']      = $post_type;
			$simple_entry['post_author_id'] = $post_author_id;
			$simple_entry['post_author']    = $post_author;
		}

		return wp_json_encode( $simple_entry );
	}

	/**
	 * Get Posts by post_type query
	 *
	 * @since 2.2.2
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_posts_by_post_type( $fields ) {
		$app_args        = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_type       = $app_args['post_type'];
		$posts_per_page  = isset( $app_args['posts_per_page'] ) ? $app_args['posts_per_page'] : get_option( 'posts_per_page' );
		$order           = isset( $app_args['order'] ) ? $app_args['order'] : 'DESC';
		$orderby         = $app_args['orderby'];
		$single_array    = ( isset( $app_args['all_posts_as_array'] ) && 'No' === $app_args['all_posts_as_array'] ) ? false : true;
		$exclude_content = ( isset( $app_args['exclude_post_content'] ) && 'Yes' === $app_args['exclude_post_content'] ) ? true : false;
		$exclude_meta    = ( isset( $app_args['exclude_post_meta'] ) && 'Yes' === $app_args['exclude_post_meta'] ) ? true : false;

		// Prepare query data.
		$args = array(
			'post_type' => $post_type,
		);

		if ( '' !== $posts_per_page ) {
			$args['posts_per_page'] = $posts_per_page;
		}
		if ( '' !== $order ) {
			$args['order'] = $order;
		}
		if ( '' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		// Run query.
		$posts = get_posts( $args );

		// Check results.
		if ( ! $posts || is_wp_error( $posts ) ) {
			return false;
		}

		foreach ( $posts as $key => $post_object ) {
			$post_object = (array) $post_object;

			if ( $exclude_content ) {
				unset( $post_object['post_content'] );
			} else {
				// Convert double quotes in post content to html entity.
				$post_object['post_content'] = esc_attr( $post_object['post_content'] );
			}

			// Convert double quotes in post title to html entity.
			$post_object['post_title'] = esc_attr( $post_object['post_title'] );

			if ( ! $single_array ) {
				$posts[ $key ]    = array();
				$posts[ $key ][0] = $post_object;
			} else {
				$posts[ $key ] = $post_object;
			}

			$post_id        = $post_object['ID'];
			$post_meta_data = array();

			if ( $exclude_meta ) {
				unset( $posts[ $key ]['post_meta'] );
			} else {
				// Get all meta.
				$post_meta_data = get_post_meta( $post_id );
				$post_meta_data = array_combine( array_keys( $post_meta_data ), array_column( $post_meta_data, '0' ) );

				foreach ( $post_meta_data as $metakey => $post_meta ) {
					// If ACF is installed, try getting the data from ACF fields.
					if ( function_exists( 'get_field' ) ) {
						$post_meta = get_field( $metakey, $post_id );
					}

					$post_meta_array = array();
					if ( is_array( $post_meta ) ) {
						foreach ( $post_meta as $meta_key => $meta ) {
							$post_meta_array[ $key ] = is_array( $meta ) ? array_map( 'addslashes', $meta ) : addslashes( $meta );
						}
						$post_meta = $post_meta_array;
					} else {
						$post_meta = addslashes( $post_meta );
					}

					$post_meta_data[ $metakey ] = $post_meta;
				}

				if ( ! $single_array ) {
					$posts[ $key ][0]['post_meta'] = $post_meta_data;
				} else {
					$posts[ $key ]['post_meta'] = $post_meta_data;
				}
			}
		}

		if ( $single_array ) {
			// Prepare for iterator.
			$posts = array(
				'all_posts' => stripslashes( wp_json_encode( $posts ) ),
			);
		} else {
			$posts = array_map( 'wp_json_encode', $posts );
		}

		// Send back results.
		return wp_json_encode( $posts );
	}

	/**
	 * Delete media action
	 *
	 * @since 4.0
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function delete_media( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];

		$media_id = $app_args['media_id'];

		// Check if media ID is provided.
		if ( ! $media_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media ID is required.', 'flowmattic' ),
				)
			);
		}

		// Delete the media.
		$delete_media = wp_delete_attachment( $media_id, true );

		// Check if media is deleted.
		if ( ! $delete_media ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media is not deleted.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Media is successfully deleted.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Rename media action.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function rename_media( $fields ) {
		$app_args  = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$media_id  = $app_args['media_id'];
		$new_title = $app_args['media_title'];

		// Check if media ID is provided.
		if ( ! $media_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if new_title is provided.
		if ( ! $new_title ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'new_title is required.', 'flowmattic' ),
				)
			);
		}

		// Get the media.
		$media = get_post( $media_id );

		// Check if media is found.
		if ( ! $media ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media is not found.', 'flowmattic' ),
				)
			);
		} else {
			// Rename the media.
			$rename_media = wp_update_post(
				array(
					'ID'         => $media_id,
					'post_title' => $new_title,
				)
			);

			// Check if media is renamed.
			if ( ! $rename_media ) {
				return wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Media is not renamed.', 'flowmattic' ),
					)
				);
			}

			return wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Media is successfully renamed.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Get Posts by post_meta query
	 *
	 * @since 2.2.2
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_posts_by_meta( $fields ) {
		$app_args        = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_type       = $app_args['post_type'];
		$meta_key        = $app_args['post_meta_key'];
		$meta_value      = $app_args['post_meta_value'];
		$single_array    = ( isset( $app_args['all_posts_as_array'] ) && 'No' === $app_args['all_posts_as_array'] ) ? false : true;
		$exclude_content = ( isset( $app_args['exclude_post_content'] ) && 'Yes' === $app_args['exclude_post_content'] ) ? true : false;
		$exclude_meta    = ( isset( $app_args['exclude_post_meta'] ) && 'Yes' === $app_args['exclude_post_meta'] ) ? true : false;

		// Prepare query data.
		$args = array(
			'meta_query'     => array( // @codingStandardsIgnoreLine
				array(
					'key'   => $meta_key,
					'value' => $meta_value,
				),
			),
			'post_type'      => $post_type,
			'posts_per_page' => '-1',
		);

		// Run query.
		$posts = get_posts( $args );

		// Check results.
		if ( ! $posts || is_wp_error( $posts ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'No posts found for the given meta query.', 'flowmattic' ),
					'error'   => is_wp_error( $posts ) ? $posts->get_error_message( 'no_posts_found' ) : esc_html__( 'No posts found.', 'flowmattic' ),
				)
			);
		}

		foreach ( $posts as $key => $post_object ) {
			$post_object    = (array) $post_object;
			$posts[ $key ]  = $post_object;
			$post_id        = $post_object['ID'];
			$post_meta_data = array();

			if ( $exclude_content ) {
				unset( $posts[ $key ]['post_content'] );
			} else {
				// Convert double quotes in post content to html entity.
				$posts[ $key ]['post_content'] = esc_attr( $posts[ $key ]['post_content'] );
			}

			// Convert double quotes in post title to html entity.
			$post_object['post_title'] = esc_attr( $post_object['post_title'] );

			if ( $exclude_meta ) {
				unset( $posts[ $key ]['post_meta'] );
			} else {
				// Get all meta.
				$post_meta_data = get_post_meta( $post_id );
				$post_meta_data = array_combine( array_keys( $post_meta_data ), array_column( $post_meta_data, '0' ) );

				foreach ( $post_meta_data as $metakey => $post_meta ) {
					// If ACF is installed, try getting the data from ACF fields.
					if ( function_exists( 'get_field' ) ) {
						$post_meta = get_field( $metakey, $post_id );
					}

					$post_meta_data[ $metakey ] = $post_meta;
				}

				$posts[ $key ]['post_meta'] = $post_meta_data;
			}
		}

		if ( $single_array ) {
			// Prepare for iterator.
			$posts = array(
				'all_posts' => stripslashes( wp_json_encode( $posts ) ),
			);
		} else {
			$posts = array_map( 'wp_json_encode', $posts );
		}

		// Send back results.
		return wp_json_encode( $posts );
	}

	/**
	 * Get post taxonomy.
	 *
	 * @since 4.0
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_post_taxonomies( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Get the post taxonomy.
		$post_taxonomies = get_post_taxonomies( $post_id );

		// Initialize a simple array.
		$taxonomies = array(
			'status'   => 'success',
			'message'  => esc_html__( 'Post taxonomy is successfully retrieved.', 'flowmattic' ),
			'taxonomy' => wp_json_encode( $post_taxonomies ),
		);

		// Loop through the taxonomies, and make into a simple array.
		foreach ( $post_taxonomies as $taxonomy ) {
			// Add individual taxonomies to the taxonomy array.
			$taxonomies[ 'taxonomy_' . $taxonomy ] = $taxonomy;
		}

		// Check if post taxonomy is found.
		if ( ! $post_taxonomies ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post taxonomy is not found.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode( $taxonomies );
	}

	/**
	 * Get post terms.
	 *
	 * @since 4.0
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_the_terms( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$taxonomy = $app_args['taxonomy_name'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if taxonomy is provided.
		if ( ! $taxonomy ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is required.', 'flowmattic' ),
				)
			);
		}

		// Get the post terms.
		$post_terms = get_the_terms( $post_id, $taxonomy );

		// Initialize a simple array.
		$terms = array(
			'status'     => 'success',
			'message'    => esc_html__( 'Post terms is successfully retrieved.', 'flowmattic' ),
			'terms'      => wp_json_encode( $post_terms ),
			'taxonomy'   => $taxonomy,
			'term_ids'   => '',
			'term_names' => '',
			'term_slugs' => '',
		);

		$term_ids   = array();
		$term_names = array();
		$term_slugs = array();

		// Loop through the terms, and make into a simple array.
		foreach ( $post_terms as $term ) {
			// Add term id.
			$terms[ 'term_' . $term->term_id . '_id' ] = $term->term_id;

			// Add term name.
			$terms[ 'term_' . $term->term_id . '_name' ] = $term->name;

			// Add term slug.
			$terms[ 'term_' . $term->term_id . '_slug' ] = $term->slug;

			// Add individual term ids to the term_ids array.
			$term_ids[] = $term->term_id;

			// Add individual term names to the term_names array.
			$term_names[] = $term->name;

			// Add individual term slugs to the term_slugs array.
			$term_slugs[] = $term->slug;
		}

		// Add term ids to the terms array.
		$terms['term_ids'] = wp_json_encode( $term_ids );

		// Add term names to the terms array.
		$terms['term_names'] = wp_json_encode( $term_names );

		// Add term slugs to the terms array.
		$terms['term_slugs'] = wp_json_encode( $term_slugs );

		// Check if post terms is found.
		if ( ! $post_terms ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post terms is not found.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode( $terms );
	}

	/**
	 * Get post by ID.
	 *
	 * @since 4.0
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_post_by_id( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Get the post.
		$post = get_post( $post_id );

		// Check if post is found.
		if ( ! $post ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post is not found.', 'flowmattic' ),
				)
			);
		}

		// Get the post data.
		$post_data = $this->get_post_details( $post );

		// Add success status and message.
		$post_data['status'] = 'success';

		return wp_json_encode( $post_data );
	}

	/**
	 * Get taxonomy by name.
	 *
	 * @since 4.1.3
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function get_taxonomy_by_name( $fields ) {
		$tax_name = $fields['tax_name'];
		$tax_term = $fields['tax_term'];

		// Get the taxonomy for the given name.
		$data = get_term_by( 'name', $tax_name, $tax_term );

		// Check if post is found.
		if ( ! $data ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy not found.', 'flowmattic' ),
				)
			);
		}

		// Initialize a simple array.
		$simple_entry = array(
			'status' => 'success',
		);

		// Loop through the data, and make into a simple array.
		foreach ( $data as $key => $value ) {
			// Add individual data to the simple array.
			$simple_entry[ $key ] = $value;
		}

		return wp_json_encode( $simple_entry );
	}

	/**
	 * Get all users by user role
	 *
	 * @since 4.1.5
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function get_all_users_by_role( $fields ) {
		$user_role = ( isset( $fields['user_role'] ) ) ? $fields['user_role'] : '';

		// Check if user role is provided.
		if ( ! $user_role ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'User role is required.', 'flowmattic' ),
				)
			);
		}

		// Get all users by user role.
		$users = get_users(
			array(
				'role'    => $user_role,
				'orderby' => 'ID',
			)
		);

		// Check if users are found.
		if ( ! $users ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Users are not found.', 'flowmattic' ),
				)
			);
		}

		// Initialize a simple array.
		$all_users = array(
			'status'    => 'success',
			'all_users' => array(),
		);

		// Loop through the users, and make into a simple array.
		foreach ( $users as $user ) {
			$user_data = (array) $user->data;

			// Remove the user passwords from the received data for privacy concern.
			unset( $user_data['user_pass'] );
			unset( $user_data['userpass'] );
			unset( $user_data['user_password'] );

			// Add individual user to the all_users array.
			$all_users[ 'user_' . $user->ID ] = wp_json_encode( $user_data );

			// Add individual user to the all_users array.
			$all_users['all_users'][] = $user_data;
		}

		// Add all the users to the all_users array.
		$all_users['all_users'] = wp_json_encode( $all_users['all_users'] );

		return wp_json_encode( $all_users );
	}

	/**
	 * Add tag to post.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function add_tag_to_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$tag_name = $app_args['tag_name'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if tag name is provided.
		if ( ! $tag_name ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag name is required.', 'flowmattic' ),
				)
			);
		}

		// Add tag to post.
		$add_tag = wp_set_post_tags( $post_id, $tag_name, true );

		// Check if tag is added.
		if ( ! $add_tag ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag is not added.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Tag is successfully added.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Remove tag from post.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function remove_tag_from_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$tag_name = $app_args['tag_name'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if tag name is provided.
		if ( ! $tag_name ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag name is required.', 'flowmattic' ),
				)
			);
		}

		// Remove tag from post.
		$remove_tag = wp_remove_object_terms( $post_id, $tag_name, 'post_tag' );

		// Check if tag is removed.
		if ( ! $remove_tag ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag is not removed.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Tag is successfully removed.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Add category to post.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function add_category_to_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$cat_name = $app_args['category_name'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if category name is provided.
		if ( ! $cat_name ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category name is required.', 'flowmattic' ),
				)
			);
		}

		// Get the category ID by slug.
		$cat_id = get_cat_ID( $cat_name );

		// If category ID is not found, try to get the category by slug.
		if ( 0 === $cat_id ) {
			$cat = get_term_by( 'slug', $cat_name, 'category' );
			$cat_id = ( $cat && ! is_wp_error( $cat ) ) ? $cat->term_id : 0;
		}

		// Add category to post.
		$add_cat = wp_set_post_categories( $post_id, array( $cat_id ), true );

		// Check if category is added.
		if ( ! $add_cat ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category is not added.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Category is successfully added.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Remove category from post.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function remove_category_from_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$cat_name = $app_args['category_name'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if category name is provided.
		if ( ! $cat_name ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category name is required.', 'flowmattic' ),
				)
			);
		}

		// Remove category from post.
		$remove_cat = wp_remove_object_terms( $post_id, $cat_name, 'category' );

		// Check if category is removed.
		if ( ! $remove_cat ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category is not removed.', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Category is successfully removed.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Check plugin activation.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function check_plugin_active( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$plugin   = $app_args['plugin'];

		// Check if plugin is provided.
		if ( ! $plugin ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Plugin is required', 'flowmattic' ),
				)
			);
		}

		// Check if plugin is active.
		if ( is_plugin_active( $plugin ) ) {
			return wp_json_encode(
				array(
					'status'  => 'success',
					'message' => esc_html__( 'Plugin is active', 'flowmattic' ),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'error',
				'message' => esc_html__( 'Plugin is not active', 'flowmattic' ),
			)
		);
	}

	/**
	 * Activate plugin.
	 *
	 * @since 4.1.6
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function activate_plugin( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$plugin   = $app_args['plugin'];

		// Check if plugin is provided.
		if ( ! $plugin ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Plugin is required', 'flowmattic' ),
				)
			);
		}

		// Check if plugin is already active.
		if ( is_plugin_active( $plugin ) ) {
			return wp_json_encode(
				array(
					'status'  => 'status',
					'message' => esc_html__( 'Plugin is already activated', 'flowmattic' ),
				)
			);
		}

		// Activate the plugin.
		$activate = activate_plugin( $plugin );

		// Check if plugin is activated.
		if ( is_wp_error( $activate ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Plugin is not activated', 'flowmattic' ),
					'error'   => $activate->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Plugin is successfully activated', 'flowmattic' ),
			)
		);
	}

	/**
	 * Update category.
	 *
	 * @since 1.3.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function update_category( $fields ) {
		$app_args        = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$cat_id          = $app_args['cat_id'];
		$cat_name        = $app_args['cat_name'];
		$cat_description = $app_args['cat_description'];
		$cat_slug        = $app_args['cat_slug'];

		// Check if category ID is provided.
		if ( ! $cat_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category ID is required.', 'flowmattic' ),
				)
			);
		}

		$data_to_update = array();

		// Check if category description is provided.
		if ( $cat_description ) {
			$data_to_update['description'] = $cat_description;
		}

		// Check if category name is provided.
		if ( $cat_name ) {
			$data_to_update['name'] = $cat_name;
		}

		// Check if category slug is provided.
		if ( $cat_slug ) {
			$data_to_update['slug'] = $cat_slug;
		}

		// Update the category.
		$update_cat = wp_update_term( $cat_id, 'category', $data_to_update );

		// Check if category is updated.
		if ( is_wp_error( $update_cat ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Category is not updated.', 'flowmattic' ),
					'error'   => $update_cat->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Category is successfully updated.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Update tag.
	 *
	 * @since 1.3.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function update_tag( $fields ) {
		$app_args        = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$tag_id          = $app_args['tag_id'];
		$tag_name        = $app_args['tag_name'];
		$tag_description = $app_args['tag_description'];
		$tag_slug        = $app_args['tag_slug'];

		// Check if tag ID is provided.
		if ( ! $tag_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag ID is required.', 'flowmattic' ),
				)
			);
		}

		$data_to_update = array();

		// Check if tag description is provided.
		if ( $tag_description ) {
			$data_to_update['description'] = $tag_description;
		}

		// Check if tag name is provided.
		if ( $tag_name ) {
			$data_to_update['name'] = $tag_name;
		}

		// Check if tag slug is provided.
		if ( $tag_slug ) {
			$data_to_update['slug'] = $tag_slug;
		}

		// Update the tag.
		$update_tag = wp_update_term( $tag_id, 'post_tag', $data_to_update );

		// Check if tag is updated.
		if ( is_wp_error( $update_tag ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Tag is not updated.', 'flowmattic' ),
					'error'   => $update_tag->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Tag is successfully updated.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Update term.
	 *
	 * @since 1.3.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function update_term( $fields ) {
		$app_args         = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$term_id          = $app_args['tag_id'];
		$term_name        = $app_args['tag_name'];
		$term_description = $app_args['tag_description'];
		$term_slug        = $app_args['tag_slug'];
		$taxonomy         = $app_args['term_taxonomy'];

		// Check if term ID is provided.
		if ( ! $term_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if taxonomy is provided.
		if ( ! $taxonomy ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is required.', 'flowmattic' ),
				)
			);
		}

		$data_to_update = array();

		// Check if term description is provided.
		if ( $term_description ) {
			$data_to_update['description'] = $term_description;
		}

		// Check if term name is provided.
		if ( $term_name ) {
			$data_to_update['name'] = $term_name;
		}

		// Check if term slug is provided.
		if ( $term_slug ) {
			$data_to_update['slug'] = $term_slug;
		}

		// Update the term.
		$update_term = wp_update_term( $term_id, $taxonomy, $data_to_update );

		// Check if term is updated.
		if ( is_wp_error( $update_term ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term is not updated.', 'flowmattic' ),
					'error'   => $update_term->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Term is successfully updated.', 'flowmattic' ),
			)
		);
	}

	/**
	 * Search media by title.
	 *
	 * @since 4.3.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function search_media_by_title( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$title    = $app_args['media_title'];

		// Check if title is provided.
		if ( ! $title ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Title is required.', 'flowmattic' ),
				)
			);
		}

		// Search media by title.
		$media = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				's'              => $title,
			)
		);

		// Check if media is found.
		if ( ! $media ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media is not found.', 'flowmattic' ),
				)
			);
		}

		// Initialize a simple array.
		$all_media = array(
			'status'    => 'success',
			'all_media' => array(),
		);

		// Loop through the media, and make into a simple array.
		foreach ( $media as $media_item ) {
			$media_data = (array) $media_item;

			// Add individual media to the all_media array.
			$all_media['all_media'][] = $media_data;
		}

		// Add all the media to the all_media array.
		$all_media['all_media'] = wp_json_encode( $all_media['all_media'] );

		return wp_json_encode( $all_media );
	}

	/**
	 * Add taxonomy to post.
	 *
	 * @since 5.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function add_taxonomy_to_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$taxonomy = $app_args['taxonomy_name'];
		$term     = $app_args['taxonomy_term'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if taxonomy is provided.
		if ( ! $taxonomy ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is required.', 'flowmattic' ),
				)
			);
		}

		// Check if term is provided.
		if ( ! $term ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term is required.', 'flowmattic' ),
				)
			);
		}

		// Add taxonomy to post.
		$add_taxonomy = wp_set_object_terms( $post_id, $term, $taxonomy, true );

		// Check if taxonomy is added.
		if ( is_wp_error( $add_taxonomy ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is not added.', 'flowmattic' ),
					'error'   => $add_taxonomy->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Taxonomy is successfully added.', 'flowmattic' ),
				'post_id' => $post_id,
				'taxonomy' => $taxonomy,
				'term' => $term,
			)
		);
	}

	/**
	 * Remove taxonomy from post.
	 *
	 * @since 5.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function remove_taxonomy_from_post( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$post_id  = $app_args['post_id'];
		$taxonomy = $app_args['taxonomy_name'];
		$term     = $app_args['taxonomy_term'];

		// Check if post ID is provided.
		if ( ! $post_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Post ID is required.', 'flowmattic' ),
				)
			);
		}

		// Check if taxonomy is provided.
		if ( ! $taxonomy ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is required.', 'flowmattic' ),
				)
			);
		}

		// Check if term is provided.
		if ( ! $term ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term is required.', 'flowmattic' ),
				)
			);
		}

		// Remove taxonomy from post.
		$remove_taxonomy = wp_remove_object_terms( $post_id, $term, $taxonomy );

		// Check if taxonomy is removed.
		if ( is_wp_error( $remove_taxonomy ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is not removed.', 'flowmattic' ),
					'error'   => $remove_taxonomy->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Taxonomy is successfully removed.', 'flowmattic' ),
				'post_id' => $post_id,
				'taxonomy' => $taxonomy,
				'term' => $term,
			)
		);
	}

	/**
	 * Create new taxonomy term.
	 *
	 * @since 5.0
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function create_term( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$term_name = $app_args['term_name'];
		$taxonomy  = $app_args['taxonomy_name'];

		// Optional term arguments.
		$alias_of = isset( $app_args['alias_of'] ) ? $app_args['alias_of'] : '';
		$description = isset( $app_args['term_description'] ) ? $app_args['term_description'] : '';
		$parent = isset( $app_args['parent_term_id'] ) ? $app_args['parent_term_id'] : '';
		$slug = isset( $app_args['term_slug'] ) ? $app_args['term_slug'] : '';

		$term_args = array();

		// Check if alias of is provided.
		if ( '' !== $alias_of ) {
			$term_args['alias_of'] = $alias_of;
		}

		// Check if description is provided.
		if ( '' !== $description ) {
			$term_args['description'] = $description;
		}

		// Check if parent is provided.
		if ( '' !== $parent ) {
			$term_args['parent'] = $parent;
		}

		// Check if slug is provided.
		if ( '' !== $slug ) {
			$term_args['slug'] = $slug;
		}

		// Check if term name is provided.
		if ( ! $term_name ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term name is required.', 'flowmattic' ),
				)
			);
		}

		// Check if taxonomy is provided.
		if ( ! $taxonomy ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Taxonomy is required.', 'flowmattic' ),
				)
			);
		}

		// Create term.
		$term = wp_insert_term( $term_name, $taxonomy, $term_args );

		// Check if term is created.
		if ( is_wp_error( $term ) ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Term is not created.', 'flowmattic' ),
					'error'   => $term->get_error_message(),
				)
			);
		}

		return wp_json_encode(
			array(
				'status'  => 'success',
				'message' => esc_html__( 'Term is successfully created.', 'flowmattic' ),
				'term_id' => $term['term_id'],
				'term_name' => $term_name,
				'taxonomy' => $taxonomy,
			)
		);
	}

	/**
	 * Get media by ID.
	 *
	 * @since 5.0.5
	 * @param array $fields Workflow captured data.
	 * @return string JSON String.
	 */
	public function get_media_by_id( $fields ) {
		$app_args = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$media_id = $app_args['media_id'];

		// Check if media ID is provided.
		if ( ! $media_id ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media ID is required.', 'flowmattic' ),
				)
			);
		}

		// Get media by ID.
		$media = get_post( $media_id );

		// Check if media is found.
		if ( ! $media ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Media is not found.', 'flowmattic' ),
				)
			);
		}

		// Initialize a simple array.
		$media_data = array(
			'status'    => 'success',
			'media' => array(),
		);

		$media_file = wp_get_attachment_image_src( $media_id, 'full' );

		// Add individual media to the media_data array.
		$media_data['media_url']    = $media_file[0];
		$media_data['media_width']  = $media_file[1];
		$media_data['media_height'] = $media_file[2];
		$media_data['media_post']   = wp_json_encode( (array) $media );

		return wp_json_encode( $media_data );
	}

	/**
	 * Get user metadata.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param array $fields Workflow captured data.
	 * @return array
	 */
	public function get_user_meta( $fields ) {
		$user_id       = (int) $fields['user_id'];
		$user_meta_key = isset( $fields['user_meta_key'] ) ? $fields['user_meta_key'] : '';

		$simple_entry = array();

		// If single user meta.
		if ( '' !== $user_meta_key ) {

			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$user_meta_data = get_field( $user_meta_key, 'user_' . $user_id );

				$simple_entry = array(
					'user_meta' => $user_meta_key,
				);

				if ( $user_meta_data ) {
					if ( is_array( $user_meta_data ) ) {
						$array_value = wp_json_encode( $user_meta_data );

						$simple_entry['acf_array_value'] = $array_value;
					} else {
						$simple_entry['value'] = $user_meta_data;
					}
				} else {
					$user_meta_data = get_user_meta( $user_id, $user_meta_key, true );

					$simple_entry['value'] = $user_meta_data;
				}
			} else {
				$user_meta_data = get_user_meta( $user_id, $user_meta_key, true );

				$simple_entry = array(
					'user_meta' => $user_meta_key,
					'value'     => $user_meta_data,
				);
			}
		} else {
			$simple_entry = $this->get_user_metadata( $user_id );
		}

		return wp_json_encode( $simple_entry );
	}

	/**
	 * Get WP user metadata.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $user_id User ID.
	 * @return array
	 */
	public function get_user_metadata( $user_id ) {
		$simple_entry   = array();
		$user_meta_data = get_user_meta( $user_id );

		foreach ( $user_meta_data as $key => $user_meta ) {
			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$user_meta = get_field( $key, 'user_' . $user_id );
			} else {
				$user_meta = maybe_unserialize( $user_meta[0] );
			}

			if ( is_array( $user_meta ) ) {
				$user_meta = wp_json_encode( $user_meta );
			}

			$simple_entry[ $key ] = $user_meta;
		}

		return $simple_entry;
	}

	/**
	 * Get all WP user roles.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_all_user_roles() {
		$all_user_roles = array();
		$user_roles     = wp_roles();
		foreach ( $user_roles->roles as $name => $role ) {
			$all_user_roles[ $name ] = esc_attr( $role['name'] );
		}

		return $all_user_roles;
	}

	/**
	 * Get all WP user roles capabilities.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_all_user_role_capabilities() {
		$all_user_role_capabilities = array();
		$user_roles                 = wp_roles();

		foreach ( $user_roles->roles as $name => $role ) {
			foreach ( $role['capabilities'] as $key => $cap ) {
				$all_user_role_capabilities[ $key ] = esc_attr( $key );
			}
		}

		return $all_user_role_capabilities;
	}

	/**
	 * Fetch records from database.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function fetch_from_database() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get record type.
		$record_type = ( isset( $_POST['recordType'] ) ) ? sanitize_text_field( wp_unslash( $_POST['recordType'] ) ) : '';

		// Initialize record array.
		$record_data = array(
			'response' => esc_html__( 'No records found, please try capturing live data', 'flowmattic' ),
		);

		// Initial record data.
		$record_data = array();

		switch ( $record_type ) {
			case 'post':
			case 'page':
				$args = array(
					'post_type'      => $record_type,
					'posts_per_page' => 1,
					'orderby'        => 'id',
					'order'          => 'DESC',
					'post_status'    => 'any',
				);

				// Get posts.
				$posts = get_posts( $args );

				if ( ! empty( $posts ) ) {
					foreach ( $posts as $wp_post ) {
						$record_data = $this->get_post_details( $wp_post );
					}
				}

				break;

			case 'user':
				// Get current user.
				$current_user = wp_get_current_user();

				// Get user data.
				$record_data = $this->get_user_details_by_id( $current_user->ID );

				break;

			case 'media':
				$args = array(
					'post_type'      => 'attachment',
					'posts_per_page' => 1,
					'orderby'        => 'id',
					'order'          => 'DESC',
					'post_status'    => 'any',
				);

				// Get posts.
				$posts = get_posts( $args );

				if ( ! empty( $posts ) ) {
					foreach ( $posts as $wp_post ) {
						$attachment_data = (array) $wp_post;

						// Get the post featured image data.
						$record_data = flowmattic_get_attachment( $wp_post->ID );

						// Add attachment ID.
						$record_data['attachment_id'] = $wp_post->ID;

						// Get attachment MIME type.
						$record_data['mime_type'] = $wp_post->post_mime_type;

						// Get post Meta.
						$post_meta_data = get_post_meta( $wp_post->ID );

						foreach ( $post_meta_data as $key => $post_meta ) {
							// If ACF is installed, try getting the data from ACF fields.
							if ( function_exists( 'get_field' ) ) {
								$post_meta = get_field( $key, $wp_post->ID );
							}

							$post_meta_data[ $key ] = $post_meta;
						}

						$record_data['attachment_meta'] = wp_json_encode( $post_meta_data );
					}
				}

				break;
		}

		echo wp_json_encode( $record_data );

		die();
	}

	/**
	 * Get post details.
	 *
	 * @access public
	 * @since 4.0
	 * @param object $wp_post WP Post object.
	 * @return array
	 */
	public function get_post_details( $wp_post ) {
		$current_taxonomies = get_object_taxonomies( $wp_post, 'objects' );
		$taxonomies         = array();
		$record_data        = (array) $wp_post;

		// Add permalink.
		$record_data['permalink'] = get_permalink( $wp_post->ID );

		// Loop through taxonomies.
		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $wp_post, $tax_title );
			if ( $terms ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = array(
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					);
				}
			}
		}

		$record_data['taxonomies'] = wp_json_encode( $taxonomies );

		// Get the post featured image.
		$post_thumbnail_id = get_post_thumbnail_id( $wp_post->ID );
		$featured_image    = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

		if ( $featured_image ) {
			$record_data['featured_image']    = $featured_image[0];
			$record_data['featured_image_id'] = $post_thumbnail_id;
		}

		// Get post Meta.
		$post_meta_data = get_post_meta( $wp_post->ID );

		foreach ( $post_meta_data as $key => $post_meta ) {
			// If ACF is installed, try getting the data from ACF fields.
			if ( function_exists( 'get_field' ) ) {
				$post_meta = get_field( $key, $wp_post->ID );
			}

			$post_meta_data[ $key ] = $post_meta;
		}

		$record_data['post_meta'] = wp_json_encode( $post_meta_data );

		return $record_data;
	}

	/**
	 * Get user details by ID.
	 *
	 * @access public
	 * @since 4.0
	 * @param int $user_id User ID.
	 * @return array
	 */
	public function get_user_details_by_id( $user_id ) {
		// Get user.
		$user = get_userdata( $user_id );

		// Get the user data.
		$user_data = (array) $user->data;

		// Remove the user passwords from the received data for privacy concern.
		unset( $user_data['pass1'] );
		unset( $user_data['pass2'] );
		unset( $user_data['password'] );
		unset( $user_data['user_pass'] );
		unset( $user_data['userpass'] );
		unset( $user_data['user_password'] );
		unset( $user_data['user_activation_key'] );

		$user_data['user_id'] = $user_id;

		// Get all available meta data for user.
		$user_meta = $this->get_user_metadata( $user_id );

		// Loop through user meta data to normalize the data.
		foreach ( $user_meta as $key => $meta ) {
			$user_data[ $key ] = $meta;
		}

		// Add user meta data to user data.
		$user_data['user_meta_json'] = wp_json_encode( $user_meta );

		return $user_data;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		// Set true to avoid running the entire workflow.
		$this->is_doing_test = true;

		$response    = array();
		$event       = $event_data['event'];
		$fields      = isset( $event_data['actionAppArgs'] ) ? $event_data['actionAppArgs'] : $event_data['settings'];
		$workflow_id = $event_data['workflow_id'];

		if ( isset( $event_data['fields']['user_id'] ) ) {
			$fields['user_id'] = $event_data['fields']['user_id'];
		}

		if ( isset( $event_data['fields']['user_email'] ) ) {
			$fields['user_email'] = $event_data['fields']['user_email'];
		}

		// Compatibility fix.
		$fields['actionAppArgs'] = $fields;

		switch ( $event ) {
			case 'new_user':
				$response = $this->create_new_user( $fields );
				break;

			case 'update_user':
				$response = $this->update_existing_user( $fields );
				break;

			case 'delete_post':
				$response = $this->delete_post( $fields );
				break;

			case 'new_comment':
				$response = $this->create_new_comment( $fields );
				break;

			case 'new_post':
				$response = $this->create_new_post( $fields );
				break;

			case 'update_post':
				$response = $this->update_post( $fields );
				break;

			case 'new_media':
				$response = $this->create_new_media( $fields );
				break;

			case 'update_user_meta':
				$response = $this->update_user_meta( $fields );
				break;

			case 'get_user_by_id':
				$response = $this->get_user_by_id( $fields );
				break;

			case 'get_user_by_email':
				$response = $this->get_user_by_email( $fields );
				break;

			case 'create_category':
				if ( isset( $event_data['fields']['category_name'] ) ) {
					$fields['category_name'] = $event_data['fields']['category_name'];
				}

				$response = $this->create_category( $fields );
				break;

			case 'create_tag':
				if ( isset( $event_data['fields']['tag_name'] ) ) {
					$fields['tag_name'] = $event_data['fields']['tag_name'];
				}

				$response = $this->create_tag( $fields );
				break;

			case 'add_user_role':
				$response = $this->add_user_role( $fields );
				break;

			case 'change_user_role':
				$response = $this->change_user_role( $fields );
				break;

			case 'remove_user_role':
				$response = $this->remove_user_role( $fields );
				break;

			case 'delete_user':
				$response = $this->delete_user( $fields );
				break;

			case 'add_role':
				if ( isset( $event_data['fields'] ) ) {
					$fields = $event_data['fields'];
				}

				$response = $this->add_role( $fields );
				break;

			case 'get_post_meta':
				$response = $this->get_post_meta( $fields );
				break;

			case 'get_user_meta':
				$response = $this->get_user_meta( $fields );
				break;

			case 'get_posts_by_meta':
				$response = $this->get_posts_by_meta( $fields );
				break;

			case 'get_posts_by_post_type':
				$response = $this->get_posts_by_post_type( $fields );
				break;

			case 'delete_media':
				$response = $this->delete_media( $fields );
				break;

			case 'rename_media':
				$response = $this->rename_media( $fields );
				break;

			case 'get_post_taxonomies':
				$response = $this->get_post_taxonomies( $fields );
				break;

			case 'get_taxonomy_by_name':
				if ( isset( $event_data['fields'] ) ) {
					$fields = $event_data['fields'];
				}

				$response = $this->get_taxonomy_by_name( $fields );
				break;

			case 'get_all_users_by_role':
				$response = $this->get_all_users_by_role( $fields );
				break;

			case 'get_the_terms':
				$response = $this->get_the_terms( $fields );
				break;

			case 'get_post_by_id':
				$response = $this->get_post_by_id( $fields );
				break;

			case 'add_tag_to_post':
				$response = $this->add_tag_to_post( $fields );
				break;

			case 'remove_tag_from_post':
				$response = $this->remove_tag_from_post( $fields );
				break;

			case 'add_category_to_post':
				$response = $this->add_category_to_post( $fields );
				break;

			case 'remove_category_from_post':
				$response = $this->remove_category_from_post( $fields );
				break;

			case 'check_plugin_active':
				$response = $this->check_plugin_active( $fields );
				break;

			case 'activate_plugin':
				$response = $this->activate_plugin( $fields );
				break;

			case 'update_category':
				$response = $this->update_category( $fields );
				break;

			case 'update_tag':
				$response = $this->update_tag( $fields );
				break;

			case 'update_term':
				$response = $this->update_term( $fields );
				break;

			case 'search_media_by_title':
				$response = $this->search_media_by_title( $fields );
				break;

			case 'add_taxonomy_to_post':
				$response = $this->add_taxonomy_to_post( $fields );
				break;

			case 'remove_taxonomy_from_post':
				$response = $this->remove_taxonomy_from_post( $fields );
				break;

			case 'create_term':
				$response = $this->create_term( $fields );
				break;

			case 'get_media_by_id':
				$response = $this->get_media_by_id( $fields );
				break;

			default:
				$response = wp_json_encode(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid action event.', 'flowmattic' ),
					)
				);
				break;
		}

		return $response;
	}
}

new FlowMattic_Wordpress();
