<?php
/**
 * FlowMattic Chatbot embed file.
 *
 * @package flowmattic
 * @since 4.0
 */

remove_all_actions( 'wp_head' );
remove_all_actions( 'wp_footer' );

// Add print scripts action.
add_action( 'wp_head', 'wp_print_scripts', 1 );
add_action( 'wp_head', 'wp_print_styles', 1 );

// Add action to enqueue scripts.
add_action( 'wp_print_scripts', 'flowmattic_chatbot_enqueue_scripts' );

/**
 * Enqueue scripts.
 *
 * @since 4.0
 * @access public
 */
function flowmattic_chatbot_enqueue_scripts() {
	wp_enqueue_script( 'flowmattic-chatbot', FLOWMATTIC_PLUGIN_URL . 'assets/admin/min/flowmattic-chatbot.min.js', array( 'jquery' ), FLOWMATTIC_VERSION, true );

	// Localize script for the flowmattic admin pages.
	wp_localize_script(
		'flowmattic-chatbot',
		'FMConfig',
		array(
			'chatbotAjax'   => rest_url( 'flowmattic/v1/ajax/' ),
			'chatbot_nonce' => wp_create_nonce( 'wp_rest' ),
			'chatbot_auth'  => wp_create_nonce( 'flowmattic_chatbot_auth' ),
		)
	);
}

// Add action to enqueue styles.
add_action( 'wp_print_styles', 'flowmattic_chatbot_enqueue_styles' );

/**
 * Enqueue styles.
 *
 * @since 4.0
 * @access public
 */
function flowmattic_chatbot_enqueue_styles() {
	// Enqueue Open Sans font.
	wp_enqueue_style( 'open-sans-google-font', 'https://fonts.googleapis.com/css?display=swap&family=Open+Sans:100,200,300,400,600,bold', '', FLOWMATTIC_VERSION );

	// Enqueue Bootstrap CSS.
	wp_enqueue_style( 'flowmattic-bootstrap', FLOWMATTIC_PLUGIN_URL . 'assets/admin/css/min/bootstrap.min.css', '', FLOWMATTIC_VERSION );
}
?>
<?php
// Assign the chatbot ID.
$chatbot_id = isset( $_GET['chatbot_id'] ) ? base64_decode( sanitize_text_field( wp_unslash( $_GET['chatbot_id'] ) ) ) : '';

// Get the chatbot data.
$chatbot_object = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

// Return error if chatbot is not found.
if ( empty( $chatbot_object ) ) {
	echo esc_attr__( 'Chatbot not found.', 'flowmattic' );
	return;
}

// Get the chatbot data.
$chatbot_name     = ! empty( $chatbot_object ) ? $chatbot_object[0]->chatbot_name : 'FlowMattic Chatbot';
$chatbot_data     = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_data ) : array();
$chatbot_settings = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_settings ) : array();
$chatbot_actions  = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_actions ) : array();
$chatbot_styles   = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_styles ) : array();

// Get the chatbot connect.
$chatbot_connect = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

// Get the chatbot assistant.
$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

// Get user input placeholder.
$chatbot_user_placeholder = isset( $chatbot_settings->chatbot_user_placeholder ) ? $chatbot_settings->chatbot_user_placeholder : '';

// Get welcome text.
$chatbot_welcome_text = isset( $chatbot_settings->chatbot_welcome_text ) ? $chatbot_settings->chatbot_welcome_text : '';

// Get instructions.
$chatbot_instructions = isset( $chatbot_settings->chatbot_instructions ) ? $chatbot_settings->chatbot_instructions : '';

// Get the chatbot model.
$chatbot_model = isset( $chatbot_settings->chatbot_model ) ? $chatbot_settings->chatbot_model : 'gpt-4-turbo';

// Get load user chats.
$load_user_chats = isset( $chatbot_settings->load_user_chats ) ? $chatbot_settings->load_user_chats : 'yes';

// Get the page background color.
$chatbot_page_background = isset( $chatbot_styles->chatbot_page_background ) ? $chatbot_styles->chatbot_page_background : '#f0f8ff';

// Get the chatbot message background color.
$chatbot_message_background = isset( $chatbot_styles->chatbot_message_background ) ? $chatbot_styles->chatbot_message_background : '#0d6efd';

// Convert the chatbot message background color to light.
$chatbot_message_background_light = flowmattic_hex_to_hsl( $chatbot_message_background );

// Get the user message background color.
$user_message_background = isset( $chatbot_styles->chatbot_user_message_background ) ? $chatbot_styles->chatbot_user_message_background : '#fff';

// Set the loading animation type.
$loading_animation_type = isset( $chatbot_styles->loading_animation_type ) ? $chatbot_styles->loading_animation_type : 'spinner';

