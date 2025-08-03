<?php
/**
 ** Give Notice
 **/
function bbpc_offer_notice() {
	if ( is_user_logged_in() ) {
		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'bbpc_offer_dismissed', true );

		if ( '1' === $dismissed ) {
			return; // Don't show the notice if it has been dismissed by this user
		}
	}
	?>
    <div class="bbpc-offer-wrap">
        <div class="bbpc-offer">
            <button class="dismiss-btn">&times;</button>
            <div class="bbpc-col">
                <span class="dashicons dashicons-megaphone"></span>
                <div class="bbpc-col-text">
                    <p><strong> Upgrade to Bbp Core Pro </strong></p>
                    <p> Massive discount </p>
                </div>
            </div>
            <div class="bbpc-col">
                <img src="<?php echo BBPC_IMG ?>/icon/coupon.svg"
                     alt="<?php echo esc_attr_x( 'Coupon', 'coupon', 'bbp-core' ); ?>" class="coupon-icon">
                <div class="bbpc-col-text">
                    <p><strong> Up to 40% Off </strong></p>
                    <p> This is limited time offer! </p>
                </div>
            </div>
            <div class="bbpc-col">
                <img src="<?php echo BBPC_IMG ?>/icon/cursor-hand.svg"
                     alt="<?php echo esc_attr_x( 'Coupon', 'coupon', 'bbp-core' ); ?>" class="coupon-icon">
                <div class="bbpc-col-text">
                    <p><strong> Grab the deal </strong></p>
                    <p> Before it expires! </p>
                </div>
            </div>
            <div class="bbpc-col">
                <div class="bbpc-col-box">
                    <label for="coupon">Coupon Code:</label>
                    <div class="coupon-container">
                        <input type="text" value="DASH40" id="coupon" class="coupon" readonly>
                        <span class="copy-message">Coupon copied.</span>
                        <button class="copy-btn">Copy</button>
                    </div>
                </div>
            </div>
            <div class="bbpc-col">
                <a href="https://spider-themes.net/bbp-core/pricing/" class="buy-btn" target="_blank"> Claim Discount </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyBtn = document.querySelector('.copy-btn');
            const couponInput = document.querySelector('.coupon');
            const copyMessage = document.querySelector('.copy-message');
            const dismissBtn = document.querySelector('.dismiss-btn');
            const offerWrap = document.querySelector('.bbpc-offer-wrap');

            copyBtn.addEventListener('click', function () {
                navigator.clipboard.writeText(couponInput.value).then(() => {
                    copyMessage.style.display = 'inline';
                    setTimeout(() => {
                        copyMessage.style.display = 'none';
                    }, 1000);
                }).catch(err => {
                    console.error('Could not copy text: ', err);
                });
            });

            dismissBtn.addEventListener('click', function () {
                offerWrap.style.display = 'none';

                // Make an AJAX request to save the dismissal for the logged-in user
                fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=bbpc_dismiss_offer_notice&nonce=<?php echo wp_create_nonce( "bbpc-dismiss-notice" ); ?>'
                }).then(response => response.json()).then(data => {
                    if (!data.success) {
                        console.error('Error dismissing the notice:', data.message);
                    }
                }).catch(err => {
                    console.error('Error dismissing the offer:', err);
                });
            });
        });
    </script>
	<?php
}

add_action( 'wp_ajax_bbpc_dismiss_offer_notice', 'bbpc_dismiss_offer_notice' );

function bbpc_dismiss_offer_notice() {
	check_ajax_referer( 'bbpc-dismiss-notice', 'nonce' );

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'bbpc_offer_dismissed', '1' );

		wp_send_json_success( array( 'message' => 'Notice dismissed for this user.' ) );
	} else {
		wp_send_json_error( array( 'message' => 'User not logged in.' ) );
	}

	wp_die();
}
