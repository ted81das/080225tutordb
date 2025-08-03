<?php 
$forum_tab_title = ! empty ( $settings['forum_tab_title'] ) ? $settings['forum_tab_title'] :  __( 'Show Forum', 'bbp-core' );
$topics_tab_title = ! empty ( $settings['topics_tab_title'] ) ? $settings['topics_tab_title'] :  __( 'Show Topics', 'bbp-core' );
?>

<section class="community-area" id="forumTab-<?php echo esc_attr( $this->get_id() ); ?>">
    <ul class="nav nav-tabs tab-buttons" role="tablist">
        <li class="nav-item" role="presentation">
        <button class="nav-link active tab-button tab" onclick="forumTab(event, 'forumTab-<?php echo esc_attr( $this->get_id() ); ?>', 'forum-<?php echo esc_attr( $this->get_id() ); ?>')">
            <?php echo esc_html( $forum_tab_title ); ?>
        </button>

        </li>
        <li class="nav-item" role="presentation">
        <button class="nav-link tab-button tab" onclick="forumTab(event, 'forumTab-<?php echo esc_attr( $this->get_id() ); ?>', 'topics-<?php echo esc_attr( $this->get_id() ); ?>')">
            <?php echo esc_html( $topics_tab_title ); ?>
        </button>
        </li>
    </ul>
    <div id="forum-<?php echo esc_attr( $this->get_id() ); ?>" class="tab-content show active">
        <div class="gy-4 bbpc-community-topic-widget-main-wrapper">
            <?php
            while ( $forums->have_posts() ) : $forums->the_post();
                $item_id   = get_the_ID();
                $author_id = get_post_field( 'post_author', $item_id );
                ?>
                <div class="col-md-6 col-lg-4 bbpc-community-topic-widget-wrapper">
                    <div class="community-topic-widget-box">
                        <?php the_post_thumbnail( 'full' ); ?>
                        <div class="box-content">
                            <h5>
                                <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
                            </h5>
                            <span><?php bbp_forum_topic_count( $item_id );_e( ' Posts', 'bbp-core' ); ?></span>
                            <span class="vr-line">|</span>
                            <span><?php bbp_forum_reply_count( $item_id );_e( ' Replies', 'bbp-core' ); ?></span>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </div>

        <?php if ( $settings['is_forum_tab_btn'] == 'yes' ) : ?>
            <div class="text-center bbpc-show-more-btn-wrapper">
                <a href="<?php echo esc_url( $settings['more_url']['url'] ); ?>" class="dbl-arrow-upper show-more-btn show-more-round mt-70">
                    <div class="arrow-cont">
                        <svg width="13px" height="13px" class="first" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><polygon points="24,29.171 9.414,14.585 6.586,17.413 24,34.827 41.414,17.413 38.586,14.585"></polygon> </g> </g></svg>
                        <svg width="13px" height="13px" class="second" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><polygon points="24,29.171 9.414,14.585 6.586,17.413 24,34.827 41.414,17.413 38.586,14.585"></polygon> </g> </g></svg>
                    </div>
                    <?php echo esc_html( $settings['more_txt'] ?? '' ); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>

    <div id="topics-<?php echo esc_attr( $this->get_id() ); ?>" class="tab-content ">
		<?php
        $i = 0;
        while ( $topics->have_posts() ) : $topics->the_post();
            $topic_id   = $topics->posts[ $i ]->ID;
            $vote_count = get_post_meta( $topic_id, "bbpv-votes", true );
            $forum_id   = bbp_get_topic_forum_id();
            ?>
            <div class="single-forum-post-widget">
                <div class="post-content">
                    <div class="post-title">
                        <h6> <a href="<?php the_permalink(); ?>"> <?php the_title() ?> </a> </h6>
                    </div>
                    <div class="post-info">
                        <div class="author">
                            <img src="<?php echo BBPC_IMG . '/forum_tab/user-circle-alt.svg' ?>" alt="<?php esc_attr_e( 'User circle', 'bbp-core' );
                            ?>">
                            <?php 
                            echo bbp_get_topic_author_link( 
                                array( 
                                    'post_id' 	=> $topic_id, 
                                    'type' 		=> 'name' 
                                )
                            );
                            ?>
                        </div>

                        <div class="post-time">
                            <img src="<?php echo BBPC_IMG . '/forum_tab/time-outline.svg'?>" alt="<?php esc_attr_e( 'Time outline', 'bbp-core' );
                            ?>">
                            <?php bbp_forum_last_active_time( get_the_ID() ); ?>
                        </div>

                        <div class="post-category">
                            <a href="<?php echo get_the_permalink( $forum_id ) ?>">
                                <?php echo get_the_post_thumbnail( $forum_id ); ?>
                                <?php echo get_the_title( $forum_id ) ?>
                            </a>
                        </div>
                    </div>
                    <?php
                    bbp_topic_tag_list( '',
                        array(
                            'before' => '<div class="post-tags">',
                            'after'  => '</div>',
                            'sep'    => ''
                        )
                    );
                    ?>
                </div>
                <div class="post-reach">
                    <div class="post-view">
                        <img src="<?php echo BBPC_IMG . '/forum_tab/eye-outline.svg' ?>" alt="<?php esc_attr_e( 'View icon', 'bbp-core' ); ?>">
                        <?php bbp_topic_view_count( $topic_id );
                        _e( ' Views', 'bbp-core' ); ?>
                    </div>
                    <div class="post-like">
                        <img src="<?php echo BBPC_IMG . '/forum_tab/thumbs-up-outline.svg' ?>" alt="<?php esc_attr_e( 'Like icon', 'bbp-core' ); ?>">
                        <?php
                        if ( $vote_count ) {
                            echo $vote_count;
                        } else {
                            echo "0";
                        }
                        _e( ' Likes', 'bbp-core' ); ?>
                    </div>
                    <div class="post-comment">
                        <img src="<?php echo BBPC_IMG . '/forum_tab/chatbubbles-outline.svg' ?>" alt="<?php esc_attr_e( 'Comment icon', 'bbp-core' ); ?>">
                        <?php
                        bbp_topic_reply_count( $topic_id );
                        esc_html_e( ' Replies', 'bbp-core' )
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $i ++;
        endwhile;
        unset( $i );
        wp_reset_postdata();
        ?>

        <?php if ( $settings['is_topic_tab_btn'] == 'yes' ) : ?>
            <div class="row">
                <div class="text-center bbpc-show-more-btn-wrapper">
                    <a href="<?php echo esc_url( $settings['more_url2']['url'] ); ?>" class="dbl-arrow-upper show-more-btn show-more-round mt-70">
                        <div class="arrow-cont">
                            <svg width="13px" height="13px" class="first" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48H0z" fill="none"></path><polygon points="24,29.171 9.414,14.585 6.586,17.413 24,34.827 41.414,17.413 38.586,14.585"></polygon> </g> </g></svg>
                            <svg width="13px" height="13px" class="second" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"> <path d="M0 0h48v48H0z" fill="none"></path><polygon points="24,29.171 9.414,14.585 6.586,17.413 24,34.827 41.414,17.413 38.586,14.585"></polygon> </g> </g> </svg>
                        </div>
                        <?php echo esc_html( $settings['more_txt2'] ?? '' ); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>