// Get chatbox icon image.
$chatbox_icon = isset( $chatbot_styles->chatbox_icon ) ? $chatbot_styles->chatbox_icon : FLOWMATTIC_PLUGIN_URL . 'assets/admin/img/chat-icon.svg';
?>
<!DOCTYPE html>
<html class="flowmattic-chatbot-html">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo esc_attr( $chatbot_name ); ?> Chatbot</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body>
	<div class="flowmattic-chatbot">
		<div class="flowmattic-chatbot-body p-3">
			<div class="flowmattic-chatbot-context">
				<div class="flowmattic-chatbot-messages">
					<div class="flowmattic-chatbot-message message-node-ai">
						<div class="flowmattic-chatbot-message-content">
							<div class="flowmattic-chatbot-system-user">
								<?php echo esc_attr( $chatbot_name ); ?>
							</div>
							<div class="flowmattic-chatbot-message-text"><?php echo esc_attr( $chatbot_welcome_text ); ?></div>
						</div>
					</div>
					<div class="flowmattic-chatbot-message-holder"></div>
				</div>
				<div class="jump-btn-container d-flex align-items-center justify-content-center position-relative">
					<button class="position-absolute d-flex bottom-0 mb-5 rounded-pill border border-solid btn btn-light btn-md gap-2 pe-3 jump-to-latest" type="button"><span aria-hidden="true" class="currentColor">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="22" width="22" size="22" color="currentColor" name="arrowBigDown"><path fill="#2D2E2E" d="m11 16.86-6-5.04v2.62l7 5.86 7-5.86v-2.62l-6 5.04V4h-2v12.86Z"></path></svg></span>
						<?php echo esc_attr__( 'Jump to latest', 'flowmattic' ); ?>
					</button>
				</div>
			</div> <!-- .flowmattic-chatbot-context -->
			<form id="flowmattic-chatbot-user-input">
				<input type="hidden" id="fm_chatbot_id" class="form-control" value="<?php echo esc_attr( base64_encode( $chatbot_id ) ); // @codingStandardsIgnoreLine ?>">
				<input type="hidden" id="fm_chatbot_name" class="form-control" value="<?php echo esc_attr( $chatbot_name ); ?>">
				<input type="hidden" id="loading_animation_type" class="form-control" value="<?php echo esc_attr( $loading_animation_type ); ?>">
				<input type="hidden" id="load_user_chats" class="form-control" value="<?php echo esc_attr( $load_user_chats ); ?>">
				<input type="hidden" id="fm_chatbot_status" class="form-control" value="live">
				<div class="flowmattic-chatbot-input mb-0 d-flex align-items-baseline mb-4 ms-4 me-2">
					<textarea class="w-100 border-0 p-0 shadow-none user-input-textarea" id="chatbot_input_field" rows="1" placeholder="<?php echo esc_attr( $chatbot_user_placeholder ); ?>" style="height: 24px !important;"></textarea>
					<button class="fm-send-message-btn border-0 p-0 btn btn-link" id="chatbot_input_button" style="height: 24px !important;"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg></button>
				</div>
			</form>
		</div> <!-- .flowmattic-chatbot-body -->
	</div>
<style type="text/css">
:root {
	--chatbot_page_background: <?php echo esc_attr( $chatbot_page_background ); ?>;
	--chatbot_message_background: <?php echo esc_attr( $chatbot_message_background ); ?>;
	--chatbot_message_background_light: <?php echo esc_attr( $chatbot_message_background_light ); ?>;
	--chatbot_user_message_background: <?php echo esc_attr( $user_message_background ); ?>;
	--chatbot_user_message_color: #000;
	--chatbot_user_message_border_color: #e5e5e5	
}
.flowmattic-chatbot {
	min-height: 100vh;
	display: flex;
	width: 100%;
	position: relative;
	height: 100%;
}
.flowmattic-chatbot-body {
	width: 100%;
	background: var(--chatbot_page_background);
}
.flowmattic-chatbot-context {
	max-width: 800px;
	margin: 0 auto;
	max-height: calc(100vh - 180px);
	height: initial;
}
#flowmattic-chatbot-user-input {
	max-width: 800px;
	margin: 0 auto;
}
.spin-icon {
	animation: spin 1s linear infinite;
	font-size: 24px;
}

@keyframes spin {
	100% {
		transform: rotate(1turn);
	}
}
@keyframes slideFromBottom {
	from {
		transform: translateY(30%);
		opacity: 0;
	}
	to {
		transform: translateY(0);
		opacity: 1;
	}
}

