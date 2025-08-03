<?php
namespace BBPCorePro\Admin;

/**
 * Class Admin_Actions
 * @package BBPCorePro\Admin
 */
class Notifications {
	function __construct() {
        // post type for notification
        add_action( 'init', [ $this, 'bbpc_notification_post_type' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_notification_meta_box' ] );
        
	}	

	/**
	 * BBP Core Notification
	 */
	public function bbpc_notification_post_type() {
        $labels = array(
            'name'                  => _x( 'Notifications', 'Post Type General Name', 'bbp-core-pro' ),
            'singular_name'         => _x( 'Notification', 'Post Type Singular Name', 'bbp-core-pro' ),
            'menu_name'             => __( 'Notifications', 'bbp-core-pro' ),
            'name_admin_bar'        => __( 'Notifications', 'bbp-core-pro' ),
            'archives'              => __( 'Item Archives', 'bbp-core-pro' ),
            'attributes'            => __( 'Item Attributes', 'bbp-core-pro' ),
            'parent_item_colon'     => __( 'Parent Item:', 'bbp-core-pro' ),
            'all_items'             => __( 'All Items', 'bbp-core-pro' ),
            'add_new_item'          => __( 'Add New Item', 'bbp-core-pro' ),
            'add_new'               => __( 'Add New', 'bbp-core-pro' ),
            'new_item'              => __( 'New Item', 'bbp-core-pro' ),
            'edit_item'             => __( 'Edit Item', 'bbp-core-pro' ),
            'update_item'           => __( 'Update Item', 'bbp-core-pro' )
        );
        register_post_type('bbpc-notification', [
            'public'        => false,
            'labels'        => $labels,
            'supports'      => ['title', 'editor', 'author'],
            'show_ui'       => false
        ]);
    }

    public function add_notification_meta_box() {
        add_meta_box(
            'notification_meta_box',
            'Notification Information',
            function($post) {
            $post_id = get_post_meta($post->ID, 'post_id', true);
                
            $author_id = get_post_meta($post->ID, 'current_post_author', true);
            
            $post_date = get_the_date('F j, Y', $post->ID);

            $forum_id = get_post_meta($post->ID, 'parent_forum_id', true);

            $parent_id = get_post_meta($post->ID, 'parent_author', true);
            $parent_author = get_userdata($parent_id);

            $subscriber_ids = get_post_meta($post->ID, 'subscriber_ids', true);    
            $read_status = get_post_meta($post->ID, 'read_by_user_'.get_current_user_id(), true);   

            ?>
            <!-- input fields -->
            <div class="bbpc-notification-info">
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-id">Created Post ID</label>
                    <input type="text" id="bbpc-notification-id" value="<?php echo $post_id; ?>" readonly>
                </div>
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-author">Post Submitted Author</label>
                    <input type="text" id="bbpc-notification-author" value="<?php echo $author_id; ?>" readonly>
                </div>
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-date">Date</label>
                    <input type="text" id="bbpc-notification-date" value="<?php echo $post_date; ?>" readonly>
                </div>
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-forum">Parent ID</label>
                    <input type="text" value="<?php echo $forum_id; ?>" readonly>
                </div>
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-forum">Submission Type</label>
                    <!-- select field value forum or topic -->
                    <select name="bbpc-notification-type" id="bbpc-notification-type">
                        <option value="forum" <?php selected(get_post_meta($post->ID, 'submission_type', true), 'forum'); ?>>Forum</option>
                        <option value="topic" <?php selected(get_post_meta($post->ID, 'submission_type', true), 'topic'); ?>>Topic</option>
                        <option value="reply" <?php selected(get_post_meta($post->ID, 'submission_type', true), 'reply'); ?>>Reply</option>
                        <option value="resolved" <?php selected(get_post_meta($post->ID, 'submission_type', true), 'resolved'); ?>>Resolved</option>
                        <option value="re-opened" <?php selected(get_post_meta($post->ID, 'submission_type', true), 're-opened'); ?>>Re-opened</option>
                    </select>
                </div>
                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-subscribers">Subscriber IDs</label>
                    <input type="text" value="<?php echo $subscriber_ids; ?>" readonly>
                </div>

                <div class="bbpc-notification-info-item">
                    <label for="bbpc-notification-read">Read status</label>
                    <input type="text" value="<?php echo $read_status; ?>" readonly>
                </div>
            </div>
        <?php
        }, 'bbpc-notification', 'normal', 'high' );
    }
	// Notification ended
}