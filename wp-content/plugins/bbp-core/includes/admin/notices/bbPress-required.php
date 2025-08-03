<?php
/**
 * Notice
 * Activate the bbPress
 * @return void
 */

include_once BBPC_DIR . '/includes/admin/notices/core_installer.php';
new BBPCore_Install_Core('');

add_action( 'admin_notices', function(){

	$has_installed = get_plugins();
	$button_text = isset( $has_installed['bbpress/bbpress.php'] ) ? __( 'Activate Now!', 'bbp-core' ) : __( 'Install Now!', 'bbp-core' );

	if( ! class_exists( 'bbPress' ) ) :
		?>
        <div class="error notice is-dismissible bottom-10">
            <p>
                <?php echo sprintf( '<strong>%1$s</strong> %2$s <strong>%3$s</strong> %4$s', __( 'BBP Core', 'bbp-core' ), __( 'requires', 'bbp-core' ), __( 'bbPress', 'bbp-core' ), __( ' plugin to be installed. Please get the plugin now!', 'bbp-core' ) ) ?>
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