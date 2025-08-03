<?php FlowMattic_Admin::loader(); ?>
<div class="wrap flowmattic-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<?php
		$_user            = wp_get_current_user();
		$users_email      = $_user->user_email;
		$flowmattic_apps  = wp_flowmattic()->apps;
		$all_applications = $flowmattic_apps->get_all_applications();

		if ( current_user_can( 'manage_options' ) ) {
			$all_workflows = wp_flowmattic()->workflows_db->get_all_with_pagination( 0, 5 );
		} else {
			$all_workflows = wp_flowmattic()->workflows_db->get_user_workflows( $users_email );
		}

		$all_workflows_count = wp_flowmattic()->workflows_db->get_all_count();

		$task_count = wp_flowmattic()->tasks_db->get_tasks_count();
		$settings   = get_option( 'flowmattic_settings', array() );

		// Get all custom app types.
		$custom_apps = $custom_apps_db = wp_flowmattic()->custom_apps_db->get_all();
		$apps_count  = ( ! empty( $custom_apps ) ) ? count( $custom_apps ) : 0;

		// Get all connects.
		$all_connects   = wp_flowmattic()->connects_db->get_all();
		$connects_count = ( ! empty( $all_connects ) ) ? count( $all_connects ) : 0;

		$license_key      = get_option( 'flowmattic_license_key', '' );
		$license          = wp_flowmattic()->check_license();
		$all_integrations = (array) flowmattic_get_integrations();

		// Get tables count.
		$tables_schema = (array) wp_flowmattic()->tables_schema_db->get_all();
		$tables_count  = ( ! empty( $tables_schema ) ) ? count( $tables_schema ) : 0;

		// Get the AI Assistants.
		$assistants = wp_flowmattic()->chatbots_db->get_all();
		$assistants_count = ( ! empty( $assistants ) ) ? count( $assistants ) : 0;

		// Get the custom variables.
		$custom_vars = wp_flowmattic()->variables->get_custom_vars();
		$variables_count = ( ! empty( $custom_vars ) ) ? count( $custom_vars ) : 0;

		// Get email templates count
		$all_email_templates   = wp_flowmattic()->email_templates_db->get_all();
		$email_templates_count = ( ! empty( $all_email_templates ) ) ? count( $all_email_templates ) : 0;
		
		// Get MCP tools count
		$server_id = get_option( 'flowmattic_mcp_server_id', 0 );
		$mcp_tools_count = 0;
		if ( $server_id ) {
			$mcp_tools = wp_flowmattic()->mcp_server_db->get_tools_by_server( $server_id );
			$mcp_tools_count = ( ! empty( $mcp_tools ) ) ? count( $mcp_tools ) : 0;
		}
		?>
		<div class="flowmattic-dashboard-content container m-0 ps-3">
			<div class="row">
				<div class="flowmattic-content-block col-12 pe-2">
					<!-- Hero Section -->
					<div class="hero-section">
						<div class="hero-content">
							<h1 class="hero-title">Welcome to FlowMattic!</h1>
							<p class="hero-subtitle">Accelerate productivity with FlowMattic, facilitating limitless workflow automation. Assemble your workflows interruption-free and connect your applications like a seasoned professional!</p>
						</div>
					</div>

					<!-- Quick Actions -->
					<div class="start-from-scratch-section mb-2">
						<div class="card-header py-3 bg-transparent border-0 pb-0">
							<h5 class="section-title"><?php echo esc_html__( 'Start From Scratch', 'flowmattic' ); ?></h5>
						</div>
						<div class="action-grid">
							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 512 512" style="transform: rotate(180deg);"><path d="M384 32a64 64 0 00-57.67 91.73l-70.83 80.82-70.19-80.1A64 64 0 10128 160c1.1 0 2.2 0 3.29-.08L224 265.7v94.91a64 64 0 1064 0v-96.05l91.78-104.71c1.39.09 2.8.15 4.22.15a64 64 0 000-128zM96 96a32 32 0 1132 32 32 32 0 01-32-32zm160 352a32 32 0 1132-32 32 32 0 01-32 32zm128-320a32 32 0 1132-32 32 32 0 01-32 32z"/></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Create a New Workflow', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Create a new workflow from scratch and automate your tasks in a few clicks.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=new&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) ); ?>" class="action-button"><?php echo esc_html__( 'Create Workflow', 'flowmattic' ); ?></a>
							</div>

							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Core-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="table" fill="currentColor" fill-rule="nonzero"><path d="M0,2.34065934 C0,0.683805091 0.255420331,0 1.91226143,0 L22.1868258,0 C23.8436669,0 24,0.683805091 24,2.34065934 L24,21.7912088 C24,23.448063 23.8436669,24 22.1868258,24 L1.91226143,24 C0.255420331,24 0,23.448063 0,21.7912088 L0,2.34065934 Z M22.5000101,6 L16.5000578,6 L16.5000578,10.5 L22.5000101,10.5 L22.5000101,6 Z M22.5000101,12 L16.5000578,12 L16.5000578,16.5 L22.5000101,16.5 L22.5000101,12 Z M22.5000101,18 L16.5000578,18 L16.5000578,22.5 L21.5274904,22.5164835 C22.3559109,22.5164835 22.5164935,22.2569986 22.5164935,21.4285714 L22.5000101,18 Z M15.0000697,22.5 L15.0000697,18 L9.00011728,18 L9.00011728,22.5 L15.0000697,22.5 Z M7.50012918,22.5 L7.50012918,18 L1.50017679,18 L1.51666018,21.3296703 C1.51666018,22.1580975 1.67724277,22.5164835 2.50566332,22.5164835 L7.50012918,22.5 Z M1.50017679,16.5 L7.50012918,16.5 L7.50012918,12 L1.50017679,12 L1.50017679,16.5 Z M1.50017679,10.5 L7.50012918,10.5 L7.50012918,6 L1.50017679,6 L1.50017679,10.5 Z M9.00011728,6 L9.00011728,10.5 L15.0000697,10.5 L15.0000697,6 L9.00011728,6 Z M15.0000697,12 L9.00011728,12 L9.00011728,16.5 L15.0000697,16.5 L15.0000697,12 Z" id="Shape"></path></g></g></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Create a New Table', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Create a new table to store your data and automate it with your workflows.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-tables&openModal=createTable&nonce=' . wp_create_nonce( 'flowmattic-table-new' ) ); ?>" class="action-button"><?php echo esc_html__( 'Create Table', 'flowmattic' ); ?></a>
							</div>

							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 -960 960 960" fill="currentColor"><path d="M240-400h320v-80H240v80Zm0-120h480v-80H240v80Zm0-120h480v-80H240v80ZM80-80v-720q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H240L80-80Zm126-240h594v-480H160v525l46-45Zm-46 0v-480 480Z"/></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Create a New AI Assistant', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Create a new AI assistant to automate replies to your customer queries in chat.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-ai-assistants&openModal=createAssistant&nonce=' . wp_create_nonce( 'flowmattic-assistant-new' ) ); ?>" class="action-button"><?php echo esc_html__( 'Create Assistant', 'flowmattic' ); ?></a>
							</div>

							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.9061 21C21.2464 18.2888 22 15.2329 22 12C22 8.76711 21.2464 5.71116 19.9061 3M4.09393 3C2.75363 5.71116 2 8.76711 2 12C2 15.2329 2.75363 18.2888 4.09393 21M16.5486 8.625H16.459C15.8056 8.625 15.1848 8.91202 14.7596 9.41072L9.38471 15.7143C8.95948 16.213 8.33871 16.5 7.6853 16.5H7.59563M8.71483 8.625H10.1089C10.6086 8.625 11.0477 8.95797 11.185 9.44094L12.9594 15.6841C13.0967 16.167 13.5358 16.5 14.0355 16.5H15.4296" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Create Variable', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Create custom variables to store and reuse data across your workflows.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-variables&openModal=createVariable' ); ?>" class="action-button"><?php echo esc_html__( 'Create Variable', 'flowmattic' ); ?></a>
							</div>

							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Core-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="email-templates" fill="currentColor" fill-rule="nonzero"><path d="M18.3714118,1.5 L5.62390158,1.5 C5.00058582,1.5 4.49912127,2.0015625 4.49912127,2.625 L4.49912127,10.4296875 L3.6321031,9.7125 C3.43526655,9.553125 3.22437024,9.4171875 2.99941418,9.309375 L2.99941418,2.625 C2.99941418,1.1765625 4.17574692,0 5.62390158,0 L18.3714118,0 C19.8195665,0 20.9958992,1.1765625 20.9958992,2.625 L20.9958992,9.309375 C20.7709432,9.4171875 20.5600469,9.553125 20.3678969,9.7125 L19.4961921,10.4296875 L19.4961921,2.625 C19.4961921,2.0015625 18.9947276,1.5 18.3714118,1.5 Z M1.64030463,12 C1.56531927,12 1.49970709,12.0609375 1.49970709,12.140625 L1.49970709,21 C1.49970709,21.8296875 2.16988869,22.5 2.99941418,22.5 L20.9958992,22.5 C21.8254247,22.5 22.4956063,21.8296875 22.4956063,21 L22.4956063,12.140625 C22.4956063,12.065625 22.4346807,12 22.3550088,12 C22.3222027,12 22.2940832,12.009375 22.2659637,12.0328125 L13.8019918,18.9890625 C13.2958407,19.40625 12.6537786,19.6359375 11.9976567,19.6359375 C11.3415349,19.6359375 10.6994728,19.40625 10.1933216,18.9890625 L1.72466315,12.0328125 C1.70123023,12.0140625 1.66842414,12 1.63561804,12 L1.64030463,12 Z M0,12.140625 C0,11.2359375 0.731107206,10.5 1.64030463,10.5 C2.01991798,10.5 2.38547159,10.63125 2.68072642,10.8703125 L11.1446983,17.8265625 C11.3837141,18.0234375 11.6883421,18.13125 11.9976567,18.13125 C12.3069713,18.13125 12.6115993,18.0234375 12.8506151,17.8265625 L21.3192736,10.8703125 C21.6145284,10.63125 21.980082,10.5 22.3596954,10.5 C23.2642062,10.5 24,11.23125 24,12.140625 L24,21 C24,22.6546875 22.6549502,24 21.0005858,24 L2.99941418,24 C1.34504979,24 0,22.6546875 0,21 L0,12.140625 Z M8.24838899,6 L15.7469244,6 C16.1593439,6 16.496778,6.3375 16.496778,6.75 C16.496778,7.1625 16.1593439,7.5 15.7469244,7.5 L8.24838899,7.5 C7.83596954,7.5 7.49853544,7.1625 7.49853544,6.75 C7.49853544,6.3375 7.83596954,6 8.24838899,6 Z M8.24838899,10.5 L15.7469244,10.5 C16.1593439,10.5 16.496778,10.8375 16.496778,11.25 C16.496778,11.6625 16.1593439,12 15.7469244,12 L8.24838899,12 C7.83596954,12 7.49853544,11.6625 7.49853544,11.25 C7.49853544,10.8375 7.83596954,10.5 8.24838899,10.5 Z" id="Shape"></path></g></g></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Create Email Template', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Design professional email templates for automated communications.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-email-templates&openModal=createEmailTemplate' ); ?>" class="action-button"><?php echo esc_html__( 'Create Template', 'flowmattic' ); ?></a>
							</div>

							<div class="action-card">
								<div class="action-card-header">
									<div class="action-icon">
										<svg xmlns="http://www.w3.org/2000/svg" height="20px" width="20px" fill="currentColor" viewBox="0 0 512 512"><path d="M197.9 253.9c12.9 6.4 24.7 14.8 35 25.2s18.8 22.2 25.2 35c105.8-30 156.7-79.5 181.5-126c25-46.8 27.3-97.6 22-137.7c-40.1-5.3-90.9-3-137.7 22c-46.5 24.8-96 75.8-126 181.5zM384 312.1l0 82.2c0 25.4-13.4 49-35.3 61.9l-88.5 52.5c-7.4 4.4-16.6 4.5-24.1 .2s-12.1-12.2-12.1-20.9l0-114.7c0-22.6-9-44.3-25-60.3s-37.7-25-60.3-25L24 288c-8.6 0-16.6-4.6-20.9-12.1s-4.2-16.7 .2-24.1l52.5-88.5c13-21.9 36.5-35.3 61.9-35.3l82.2 0C281.7-3.8 408.8-8.5 483.9 5.3c11.6 2.1 20.7 11.2 22.8 22.8c13.8 75.1 9.1 202.2-122.7 284zM28.3 511.9c-16 .4-28.6-12.2-28.2-28.2C1 446 7.7 379.7 42 345.5c34.4-34.4 90.1-34.4 124.5 0s34.4 90.1 0 124.5C132.3 504.3 66 511 28.3 511.9zm50.2-64.5c12.8-.7 31.2-3.7 41.3-13.7c11.4-11.4 11.4-30 0-41.4s-30-11.4-41.4 0c-10.1 10.1-13 28.5-13.7 41.3c-.5 8 5.9 14.3 13.9 13.9zM328 144a40 40 0 1 1 80 0 40 40 0 1 1 -80 0z"/></svg>
									</div>
									<h3 class="action-title"><?php echo esc_html__( 'Add MCP Tool', 'flowmattic' ); ?></h3>
								</div>
								<p class="action-description"><?php echo esc_html__( 'Add a new MCP tool to your MCP server for advanced automation capabilities.', 'flowmattic' ); ?></p>
								<a href="<?php echo admin_url( '/admin.php?page=flowmattic-mcp-server&openModal=createTool' ); ?>" class="action-button"><?php echo esc_html__( 'Add MCP Tool', 'flowmattic' ); ?></a>
							</div>
						</div>
					</div>

					<div class="ai-workflow-assistance-wrap mb-4 ms-2 d-none">
						<div class="card-header py-3 bg-white d-flex">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="24" width="24" size="24" name="miscAI"><path fill="var(--primary)" fill-rule="evenodd" d="m7.754 8.377 2.261-3.888 2.261 3.888.362.362L16.526 11l-3.888 2.261-.362.362-2.26 3.888-1.784-3.066-1.554 1.304 2.473 4.254h1.729l2.992-5.146 5.146-2.992v-1.73l-5.146-2.992-2.992-5.146H9.15L6.16 7.143l-5.146 2.993v1.729l4.243 2.467 1.628-1.367L3.504 11l3.889-2.26.361-.363Z" clip-rule="evenodd"></path><path fill="#00BCD4" d="m19.516 15.991 1.28 2.203L23 19.474l-2.203 1.282-1.281 2.203-1.281-2.203-2.203-1.281 2.203-1.281 1.28-2.203Z"></path></svg>
							<h5 class="fw-bold m-0 ms-2">
								<?php echo esc_html__( 'What would you like to automate?', 'flowmattic' ); ?>
								<span class="badge bg-warning text-dark ms-2">Beta</span>
							</h5>
						</div>
						<div class="card-body bg-white">
							<div class="d-flex justify-content-between align-items-center">
								<div class="ai-workflow-assistance m-2 w-100 d-flex">
									<textarea class="fm-textarea form-control mb-2 w-100 me-2 workflow-prompt" rows="1" style="height: 42px" placeholder="Describe your workflow.."></textarea>
									<div class="ai-workflow-assistance-action">
										<a href="javascript:void(0);" class="btn btn-outline-primary btn-sm generate-workflow-btn" style="height: 42px;align-items: center;display: flex;width: 60px;text-align: center;justify-content: center;">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" height="28" viewBox="0 0 32 32" width="28"><g fill="currentColor"><path d="m27.8709 4.42343c.1305.2831.3515.50762.6427.63453l1.2152.52715c.3616.16596.3616.66382 0 .82977l-1.2051.52715c-.2913.12691-.5223.35144-.6428.63454l-.9541 2.16718c-.1707.34165-.6829.34165-.8536 0l-.9541-2.16718c-.1306-.2831-.3515-.50763-.6428-.63454l-1.2051-.52715c-.3616-.16595-.3616-.66381 0-.82977l1.2051-.52715c.2913-.12691.5223-.35143.6428-.63453l.9541-2.16718c.1707-.34167.6829-.34167.8536 0z"/><path d="m15.9834 5.1646c.0936.19469.2497.35045.4578.43806l.8637.36017c.2601.11681.2601.45753 0 .57434l-.8637.36018c-.2081.08761-.3746.24336-.4578.43805l-.6764 1.48938c-.1249.23363-.4891.23363-.614 0l-.6763-1.48938c-.0937-.19469-.2498-.35044-.4579-.43805l-.8637-.36018c-.2601-.11681-.2601-.45753 0-.57434l.8637-.36017c.2081-.08761.3746-.24337.4579-.43806l.6763-1.48938c.1249-.23363.4891-.23363.614 0z"/><path d="m4 26-1.6625 1.6271c-.45.4513-.45 1.1733 0 1.6245l.41.4112c.44.4513 1.17.4513 1.61-.01l1.6425-1.6472-.0028-.0028 14.0028-14.0028 1.6741-1.6673c.4345-.4449.4345-1.2392 0-1.6747l-.3305-.3314c-.444-.43548-1.3436-.3266-1.8436.1734z"/><path d="m25.9507 16.2976c-.2034-.098-.364-.2613-.4603-.4791l-.6853-1.6225c-.1284-.2613-.4925-.2613-.6102 0l-.6853 1.6225c-.0856.2069-.2569.3811-.4603.4791l-.8566.3921c-.2569.1306-.2569.5009 0 .6206l.8566.3921c.2034.098.364.2613.4603.4791l.6853 1.6225c.1284.2613.4925.2613.6102 0l.6853-1.6225c.0856-.2069.2569-.3811.4603-.4791l.8566-.3921c.2569-.1306.2569-.5009 0-.6206z"/><path d="m12 14c.5523 0 1-.4477 1-1s-.4477-1-1-1-1 .4477-1 1 .4477 1 1 1z"/><path d="m30 13c0 .5523-.4477 1-1 1s-1-.4477-1-1 .4477-1 1-1 1 .4477 1 1z"/><path d="m19 4c.5523 0 1-.44771 1-1s-.4477-1-1-1-1 .44771-1 1 .4477 1 1 1z"/><path d="m20 21c0 .5523-.4477 1-1 1s-1-.4477-1-1 .4477-1 1-1 1 .4477 1 1z"/></g></svg>
										</a>
									</div>
								</div>
							</div>
							<p class="ms-2"><?php echo esc_attr__( 'Example: When new order is placed in WooCommerce, add a new row in Google Sheets', 'flowmattic' ); ?></p>
							<p class="ms-2"><?php echo esc_attr__( 'FlowMattic will assist you in creating workflows based on your description. Click the magic button to get started!', 'flowmattic' ); ?></p>
							<p class="ms-2"><?php echo esc_attr__( 'Note: This feature is in beta and may not work as expected in all cases.', 'flowmattic' ); ?></p>
						</div>
					</div>

					<!-- Stats Section -->
					<div class="flowmattic-stats ms-2 mb-5">
						<div class="card-header py-3 bg-white">
							<h5 class="section-title"><?php echo esc_html__( 'Know Your Stats', 'flowmattic' ); ?></h5>
						</div>
						<div class="stats-grid">
							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 512 512" style="transform: rotate(180deg);"><path d="M384 32a64 64 0 00-57.67 91.73l-70.83 80.82-70.19-80.1A64 64 0 10128 160c1.1 0 2.2 0 3.29-.08L224 265.7v94.91a64 64 0 1064 0v-96.05l91.78-104.71c1.39.09 2.8.15 4.22.15a64 64 0 000-128zM96 96a32 32 0 1132 32 32 32 0 01-32-32zm160 352a32 32 0 1132-32 32 32 0 01-32 32zm128-320a32 32 0 1132-32 32 32 0 01-32 32z"/></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $all_workflows_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Workflows', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Core-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="table" fill="currentColor" fill-rule="nonzero"><path d="M0,2.34065934 C0,0.683805091 0.255420331,0 1.91226143,0 L22.1868258,0 C23.8436669,0 24,0.683805091 24,2.34065934 L24,21.7912088 C24,23.448063 23.8436669,24 22.1868258,24 L1.91226143,24 C0.255420331,24 0,23.448063 0,21.7912088 L0,2.34065934 Z M22.5000101,6 L16.5000578,6 L16.5000578,10.5 L22.5000101,10.5 L22.5000101,6 Z M22.5000101,12 L16.5000578,12 L16.5000578,16.5 L22.5000101,16.5 L22.5000101,12 Z M22.5000101,18 L16.5000578,18 L16.5000578,22.5 L21.5274904,22.5164835 C22.3559109,22.5164835 22.5164935,22.2569986 22.5164935,21.4285714 L22.5000101,18 Z M15.0000697,22.5 L15.0000697,18 L9.00011728,18 L9.00011728,22.5 L15.0000697,22.5 Z M7.50012918,22.5 L7.50012918,18 L1.50017679,18 L1.51666018,21.3296703 C1.51666018,22.1580975 1.67724277,22.5164835 2.50566332,22.5164835 L7.50012918,22.5 Z M1.50017679,16.5 L7.50012918,16.5 L7.50012918,12 L1.50017679,12 L1.50017679,16.5 Z M1.50017679,10.5 L7.50012918,10.5 L7.50012918,6 L1.50017679,6 L1.50017679,10.5 Z M9.00011728,6 L9.00011728,10.5 L15.0000697,10.5 L15.0000697,6 L9.00011728,6 Z M15.0000697,12 L9.00011728,12 L9.00011728,16.5 L15.0000697,16.5 L15.0000697,12 Z" id="Shape"></path></g></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $tables_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Tables', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 -960 960 960" fill="currentColor"><path d="M240-400h320v-80H240v80Zm0-120h480v-80H240v80Zm0-120h480v-80H240v80ZM80-80v-720q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H240L80-80Zm126-240h594v-480H160v525l46-45Zm-46 0v-480 480Z"/></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $assistants_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'AI Assistants', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" width="24px" height="24px" viewBox="0 0 24 24" fill="currentColor"><g><path d="M14,12l-2,2l-2-2l2-2L14,12z M12,6l2.12,2.12l2.5-2.5L12,1L7.38,5.62l2.5,2.5L12,6z M6,12l2.12-2.12l-2.5-2.5L1,12 l4.62,4.62l2.5-2.5L6,12z M18,12l-2.12,2.12l2.5,2.5L23,12l-4.62-4.62l-2.5,2.5L18,12z M12,18l-2.12-2.12l-2.5,2.5L12,23l4.62-4.62 l-2.5-2.5L12,18z"></path></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $connects_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Connects', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.9061 21C21.2464 18.2888 22 15.2329 22 12C22 8.76711 21.2464 5.71116 19.9061 3M4.09393 3C2.75363 5.71116 2 8.76711 2 12C2 15.2329 2.75363 18.2888 4.09393 21M16.5486 8.625H16.459C15.8056 8.625 15.1848 8.91202 14.7596 9.41072L9.38471 15.7143C8.95948 16.213 8.33871 16.5 7.6853 16.5H7.59563M8.71483 8.625H10.1089C10.6086 8.625 11.0477 8.95797 11.185 9.44094L12.9594 15.6841C13.0967 16.167 13.5358 16.5 14.0355 16.5H15.4296" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $variables_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Variables', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="currentColor"><g fill-rule="evenodd"><path d="M0 0h24v24H0z" fill="none"/><path d="M3 3v8h8V3H3zm6 6H5V5h4v4zm-6 4v8h8v-8H3zm6 6H5v-4h4v4zm4-16v8h8V3h-8zm6 6h-4V5h4v4zm-6 4v8h8v-8h-8zm6 6h-4v-4h4v4z"/></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( get_option( 'flowmattic_installed_apps' ) ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Integrations', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" width="24px" height="24px" viewBox="0 0 24 24" fill="currentColor"><g><g><path d="M3,11h8V3H3V11z M5,5h4v4H5V5z"/><path d="M13,3v8h8V3H13z M19,9h-4V5h4V9z"/><path d="M3,21h8v-8H3V21z M5,15h4v4H5V15z"/><polygon points="18,13 16,13 16,16 13,16 13,18 16,18 16,21 18,21 18,18 21,18 21,16 18,16"/></g></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $apps_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Custom Apps', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Core-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="email-templates" fill="currentColor" fill-rule="nonzero"><path d="M18.3714118,1.5 L5.62390158,1.5 C5.00058582,1.5 4.49912127,2.0015625 4.49912127,2.625 L4.49912127,10.4296875 L3.6321031,9.7125 C3.43526655,9.553125 3.22437024,9.4171875 2.99941418,9.309375 L2.99941418,2.625 C2.99941418,1.1765625 4.17574692,0 5.62390158,0 L18.3714118,0 C19.8195665,0 20.9958992,1.1765625 20.9958992,2.625 L20.9958992,9.309375 C20.7709432,9.4171875 20.5600469,9.553125 20.3678969,9.7125 L19.4961921,10.4296875 L19.4961921,2.625 C19.4961921,2.0015625 18.9947276,1.5 18.3714118,1.5 Z M1.64030463,12 C1.56531927,12 1.49970709,12.0609375 1.49970709,12.140625 L1.49970709,21 C1.49970709,21.8296875 2.16988869,22.5 2.99941418,22.5 L20.9958992,22.5 C21.8254247,22.5 22.4956063,21.8296875 22.4956063,21 L22.4956063,12.140625 C22.4956063,12.065625 22.4346807,12 22.3550088,12 C22.3222027,12 22.2940832,12.009375 22.2659637,12.0328125 L13.8019918,18.9890625 C13.2958407,19.40625 12.6537786,19.6359375 11.9976567,19.6359375 C11.3415349,19.6359375 10.6994728,19.40625 10.1933216,18.9890625 L1.72466315,12.0328125 C1.70123023,12.0140625 1.66842414,12 1.63561804,12 L1.64030463,12 Z M0,12.140625 C0,11.2359375 0.731107206,10.5 1.64030463,10.5 C2.01991798,10.5 2.38547159,10.63125 2.68072642,10.8703125 L11.1446983,17.8265625 C11.3837141,18.0234375 11.6883421,18.13125 11.9976567,18.13125 C12.3069713,18.13125 12.6115993,18.0234375 12.8506151,17.8265625 L21.3192736,10.8703125 C21.6145284,10.63125 21.980082,10.5 22.3596954,10.5 C23.2642062,10.5 24,11.23125 24,12.140625 L24,21 C24,22.6546875 22.6549502,24 21.0005858,24 L2.99941418,24 C1.34504979,24 0,22.6546875 0,21 L0,12.140625 Z M8.24838899,6 L15.7469244,6 C16.1593439,6 16.496778,6.3375 16.496778,6.75 C16.496778,7.1625 16.1593439,7.5 15.7469244,7.5 L8.24838899,7.5 C7.83596954,7.5 7.49853544,7.1625 7.49853544,6.75 C7.49853544,6.3375 7.83596954,6 8.24838899,6 Z M8.24838899,10.5 L15.7469244,10.5 C16.1593439,10.5 16.496778,10.8375 16.496778,11.25 C16.496778,11.6625 16.1593439,12 15.7469244,12 L8.24838899,12 C7.83596954,12 7.49853544,11.6625 7.49853544,11.25 C7.49853544,10.8375 7.83596954,10.5 8.24838899,10.5 Z" id="Shape"></path></g></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $email_templates_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Email Templates', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 512 512"><path d="M197.9 253.9c12.9 6.4 24.7 14.8 35 25.2s18.8 22.2 25.2 35c105.8-30 156.7-79.5 181.5-126c25-46.8 27.3-97.6 22-137.7c-40.1-5.3-90.9-3-137.7 22c-46.5 24.8-96 75.8-126 181.5zM384 312.1l0 82.2c0 25.4-13.4 49-35.3 61.9l-88.5 52.5c-7.4 4.4-16.6 4.5-24.1 .2s-12.1-12.2-12.1-20.9l0-114.7c0-22.6-9-44.3-25-60.3s-37.7-25-60.3-25L24 288c-8.6 0-16.6-4.6-20.9-12.1s-4.2-16.7 .2-24.1l52.5-88.5c13-21.9 36.5-35.3 61.9-35.3l82.2 0C281.7-3.8 408.8-8.5 483.9 5.3c11.6 2.1 20.7 11.2 22.8 22.8c13.8 75.1 9.1 202.2-122.7 284zM28.3 511.9c-16 .4-28.6-12.2-28.2-28.2C1 446 7.7 379.7 42 345.5c34.4-34.4 90.1-34.4 124.5 0s34.4 90.1 0 124.5C132.3 504.3 66 511 28.3 511.9zm50.2-64.5c12.8-.7 31.2-3.7 41.3-13.7c11.4-11.4 11.4-30 0-41.4s-30-11.4-41.4 0c-10.1 10.1-13 28.5-13.7 41.3c-.5 8 5.9 14.3 13.9 13.9zM328 144a40 40 0 1 1 80 0 40 40 0 1 1 -80 0z"/></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $mcp_tools_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'MCP Tools', 'flowmattic' ); ?></div>
							</div>

							<div class="stat-card">
								<div class="stat-header">
									<div class="stat-icon">
										<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" width="24px" height="24px" viewBox="0 0 24 24" fill="currentColor"><g><path d="M22.69,18.37l1.14-1l-1-1.73l-1.45,0.49c-0.32-0.27-0.68-0.48-1.08-0.63L20,14h-2l-0.3,1.49c-0.4,0.15-0.76,0.36-1.08,0.63 l-1.45-0.49l-1,1.73l1.14,1c-0.08,0.5-0.08,0.76,0,1.26l-1.14,1l1,1.73l1.45-0.49c0.32,0.27,0.68,0.48,1.08,0.63L18,24h2l0.3-1.49 c0.4-0.15,0.76-0.36,1.08-0.63l1.45,0.49l1-1.73l-1.14-1C22.77,19.13,22.77,18.87,22.69,18.37z M19,21c-1.1,0-2-0.9-2-2s0.9-2,2-2 s2,0.9,2,2S20.1,21,19,21z M11,7v5.41l2.36,2.36l1.04-1.79L13,11.59V7H11z M21,12c0-4.97-4.03-9-9-9C9.17,3,6.65,4.32,5,6.36V4H3v6 h6V8H6.26C7.53,6.19,9.63,5,12,5c3.86,0,7,3.14,7,7H21z M10.86,18.91C7.87,18.42,5.51,16.01,5.08,13H3.06c0.5,4.5,4.31,8,8.94,8 c0.02,0,0.05,0,0.07,0L10.86,18.91z"/></g></svg>
									</div>
								</div>
								<div class="stat-value"><?php echo number_format( $task_count ); ?></div>
								<div class="stat-label"><?php echo esc_html__( 'Task Executions', 'flowmattic' ); ?> <span class="stat-sublabel"><?php echo sprintf( esc_attr__( '/ Last %s days', 'flowmattic' ), ( isset( $settings['task_clean_interval'] ) ? $settings['task_clean_interval'] : '90' ) ); ?></span></div>
							</div>
						</div>
					</div>

					<!-- Recent Workflows -->
					<div class="flowmattic-content-sidebar col-12">
						<div class="flowmattic-recent-workflows">
							<div class="recent-section">
								<div class="recent-header bg-light">
									<h4 class="recent-title">Recent Workflows</h4>
									<div class="recent-actions">
										<a href="<?php echo admin_url( '/admin.php?page=flowmattic-workflows' ); ?>" class="recent-button"><?php echo esc_html__( 'View All', 'flowmattic' ); ?></a>
										<a href="<?php echo admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=new&nonce=' . wp_create_nonce( 'flowmattic-workflow-new' ) ); ?>" class="recent-button primary"><?php echo esc_html__( 'Create New', 'flowmattic' ); ?></a>
									</div>
								</div>
								<div class="recent-workflows-content">
									<?php
									if ( ! empty( $all_workflows ) ) {
										$nonce            = wp_create_nonce( 'flowmattic-workflow-edit' );
										$recent_workflows = $all_workflows;

										// Sort workflows.
										arsort( $recent_workflows );

										foreach ( $recent_workflows as $key => $workflow ) {
											$applications      = array();
											$workflow_steps    = json_decode( $workflow->workflow_steps, true );
											$workflow_settings = json_decode( $workflow->workflow_settings );
											$workflow_time     = $workflow_settings->time;

											$builder_version = isset( $workflow_settings->builder_version ) ? $workflow_settings->builder_version : '1.0';

											$steps_count = 0;

											// If builder version is v2, then get the workflow steps from the new builder.
											if ( 'v2' === $builder_version ) {
												$workflow_steps_new = isset( $workflow_steps['steps'] ) ? $workflow_steps['steps'] : array();

												if ( ! empty( $workflow_steps_new ) ) {
													$workflow_steps_cleaned = array();

													// Remove the placeholder type steps.
													foreach ( $workflow_steps_new as $step ) {
														if ( 'placeholder' !== $step['type'] && 'routerSwitch' !== $step['type'] && 'routerRoute' !== $step['type'] ) {
															$workflow_steps_cleaned[] = $step;
														}
													}

													$workflow_steps_v2 = array();
													$steps_count    = count( $workflow_steps_cleaned );

													if ( 1 < $steps_count ) {
														$workflow_steps_v2[] = $workflow_steps_cleaned[0];
														$workflow_steps_v2[] = $workflow_steps_cleaned[ $steps_count - 1 ];
													}

													$workflow_steps = $workflow_steps_v2;
												}
											} else {
												$steps_count = count( $workflow_steps );

												if ( 1 < $steps_count ) {
													$workflow_steps = array( $workflow_steps[0], $workflow_steps[ $steps_count - 1 ] );
												}
											}

											if ( ! empty( $workflow_steps ) ) {
												foreach ( $workflow_steps as $index => $step ) {
													if ( ! isset( $step['application'] ) && ! isset( $step['data']['app']['key'] ) ) {
														continue;
													}

													$step_app = 'v2' === $builder_version ? $step['data']['app']['key'] : $step['application'];

													$application_icon = $all_applications[ $step_app ]['icon'];
													$application_name = $all_applications[ $step_app ]['name'];

													$applications[] = '<div class="app-icon" data-toggle="tooltip" title="' . $application_name . '"><img width="32" height="32" src="' . esc_url( $application_icon ) . '" alt="' . esc_attr( $application_name ) . '"></div>';
													if ( $index < count( $workflow_steps ) - 1 ) {
														$applications[] = '<svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="12" height="12" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg>';
													}
												}

												// If count is greater than 2, show the remaining numbers.
												if ( 2 < $steps_count ) {
													$count_display = '<div class="app-icon d-flex align-items-center justify-content-center" data-toggle="tooltip" title="' . ( $steps_count - 2 ) . ' ' . esc_html__( 'Steps more', 'flowmattic' ) . '">+' . ( $steps_count - 2 ) . '</div>';
													
													// Insert the count_display at the second last position.
													array_splice( $applications, 1, 0, $count_display );
												}
											}

											$workflow_edit_url = admin_url( '/admin.php?page=flowmattic-workflows&flowmattic-action=edit&workflow-id=' . $workflow->workflow_id . '&nonce=' . $nonce );

											if ( 'v2' === $builder_version ) {
												$workflow_edit_url = admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=edit&workflow-id=' . $workflow->workflow_id . '&builder-version=v2&nonce=' . $nonce );
											}
											?>
											<div class="workflow-item">
												<div class="workflow-info">
													<div class="workflow-name"><?php echo rawurldecode( $workflow->workflow_name ); ?></div>
													<div class="workflow-meta"><?php echo sprintf( __( 'Created on %s', 'flowmattic' ), $workflow_time ); ?></div>
												</div>
												<div class="workflow-apps">
													<?php echo implode( '', $applications ); ?>
												</div>
												<div class="workflow-actions">
													<a href="<?php echo $workflow_edit_url; ?>" class="workflow-action"><?php echo esc_html__( 'Edit', 'flowmattic' ); ?></a>
												</div>
											</div>
											<?php
										}
									} else {
										echo '<div class="workflow-item"><div class="workflow-info">' . __( 'No workflows created. Click the Create Workflow button and create a new workflow to show it here.', 'flowmattic' ) . '</div></div>';
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<!-- Workflow Templates Section -->
					<div class="workflow-templates mt-4">
						<div class="workflow-templates-header bg-light d-flex align-items-center justify-content-between p-4">
							<div class="workflow-templates-title">
								<h4 class="recent-title pb-2"><?php echo esc_html__( 'Workflow Templates', 'flowmattic' ); ?></h4>
								<p class="m-0"><?php esc_html_e( 'Get started with workflow templates in just a click.', 'flowmattic' ); ?></p>
							</div>
							<div class="search-bar w-25">
								<input type="text" id="template-search" placeholder="Search templates...">
							</div>
						</div>
						<div class="workflow-template-list">
							<?php
							$flowmattic_apps    = wp_flowmattic()->apps;
							$all_applications   = $flowmattic_apps->get_all_applications();
							$workflow_templates = flowmattic_get_workflow_templates();

							if ( is_array( $workflow_templates ) ) {
								// Loop through all the templates.
								foreach ( $workflow_templates as $template_id => $template ) {
									$template_apps = $template['apps'];
									$applications  = array();
									$app_names     = array();
									$popup_apps    = array();
									$count         = count( $template_apps );
									$i             = ( 3 >= $count ) ? 0 : 1;

									foreach ( $template_apps as $key => $template_app ) {
										++$i;
										$app_name = $template_app['app'];
										$find_app = array_search( $app_name, array_column( $all_applications, 'name' ), true );
										
										if ( $find_app !== false ) {
											$app_data = $all_applications[ array_keys( $all_applications )[ $find_app ] ];
											$app_icon = $template_app['icon'];
										} else {
											$app_icon = $template_app['icon'];
										}

										$app_names[] = $app_name;

										if ( 3 >= $i ) {
											$applications[] = '<div class="workflow-image" data-toggle="tooltip" title="' . esc_attr( $app_name ) . '">';
											if ( !empty( $app_icon ) && filter_var( $app_icon, FILTER_VALIDATE_URL ) ) {
												$applications[] = '<img src="' . esc_url( $app_icon ) . '" alt="' . esc_attr( $app_name ) . '">';
											} else {
												$applications[] = esc_html( substr( $app_name, 0, 2 ) );
											}
											$applications[] = '</div>';
											
											if ( $i < 3 && $i < $count ) {
												$applications[] = '<span class="svg-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="12" height="12" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
											}
										} else {
											$popup_apps[] = '<div class="workflow-image" data-toggle="tooltip" title="' . esc_attr( $app_name ) . '">';
											if ( !empty( $app_icon ) && filter_var( $app_icon, FILTER_VALIDATE_URL ) ) {
												$popup_apps[] = '<img src="' . esc_url( $app_icon ) . '" alt="' . esc_attr( $app_name ) . '">';
											} else {
												$popup_apps[] = esc_html( substr( $app_name, 0, 2 ) );
											}
											$popup_apps[] = '</div>';
											$popup_apps[] = '<span class="svg-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="12" height="12" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
										}
									}

									// If count is greater than 3, show the remaining numbers.
									if ( 3 < $count ) {
										$applications[] = '<span class="svg-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#94a3b8" width="12" height="12" viewBox="0 0 448 512"><path d="M440.6 273.4c4.7-4.5 7.4-10.8 7.4-17.4s-2.7-12.8-7.4-17.4l-176-168c-9.6-9.2-24.8-8.8-33.9 .8s-8.8 24.8 .8 33.9L364.1 232 24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l340.1 0L231.4 406.6c-9.6 9.2-9.9 24.3-.8 33.9s24.3 9.9 33.9 .8l176-168z"/></svg></span>';
										$applications[] = '<div class="workflow-image fm-workflow-popup-trigger">+' . ( $count - 2 ) . '</div>';
									}
									?>
									<div class="flowmattic-recent-workflow-item d-none">
										<div class="card-body">
											<div class="workflow-template-item">
												<div class="workflow-applications">
													<?php echo implode( '', $applications ); ?>
													<?php
													if ( ! empty( $popup_apps ) ) {
														echo '<div class="fm-workflow-apps-popup">' . implode( '', $popup_apps ) . '</div>';
													}
													?>
												</div>
												<h5><?php echo esc_html( $template['title'] ); ?></h5>
												<div class="workflow-template-action">
													<a href="javascript:void(0);" 
													data-template-id="<?php echo esc_attr( $template_id ); ?>" 
													data-applications="<?php echo esc_attr( implode( ' > ', $app_names ) ); ?>" 
													class="btn-use-template">
														<?php echo esc_html__( 'Use Template', 'flowmattic' ); ?>
													</a>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
							} else {
								$workflow_message = $workflow_templates;

								// If license key is not set, show the message to enter the license key.
								if ( '' === $license_key ) {
									$workflow_message = esc_html__( 'Please enter your license key to get access to the workflow templates.', 'flowmattic' );
								}
								?>
								<div class="card-body">
									<div class="alert" role="alert">
										<?php echo esc_html( $workflow_message ); ?>
									</div>
								</div>
								<?php
							}
							?>
							
							<div class="d-flex justify-content-center">
								<button id="fmLoadMore"><?php echo esc_attr__( 'Load More', 'flowmattic' ); ?></button>
							</div>
						</div>
						
						<!-- Import Template Modal -->
						<div class="modal fade" id="fm-template-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-template-modal-label" aria-hidden="true">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<form id="fm-template-import" class="w-100" method="POST" novalidate>
									<input class="hidden" name="template_id" type="hidden" value=""/>
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title" id="fm-template-modal-label"><?php esc_html_e( 'Import Workflow Template', 'flowmattic' ); ?></h5>
											<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
										</div>
										<div class="modal-body">
											<div class="mb-3">
												<label for="template_name" class="form-label"><?php esc_html_e( 'Workflow Name', 'flowmattic' ); ?> <small class="badge text-danger bg-light"><?php esc_html_e( 'Required', 'flowmattic' ); ?></small></label>
												<input type="search" name="template_name" class="form-control fm-textarea" id="template_name" required>
												<div class="form-text"><?php esc_html_e( 'Name the workflow you want to import this template as.', 'flowmattic' ); ?></div>
											</div>
											<div class="alert alert-info" role="alert">
												<?php echo esc_html__( 'NOTE: Make sure you have installed the integrations before importing the workflow template', 'flowmattic' ); ?>
											</div>
											<p>
												<button type="submit" class="btn btn-import-template btn-primary me-2"><?php esc_html_e( 'Get Started', 'flowmattic' ); ?></button>
												<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
											</p>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- AI Workflow Modal -->
<div class="modal fade" id="fm-workflow-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="fm-workflow-modal-label" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="fm-workflow-modal-label"><?php esc_html_e( 'Creating Workflow', 'flowmattic' ); ?></h5>
				<button type="button" class="btn-close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close', 'flowmattic' ); ?>"></button>
			</div>
			<div class="modal-body p-0">
				<div class="card p-0 border-0 mw-100 mt-0 pt-0">
					<div class="card-body bg-light">
						<div class="d-flex justify-content-between align-items-center">
							<div class="workflow-preview-loading d-flex align-items-center flex-column w-100 mb-4">
								<h3 class="text-center w-100 mb-4">
									Sit back! FlowMattic AI is creating a workflow for you!!<br/>
									This may take a few seconds..
								</h3>
								<div id="workflow-generator-lottie" style="width: 500px; height: 360px;"></div>
							</div>
							<div class="workflow-suggesion-preview w-100 d-none"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary btn-import-workflow-suggestion d-none"><?php esc_html_e( 'Use Workflow', 'flowmattic' ); ?></button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'flowmattic' ); ?></button>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	/* Modern FlowMattic Dashboard Styles */
	.flowmattic-wrap {
		--primary: #0d6efd;
		--primary-dark: #0a58ca;
		--primary-light: #BBDEFB;
		--primary-bg: #E3F2FD;
		--surface: #ffffff;
		--background: #f8fafc;
		--text-primary: #1e293b;
		--text-secondary: #64748b;
		--text-muted: #94a3b8;
		--border: #e2e8f0;
		--border-light: #f1f5f9;
		--success: #10b981;
		--radius: 12px;
		--radius-lg: 16px;
		--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
		--shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
		--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
		--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
	}

	/* Reset and base styles */
	.flowmattic-wrap * {
		box-sizing: border-box;
	}

	/* Override WordPress admin styles */
	.flowmattic-wrap .d-flex {
		display: flex !important;
	}

	.flowmattic-wrap .d-grid {
		display: grid !important;
	}

	.flowmattic-wrap .flex-wrap {
		flex-wrap: wrap !important;
	}

	.flowmattic-wrap .align-items-center {
		align-items: center !important;
	}

	.flowmattic-wrap .justify-content-between {
		justify-content: space-between !important;
	}

	.flowmattic-wrap .gap-3 {
		gap: 1rem !important;
	}

	.flowmattic-wrap .mb-2 {
		margin-bottom: 0.5rem !important;
	}

	.flowmattic-wrap .mb-4 {
		margin-bottom: 1.5rem !important;
	}

	.flowmattic-wrap .mb-5 {
		margin-bottom: 3rem !important;
	}

	.flowmattic-wrap .ms-2 {
		margin-left: 0.5rem !important;
	}

	.flowmattic-wrap .py-3 {
		padding-top: 1rem !important;
		padding-bottom: 1rem !important;
	}

	.flowmattic-wrap .px-0 {
		padding-left: 0 !important;
		padding-right: 0 !important;
	}

	.flowmattic-wrap .bg-transparent {
		background-color: transparent !important;
	}

	.flowmattic-wrap .bg-white {
		background-color: #ffffff !important;
	}

	.flowmattic-wrap .border-0 {
		border: 0 !important;
	}

	.flowmattic-wrap .pb-0 {
		padding-bottom: 0 !important;
	}

	.flowmattic-wrap .card-header {
		background-color: transparent !important;
		border-bottom: none !important;
		padding: 1rem 0 !important;
	}

	.flowmattic-wrap .card-body {
		padding: 0 !important;
	}

	/* Hero Section */
	.flowmattic-wrap .hero-section {
		background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
		border: 2px solid #f1f5f9 !important;
		border-radius: 16px !important;
		padding: 32px !important;
		margin-bottom: 32px !important;
		color: #1e293b !important;
		position: relative;
		overflow: hidden;
	}

	.flowmattic-wrap .hero-section::before {
		content: '';
		position: absolute;
		top: 0;
		right: 0;
		width: 300px;
		height: 300px;
		background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(33,150,243,0.1)"/><circle cx="80" cy="20" r="1.5" fill="rgba(33,150,243,0.15)"/><circle cx="50" cy="50" r="3" fill="rgba(33,150,243,0.08)"/><circle cx="20" cy="80" r="1" fill="rgba(33,150,243,0.2)"/><circle cx="80" cy="80" r="2.5" fill="rgba(33,150,243,0.1)"/></svg>') repeat;
		opacity: 0.6;
	}

	.flowmattic-wrap .hero-content {
		position: relative;
		z-index: 1;
	}

	.flowmattic-wrap .hero-title {
		font-size: 32px !important;
		font-weight: 700 !important;
		margin-bottom: 12px !important;
		line-height: 1.2 !important;
		color: var(--primary) !important;
	}

	.flowmattic-wrap .hero-subtitle {
		font-size: 16px !important;
		opacity: 0.8;
		max-width: 600px;
		line-height: 1.5 !important;
		color: #64748b !important;
	}

	/* Section Titles */
	.flowmattic-wrap .section-title {
		font-size: 20px !important;
		font-weight: 600 !important;
		margin-bottom: 20px !important;
		color: #1e293b !important;
	}

	/* Action Grid */
	.flowmattic-wrap .action-grid {
		display: grid !important;
		grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important;
		gap: 20px !important;
		margin-bottom: 32px !important;
	}

	.flowmattic-wrap .action-card {
		background: #ffffff !important;
		border-radius: 12px !important;
		padding: 24px !important;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important;
		transition: all 0.2s ease !important;
		border: 1px solid #f1f5f9 !important;
		position: relative;
		overflow: hidden;
	}

	.flowmattic-wrap .action-card::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: var(--primary);
		transform: scaleX(0);
		transition: transform 0.2s ease;
	}

	.flowmattic-wrap .action-card:hover {
		transform: translateY(-2px) !important;
		box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1) !important;
	}

	.flowmattic-wrap .action-card:hover::before {
		transform: scaleX(1);
	}

	.flowmattic-wrap .action-card-header {
		display: flex !important;
		align-items: center !important;
		margin-bottom: 12px !important;
	}

	.flowmattic-wrap .action-icon {
		width: 40px !important;
		height: 40px !important;
		background: var(--primary) !important;
		border-radius: 10px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: white !important;
		font-size: 18px !important;
		margin-right: 12px !important;
		flex-shrink: 0 !important;
	}

	.flowmattic-wrap .action-title {
		font-size: 16px !important;
		font-weight: 600 !important;
		color: #1e293b !important;
		line-height: 1.3 !important;
		margin: 0 !important;
	}

	.flowmattic-wrap .action-description {
		color: #64748b !important;
		margin-bottom: 16px !important;
		font-size: 13px !important;
		line-height: 1.4 !important;
	}

	.flowmattic-wrap .action-button {
		background: var(--primary) !important;
		color: white !important;
		border: none !important;
		padding: 10px 16px !important;
		border-radius: 8px !important;
		font-weight: 500 !important;
		cursor: pointer !important;
		transition: all 0.2s ease !important;
		text-decoration: none !important;
		display: inline-block !important;
		font-size: 13px !important;
	}

	.flowmattic-wrap .action-button:hover {
		background: #1976D2 !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3) !important;
		color: white !important;
		text-decoration: none !important;
	}

	/* Stats Grid */
	.flowmattic-wrap .stats-grid {
		display: grid !important;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
		gap: 16px !important;
		margin-bottom: 32px !important;
	}

	.flowmattic-wrap .stat-card {
		background: #ffffff !important;
		border-radius: 12px !important;
		padding: 20px !important;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important;
		transition: all 0.2s ease !important;
		position: relative;
		overflow: hidden;
		min-height: 120px;
	}

	.flowmattic-wrap .stat-card:hover {
		transform: translateY(-1px) !important;
		box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
	}

	.flowmattic-wrap .stat-card::before {
		content: '';
		position: absolute;
		top: 0;
		right: 0;
		width: 80px;
		height: 80px;
		background: linear-gradient(135deg, rgba(33, 150, 243, 0.08), rgba(25, 118, 210, 0.05));
		border-radius: 50%;
		transform: translate(25px, -25px);
	}

	.flowmattic-wrap .stat-header {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		margin-bottom: 12px !important;
		position: relative;
		z-index: 1;
	}

	.flowmattic-wrap .stat-icon {
		width: 32px !important;
		height: 32px !important;
		/* background: var(--primary) !important; */
		border-radius: 8px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: var(--primary) !important;
		font-size: 14px !important;
		position: absolute;
		right: -12px;
		top: -12px;
	}

	.flowmattic-wrap .stat-value {
		font-size: 28px !important;
		font-weight: 700 !important;
		color: #1e293b !important;
		margin-bottom: 4px !important;
		position: relative;
		z-index: 1;
		line-height: 1.1 !important;
	}

	.flowmattic-wrap .stat-label {
		font-size: 12px !important;
		color: #64748b !important;
		text-transform: uppercase !important;
		letter-spacing: 0.025em !important;
		font-weight: 500 !important;
		position: relative;
		z-index: 1;
	}

	.flowmattic-wrap .stat-sublabel {
		font-size: 10px !important;
		color: #94a3b8 !important;
		font-weight: 400 !important;
		text-transform: none !important;
		letter-spacing: 0 !important;
	}

	/* Recent Workflows */
	.flowmattic-wrap .recent-section {
		background: #ffffff !important;
		border-radius: 12px !important;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important;
		overflow: hidden;
		margin-bottom: 32px !important;
	}

	.flowmattic-wrap .recent-header {
		padding: 24px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
	}

	.flowmattic-wrap .recent-title {
		font-size: 18px !important;
		font-weight: 600 !important;
		color: #1e293b !important;
		margin: 0 !important;
	}

	.flowmattic-wrap .recent-actions {
		display: flex !important;
		gap: 12px !important;
	}

	.flowmattic-wrap .recent-button {
		padding: 8px 16px !important;
		border: 1px solid #e2e8f0 !important;
		background: #ffffff !important;
		color: #64748b !important;
		border-radius: 6px !important;
		text-decoration: none !important;
		font-size: 13px !important;
		font-weight: 500 !important;
		transition: all 0.2s ease !important;
	}

	.flowmattic-wrap .recent-button:hover {
		border-color: var(--primary) !important;
		color: var(--primary) !important;
		text-decoration: none !important;
	}

	.flowmattic-wrap .recent-button.primary {
		background: var(--primary) !important;
		color: white !important;
		border-color: var(--primary) !important;
	}

	.flowmattic-wrap .recent-button.primary:hover {
		background: #1976D2 !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3) !important;
		color: white !important;
	}

	.flowmattic-wrap .workflow-item {
		padding: 20px 24px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		transition: all 0.2s ease !important;
	}

	.flowmattic-wrap .workflow-item:hover {
		background: #f8fafc !important;
	}

	.flowmattic-wrap .workflow-item:last-child {
		border-bottom: none !important;
	}

	.flowmattic-wrap .workflow-info {
		flex: 1 !important;
		min-width: 0 !important;
	}

	.flowmattic-wrap .workflow-name {
		font-size: 14px !important;
		font-weight: 600 !important;
		color: #1e293b !important;
		margin-bottom: 4px !important;
		white-space: nowrap !important;
		overflow: hidden !important;
		text-overflow: ellipsis !important;
	}

	.flowmattic-wrap .workflow-meta {
		font-size: 12px !important;
		color: #64748b !important;
	}

	.flowmattic-wrap .workflow-apps {
		display: flex !important;
		align-items: center !important;
		gap: 8px !important;
		margin: 0 24px !important;
		flex-shrink: 0 !important;
	}

	.flowmattic-wrap .app-icon {
		width: 28px !important;
		height: 28px !important;
		border: 1px solid var(--primary) !important;
		border-radius: 6px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: var(--primary) !important;
		font-size: 12px !important;
	}

	.flowmattic-wrap .workflow-arrow {
		color: #94a3b8 !important;
		font-size: 12px !important;
	}

	.flowmattic-wrap .workflow-actions {
		display: flex !important;
		gap: 8px !important;
		flex-shrink: 0 !important;
	}

	.flowmattic-wrap .workflow-action {
		padding: 6px 12px !important;
		background: #f8fafc !important;
		color: #64748b !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		text-decoration: none !important;
		font-size: 12px !important;
		font-weight: 500 !important;
		transition: all 0.2s ease !important;
	}

	.flowmattic-wrap .workflow-action:hover {
		background: var(--primary) !important;
		color: white !important;
		border-color: var(--primary) !important;
		text-decoration: none !important;
	}

	/* Workflow Templates Section */
	.flowmattic-wrap .workflow-templates {
		background: #ffffff !important;
		border-radius: 12px !important;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important;
		overflow: hidden;
		margin-bottom: 32px !important;
		margin-top: 32px !important;
	}

	.flowmattic-wrap .workflow-templates .card-header {
		padding: 24px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		background: #ffffff !important;
		margin: 0 !important;
	}

	.flowmattic-wrap .workflow-templates .card-header h5 {
		font-size: 18px !important;
		font-weight: 600 !important;
		color: #1e293b !important;
		margin: 0 !important;
	}

	.flowmattic-wrap .workflow-templates .navbar-text {
		padding: 20px 24px !important;
		background: #f8fafc !important;
		border-bottom: 1px solid #f1f5f9 !important;
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		margin: 0 !important;
		font-size: 14px !important;
		color: #64748b !important;
	}

	.flowmattic-wrap .workflow-templates #template-search {
		padding: 8px 12px !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		background: #ffffff !important;
		color: #1e293b !important;
		font-size: 13px !important;
		min-width: 200px !important;
		width: 100%;
	}

	.flowmattic-wrap .workflow-templates #template-search:focus {
		outline: none !important;
		border-color: var(--primary) !important;
		box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1) !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-template-list {
		padding: 0 !important;
		background: #ffffff !important;
	}

	.flowmattic-wrap .workflow-templates .flowmattic-recent-workflow-item {
		border-bottom: 1px solid #f1f5f9 !important;
		transition: all 0.2s ease !important;
	}

	.flowmattic-wrap .workflow-templates .flowmattic-recent-workflow-item:last-child {
		border-bottom: none !important;
	}

	.flowmattic-wrap .workflow-templates .flowmattic-recent-workflow-item:hover {
		background: #f8fafc !important;
	}

	.flowmattic-wrap .workflow-templates .flowmattic-recent-workflow-item .card-body {
		padding: 20px 24px !important;
		background: transparent !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-template-item {
		display: flex !important;
		align-items: center !important;
		width: 100% !important;
		gap: 16px !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-applications {
		display: flex !important;
		align-items: center !important;
		gap: 8px !important;
		min-width: 200px !important;
		flex-shrink: 0 !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-image {
		width: 32px !important;
		height: 32px !important;
		border-radius: 6px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: var(--primary) !important;
		font-size: 12px !important;
		font-weight: 600 !important;
		overflow: hidden !important;
		padding: 2px !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-image img {
		width: 32px !important;
		height: 32px !important;
		object-fit: contain !important;
		border-radius: 2px !important;
	}

	.flowmattic-wrap .workflow-templates .svg-icon {
		width: 16px !important;
		height: 16px !important;
		fill: #94a3b8 !important;
		flex-shrink: 0 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
	}

	.flowmattic-wrap .workflow-templates .svg-icon svg {
		width: 12px !important;
		height: 12px !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-template-item h5 {
		font-size: 14px !important;
		font-weight: 600 !important;
		color: #1e293b !important;
		margin: 0 !important;
		flex: 1 !important;
		line-height: 1.4 !important;
	}

	.flowmattic-wrap .workflow-templates .workflow-template-action {
		flex-shrink: 0 !important;
	}

	.flowmattic-wrap .workflow-templates .btn-use-template {
		padding: 8px 16px !important;
		background: #ffffff !important;
		color: var(--primary) !important;
		border: 1px solid var(--primary) !important;
		border-radius: 6px !important;
		font-size: 13px !important;
		font-weight: 500 !important;
		text-decoration: none !important;
		transition: all 0.2s ease !important;
		cursor: pointer !important;
	}

	.flowmattic-wrap .workflow-templates .btn-use-template:hover {
		background: var(--primary) !important;
		color: white !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3) !important;
		text-decoration: none !important;
	}

	.flowmattic-wrap .workflow-templates #fmLoadMore {
		background: #ffffff !important;
		color: #64748b !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		padding: 10px 20px !important;
		font-size: 13px !important;
		font-weight: 500 !important;
		cursor: pointer !important;
		transition: all 0.2s ease !important;
		margin: 24px !important;
	}

	.flowmattic-wrap .workflow-templates #fmLoadMore:hover {
		background: #f8fafc !important;
		border-color: var(--primary) !important;
		color: var(--primary) !important;
	}

	/* Load More Button Container */
	.flowmattic-wrap .workflow-templates .d-flex.justify-content-center {
		display: flex !important;
		justify-content: center !important;
		padding: 24px !important;
		background: #ffffff !important;
		border-top: 1px solid #f1f5f9 !important;
	}

	/* Template popup styles */
	.flowmattic-wrap .fm-workflow-apps-popup {
		position: absolute !important;
		top: 100% !important;
		left: 0 !important;
		background: #ffffff !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 8px !important;
		padding: 12px !important;
		box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
		z-index: 10 !important;
		display: none !important;
		min-width: 200px !important;
	}

	.flowmattic-wrap .fm-workflow-popup-trigger:hover + .fm-workflow-apps-popup,
	.flowmattic-wrap .fm-workflow-apps-popup:hover {
		display: block !important;
	}

	/* Hide elements properly */
	.flowmattic-wrap .d-none {
		display: none !important;
	}

	/* Alert styles for error states */
	.flowmattic-wrap .workflow-templates .alert {
		margin: 24px !important;
		padding: 16px !important;
		border-radius: 8px !important;
		border: 1px solid #fecaca !important;
		background: #fef2f2 !important;
		color: #dc2626 !important;
		font-size: 14px !important;
	}

	/* Template popup styles */
	.flowmattic-wrap .fm-workflow-apps-popup {
		position: absolute !important;
		top: 100% !important;
		left: 0 !important;
		background: #ffffff !important;
		border: 1px solid #e2e8f0 !important;
		border-radius: 8px !important;
		padding: 12px !important;
		box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
		z-index: 10 !important;
		display: none !important;
		min-width: 200px !important;
	}

	.flowmattic-wrap .fm-workflow-popup-trigger:hover + .fm-workflow-apps-popup,
	.flowmattic-wrap .fm-workflow-apps-popup:hover {
		display: block !important;
	}

	/* Hide elements properly */
	.flowmattic-wrap .d-none {
		display: none !important;
	}

	/* Alert styles for error states */
	.flowmattic-wrap .workflow-templates .alert {
		margin: 24px !important;
		padding: 16px !important;
		border-radius: 8px !important;
		border: 1px solid #fecaca !important;
		background: #fef2f2 !important;
		color: #dc2626 !important;
		font-size: 14px !important;
	}

	/* Remove old styles conflicts */
	.flowmattic-wrap .fm-workflow-steps .fm-workflow-trigger:after,
	.flowmattic-wrap .fm-workflow-steps .fm-workflow-action:after {
		display: none !important;
	}

	/* Responsive Design */
	@media (max-width: 1024px) {
		.flowmattic-wrap .stats-grid {
			grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
		}
	}

	@media (max-width: 768px) {
		.flowmattic-wrap .action-grid {
			grid-template-columns: 1fr !important;
		}

		.flowmattic-wrap .stats-grid {
			grid-template-columns: repeat(2, 1fr) !important;
		}

		.flowmattic-wrap .workflow-item {
			flex-direction: column !important;
			align-items: flex-start !important;
			gap: 12px !important;
		}

		.flowmattic-wrap .workflow-apps,
		.flowmattic-wrap .workflow-actions {
			margin: 0 !important;
			align-self: stretch !important;
			justify-content: flex-start !important;
		}

		.flowmattic-wrap .recent-header {
			flex-direction: column !important;
			gap: 12px !important;
			align-items: flex-start !important;
		}
	}

	@media (max-width: 480px) {
		.flowmattic-wrap .stats-grid {
			grid-template-columns: 1fr !important;
		}
	}
</style>

<script type="text/javascript">
	jQuery( document ).ready( function ($) {
		var listItems = <?php echo wp_json_encode( $workflow_templates ); ?>;
		var currentPage = 1;
		var timer;  // For search debounce.
		const swalPopup = window.Swal.mixin({
			customClass: {
				confirmButton: 'btn btn-primary shadow-none me-xxl-3',
				cancelButton: 'btn btn-danger shadow-none'
			},
			buttonsStyling: false
		} );

		function showPage( page, items ) {
			var itemsPerPage = ( 'undefined' !== typeof items ) ? items : 10;
			var listItems = jQuery( '.workflow-template-list' ).find( '.flowmattic-recent-workflow-item' );
			var start = (page - 1) * itemsPerPage;
			var end = start + itemsPerPage;

			// Then, using slice function, select items from start to end and show them.
			listItems.slice( 0, end ).removeClass( 'd-none' );

			// If all items are visible, hide the Load More button.
			if ( end >= listItems.length ) {
				jQuery( '#fmLoadMore' ).hide();
			} else {
				jQuery('#fmLoadMore').show();
			}
		}

		jQuery( '#fmLoadMore' ).on( 'click', function() {
			currentPage++;
			showPage( currentPage );
		} );

		jQuery('#template-search').on('input', function() {
			clearTimeout(timer);  // Clear previous timer if it exists.
			timer = setTimeout(function() {
				var searchTerm = jQuery('#template-search').val().toLowerCase();
				if (searchTerm) {
					jQuery( '.workflow-template-list' ).find( '.flowmattic-recent-workflow-item' ).addClass( 'd-none' );
					jQuery( '.workflow-template-list' ).find( '.flowmattic-recent-workflow-item' ).each(function() {
						if (jQuery(this).text().toLowerCase().includes(searchTerm)) {
							jQuery(this).removeClass( 'd-none' );
						}
					});
					jQuery('#fmLoadMore').hide();
				} else {
					currentPage = 1;
					showPage(currentPage);
				}
			}, 300);  // Delay of 300ms.
		} );
		jQuery( document ).ready( function() {
			showPage( currentPage, 5 );
		} );

		jQuery( '.btn-use-template' ).on( 'click', function() {
			var templateID   = jQuery( this ).attr( 'data-template-id' ),
				templateName = jQuery( this ).attr( 'data-applications' );

			// Set the template name.
			jQuery( '#fm-template-modal' ).find( '[name="template_name"]' ).val( templateName );

			// Set the template ID.
			jQuery( '#fm-template-modal' ).find( '[name="template_id"]' ).val( templateID );

			// Show the modal.
			jQuery( '#fm-template-modal' ).modal( 'show' );
		} );

		jQuery( '#fm-template-import' ).on( 'submit', function( e ) {
			e.preventDefault();

			const form             = jQuery( this );
			const thisButton       = form.find( '.btn-import-template' );
			const templateID       = form.find( '[name="template_id"]' ).val();
			const templateName     = form.find( '[name="template_name"]' ).val();
			const templateFormData = new FormData( form[0] );

			// Show saving popup.
			swalPopup.fire(
				{
					title: 'Importing Template',
					showConfirmButton: false,
					didOpen: function() {
						swalPopup.showLoading();
					}
				}
			);

			// Add action name and nonce to the form data.
			templateFormData.append( 'action', 'flowmattic_import_workflow_template' );
			templateFormData.append( 'workflow_nonce', FMConfig.workflow_nonce );
			templateFormData.append( 'template_id', templateID );
			templateFormData.append( 'template_name', templateName );

			// Add saving animation for button.
			thisButton.addClass( 'disabled' );
			thisButton.html( '<span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>Importing...' );

			jQuery.ajax( {
				url: ajaxurl,
				data: templateFormData,
				processData: false,
				contentType: false,
				type: 'POST',
				success: function( response ) {
					response = JSON.parse( response );
					swalPopup.fire(
						{
							title: 'Workflow Template Imported!',
							html: 'Redirecting to the workflow editor...',
							icon: 'success',
							showConfirmButton: false,
							timer: 5000,
							timerProgressBar: true
						}
					);

					// Hide the modal.
					jQuery( '#fm-template-modal' ).modal( 'hide' );

					// Remove animation for button and enable it.
					thisButton.html( 'Get Started' );
					thisButton.removeClass( 'disabled' );

					window.location = "<?php echo admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=edit&workflow-id=' ); ?>" + response.workflow_id;
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					swalPopup.fire(
						{
							title: 'Workflow Template Import Failed!',
							text: 'Something went wrong, and the workflow template import was not succesful. Please try again.',
							icon: 'error',
							showConfirmButton: true,
							timer: 3000
						}
					);

					// Hide the modal.
					jQuery( '#fm-template-modal' ).modal( 'hide' );

					// Remove animation for button and enable it.
					thisButton.html( 'Get Started' );
					thisButton.removeClass( 'disabled' );
				}
			} );
		} );
	} );
</script>

<script type="text/javascript">
	jQuery( document ).ready( function() {
		const swalPopup = window.Swal.mixin({
			customClass: {
				confirmButton: 'btn btn-primary shadow-none me-xxl-3',
				cancelButton: 'btn btn-danger shadow-none'
			},
			buttonsStyling: false
		} );

		var animation,
			promptTextarea = jQuery( '.ai-workflow-assistance .workflow-prompt' );

		jQuery( '.generate-workflow-btn' ).on( 'click', function() {
			var promptText = promptTextarea.val();
				
			window.workflowData = {};

			if ( 'undefined' !== typeof promptText && '' === promptText ) {
				alert( 'Please enter a prompt to generate the workflow.' );
				return;
			}

			if ( ! animation ) {
				animation = window.lottie.loadAnimation({
					container: document.getElementById('workflow-generator-lottie'),
					renderer: 'svg',
					loop: true,
					autoplay: true,
					path: '<?php echo esc_url( FLOWMATTIC_PLUGIN_URL . 'assets/admin/img/ai-workflow-assistance.json' ); ?>'
				});
			} else {
				animation.play();
			}

			jQuery( '.workflow-preview-loading' ).removeClass( 'd-none' ).addClass( 'd-flex' );
			jQuery( '.workflow-suggesion-preview' ).addClass( 'd-none' );
			jQuery( '#fm-workflow-modal-label' ).html( 'Creating Workflow...' );

			jQuery( '#fm-workflow-modal' ).modal( 'show' );

			// If modal closed, stop the animation.
			jQuery( '#fm-workflow-modal' ).on( 'hidden.bs.modal', function() {
				animation.stop();
			} );

			jQuery.ajax( {
				url: '<?php echo rest_url( 'flowmattic/v1/workflow-assistance' ); ?>',
				data: {
					action: 'flowmattic_generate_workflow_assistance',
					prompt: promptText,
					workflow_nonce: FMConfig.workflow_nonce
				},
				type: 'POST',
				success: function( response ) {
					jQuery( '.workflow-suggesion-preview' ).removeClass( 'd-none' );
					jQuery( '.workflow-preview-loading' ).removeClass( 'd-flex' ).addClass( 'd-none' );
					if ( response.workflow_preview && '' !== response.workflow_preview ) {
						jQuery( '#fm-workflow-modal-label' ).html( 'Here\'s your workflow!' );
						jQuery( '.workflow-suggesion-preview' ).html( response.workflow_preview );
						jQuery( '.btn-import-workflow-suggestion' ).removeClass( 'd-none' );

						// Set the workflow data.
						window.workflowData = response.workflow_suggestion;
					} else {
						jQuery( '.workflow-suggesion-preview' ).html( '<h3 class="text-center w-100 mb-4">We couldn\'t generate a workflow for the prompt you entered. Please try again.</h3>' );
						jQuery( '.btn-import-workflow-suggestion' ).addClass( 'd-none' );
					}
				}
			} );
		} );

		// Import the workflow, and redirect the user to the workflow editor.
		jQuery( '.btn-import-workflow-suggestion' ).on( 'click', function() {
			var attributes = {
					action: 'flowmattic_import_workflow',
					workflowData: window.workflowData,
					workflow_nonce: FMConfig.workflow_nonce,
					ai_workflow: true
				};

			// Show loading popup.
			swalPopup.fire(
				{
					title: 'Creating workflow...',
					showConfirmButton: false,
					didOpen: function() {
						swalPopup.showLoading();
					}
				}
			);

			if ( 'undefined' !== typeof window.workflowData.workflow_steps && '' !== window.workflowData.workflow_steps ) {
				jQuery.ajax( {
					url: ajaxurl,
					data: attributes,
					type: 'POST',
					success: function( response ) {
						var data = JSON.parse( response );
						// Hide the import modal.
						jQuery( '#workflow-import-modal' ).modal( 'hide' );

						// Reload the page.
						setTimeout( function() {
							window.location = "<?php echo admin_url( '/admin.php?page=flowmattic-workflow-builder&flowmattic-action=edit&workflow-id=' ); ?>" + data.workflow_id;
						}, 200 );
					}
				} );
			}
		} );

		// Auto resize the textarea.
		promptTextarea.on( 'input', function() {
			this.style.height = 'auto';
			this.style.height = ( this.scrollHeight ) + 'px';
		} );
	} );
</script>
<?php 
wp_enqueue_script( 'flowmattic-lottie', 'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.4/lottie.min.js', array(), '5.7.4', true );
FlowMattic_Admin::footer(); ?>