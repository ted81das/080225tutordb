<?php
/**
 * FlowMattic Chatbot Settings Page.
 *
 * @package FlowMattic
 * @since   4.0
 * @version 5.0
 */

// Get the license key.
$license_key = get_option( 'flowmattic_license_key', '' );

// Check the license.
$license = wp_flowmattic()->check_license();

// If the license is not valid, redirect to the license page.
if ( '' === $license_key ) {
	wp_safe_redirect( admin_url( '/admin.php?page=flowmattic-license' ) );
	exit;
}

// Assign the chatbot ID.
$chatbot_id = isset( $_GET['assistant_id'] ) ? sanitize_text_field( wp_unslash( base64_decode( $_GET['assistant_id'] ) ) ) : 'fbchatbot01';

// Get current user.
$fm_current_user = wp_get_current_user();

// Get current User ID.
$user_id = $fm_current_user->ID;

// Get the user secure key.
$secure_key = $fm_current_user->user_pass;

// Get the chatbot data.
$chatbot_object = wp_flowmattic()->chatbots_db->get_chatbot_by_id( $chatbot_id );

// Get the chatbot data.
$chatbot_name     = ! empty( $chatbot_object ) ? $chatbot_object[0]->chatbot_name : 'FlowMattic Chatbot';
$chatbot_data     = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_data ) : array();
$chatbot_settings = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_settings ) : array();
$chatbot_actions  = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_actions ) : array();
$chatbot_styles   = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_styles ) : array();

// Get all connects.
$all_connects = wp_flowmattic()->connects_db->get_all();

// Get the chatbot connect.
$chatbot_connect = isset( $chatbot_settings->chatbot_connect ) ? $chatbot_settings->chatbot_connect : '';

if ( '' !== $chatbot_connect ) {
	// Get the connect data.
	$connect_args = array(
		'connect_id' => $chatbot_connect,
	);

	// Get the connect data from db.
	$connect = wp_flowmattic()->connects_db->get( $connect_args );

	// Get the connect data.
	$connect_data = $connect->connect_settings;

	// Get the token.
	$token = $connect_data['auth_bearer_token'];
} else {
	$token = '';
}

// Get the chatbot assistant.
$chatbot_assistant = isset( $chatbot_settings->chatbot_assistant ) ? $chatbot_settings->chatbot_assistant : '';

// Get the chatbot description.
$chatbot_description = isset( $chatbot_settings->chatbot_description ) ? $chatbot_settings->chatbot_description : '';

// Get user input placeholder.
$chatbot_user_placeholder = isset( $chatbot_settings->chatbot_user_placeholder ) ? $chatbot_settings->chatbot_user_placeholder : '';

// Get welcome text.
$chatbot_welcome_text = isset( $chatbot_settings->chatbot_welcome_text ) ? $chatbot_settings->chatbot_welcome_text : '';

// Get instructions.
$chatbot_instructions = isset( $chatbot_settings->chatbot_instructions ) ? $chatbot_settings->chatbot_instructions : '';

// Get default reply.
$chatbot_default_reply = isset( $chatbot_settings->chatbot_default_reply ) ? $chatbot_settings->chatbot_default_reply : '';

// Get load user chats.
$load_user_chats = isset( $chatbot_settings->load_user_chats ) ? $chatbot_settings->load_user_chats : 'yes';

// Get the chatbot model.
$chatbot_model = isset( $chatbot_settings->chatbot_model ) ? $chatbot_settings->chatbot_model : 'gpt-4-turbo';

// Get the chatbot action event.
$chatbot_action_event = isset( $chatbot_actions[0]->chatbot_action_event ) ? $chatbot_actions[0]->chatbot_action_event : 'chatbot_responded';

// Get the chatbot action type.
$chatbot_action_type = isset( $chatbot_actions[0]->chatbot_action_type ) ? $chatbot_actions[0]->chatbot_action_type : 'trigger_workflow';

// Get the chatbot action workflow ID.
$chatbot_action_workflow_id = isset( $chatbot_actions[0]->chatbot_action_workflow_id ) ? $chatbot_actions[0]->chatbot_action_workflow_id : '';

