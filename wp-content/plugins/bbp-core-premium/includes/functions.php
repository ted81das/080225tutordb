<?php 

/**
 * Same topic vote counter ajax [Single topic]
*/
function bbpc_same_topic_voting(){
	$post_id 	= $_POST['post_id'];
	$vote_count = $_POST['bbpc_same_topic_voting'];
	$vote_count = (int) $vote_count;

	$prev_value = get_post_meta( $post_id, '_bbpc_same_topic_voting_count', true );
	$prev_value = (int) $prev_value;
	$vote_count = $vote_count + $prev_value;
	update_post_meta( $post_id, '_bbpc_same_topic_voting_count', $vote_count );

	wp_die();
}
add_action( 'wp_ajax_bbpc_same_topic_voting', 'bbpc_same_topic_voting' );
add_action( 'wp_ajax_nopriv_bbpc_same_topic_voting', 'bbpc_same_topic_voting' );

/**
 * Mini profile content
 * 
*/
 function bbpc_mini_profile_content(){
	if ( class_exists( 'bbPress' ) && is_user_logged_in() && function_exists('bbpc_is_premium') ) {
        
        $last_post_time = bbp_get_user_last_posted( bbp_get_current_user_id() );
        $last_activity  = ! empty ( $last_post_time ) ? bbp_get_time_since( $last_post_time, false, true ) : esc_html__( 'No activity', 'bbp-core-pro');

		return '<div class="bbpc-mini-profile-wrapper" style="display:none">
		<div class="bbpc-mini-profile-head">
			<div class="bbpc-mini-profile-avatar">
				<img src="'.get_avatar_url( get_current_user_id(), 32 ).'" alt="avatar">
			</div>
			<div class="bbpc-mini-profile-name">
				<a href="'.bbp_get_user_profile_url( bbp_get_current_user_id() ).'">'.get_the_author_meta( 'display_name', get_current_user_id() ).'</a>
				<p>'.bbp_get_user_display_role( bbp_get_current_user_id() ).'</p>
			</div>
		</div>
		<div class="bbpc-mini-middle"> 
			<p>
			<span>'. esc_html__( 'Last activity:', 'bbp-core-pro' ) .'</span><span> '.  $last_activity .'</span></p>
			<p><span>'. esc_html__( 'Topics Started:', 'bbp-core-pro' ) .'</span> <span>'.bbp_get_user_topic_count( bbp_get_current_user_id() ).'</span></p>
			<p><span>'. esc_html__( 'Replies Created:', 'bbp-core-pro' ) .'</span> <span>'.bbp_get_user_reply_count( bbp_get_current_user_id() ).'</span></p>
		</div>
		<div class="bbpc-min-profile-links">
			<ul>
				<li>
					<a href='.bbp_get_user_topics_created_url(bbp_get_current_user_id()).'>'. esc_html__( 'Topics', 'bbp-core-pro' ) .'</a>
				</li>
				<li>
					<a href='.bbp_get_user_replies_created_url(bbp_get_current_user_id()).'>'. esc_html__( 'Replies', 'bbp-core-pro' ) .'</a>
				</li>
				<li>
					<a href='.bbp_get_user_engagements_url(bbp_get_current_user_id()).'>'. esc_html__( 'Engagements', 'bbp-core-pro' ) .'</a>
				</li>
				<li>
					<a href='.bbp_get_favorites_permalink(bbp_get_current_user_id()).'>'. esc_html__( 'Favorites', 'bbp-core-pro' ) .'</a>
				</li>
				<li>
					<a href='.bbp_get_subscriptions_permalink(bbp_get_current_user_id()).'>'. esc_html__( 'Subscriptions', 'bbp-core-pro' ) .'</a>
				</li>
				<li>
					<a href='.bbp_get_user_profile_edit_url(bbp_get_current_user_id()).'>'. esc_html__( 'Edit', 'bbp-core-pro' ) .'</a>
				</li>
			</ul>
			<ul class="bbpc-user-logout">
				<li>
					<a href='.wp_logout_url( home_url() ).'>'. esc_html__( 'Logout', 'bbp-core-pro' ) .'</a>
				</li>
			</ul>
		</div>
		</div>';
	}
}

function get_post_ids_from_query($args) {
    $post_ids = array();

    $query = new WP_Query($args);

    while ($query->have_posts()) {
        $query->the_post();
        $post_ids[] = get_the_ID();
    }

    wp_reset_postdata();

    return array_filter($post_ids);
}

