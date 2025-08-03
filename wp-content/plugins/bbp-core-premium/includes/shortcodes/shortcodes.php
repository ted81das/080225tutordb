<?php
/**
 * bbPress Profile Link Shortcode
 */

add_shortcode( 'bbpc_profile_link', function($content) {
    if ( function_exists('bbpc_is_premium') && bbpc_is_premium() ) {
        $content =  bbp_get_user_profile_url( bbp_get_current_user_id() );
    }
    return $content;
});