<?php
$optionReview = get_option('bbpc_notify_review');
if ( is_admin() && time() >= (int) $optionReview && $optionReview !== '0') {
    add_action('admin_enqueue_scripts', 'bbpc_notify_enqueue_script'); // Hook to admin_enqueue_scripts
    
    $bbpc_installed = get_option('bbpc_installed');
    // Check if timestamp exists
    if ( $bbpc_installed ) {
        // Add 7days to the timestamp
        $show_notice = $bbpc_installed + (7 * 24 * 60 * 60);
        
        // Get the current time
        $current_time = current_time('timestamp');

        // Compare current time with timestamp + 7 days
        if ( $current_time >= $show_notice ) {
            add_action('admin_notices', 'bbpc_notify_give_review');
        }
    }
}

function bbpc_notify_enqueue_script() {
    wp_enqueue_script( 'bbpc-notify-review' );
}

add_action('wp_ajax_bbpc_notify_save_review', 'bbpc_notify_save_review');

/**
 ** Give Notice
 **/
function bbpc_notify_give_review() {
    ?>
    <div class="notice notice-success is-dismissible" id="bbpc_notify_review">
        <h3> <?php _e('Give BBP Core a review', 'bbp-core'); ?> </h3>
        <p>
            <?php _e('Thank you for choosing BBP Core. We hope you love it. Could you take a couple of seconds posting a nice review to share your happy experience?', 'bbp-core')?>
        </p>
        <p class="bbpc_notify_review_subheading">
            <?php _e('We will be forever grateful. Thank you in advance.', 'bbp-core'); ?>
        </p>
        <p>
            <a href="javascript:void(0)" data="rateNow" class="button button-primary" style="margin-right: 5px"><?php _e('Rate now', 'bbp-core')?></a>
            <a href="javascript:void(0)" data="later" class="button" style="margin-right: 5px"><?php _e('Later', 'bbp-core')?></a>
            <a href="javascript:void(0)" data="alreadyDid" class="button"><?php _e('Already did', 'bbp-core')?></a>
        </p>
    </div>
    <?php
}

/**
 ** Save Notice
 **/
function bbpc_notify_save_review() {
    if ( isset( $_POST ) ) {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : null;
        $field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : null;

        if ( ! wp_verify_nonce( $nonce, 'bbpc-admin-nonce' ) ) {
            wp_send_json_error( array( 'status' => 'Wrong nonce validate!' ) );
            exit();
        }

        if ( $field == 'later' ) {
            update_option('bbpc_notify_review', time() + 3*60*60*24); //After 3 days show
        } else if ( $field == 'alreadyDid' ) {
            update_option('bbpc_notify_review', 0);
        }
        wp_send_json_success();
    }
    wp_send_json_error( array( 'message' => 'Update fail!' ) );
}