<?php
namespace features;

class BBP_Private_Replies {

	/**
	 * The capability required to view private posts.
	 *
	 * @since 1.3.3
	 *
	 * @var string $capability
	 */
	public $capability = 'moderate';

	/*
	--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting filters, and administration functions.
	 */
	function __construct() {

		// Allow others to change the capability required to view private posts.
		add_action( 'plugins_loaded', [ $this, 'filter_capability' ] );

		// show the "Private Reply?" checkbox
		add_action( 'bbp_theme_before_reply_form_submit_wrapper', [ $this, 'checkbox' ] );

		// save the private reply state
		add_action( 'bbp_new_reply', [ $this, 'update_reply' ], 0, 6 );
		add_action( 'bbp_edit_reply', [ $this, 'update_reply' ], 0, 6 );

		// hide reply content
		add_filter( 'bbp_get_reply_excerpt', [ $this, 'hide_reply' ], 999, 2 );
		add_filter( 'bbp_get_reply_content', [ $this, 'hide_reply' ], 999, 2 );
		add_filter( 'the_content', [ $this, 'hide_reply' ], 999 );
		add_filter( 'the_excerpt', [ $this, 'hide_reply' ], 999 );

		// prevent private replies from being sent in email subscriptions
		add_filter( 'bbp_subscription_mail_message', [ $this, 'prevent_subscription_email' ], 999999, 3 );

		// add a class name indicating the read status
		add_filter( 'post_class', [ $this, 'reply_post_class' ] );

	} // end constructor

	/**
	 * Called during the plugins_loaded action to filter the capability
	 * required to view private replies.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function filter_capability() {
		$this->capability = apply_filters( 'bbp_private_replies_capability', $this->capability );
	}

	/**
	 * Retrieves the no reply address.
	 *
	 * @since 1.3.3
	 *
	 * @return string
	 */
	public function get_no_reply() {
		return apply_filters( 'bbp_private_replies_no_reply_address', bbp_get_do_not_reply_address() );
	}

