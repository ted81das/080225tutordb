<?php
/**
 * Handle database queries for RSS Feed.
 *
 * @package flowmattic
 * @since 4.1.0
 */

if ( ! class_exists( 'FlowMattic_Database_Rss_Feed' ) ) {
	/**
	 * Handle database queries for RSS Feeds.
	 *
	 * @since 4.1.0
	 */
	class FlowMattic_Database_Rss_Feed {

		/**
		 * The table name.
		 *
		 * @access protected
		 * @since 4.1.0
		 * @var string
		 */
		protected $table_name = 'flowmattic_rss_feed';

		/**
		 * The Constructor.
		 *
		 * @since 4.1.0
		 * @access public
		 */
		public function __construct() {
		}

		/**
		 * Insert RSS Feed to database.
		 *
		 * @since 4.1.0
		 * @access public
		 * @param array $args The arguments.
		 * @return integer|boolean The last insert id or false if query failed.
		 */
		public function insert( $args ) {
			global $wpdb;

			// Check if feed slug is not provided.
			if ( isset( $args['feed_slug'] ) || '' !== trim( $args['feed_slug'] ) ) {
				$status = $wpdb->insert(
					$wpdb->prefix . $this->table_name,
					array(
						'feed_slug'      => esc_attr( $args['feed_slug'] ),
						'feed_data'      => wp_json_encode( $args['feed_data'] ),
						'feed_items'     => wp_json_encode( $args['feed_items'] ),
						'item_published' => date_i18n( 'Y-m-d H:i:s' ),
					)
				);

				return $wpdb->insert_id;
			}
		}

		/**
		 * Delete the connect from database.
		 *
		 * @since 4.1.0
		 * @access public
		 * @param array $args The arguments.
		 * @return void
		 */
		public function delete( $args ) {
			global $wpdb;

			// Check if feed id is provided, else skip.
			if ( isset( $args['feed_id'] ) ) {
				$wpdb->delete(
					$wpdb->prefix . $this->table_name,
					array(
						'id' => esc_attr( $args['feed_id'] ),
					)
				);
			}
		}

		/**
		 * Get the RSS Feeds from database.
		 *
		 * @since 4.1.0
		 * @access public
		 * @param array $args The arguments.
		 * @return array Feed data from database.
		 */
		public function get( $args ) {
			global $wpdb;

			// Check if Feed Slug is provided, else skip.
			if ( isset( $args['feed_slug'] ) ) {
				$feed_items = $wpdb->get_results(
					$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE `feed_slug` = %s ORDER BY id DESC', $args['feed_slug'] )
				);
			}

			return $feed_items;
		}

		/**
		 * Get all RSS Feeds from database.
		 *
		 * @since 4.1.0
		 * @access public
		 * @return object Connect data from database.
		 */
		public function get_all() {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 )
			);
		}

		/**
		 * Get the RSS Feed slugs from database.
		 *
		 * @since 4.1.0
		 * @access public
		 * @return Object|Boolean Feed slugs from database.
		 */
		public function get_slugs() {
			global $wpdb;

			$feed_items = $wpdb->get_results(
				$wpdb->prepare( 'SELECT GROUP_CONCAT( DISTINCT `feed_slug` SEPARATOR ",") as `feed_slugs` FROM `' . $wpdb->prefix . $this->table_name . '` WHERE %s', 1 )
			);

			$feed_items_db = ( ! empty( $feed_items ) && is_array( $feed_items ) ) ? $feed_items[0] : false;

			if ( ! empty( $feed_items_db ) && is_object( $feed_items_db ) ) {
				// Explode the feed slugs.
				$feed_items_db->feed_slugs = isset( $feed_items_db->feed_slugs ) ? explode( ',', $feed_items_db->feed_slugs ) : array();

				return $feed_items_db;
			}

			return $feed_items_db;
		}
	}
}
