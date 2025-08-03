<?php
namespace BBPCorePro\Frontend;

class Frontend_Actions {
    public function __construct() {
        add_action( 'bbp_theme_after_topic_content', [ $this, 'same_topic_voting_button' ], 99, 1 );
        add_filter( 'bbp_new_topic_pre_insert', [ $this, 'new_topic_insert' ], 10, 1 );
        add_filter( 'bbp_new_topic_redirect_to', [ $this, 'new_topic_redirect_to' ], 10, 1 );
        add_filter( 'bbp_get_single_forum_description', [ $this, 'get_single_forum_description' ], 10, 1 );
        add_action( 'bbp_theme_before_topic_form_submit_wrapper', [ $this, 'add_anonymous_checkbox' ] );
        add_action( 'wp_insert_post', [ $this, 'anonymous_topic' ], 10, 3 );
        add_action( 'bbp_theme_before_reply_form_submit_wrapper', [ $this, 'add_anonymous_checkboxs' ] );
        add_action( 'wp_insert_post', [ $this, 'anonymous_reply' ] );

        add_filter( 'wp_nav_menu_items', [ $this, 'mini_profile_navigation' ], 10, 2 );
        add_filter( 'wp_nav_menu_items', [ $this, 'notification_navigation' ], 10, 2 );

        add_action( 'bbp_new_topic', [ $this,  'create_notification_post_on_new_topic' ], 10, 4 );
        add_action( 'bbp_new_reply', [ $this,  'create_notification_post_on_new_topic' ], 10, 4 );
        
        add_action( 'bbp_template_after_user_details_menu_items', [ $this, 'notification_menu' ] );
        add_action( 'bbp_template_before_user_wrapper', [ $this, 'notification_content' ] );
    }
   
    /**
     * Same topic vote counter button [Single topic]
    */
    public function same_topic_voting_button() {
        if ( function_exists('bbpc_is_premium') && bbpc_is_premium() && function_exists('bbpc_get_opt') && bbpc_get_opt('same_topic_voting') == true ) {
            
            wp_enqueue_style( 'bbpc-pro-frontend' );
            wp_enqueue_script( 'bbpc-pro-frontend' );

            $opt                        = get_option( 'bbp_core_settings' );
            $reaction_condition         = $opt['reaction_display_condition'] ?? 'always';
            $topic_conditional_count    = 0;

            // Check if the reaction condition is based on reply count
            if ( $reaction_condition === 'has_replies' ) {
                $topic_conditional_count = absint( $opt['reaction_display_condition_count'] ?? 0 );
            }
            $has_reactions = false;
            if ( function_exists('bbpc_get_reply_count') && bbpc_get_reply_count() >= $topic_conditional_count ) { 
                $has_reactions = true;
            }
            
            $voting_options     = bbpc_get_opt('same_topic_settings');
            $voting_label       = $voting_options['same_topic_voting_label'] ?? __( 'I have the same question', 'bbp-core' );
            $voting_success     = $voting_options['same_topic_voting_success'] ?? __( 'Updated vote successfully', 'bbp-core' );
            $voting_count_meta 	= get_post_meta( get_the_ID(), '_bbpc_same_topic_voting_count', true );
            $counter_meta_int	= (int) $voting_count_meta;
            $voting_counter	    = $counter_meta_int < 1 ? 0 : $counter_meta_int;
            ?>
            <div class="<?php if ( $has_reactions == false ) { echo 'absolute'; } ?> bbpc-same-topic-voting-wrap">
                <button class="bbpc-same-topic-btn" data-post-id="<?php echo get_the_ID(); ?>">
                    <?php echo esc_html($voting_label); ?>
                    (<span class="bbpc-same-topic-counter-wrap" data-post-id="<?php echo get_the_ID(); ?>"><?php echo $voting_counter; ?></span>)
                </button>
                <span class="bbpc-same-topic-counter screen-reader-text" data-post-id="<?php echo get_the_ID(); ?>">0</span>
            </div>
            <?php
            
            // Check if topic meets the reply count condition for showing reactions
            if ( $has_reactions == true ) {  
                echo '</div>';
            }
            ?>
            <div class="same-topic-voting-notice">
                <?php echo esc_html($voting_success); ?>
            </div>
        <?php
        }
    }
    
