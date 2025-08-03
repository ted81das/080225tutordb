<?php
/**
 * Admin page template for AI Assistants.
 *
 * @package FlowMattic
 * @since 5.0
 */

FlowMattic_Admin::loader();

$license_key = get_option( 'flowmattic_license_key', '' );

if ( '' === $license_key ) {
	?>
	<div class="card border-light mw-100">
		<div class="card-body text-center">
			<div class="alert alert-primary" role="alert">
				<?php echo esc_html__( 'License key not registered. Please register your license first to use assistants.', 'flowmattic' ); ?>
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
				<?php echo esc_html__( 'License key not valid. Please register your license first to use assistants.', 'flowmattic' ); ?>
				</div>
			</div>
		</div>
	<?php
	wp_die();
}

$license  = wp_flowmattic()->check_license();
$settings = get_option( 'flowmattic_settings', array() );
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-container flowmattic-ai-assistants-list-container w-100">
			<div class="flowmattic-dashboard-content container m-0 ps-3">
				<div class="row">
					<div id="flowmattic-ai-assistants">
						<div class="flowmattic-ai-assistants-list">
						<div class="fm-assistant-task-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-assistant-heading m-0 d-flex align-items-center">
									<?php esc_html_e( 'AI Assitants', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-ai-assistants-header-actions">
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="modal" data-target="#helpModal">
										<span class="dashicons dashicons-editor-help d-inline-block fs-3 d-flex align-items-center justify-content-center"></span>
									</a>
									<?php
									$button_type  = '';
									$button_class = 'create-new-assistant';
									$button_url   = 'javascript:void(0);';

									if ( ! $license || '' === $license_key ) {
										$button_type  = 'disabled';
										$button_class = 'needs-registration';
									} else {
										?>
										<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>">
											<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
											<?php esc_html_e( 'Create New Assistant', 'flowmattic' ); ?>
										</a>
										<?php
									}
									?>
								</div>
							</div>
							<div class="ai-assistants-nav navbar mt-3 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Connect and manage your database assistants and assistant data here.', 'flowmattic' ); ?>
									<a href="https://help.flowmattic.com/docs/features/ai-assistants-by-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="flowmattic-ai-assistants-list-body">
								<div class="flowmattic-ai-assistants-list-body-inner">
									<div class="flowmattic-ai-assistants-list-body-inner-assistant bg-white">
										<table class="table bg-white">
											<thead>
												<tr>
													<th class="p-3" style="width: 25%;"><?php echo esc_html__( 'Assistant Name', 'flowmattic' ); ?></th>
													<th class="p-3"><?php echo esc_html__( 'Description', 'flowmattic' ); ?></th>
													<th class="p-3"><?php echo esc_html__( 'Connect ID', 'flowmattic' ); ?></th>
													<th class="p-3" style="width: 200px;"><?php echo esc_html__( 'AI Model', 'flowmattic' ); ?></th>
													<th class="p-3" style="width: 200px;"><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												// Get the AI Assistants.
												$assistants = wp_flowmattic()->chatbots_db->get_all();

												if ( ! empty( $assistants ) ) {
													foreach ( $assistants as $key => $assistant ) {
														// Get assistant ID.
														$assistant_id = base64_encode( $assistant->chatbot_id );

														// Chatbot settings.
														$chatbot_settings = json_decode( $assistant->chatbot_settings );
														
														// Get assistant name.
														$assistant_name = $chatbot_settings->chatbot_name;

														// Assistant Description.
														$assistant_description = isset( $chatbot_settings->chatbot_description ) ? $chatbot_settings->chatbot_description : '';
														
														// Assistant Connect ID.
														$assistant_connect_id = $chatbot_settings->chatbot_connect;
														
														// Assistant Model.
														$assistant_model = isset( $chatbot_settings->chatbot_model ) ? $chatbot_settings->chatbot_model : '';

														// Prepare the edit link.
														$edit_link = admin_url( 'admin.php?page=flowmattic-ai-assistants&assistant_id=' . $assistant_id . '&action=edit' );
														?>
														<tr>
															<td class="p-3" valign="middle">
																<div class="mb-1 mt-1">
																	<?php echo esc_html( $assistant_name ); ?>
																</div>
															</td>
															<td class="p-3" valign="middle"><?php echo esc_html( $assistant_description ); ?></td>
															<td class="p-3" valign="middle"><?php echo esc_html( $assistant_connect_id ); ?></td>
															<td class="p-3" valign="middle"><?php echo esc_html( $assistant_model ); ?></td>
															<td class="p-3" valign="middle">
																<a href="<?php echo esc_attr( $edit_link ); ?>" class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center fm-edit-assistant" data-toggle="tooltip" title="<?php esc_html_e( 'Edit', 'flowmattic' ); ?>">
																	<?php echo esc_attr__( 'Edit', 'flowmattic' ); ?>
																</a>
																<a href="javascript:void(0);" data-assistant-id="<?php echo esc_attr( $assistant_id ); ?>" data-connect-id="<?php echo esc_attr( $assistant_connect_id ); ?>" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center fm-delete-assistant" data-toggle="tooltip" title="<?php esc_html_e( 'Delete', 'flowmattic' ); ?>">
																	<?php echo esc_attr__( 'Delete', 'flowmattic' ); ?>
																</a>
															</td>
														</tr>
														<?php
													}
												} else {
													?>
													<tr>
														<td class="p-3" colspan="4"><?php echo esc_html__( 'No AI Assistants found.', 'flowmattic' ); ?></td>
													</tr>
													<?php
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- #flowmattic-ai-assistants -->
				</div> <!-- .row -->
			</div> <!-- .flowmattic-dashboard-content -->
		</div> <!-- .flowmattic-container -->
	</div> <!-- .flowmattic-wrapper -->
</div> <!-- .flowmattic-dashboard-content -->

<!-- Create new assistant modal -->
<div class="modal fade" id="createNewAssistantModal" tabindex="-1" aria-labelledby="createNewAssistantModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createNewAssistantModalLabel"><?php echo esc_html__( 'Create New Assistant', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<form id="createNewAssistantForm" class="flowmattic-form">
					<div class="mb-3">
						<label for="assistant_name" class="form-label"><?php echo esc_html__( 'Assistant Name', 'flowmattic' ); ?></label>
						<input type="text" class="form-control" id="assistant_name" name="assistant_name" required>
					</div>
					<div class="mb-3">
						<label for="connect_id" class="form-label"><?php echo esc_html__( 'OpenAI Connect', 'flowmattic' ); ?></label>
						<select class="form-select w-100 mw-100" id="connect_id" name="connect_id" required>
							<?php
							$all_connects = wp_flowmattic()->connects_db->get_all();
							if ( ! empty( $all_connects ) ) {
								foreach ( $all_connects as $id => $connect_item ) {
									$connect_id   = $connect_item->id;
									$connect_name = $connect_item->connect_name;
									?>
									<option value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
						<div class="form-text"><?php echo esc_html__( 'Select the OpenAI Connect to use for this assistant.', 'flowmattic' ); ?></div>
					</div>
					<div class="mb-3">
						<label for="assistant_description" class="form-label"><?php echo esc_html__( 'Assistant Description', 'flowmattic' ); ?></label>
						<textarea class="form-control" id="assistant_description" name="assistant_description" rows="3" placeholder="<?php echo esc_attr__( 'Ex. AI assistant for ACME Inc.', 'flowmattic' ); ?>"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<span class="spinner-border spinner-border-sm text-primary d-none create-assistant-spinner ms-2" role="status" aria-hidden="true"></span>
				<button type="submit" class="btn btn-primary fm-create-assistant"><?php echo esc_html__( 'Create', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Delete assistant confirmation modal -->
<div class="modal fade" id="deleteAssistantModal" tabindex="-1" aria-labelledby="deleteAssistantModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteAssistantModalLabel"><?php echo esc_html__( 'Delete Assistant?', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<p class="fs-6"><?php echo esc_html__( 'Are you sure you want to delete this assistant?', 'flowmattic' ); ?></p>
				<p class="fs-6"><?php echo esc_html__( 'This action cannot be undone.', 'flowmattic' ); ?></p>
				<p class="fs-6"><?php echo esc_html__( 'Please note that this will delete the assistant and all its data.', 'flowmattic' ); ?></p>
			</div>
			<div class="modal-footer">
				<span class="spinner-border spinner-border-sm text-primary d-none delete-assistant-spinner ms-2" role="status" aria-hidden="true"></span>
				<button type="button" class="btn btn-danger btn-sm fm-delete-assistant-confirm"><?php echo esc_html__( 'Delete Assistant', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Help modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content border-0">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModal-label"><?php esc_html_e( 'FlowMattic Assistants', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="accordion" id="helpAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingOne">
							<button class="accordion-button" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								<?php esc_html_e( 'What is FlowMattic AI Assistants?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'FlowMattic AI Assistants is a chat assistant or chatbot build on top of OpenAI Assistants API. It helps you show a chatbot on your site and let your users ask questions to the AI Assistant to get the information they need. AI Assistant will provide the professional responses to your users based on the data you upload.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTwo">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php esc_html_e( 'How to create a new FlowMattic Assistant?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can create a new FlowMattic Assistant by clicking the "Create New Assistant" button. You will be asked to enter the assistant name, assistant description and Connect ID where you entered your OpenAI API Key. Once you click the Create button, FlowMattic will register a new Assistant in OpenAI on behalf of you, create a new vector store for your assistant and add a new entry to your website to easily manage your assistant.', 'flowmattic' ); ?>
								<ul style="list-style: square;">
									<li><?php esc_html_e( 'Assistant name should be unique and should not contain any special characters.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Assistant name accepts only English letters or numbers.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Assistant description is optional and is used to identify the assistant.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Assistant requires a Connect ID to connect with OpenAI API.', 'flowmattic' ); ?> <a href="<?php echo admin_url( 'admin.php?page=flowmattic-connects' ); ?>" class="hover-text-white"><?php esc_html_e( 'Create a new Connect', 'flowmattic' ); ?></a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingThree">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<?php esc_html_e( 'How to edit a FlowMattic AI Assistant?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'To edit a your AI Assistant, just click the edit button next to the Assistant, you will be redirected to the edit page where you can add or update the data sources and change the look and feel of the Assistant.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFour">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<?php esc_html_e( 'How to delete a FlowMattic Assistant?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can delete a FlowMattic AI Assistant by clicking the "Delete" button next to the Assistant. This will also delete the data associated with the Assistant, including the vector store and the assistant data.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFive">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<?php esc_html_e( 'What AI Models are available to use with Assistant?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'FlowMattic AI Assistants supports all the models available in OpenAI API. You can select the model you want to use with your Assistant while editing the Assistant. You need to make sure that the model you select is supported by OpenAI API for the assistant in your OpenAI account.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSix">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								<?php esc_html_e( 'How to upload new data to Assistant?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can upload new data to your Assistant by editing the Assistant and adding the data sources. You can upload the data in Text, JSON, PDF or DOCX format. The data will be used by the Assistant to provide the responses to the users.', 'flowmattic' ); ?>
								<?php esc_html_e( 'You can also upload the data to the Assistant directly from your OpenAI account. However, the files added to Assistant using OpenAI account dashboard will not be visible in the FlowMattic Assistant dashboard.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSeven">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
								<?php esc_html_e( 'How to get help with FlowMattic AI Assistants?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'If you need help with FlowMattic AI Assistants, you can visit the help documentation to learn more about the features and how to use them. You can also contact the support team for any help or assistance.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingEight">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
								<?php esc_html_e( 'Can I connect multiple Assistants to a single Connect?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'Yes, you can connect multiple Assistants to a single Connect. This will help you manage all your Assistants from a single Connect and easily switch between your Assistants. Note that, all the Assistants connected to a single Connect will share the same OpenAI API Key, and the usage will be counted against the same OpenAI account.', 'flowmattic' ); ?>
								<?php esc_html_e( 'Also, once you create an AI Assistant using a Connect, you cannot change the Connect for that Assistant. However, if you need, you can update the API key in the Connect and all the Assistants connected to that Connect will use the updated API Key.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingNine">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
								<?php esc_html_e( 'Can I use the same Assistant on multiple websites?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'Yes, you can use the same Assistant on multiple websites. You can create an Assistant once and use the same Assistant on multiple websites by using the embed code provided in the Assistant settings. However, the look and feel of the Assistant cannot be changed based on the website.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTen">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
								<?php esc_html_e( 'Can I use different Connect for different Assistants?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'Yes, you can use different Connect for different Assistants. You can create multiple Connects with different OpenAI API Keys and use them to create different Assistants. This will help you manage different Assistants with different API Keys or different OpenAI accounts.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="https://help.flowmattic.com/docs/features/ai-assistants-by-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Read more' );?></a>
				<button type="button" class="btn btn-secondary" id="close-help-modal" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	#wpfooter {
		display: none;
	}
	div#wpbody-content {
		padding-bottom: 0 !important;
	}
	.hover-text-white:hover {
    color: #fff !important;
}
</style>
<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		jQuery( '.create-new-assistant' ).on( 'click', function() {
			jQuery( '#createNewAssistantModal' ).modal( 'show' );
		} );

		// If query string is present, open the modal.
		var urlParams = new URLSearchParams( window.location.search );
		var openModal = urlParams.get( 'openModal' );

		if ( 'createAssistant' === openModal ) {
			jQuery( '#createNewAssistantModal' ).modal( 'show' );
			
			// Remove openModal from the URL.
			var newURL = urlParams.toString().replace( '&openModal=createAssistant', '' );
			window.history.pushState( '', '', '?' + newURL );
		}

		jQuery( '.fm-create-assistant' ).on( 'click', function() {
			var thisBtn = jQuery( this );
			var assistant_name        = jQuery( '#assistant_name' ).val();
			var assistant_description = jQuery( '#assistant_description' ).val();
			var connect_id    = jQuery( '#connect_id' ).val();
			var loadingSpinner = jQuery( '.create-assistant-spinner' );

			// Show the spinner.
			loadingSpinner.removeClass( 'd-none' );

			// Disable the button.
			thisBtn.prop( 'disabled', true );

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_create_ai_assistant',
					security: '<?php echo wp_create_nonce( 'flowmattic_create_assistant' ); ?>',
					assistant_name: assistant_name,
					assistant_description: assistant_description,
					connect_id: connect_id
				},
				success: function( response ) {
					var response = JSON.parse( response );
					if ( response.status && 'success' === response.status ) {
						jQuery( '#createNewAssistantModal' ).modal( 'hide' );
						location.reload();
					}
				},
				complete: function( response ) {
					// Enable the button.
					thisBtn.prop( 'disabled', false );

					// Hide the spinner.
					loadingSpinner.addClass( 'd-none' );
				}
			} );
		} );

		// Delete assistant.
		var assistant_id = '';
		var connect_id = '';
		jQuery( '.fm-delete-assistant' ).on( 'click', function() {
			jQuery( '#deleteAssistantModal' ).modal( 'show' );
			assistant_id = jQuery( this ).data( 'assistant-id' );
			connect_id = jQuery( this ).data( 'connect-id' );
		} );

		jQuery( '.fm-delete-assistant-confirm' ).on( 'click', function() {
			var thisBtn = jQuery( this ),
				loadingSpinner = jQuery( '.delete-assistant-spinner' );

			loadingSpinner.removeClass( 'd-none' );

			// Disable the button.
			thisBtn.prop( 'disabled', true );

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'flowmattic_delete_assistant',
					security: '<?php echo wp_create_nonce( 'flowmattic_delete_assistant' ); ?>',
					assistant_id: assistant_id,
					connect_id: connect_id
				},
				success: function( response ) {
					var response = JSON.parse( response );
					if (  response.status && 'success' === response.status ) {
						loadingSpinner.addClass( 'd-none' );
						thisBtn.text( 'Assistant Deleted' );
						jQuery( '#deleteAssistantModal' ).modal( 'hide' );
						location.reload();
					}
				},
				complete: function( response ) {
					loadingSpinner.addClass( 'd-none' );
					thisBtn.prop( 'disabled', false );
				}
			} );
		} );
	} );
</script>
<?php 
// FlowMattic_Admin::footer();
add_filter( 'admin_footer_text', function( $text ) {
	return '';
} );

add_filter( 'update_footer', function( $text ) {
	return '';
} );
