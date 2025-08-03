<?php 
// Handle 'Agree' vote for logged-in users
function bbpc_handle_agree() {
    check_ajax_referer('bbpc-nonce', 'nonce');

    $topic_id = intval($_POST['topic_id']);
    $user_id = get_current_user_id();

    if ( ! $user_id ) {
        wp_send_json_error('User not logged in.');
        return;
    }

    // Check if the user has already voted
    $user_vote = get_user_meta($user_id, '_bbpc_vote_' . $topic_id, true);

    if ($user_vote === 'agree') {
        // If user has already agreed, remove the agree vote
        $agree_count = get_post_meta($topic_id, '_bbpc_agree_count', true) ?: 0;
        update_post_meta($topic_id, '_bbpc_agree_count', --$agree_count);

        // Remove the vote tracking
        delete_user_meta($user_id, '_bbpc_vote_' . $topic_id);
    } else {
        // If user had previously disagreed, remove 'disagree' first
        if ($user_vote === 'disagree') {
            $disagree_count = get_post_meta($topic_id, '_bbpc_disagree_count', true) ?: 0;
            update_post_meta($topic_id, '_bbpc_disagree_count', --$disagree_count);
        }

        // Add agree vote
        $agree_count = get_post_meta($topic_id, '_bbpc_agree_count', true) ?: 0;
        update_post_meta($topic_id, '_bbpc_agree_count', ++$agree_count);

        // Track the new vote as 'agree'
        update_user_meta($user_id, '_bbpc_vote_' . $topic_id, 'agree');
    }

    wp_send_json_success(array(
        'agree_count' => get_post_meta($topic_id, '_bbpc_agree_count', true),
        'disagree_count' => get_post_meta($topic_id, '_bbpc_disagree_count', true)
    ));
}
add_action('wp_ajax_bbpc_agree', 'bbpc_handle_agree');


// Handle 'Disagree' vote for logged-in users
function bbpc_handle_disagree() {
    check_ajax_referer('bbpc-nonce', 'nonce');

    $topic_id = intval($_POST['topic_id']);
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_send_json_error('User not logged in.');
        return;
    }

    // Check if the user has already voted
    $user_vote = get_user_meta($user_id, '_bbpc_vote_' . $topic_id, true);

    if ($user_vote === 'disagree') {
        // If user has already disagreed, remove the disagree vote
        $disagree_count = get_post_meta($topic_id, '_bbpc_disagree_count', true) ?: 0;
        update_post_meta($topic_id, '_bbpc_disagree_count', --$disagree_count);

        // Remove the vote tracking
        delete_user_meta($user_id, '_bbpc_vote_' . $topic_id);
    } else {
        // If user had previously agreed, remove 'agree' first
        if ($user_vote === 'agree') {
            $agree_count = get_post_meta($topic_id, '_bbpc_agree_count', true) ?: 0;
            update_post_meta($topic_id, '_bbpc_agree_count', --$agree_count);
        }

        // Add disagree vote
        $disagree_count = get_post_meta($topic_id, '_bbpc_disagree_count', true) ?: 0;
        update_post_meta($topic_id, '_bbpc_disagree_count', ++$disagree_count);

        // Track the new vote as 'disagree'
        update_user_meta($user_id, '_bbpc_vote_' . $topic_id, 'disagree');
    }

    wp_send_json_success(array(
        'agree_count' => get_post_meta($topic_id, '_bbpc_agree_count', true),
        'disagree_count' => get_post_meta($topic_id, '_bbpc_disagree_count', true)
    ));
}
add_action('wp_ajax_bbpc_disagree', 'bbpc_handle_disagree');