.flowmattic-chatbot-messages,
.flowmattic-chatbot-message-content,
.flowmattic-chatbot-message-holder,
.flowmattic-chatbot-context {
	display: flex;
	flex-direction: column;
	gap: 20px;
}
.flowmattic-chatbot-context .flowmattic-chatbot-messages {
	padding-top: 30px;
	height: 100%;
	overflow-y: auto;
	scrollbar-width: none; 
	-ms-overflow-style: none;
}
.flowmattic-chatbot-context .flowmattic-chatbot-messages::-webkit-scrollbar {
	display: none;
}
.flowmattic-chatbot-context p {
	margin: 0;
	line-height: 24px;
}
.flowmattic-chatbot-message {
	padding: 17px 25px;
	border-radius: 8px;
	animation: slideFromBottom 0.35s ease-out forwards;
}
.flowmattic-chatbot-message-holder.scrollable .flowmattic-chatbot-message {
	animation: none;
}
.flowmattic-chatbot-message.message-node-ai {
	background: var( --chatbot_message_background_light );
	border: 1px solid var( --chatbot_message_background );
	margin-right: 20px;
	font-size: 14px;
}
.copy-ai-response {
	margin-right: -10px;
	color: var( --chatbot_message_background );
	z-index: 9;
	position: relative;
	opacity: 0;
	transition-property: opacity;
	transition-timing-function: cubic-bezier(.4,0,.2,1);
	transition-duration: .25s;
}
.copy-ai-response .click-to-copy {
	cursor: pointer;
}
.message-node-ai.responded:hover .copy-ai-response {
	opacity: 1;
}
.click-to-copy {
	cursor: pointer;
}
.flowmattic-chatbot-message.message-node-user {
	background: var( --chatbot_user_message_background );
	border: 1px solid #e1e6ef;
	margin-left: 20px;
	padding: 20px 25px;
}
.flowmattic-chatbot-message-text {
	font-size: 16px;
	white-space: pre-line;
	word-break: break-word;
}
.flowmattic-chatbot-message-text table {
	width: 100%;
}
.flowmattic-chatbot-message-text >strong {
	display: block;
}
.message-node-ai .flowmattic-chatbot-message-text {
	padding-right: 20px;
}
.flowmattic-chatbot-system-user {
	font-size: 14px;
	font-weight: bold;
	line-height: 17px;
}

.flowmattic-chatbot-message-content {
	gap: 10px;
}
.user-input-textarea {
	resize: none;
	overflow: none;
	box-shadow: none !important;
	border: none !important;
	outline: none !important;
	background: transparent;
}
.flowmattic-chatbot-input {
	transition-property: color,background-color,border-color,text-decoration-color,fill,stroke,opacity,box-shadow,transform,filter,backdrop-filter,-webkit-backdrop-filter;
	transition-timing-function: cubic-bezier(.4,0,.2,1);
	transition-duration: .15s;
	border: 1px solid #e1e6ef;
	border-radius: 8px;
	padding: 20px;
	background: var( --chatbot_user_message_background );
}
.flowmattic-chatbot-input:focus-within {
	border: 1px solid #e1e6ef;
	overflow: hidden;
	box-shadow: #fff 0px 0px 0px 0px, var( --chatbot_message_background ) 0px 0px 0px 1px, rgba(0, 0, 0, 0) 0px 0px 0px 0px;
}
.flowmattic-chatbot-iframe .flowmattic-chatbot-context {
	max-height: calc( 100vh - 85px ) !important;
}
.flowmattic-chatbot-iframe .flowmattic-chatbot-context .flowmattic-chatbot-messages {
	padding-top: 15px;
	gap: 20px;
}
.flowmattic-chatbot-iframe .flowmattic-chatbot-input {
	position: absolute;
	margin: 0 !important;
	bottom: 5px;
	width: calc( 100% - 10px );
	max-width: 800px;
	margin-left: -12px !important;
	margin-right: -12px !important;
}
#chatbot_input_button {
	color: var( --chatbot_message_background );
}
.jump-to-latest {
	opacity: 0;
	visibility: hidden;
	transition-property: opacity;
	transition-timing-function: cubic-bezier(.4,0,.2,1);
	transition-duration: .25s;
}
.jump-btn-container.show .jump-to-latest {
	opacity: 1;
	visibility: visible;
}
#fm-ai-wave {
	position:relative;
	width:100px;
}
	
#fm-ai-wave .fm-loading-dot {
	display:inline-block;
	width: 8px;
	height: 8px;
	border-radius:50%;
	margin-right: 5px;
	background: #616161;
	animation: aiwave 1s linear infinite;
}

#fm-ai-wave .fm-loading-dot:nth-child(2) {
	animation-delay: -0.8s;
}

#fm-ai-wave .fm-loading-dot:nth-child(3) {
	animation-delay: -0.6s;
}

@keyframes aiwave {
	0%, 60%, 100% {
		transform: initial;
	}

	30% {
		transform: translateY(-10px);
	}
}
</style>
<script type="text/javascript">
	// Detect if the page is in an iframe. Add a class to html tag.
	if ( window.self !== window.top ) {
		// Add a class to html tag if the page is in an iframe.
		document.documentElement.classList.add( 'flowmattic-chatbot-iframe' );
	}
</script>
</body>
</html>