/**
 * Create notification with topic solve
 */
function create_notification_by_solve(){

    // Remove existing re-opened topic
    $args = array(
        'post_type'         => 'bbpc-notification', // Replace with your post type
        'posts_per_page'    => -1,
        'meta_query' => array(
            'relation'      => 'AND',
            array(
                'key'       => 'post_id', // Replace with your second custom meta key
                'value'     => $_POST['post_id'] ?? '', // Replace with the value you're checking for
                'compare'   => '='
            ),
            array(
                'key'       => 'submission_type', // Replace with your second custom meta key
                'value'     => 're-opened', // Replace with the value you're checking for
                'compare'   => '='
            )
        )
    );

    $remove_posts = new WP_Query($args);

    if ($remove_posts->have_posts()) {
        while ($remove_posts->have_posts()) {
            $remove_posts->the_post();
            wp_delete_post(get_the_ID(), true); // Delete posts, 'true' skips the trash
        }
        wp_reset_postdata();
    }
    
    // Insert solved topic
    $current_user       = get_userdata( get_current_user_id() );
    $user_display_name  = $current_user->display_name;
    $user_url           = get_author_posts_url( $current_user->ID );

    $post_id            = $_POST['post_id'] ?? '';        
    $topic              = get_post( $post_id );

    $topic_title        = $topic->post_title;     
    $author_id          = $topic->post_author ?? '';
    
    $topic_id_meta      = get_post_meta($post_id, '_bbp_topic_id', true);    
    $subscriber_ids     = bbpc_subscriber_list($topic_id_meta);
    
    $post_content       = "<a href='$user_url'><b>$user_display_name</b></a> <a href=".get_permalink($post_id).">marked your topic <b> $topic_title</b> as solved</a>";

    $post_data = array(
        'post_title'   => $post_content,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'bbpc-notification',
        'post_author'  => $author_id
    );
    
    $solved_topic_id = wp_insert_post( $post_data );

    if ( $solved_topic_id ) {
        update_post_meta( $solved_topic_id, 'current_post_author', $current_user->ID );
        update_post_meta( $solved_topic_id, 'post_id', $post_id );
        update_post_meta( $solved_topic_id, 'subscriber_ids', $subscriber_ids );
        update_post_meta( $solved_topic_id, 'submission_type', 'resolved' );
    }
    wp_die();
        
}
add_action('wp_ajax_create_notification_by_solve', 'create_notification_by_solve');
add_action('wp_ajax_nopriv_create_notification_by_solve', 'create_notification_by_solve');

/**
 * Create notification with topic unsolved
 */
function create_notification_by_unsolved(){

    // Remove if exists solved topic
    $args = array(
        'post_type'         => 'bbpc-notification', // Replace with your post type
        'posts_per_page'    => -1,
        'meta_query' => array(
            'relation'      => 'AND',
            array(
                'key'       => 'post_id', // Replace with your second custom meta key
                'value'     => $_POST['post_id'] ?? '', // Replace with the value you're checking for
                'compare'   => '='
            ),
            array(
                'key'       => 'submission_type', // Replace with your second custom meta key
                'value'     => 'resolved', // Replace with the value you're checking for
                'compare'   => '='
            )
        )
    );

    $remove_posts = new WP_Query($args);

    if ($remove_posts->have_posts()) {
        while ($remove_posts->have_posts()) {
            $remove_posts->the_post();
            wp_delete_post(get_the_ID(), true); // Delete posts, 'true' skips the trash
        }
        wp_reset_postdata();
    }
    
    // Insert re-opened topic 
    $current_user       = get_userdata( get_current_user_id() );
    $user_display_name  = $current_user->display_name;
    $user_url           = get_author_posts_url( $current_user->ID );

    $post_id            = $_POST['post_id'] ?? '';        
    $topic              = get_post( $post_id );

    $topic_title        = $topic->post_title;     
    $author_id          = $topic->post_author ?? '';
    
    $topic_id_meta      = get_post_meta($post_id, '_bbp_topic_id', true);    
    $subscriber_ids     = bbpc_subscriber_list($topic_id_meta);
    
    $post_content       = "<a href='$user_url'><b>$user_display_name</b></a> <a href=".get_permalink($post_id).">re-opened your topic <b> $topic_title</b></a>";

    $post_data = array(
        'post_title'   => $post_content,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'bbpc-notification',
        'post_author'  => $author_id
    );
    
    $solved_topic_id = wp_insert_post( $post_data );

    if ( $solved_topic_id ) {
        update_post_meta( $solved_topic_id, 'current_post_author', $current_user->ID );
        update_post_meta( $solved_topic_id, 'post_id', $post_id );
        update_post_meta( $solved_topic_id, 'subscriber_ids', $subscriber_ids );
        update_post_meta( $solved_topic_id, 'submission_type', 're-opened' );
    }
    wp_die();
}
add_action('wp_ajax_create_notification_by_unsolved', 'create_notification_by_unsolved');
add_action('wp_ajax_nopriv_create_notification_by_unsolved', 'create_notification_by_unsolved');