    /**
     * Add mini profile to menu
    */
    public function mini_profile_navigation( $items, $args ){

        if ( class_exists( 'bbPress' ) && is_user_logged_in() && function_exists('bbpc_is_premium') && bbpc_is_premium() && function_exists('bbpc_get_opt') && bbpc_get_opt('bbpc_mini_profile') == true ) {

            wp_enqueue_style( 'bbpc-pro-frontend' );
            wp_enqueue_script( 'bbpc-pro-frontend' );

            // Get the menu ID of the selected location
            $opt 		        = get_option('bbp_core_settings');
            $profile_location 	= $opt['bbpc_profile_location']['location_option'] ?? '';
            $menu_wrapper       = $opt['bbpc_profile_location']['location_selector'] ?? '';
      
            // Check if the menu ID matches the desired location
            if ( ! empty ( $menu_wrapper ) ) {
                ?>
                <script>
                    jQuery(document).ready(function($) {
                        // Check if the menu has the desired class
                        if ($('<?php echo $menu_wrapper; ?>').length) {
                            // Append the new list item after the existing li items
                            $('<?php echo $menu_wrapper; ?>').append('<li class="bbpc-mini-profile"><a href="javascript:void(0);">'+bbpc_localize_script.bbpc_mini_profile_avatar+'</a>'+bbpc_localize_script.bbpc_mini_profile_content+'</li>');
                            // remove all without first item
                            $('<?php echo $menu_wrapper; ?> li.bbpc-mini-profile').not(':first').remove();
                        }
                    });
                </script>
                <?php
            } elseif ( ! empty($profile_location) && $args->theme_location == $profile_location ) {
                $items 	.= '<li class="bbpc-mini-profile"><a href="javascript:void(0);">'.get_avatar( get_current_user_id(), 32 ).'</a>'.bbpc_mini_profile_content().'</li>';
            }            
        }
        return $items;
    }

    // Auto approval off for bbpress topic
    public function new_topic_insert( $topic_data ) {
        if ( class_exists( 'bbPress' ) && function_exists('bbpc_get_opt') && bbpc_get_opt('is_auto_approval_topics') == false && function_exists('bbpc_is_premium') && bbpc_is_premium() ) { 
            $topic_data['post_status'] = 'pending';
        }
        return $topic_data;
    }
    
