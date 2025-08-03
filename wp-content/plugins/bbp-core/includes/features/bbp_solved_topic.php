<?php
namespace features;

class bbp_solved_topic {

	/**
	 * The capability required to view private posts.
	 *
	 * @since 1.0
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
		add_action( 'plugins_loaded', [ $this, 'filter_capability' ] );

		// show the checkboxes
		add_action( 'bbp_theme_before_reply_form_submit_wrapper', [ $this, 'reply_checkbox' ] );
		add_action( 'bbp_theme_before_topic_form_submit_wrapper', [ $this, 'topic_checkbox' ] );

		// save the reply state
		add_action( 'bbp_new_reply', [ $this, 'update_reply' ], 0, 6 );
		add_action( 'bbp_edit_reply', [ $this, 'update_reply' ], 0, 6 );

		// save the topic state
		add_action( 'bbp_new_topic', [ $this, 'update_topic' ], 0, 6 );
		add_action( 'bbp_edit_topic', [ $this, 'update_topic' ], 0, 6 );

		// reply content filter
		add_filter( 'bbp_get_reply_excerpt', [ $this, 'reply_content_filter' ], 999, 2 );
		add_filter( 'bbp_get_reply_content', [ $this, 'reply_content_filter' ], 999, 2 );

		add_filter( 'bbp_theme_before_topic_title', [ $this, 'solved_badge' ], 999, 2 );
		add_filter( 'bbp_topic_admin_links', [ $this, 'add_topic_admin_link' ], 10, 2 );

		// add a class name indicating the read status
		add_filter( 'post_class', [ $this, 'reply_post_class' ] );
	}

	/**
	 * Admin links
	 */
	public function add_topic_admin_link( $links, $topic_id ) {

		$topic_author = bbp_get_topic_author_id( bbp_get_topic_id() );
		if ( ( current_user_can( $this->capability ) ) || ( $topic_author == get_current_user_id() ) ) {
			if ( isset( $_GET['unsolve_topic'] ) ) {
				$this->unsolve_topic( bbp_get_topic_id() );
				$links['solved'] = '<a href="' . bbp_get_topic_permalink() . '?solve_topic">' . esc_html__( 'Mark as solved', 'ama-core' ) . '</a>';
			} elseif ( isset( $_GET['solve_topic'] ) ) {
				$this->solve_topic( bbp_get_topic_id() );
				$links['unsolved'] = '<a href="' . bbp_get_topic_permalink() . '?unsolve_topic">' . esc_html__( 'Mark as unsolved', 'ama-core' ) . '</a>';
			} else {
				if ( $this->is_solved( bbp_get_topic_id() ) ) {
					$links['unsolved'] = '<a href="' . bbp_get_topic_permalink() . '?unsolve_topic">' . esc_html__( 'Mark as unsolved', 'ama-core' ) . '</a>';
				} else {
					$links['solved'] = '<a href="' . bbp_get_topic_permalink() . '?solve_topic">' . esc_html__( 'Mark as solved', 'ama-core' ) . '</a>';
				}
			}
		}
		return $links;
	}

	public function solve_topic( $topic_id = 0 ) {
		$topic_author = bbp_get_topic_author_id( $topic_id );
		if ( ( current_user_can( $this->capability ) ) || ( $topic_author == get_current_user_id() ) ) {
			update_post_meta( $topic_id, '_bbp_topic_is_solved', '1' );
			$redirect = remove_query_arg( [ 'solve_topic' ] );
			wp_safe_redirect( $redirect );
		} else {
			wp_die( esc_html__( 'You do not have the permission to do that!', 'ama-core' ) );
		}

	}

	public function unsolve_topic( $topic_id = 0 ) {
		$topic_author = bbp_get_topic_author_id( $topic_id );
		if ( ( current_user_can( $this->capability ) ) || ( $topic_author == get_current_user_id() ) ) {
			delete_post_meta( $topic_id, '_bbp_topic_is_solved' );
			$redirect = remove_query_arg( [ 'unsolve_topic' ] );
			wp_safe_redirect( $redirect );
		} else {
			wp_die( esc_html__( 'You do not have the permission to do that!', 'ama-core' ) );
		}
	}

