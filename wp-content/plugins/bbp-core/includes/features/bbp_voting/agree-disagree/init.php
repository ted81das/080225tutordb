<?php 
// Add Agree/Disagree buttons counters
function bbpc_get_reply_count(){
    ob_start(); 
    bbp_topic_reply_count(get_the_ID());
    return ob_get_clean();
}

// add a menu inside bbpress profile 
add_action('bbp_template_after_user_details_menu_items', function() {
    ?>
    <ul>
        <li class="bbp-user-votings-wrap">
            <span>
                <a href="<?php bbp_user_profile_url(); ?>?bbpc-voting=true" title="<?php printf( esc_attr__( "%s's Votings", 'bbp-core-pro' ), bbp_get_displayed_user_field( 'display_name' ) ); ?>">
                    <?php esc_html_e( 'Reactions', 'bbp-core-pro' ); ?>
                </a>
            </span>
        </li>
    </ul>
    <?php
});

// Display the voting content
function bbpc_geo_voting_content(){
    ob_start();
    echo do_shortcode("[bbpc_geo_votings type='liked']") . do_shortcode("[bbpc_geo_votings type='disliked']");
    return ob_get_clean();
}  

// Display the voting content in profile content section
add_action( 'bbp_template_before_user_wrapper', function(){
    $bbpc_voting = $_GET['bbpc-voting'] ?? '';
    if ( $bbpc_voting == true ) {        
        if ( get_current_user_id() != bbp_get_displayed_user_field( 'ID' ) ) {      
			wp_enqueue_style( 'bbpc' );
            ?>
            <script>
                ;(function($){
                    $(document).ready(function(){
                        $('#bbp-user-navigation ul li').removeClass('current');
                        $('.bbp-user-votings-wrap').addClass('current');
                        $('#bbp-user-body').html("<div class='bbpc-no-voting-wrap'> You must logged in to view your votes! </div>");
                        $('.bbpc-no-voting-wrap:not(:last-child)').remove();
                    });
                })(jQuery);
            </script>
            <?php 
        } else {
            $voting_content = bbpc_geo_voting_content();
            $voting_content = wp_json_encode($voting_content);            
			wp_enqueue_style( 'bbpc' );
            ?>
            <script>
                ;(function($){
                    $(document).ready(function(){
                        $('#bbp-user-navigation ul li').removeClass('current');
                        $('.bbp-user-votings-wrap').addClass('current');
                        $('#bbp-user-body').html(<?php echo $voting_content; ?>);
                        $('.bbpc-no-voting-wrap:not(:last-child)').remove();
                    });
                })(jQuery);
            </script>
            <?php
        }
    }    
} );