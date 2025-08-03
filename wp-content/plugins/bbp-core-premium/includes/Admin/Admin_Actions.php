<?php
namespace BBPCorePro\Admin;
use WP_Query;

/**
 * Class Admin_Actions
 * @package BBPCorePro\Admin
 */
class Admin_Actions {
	function __construct() {
		add_action( 'bbpcorepro_notification', [ $this, 'bbpcorepro_notification' ], 99, 1 );
	}
	
	/**
	 * EazyDocs Notification
	 */
	public function bbpcorepro_notification() {
        $closed_topics = get_posts( array(
            'fields'            => 'ids',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_type'        => 'topic',
            'post_status'      => 'closed',
            'suppress_filters' => false,
			'posts_per_page'   => -1
        ));

        $solved_topics = get_posts( array(
            'fields'            => 'ids',
            'orderby'           => 'date',
            'order'             => 'DESC',
            'post_type'         => 'topic', 
            'suppress_filters'  => false,
            'meta_key'          => '_bbp_topic_is_solved',
            'meta_value'        => true,
			'posts_per_page'    => -1
        ));

        $latest_replies = get_posts( array(
            'fields'           => 'ids',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_type'        => 'reply',
            'post_status'      => 'publish',
            'suppress_filters' => false,
			'posts_per_page'   => -1
        ));
        
        $merged_post_ids = array_merge( $closed_topics, $solved_topics, $latest_replies);
			
        // the main query
		$merged_posts = new WP_Query(array(
			'post_type' 	   => ['topic', 'reply'],
			'post__in'     	   => $merged_post_ids, 
			'orderby'   	   => 'date',
			'order'     	   => 'DESC',
			'posts_per_page'   => 10
		));
		?>
        <li class="easydocs-notification" title="<?php esc_attr_e('Notifications', 'bbp-core-pro'); ?>">
            <div class="header-notify-icon">
                <img class="notify-icon" src="<?php echo BBPC_IMG ?>/admin/notification.svg" alt="<?php esc_html_e( 'Notify Icon', 'bbp-core-pro' ); ?>">
            </div>
			<?php if ( ! empty( $merged_post_ids ) ) : ?>
                <span class="easydocs-badge">
                 <?php echo esc_html( $merged_posts->post_count ); ?>
                </span>
			<?php endif; ?>

            <div class="easydocs-dropdown notification-dropdown">

				<?php if ( ! empty( $merged_post_ids ) ) : ?>
                    <div class="notification-head d-flex alignment-center justify-content-between">
                        <span class="header-text">
                            <?php esc_html_e( 'INBOX', 'bbp-core-pro' ); ?>
                        </span>
                    </div>
                    <div class="notification-body" data-ref="container-1">
                        
                        <div class="notify-column">
                            <ul> 
                                <?php
                                if( ! empty( $merged_post_ids ) ) :
                                    while($merged_posts->have_posts()) : $merged_posts->the_post();
                                    if ( get_post_type( get_the_ID() ) == 'reply' ) {
                                        $topic_id 			= bbp_get_reply_topic_id(get_the_ID());
                                        $notify_permalink 	= get_permalink($topic_id).'/#post-'.get_the_ID();
                                    } else {
                                        $notify_permalink 	= get_permalink(get_the_ID());
                                        $topic_status       = get_post_status(get_the_ID());
                                    }
                                    ?>
                                    
                                    <li class="notify-item">
                                        <a href="<?php echo esc_url($notify_permalink); ?>" target="_blank" class="d-flex">
                                            <div class="notify-icon"> 
                                                <?php
                                                if ( get_post_type( get_the_ID() ) == 'reply' ) :
                                                    ?>
                                                    <span class="dashicons dashicons-buddicons-replies"></span>
                                                    <?php 
                                                 else :
                                                    if ( $topic_status == 'closed' ) :
                                                        ?>
                                                        <span class="dashicons dashicons-buddicons-topics"></span>
                                                        <?php 
                                                    else :
                                                        ?>
                                                        <i class="icon_check_alt"></i>
                                                        <?php
                                                    endif;
                                                    ?>
                                                    <?php 
                                                endif; 
                                                ?>
                                            </div>                            
                                            <div class="notify-content-wrap">
                                                <div class="notify-content-header d-flex justify-content-between">                                                    
                                                    <?php 
                                                    if ( get_post_type( get_the_ID() ) == 'reply' ) :
                                                        ?>
                                                            <span class="noti-type"><?php esc_html_e( 'Reply', 'bbp-core-pro' ); ?></span>
                                                            <?php 
                                                        else :                                                            
                                                            if( $topic_status == 'closed' ) :                                                                
                                                                echo sprintf( '<span>Closed</span>', 'bbp-core-pro' ); 
                                                            else :
                                                                $is_topic_resolved = get_post_meta(get_the_ID(), '_bbp_topic_is_solved', true);
                                                                echo ! empty( $is_topic_resolved == 1 ) ? sprintf( '<span class="resolved noti-type">Resolved</span>', 'bbp-core-pro' ) : '';
                                                            endif;
                                                    endif; 
                                                    ?>
                                                    <span class="notify-creation" title="<?php bbp_topic_post_date(); ?>">
                                                        <?php bbp_reply_post_date( get_the_ID(), true ); ?>
                                                    </span> 
                                                </div>

                                                <div class="notify-content-summary">
                                                    <?php echo wpautop(wp_trim_words(get_the_content(), 25, false)); ?>
                                                </div>
                                            </div>   
                                        </a>
                                    </li>
                                    <?php
                                    endwhile;
                                endif;
                                wp_reset_postdata();
                                ?>
                            </ul>                            
                        </div>
                    </div>
				<?php else : ?>
                    <div class="notification-head d-flex alignment-center justify-content-center no-notification-text">
						<?php esc_html_e( 'No new notifications', 'bbp-core-pro' ); ?>
                    </div>
				<?php endif; ?>
            </div>
        </li>
	<?php }
	// Notification ended
}