/**
 * Get the list of subscribers for a post
 */
function bbpc_subscriber_list($post_id) {
    $subscribers = bbp_get_users_for_object($post_id, '_bbp_subscription');
    // get parent author
    $parent_author = get_post_field('post_author', $post_id);

    $subscriber_ids = implode(',', $subscribers);

    return $subscriber_ids;
}

/**
 * Get all the registered menus
 */
function bbpc_get_registered_nav_menus() {
    $menus          = get_registered_nav_menus();
    $menu_locations = [];
    $empty          = ['' => 'Select Menu Location'];
    foreach ( $menus as $location => $description ) {
        $menu_locations[$location] = $description;
    }
    return $empty + $menu_locations;
}

/**
 * Notification
 */
function bbpc_notification_lists($post_id){
    $post_type 	    = get_post_meta($post_id, 'submission_type', true);
    $content_text 	= wp_trim_words(get_the_content($post_id), 10, '...');
    $title_text 	= wp_trim_words(get_the_title($post_id), 10, '...');
    $title 			= $title_text ?? $content_text;

    $mentioned_text = '';
    $type_text      = '';
    
    if ( $post_type == 'forum' ){
        $type_text 	= 'forum';
        $mentioned_text = 'subscribed';
    } elseif ($post_type == 'topic') {
        $type_text 	= 'forum';
        $mentioned_text = ' commented';
    } elseif ($post_type == 'reply') {
        $type_text 	= 'topic';
        $mentioned_text = ' replied';
        $title 		= $content_text;
    }

    $post_author_id = get_post_field('post_author', $post_id);
    $author 		= get_the_author_meta('display_name', $post_author_id);

    if ( ! empty($anonymous) ) {
        $post_author_id = 0;
    } else {
        $post_author_id = $post_author_id;
    }

    // get the post date
    $post_date = get_post($post_id);
    $post_date = $post_date->post_date;

    $parent_id = wp_get_post_parent_id($post_id);
    $parent_id = $post_author_id . '-' . $post_date . '-' . $parent_id;
    $parent_id = str_replace([' ', ':'], '-', $parent_id);

    $read_by_user 	= get_post_meta($post_id, 'read_by_user_' . get_current_user_id(), true);
    $read_class 	= '';
    if ($read_by_user != 'read') {
        $read_class = 'bbpc-notify-unread';
    }
    ?>
    <div class="bbpc-notification-item bbpc-notification-link <?php echo esc_attr($read_class); ?>" onclick="location.href='<?php echo get_permalink(get_post_meta($post_id,'post_id', true)); ?>';" data-post-id="<?php echo $post_id; ?>">
        <div class="bbpc-notify-author">
            <?php 
            $anonymous = get_post_meta(get_post_meta($post_id, 'post_id', true), '_bbp_anonymous_name', true);  
            if ( ! empty ( $anonymous ) ) {
                echo get_avatar($post_id, 24);
            } else {
                echo get_avatar(get_post_meta($post_id, 'current_post_author', true), 24);
            }            
            ?>
        </div>

        <div class="bbpc-notify-content">
            <h5><?php the_title(); ?></h5>
            <span> <?php echo bbp_get_time_since(get_post($post_id)->post_date, false, true); ?></span>
        </div>

        <?php
        if ($read_by_user != 'read') :
            ?>
            <span class="bbpc-notify-read"></span>
        <?php
        endif;
        ?>
    </div>
    <?php
}

/**
 * Get all the notification item ids
 */
