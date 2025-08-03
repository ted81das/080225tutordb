<?php
namespace BBPCore\WpWidgets;

use WP_Widget;

// Newsletter
class Forum_Information extends WP_Widget {
	public function __construct() {
		parent::__construct( 'bbpc_forum_info', esc_html__( 'BBPC Current Forum Info', 'bbp-core' ), array(
			'description' => esc_html__( "Displays the current forum's information, including the number of topics, replies, Last post by, and Last activity, as well as the Subscribe button", 'bbp-core' ),
			'classname'   => 'bbpc_forum_information'
		) );
	}

	// Front End
	public function widget( $args, $instance ) {
        // if single forum page
        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }
 
        $title                  = ( ! empty( $instance['title'] ) ) ? $instance['title'] : esc_html__( 'Forum Informations', 'bbp-core' );
        $title                  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        $show_icons             = $instance['show_icons'] ?? false;
        $show_topics_count      = $instance['show_topics_count'] ?? false;
        $show_replies_count     = $instance['show_replies_count'] ?? false;
        $show_last_post_user    = $instance['show_last_post_user'] ?? false;
        $show_last_activity     = $instance['show_last_activity'] ?? false;
        $show_subscribe         = $instance['show_subscribe'] ?? false;
 
        echo $args['before_widget'];
        
        if(  is_bbpress() && is_singular( 'forum' ) ) :
            $forum_id               = bbp_get_forum_id();
            $topic_count            = bbp_get_forum_topic_count( $forum_id );
            $reply_count            = bbp_get_forum_reply_count( $forum_id );
            $last_active            = bbp_get_forum_last_active_time( $forum_id );            
            $last_active_user_id    = bbp_get_forum_last_active_id( $forum_id );
            $last_active_user       = get_post_field( 'post_author', $last_active_user_id );

	        if ( $title ) {
		        echo $args['before_title'] . $title . $args['after_title'];
	        }

            $subscribe_link         = '';
            if ( is_user_logged_in() ) {
                $subscribe_link     = bbp_get_forum_subscription_link();
            }

            if ( $show_topics_count == 'on' || $show_replies_count == 'on' || $show_last_post_user == 'on' || $show_last_activity == 'on' || $show_subscribe == 'on') :
                ?>
                <div class="bbpc-widget-forum-info">
                    <table>
                        <tbody>
                            <?php 
                            if ( $show_topics_count == 'on' ) :
                                ?>
                                <tr class="show_count_topics">
                                    <th>
                                        <?php 
                                        if( $show_icons == 'on' ) :
                                            ?>
                                            <i class="icon_documents"></i>
                                            <?php 
                                        endif;
                                        esc_html_e( 'Topics', 'bbp-core' );
                                        ?>
                                    </th>
                                    <td><?php echo $topic_count; ?></td>
                                </tr>
                                <?php 
                            endif;
                            
                            if ( $show_replies_count == 'on' ) :
                                ?>
                                <tr class="show_count_replies">
                                    <th>
                                        <?php 
                                        if( $show_icons == 'on' ) :
                                            ?>
                                            <i class="icon_chat_alt"></i>
                                            <?php 
                                        endif;
                                        esc_html_e( 'Replies', 'bbp-core' );
                                        ?>    
                                    </th>
                                    <td><?php echo $reply_count; ?></td>
                                </tr>
                                <?php 
                            endif;

                            if ( $show_last_post_user == 'on' ) :
                                ?>                        
                                <tr class="show_last_post_user">
                                    <th>
                                        <?php 
                                        if ( $show_icons == 'on' ) :
                                            ?>
                                            <img src="<?php echo BBPC_IMG ?>/avatar.svg" alt="<?php esc_attr_e( 'BBP Core Pro avatar icon', 'bbp-core' ); ?>">
                                            <?php 
                                        endif;
                                        esc_html_e( 'Last post by', 'bbp-core' );
                                        ?>    
                                </th>
                                    <td>
                                        <a href="<?php echo get_author_posts_url( $last_active_user ); ?>">
                                            <?php echo $last_active_user = get_the_author_meta( 'user_nicename', $last_active_user ); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                            endif;

                            if ( $show_last_activity == 'on' ) :
                                ?>
                                <tr class="show_last_activity">
                                    <th>
                                        <?php 
                                        if( $show_icons == 'on' ) :
                                            ?>
                                            <i class="icon_clock_alt"></i>
                                            <?php 
                                        endif;
                                        esc_html_e( 'Last activity', 'bbp-core' );
                                        ?>
                                    </th>
                                    <td> <?php echo $last_active; ?> </td>
                                </tr>
                                <?php 
                            endif;
                            
                            if ( $show_subscribe == 'on' ) :
                                wp_enqueue_style( 'bbpc-frontend-global' );
                                wp_enqueue_script( 'bbpc-wp-widget' );
                                ?>
                                <tr class="show_subscribe">
                                    <th>
                                        <?php 
                                        if( $show_icons == 'on' ) :
                                            ?>
                                            <i class="icon_action"></i>
                                            <?php 
                                        endif;
                                        esc_html_e( 'Actions', 'bbp-core' );
                                        ?>
                                    </th>
                                    <td>
                                        <?php 
                                        if( is_user_logged_in() ){
                                            echo bbp_get_forum_subscription_link();
                                        } else {
                                            echo $subscribe_link = '<a href="'. wp_login_url(get_permalink()) .'">Login to subscribe</a>';
                                        }
                                        ?>
                                    </td>
                                </tr>  
                                <?php 
                            endif;
                            ?>      
                        </tbody>
                    </table>
                </div>
                <?php
            endif;
        endif;
        
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title                  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $show_icons             = isset( $instance['show_icons'] ) ? esc_attr( $instance['show_icons'] ) : '';
        $show_topics_count      = isset( $instance['show_topics_count'] ) ? esc_attr( $instance['show_topics_count'] ) : '';
        $show_replies_count     = isset( $instance['show_replies_count'] ) ? esc_attr( $instance['show_replies_count'] ) : '';
        $show_last_post_user    = isset( $instance['show_last_post_user'] ) ? esc_attr( $instance['show_last_post_user'] ) : '';
        $show_last_activity     = isset( $instance['show_last_activity'] ) ? esc_attr( $instance['show_last_activity'] ) : '';
        $show_subscribe         = isset( $instance['show_subscribe'] ) ? esc_attr( $instance['show_subscribe'] ) : '';       
        require plugin_dir_path(__FILE__) . 'admin-options.php';   
	}

    public function update($new_instance, $old_instance){
        $instance          = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['show_icons'] = (!empty($new_instance['show_icons'])) ? strip_tags($new_instance['show_icons']) : '';
        $instance['show_topics_count'] = (!empty($new_instance['show_topics_count'])) ? strip_tags($new_instance['show_topics_count']) : '';
        $instance['show_replies_count'] = (!empty($new_instance['show_replies_count'])) ? strip_tags($new_instance['show_replies_count']) : '';
        $instance['show_last_post_user'] = (!empty($new_instance['show_last_post_user'])) ? strip_tags($new_instance['show_last_post_user']) : '';
        $instance['show_last_activity'] = (!empty($new_instance['show_last_activity'])) ? strip_tags($new_instance['show_last_activity']) : '';
        $instance['show_subscribe'] = (!empty($new_instance['show_subscribe'])) ? strip_tags($new_instance['show_subscribe']) : '';

        return $instance;
    }
}