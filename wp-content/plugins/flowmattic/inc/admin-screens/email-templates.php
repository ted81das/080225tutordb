<?php
/**
 * Admin page template for emails.
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
				<?php echo esc_html__( 'License key not registered. Please register your license first to use templates.', 'flowmattic' ); ?>
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
				<?php echo esc_html__( 'License key not valid. Please register your license first to use templates.', 'flowmattic' ); ?>
				</div>
			</div>
		</div>
	<?php
	wp_die();
}

$license             = wp_flowmattic()->check_license();
$all_email_templates = wp_flowmattic()->email_templates_db->get_all();
$prebuilt_templates  = wp_flowmattic()->email_templates->get_prebuilt_templates();
?>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-container flowmattic-templates-list-container w-100">
			<div class="flowmattic-dashboard-content container m-0 ps-3">
				<div class="row">
					<div id="flowmattic-templates">
						<div class="flowmattic-templates-list">
						<div class="fm-template-task-header d-flex mb-4 mt-4 justify-content-between">
								<h3 class="fm-template-heading m-0 d-flex align-items-center">
									<?php esc_html_e( 'Email Templates', 'flowmattic' ); ?>
								</h3>
								<div class="flowmattic-templates-header-actions">
									<a href="javascript:void(0);" class="btn btn-md btn-outline-primary d-inline-flex align-items-center justify-content-center py-2" data-toggle="modal" data-target="#helpModal">
										<span class="dashicons dashicons-editor-help d-inline-block fs-3 d-flex align-items-center justify-content-center"></span>
									</a>
									<?php
									$button_type  = '';
									$button_class = 'new-template-button';
									$button_url   = 'javascript:void(0);';

									if ( ! $license || '' === $license_key ) {
										$button_type  = 'disabled';
										$button_class = 'needs-registration';
									} else {
										?>
										<a href="<?php echo $button_url; ?>" <?php echo esc_attr( $button_type ); ?>  class="btn btn-md btn-primary d-inline-flex align-items-center justify-content-center <?php echo $button_class; ?>" data-toggle="modal" data-target="#createTemplateModal">
											<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
											<?php esc_html_e( 'Create New Template', 'flowmattic' ); ?>
										</a>
										<?php
									}
									?>
								</div>
							</div>
							<div class="templates-nav navbar mt-3 mb-3 bg-light">
								<span class="navbar-text ps-3">
									<?php esc_html_e( 'Create and manage your email templates.', 'flowmattic' ); ?>
									<a href="https://support.flowmattic.com/kb/article/61/how-to-use-the-email-template-builder-in-flowmattic/" target="_blank" class="text-decoration-none"><?php esc_html_e( 'Learn more' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a>
								</span>
							</div>
							<div class="flowmattic-templates-list-body">
								<div class="flowmattic-templates-list-body-inner">
									<div class="flowmattic-templates-list-body-inner-template bg-white">
										<table class="template bg-white d-flex flex-column">
											<?php
											if ( ! empty( $all_email_templates ) ) {
												?>
												<thead class="border-bottom d-flex w-100">
													<tr class="d-flex w-100 justify-content-between">
														<th class="px-3 py-2" style="width: 50%;"><?php echo esc_html__( 'Template Name', 'flowmattic' ); ?></th>
														<th class="px-3 py-2" style="width: 200px;"><?php echo esc_html__( 'Last Updated', 'flowmattic' ); ?></th>
														<th class="px-3 py-2" style="width: 200px;"><?php echo esc_html__( 'Sends', 'flowmattic' ); ?></th>
														<th class="px-3 py-2" style="width: 250px;"><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></th>
													</tr>
												</thead>
												<?php
											}
											?>
											<tbody class="d-flex flex-column w-100">
												<?php
												if ( ! empty( $all_email_templates ) ) {
													foreach ( $all_email_templates as $template ) {
														?>
														<tr class="d-flex w-100 justify-content-between border-bottom">
															<td class="p-3" style="width: 50%;">
																<a href="<?php echo esc_url( admin_url( 'admin.php?page=flowmattic-email-templates&action=edit&template-id=' . $template->email_template_id ) ); ?>" class="text-decoration-none">
																	<?php echo esc_html( $template->email_template_name ); ?>
																</a>
																<small class="text-secondary d-block">
																	ID: <?php echo esc_html( $template->email_template_id ); ?>
																</small>
															</td>
															<td class="p-3" style="width: 200px;">
																<?php echo esc_html( date_i18n( 'd-M-Y H:i', strtotime( $template->email_template_updated ) ) ); ?>
															</td>
															<td class="p-3" style="width: 200px;">
																<span><?php echo esc_html( $template->email_template_sent_count ); ?></span>
															</td>
															<td class="p-3" style="width: 250px;">
																<a href="<?php echo esc_url( admin_url( 'admin.php?page=flowmattic-email-templates&action=edit&template-id=' . $template->email_template_id ) ); ?>" class="btn btn-sm btn-outline-primary me-2 edit-template"><?php esc_html_e( 'Edit', 'flowmattic' ); ?></a>
																<a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary me-2 clone-template" data-id="<?php echo esc_attr( $template->email_template_id ); ?>"><?php esc_html_e( 'Clone', 'flowmattic' ); ?></a>
																<a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-template" data-id="<?php echo esc_attr( $template->email_template_id ); ?>"><?php esc_html_e( 'Delete', 'flowmattic' ); ?></a>
															</td>
														</tr>
														<?php
													}
												} else {
													?>
													<div class="card border-light mw-100 p-3">
														<div class="card-body text-center">
															<div class="no-records-found text-center mb-3">
																<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 44 44" fill="none"><g clip-path="url(#clip0_1996_241)"><rect x="-1.0249" y="8.57056" width="37.0156" height="37.7623" rx="7" transform="rotate(-15 -1.0249 8.57056)" fill="#EFF3F9"/><path d="M37.8231 7.51123L38.1803 8.07254L38.7416 8.42977L38.1803 8.787L37.8231 9.34831L37.4659 8.787L36.9045 8.42977L37.4659 8.07254L37.8231 7.51123Z" stroke="#65FA86" stroke-width="0.45927" stroke-linejoin="round"/><path d="M10.4964 38.0527V39.8898M9.57788 38.9713H11.415" stroke="#5F7BF8" stroke-width="0.45927" stroke-linecap="round"/><path d="M8 11C8 9.34314 9.34315 8 11 8H30C31.6569 8 33 9.34315 33 11V30.0001C33 31.6569 31.6568 33.0001 30 33.0001L11 32.9999C9.34313 32.9999 8 31.6568 8 29.9999V11Z" fill="white" stroke="#A3B2C8" stroke-width="2" stroke-linejoin="round"/><circle cx="4.13338" cy="30.3932" r="0.91854" stroke="#EF5858" stroke-width="0.45927"/><path d="M9 15H32" stroke="#A3B2C8" stroke-width="2" stroke-linecap="round"/><circle cx="28" cy="28" r="9" fill="white" stroke="#4C5666" stroke-width="2"/><path d="M28 23V28" stroke="#4C5666" stroke-width="2" stroke-linecap="round"/><circle cx="28" cy="32" r="1.5" fill="#4C5666"/></g><defs><clipPath id="clip0_1996_241"><rect width="44" height="44" fill="white"/></clipPath></defs></svg>
															</div>
															<h5 class="card-title text-dark"><?php esc_html_e( 'No Templates Found', 'flowmattic' ); ?></h5>
															<p class="card-text text-secondary">
																<?php esc_html_e( 'You can use email templates to send emails quickly and easily.', 'flowmattic' ); ?>
																<br>
																<?php esc_html_e( 'Click on the button below to create a new template.', 'flowmattic' ); ?>
															</p>
															<div class="d-flex justify-content-center">
																<a href="<?php echo esc_url( admin_url( 'admin.php?page=flowmattic-email-templates&action=edit&template-id=templateID' ) ); ?>" class="btn btn-primary d-inline-flex align-items-center justify-content-center create-new-template" data-toggle="modal" data-target="#createTemplateModal">
																	<span class="dashicons dashicons-plus-alt2 d-inline-block pe-3 ps-0 me-1"></span>
																	<?php esc_html_e( 'Create New Template', 'flowmattic' ); ?>
																</a>
															</div>
														</div>
													</div>
													<?php
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true" style="z-index: 999999;">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModalLabel"><?php esc_html_e( 'Email Templates Help', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<p><?php esc_html_e( 'Email templates are pre-designed email layouts that you can use to create and send emails quickly and easily. They allow you to save time and effort by providing a ready-made structure for your emails.', 'flowmattic' ); ?></p>
				<p><?php esc_html_e( 'You can create new email templates, edit existing ones, and delete templates that you no longer need. You can also use the templates in your workflows to send emails automatically.', 'flowmattic' ); ?></p>
				<div class="accordion" id="helpAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingOne">
							<button class="accordion-button" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								<?php esc_html_e( 'How to create a new email template?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To create a new email template, click on the "Create New Template" button. This will open a modal where you can enter the name of the template and select a pre-built template to use as a starting point.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTwo">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php esc_html_e( 'How to edit an email template?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To edit an email template, click on the name of the template in the list or the edit button next to the desired template. This will open the template editor where you can make changes to the template.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingThree">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<?php esc_html_e( 'How to delete an email template?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To delete an email template, click on the delete button next to the desired template. This will open a confirmation modal where you can confirm the deletion.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFour">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<?php esc_html_e( 'How to clone an email template?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To clone an email template, click on the clone button next to the desired template. This will create a copy of the template with a new name. The new template will have the name as the original template with " (Copy)" appended to it.  The new template will have the same content as the original template, but you can edit it separately.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFive">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<?php esc_html_e( 'How to use pre-built templates?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To use a pre-built template, click on the "Create New Template" button. This will open a modal where you can select a pre-built template to use as a starting point. You can then edit the template as needed. In case you want to change the template in the editor, you can do that as well.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSix">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								<?php esc_html_e( 'How to use templates in workflows?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'To use a template in a workflow, select the "Send Template Email" action in the workflow editor after you choose the Email SMTP module for your action step. Then, select the desired template from the list of available templates. You can also customize the email subject and other settings as needed.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingSeven">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
								<?php esc_html_e( 'How to get support?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'If you need help with email templates or have any questions, please contact our support team. We are here to help you!', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingEight">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
								<?php esc_html_e( 'How to get more templates?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'We are constantly adding new templates to our library. If you have any specific requests or suggestions for templates, please let us know. We would love to hear from you!', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingNine">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
								<?php esc_html_e( 'Can I export my templates?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'Yes, you can export your templates as JSON files. This allows you to back up your templates or share them with others. To export a template, click on the "Export" button in the template editor.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingTen">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
								<?php esc_html_e( 'Can I import templates from other FlowMattic installations?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'Yes, you can import templates from JSON files created in other FlowMattic installations. To import a template, click on the "Import" button in the template editor and copy the JSON code into the text area. This will create a new template based on the imported JSON data.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingEleven">
							<button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#collapseEleven" aria-expanded="false" aria-controls="collapseEleven">
								<?php esc_html_e( 'Can I use templates in other plugins?', 'flowmattic' ); ?>
							</button>
						</h2>
						<div id="collapseEleven" class="accordion-collapse collapse" aria-labelledby="headingEleven" data-parent="#helpAccordion">
							<div class="accordion-body">
								<p><?php esc_html_e( 'Unfortunately, email templates are designed to work specifically with FlowMattic. However, you can copy the HTML code from the template and use it in other plugins or email marketing services if you wish.', 'flowmattic' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="createTemplateModal" tabindex="-1" role="dialog" aria-labelledby="createTemplateModalLabel" aria-hidden="true" style="z-index: 999999;">
	<div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="createTemplateModalLabel"><?php esc_html_e( 'Create New Template', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<h6 class="form-label"><?php esc_html_e( 'Template Name', 'flowmattic' ); ?></h6>
				<input type="text" class="form-control" id="templateName" placeholder="<?php esc_html_e( 'Template Name', 'flowmattic' ); ?>" />
				<div class="pre-built-templates mt-3">
					<h6 class="m-0"><?php esc_html_e( 'Prebuilt Templates', 'flowmattic' ); ?></h6>
					<!-- Show 3 column grid -->
					<div class="container mt-3 px-0">
						<ul class="template-list p-0 px-0 d-grid" style="grid-template-columns: repeat(3, 1fr); grid-gap: 14px;">
							<?php
							foreach ( $prebuilt_templates as $template ) {
								?>
								<li class="template-list-item p-0 m-0">
									<div class="d-flex flex-column justify-content-between align-items-center w-100 border" style="background-color: rgb(237, 242, 245); height: 280px;">
										<div class="template-image d-flex justify-content-center align-items-center w-100" style="height: calc( 100% - 48px );">
											<img src="<?php echo esc_url( $template['img'] ); ?>" alt="<?php echo esc_attr( $template['title'] ); ?>" class="img-fluid"
											style="
												object-fit: <?php echo esc_attr( $template['title'] ) === 'Blank template' ? 'contain' : 'cover'; ?>;
												object-position: <?php echo esc_attr( $template['title'] ) === 'Blank template' ? 'center' : 'top'; ?>;
												height: <?php echo esc_attr( $template['title'] ) === 'Blank template' ? '48px' : '100%'; ?>;
											"/>
										</div>
										<div class="template-info d-flex justify-content-between align-items-center border-top p-2 w-100 bg-light">
											<h6 class="template-title m-0"><?php echo esc_html( $template['title'] ); ?></h6>
											<a href="javascript:void(0);" data-sample-id="<?php echo $template['template'] ?>" class="btn btn-sm btn-outline-primary create-template-button"><?php esc_html_e( 'Use Template', 'flowmattic' ); ?></a>
										</div>
									</div>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="deleteTemplateModal" tabindex="-1" role="dialog" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true" style="z-index: 999999;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteTemplateModalLabel"><?php esc_html_e( 'Delete Template', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body">
				<p><?php esc_html_e( 'Are you sure you want to delete this template?', 'flowmattic' ); ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
				<a href="#" id="confirmDeleteTemplateBtn" class="btn btn-danger"><?php esc_html_e( 'Delete', 'flowmattic' ); ?></a>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		// If query string is present, open the modal.
		const urlParams = new URLSearchParams( window.location.search );
		var openModal = urlParams.get( 'openModal' );

		if ( 'createEmailTemplate' === openModal ) {
			// Open the modal.
			jQuery( '#createTemplateModal' ).modal( 'show' );

			// Remove openModal from the URL.
			var newURL = urlParams.toString().replace( '&openModal=createEmailTemplate', '' );
			window.history.pushState( '', '', '?' + newURL );
		}

		// Create template.
		jQuery('.create-template-button').on('click', function(e) {
			e.preventDefault();
			// Generate a random template ID similar to wp_generate_uuid4().
			var templateID = crypto.randomUUID();

			// Get the template sample ID from the data attribute.
			var templateSampleID = jQuery(this).data('sample-id');
			var templateName = jQuery('#templateName').val();
			if ( '' === templateName ) {
				alert('<?php esc_html_e( 'Please enter a template name.', 'flowmattic' ); ?>');
				return;
			}
			window.location.href = '<?php echo admin_url( 'admin.php?page=flowmattic-email-templates&action=edit&template-id=' ); ?>' + templateID + '&template-name=' + templateName + '&template=' + templateSampleID;
		});

		// Clone template
		jQuery('.clone-template').on('click', function(e) {
			var $this = jQuery(this);
			e.preventDefault();
			var templateId = jQuery(this).data('id');
			
			// Show loading spinner.
			jQuery(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php esc_html_e( 'Cloning...', 'flowmattic' ); ?>');
			jQuery(this).attr('disabled', true).addClass('disabled');

			// Hide the edit and delete buttons.
			jQuery( e.currentTarget ).closest( 'tr' ).find( '.delete-template, .edit-template' ).hide();

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				data: {
					action: 'flowmattic_clone_email_template',
					nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_email_template_nonce' ) ); ?>',
					template_id: templateId,
				},
				success: function(response) {
					if (response.success) {
						// Show success icon and text for 2 seconds.
						$this.html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cloned', 'flowmattic' ); ?>');
						setTimeout(function() {
							$this.html('<?php esc_html_e( 'Redirecting...', 'flowmattic' ); ?>');
							window.location.href = '<?php echo admin_url( 'admin.php?page=flowmattic-email-templates&action=edit&template-id=' ); ?>' + response.data;
						}, 1000);
					} else {
						alert(response.data);
					}
				},
				error: function() {
					alert('<?php esc_html_e( 'Error cloning template.', 'flowmattic' ); ?>');
				}
			});
		});

		// Delete template
		jQuery('.delete-template').on('click', function(e) {
			e.preventDefault();
			var templateId = jQuery(this).data('id');
			jQuery('#deleteTemplateModal').modal('show');

			jQuery( '#confirmDeleteTemplateBtn' ).on( 'click', function( e ) {
				e.preventDefault();

				// Show loading spinner.
				jQuery('#confirmDeleteTemplateBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php esc_html_e( 'Deleting...', 'flowmattic' ); ?>');
				jQuery('#confirmDeleteTemplateBtn').attr('disabled', true).addClass('disabled');

				jQuery.ajax({
					type: 'POST',
					url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
					data: {
						action: 'flowmattic_delete_email_template',
						nonce: '<?php echo esc_attr( wp_create_nonce( 'flowmattic_email_template_nonce' ) ); ?>',
						template_id: templateId,
					},
					success: function(response) {
						if (response.success) {
							// Show success icon and text for 2 seconds.
							jQuery('#confirmDeleteTemplateBtn').html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Deleted', 'flowmattic' ); ?>');
							jQuery('#confirmDeleteTemplateBtn').attr('disabled', false);
							setTimeout(function() {
								jQuery('#confirmDeleteTemplateBtn').html('<?php esc_html_e( 'Delete', 'flowmattic' ); ?>');
								jQuery('#deleteTemplateModal').modal('hide');
								// Reload the page after 2 seconds.
								window.location.reload();
							}, 1000);
						} else {
							alert(response.data);
						}
					},
					error: function() {
						alert('<?php esc_html_e( 'Error deleting template.', 'flowmattic' ); ?>');
						jQuery('#deleteTemplateModal').modal('hide');
					}
				});
			});
		});
	});
</script>
<style type="text/css">
	.modal-backdrop.fade.show {
		z-index: 99999;
	}
</style>
<?php
// FlowMattic_Admin::footer();
add_filter( 'admin_footer_text', function( $text ) {
	return '';
} );

add_filter( 'update_footer', function( $text ) {
	return '';
} );