// Get the chatbot action webhook URL.
$chatbot_action_webhook_url = isset( $chatbot_actions[0]->chatbot_action_webhook_url ) ? $chatbot_actions[0]->chatbot_action_webhook_url : '';

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
<div class="wrap flowmattic-wrap about-wrap flowmattic-chatbot-wrap">
	<div class="flowmattic-wrapper d-flex">
		<div class="flowmattic-dashboard-content container p-0 m-0 mw-100">
			<div class="row m-0">
				<div class="flowmattic-chatbot-header-wrapper">
					<div class="flowmattic-chatbot-header">
						<nav class="flowmattic-chatbot-header_nav d-flex align-items-center justify-content-between">
							<a href="<?php echo esc_attr( admin_url( '/admin.php?page=flowmattic-ai-assistants' ) ); ?>" class="btn flowmattic-dashboard-button d-flex" title="<?php echo esc_attr__( 'Back to FlowMattic AI Assistants', 'flowmattic' ); ?>">
								<h3 class="d-inline-block m-0 back-to-dashboard">
									<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="#95928e"><path d="M0 0h24v24H0z" fill="none"/><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
									<span class="ps-2"><?php echo esc_html__( 'FlowMattic AI Assistants', 'flowmattic' ); ?></span>
								</h3>
							</a>
							<div class="flowmattic-chatbot-name ps-2 pe-2">
								<h3 class="m-0"><?php echo esc_html( $chatbot_name ); ?></h3>
							</div>
							<div class="flowmattic-chatbot-help pe-3">
								<h3 class="m-0">
									<a href="https://flowmattic.com/features/ai-chatbot" target="_blank" class="text-reset text-decoration-none">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="16" width="16" size="16" color="neutral600" name="navHelp"><path fill="#2D2E2E" d="M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10 10-4.49 10-10S17.51 2 12 2Zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8Z"></path><path fill="#2D2E2E" d="M12.01 18.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM12.36 6.13c-1.18-.01-2.19.3-2.98.67v1.98c.62-.4 1.65-.9 2.92-.9h.05c1.3.01 2.07.72 2.14 1.38.08.88-.96 1.67-2.65 2.01l-.7.14.01 3.08h1.75v-1.68c2.64-.76 3.47-2.38 3.34-3.73-.18-1.68-1.81-2.93-3.88-2.95Z"></path></svg>
										<?php echo esc_html__( 'Help', 'flowmattic' ); ?>
									</a>
								</h3>
							</div>
						</nav>
					</div>
				</div>
				<div class="flowmattic-chatbot-body-wrapper d-grid p-0">
					<!-- Chatbot settings wrapper -->
					<div class="flowmattic-chatbot-settings-wrapper border-end">
						<ul class="nav nav-tabs border-bottom ps-3 pe-3">
							<li class="nav-item m-0">
								<a class="nav-link active" data-toggle="tab" aria-controls="setup" data-target="#setup" aria-current="page" href="#"><?php echo esc_html__( 'Setup', 'flowmattic' ); ?></a>
							</li>
							<li class="nav-item m-0">
								<a class="nav-link" data-toggle="tab" aria-controls="content" data-target="#content" href="#"><?php echo esc_html__( 'Content', 'flowmattic' ); ?></a>
							</li>
							<li class="nav-item m-0">
								<a class="nav-link" data-toggle="tab" aria-controls="actions" data-target="#actions" href="#"><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></a>
							</li>
							<li class="nav-item m-0">
								<a class="nav-link" data-toggle="tab" aria-controls="style" data-target="#style" href="#"><?php echo esc_html__( 'Style', 'flowmattic' ); ?></a>
							</li>
							<li class="nav-item m-0">
								<a class="nav-link" data-toggle="tab" aria-controls="conversations" data-target="#conversations" href="#"><?php echo esc_html__( 'Conversations', 'flowmattic' ); ?></a>
							</li>
						</ul>
						<div class="tab-content p-0">
							<div class="tab-pane fade show active" id="setup" role="tabpanel" aria-labelledby="setup-tab">
								<form id="chatbot-setup" class="p-3">
									<input type="hidden" id="chatbot_id" class="form-control" value="<?php echo esc_attr( $chatbot_id ); ?>">
									<div class="mb-4">
										<label for="flowmattic_chatbot_name" class="form-label fw-bold"><?php echo esc_html__( 'Chatbot Name', 'flowmattic' ); ?></label>
										<input type="search" class="form-control border-dark" id="flowmattic_chatbot_name" value="<?php echo esc_attr( $chatbot_name ); ?>">
									</div>
									<div class="mb-4">
										<label for="chatbot_model_connect" class="form-label fw-bold"><?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?></label>
										<select id="chatbot_model_connect" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="<?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?>" title="<?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?>" data-live-search="true">
											<?php
											foreach ( $all_connects as $key => $connect_item ) {
												$connect_id   = $connect_item->id;
												$connect_name = $connect_item->connect_name;
												$selected     = $connect_id === $chatbot_connect ? 'selected' : '';
												?>
												<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
												<?php
											}
											?>
										</select>
										<div class="form-text">
											<?php echo esc_attr__( 'Choose the FlowMattic Connect to use with Chatbot. Make sure to connect your OpenAI account', 'flowmattic' ); ?> <a href="<?php echo esc_attr( admin_url( '/admin.php?page=flowmattic-connects' ) ); ?>" target="_blank"><?php echo esc_attr__( 'here', 'flowmattic' ); ?></a>
										</div>
									</div>
									<div class="mb-4">
										<label for="assistant_description" class="form-label fw-bold"><?php echo esc_html__( 'Description', 'flowmattic' ); ?></label>
										<textarea class="form-control border-dark" id="assistant_description" rows="1" placeholder="<?php echo esc_attr__( 'Ex. AI assistant for ACME Inc.', 'flowmattic' ); ?>"><?php echo esc_textarea( $chatbot_description ); ?></textarea>
										<div class="form-text"><?php echo esc_attr__( 'The description of the assistant. The maximum length is 512 characters', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-4">
										<label for="chatbot_assistant" class="form-label fw-bold"><?php echo esc_html__( 'Choose Assistant', 'flowmattic' ); ?></label>
										<div class="d-flex flex-row align-items-center">
											<select id="chatbot_assistant" data-assistant-id="<?php echo esc_attr( $chatbot_assistant ); ?>" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="<?php echo esc_html__( 'Choose Assistant', 'flowmattic' ); ?>" title="<?php echo esc_html__( 'Choose Assistant', 'flowmattic' ); ?>" data-live-search="true">
											</select>
											<div class="refresh-assistants btn btn-refresh btn-outline-secondary ms-2 p-2 d-flex">
												<svg width="20" height="22" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
											</div>
										</div>
										<div class="form-text">
											<?php echo esc_attr__( 'Choose the assistant from the list of your OpenAI assistants. Or Click the button to create new one.', 'flowmattic' ); ?>
											<button type="button" class="btn btn-outline-primary btn-sm ms-2" data-toggle="modal" data-target="#createAssistantModal"><?php echo esc_html__( 'Create Assistant', 'flowmattic' ); ?></button>
										</div>
									</div>
									<div class="mb-4">
										<label for="chatbot_welcome_text" class="form-label fw-bold"><?php echo esc_html__( 'Welcome Text', 'flowmattic' ); ?></label>
										<textarea class="form-control border-dark" id="chatbot_welcome_text" rows="1" placeholder="<?php echo esc_attr__( 'How can I help you today?', 'flowmattic' ); ?>"><?php echo esc_textarea( $chatbot_welcome_text ); ?></textarea>
									</div>
									<div class="mb-4">
										<label for="chatbot_user_placeholder" class="form-label fw-bold"><?php echo esc_html__( 'User Input Placeholder', 'flowmattic' ); ?></label>
										<input type="search" class="form-control border-dark" id="chatbot_user_placeholder" placeholder="<?php echo esc_attr__( 'Ask me anything...', 'flowmattic' ); ?>" value="<?php echo esc_attr( $chatbot_user_placeholder ); ?>">
									</div>
									<div class="mb-4">
										<label for="chatbot_instructions" class="form-label fw-bold"><?php echo esc_html__( 'Instructions', 'flowmattic' ); ?></label>
										<textarea class="form-control border-dark" id="chatbot_instructions" rows="3" placeholder="<?php echo esc_attr__( 'You are an AI chatbot. You are a helpful assistant.', 'flowmattic' ); ?>"><?php echo esc_textarea( $chatbot_instructions ); ?></textarea>
										<div class="form-text"><?php echo esc_attr__( 'The system instructions that the assistant uses. The maximum length is 32768 characters', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-4 d-none">
										<label for="chatbot_default_reply" class="form-label fw-bold"><?php echo esc_html__( 'Default Reply', 'flowmattic' ); ?></label>
										<textarea class="form-control border-dark disabled" id="chatbot_default_reply" rows="1" placeholder="<?php echo esc_attr__( 'I am sorry, I do not understand. Please try again.', 'flowmattic' ); ?>"></textarea>
										<div class="form-text"><?php echo esc_attr__( 'AI will attempt to use the default reply, when the assistant does not find any information for user question in the content sources. Leave empty to let AI generate the responce.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-4">
										<label for="chatbot_model" class="form-label fw-bold"><?php echo esc_html__( 'AI Model', 'flowmattic' ); ?></label>
										<select id="chatbot_model" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="Choose model" title="Choose model">
											<?php
											// Set the AI models with available tools.
											$default_models = array(
												'gpt-4o'                 => 'gpt-4o',
												'gpt-4o-mini'            => 'gpt-4o-mini',
												'gpt-4-turbo'            => 'gpt-4-turbo',
												'gpt-3.5-turbo'          => 'gpt-3.5-turbo',
												'gpt-4'                  => 'gpt-4',
												'gpt-4o-mini-2024-07-18' => 'gpt-4o-mini-2024-07-18',
												'gpt-4o-2024-11-20'      => 'gpt-4o-2024-11-20',
												'gpt-4o-2024-08-06'      => 'gpt-4o-2024-08-06',
												'gpt-4o-2024-05-13'      => 'gpt-4o-2024-05-13',
												'gpt-4-turbo-preview'    => 'gpt-4-turbo-preview',
												'gpt-4-turbo-2024-04-09' => 'gpt-4-turbo-2024-04-09',
												'gpt-4-1106-preview'     => 'gpt-4-1106-preview',
												'gpt-4-0613'             => 'gpt-4-0613',
												'gpt-4-0125-preview'     => 'gpt-4-0125-preview',
												'gpt-3.5-turbo-16k'      => 'gpt-3.5-turbo-16k',
												'gpt-3.5-turbo-1106'     => 'gpt-3.5-turbo-1106',
												'gpt-3.5-turbo-0125'     => 'gpt-3.5-turbo-0125',
											);

											// Get the AI Models from API.
											$ai_models = flowmattic_get_openai_models( $token );

											// Fallback to default models if API is WP_Error.
											if ( is_wp_error( $ai_models ) ) {
												$ai_models = $default_models;
											}

											foreach ( $ai_models as $ai_model ) {
												$selected = $ai_model === $chatbot_model ? 'selected' : '';
												?>
												<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $ai_model ); ?>">
													<?php echo esc_attr( $ai_model ); ?>
												</option>
												<?php
											}
											?>
										</select>
										<div class="form-text"><?php echo esc_attr__( 'AI Models are subject to availibility, as per your OpenAI account. If you are using files to train your assistant, make sure to select the model that works with the retrieval tool in OpenAI.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-4">
										<label for="load_user_chats" class="form-label fw-bold"><?php echo esc_html__( 'Load User Session Chats', 'flowmattic' ); ?></label>
										<select id="load_user_chats" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="Choose option" title="Choose option">
											<?php
											$options = array(
												'yes' => esc_html__( 'Yes', 'flowmattic' ),
												'no'  => esc_html__( 'No', 'flowmattic' ),
											);

											foreach ( $options as $option => $label ) {
												$selected = $option === $load_user_chats ? 'selected' : '';
												?>
												<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $option ); ?>">
													<?php echo esc_attr( $label ); ?>
												</option>
												<?php
											}
											?>
										</select>
										<div class="form-text"><?php echo esc_attr__( 'FlowMattic remembers the user thread ID in sessions. Selecting "Yes" will load the messages of the respective user when they re-visit the chat box. Default: Yes', 'flowmattic' ); ?></div>
									</div>
									<div class="d-grid gap-2 save-button border-top pt-3 mt-3 position-sticky bottom-0 bg-white pb-3">
										<button class="mt-2 btn btn-outline-primary fm-save-chatbot-settings btn-lg disabled" type="button"><?php echo esc_html__( 'Save Changes', 'flowmattic' ); ?></button>
									</div>
								</form>
							</div>
							<div class="tab-pane fade p-3" id="content" role="tabpanel" aria-labelledby="content-tab">
								<div class="section-header d-flex flex-column gap-2">
									<strong><?php echo esc_html__( 'Content Sources', 'flowmattic' ); ?></strong>
									<p><?php echo esc_html__( 'Upload and manage the content your chatbot needs to understand your business, or project to answer questions.', 'flowmattic' ); ?></p>
								</div>
								<div class="content-sources-wrapper">
									<div class="content-sources d-flex flex-column gap-2">
										<?php
										$all_content_sources = ! empty( $chatbot_object ) ? json_decode( $chatbot_object[0]->chatbot_data ) : array();

										if ( ! empty( $all_content_sources ) ) {
											foreach ( $all_content_sources as $key => $content_source ) {
												$file_id     = $content_source->file_id;
												$file_name   = $content_source->file_name;
												$description = $content_source->description;
												?>
												<div class="content-source-item d-flex flex-row align-items-top justify-content-between p-2 border rounded gap-2">
													<div class="content-source-info d-flex flex-column gap-1">
														<div class="content-source-name d-flex flex-column gap-1">
															<span class="file-name fw-bold"><?php echo esc_attr( $file_name ); ?></span>
															<small class="file-id">FILE ID: <?php echo esc_attr( $file_id ); ?></small>
														</div>
														<div class="content-source-description">
															<?php echo esc_attr( $description ); ?>
														</div>
													</div>
													<div class="content-source-actions">
														<button type="button" class="btn btn-outline-danger btn-sm btn-delete-source" data-file-id="<?php echo esc_attr( $file_id ); ?>"><?php echo esc_html__( 'Delete', 'flowmattic' ); ?></button>
													</div>
												</div>
												<?php
											}
										}
										?>
									</div>
									<div class="content-sources-footer mt-3">
										<button type="button" class="btn btn-outline-primary btn-sm d-flex gap-2" data-toggle="modal" data-target="#addContentSourceModal">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24" width="24" size="24" fill="currentColor"><path d="M13 19v-6h6v-2h-6V5h-2v6H5v2h6v6h2Z"></path></svg>
											<?php echo esc_html__( 'Add Source', 'flowmattic' ); ?>
										</button>
									</div>
								</div>
							</div>
							<div class="tab-pane fade p-3" id="actions" role="tabpanel" aria-labelledby="actions-tab">
								<div class="section-header d-flex flex-column gap-2">
									<strong><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></strong>
									<p><?php echo esc_html__( 'Trigger workflows or actions when chatbot response is generated.', 'flowmattic' ); ?></p>
								</div>
								<div class="actions-wrapper">
									<form id="form-actions">
										<div class="mb-4">
											<label class="form-label fw-bold"><?php echo esc_html__( 'Event', 'flowmattic' ); ?></label>
											<div class="d-block">
												<select id="chatbot_action_event" class="form-select form-control w-100 mw-100 form-select-md border border-dark disabled" aria-label="Choose option" title="Choose option">
													<option value="chatbot_responded" selected><?php echo esc_html__( 'Chatbot Response Generated', 'flowmattic' ); ?></option>
												</select>
											</div>
										</div>
										<div class="mb-4">
											<label class="form-label fw-bold"><?php echo esc_html__( 'Action', 'flowmattic' ); ?></label>
											<div class="d-block">
												<select id="chatbot_action_type" class="form-select form-control w-100 mw-100 form-select-md border border-dark disabled" aria-label="Choose option" title="Choose option">
													<?php
													$all_actions = array(
														'trigger_workflow' => esc_html__( 'Trigger Workflow', 'flowmattic' ),
														'send_to_webhook'  => esc_html__( 'Send to Webhook', 'flowmattic' ),
													);

													foreach ( $all_actions as $action_type => $label ) {
														$selected = $action_type === $chatbot_action_type ? 'selected' : '';
														?>
														<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $action_type ); ?>"><?php echo esc_attr( $label ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
											<div class="form-text"><?php echo esc_attr__( 'Choose the action to perform when the chatbot generates a response.', 'flowmattic' ); ?></div>
										</div>
										<div class="mb-4 action-type-trigger_workflow <?php echo ( 'trigger_workflow' === $chatbot_action_type ) ? 'd-block' : 'd-none'; ?>">
											<!-- Display all workflows -->
											<label class="form-label fw-bold"><?php echo esc_html__( 'Workflow', 'flowmattic' ); ?></label>
											<select id="chatbot_action_workflow_id" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="Choose option" title="Choose option" data-live-search="true">
												<?php
												$wp_current_user = wp_get_current_user();
												$wp_user_email   = $wp_current_user->user_email;

												if ( current_user_can( 'manage_options' ) ) {
													$all_workflows = wp_flowmattic()->workflows_db->get_all();
												} else {
													$all_workflows = wp_flowmattic()->workflows_db->get_user_workflows( $wp_user_email );
												}

												// Sort workflows.
												arsort( $all_workflows );

												foreach ( $all_workflows as $key => $workflow_item ) {
													$workflow_id   = $workflow_item->workflow_id;
													$workflow_name = $workflow_item->workflow_name;
													$selected      = $workflow_id === $chatbot_action_workflow_id ? 'selected' : '';

													// Make sure the workflow name is trimmed to 50 characters.
													$workflow_name = strlen( $workflow_name ) > 50 ? substr( $workflow_name, 0, 60 ) . '...' : $workflow_name;
													?>
													<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $workflow_id ); ?>" data-subtext="ID: <?php echo esc_attr( $workflow_id ); ?>"><?php echo esc_attr( $workflow_name ); ?></option>
													<?php
												}
												?>
											</select>
											<div class="form-text"><?php echo esc_attr__( 'Choose the workflow to trigger when the chatbot generates a response.', 'flowmattic' ); ?></div>
										</div>
										<!-- Option to add webhook URL. -->
										<div class="mb-4 action-type-send_to_webhook <?php echo ( 'send_to_webhook' === $chatbot_action_type ) ? 'd-block' : 'd-none'; ?>">
											<label class="form-label fw-bold"><?php echo esc_html__( 'Webhook URL', 'flowmattic' ); ?></label>
											<input type="search" class="form-control border-dark" id="chatbot_action_webhook_url" placeholder="<?php echo esc_attr__( 'https://example.com/webhook', 'flowmattic' ); ?>" value="<?php echo esc_attr( $chatbot_action_webhook_url ); ?>">
											<div class="form-text"><?php echo esc_attr__( 'Enter the webhook URL to send the chatbot responses. Webhook URL will receive the chatbot response as JSON in the POST request.', 'flowmattic' ); ?></div>
										</div>
										<div class="d-grid gap-2 save-button border-top pt-3 mt-3">
											<button class="mt-2 btn btn-outline-primary fm-save-chatbot-settings btn-lg disabled" type="button"><?php echo esc_html__( 'Save Changes', 'flowmattic' ); ?></button>
										</div>
									</form>
								</div>
							</div>
							<div class="tab-pane fade p-3" id="style" role="tabpanel" aria-labelledby="style-tab">
								<form id="form-styles">
									<div class="mb-4">
										<label class="form-label fw-bold"><?php echo esc_html__( 'Page Background', 'flowmattic' ); ?></label>
										<div class="d-block">
											<input type="search" class="form-control color-control border-dark" id="chatbot_page_background" value="<?php echo esc_attr( $chatbot_page_background ); ?>">
										</div>
									</div>
									<div class="mb-4">
										<label class="form-label fw-bold"><?php echo esc_html__( 'Buttons and Chatbot Message Background', 'flowmattic' ); ?></label>
										<div class="d-block">
											<input type="search" class="form-control color-control border-dark" id="chatbot_message_background" value="<?php echo esc_attr( $chatbot_message_background ); ?>">
										</div>
									</div>
									<div class="mb-4">
										<label class="form-label fw-bold"><?php echo esc_html__( 'User Message Background', 'flowmattic' ); ?></label>
										<div class="d-block">
											<input type="search" class="form-control color-control border-dark" id="chatbot_user_message_background" value="<?php echo esc_attr( $user_message_background ); ?>">
										</div>
									</div>
									<div class="mb-4">
										<label class="form-label fw-bold"><?php echo esc_html__( 'Loading Animation', 'flowmattic' ); ?></label>
										<div class="d-block">
											<select id="loading_animation_type" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="Choose option" title="Choose option">
												<?php
												$all_types = array(
													'spinner' => esc_html__( 'Spinner Icon', 'flowmattic' ),
													'wave' => esc_html__( 'Wave Dots', 'flowmattic' ),
												);

												foreach ( $all_types as $animation_type => $label ) {
													$selected = $animation_type === $loading_animation_type ? 'selected' : '';
													?>
													<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $animation_type ); ?>"><?php echo esc_attr( $label ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<div class="form-text"><?php echo esc_attr__( 'Choose the animation type you like to show when the chatbot is waiting for reply.', 'flowmattic' ); ?></div>
									</div>
									<div class="mb-4">
										<label for="chatbox_icon" class="form-label fw-bold"><?php echo esc_html__( 'Chatbox Icon', 'flowmattic' ); ?></label>
										<div class="d-flex align-items-center">
											<div class="icon-preview rounded-circle">
												<img src="<?php echo esc_attr( $chatbox_icon ); ?>" alt="<?php echo esc_attr__( 'Chatbox Icon', 'flowmattic' ); ?>" width="40" height="40">
											</div>
											<input type="search" class="form-control border-dark d-none" id="chatbox_icon" value="<?php echo esc_attr( $chatbox_icon ); ?>" data-default="<?php echo esc_attr( FLOWMATTIC_PLUGIN_URL . 'assets/admin/img/chat-icon.svg' ); ?>">
											<button type="button" class="btn btn-outline-secondary btn-sm ms-2 btn-upload-icon"><?php echo esc_html__( 'Upload Icon', 'flowmattic' ); ?></button>
										</div>
									</div>
									<div class="d-grid gap-2 save-button border-top pt-3 mt-3">
										<button class="mt-2 btn btn-outline-primary fm-save-chatbot-settings btn-lg disabled" type="button"><?php echo esc_html__( 'Save Changes', 'flowmattic' ); ?></button>
										<button class="mt-2 btn btn-outline-secondary fm-reset-chatbot-settings btn-lg" type="button"><?php echo esc_html__( 'Reset to Default', 'flowmattic' ); ?></button>
									</div>
								</form>
							</div>
							<div class="tab-pane fade p-3" id="conversations" role="tabpanel" aria-labelledby="conversations-tab">
								<div class="section-header d-flex flex-column gap-2">
									<strong><?php echo esc_html__( 'Conversations', 'flowmattic' ); ?></strong>
									<p><?php echo esc_html__( 'Conversations handled with only FlowMattic chatbot will appear here.', 'flowmattic' ); ?></p>
								</div>
								<div class="conversation-wrapper d-flex flex-column gap-3 mt-2"></div>
							</div>
						</div>
					</div>
					<!-- Chatbot preview wrapper -->
					<div class="flowmattic-chatbot-preview-wrapper">
						<div class="flowmattic-chatbot-preview-top border-bottom ps-3 d-flex justify-content-between">
						<button type="button" class="btn btn-outline-primary btn-sm" id="btn-restart-chatbot-preview">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="18" width="18" size="18" name="arrowRefresh"><path fill="currentColor" d="M12 4H7.1l2.52-3H7L3.65 5 7 9h2.62L7.1 6H12a7 7 0 1 1-7 7H3a9 9 0 1 0 9-9Z"></path></svg>
							<span><?php echo esc_html__( 'Restart', 'flowmattic' ); ?></span>
						</button>
						<div class="d-flex flex-row pe-3 border-start gap-2">
							<button type="button" data-toggle="modal" data-target="#embedChatbotModal" class="btn btn-primary btn-sm"><?php echo esc_html__( 'Embed', 'flowmattic' ); ?></button>
							<button type="button" data-toggle="modal" data-target="#shareChatbotModal" class="btn btn-secondary btn-sm me-2"><?php echo esc_html__( 'Share', 'flowmattic' ); ?></button>
						</div>
					</div>
					<div class="flowmattic-chatbot-preview-body p-3">
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
							<form id="flowmattic-chatbot-user-input">
								<input type="hidden" id="fm_chatbot_id" class="form-control" value="<?php echo esc_attr( base64_encode( $chatbot_id ) ); // @codingStandardsIgnoreLine ?>">
								<input type="hidden" id="fm_chatbot_name" class="form-control" value="<?php echo esc_attr( $chatbot_name ); ?>">
								<input type="hidden" id="fm_chatbot_status" class="form-control" value="previews">
								<input type="hidden" id="fm_user_id" class="form-control" value="<?php echo esc_attr( $user_id ); // @codingStandardsIgnoreLine ?>">
								<input type="hidden" id="fm_secure_key" class="form-control" value="<?php echo esc_attr( $secure_key ); // @codingStandardsIgnoreLine ?>">
								<div class="flowmattic-chatbot-input mb-0 d-flex align-items-baseline mb-4 ms-4 me-2">
									<textarea class="w-100 border-0 p-0 shadow-none user-input-textarea" id="chatbot_input_field" rows="1" placeholder="<?php echo esc_attr( $chatbot_user_placeholder ); ?>" style="height: 24px !important;"></textarea>
									<button class="fm-send-message-btn border-0 p-0 btn btn-link" id="chatbot_input_button" style="height: 24px !important;"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg></button>
								</div>
							</form>
						</div> <!-- .flowmattic-chatbot-context -->
					</div> <!-- .flowmattic-chatbot-preview-body -->
				</div> <!-- .flowmattic-chatbot-preview-wrapper -->
			</div> <!-- .flowmattic-chatbot-body-wrapper -->
		</div> <!-- .flowmattic-dashboard-content -->
	</div> <!-- .flowmattic-wrapper -->
</div><!-- .wrap -->

<!-- Modal to create assistant -->
<div class="modal fade" id="createAssistantModal" data-backdrop="dynamic" data-keyboard="false" tabindex="-1" aria-labelledby="createAssistantLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createAssistantLabel">Create Assistant</h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-3">
				<form id="create_assistant_form">
					<div class="mb-4">
						<label for="assistant_name" class="form-label fw-bold"><?php echo esc_html__( 'Assistant Name', 'flowmattic' ); ?></label>
						<input type="search" class="form-control border-dark" id="assistant_name" placeholder="<?php echo esc_attr__( 'Eg. FlowMattic Support', 'flowmattic' ); ?>" value="">
					</div>
					<div class="mb-4">
						<label for="chatbot_connect" class="form-label fw-bold"><?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?></label>
						<select id="chatbot_connect" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="<?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?>" title="<?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?>" data-live-search="true">
							<?php
							foreach ( $all_connects as $key => $connect_item ) {
								$connect_id   = $connect_item->id;
								$connect_name = $connect_item->connect_name;
								?>
								<option value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
								<?php
							}
							?>
						</select>
						<div class="form-text">
						<?php echo esc_attr__( 'Choose the FlowMattic Connect to use with Chatbot. Make sure to connect your OpenAI account', 'flowmattic' ); ?> <a href="<?php echo esc_attr( admin_url( '/admin.php?page=flowmattic-connects' ) ); ?>" target="_blank"><?php echo esc_attr__( 'here', 'flowmattic' ); ?></a>
						</div>
					</div>
					<div class="mb-4">
						<label for="assistant_model" class="form-label fw-bold"><?php echo esc_html__( 'Assistant AI Model', 'flowmattic' ); ?></label>
						<select id="assistant_model" class="form-select form-control w-100 mw-100 form-select-md border border-dark" aria-label="Choose model" title="Choose model">
							<?php
							// Set the AI models with available tools.
							$default_models = array(
								'gpt-4o'                 => 'gpt-4o',
								'gpt-4o-mini'            => 'gpt-4o-mini',
								'gpt-4-turbo'            => 'gpt-4-turbo',
								'gpt-3.5-turbo'          => 'gpt-3.5-turbo',
								'gpt-4'                  => 'gpt-4',
								'gpt-4o-mini-2024-07-18' => 'gpt-4o-mini-2024-07-18',
								'gpt-4o-2024-11-20'      => 'gpt-4o-2024-11-20',
								'gpt-4o-2024-08-06'      => 'gpt-4o-2024-08-06',
								'gpt-4o-2024-05-13'      => 'gpt-4o-2024-05-13',
								'gpt-4-turbo-preview'    => 'gpt-4-turbo-preview',
								'gpt-4-turbo-2024-04-09' => 'gpt-4-turbo-2024-04-09',
								'gpt-4-1106-preview'     => 'gpt-4-1106-preview',
								'gpt-4-0613'             => 'gpt-4-0613',
								'gpt-4-0125-preview'     => 'gpt-4-0125-preview',
								'gpt-3.5-turbo-16k'      => 'gpt-3.5-turbo-16k',
								'gpt-3.5-turbo-1106'     => 'gpt-3.5-turbo-1106',
								'gpt-3.5-turbo-0125'     => 'gpt-3.5-turbo-0125',
							);

							// Get the AI Models from API.
							$ai_models = flowmattic_get_openai_models( $token );

							// Fallback to default models if API is WP_Error.
							if ( is_wp_error( $ai_models ) ) {
								$ai_models = $default_models;
							}

							foreach ( $ai_models as $ai_model ) {
								?>
								<option value="<?php echo esc_attr( $ai_model ); ?>">
									<?php echo esc_attr( $ai_model ); ?>
								</option>
								<?php
							}
							?>
						</select>
						<div class="form-text"><?php echo esc_attr__( 'AI Models are subject to availibility, as per your OpenAI account. Check your OpenAI account to see if the model you selected is available in Create Assistant.', 'flowmattic' ); ?></div>
					</div>
					<div class="mb-4">
						<label for="assistant_instructions" class="form-label fw-bold"><?php echo esc_html__( 'Instructions', 'flowmattic' ); ?></label>
						<textarea class="form-control border-dark" id="assistant_instructions" rows="3" placeholder="<?php echo esc_attr__( 'You are an AI chatbot. You are a helpful assistant.', 'flowmattic' ); ?>"><?php echo esc_textarea( __( 'You are an AI chatbot. You are a helpful assistant.', 'flowmattic' ) ); ?></textarea>
						<div class="form-text"><?php echo esc_attr__( 'The system instructions that the assistant uses. The maximum length is 32768 characters', 'flowmattic' ); ?></div>
					</div>
					<div class="mb-4">
						<label for="assistant_description" class="form-label fw-bold"><?php echo esc_html__( 'Description', 'flowmattic' ); ?></label>
						<textarea class="form-control border-dark" id="assistant_description" rows="3" placeholder="<?php echo esc_attr__( 'This is an AI assistant to handle support queries.', 'flowmattic' ); ?>"><?php echo esc_textarea( __( 'This is an AI assistant to handle support queries.', 'flowmattic' ) ); ?></textarea>
						<div class="form-text"><?php echo esc_attr__( 'The description of the assistant. The maximum length is 512 characters', 'flowmattic' ); ?></div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="create-assistant-btn"><?php echo esc_attr__( 'Create Assistant', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>
<?php
// Get the chatbot route URL.
$chatbot_id_encoded = base64_encode( $chatbot_id ); // @codingStandardsIgnoreLine

// Remove the last = from the encoded string.
$chatbot_id_encoded = rtrim( $chatbot_id_encoded, '=' );

// Get the chatbot URL.
$chatbot_url = rest_url( 'flowmattic/v1/embed/chatbot/' ) . '?chatbot_id=' . $chatbot_id_encoded;
?>
<!-- Modal to show share chatbot link -->
<div class="modal fade" id="shareChatbotModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="shareChatbotModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="shareChatbotModalLabel">Share Chatbot</h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p><?php echo esc_attr__( 'Copy the link and share it with your users.', 'flowmattic' ); ?></p>
				<p><?php echo esc_attr__( 'To use custom URL or restrict access with password, please use the embed options in your page.', 'flowmattic' ); ?></p>
				<div class="input-group mb-3 d-flex flex-column gap-4">
					<textarea class="form-control pre-content fm-copy-content w-100" id="chatbotLinkInput" rows="1" readonly><?php echo esc_attr( $chatbot_url ); ?></textarea>
					<button class="btn btn-outline-secondary btn-copy-content" type="button" id="copyChatbotLinkButton"><?php echo esc_attr__( 'Copy', 'flowmattic' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal to show chatbot embed options -->
<div class="modal fade" id="embedChatbotModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="embedChatbotModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="embedChatbotModalLabel">Embed Chatbot</h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<ul class="nav nav-tabs mb-3" id="embedOptionsTabs" role="tablist">
					<li class="nav-item m-0" role="presentation">
						<button class="nav-link active" id="shortcode-tab" data-toggle="tab" data-target="#shortcode" type="button" role="tab" aria-controls="shortcode" aria-selected="true">Shortcode</button>
					</li>
					<li class="nav-item m-0" role="presentation">
						<button class="nav-link" id="iframe-tab" data-toggle="tab" data-target="#iframe" type="button" role="tab" aria-controls="iframe" aria-selected="false">iFrame</button>
					</li>
					<li class="nav-item m-0" role="presentation">
						<button class="nav-link" id="popup-tab" data-toggle="tab" data-target="#popup" type="button" role="tab" aria-controls="popup" aria-selected="false">Popup</button>
					</li>
				</ul>
				<div class="tab-content" id="embedOptionsContent">
					<div class="tab-pane fade show active" id="shortcode" role="tabpanel" aria-labelledby="shortcode-tab">
						<p><?php echo esc_attr__( 'Copy the shortcode and paste it in your post or page.', 'flowmattic' ); ?></p>
						<div class="input-group mb-3 d-flex flex-column gap-4">
							<textarea class="form-control pre-content fm-copy-content w-100" id="chatbotShortcodeInput" rows="1" readonly>[flowmattic_chatbot chatbot_id="<?php echo esc_attr( $chatbot_id_encoded ); ?>" width="600px" height="800px"]</textarea>
							<button class="btn btn-outline-secondary btn-copy-content" type="button" id="copyChatbotShortcodeButton"><?php echo esc_attr__( 'Copy', 'flowmattic' ); ?></button>
						</div>
					</div>
					<div class="tab-pane fade" id="iframe" role="tabpanel" aria-labelledby="iframe-tab">
						<p><?php echo esc_attr__( 'Copy the iframe code and paste it in your post or page.', 'flowmattic' ); ?></p>
						<div class="input-group mb-3 d-flex flex-column gap-4">
							<textarea class="form-control pre-content fm-copy-content w-100" id="chatbotIframeInput" rows="1" readonly><iframe src="<?php echo esc_attr( $chatbot_url ); ?>" width="400px" height="600px"></iframe></textarea>
							<button class="btn btn-outline-secondary btn-copy-content" type="button" id="copyChatbotIframeButton"><?php echo esc_attr__( 'Copy', 'flowmattic' ); ?></button>
						</div>
					</div>
					<div class="tab-pane fade" id="popup" role="tabpanel" aria-labelledby="popup-tab">
						<p><?php echo esc_attr__( 'Copy the popup code and paste the following script in your website in the footer.', 'flowmattic' ); ?></p>
						<div class="input-group mb-3 d-flex flex-column gap-4">
							<textarea class="form-control pre-content fm-copy-content w-100" id="chatbotPopupInput" rows="1" readonly><script type="text/javascript" src="<?php echo esc_attr( rest_url( 'flowmattic/v1/embed/chatbot/widget/' ) . '?chatbot_id=' . $chatbot_id_encoded ); // @codingStandardsIgnoreLine ?>"></script></textarea>
							<button class="btn btn-outline-secondary btn-copy-content" type="button" id="copyChatbotPopupButton"><?php echo esc_attr__( 'Copy', 'flowmattic' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal to add content sources -->
<div class="modal fade" id="addContentSourceModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="addContentSourceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form id="add_content_source_form">
				<div class="modal-header">
					<h5 class="modal-title" id="addContentSourceModalLabel">Add Content Source</h5>
					<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body d-flex flex-column gap-3">
					<div class="form-group d-flex flex-column gap-2 upload-block">
						<label for="fileInput">Content Source File</label>
						<button type="button" class="btn btn-primary btn-upload-file">Upload File</button>
						<p><?php echo esc_attr__( 'Text-based files upto 10MB supported. Once you select the file from media library, it will be uploaded to OpenAI and used for this ChatBot.', 'flowmattic' ); ?></p>
						<p><?php echo esc_attr__( 'Click the add resource button below to add the file to the chatbot for your identification purpose.', 'flowmattic' ); ?></p>
						<p><?php echo esc_attr__( 'Supported file types: .txt, .doc, .docx, .pdf, .html, .json and .md', 'flowmattic' ); ?></p>
						<p><?php echo esc_attr__( 'Note: The file will be uploaded to OpenAI and used for this ChatBot.', 'flowmattic' ); ?></p>
					</div>
					<div class="form-group d-flex flex-column gap-2 file-block d-none">
						<label for="fileName">Content Source File</label>
						<input type="url" class="form-control" id="fileName" readonly>
						<input type="hidden" class="form-control" id="openai_file_id">
						<p><?php echo esc_attr__( 'Text-based files upto 10MB supported.', 'flowmattic' ); ?></p>
					</div>
					<div class="form-group d-flex flex-column gap-1">
						<label for="descriptionTextarea">Description</label>
						<textarea class="form-control" id="descriptionTextarea" rows="3"></textarea>
					</div>
				</div>
			</form>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary btn-add-source disabled">Add Source</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
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
div#setup form {
    max-height: calc(100vh - 90px);
    overflow: hidden;
    overflow-y: auto;
}
</style>
<?php
add_filter(
	'admin_footer_text',
	function() {
		return '';
	}
);