// Shortcode to display votings
function bbpc_display_bbpc_geo_votings( $atts ) {
    $atts = shortcode_atts(array(
        'type' => 'liked',
    ), $atts);
    ob_start();

    $user_id            = get_current_user_id();
    $topic_id           = [];
    $agree_voting       = [];
    $disagree_voting    = [];

    if ($user_id) {
        // Fetch topics where the user has voted "agree"
        $args = array(
            'post_type'         => 'topic', 
            'posts_per_page'    => -1         
        );
        $bbpc_geo_votings = new WP_Query($args);
        if ($bbpc_geo_votings->have_posts()) {
            while ($bbpc_geo_votings->have_posts()) : $bbpc_geo_votings->the_post();
            $topic_id[] = get_the_ID();
            endwhile;
            wp_reset_postdata();
        }
    }
    
    foreach ( $topic_id as $topic_ids ) {
        $voting_type = get_user_meta($user_id, '_bbpc_vote_' . $topic_ids, true);
        if ( $voting_type == 'agree' ) {
            $agree_voting[] = $topic_ids;           
        }
        if ( $voting_type == 'disagree' ) {
            $disagree_voting[] = $topic_ids;
        }
    }
    
    $single_agree_voting    = '';
    $single_disagree_voting = '';

    if ( count($agree_voting) === 1 ) {
        $single_agree_voting = 'single-topic-wrap';
    }
    if ( count($disagree_voting) === 1 ) {
        $single_disagree_voting = 'single-topic-wrap';
    }

    if ( ! empty ( $agree_voting ) || ! empty ( $disagree_voting ) ) {
        if ( $atts['type'] == 'liked' && ! empty( $agree_voting ) ) {

            echo '<div class="bbpc-voting-heading"> Liked Topics </div>';
              
            echo '<div class="bbpc-voting-liked-wrap '. esc_attr( $single_agree_voting ) . '">';  
            foreach ( $agree_voting as $agree_voting_id ) {
                $agree_count = get_post_meta($agree_voting_id, '_bbpc_agree_count', true) ?: 0;
                ?>
                <div class="bbpc-voting-liked">
                    <a href="<?php echo get_the_permalink($agree_voting_id); ?>"><?php echo get_the_title($agree_voting_id); ?>
                    
                    <span class="bbpc-agree-disagree-counter-wrap">                    
                        <span id="bbpc-agree-count-<?php echo esc_attr($agree_voting_id); ?>">
                            <img src="<?php echo BBPC_IMG . '/icon/thumbs-up.svg'; ?>" />
                            <?php echo esc_html( $agree_count );?>
                        </span>
                    </span>
                    </a>
                </div>
                <?php
            }
            echo '</div>';
        }

        if ( $atts['type'] == 'disliked' && ! empty( $disagree_voting ) ) {
            echo '<div class="bbpc-voting-heading"> Disliked Topics </div>';

            echo '<div class="bbpc-voting-liked-wrap '. esc_attr( $single_disagree_voting ) . '">';
            foreach ( $disagree_voting as $disagree_voting_id ) { 
                
                $disagree_count = get_post_meta($disagree_voting_id, '_bbpc_disagree_count', true) ?: 0;
                ?>

                <div class="bbpc-voting-liked">
                    <a href="<?php echo get_the_permalink($disagree_voting_id); ?>"><?php echo get_the_title($disagree_voting_id); ?>
                    
                    <span class="bbpc-agree-disagree-counter-wrap"> 
                        
                        <span id="bbpc-disagree-count-<?php echo esc_attr($disagree_voting_id); ?>">
                        <img src="<?php echo BBPC_IMG . '/icon/thumbs-down.svg'; ?>" />
                            <?php echo esc_html( $disagree_count );?>
                        </span>
                    </span>
                    </a>
                </div>

                <?php
                
            }
            echo '</div>';
        }
    } else {
        echo "<div class='bbpc-no-voting-wrap'> You don't have votes! </div>";
    }
    
    wp_enqueue_style( 'bbpc' );
    wp_enqueue_style('bbpc-pro-frontend');
        
    return ob_get_clean();
}
add_shortcode('bbpc_geo_votings', 'bbpc_display_bbpc_geo_votings');

// Add Agree/Disagree buttons content
add_action( 'bbp_theme_after_topic_content', function() {
    // Retrieve bbPress Core settings
    $opt                        = get_option( 'bbp_core_settings' );
    
    // Set default values if options are not set
    $reaction_condition         = $opt['reaction_display_condition'] ?? 'always';
    $topic_conditional_count    = 0;

    // Check if the reaction condition is based on reply count
    if ( $reaction_condition === 'has_replies' ) {
        $topic_conditional_count = absint( $opt['reaction_display_condition_count'] ?? 0 );
    }

    // Check if topic meets the reply count condition for showing reactions
    if ( bbpc_get_reply_count() >= $topic_conditional_count ) {  
        
        // Handle login URL if user is not logged in
        $login_url = '';
        if ( ! is_user_logged_in() ) {
            $login_url = 'data-login="' . esc_url( wp_login_url( get_permalink() ) ) . '"';
        }

        // Enqueue the voting script
        wp_enqueue_script( 'bbpc-voting' );  

        $has_same_topic = false;

        // Check if the user is premium and the same topic voting is enabled
        if ( function_exists('bbpc_is_premium') && bbpc_is_premium() && function_exists('bbpc_get_opt') && bbpc_get_opt('same_topic_voting') == true ) {
            $has_same_topic = true;
            echo '<div class="bbpc-footer-actions">';
        }                 

        // Begin outputting the agree/disagree buttons
        ?>
        <div class="bbpc-agree-disagree-buttons">
        <?php

        // Fetch the current user ID and topic ID
        $user_id  = get_current_user_id();
        $topic_id = get_the_ID();
        $topic_id = absint( $topic_id ); // Sanitize the topic ID

        // Determine if the user has voted (using user meta for logged-in users or cookies for guests)
        $user_vote = '';
        if ( $user_id ) {
            $user_vote = get_user_meta( $user_id, '_bbpc_vote_' . $topic_id, true );
        }

        // Get vote counts (agree/disagree)
        $agree_count    = absint( get_post_meta( $topic_id, '_bbpc_agree_count', true ) ?: 0 );
        $disagree_count = absint( get_post_meta( $topic_id, '_bbpc_disagree_count', true ) ?: 0 );

        // Determine which button is active based on the user's vote
        $agree_active_class    = ( $user_vote === 'agree' ) ? 'active' : '';
        $disagree_active_class = ( $user_vote === 'disagree' ) ? 'active' : '';

        // Output Agree button
        echo '<button ' . $login_url . ' class="bbpc-agree-button ' . esc_attr( $agree_active_class ) . '" data-type="like" data-topic="' . esc_attr( $topic_id ) . '">Agree</button>';
        echo '<span class="bbpc-reactions-btn-counter" id="bbpc-agree-count-' . esc_attr( $topic_id ) . '">' . esc_html( $agree_count ) . '</span>';

        // Output Disagree button
        echo '<button ' . $login_url . ' class="bbpc-disagree-button ' . esc_attr( $disagree_active_class ) . '" data-type="dislike" data-topic="' . esc_attr( $topic_id ) . '">Disagree</button>';
        echo '<span class="bbpc-reactions-btn-counter" id="bbpc-disagree-count-' . esc_attr( $topic_id ) . '">' . esc_html( $disagree_count ) . '</span>';

        echo '</div>'; // Close agree-disagree-buttons div
    }
}, 98 );