	/**
	 * Called during the plugins_loaded action to filter the capability
	 * required to view solved topics.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function filter_capability() {
		$this->capability = apply_filters( 'bbp_solved_topic_capability', $this->capability );
	}

	/**
	 * Solved topic badge
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function solved_badge() {
		$topic_id = bbp_get_topic_id();
		if ( $this->is_solved( $topic_id ) ) {
			echo '<i class="fa fa-check-circle text-success"></i>';
		}
	}

	/**
	 * Outputs the reply checkbox
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function reply_checkbox() {
		$has_best_answer = $this->has_best_answer( bbp_get_topic_id() );
		$is_best_answer  = $this->is_best_answer( bbp_get_reply_id() );
		if ( bbp_is_reply_edit() && ( current_user_can( $this->capability ) ) ) {
			if ( ( ! $has_best_answer ) || ( $is_best_answer ) ) {
				?>
				<p>
					<input name="bbp_best_answer" id="bbp_best_answer" type="checkbox"<?php checked( '1', $is_best_answer ); ?> value="1" tabindex="<?php bbp_tab_index(); ?>" />
					<label for="bbp_best_answer"><?php esc_html_e( 'Set this reply as Best Answer.', 'ama-core' ); ?></label>
				</p>
				<?php
			}
		}
	}

	/**
	 * Outputs the topic checkbox
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function topic_checkbox() {
		$topic_author = bbp_get_topic_author_id( bbp_get_topic_id() );
		if ( bbp_is_topic_edit() && ( ( current_user_can( $this->capability ) ) || ( $topic_author == get_current_user_id() ) ) ) {
			?>
			<p>
				<input name="bbp_solved_topic" id="bbp_solved_topic" type="checkbox"<?php checked( '1', $this->is_solved( bbp_get_topic_id() ) ); ?> value="1" tabindex="<?php bbp_tab_index(); ?>" />
				<label for="bbp_solved_topic"><?php esc_html_e( 'Set this post as solved.', 'ama-core' ); ?></label>
			</p>
			<?php
		}

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

		if ( isset( $_POST['bbp_best_answer'] ) ) {
			$get_topic_meta = get_post_meta( $topic_id, '_bbp_topic_has_best_answer', true );
			if ( ! $get_topic_meta ) {
				update_post_meta( $topic_id, '_bbp_topic_has_best_answer', '1' );
			}
			update_post_meta( $reply_id, '_bbp_reply_is_best_answer', '1' );
		} else {
			delete_post_meta( $reply_id, '_bbp_reply_is_best_answer' );
			delete_post_meta( $topic_id, '_bbp_topic_has_best_answer' );
		}

	}

	/**
	 * Stores the private state on topic creation and edit
	 *
	 * @since 1.0
	 *
	 * @param $topic_id int The ID of the topic
	 * @param $is_edit bool Are we editing a topic?
	 *
	 * @return void
	 */
	public function update_topic( $topic_id = 0, $is_edit = false ) {

		if ( isset( $_POST['bbp_solved_topic'] ) ) {
			update_post_meta( $topic_id, '_bbp_topic_is_solved', '1' );
		} else {
			delete_post_meta( $topic_id, '_bbp_topic_is_solved' );
		}

	}


	/**
	 * Determines if a reply is marked as solved
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 *
	 * @return bool
	 */
	public function is_best_answer( $reply_id = 0 ) {

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
			$retval = get_post_meta( $reply_id, '_bbp_reply_is_best_answer', true );
		}