    /**
     * Redirect to previous page after topic creation
     */
    public function new_topic_redirect_to( $redirect_to ) {
        if ( function_exists('bbpc_get_opt') && bbpc_get_opt('is_auto_approval_topics') == false && function_exists('bbpc_is_premium') ) {
            if ( ! class_exists( 'bbPress' ) ) {
                return $redirect_to;
            }
            if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
                $redirect_to = $_SERVER['HTTP_REFERER'] ?? '';
            }
            if ( !empty($redirect_to) ) {
                $redirect_to = add_query_arg( array('pending' => '1'), $redirect_to );
            }	
        }
        return $redirect_to;
    }

    /**
     * Add notice to forum description
     */
    public function get_single_forum_description( $notices ){
        if ( function_exists('bbpc_get_opt') && bbpc_get_opt('is_auto_approval_topics') == false && function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
            $topic_pending_notice = bbpc_get_opt('topic_pending_notice', __( 'Your topic is awaiting for moderation.', 'bbp-core' ) );
            return ! empty ( $_GET['pending'] ) ? sprintf( __( "%s$topic_pending_notice%s", 'bbp-core' ), '<div class="bbp-template-notice info"><ul><li class="bbp-forum-description">', '</li></ul></div>' ) :  $notices;
        }
        return $notices;
    }

    /**
     * Add Anonymous checkbox to topic form
     */
    public function add_anonymous_checkbox() {
        if ( function_exists('bbpc_get_opt') && bbpc_get_opt('anonymous_topic') && is_user_logged_in() && function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
        echo '<p>';
        echo '<label for="anonymous_topic">';
        echo '<input type="checkbox" id="anonymous_topic" name="_bbp_anonymous_name" value="1" /> ';
        echo bbpc_get_opt('anonymous_topic_label', __( 'Post Anonymously', 'bbp-core' ));
        echo '</label>';
        echo '</p>';
        }
    }

    /**
     * Save Anonymous checkbox value [ Topic ]
     */
    public function anonymous_topic($post_id, $post, $update){
        if ( function_exists('bbpc_get_opt') && bbpc_get_opt('anonymous_topic') && is_user_logged_in() && function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
            if ( ! isset( $_POST['_bbp_anonymous_name'] ) ) {
                return;
            }
            update_post_meta( $post_id, '_bbp_anonymous_name', __( 'Anonymous', 'bbp-core-pro' ) );
        }
    }
        
    /**
     * Add Anonymous checkbox to reply form
     */
    public function add_anonymous_checkboxs() {
        if ( is_user_logged_in() && function_exists('bbpc_get_opt') && bbpc_get_opt('anonymous_reply') && function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
        echo '<p>';
        echo '<label for="anonymous_reply">';
        echo '<input type="checkbox" id="anonymous_reply" name="anonymous_reply" value="1" /> ';
        echo bbpc_get_opt('anonymous_reply_label', __( 'Post Anonymously', 'bbp-core' ));
        echo '</label>';
        echo '</p>';
        }
    }
    
    /**
     * Save Anonymous checkbox value [ Reply ]
    */
    public function anonymous_reply( $post_id ){
        if ( function_exists('bbpc_get_opt') && bbpc_get_opt('anonymous_reply') && is_user_logged_in() && function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
            if ( ! isset( $_POST['anonymous_reply'] ) ) {
                return;
            }
            update_post_meta( $post_id, '_bbp_anonymous_name', 'Anonymous' );
        }
    }
    
    public function notification_navigation( $items, $args ){

        if ( class_exists( 'bbPress' ) && is_user_logged_in() && function_exists('bbpc_is_premium') && bbpc_is_premium() && function_exists('bbpc_get_opt') && bbpc_get_opt('bbpc_notification') == true ) {

            wp_enqueue_style( 'bbpc-pro-frontend' );
            wp_enqueue_script( 'bbpc-pro-frontend' );

            // Get the menu ID of the selected location
            $opt                = get_option('bbp_core_settings');            
            $avatar             = $opt['bbpc_notification_avatar'] ?? '';
            $img_url            = ! empty ( $avatar['url'] ) ? $avatar['url'] : BBPCOREPRO_IMG . '/notification.svg';
            
            $profile_location 	= $opt['bbpc_notification_location']['location_option'] ?? '';
            $menu_wrapper       = $opt['bbpc_notification_location']['location_selector'] ?? '';

            // Check if the menu ID matches the desired location        
            if ( ! empty ( $menu_wrapper ) ) {
                ?>
                <script>
                    jQuery(document).ready(function($) {
                        // Check if the menu has the desired class
                        if ($('<?php echo $menu_wrapper; ?>').length) {
                            // Append the new list item after the existing li items
                            $('<?php echo $menu_wrapper; ?>').append('<li class="bbpc-nav-notification"><a href="javascript:void(0);"><img width="23px" height="23px" src="'+bbpc_localize_script.bbpc_notification_avatar+'">'+bbpc_localize_script.bbpc_notification_unread_count+'</a>'+bbpc_localize_script.bbpc_notification_render+'</li>');
                            // remove all without first item
                            $('<?php echo $menu_wrapper; ?> li.bbpc-nav-notification').not(':first').remove();
                        }
                    });
                </script>
                <?php
            } elseif ( ! empty($profile_location) && $args->theme_location == $profile_location ) {
                $items 	.= '<li class="bbpc-nav-notification"><a href="javascript:void(0);"><img width="23px" height="23px" src="'.$img_url.'">'.bbpc_notification_unread_count().'</a>'.bbpc_notification_render().'</li>';
            }
        }
        return $items;
    }

    /**
     * Create notification with topic and reply creation
     */
    public function create_notification_post_on_new_topic( $topic_id, $forum_id, $anonymous_data, $author_id ) {
        
        $topic              = get_post( $topic_id );
        $topic_content      = $topic->post_content ?? ''; 
        $topic_content      = wp_trim_words( $topic_content, 10, '...' );
        $topic_title        = ! empty ( $topic->post_title ) ? $topic->post_title : $topic_content;
        
        $current_user       = get_userdata( get_current_user_id() );
        $user_display       = $current_user->display_name;        
        $user_url           = get_author_posts_url( $current_user->ID );

        $anonymous          = get_post_meta($topic_id, '_bbp_anonymous_name', true);        
        $user_display_name 	= !empty($anonymous) ? $anonymous : $user_display;
        $user_url 	        = !empty($anonymous) ? 'javascript:void(0)' : $user_url;
        
        $forum_id_meta      = get_post_meta($topic_id, '_bbp_forum_id', true);
        $topic_id_meta      = get_post_meta($topic_id, '_bbp_topic_id', true);

        if ( get_post_type($topic_id) == 'topic' ){
            $submission_type = 'topic';
            $subscriber_ids = bbpc_subscriber_list($forum_id_meta);
            $mentioned_text = ' commented';
            $post_type_text = 'forum';
            $title_text     = get_the_title($forum_id);

        } elseif ( get_post_type($topic_id) == 'reply' ){
            $submission_type = 'reply';
            $subscriber_ids = bbpc_subscriber_list($topic_id_meta);
            $mentioned_text = ' replied';
            $post_type_text = 'topic';
            $title_text     = $topic->post_content ?? $topic_title;

        } else {
            $submission_type = 'forum';
            $subscriber_ids = '';
            $mentioned_text = 'subscribed';
            $post_type_text = 'reply';
            $post_type_text = 'reply';
            $title_text     = '';
        }
        
        $post_content       = "<a href='$user_url'><b>$user_display_name</b></a> <a href=".get_permalink($topic->ID).">$mentioned_text to your $post_type_text <b>$title_text</b></a>";

        //  post author
        $author_id          = get_post_field('post_author', $topic_id);
        $parent_author      = get_post_field('post_author', $forum_id);
        
        $notification_post = array(
            'post_title'    => $post_content,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'bbpc-notification',
            'post_author'   => $parent_author
        );
    
        // Insert the post and get its ID
        $notification_post_id = wp_insert_post( $notification_post );
    
        if ( $notification_post_id ) {
            // Add post meta for the forum that is the parent of this topic
            update_post_meta( $notification_post_id, 'post_id', $topic_id );
            update_post_meta( $notification_post_id, 'parent_forum_id', $forum_id );
            update_post_meta( $notification_post_id, 'submission_type', $submission_type );
            update_post_meta( $notification_post_id, 'subscriber_ids', $subscriber_ids );
            update_post_meta( $notification_post_id, 'current_post_author', $author_id );
        }
    }

    /**
     * BBP Core notification menu item in author page
     */
    public function notification_menu(){
        
        if ( get_current_user_id() != bbp_get_displayed_user_field( 'ID' ) ) {
            return;
        }   
        
        ?>
        <ul>
            <li class="bbp-user-notification-link">
               <span>
                   <a href="<?php bbp_user_profile_url(); ?>?bbpc-notification=true" title="<?php printf( esc_attr__( "%s's notifications", 'bbp-core-pro' ), bbp_get_displayed_user_field( 'display_name' ) ); ?>">
                       <?php esc_html_e( 'Notifications', 'bbp-core-pro' ); ?>
                   </a>
               </span>
            </li>
        </ul>
       <?php
    }

    /**
     * BBP Core notification content in author page
     */
    public function notification_content(){
        
        if ( get_current_user_id() != bbp_get_displayed_user_field( 'ID' ) ) {
            return;
        }        

        $notification = $_GET['bbpc-notification'] ?? '';
        if ( $notification == true ) {
            $current_theme  = wp_get_theme();
            ?>
            <div class="notification-wrapper <?php echo esc_attr( $current_theme->get('Name') ); ?>">
            <?php
                if ( ! is_user_logged_in() ) {
                    esc_html_e( 'You must login to view your notifications', 'bbp-core-pro' );
                } else {           
                    echo bbpc_notification_render(); 
                }
            ?> 
            </div>

            <script>
            jQuery(document).ready(function($) {
                
                var notificationWrapper = $('.notification-wrapper');
                var userBody            = $('#bbp-user-body,.bbp-user-body');
                
                userBody.append(notificationWrapper);            
                userBody.children('div').not('.notification-wrapper').remove();
                
                setTimeout(function() {
                    notificationWrapper.show();
                    $('.bbp-user-notification-link').addClass('current');
                }, 500);
                
                $('.bbp-user-profile-link').parent().removeClass('current');
            });        
            </script>

        <?php 
        }
    }
}