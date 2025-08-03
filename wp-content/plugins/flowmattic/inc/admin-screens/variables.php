<?php
/**
 * Admin page template for variables.
 *
 * @package FlowMattic
 * @since 4.0
 */

FlowMattic_Admin::loader();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<div class="flowmattic-container flowmattic-dashboard m-0">
					<div class="row flex-row-reverse flex-xl-row-reverse flex-sm-column">
						<div class="col-sm-12 fm-variables-list-container flowmattic-variables ps-4 pe-4">
							<div class="fm-variable-page-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-variable-heading m-0 d-flex align-items-center">
									<?php echo esc_attr__( 'FlowMattic Variables', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-variables-header-actions d-flex gap-2">
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="modal" data-target="#helpModal">
										<span class="dashicons dashicons-editor-help d-inline-block fs-3 d-flex align-items-center justify-content-center"></span>
									</a>
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#newCustomVariableModal">
										<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
										<?php echo esc_attr__( 'New Variable', 'flowmattic' ); ?>
									</a>
								</div>
							</div>
							<div class="variables-nav navbar mt-3 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Create and manage custom variables that can be used to store and manipulate data within your workflows.', 'flowmattic' ); ?>
									<a href="https://help.flowmattic.com/docs/setup/create-custom-variables/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="fm-variables-custom mb-4">
								<div class="fm-variables-custom-header d-flex justify-content-between">
									<h4 class="fm-variables-heading m-0 d-flex align-items-center px-3 py-3 bg-white w-100 border-bottom border-dark">
										<?php echo esc_attr__( 'Custom Variables', 'flowmattic' ); ?>
									</h4>
								</div>
								<div class="custom-variables-table variables-table">
									<?php
									// Get the custom variables.
									$custom_vars = wp_flowmattic()->variables->get_custom_vars();

									// If there are no custom variables, display a message.
									if ( empty( $custom_vars ) ) {
										?>
										<div class="fm-no-variables text-center p-5 bg-white">
											<h4 class="fs-5 fw-500 mb-3"><?php echo esc_attr__( 'No custom variables found.', 'flowmattic' ); ?></h4>
											<p class="mb-0"><?php echo esc_attr__( 'Create your first custom variable by clicking the "New Variable" button above.', 'flowmattic' ); ?></p>
										</div>
										<?php
									}
									?>
									<table class="table bg-white table-hover table-custom-vars <?php echo empty( $custom_vars ) ? 'd-none' : ''; ?>">
										<thead>
											<tr class="table-light">
												<th class="border-bottom" scope="col">#</th>
												<th class="border-bottom w-25" scope="col"><?php echo esc_attr__( 'Variable Name', 'flowmattic' ); ?></th>
												<th class="border-bottom w-25" scope="col"><?php echo esc_attr__( 'Description', 'flowmattic' ); ?></th>
												<th class="border-bottom" scope="col" style="width: 30%;"><?php echo esc_attr__( 'Value', 'flowmattic' ); ?></th>
												<th class="border-bottom" scope="col" style="width: 110px;"></th>
											</tr>
										</thead>
										<tbody>
											<?php
											// Set the row count.
											$row_count = 1;

											// Loop through the custom variables.
											foreach ( $custom_vars as $custom_var => $custom_var_data ) :
												?>
												<tr class="custom-var variable-item">
													<th scope="row"><?php echo esc_attr( $row_count ); ?></th>
													<td>
														<?php echo esc_attr( $custom_var_data->variable_name ); ?>
														<span class="click-to-copy text-primary ms-1" data-var="{{<?php echo esc_attr( $custom_var_data->variable_name ); ?>}}"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="icon-md"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 4C10.8954 4 10 4.89543 10 6H14C14 4.89543 13.1046 4 12 4ZM8.53513 4C9.22675 2.8044 10.5194 2 12 2C13.4806 2 14.7733 2.8044 15.4649 4H17C18.6569 4 20 5.34315 20 7V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V7C4 5.34315 5.34315 4 7 4H8.53513ZM8 6H7C6.44772 6 6 6.44772 6 7V19C6 19.5523 6.44772 20 7 20H17C17.5523 20 18 19.5523 18 19V7C18 6.44772 17.5523 6 17 6H16C16 7.10457 15.1046 8 14 8H10C8.89543 8 8 7.10457 8 6Z" fill="currentColor"></path></svg></span>
													</td>
													<td><?php echo esc_attr( $custom_var_data->variable_description ); ?></td>
													<td><code class="text-reset"><?php echo stripslashes( esc_attr( $custom_var_data->variable_value ) ); ?></code></td>
													<td class="text-end hover-actions">
														<a href="javascript:void(0);" class="btn btn-sm btn-outline-primary edit-variable" data-variable-name="<?php echo esc_attr( $custom_var_data->variable_name ); ?>">
															<span class="dashicons dashicons-edit"></span>
														</a>
														<a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-variable" data-variable-name="<?php echo esc_attr( $custom_var_data->variable_name ); ?>">
															<span class="dashicons dashicons-trash"></span>
														</a>
													</td>
												</tr>
												<?php
												// Increment the row count.
												++$row_count;
											endforeach;
											?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="fm-variables-system">
								<div class="fm-variables-system-header d-flex justify-content-between">
									<h4 class="fm-variables-heading m-0 d-flex align-items-center px-3 py-3 bg-white w-100 border-bottom border-dark">
										<?php echo esc_attr__( 'System Variables', 'flowmattic' ); ?>
									</h4>
								</div>
								<div class="system-variables-table variables-table">
									<table class="table bg-white table-hover">
										<thead>
											<tr class="table-light">
												<th class="border-bottom" scope="col">#</th>
												<th class="border-bottom" scope="col"><?php echo esc_attr__( 'Variable Name', 'flowmattic' ); ?></th>
												<th class="border-bottom" scope="col"><?php echo esc_attr__( 'Description', 'flowmattic' ); ?></th>
												<th class="border-bottom" scope="col"><?php echo esc_attr__( 'Value', 'flowmattic' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											// Set the row count.
											$row_count = 1;

											// Loop through the system variables.
											foreach ( wp_flowmattic()->variables->system_vars as $system_var => $system_var_data ) :
												?>
												<tr class="system-var variable-item">
													<th scope="row"><?php echo esc_attr( $row_count ); ?></th>
													<td>
														<?php echo esc_attr( $system_var ); ?>
														<span class="click-to-copy text-primary ms-1" data-var="{{<?php echo esc_attr( $system_var ); ?>}}"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="icon-md"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 4C10.8954 4 10 4.89543 10 6H14C14 4.89543 13.1046 4 12 4ZM8.53513 4C9.22675 2.8044 10.5194 2 12 2C13.4806 2 14.7733 2.8044 15.4649 4H17C18.6569 4 20 5.34315 20 7V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V7C4 5.34315 5.34315 4 7 4H8.53513ZM8 6H7C6.44772 6 6 6.44772 6 7V19C6 19.5523 6.44772 20 7 20H17C17.5523 20 18 19.5523 18 19V7C18 6.44772 17.5523 6 17 6H16C16 7.10457 15.1046 8 14 8H10C8.89543 8 8 7.10457 8 6Z" fill="currentColor"></path></svg></span>
													</td>
													<td><?php echo esc_attr( $system_var_data['variable_description'] ); ?></td>
													<td><code class="text-reset"><?php echo esc_attr( $system_var_data['variable_value'] ); ?></code></td>
												</tr>
												<?php
												// Increment the row count.
												++$row_count;
											endforeach;
											?>
										</tbody>
									</table>
								</div>
							</div> <!-- .fm-variables-system -->				
						</div> <!-- .fm-variables-list-container -->
					</div>
				</div> <!-- .flowmattic-dashboard -->
			</div>
		</div> <!-- .flowmattic-dashboard-content -->
	</div> <!-- .flowmattic-wrapper -->
</div>

<!-- New Custom Variable Modal -->
<div class="modal fade" id="newCustomVariableModal" tabindex="-1" role="dialog" aria-labelledby="newCustomVariableModal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="newCustomVariableModal-label"><?php esc_html_e( 'Create New Custom Variable', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body d-flex flex-column gap-4">
				<div class="form-group d-flex flex-column gap-1">
					<label for="variable_name"><?php esc_html_e( 'Variable Name', 'flowmattic' ); ?> <span class="badge text-danger">Required</span></label>
					<input type="text" class="form-control border border-dark" id="variable_name" required placeholder="my_custom_var OR myCustomVar">
					<small class="text-muted"><?php esc_html_e( 'Variable name should be unique and should not contain any special characters or spaces.', 'flowmattic' ); ?></small>
				</div>
				<div class="form-group d-flex flex-column gap-1">
					<label for="variable_value"><?php esc_html_e( 'Variable Value', 'flowmattic' ); ?> <span class="badge text-danger">Required</span></label>
					<textarea class="form-control border border-dark" id="variable_value" rows="2" required placeholder="Enter variable value"></textarea>
					<small class="text-muted"><?php esc_html_e( 'Variable value can be any text, number or boolean value.', 'flowmattic' ); ?></small>
				</div>
				<div class="form-group d-flex flex-column gap-1">
					<label for="variable_description"><?php esc_html_e( 'Variable Description', 'flowmattic' ); ?></label>
					<textarea class="form-control border border-dark" id="variable_description" rows="2" placeholder="Enter variable description"></textarea>
					<small class="text-muted"><?php esc_html_e( 'Variable description is optional and is used to describe the variable.', 'flowmattic' ); ?></small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="create-variable"><?php esc_html_e( 'Create Variable', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary" id="cancel-create-variable" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Help modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content border-0">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModal-label"><?php esc_html_e( 'FlowMattic Variables', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="accordion" id="helpAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingOne">
							<button class="accordion-button" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								<?php esc_html_e( 'What are FlowMattic Variables?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'FlowMattic Variables are special tags that can be used to store and manipulate data within your workflows. You can create custom variables and use them in your workflows to store and retrieve any data in text format, and use them in your workflows to retrieve its value.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTwo">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php esc_html_e( 'How to use FlowMattic Variables?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can use FlowMattic Variables in your workflows by using the variable name in double curly braces. For example, if you have a variable named "user_name", you can use it in your workflow by using "{{user_name}}".', 'flowmattic' ); ?><br><br>
								<?php esc_html_e( 'You can also use FlowMattic Variables in your workflows by selecting the variable from the "Variables" dropdown in the workflow builder.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingThree">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<?php esc_html_e( 'How to create a new FlowMattic Variable?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can create a new FlowMattic Variable by clicking the "New Variable" button above. You will be asked to enter the variable name, variable value and variable description. Variable name should be unique and should not contain any special characters or spaces. Variable value can be any text, number or boolean value. Variable description is optional and is used to describe the variable.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFour">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<?php esc_html_e( 'How to edit a FlowMattic Variable?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can edit a FlowMattic Variable by clicking the "Edit" button next to the variable. You will be asked to enter the new variable value and variable description. Variable value can be any text, number or boolean value. Variable description is optional and is used to describe the variable.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFive">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<?php esc_html_e( 'How to delete a FlowMattic Variable?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'You can delete a FlowMattic Variable by clicking the "Delete" button next to the variable. You will be asked to confirm the deletion. Once deleted, the variable cannot be recovered.', 'flowmattic' ); ?>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSix">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								<?php esc_html_e( 'Can I call PHP function as variable value?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-parent="#helpAccordion">
							<div class="accordion-body">
								<?php esc_html_e( 'Yes, you can! Just make sure to prepend the value with PHP: and then use your function name with parenthesis. For example, ', 'flowmattic' ); ?><code>PHP:time()</code>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSeven">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
								<?php esc_html_e( 'Few things to note about FlowMattic Variables', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-parent="#helpAccordion">
							<div class="accordion-body">
								<ul style="list-style: square;">
									<li><?php esc_html_e( 'FlowMattic Variables are case sensitive.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'You can modify the value of custom variables in your workflows. However, you cannot modify the value of system variables.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'No action is reversible. Once deleted, or modified, the variable cannot be recovered.', 'flowmattic' ); ?></li>
									<li><?php esc_html_e( 'Variable values are global. If you modify the value of a variable in one workflow, it will be modified in all other workflows as well.', 'flowmattic' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
					<a href="https://help.flowmattic.com/docs/setup/create-custom-variables/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Read more' );?></a>
					<button type="button" class="btn btn-secondary" id="close-help-modal" data-dismiss="modal"><?php esc_html_e( 'Close', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>


<style type="text/css">
.variables-table {
	overflow-x: auto;
	max-height: 500px;
}
.fm-variables-heading {
	font-size: 18px;
	font-weight: 600;
}
.flowmattic-variables th {
	font-weight: 600;
}
.hover-actions .btn,
.variable-item .click-to-copy {
	cursor: pointer;
	opacity: 0;
	transition: opacity 0.3s ease-in-out;
}
.variable-item:hover .hover-actions .btn,
.variable-item:hover .click-to-copy {
	opacity: 1;
}
.custom-var.variable-item code {
    max-width: 400px !important;
    overflow: hidden;
    display: block;
    width: fit-content;
    height: 26px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	const swalPopup = window.Swal.mixin({
		customClass: {
			confirmButton: 'btn btn-primary shadow-none me-xxl-3',
			cancelButton: 'btn btn-danger shadow-none'
		},
		buttonsStyling: false
	} );

	// If query string is present, open the modal.
	const urlParams = new URLSearchParams( window.location.search );
	var openModal = urlParams.get( 'openModal' );

	if ( 'createVariable' === openModal ) {
		// Open the modal.
		jQuery( '#newCustomVariableModal' ).modal( 'show' );

		// Remove openModal from the URL.
		var newURL = urlParams.toString().replace( '&openModal=createVariable', '' );
		window.history.pushState( '', '', '?' + newURL );
	}

	// Copy the chatbot response to clipboard.
	jQuery(document).on('click', '.click-to-copy', function() {
		var copyButton = jQuery( this ),
			copiedIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="icon-md"><path fill-rule="evenodd" clip-rule="evenodd" d="M18.0633 5.67375C18.5196 5.98487 18.6374 6.607 18.3262 7.06331L10.8262 18.0633C10.6585 18.3093 10.3898 18.4678 10.0934 18.4956C9.79688 18.5234 9.50345 18.4176 9.29289 18.2071L4.79289 13.7071C4.40237 13.3166 4.40237 12.6834 4.79289 12.2929C5.18342 11.9023 5.81658 11.9023 6.20711 12.2929L9.85368 15.9394L16.6738 5.93664C16.9849 5.48033 17.607 5.36263 18.0633 5.67375Z" fill="currentColor"></path></svg>',
			copyIcon = copyButton.html();

		// Get the variable.
		var variable = copyButton.data( 'var' );

		// Use the clipboard API to copy the text to clipboard.
		navigator.clipboard.writeText( variable );

		// Replace the copy icon with the copied icon.
		copyButton.html( copiedIcon );

		// Reset the copy icon after 2 seconds.
		setTimeout(function() {
			copyButton.html( copyIcon );
		}, 2000);
	});

	// Replace the spaces and special characters with underscores.
	jQuery( '#variable_name' ).on( 'keyup', function() {
		var variable_name = jQuery( this ).val();

		// Replace the spaces and special characters with underscores.
		variable_name = variable_name.replace( /[^a-zA-Z0-9]/g, '_' );

		// Replace multiple underscores with a single underscore.
		variable_name = variable_name.replace( /_+/g, '_' );

		// Replace the underscores at the beginning of the string.
		variable_name = variable_name.replace( /^_+/g, '' );

		// Set the variable name.
		jQuery( this ).val( variable_name );
	} );

	// Create new variable.
	jQuery( '#create-variable' ).on( 'click', function() {
		// Get the variable name.
		var variable_name = jQuery( '#variable_name' ).val();

		// Get the variable value.
		var variable_value = jQuery( '#variable_value' ).val();

		// Get the variable description.
		var variable_description = jQuery( '#variable_description' ).val();

		// If the variable name is empty, show an error message.
		if ( '' === variable_name ) {
			swalPopup.fire( {
				icon: 'error',
				title: '<?php echo esc_attr__( 'Please enter a variable name.', 'flowmattic' ); ?>',
				showConfirmButton: true,
				timer: 1000
			} );

			return;
		}

		// If the variable value is empty, show an error message.
		if ( '' === variable_value ) {
			swalPopup.fire( {
				icon: 'error',
				title: '<?php echo esc_attr__( 'Please enter a variable value.', 'flowmattic' ); ?>',
				showConfirmButton: true,
				timer: 1000
			} );

			return;
		}

		// If all good, show a loading message.
		swalPopup.fire( {
			icon: 'info',
			title: '<?php echo esc_attr__( 'Creating variable...', 'flowmattic' ); ?>',
			didOpen: function() {
				swalPopup.showLoading();
			}
		} );

		// Create the variable.
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'flowmattic_create_variable',
				variable_name: variable_name,
				variable_value: variable_value,
				variable_description: variable_description,
				nonce: FMConfig.workflow_nonce
			},
			success: function( response ) {
				// If the variable was created successfully, append the variable to the table, remove the d-none class from the table and hide the modal.
				if ( response.success && 'Created' === response.data ) {
					var variable_count = jQuery( '.custom-variables-table tbody tr' ).length;

					// Append the variable to the table.
					jQuery( '.custom-variables-table tbody' ).append( '<tr class="custom-var variable-item"><th scope="row">' + ( variable_count + 1 ) + '</th><td>' + variable_name + '<span class="click-to-copy text-primary ms-1" data-var="{{' + variable_name + '}}"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="icon-md"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 4C10.8954 4 10 4.89543 10 6H14C14 4.89543 13.1046 4 12 4ZM8.53513 4C9.22675 2.8044 10.5194 2 12 2C13.4806 2 14.7733 2.8044 15.4649 4H17C18.6569 4 20 5.34315 20 7V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V7C4 5.34315 5.34315 4 7 4H8.53513ZM8 6H7C6.44772 6 6 6.44772 6 7V19C6 19.5523 6.44772 20 7 20H17C17.5523 20 18 19.5523 18 19V7C18 6.44772 17.5523 6 17 6H16C16 7.10457 15.1046 8 14 8H10C8.89543 8 8 7.10457 8 6Z" fill="currentColor"></path></svg></span></td><td>' + variable_description + '</td><td><code class="text-reset">' + variable_value + '</code></td><td class="text-end hover-actions">\
														<a href="javascript:void(0);" class="btn btn-sm btn-outline-primary edit-variable" data-variable-name="' + variable_name + '">\
															<span class="dashicons dashicons-edit"></span>\
														</a>\
														<a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-variable" data-variable-name="' + variable_name + '">\
															<span class="dashicons dashicons-trash"></span>\
														</a>\
													</td></tr>' );

					// Remove the d-none class from the table.
					jQuery( '.table-custom-vars' ).removeClass( 'd-none' );

					// Hide the no variables message.
					jQuery( '.fm-no-variables' ).addClass( 'd-none' );

					// Show a success message.
					swalPopup.fire( {
						icon: 'success',
						title: '<?php echo esc_attr__( 'Variable created successfully.', 'flowmattic' ); ?>',
						showConfirmButton: true,
						timer: 1000
					} );
				} else if ( response.success && 'Updated' === response.data ) {
					// If the variable was updated successfully, update the variable in the table and hide the modal.
					// Get the variable row.
					var variable_row = jQuery( '.custom-variables-table tbody [data-variable-name="' + variable_name + '"]' ).closest( '.variable-item' );

					// Update the variable name.
					variable_row.find( 'td:nth-child(2)' ).text( variable_name );

					// Update the variable description.
					variable_row.find( 'td:nth-child(3)' ).text( variable_description );

					// Update the variable value.
					variable_row.find( 'td:nth-child(4) code' ).text( variable_value );

					// Show a success message.
					swalPopup.fire( {
						icon: 'success',
						title: '<?php echo esc_attr__( 'Variable updated successfully.', 'flowmattic' ); ?>',
						showConfirmButton: true,
						timer: 1000
					} );

					// Close the modal.
					jQuery( '#newCustomVariableModal' ).modal( 'hide' );
				} else {
					// Show an error message.
					swalPopup.fire( {
						icon: 'error',
						title: '<?php echo esc_attr__( 'There was an error creating the variable. Please try again.', 'flowmattic' ); ?>',
						showConfirmButton: true,
						timer: 1000
					} );
				}

				// Hide the modal.
				jQuery( '#newCustomVariableModal' ).modal( 'hide' );

				// Reset the variable name and value.
				jQuery( '#variable_name' ).val( '' );
				jQuery( '#variable_value' ).val( '' );
			}
		});
	});

	// Delete variable. Make sure the click on newly added elements is also captured.
	jQuery( document ).on( 'click', '.delete-variable', function() {
		var deleteButton = jQuery( this );

		swalPopup.fire( {
			title: 'Are you sure?',
			text: "This will permanently delete the variable and cannot be undone. Any workflows using this variable will show the variable name.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
		} ).then( function( result ) {
			if ( result.isConfirmed ) {
				// Get the variable name.
				var variable_name = deleteButton.data( 'variable-name' );

				// Show a loading message.
				swalPopup.fire( {
					icon: 'info',
					title: '<?php echo esc_attr__( 'Deleting variable...', 'flowmattic' ); ?>',
					didOpen: function() {
						swalPopup.showLoading();
					}
				} );

				// Delete the variable.
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'flowmattic_delete_variable',
						variable_name: variable_name,
						nonce: FMConfig.workflow_nonce
					},
					success: function( response ) {
						// If the variable was deleted successfully, remove the variable from the table.
						if ( response.success ) {
							// Remove the variable item row from the table.
							deleteButton.closest( '.variable-item' ).remove();

							// If there are no more variables, show the no variables message.
							if ( 0 === jQuery( '.custom-variables-table tbody tr' ).length ) {
								// Hide the table.
								jQuery( '.table-custom-vars' ).addClass( 'd-none' );

								// Show the no variables message.
								jQuery( '.fm-no-variables' ).removeClass( 'd-none' );
							}

							// Show a success message.
							swalPopup.fire( {
								icon: 'success',
								title: '<?php echo esc_attr__( 'Variable deleted successfully.', 'flowmattic' ); ?>',
								showConfirmButton: true,
								timer: 1000
							} );
						} else {
							// Show an error message.
							swalPopup.fire( {
								icon: 'error',
								title: '<?php echo esc_attr__( 'There was an error deleting the variable. Please try again.', 'flowmattic' ); ?>',
								showConfirmButton: true,
								timer: 1000
							} );
						}
					}
				});
			}
		} );
	});

	// Edit variable.
	jQuery( document ).on( 'click', '.edit-variable', function() {
		var editButton = jQuery( this );

		// Get the variable name.
		var variable_name = editButton.data( 'variable-name' );

		// Get the variable value.
		var variable_value = editButton.closest( '.variable-item' ).find( 'code' ).text();

		// Get the variable description.
		var variable_description = editButton.closest( '.variable-item' ).find( 'td:nth-child(3)' ).text();

		// Set the variable name.
		jQuery( '#variable_name' ).val( variable_name );

		// Disable the variable name field.
		jQuery( '#variable_name' ).prop( 'disabled', true );

		// Set the variable value.
		jQuery( '#variable_value' ).val( variable_value );

		// Set the variable description.
		jQuery( '#variable_description' ).val( variable_description );

		// Change the modal title.
		jQuery( '#newCustomVariableModal-label' ).text( '<?php echo esc_attr__( 'Edit Custom Variable', 'flowmattic' ); ?>' );

		// Change the button text.
		jQuery( '#create-variable' ).text( '<?php echo esc_attr__( 'Update Variable', 'flowmattic' ); ?>' );

		// Show the modal.
		jQuery( '#newCustomVariableModal' ).modal( 'show' );
	});

	// On modal close, reset the modal.
	jQuery( '#newCustomVariableModal' ).on( 'hidden.bs.modal', function() {
		// Reset the modal title.
		jQuery( '#newCustomVariableModal-label' ).text( '<?php echo esc_attr__( 'Create New Custom Variable', 'flowmattic' ); ?>' );

		// Reset the button text.
		jQuery( '#create-variable' ).text( '<?php echo esc_attr__( 'Create Variable', 'flowmattic' ); ?>' );

		// Reset the variable name.
		jQuery( '#variable_name' ).val( '' );

		// Enable the variable name field.
		jQuery( '#variable_name' ).prop( 'disabled', false );

		// Reset the variable value.
		jQuery( '#variable_value' ).val( '' );

		// Reset the variable description.
		jQuery( '#variable_description' ).val( '' );
	});
} );
</script>
<?php FlowMattic_Admin::footer(); ?>