		return (bool) apply_filters( 'bbp_reply_is_best_answer', (bool) $retval, $reply_id );
	}

	/**
	 * Determines if a reply is marked as solved
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 *
	 * @return bool
	 */
	public function is_solved( $topic_id = 0 ) {

		$retval = false;

		// Checking a specific reply id.
		if ( ! empty( $topic_id ) ) {
			$topic    = bbp_get_topic( $topic_id );
			$topic_id = ! empty( $topic ) ? $topic->ID : 0;

		// Using the global reply id.
		} elseif ( bbp_get_topic_id() ) {
			$topic_id = bbp_get_topic_id();

		// Use the current post id.
		} elseif ( ! bbp_get_topic_id() ) {
			$topic_id = get_the_ID();
		}

		if ( ! empty( $topic_id ) ) {
			$retval = get_post_meta( $topic_id, '_bbp_topic_is_solved', true );
		}

		return (bool) apply_filters( 'bbp_topic_is_solved', (bool) $retval, $topic_id );
	}

	/**
	 * Determines if a reply is marked as solved
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 *
	 * @return bool
	 */
	public function has_best_answer( $topic_id = 0 ) {
		
		// if bbpress is not active, return the classes

		if ( ! function_exists( 'bbp_get_topic_id' ) ) {
			return false;
		}

		$retval = false;

		// Checking a specific reply id.
		if ( ! empty( $topic_id ) ) {
			$topic    = bbp_get_topic( $topic_id );
			$topic_id = ! empty( $topic ) ? $topic->ID : 0;

			// Using the global reply id.
		} elseif ( bbp_get_topic_id() ) {
			$topic_id = bbp_get_topic_id();

			// Use the current post id.
		} elseif ( ! bbp_get_topic_id() ) {
			$topic_id = get_the_ID();
		}

		if ( ! empty( $topic_id ) ) {
			$retval = get_post_meta( $topic_id, '_bbp_topic_has_best_answer', true );
		}

		return (bool) apply_filters( 'bbp_topic_has_best_answer', (bool) $retval, $topic_id );
	}


	/**
	 * Reply content filter
	 *
	 * @since 1.0
	 *
	 * @param $content string The content of the reply
	 * @param $reply_id int The ID of the reply
	 *
	 * @return string
	 */
	public function reply_content_filter( $content = '', $reply_id = 0 ) {

		if ( empty( $reply_id ) ) {
			$reply_id = bbp_get_reply_id( $reply_id );
		}

		$topic_id = bbp_get_topic_id();

		if ( $this->is_best_answer( $reply_id ) ) {
			$content = '<div class="solved-topic-bar" id="best-answer"><span class="badge"><i class="icon_check"></i> ' . esc_html__( 'Best Answer', 'ama-core' ) . '</span></div>' . $content;
		}

		return $content;
	}

	/**
	 * Topic content filter
	 *
	 * @since 1.0
	 *
	 * @param $content string The content of the topic
	 * @param $topic_id int The ID of the reply
	 *
	 * @return string
	 */
	public function topic_content_filter( $content = '', $topic_id = 0 ) {

		if ( empty( $topic_id ) ) {
			$topic_id = bbp_get_topic_id( $topic_id );
		}

		$open            = '';
		$close           = '';
		$resolved        = '';
		$has_best_answer = '';

		if ( $this->is_solved( $topic_id ) || $this->has_best_answer( $topic_id ) ) {
			$open  = '<div class="solved-topic-bar">';
			$close = '</div>';
		}

		if ( $this->is_solved( $topic_id ) ) {
			$resolved = '<span class="accepted-ans-mark"><i class="icon_check_alt"></i> ' . esc_html__( 'Resolved', 'ama-core' ) . '</span>';
		}

		$content = $content . $open . $resolved . $has_best_answer . $close;

		return $content;
	}

	/**
	 * Adds a new class to replies that are marked as solved
	 *
	 * @since 1.0
	 *
	 * @param $classes array An array of current class names
	 *
	 * @return bool
	 */
	public function reply_post_class( $classes ) {

		// if bbpress is not active, return the classes
		if ( ! function_exists( 'bbp_get_reply_id' ) ) {
			return $classes;
		}

		$reply_id = bbp_get_reply_id();

		// Only apply the class to replies.
		if ( bbp_get_reply_post_type() != get_post_type( $reply_id ) ) {
			return $classes;
		}

		if ( $this->is_best_answer( $reply_id ) ) {
			$classes[] = 'best-answer';
		}

		return $classes;
	}
}

// Instantiate the class.
$GLOBALS['bbp_solved_topic'] = new bbp_solved_topic();

function bbp_solved_topic(): bbp_solved_topic {
	return new bbp_solved_topic();
}