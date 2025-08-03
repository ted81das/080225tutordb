<?php

namespace Groundhogg\Api\V3;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Groundhogg\Contact;
use Groundhogg\Contact_Query;
use function Groundhogg\current_user_is;
use function Groundhogg\get_array_var;
use function Groundhogg\get_contactdata;
use Groundhogg\Plugin;
use function Groundhogg\get_db;
use function Groundhogg\sort_by_string_in_array;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Contacts_Api extends Base {

	public function register_routes() {

		$auth_callback = $this->get_auth_callback();

		register_rest_route( self::NAME_SPACE, '/contacts', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_contacts' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'query'       => [
						'required'    => false,
						'description' => _x( 'An array of query args. See WPGH_Contact_Query for acceptable arguments.', 'api', 'groundhogg' ),
					],
					'select'      => [
						'required'    => false,
						'description' => _x( 'Whether to retrieve as available for a select input.', 'api', 'groundhogg' ),
					],
					'select2'     => [
						'required'    => false,
						'description' => _x( 'Whether to retrieve as available for an ajax select2 input.', 'api', 'groundhogg' ),
					],
					'search'      => [
						'required'    => false,
						'description' => _x( 'Search string for tag name.', 'api', 'groundhogg' ),
					],
					'q'           => [
						'required'    => false,
						'description' => _x( 'Shorthand for search.', 'api', 'groundhogg' ),
					],
					'id_or_email' => [
						'required'    => false,
						'description' => _x( 'The ID or email of the contact you want to get.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
				]
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_contact' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'contact' => [
						'required'    => false,
						'description' => _x( 'Contains list of contact arguments. Please visit www.groundhogg.io for full list of accepted arguments.', 'api', 'groundhogg' )
					]
				]
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_contact' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to update.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'contact'     => [
						'required'    => true,
						'description' => _x( 'Array of updated contact details.', 'api', 'groundhogg' )
					],
				]
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_contact' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => false,
						'description' => _x( 'The ID or email of the contact you want to delete.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
				]
			],
		] );

		register_rest_route( self::NAME_SPACE, '/contacts/tags', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_tags' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to apply tags to.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
				]
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'apply_tags' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to apply tags to.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'tags'        => [
						'required'    => true,
						'description' => _x( 'Array of tag names or tag ids.', 'api', 'groundhogg' ),
					]
				]
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'remove_tags' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to remove tags from.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'tags'        => [
						'required'    => true,
						'description' => _x( 'Array of tag names or tag ids.', 'api', 'groundhogg' ),
					]
				]
			]
		] );

		register_rest_route( self::NAME_SPACE, '/contacts/apply_tags', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'apply_tags' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to apply tags to.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'tags'        => [
						'required'    => true,
						'description' => _x( 'Array of tag names or tag ids.', 'api', 'groundhogg' ),
					]
				]
			]
		] );

		register_rest_route( self::NAME_SPACE, '/contacts/remove_tags', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'remove_tags' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to remove tags from.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'tags'        => [
						'required'    => true,
						'description' => _x( 'Array of tag names or tag ids.', 'api', 'groundhogg' ),
					]
				]
			]
		] );

		register_rest_route( self::NAME_SPACE, '/contacts/notes', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_notes' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to apply tags to.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					]
				]
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_note' ],
				'permission_callback' => $auth_callback,
				'args'                => [
					'id_or_email' => [
						'required'    => true,
						'description' => _x( 'The ID or email of the contact you want to apply tags to.', 'api', 'groundhogg' ),
					],
					'by_user_id'  => [
						'required'    => false,
						'description' => _x( 'Search using the user ID.', 'api', 'groundhogg' ),
					],
					'note'        => [
						'required'    => true,
						'description' => _x( 'the note text you wish to add.', 'api', 'groundhogg' ),
					]
				]
			]
		] );


	}

	/**
	 * Get a contact which will look good in a JSON response.
	 *
	 * @param $id
	 *
	 * @return array|false
	 */
	public function get_contact_for_rest_response( $id ) {
		$contact = get_contactdata( $id );

		return $contact ? $contact->get_as_array() : false;
	}

	/**
	 * Takes a single parameter 'query' or empty to return a list of contacts.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_contacts( WP_REST_Request $request ) {
		if ( ! current_user_can( 'view_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		/* CHECK IF SINGLE FIRST */
		$contact = self::get_contact_from_request( $request );

		/* Is single */
		if ( $contact ) {
			if ( is_wp_error( $contact ) ) {
				return $contact;
			}

			return self::SUCCESS_RESPONSE( [ 'contact' => $this->get_contact_for_rest_response( $contact->ID ) ] );
		}

		$query = $request->get_param( 'query' ) ? (array) $request->get_param( 'query' ) : [];

		$search = $request->get_param( 'q' ) ? $request->get_param( 'q' ) : $request->get_param( 'search' );
		$search = sanitize_text_field( stripslashes( $search ) );

		if ( ! key_exists( 'search', $query ) && ! empty( $search ) ) {
			$query['search'] = $search;
		}

		$is_for_select  = filter_var( $request->get_param( 'select' ), FILTER_VALIDATE_BOOLEAN );
		$is_for_select2 = filter_var( $request->get_param( 'select2' ), FILTER_VALIDATE_BOOLEAN );

		$contact_query = new Contact_Query();

		$default_query_limit = $request->get_param( 'limit' ) ?: 100;
		$default_offset      = $request->get_param( 'offset' ) ?: 0;

		$query = wp_parse_args( $query, [
			'limit'  => $default_query_limit,
			'offset' => $default_offset,
		] );

		if ( $is_for_select2 || $is_for_select ) {
			$query['orderby'] = 'last_name';
			$query['order']   = 'ASC';
		}

		$contacts = $contact_query->query( $query );

		if ( $is_for_select2 ) {
			$json = array();

			foreach ( $contacts as $i => $contact ) {

				$json[] = array(
					'id'   => absint( $contact->ID ),
					'text' => sprintf( "%s %s (%s)", $contact->first_name, $contact->last_name, $contact->email )
				);

			}

			$results = array( 'results' => $json, 'more' => false );

			return rest_ensure_response( $results );
		} else if ( $is_for_select ) {

			$response_contacts = [];

			foreach ( $contacts as $i => $contact ) {
				$response_contacts[ absint( $contact->ID ) ] = sprintf( "%s %s (%s)", $contact->first_name, $contact->last_name, $contact->email );
			}

			$contacts = $response_contacts;

		} else {
			$response_contacts = [];

			foreach ( $contacts as $contact ) {
				$id                       = absint( $contact->ID );
				$response_contacts[ $id ] = $this->get_contact_for_rest_response( $id );
			}

			$contacts = $response_contacts;
		}

		$response = [
			'contacts' => $contacts,
		];

		if ( $request->get_param( 'show_sql' ) ) {
			$response['sql'] = $contact_query->request;
		}

		return self::SUCCESS_RESPONSE( $response );
	}

	/**
	 * Update a contact given their whatever...
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function create_contact( WP_REST_Request $request ) {
		if ( ! current_user_can( 'add_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$args = (array) $request->get_param( 'contact' ) ?: (array) $request->get_params();

		$meta = get_array_var( $args, 'meta', [] );
		$tags = get_array_var( $args, 'tags', [] );

		if ( ! isset( $args['email'] ) || ! is_email( $args['email'] ) ) {
			return self::ERROR_400( 'invalid_email', _x( 'Please provide a valid email address.', 'api', 'groundhogg' ) );
		}

		$args = map_deep( $args, 'sanitize_text_field' );

		$contact_id = get_db( 'contacts' )->add( $args );

		if ( ! $contact_id ) {
			return self::ERROR_400( 'error', 'Unable to add contact.' );
		}

		$contact = get_contactdata( $contact_id );

		// Add any meta data
		foreach ( $meta as $key => $value ) {
			$contact->update_meta( sanitize_key( $key ), sanitize_text_field( $value ) );
		}

		// Add any tags
		if ( ! empty( $tags ) ) {
			$contact->add_tag( $tags );
		}

		$contact = $this->get_contact_for_rest_response( $contact_id );

		return self::SUCCESS_RESPONSE( [
			'contact' => $contact
		], _x( 'Contact added successfully.', 'api', 'groundhogg' ) );

	}

	/**
	 * Updates a contact given a contact array
	 * Can also apply & remove tags
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_contact( WP_REST_Request $request ) {
		if ( ! current_user_can( 'edit_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		$args = (array) $request->get_param( 'contact' ) ?: (array) $request->get_params();

		$meta        = get_array_var( $args, 'meta', [] );
		$meta_delete = get_array_var( $args, 'meta_delete', [] );
		$tags        = get_array_var( $args, 'tags', [] );
		$tags_remove = get_array_var( $args, 'tags_remove', [] );

		if ( isset( $args['email'] ) && ! is_email( $args['email'] ) ) {
			return self::ERROR_400( 'invalid_email', _x( 'Please provide a valid email address.', 'api', 'groundhogg' ) );
		}

		if ( isset( $args['email'] ) && $args['email'] !== $contact->get_email() && get_db( 'contacts' )->exists( $args['email'] ) ) {
			return self::ERROR_400( 'email_in_use', _x( 'This email address already belongs to another contact.', 'api', 'groundhogg' ) );
		}

		if ( isset( $args['optin_status'] ) && absint( $args['optin_status'] ) !== $contact->get_optin_status() ) {
			$contact->change_marketing_preference( absint( $args['optin_status'] ) );
			unset( $args['optin_status'] );
		}

		$args = map_deep( $args, 'sanitize_text_field' );

		//adding data in contact table
		$contact->update( $args );

		// insert data in contact meta table if users send meta data
		foreach ( $meta as $key => $value ) {
			$contact->update_meta( sanitize_key( $key ), sanitize_text_field( $value ) );
		}

		foreach ( $meta_delete as $key ) {
			$contact->delete_meta( sanitize_key( $key ) );
		}

		// Add any tags
		if ( ! empty( $tags ) ) {
			$contact->add_tag( $tags );
		}

		if ( ! empty( $tags_remove ) ) {
			$contact->remove_tag( $tags_remove );
		}

		$contact = $this->get_contact_for_rest_response( $contact->ID );

		return self::SUCCESS_RESPONSE( [
			'contact' => $contact
		], _x( 'Contact updated successfully.', 'api', 'groundhogg' ) );
	}

	/**
	 * Delete contacts
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function delete_contact( WP_REST_Request $request ) {
		if ( ! current_user_can( 'delete_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		if ( ! $contact->delete() ) {
			return self::ERROR_UNKNOWN();
		}

		return self::SUCCESS_RESPONSE( [], _x( 'Contact deleted successfully.', 'api', 'groundhogg' ) );

	}

	/**
	 * Get list of tag IDs that a contact has.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return false|WP_Error|WP_REST_Response|Contact
	 */
	public function get_tags( WP_REST_Request $request ) {
		if ( ! current_user_can( 'view_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		return self::SUCCESS_RESPONSE( [ 'tags' => $contact->get_tags() ] );
	}

	/**
	 * Apply tags to a contact
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return false|WP_Error|WP_REST_Response|Contact
	 */
	public function apply_tags( WP_REST_Request $request ) {

		if ( ! current_user_can( 'edit_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		$tag_names = $request->get_param( 'tags' );

		if ( empty( $tag_names ) ) {
			return self::ERROR_400( 'invalid_tag_names', 'An array of tags is required.' );
		}

		$contact->apply_tag( $tag_names );

		return self::SUCCESS_RESPONSE();

	}

	/**
	 * Remove tags from a contact
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function remove_tags( WP_REST_Request $request ) {

		if ( ! current_user_can( 'edit_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		$tag_names = $request->get_param( 'tags' );

		if ( empty( $tag_names ) ) {
			return self::ERROR_400( 'invalid_tag_names', 'An array of tags is required.' );
		}

		$contact->remove_tag( $tag_names );

		return self::SUCCESS_RESPONSE();

	}

	/**
	 * Get the note
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_notes( WP_REST_Request $request ) {
		if ( ! current_user_can( 'view_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		$notes = $contact->get_all_notes();
		$response = [];

		foreach ( $notes as $note ){
			$response[] = $note->get_as_array();
		}

		return self::SUCCESS_RESPONSE( [ 'notes' => $notes ] );
	}

	/**
	 * Add a note to the contact.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return false|WP_Error|WP_REST_Response|Contact
	 */
	public function add_note( WP_REST_Request $request ) {
		if ( ! current_user_can( 'edit_contacts' ) ) {
			return self::ERROR_INVALID_PERMISSIONS();
		}

		$contact = self::get_contact_from_request( $request );

		if ( is_wp_error( $contact ) ) {
			return $contact;
		}

		$note = sanitize_textarea_field( $request->get_param( 'note' ) );

		if ( ! $contact->add_note( $note, 'api' ) ) {
			return self::ERROR_403( 'bad_note', 'Could not add the given note.' );
		}

		return self::SUCCESS_RESPONSE();
	}

}