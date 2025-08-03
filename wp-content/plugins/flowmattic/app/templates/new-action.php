<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */

?>
<script type="text/html" id="flowmattic-workflow-action-template">
	<div class="fm-workflow-action fm-workflow-step step-new" step-id="{{{ stepID }}}">
		<div class="fm-workflow-step-header">
			<div class="fm-workflow-icon">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="#0d6efd" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
					<path fill="inherit" d="M16 2L6 13.5H10L8 22L18 11.5H14L16 2Z" undefined="1.5"></path>
				</svg>
			</div>
			<div class="fm-workflow-step-info">
				<span class="fm-workflow-hint-label"><?php esc_attr_e( 'Action: Do this...', 'flowmattic' ); ?></span>
				<h4 class="fm-workflow-step-application-title"><?php esc_attr_e( 'Choose an action', 'flowmattic' ); ?></h4>
			</div>
			<div class="fm-workflow-step-header-actions">
				<div class="fm-workflow-step-action fm-workflow-step-close" data-toggle="tooltip" title="<?php echo esc_html__( 'Delete Action Step', 'flowmattic' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" fill="none" d="M16.13 22H7.87C7.37 22 6.95 21.63 6.88 21.14L5 8H19L17.12 21.14C17.05 21.63 16.63 22 16.13 22Z"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" d="M3.5 8H20.5"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" d="M10 12V18"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" d="M14 12V18"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" fill="none" d="M16 5H8L9.7 2.45C9.89 2.17 10.2 2 10.54 2H13.47C13.8 2 14.12 2.17 14.3 2.45L16 5Z"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#ffffff" d="M3 5H21"></path>
					</svg>
				</div>
				<div class="fm-workflow-step-action fm-workflow-step-accordion-collapse" data-toggle="tooltip" title="<?php echo esc_html__( 'Toggle Action Step', 'flowmattic' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#ffffff" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
						<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#ffffff" d="M6.5 9.5L12 15L17.5 9.5"></path>
					</svg>
				</div>
			</div>
		</div>
		<div class="fm-workflow-step-body w-100">
			<div class="fm-workflow-action-select w-100">
				<div class="fm-workflow-actions-popup">
					<div class="fm-workflow-applications form-group">
						<div class="fm-workflow-action-heading">
							<h4><?php esc_attr_e( 'Choose Application', 'flowmattic' ); ?></h4>
						</div>
						<div class="flowmattic-dropdown">
							<select name="workflow-application" class="workflow-application w-100" title="Choose Application" data-live-search="true">
								<optgroup label="FlowMattic Apps" data-max-options="1">
									<#
									const sortedApps = {};
									Object.keys(actionApps).sort().forEach(key => {
										sortedApps[key] = actionApps[key];
									});
									_.each( sortedApps, function( settings, appSlug ) {
										settings.name = settings.name.replace( 'by FlowMattic', '' );
										#>
										<option
											<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
											<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}" <# } #>
											value="{{{appSlug}}}">
											{{{ settings.name }}}
										</option>
										<#
									} );
									#>
								</optgroup>
								<optgroup label="Other Apps" data-max-options="1">
									<#
									_.each( otherActionApps, function( settings, appSlug ) {
										#>
										<option
											<# if ( 'undefined' !== typeof application && appSlug === application ) { #>selected<# } #>
											<# if ( '' !== settings.icon ) { #> data-content="<img data-src='{{{ settings.icon }}}' />{{{ settings.name }}}" <# } #>
											value="{{{appSlug}}}">
											{{{ settings.name }}}
										</option>
										<#
									} );
									#>
								</optgroup>
							</select>
						</div>
					</div>
					<div class="built-in-actions">
						<h4 class="fw-bold fs-5"><?php esc_html_e( 'Built-in Apps', 'flowmattic' ); ?></h4>
						<span><?php esc_html_e( 'Following are the frequently used built-in apps. Click on the app to use it.', 'flowmattic' ); ?></span>
					</div>
					<div class="fm-workflow-core-actions">
						<?php
						$flowmattic_apps  = wp_flowmattic()->apps;
						$all_applications = $flowmattic_apps->get_all_applications();
						?>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="api">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['api'] ) ? $all_applications['api']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'API', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Connect with 3rd party apps using powerful API.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="filter">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['filter'] ) ? $all_applications['filter']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'Filter', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Add conditions before processing next steps.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="delay">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['delay'] ) ? $all_applications['delay']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'Delay', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Pause the workflow for certain amount of time.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="iterator">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['iterator'] ) ? $all_applications['iterator']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'Iterator', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Repeat actions based on the number of items in an object.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="iterator_storage">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['iterator_storage'] ) ? $all_applications['iterator_storage']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'Iterator Storage', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Store data from multiple actions within Iterator.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
						<div class="fm-workflow-core-highlight-app">
							<button class="d-flex border-0 bg-light p-3 rounded-3 flowmattic-action-app" data-action="iterator_end">
								<div class="highlight-app-icon pe-3">
									<img src="<?php echo ( isset( $all_applications['iterator_end'] ) ? $all_applications['iterator_end']['icon'] : '' ); ?>" style="width:48px; height:48px;"/>
								</div>
								<div class="highlight-app-title text-start">
									<strong><?php echo esc_html__( 'Iterator End', 'flowmattic' ); ?></strong>
									<p class="description"><?php echo esc_html__( 'Stop the Iterator loop at this action step.', 'flowmattic' ); ?></p>
								</div>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fm-workflow-add-step">
		<a href="javascript:void(0)" class="fm-add-step fm-add-action" data-toggle="tooltip" title="<?php echo esc_html__( 'Add New Action', 'flowmattic' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
		</a>
	</div>
</script>