function bbpc_notification_ids() {

    $exclude_posts_args = array(
        'post_type'      => 'bbpc-notification',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'current_post_author',  // Replace with your custom meta key
                'value'   => get_current_user_id(), // Current user's ID
                'compare' => '='
            )
        )
    );

    $author_posts_args = array(
        'post_type'      => 'bbpc-notification',
        'posts_per_page' => -1,
        'author'         => get_current_user_id()
    );

    $subscriber_posts_args = array(
        'post_type'      => 'bbpc-notification',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'subscriber_ids', // Replace with the actual meta key where subscriber IDs are stored
                'value'   => get_current_user_id(), // Check if the current user's ID is in the subscribers list
                'compare' => 'like'
            )
        )
    );

    $excluded_posts   = get_post_ids_from_query($exclude_posts_args);
    $author_ids       = get_post_ids_from_query($author_posts_args);
    $subscriber_ids   = get_post_ids_from_query($subscriber_posts_args);

    $myIds            = array_merge($author_ids, $subscriber_ids);
    $filtered_ids     = array_diff($myIds, $excluded_posts);

    return $filtered_ids;
}

/**
 * Get the list of subscribers for a post
 */
function bbpc_notification_render() {     
    $opt                = get_option('bbp_core_settings');
	$head_opt           = $opt['bbpc_notification_head_opt'] ?? '';
	$head_opt_text      = ! empty( $opt['bbpc_notification_head_text'] ) ? $opt['bbpc_notification_head_text'] : __('Alerts', 'bbp-core-pro');

	$sticky_head        = $opt['bbpc_notification_head_sticky'] ?? '';
	$sticky_head        = $sticky_head == true ? 'bbpc-sticky-head' : '';

    $notification_ids   = bbpc_notification_ids();
    $notification_count = count( $notification_ids );
    $per_page           = 3;

    ob_start();
    echo '<div class="bbpc-notification-wrap" data-offset="' . esc_attr($per_page) . '" data-total="' . esc_attr($notification_count) . '" style="display:none">';
    
    if ( $head_opt == true ) {
        echo '<div class="bbpc-notification-header ' . esc_attr($sticky_head) . '">' . esc_html($head_opt_text) . '</div>';   
    }

    if ( ! empty( $notification_ids ) ) :
        $query = new WP_Query(array(
            'post_type'     => 'bbpc-notification',
            'post__in'      => $notification_ids,        
            'posts_per_page'=> $per_page,
            'orderby'       => 'date',
            'order'         => 'DESC'
        ));

        while($query->have_posts()) : $query->the_post();
            bbpc_notification_lists(get_the_ID());
        endwhile;
        wp_reset_postdata();
        ?>
        <div class="bbpc-notification-footer">
            <button class="bbpc-load-more-notifications button">
                <?php esc_html_e('See previous notifications', 'bbpc-core-pro'); ?>
            </button>
        </div>
        <?php
    else :
        echo '<div class="bbpc-no-new-notification">'.esc_html__( 'You have no recent notification', 'bbpc-core-pro' ).'</div>';
    endif;

    echo '</div>';
    return ob_get_clean();
}

add_action('wp_ajax_bbpc_load_more_notifications', 'bbpc_load_more_notifications_ajax');
add_action('wp_ajax_nopriv_bbpc_load_more_notifications', 'bbpc_load_more_notifications_ajax');

function bbpc_load_more_notifications_ajax() {
    $offset             = intval( $_POST['offset'] ?? '0' );
    $per_page           = 3;
    $notification_ids   = bbpc_notification_ids();

    $query = new WP_Query(array(
        'post_type'      => 'bbpc-notification',
        'post__in'       => $notification_ids,
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'offset'         => $offset,
    ));

    ob_start();
    while ( $query->have_posts() ) : $query->the_post();
        bbpc_notification_lists(get_the_ID());
    endwhile;
    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}

/**
 * Notification unread items counter
 */
function bbpc_notification_unread_count(){
    ob_start();
    if ( ! empty ( bbpc_notification_ids() ) ) {

        $opt 		        = get_option('bbp_core_settings');
        $unread_counter 	= $opt['bbpc_notification_unread_counter'] ?? '';

        if ( $unread_counter == true ) {
            $query = new  WP_Query( array(
                'post_type'     => 'bbpc-notification',
                'post__in'      => bbpc_notification_ids(),        
                'posts_per_page'=> -1,
                'orderby'       => 'date',
                'order'         => 'DESC'
            ) );

            $unread_ids = [];
            while( $query->have_posts() ) : $query->the_post();
            $read_by_user = get_post_meta(get_the_ID(), 'read_by_user_' . get_current_user_id(), true);
            if ( $read_by_user != 'read' ) {
                $unread_ids[] = get_the_ID();
            }
            endwhile;
            
            $count_number = count($unread_ids);
            if ( $count_number > 0 ) {
                echo '<sup>'.esc_html($count_number).'</sup>';
            }
        }
    }
    return ob_get_clean();
}