	/**
	 * Outputs the "Set as private reply" checkbox
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function checkbox() {

		?>
		<p>

			<input name="bbp_private_reply" id="bbp_private_reply" type="checkbox"<?php checked( '1', $this->is_private( bbp_get_reply_id() ) ); ?> value="1" tabindex="<?php bbp_tab_index(); ?>" />

			<?php if ( bbp_is_reply_edit() && ( get_the_author_meta( 'ID' ) != bbp_get_current_user_id() ) ) : ?>

				<label for="bbp_private_reply"><?php esc_html_e( "Set author's post as private.", 'ama-core' ); ?></label>

			<?php else : ?>

				<label for="bbp_private_reply"><?php esc_html_e( 'Set as private reply', 'ama-core' ); ?></label>

			<?php endif; ?>

		</p>
		<?php

	}


	/**
	 * Stores the private state on reply creation and edit
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 * @param $topic_id int The ID of the topic the reply belongs to
	 * @param $forum_id int The ID of the forum the topic belongs to
	 * @param $anonymous_data bool Are we posting as an anonymous user?
	 * @param $author_id int The ID of user creating the reply, or the ID of the replie's author during edit
	 * @param $is_edit bool Are we editing a reply?
	 *
	 * @return void
	 */
	public function update_reply( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {

		if ( isset( $_POST['bbp_private_reply'] ) ) {
			update_post_meta( $reply_id, '_bbp_reply_is_private', '1' );
		} else {
			delete_post_meta( $reply_id, '_bbp_reply_is_private' );
		}

	}


	/**
	 * Determines if a reply is marked as private
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 *
	 * @return bool
	 */
	public function is_private( $reply_id = 0 ) {

		$retval = false;

		// Checking a specific reply id
		if ( ! empty( $reply_id ) ) {
			$reply    = bbp_get_reply( $reply_id );
			$reply_id = ! empty( $reply ) ? $reply->ID : 0;

			// Using the global reply id
		} elseif ( bbp_get_reply_id() ) {
			$reply_id = bbp_get_reply_id();

			// Use the current post id
		} elseif ( ! bbp_get_reply_id() ) {
			$reply_id = get_the_ID();
		}

		if ( ! empty( $reply_id ) ) {
			$retval = get_post_meta( $reply_id, '_bbp_reply_is_private', true );
		}

		return (bool) apply_filters( 'bbp_reply_is_private', (bool) $retval, $reply_id );
	}


	/**
	 * Hides the reply content for users that do not have permission to view it
	 *
	 * @since 1.0
	 *
	 * @param $content string The content of the reply
	 * @param $reply_id int The ID of the reply
	 *
	 * @return string
	 */
	public function hide_reply( $content = '', $reply_id = 0 ) {

		// if bbPress isn't active, bail
		if ( ! function_exists( 'bbp_get_reply_id' ) ) {
			return $content;
		}
		
		if ( empty( $reply_id ) ) {
			$reply_id = bbp_get_reply_id( $reply_id );
		}

		if ( $this->is_private( $reply_id ) ) {

			$can_view     = false;
			$current_user = is_user_logged_in() ? wp_get_current_user() : false;
			$topic_author = bbp_get_topic_author_id();
			$reply_author = bbp_get_reply_author_id( $reply_id );

			if ( ! empty( $current_user ) && $topic_author === $current_user->ID && user_can( $reply_author, $this->capability ) ) {
				// Let the thread author view replies if the reply author is from a moderator
				$can_view = true;
			}

			if ( ! empty( $current_user ) && $reply_author === $current_user->ID ) {
				// Let the reply author view their own reply
				$can_view = true;
			}

			if ( current_user_can( $this->capability ) ) {
				// Let moderators view all replies
				$can_view = true;
			}

			if ( ! $can_view ) {
				$content = '<div class="alert alert-danger">' . esc_html__( 'This reply has been marked as private.', 'ama-core' ) . '</div>';
			} else {
				$content = '<div class="alert alert-info">' . esc_html__( 'This is a private reply.', 'ama-core' ) . '</div>' . $content;
			}
		}

		return $content;
	}


	/**
	 * Prevents a New Reply notification from being sent if the user doesn't have permission to view it
	 *
	 * @since 1.0
	 *
	 * @param $message string The email message
	 * @param $reply_id int The ID of the reply
	 * @param $topic_id int The ID of the reply's topic
	 *
	 * @return mixed
	 */
	public function prevent_subscription_email( $message, $reply_id, $topic_id ) {

		if ( $this->is_private( $reply_id ) ) {
			$this->subscription_email( $message, $reply_id, $topic_id );
			return false;
		}

		return $message; // message unchanged
	}


	/**
	 * Sends the new reply notification email to moderators on private replies
	 *
	 * @since 1.2
	 *
	 * @param $message string The email message
	 * @param $reply_id int The ID of the reply
	 * @param $topic_id int The ID of the reply's topic
	 *
	 * @return void
	 */
	public function subscription_email( $message, $reply_id, $topic_id ) {

		if ( ! $this->is_private( $reply_id ) ) {

			return false; // reply isn't private so do nothing

		}

		$topic_author      = bbp_get_topic_author_id( $topic_id );
		$reply_author      = bbp_get_reply_author_id( $reply_id );
		$reply_author_name = bbp_get_reply_author_display_name( $reply_id );

		// Strip tags from text and setup mail data
		$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
		$reply_content = strip_tags( bbp_get_reply_content( $reply_id ) );
		$reply_url     = bbp_get_reply_url( $reply_id );
		$blog_name     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$subject = apply_filters( 'bbp_subscription_mail_title', '[' . $blog_name . '] ' . $topic_title, $reply_id, $topic_id );

		// Array to hold BCC's
		$headers = [];

		// Setup the From header
		$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $this->get_no_reply() . '>';

		// Get topic subscribers and bail if empty
		$user_ids = bbp_get_topic_subscribers( $topic_id, true );
		if ( empty( $user_ids ) ) {
			return false;
		}

		// Loop through users
		foreach ( (array) $user_ids as $user_id ) {

			// Don't send notifications to the person who made the post
			if ( ! empty( $reply_author ) && (int) $user_id === (int) $reply_author ) {
				continue;
			}

			$should_notify_op = user_can( $reply_author, $this->capability ) && (int) $topic_author === (int) $user_id;

			if ( user_can( $user_id, $this->capability ) || $should_notify_op ) {

				// Get email address of subscribed user
				$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;

			}
		}

		wp_mail( $this->get_no_reply(), $subject, $message, $headers );
	}


	/**
	 * Adds a new class to replies that are marked as private
	 *
	 * @since 1.0
	 *
	 * @param $classes array An array of current class names
	 *
	 * @return bool
	 */
	public function reply_post_class( $classes ) {

		// if bbPress isn't active, bail
		if ( ! function_exists( 'bbp_get_reply_id' ) ) {
			return $classes;
		}

		$reply_id = bbp_get_reply_id();

		// only apply the class to replies
		if ( bbp_get_reply_post_type() != get_post_type( $reply_id ) ) {
			return $classes;
		}

		if ( $this->is_private( $reply_id ) ) {
			$classes[] = 'bbp-private-reply';
		}

		return $classes;
	}

} // end class

// instantiate our plugin's class.
$GLOBALS['bbp_private_replies'] = new BBP_Private_Replies();
?>
