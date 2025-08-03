<?php
$workflow_action = ( isset( $_GET['workflow-id'] ) ) ? 'edit' : 'new';
$workflow_id     = ( isset( $_GET['workflow-id'] ) ) ? $_GET['workflow-id'] : flowmattic_random_string();
$all_workflows   = wp_flowmattic()->workflows_db->get_all();
$workflow        = array();
$folders         = array();

foreach ( $all_workflows as $key => $workflow_obj ) {
	$workflow_settings = json_decode( $workflow_obj->workflow_settings );

	if ( isset( $workflow_settings->folder ) ) {
		$folders[ $workflow_settings->folder ] = 1;
	}

	// If workflow ID matches, set the workflow.
	if ( $workflow_id === $workflow_obj->workflow_id ) {
		$workflow = $workflow_obj;
	}
}

$workflow_types = (array) flowmattic_get_integrations( 'workflows' );
$license_key    = get_option( 'flowmattic_license_key', '' );

if ( '' === $license_key ) {
	?>
	<div class="card border-light mw-100">
		<div class="card-body text-center">
			<div class="alert alert-primary" role="alert">
				<?php echo esc_html__( 'License key not registered. Please register your license first to edit this workflow.', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
} elseif ( '' === $license_key ) {
	?>
		<div class="card border-light mw-100">
			<div class="card-body text-center">
				<div class="alert alert-primary p-4 m-5 text-center" role="alert">
				<?php echo esc_html__( 'License key not valid. Please register your license first to edit this workflow.', 'flowmattic' ); ?>
				</div>
			</div>
		</div>
	<?php
	wp_die();
}

$steps    = array();
$settings = array();

if ( $workflow ) {
	$steps    = $workflow->workflow_steps;
	$settings = json_decode( $workflow->workflow_settings, true );

	$decoded_steps = json_decode( $steps );
	if ( isset( $decoded_steps->steps ) ) {
		$steps = $decoded_steps->steps;

		// Remove placeholder steps.
		foreach ( $steps as $key => $step ) {
			if ( 'placeholder' === $step->type ) {
				unset( $steps[ $key ] );
			}
		}

		$steps = wp_json_encode( $steps );
	}
}

$wp_current_user  = wp_get_current_user();
$wp_user_email    = $wp_current_user->user_email;
$workflow_manager = ( isset( $settings['user_email'] ) && '' !== $settings['user_email'] ) ? $settings['user_email'] : $wp_user_email;

if ( 'new' !== $workflow_action ) {
	if ( ! current_user_can( 'manage_options' ) || '' === $workflow_manager ) {
		if ( $workflow_manager !== $wp_user_email ) {
			wp_die( 'You don\'t have access to this workflow. Please contact site admin if you have any questions' );
		}
	}
}

$name               = ( isset( $workflow->workflow_name ) ) ? rawurldecode( $workflow->workflow_name ) : esc_attr__( 'New Workflow', 'flowmattic' );
$description        = ( isset( $settings['description'] ) ) ? $settings['description'] : '';
$workflow_status    = ( isset( $settings['status'] ) && $settings['status'] ) ? $settings['status'] : 'off';
$webhook_queue      = ( isset( $settings['webhook_queue'] ) && $settings['webhook_queue'] ) ? $settings['webhook_queue'] : 'disable';
$workflow_auth_key  = ( isset( $settings['workflow_auth_key'] ) ) ? $settings['workflow_auth_key'] : flowmattic_random_string( 32 );
$captured_responses = ( isset( $settings['capturedResponses'] ) ) ? $settings['capturedResponses'] : '';
$selected_response  = ( isset( $settings['selectedResponse'] ) ) ? $settings['selectedResponse'] : '';
?>
<script type="text/javascript" id="flowmattic-data">
<?php
if ( ! empty( $steps ) ) {
	?>
	var workflowSteps = <?php echo $steps; ?>;
	<?php
}

$core_settings      = get_option( 'flowmattic_settings', array() );
$default_folder     = isset( $core_settings['default_folder'] ) ? $core_settings['default_folder'] : 'default';
$folder             = ( isset( $settings['folder'] ) ) ? $settings['folder'] : $default_folder;
$webhook_url_base   = isset( $core_settings['webhook_url_base'] ) ? $core_settings['webhook_url_base'] : 'regular';
$flowmattic_apps    = wp_flowmattic()->apps;
$trigger_apps       = $flowmattic_apps->get_trigger_applications();
$action_apps        = $flowmattic_apps->get_action_applications();
$other_trigger_apps = $flowmattic_apps->get_other_trigger_applications();
$other_action_apps  = $flowmattic_apps->get_other_action_applications();
$webhook_capture    = get_option( 'webhook-capture-' . $workflow_id, false );
$workflow_url       = ( 'regular' ) === $webhook_url_base ? FlowMattic_Webhook::get_url( $workflow_id ) : FlowMattic_Webhook::get_rest_url( $workflow_id );
$mailhook_url       = FlowMattic_Email_Parser::get_mailhook_url( $workflow_id );
$mailhook_url_v2    = FlowMattic_Email_Parser::get_mailhook_url_v2( $workflow_id );
$haro_mailhook_url  = FlowMattic_Haro_Email_Parser::get_mailhook_url( $workflow_id );

// Remove disabled trigger apps.
foreach ( $trigger_apps as $app_slug => $app ) {
	if ( isset( $core_settings[ 'disable-app-' . $app_slug ] ) && $app_slug === $core_settings[ 'disable-app-' . $app_slug ] ) {
		unset( $trigger_apps[ $app_slug ] );
	}
}

// Remove disabled action apps.
foreach ( $action_apps as $app_slug => $app ) {
	if ( isset( $core_settings[ 'disable-app-' . $app_slug ] ) && $app_slug === $core_settings[ 'disable-app-' . $app_slug ] ) {
		unset( $action_apps[ $app_slug ] );
	}
}

// Reset data capturing.
delete_option( 'webhook-capture-live' );
delete_option( 'webhook-capture-app-action' );
?>
var workflowId = '<?php echo esc_attr( $workflow_id ); ?>',
	fmAuthKey = '<?php echo esc_attr( $workflow_auth_key ); ?>',
	webhookURL = '<?php echo esc_attr( $workflow_url ); ?>',
	mailhookURL = '<?php echo esc_attr( $mailhook_url ); ?>',
	mailhookURLV2 = '<?php echo esc_attr( $mailhook_url_v2 ); ?>',
	haroMailhookURL = '<?php echo esc_attr( $haro_mailhook_url ); ?>',
	workflowStatus = '<?php echo esc_attr( $workflow_status ); ?>',
	triggerApps = <?php echo wp_json_encode( $trigger_apps ); ?>,
	actionApps  = <?php echo wp_json_encode( $action_apps ); ?>,
	otherActionApps  = <?php echo wp_json_encode( $other_action_apps ); ?>,
	otherTriggerApps  = <?php echo wp_json_encode( $other_trigger_apps ); ?>;

jQuery( document ).ready( function() {
	if ( -1 === window.location.href.indexOf( 'workflow-id' ) ) {
		window.history.replaceState( null, null, window.location.href + '&workflow-id=' + workflowId );
	}
} );
<?php
// Decode responses.
$decoded_responses = json_decode( base64_decode( $captured_responses ), true ); // phpcs:ignore
?>
var capturedResponses = <?php echo ( '' !== $decoded_responses ) ? wp_json_encode( $decoded_responses ) : '[]'; ?>;
var selectedResponse = '<?php echo esc_attr( $selected_response ); ?>';
</script>
<style>
.modal-backdrop.fade.show {
	z-index: 100000;
}
</style>
<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-workflow-header">
		<a href="<?php echo admin_url( '/admin.php?page=flowmattic-workflows' ); ?>" class="btn flowmattic-back-button" title="<?php echo esc_attr__( 'Back to Workflows', 'flowmattic' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
		</a>
		<div class="flowmattic-workflow-inputs w-50">
			<label for="workflow-name">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path stroke-width="1" stroke="#221b38" d="M2 18.0012H7"></path><path stroke-width="1" stroke="#221b38" fill="none" d="M19.586 6.29119L11.0707 14.8012L10 18.0012L13.192 16.9412L21.7073 8.43119C22.0976 8.04119 22.0976 7.40119 21.7073 7.01119L21.0069 6.29119C20.6066 5.90119 19.9762 5.90119 19.586 6.29119Z"></path></svg>
			</label>
			<input type="text" id="workflow-name" name="workflow-name" class="workflow-input workflow-name hidden" value="<?php echo esc_attr( $name ); ?>" />
			<span id="workflow-name-label" name="workflow-name-label" class="workflow-input workflow-name-label"><?php echo esc_attr( $name ); ?></span>
			<input type="hidden" name="workflow-id" class="workflow-input workflow-id" value="<?php echo esc_attr( $workflow_id ); ?>" />
			<input type="hidden" name="workflow-auth-key" class="workflow-input workflow-auth-key" value="<?php echo esc_attr( $workflow_auth_key ); ?>" />
		</div>
	</div>
	<div class="flowmattic-sidebar-wrapper">
		<div id="flowmattic-workflow-sidebar">
			<nav class="flowmattic-sidebar-nav">
				<ul>
					<li>
						<a href="#flowmattic-sidebar-outline" class="flowmattic-sidebar-outline" data-toggle="tooltip" title="Show workflow outline" data-placement="right">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path stroke-width="1.3" stroke="#221b38" fill="none" d="M8 19.4999L21.3 6.29991C22.3 5.29991 22.3 3.69991 21.3 2.79991C20.3 1.89991 18.7 1.79991 17.8 2.79991L4.5 15.9999L8 19.4999Z"></path>
								<path stroke-width="1.3" stroke="#221b38" fill="none" d="M4.5 16L2 22L8 19.5L4.5 16Z"></path>
								<path stroke-width="1.3" stroke="#221b38" d="M12.5 3H2"></path>
								<path stroke-width="1.3" stroke="#221b38" d="M8.5 7H2"></path>
								<path stroke-width="1.3" stroke="#221b38" d="M4.5 11H2"></path>
							</svg>
						</a>
					</li>
					<li>
						<a href="#flowmattic-sidebar-settings" class="flowmattic-sidebar-settings" data-toggle="tooltip" title="Show workflow settings" data-placement="right">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path fill="none" d="M13.87 4.89L13.56 2.44C13.53 2.19 13.32 2 13.06 2H9.95C9.7 2 9.48 2.19 9.45 2.44L9.18 4.59C9.16 4.77 9.03 4.93 8.86 4.99C8.02 5.3 7.22 5.78 6.53 6.4L4.25 5.44C4.02 5.34 3.75 5.43 3.62 5.65L2.06 8.35C1.94 8.57 2 8.85 2.2 9L4.17 10.49C3.96 11.5 3.96 12.53 4.16 13.51H4.17L2.2 15C2 15.15 1.94 15.43 2.07 15.65L3.63 18.35C3.76 18.57 4.03 18.66 4.26 18.56L6.54 17.6L6.53 17.61C6.9 17.94 7.32 18.24 7.77 18.5C8.22 18.76 8.68 18.97 9.16 19.13V19.11L9.47 21.56C9.48 21.81 9.7 22 9.95 22H13.07C13.32 22 13.53 21.81 13.57 21.56L13.84 19.41C13.86 19.23 13.99 19.07 14.16 19.01C15 18.7 15.8 18.22 16.49 17.6L18.77 18.56C19 18.66 19.27 18.57 19.4 18.35L20.96 15.65C21.09 15.43 21.03 15.15 20.83 15L18.86 13.51C19.07 12.5 19.07 11.47 18.87 10.49H18.86L20.81 9C21.01 8.85 21.07 8.57 20.94 8.35L19.38 5.65C19.25 5.43 18.98 5.34 18.75 5.44L16.48 6.4L16.49 6.39C16.12 6.06 15.7 5.76 15.25 5.5C14.8 5.24 14.34 5.03 13.86 4.87" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
								<path stroke-width="1.3" stroke="#221b38" d="M13.87 4.89L13.56 2.44C13.53 2.19 13.32 2 13.06 2H9.95C9.7 2 9.48 2.19 9.45 2.44L9.18 4.59C9.16 4.77 9.03 4.93 8.86 4.99C8.02 5.3 7.22 5.78 6.53 6.4L4.25 5.44C4.02 5.34 3.75 5.43 3.62 5.65L2.06 8.35C1.94 8.57 2 8.85 2.2 9L4.17 10.49C3.96 11.5 3.96 12.53 4.16 13.51H4.17L2.2 15C2 15.15 1.94 15.43 2.07 15.65L3.63 18.35C3.76 18.57 4.03 18.66 4.26 18.56L6.54 17.6L6.53 17.61C6.9 17.94 7.32 18.24 7.77 18.5C8.22 18.76 8.68 18.97 9.16 19.13V19.11L9.47 21.56C9.48 21.81 9.7 22 9.95 22H13.07C13.32 22 13.53 21.81 13.57 21.56L13.84 19.41C13.86 19.23 13.99 19.07 14.16 19.01C15 18.7 15.8 18.22 16.49 17.6L18.77 18.56C19 18.66 19.27 18.57 19.4 18.35L20.96 15.65C21.09 15.43 21.03 15.15 20.83 15L18.86 13.51C19.07 12.5 19.07 11.47 18.87 10.49H18.86L20.81 9C21.01 8.85 21.07 8.57 20.94 8.35L19.38 5.65C19.25 5.43 18.98 5.34 18.75 5.44L16.48 6.4L16.49 6.39C16.12 6.06 15.7 5.76 15.25 5.5C14.8 5.24 14.34 5.03 13.86 4.87"></path>
								<path stroke-width="1.3" stroke="#221b38" fill="none" d="M11.51 16C13.7191 16 15.51 14.2091 15.51 12C15.51 9.79086 13.7191 8 11.51 8C9.30086 8 7.51 9.79086 7.51 12C7.51 14.2091 9.30086 16 11.51 16Z"></path>
							</svg>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=flowmattic-task-history&workflow-id=' . $workflow_id ); ?>" target="_blank" class="text-reset text-decoration-none flowmattic-sidebar-history" data-toggle="tooltip" title="Show history" data-placement="right">
							<span class="dashicons" style="width: 24px;height: 24px;"><svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><g><path d="M22.69,18.37l1.14-1l-1-1.73l-1.45,0.49c-0.32-0.27-0.68-0.48-1.08-0.63L20,14h-2l-0.3,1.49c-0.4,0.15-0.76,0.36-1.08,0.63 l-1.45-0.49l-1,1.73l1.14,1c-0.08,0.5-0.08,0.76,0,1.26l-1.14,1l1,1.73l1.45-0.49c0.32,0.27,0.68,0.48,1.08,0.63L18,24h2l0.3-1.49 c0.4-0.15,0.76-0.36,1.08-0.63l1.45,0.49l1-1.73l-1.14-1C22.77,19.13,22.77,18.87,22.69,18.37z M19,21c-1.1,0-2-0.9-2-2s0.9-2,2-2 s2,0.9,2,2S20.1,21,19,21z M11,7v5.41l2.36,2.36l1.04-1.79L13,11.59V7H11z M21,12c0-4.97-4.03-9-9-9C9.17,3,6.65,4.32,5,6.36V4H3v6 h6V8H6.26C7.53,6.19,9.63,5,12,5c3.86,0,7,3.14,7,7H21z M10.86,18.91C7.87,18.42,5.51,16.01,5.08,13H3.06c0.5,4.5,4.31,8,8.94,8 c0.02,0,0.05,0,0.07,0L10.86,18.91z"/></g></svg></span>
						</a>
					</li>
					<li>
						<a href="javascript:void(0);" class="text-reset text-decoration-none flowmattic-toggle-steps collapsed" data-toggle="tooltip" title="Toggle All Steps" data-placement="right">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path stroke-width="1.3" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
								<path stroke-width="1.3" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
							</svg>
						</a>
					</li>
				</ul>
			</nav>
			<div class="flowmattic-sidebar-content hidden">
				<div id="flowmattic-sidebar-outline" class="flowmattic-sidebar-section">
					<h4 class="fm-sidebar-heading">
						<span>Outline</span>
						<a href="javascript:void(0);" class="flowmattic-close-sidebar">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path d="M22.7071 1.29289C23.0976 1.68342 23.0976 2.31658 22.7071 2.70711L2.70711 22.7071C2.31658 23.0976 1.68342 23.0976 1.29289 22.7071C0.902369 22.3166 0.902369 21.6834 1.29289 21.2929L21.2929 1.29289C21.6834 0.902369 22.3166 0.902369 22.7071 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
								<path d="M1.29289 1.29289C1.68342 0.902369 2.31658 0.902369 2.70711 1.29289L22.7071 21.2929C23.0976 21.6834 23.0976 22.3166 22.7071 22.7071C22.3166 23.0976 21.6834 23.0976 21.2929 22.7071L1.29289 2.70711C0.902369 2.31658 0.902369 1.68342 1.29289 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
							</svg>
						</a>
					</h4>
					<div class="fm-sidebar-outline-content">
						<ul class="fm-workflow-outline">
						</ul>
					</div>
				</div>
				<div id="flowmattic-sidebar-settings" class="flowmattic-sidebar-section">
					<h4 class="fm-sidebar-heading">
						<span>Settings</span>
						<a href="javascript:void(0);" class="flowmattic-close-sidebar">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path d="M22.7071 1.29289C23.0976 1.68342 23.0976 2.31658 22.7071 2.70711L2.70711 22.7071C2.31658 23.0976 1.68342 23.0976 1.29289 22.7071C0.902369 22.3166 0.902369 21.6834 1.29289 21.2929L21.2929 1.29289C21.6834 0.902369 22.3166 0.902369 22.7071 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
								<path d="M1.29289 1.29289C1.68342 0.902369 2.31658 0.902369 2.70711 1.29289L22.7071 21.2929C23.0976 21.6834 23.0976 22.3166 22.7071 22.7071C22.3166 23.0976 21.6834 23.0976 21.2929 22.7071L1.29289 2.70711C0.902369 2.31658 0.902369 1.68342 1.29289 1.29289Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path>
							</svg>
						</a>
					</h4>
					<div class="fm-sidebar-settings-content">
						<div class="fm-sidebar-setting">
							<label for="sidebar-workflow-name"><?php echo esc_html__( 'Workflow Name', 'flowmattic' ); ?></label>
							<input type="text" id="sidebar-workflow-name" name="sidebar-workflow-name" class="sidebar-workflow-name" value="<?php echo esc_attr( $name ); ?>" />
						</div>
						<div class="fm-sidebar-setting">
							<label for="sidebar-workflow-folder"><?php echo esc_html__( 'Add to Folder...', 'flowmattic' ); ?></label>
							<input type="text" id="sidebar-workflow-folder" name="workflow-folder" class="sidebar-workflow-folder" value="<?php echo esc_attr( $folder ); ?>" />
							<ul class="fm-folders-list list-group" style="display:none;">
								<?php
								foreach ( $folders as $folder_id => $key ) {
									echo '<li class="list-group-item m-0" role="button" onclick="jQuery(\'#sidebar-workflow-folder\' ).val(\'' . $folder_id . '\' ); jQuery( this ).parent().hide(); ">' . $folder_id . '</li>';
								}
								?>
							</ul>
						</div>
						<div class="fm-sidebar-setting">
							<label for="sidebar-workflow-manager"><?php echo esc_html__( 'Workflow Access', 'flowmattic' ); ?></label>
							<input type="text" name="workflow-manager" class="workflow-input workflow-manager" value="<?php echo esc_attr( $workflow_manager ); ?>" />
						</div>
						<div class="fm-sidebar-setting">
							<label for="sidebar-workflow-description"><?php echo esc_html__( 'Workflow Description', 'flowmattic' ); ?></label>
							<textarea rows="4" id="sidebar-workflow-description" name="workflow-description" class="sidebar-workflow-description"><?php echo esc_attr( $description ); ?></textarea>
						</div>
						<div class="fm-sidebar-setting">
							<label for="sidebar-workflow-request-queue"><?php echo esc_html__( 'Webhook Request Queue', 'flowmattic' ); ?></label>
							<select id="sidebar-workflow-request-queue" name="webhook-queue" title="Enable or Disable webhook queue">
								<option <?php echo ( 'enable' === $webhook_queue ) ? 'selected' : ''; ?> value="enable"><?php esc_html_e( 'Enable', 'flowmattic' ); ?></option>
								<option <?php echo ( 'disable' === $webhook_queue ) ? 'selected' : ''; ?> value="disable"><?php esc_html_e( 'Disable', 'flowmattic' ); ?></option>
							</select>
							<small class="form-text mt-2">
								<?php esc_html_e( 'Enable to use queue for simultaneous requests to this workflow. Works only if the trigger is based on webhook.', 'flowmattic' ); ?>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="flowmattic-container flowmattic-edit-wrapper">
		<div id="flowmattic-workflow-container">
		</div>
	</div>
</div>
<?php FlowMattic_Admin::footer(); ?>