/**
 * Create notification with subscribe
 */
function create_notification_post() {
    
    $current_user       = get_userdata( get_current_user_id() );
    $user_display_name  = $current_user->display_name;
    $user_url           = get_author_posts_url( $current_user->ID );

    $post_id            = $_POST['post_id'] ?? '';        
    $author_id          = $current_user->ID ?? '';
    
    $topic              = get_post( $post_id );
    $topic_title        = $topic->post_title;

    $forum_id_meta      = get_post_meta($post_id, '_bbp_forum_id', true);
    $topic_id_meta      = get_post_meta($post_id, '_bbp_topic_id', true);
    
    if ( get_post_type($post_id) == 'topic' ){
        $submission_type    = 'topic';
        $subscriber_ids     = bbpc_subscriber_list($topic_id_meta);
        $forum_id           = bbp_get_topic_forum_id($post_id);

    } elseif ( get_post_type($post_id) == 'forum' ){
        $submission_type    = 'forum';
        $subscriber_ids     = bbpc_subscriber_list($forum_id_meta);
        $forum_id           = $post_id;
    }
    
    $post_content       = "<a href='$user_url'><b>$user_display_name</b></a> <a href=".get_permalink($post_id).">subscribed your <b>$submission_type $topic_title</b></a>";

    $post_author        = get_post($post_id); 
    $post_author        = $post_author->post_author;

    // Create a new post in your custom post type
    $post_data = array(
        'post_title'   => $post_content,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'bbpc-notification',
        'post_author'  => $post_author
    );
    
    $subscribed_post_id = wp_insert_post( $post_data );

    if ( $subscribed_post_id ) {
        update_post_meta( $subscribed_post_id, 'current_post_author', $author_id );
        update_post_meta( $subscribed_post_id, 'post_id', $post_id );
        update_post_meta( $subscribed_post_id, 'parent_forum_id', $forum_id );
        update_post_meta( $subscribed_post_id, 'submission_type', $submission_type );
        update_post_meta( $subscribed_post_id, 'subscriber_ids', $subscriber_ids );
    }
        
}
add_action('wp_ajax_create_notification_post', 'create_notification_post');
add_action('wp_ajax_nopriv_create_notification_post', 'create_notification_post');

/**
 * Remove notification with unsubscribe
 */
function remove_notification_post(){
       
    $args = array(
        'post_type'         => 'bbpc-notification', // Replace with your post type
        'posts_per_page'    => -1,
        'meta_query' => array(
            'relation'      => 'AND',
            array(
                'key'       => 'current_post_author', // Replace with your first custom meta key
                'value'     => get_current_user_id(), // Replace with the value you're checking for
                'compare'   => '='
            ),
            array(
                'key'       => 'post_id', // Replace with your second custom meta key
                'value'     => $_POST['post_id'] ?? '', // Replace with the value you're checking for
                'compare'   => '='
            ),
            array(
                'key'       => 'submission_type', // Replace with your second custom meta key
                'value'     => 'resolved', // Replace with the value you're checking for
                'compare'   => '!='
            )
        )
    );

    $remove_posts = new WP_Query($args);

    if ($remove_posts->have_posts()) {
        while ($remove_posts->have_posts()) {
            $remove_posts->the_post();
            wp_delete_post(get_the_ID(), true); // Delete posts, 'true' skips the trash
        }
        wp_reset_postdata();
    }
    wp_die();
}
add_action('wp_ajax_remove_notification_post', 'remove_notification_post');
add_action('wp_ajax_nopriv_remove_notification_post', 'remove_notification_post');


/**
 * Mark a post as read
 */
add_action('wp_ajax_bbpc_mark_post_as_read', 'bbpc_mark_post_as_read');
add_action('wp_ajax_nopriv_bbpc_mark_post_as_read', 'bbpc_mark_post_as_read');

function bbpc_mark_post_as_read() {
    $post_id = $_POST['post_id'];
    $user_id = get_current_user_id();

    // Update the post's "read" status in post_meta
    update_post_meta($post_id, 'read_by_user_' . $user_id, 'read');
    
    // Return a response if needed
    echo 'Post marked as read';
    wp_die();
}