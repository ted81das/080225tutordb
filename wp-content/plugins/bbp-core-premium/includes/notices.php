<?php
/**
 * Notice
 * Activate the BBP Core
 * @return void
 */

include_once BBPCOREPRO_PATH . '/includes/Admin/core_installer.php';
new bbpCore_Install_Core('');

add_action( 'admin_notices', function(){

	$has_installed = get_plugins();
	$button_text = isset( $has_installed['bbp-core/bbp-core.php'] ) ? __( 'Activate Now!', 'bbp-core-pro' ) : __( 'Install Now!', 'bbp-core-pro' );

	if( ! class_exists( 'BBP_Core' ) ) :
		?>
        <div class="error notice is-dismissible bottom-10">
            <p>
                <?php echo sprintf( '<strong>%1$s</strong> %2$s <strong>%3$s</strong> %4$s', __( 'BBP Core Pro', 'bbp-core-pro' ), __( 'requires the', 'bbp-core-pro' ), __( 'BBP Core', 'bbp-core-pro' ), __( 'Free version plugin to be installed and activated. Please get the plugin now!', 'bbp-core-pro' ) ) ?>
            </p>
            <p>
            <button id="bbp-core-install-core" class="button button-primary">
                <?php echo esc_html($button_text); ?>
            </button>
            </p>
        </div>
	    <?php
	endif;  

});