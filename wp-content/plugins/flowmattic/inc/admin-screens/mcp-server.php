<?php
/**
 * Admin page template for MCP server with database integration.
 *
 * @package FlowMattic
 * @since 5.2.0
 */

FlowMattic_Admin::loader();

$license_key = get_option( 'flowmattic_license_key', '' );

if ( '' === $license_key ) {
	?>
	<div class="mw-100 card border-light mw-100">
		<div class="card-body text-center">
			<div class="alert alert-primary" role="alert">
				<?php echo esc_html__( 'License key not registered. Please register your license first to use templates.', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}

$license = wp_flowmattic()->check_license();

// Get current server ID from URL parameter (for when we will have multiple servers)
$server_id = get_option( 'flowmattic_mcp_server_id', 0 );

// If server ID is 0, generate a new one and save it.
if ( 0 === $server_id ) {
	$server_id = wp_generate_uuid4();
	update_option( 'flowmattic_mcp_server_id', $server_id );
}

// Get server data and tools from database
$server_data = flowmattic_get_mcp_server_data( $server_id );

// Get tools from database first, fallback to dummy tools
$database_tools = array();
if ( class_exists( 'FlowMattic_Database_MCP_Server' ) && wp_flowmattic() && wp_flowmattic()->mcp_server_db ) {
	$db_tools = wp_flowmattic()->mcp_server_db->get_tools_by_server( $server_id );
	foreach ( $db_tools as $tool ) {
		$database_tools[] = array(
			'id'                => $tool->id ?? 0,
			'mcp_tool_id'       => $tool->mcp_tool_id ?? '',
			'name'              => $tool->mcp_tool_name,
			'description'       => $tool->mcp_tool_description,
			'execution_method'  => $tool->mcp_tool_execution_method ?? 'api_endpoint',
			'function_name'     => $tool->mcp_tool_function_name ?? '',
			'mcp_tool_db_query' => $tool->mcp_tool_db_query ?? '',
			'workflow_id'       => $tool->mcp_tool_workflow_id ?? '',
			'endpoint'          => $tool->mcp_tool_api_endpoint ?? '',
			'method'            => $tool->mcp_tool_http_method ?? '',
			'active'            => true, // You can add an active field to your database
			'source'            => 'database',
		);
	}
}

// Add filter to allow other plugins to add tools
$mcp_server = FlowMattic_MCP_Server::get_instance();
$plugin_tools = $mcp_server->get_all_plugin_tools( $server_id );

if ( ! empty( $plugin_tools ) ) {
	foreach ( $plugin_tools as $tool_slug => $tool ) {
		$database_tools[] = array(
			'mcp_tool_id'       => str_replace( array( ' ', '-' ), '_', strtolower( $tool['tool']['name'] ) ),
			'name'              => $tool['tool']['name'],
			'description'       => $tool['tool']['description'],
			'execution_method'  => 'php_function',
			'function_name'     => $tool['tool']['function_name'],
			'mcp_tool_db_query' => '',
			'workflow_id'       => '',
			'endpoint'          => '',
			'method'            => '',
			'active'            => true,
			'source'            => 'plugin',
			'source_name'       => $tool['plugin'],
		);
	}
}

$tools_data = $database_tools;

$wp_current_user = wp_get_current_user();
$wp_user_email   = $wp_current_user->user_email;
$license         = wp_flowmattic()->check_license();

// If the user is not an admin, get workflows by user email/
if ( current_user_can( 'manage_options' ) ) {
	$all_workflows = wp_flowmattic()->workflows_db->get_all();
} else {
	$all_workflows = wp_flowmattic()->workflows_db->get_user_workflows( $wp_user_email );
}

// Get all connects from the database.
$all_connects = wp_flowmattic()->connects_db->get_all();

// Get execution counts for all tools
$tool_ids = array();
foreach ( $tools_data as $tool ) {
	if ( is_object( $tool ) ) {
		$tool_ids[] = $tool->mcp_tool_id;
	} elseif ( is_array( $tool ) ) {
		$tool_ids[] = $tool['mcp_tool_id'];
	}
}

$execution_counts = get_tool_execution_counts( $server_id, $tool_ids );
$total_executions = array_sum( array_column( $execution_counts, 'total_executions' ) );

$tools_with_executions = count( array_filter( $execution_counts, function( $count ) {
	return $count['total_executions'] > 0;
} ) );
?>
<script type="text/javascript">
	var mcpServerId = '<?php echo esc_js( $server_id ); ?>';
</script>
<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-container flowmattic-mcp-list-container w-100">
			<div class="flowmattic-dashboard-content container m-0 ps-3">
				<div class="row">
					<div id="flowmattic-mcp">
						<div class="wrap mcp-admin-wrap">
							<!-- Modern Header Section -->
							<div class="mcp-header-modern">
								<div class="mcp-header-content">
									<div class="mcp-header-left">
										<div class="mcp-header-icon">
											<div class="mcp-icon-container">
												<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="52px" height="52px" viewBox="0 0 52 52" version="1.1"><g id="MCP" stroke="none" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-width="1"><g id="Group" transform="translate(5, 2)" stroke="#ffffff"><path d="M0,22.0224371 L20.5395177,2.06650928 C23.3754667,-0.688836428 27.9734033,-0.688836428 30.8091404,2.06650928 L30.8091404,2.06650928 C33.6451801,4.8218256 33.6451801,9.28912746 30.8091404,12.0444732 L15.29764,27.1153846" id="Path" stroke-width="4"/><path d="M15.5531915,26.7940689 L30.6556132,11.9993183 C33.4554835,9.25663749 37.9947802,9.25663749 40.7946504,11.9993183 L40.9000973,12.1027621 C43.6999676,14.8454429 43.6999676,19.2922109 40.9000973,22.0348625 L22.5609324,40.0003494 C21.6276822,40.9145178 21.6276822,42.3966763 22.5609324,43.3108447 L26.326613,47" id="Path" stroke-width="4"/><path d="M25.4634632,7.23076923 L10.350122,22.1616004 C7.52868275,24.9489193 7.52868275,29.4682046 10.350122,32.2557316 L10.350122,32.2557316 C13.1715913,35.0429613 17.7461007,35.0429613 20.56757,32.2557316 L35.6808511,17.324841" id="Path" stroke-width="4"/></g></g></svg>
											</div>
										</div>
										<div class="mcp-header-text">
											<h1 class="mcp-title"><?php echo esc_html__( 'MCP Server Management', 'flowmattic' ); ?></h1>
											<p class="mcp-subtitle"><?php echo esc_html__( 'Manage your Model Context Protocol server and tools', 'flowmattic' ); ?></p>
										</div>
									</div>
									<div class="mcp-header-right">
										<button type="button" class="btn btn-primary mcp-btn-primary <?php echo ! $server_data['active'] ? 'disabled' : ''; ?>" data-toggle="modal" data-target="#addToolModal">
											<span class="btn-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
													<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
												</svg>
											</span>
											<?php echo esc_html__( 'Add New Tool', 'flowmattic' ); ?>
										</button>
									</div>
								</div>
							</div>

							<!-- Modern Server Info Card -->
							<div class="mcp-server-card-modern">
								<div class="server-card-header">
									<div class="server-header-content">
										<div class="server-info d-flex align-items-center justify-content-between gap-3">
											<h2 class="server-name"><?php echo esc_html( $server_data['name'] ); ?></h2>
											<div class="server-status">
												<?php if ( ! $server_data['active'] ) : ?>
													<span class="status-badge status-inactive">
														<span class="status-dot"></span>
														<?php echo esc_html__( 'Inactive', 'flowmattic' ); ?>
													</span>
												<?php else : ?>
													<span class="status-badge status-active">
														<span class="status-dot"></span>
														<?php echo esc_html__( 'Active', 'flowmattic' ); ?>
													</span>
												<?php endif; ?>
											</div>
										</div>
										<div class="server-actions d-flex align-items-center gap-2">
											<button type="button" class="btn btn-secondary-light btn-outline-primary btn-sm d-flex gap-1" data-toggle="modal" data-target="#serverSettingsModal">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
													<path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/>
												</svg>
												<span class="ps-1"><?php echo esc_html__( 'Settings', 'flowmattic' ); ?></span>
											</button>
											<button type="button" class="btn btn-secondary-light btn-outline-primary btn-sm d-flex gap-1 me-2" onclick="window.location.href='<?php echo admin_url( 'admin.php?page=flowmattic-mcp-history&server_id=' . urlencode( $server_id ) ); ?>'">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
													<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
												</svg>
												<span class="ps-1"><?php echo esc_html__( 'History', 'flowmattic' ); ?></span>
											</button>
										</div>
									</div>
								</div>

								<div class="server-card-body">
									<div class="server-description">
										<p><?php echo esc_html( $server_data['description'] ); ?></p>
									</div>									
									<div class="server-url-section">
										<label class="section-label"><?php echo esc_html__( 'MCP Server URL', 'flowmattic' ); ?></label>
										<div class="url-input-group">
											<?php
											$server_url         = $server_data['url'];
											$server_placeholder = $server_url;
											if ( strpos( $server_url, 'v1/mcp-' ) !== false ) {
												$server_placeholder = substr( $server_url, 0, strpos( $server_url, 'v1/mcp' ) + 7 ) . str_repeat( '*', strlen( substr( $server_url, strpos( $server_url, 'v1/mcp' ) + 8 ) ) );
											}
											?>
											<input type="text" class="form-control url-input" id="mcpServerUrl" placeholder="<?php echo esc_attr( $server_placeholder ); ?>" data-value="<?php echo esc_attr( $server_data['url'] ); ?>" readonly>
											<button class="btn btn-copy btn-sm rounded border <?php echo ! $server_data['active'] ? 'disabled' : ''; ?>" type="button" onclick="copyServerUrl()">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
													<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
												</svg>
												<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
											</button>
											<div class="config-actions">
												<button type="button" class="btn btn-secondary-light rounded btn-outline-primary btn-md d-flex gap-1" data-toggle="modal" data-target="#clientConfigModal">
													<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512"><path d="M288 368a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-80c-8.8 0-16 7.2-16 16l0 48-48 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 0 48c0 8.8 7.2 16 16 16s16-7.2 16-16l0-48 48 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-48 0 0-48c0-8.8-7.2-16-16-16zM104 0c13.3 0 24 10.7 24 24l0 88-48 0 0-88C80 10.7 90.7 0 104 0zM280 0c13.3 0 24 10.7 24 24l0 88-48 0 0-88c0-13.3 10.7-24 24-24zM0 168c0-13.3 10.7-24 24-24l8 0 48 0 224 0 48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 19.2c-18 9.2-34.2 21.4-48 36l0-55.2L80 192l0 64c0 61.9 50.1 112 112 112c24.3 0 46.9-7.8 65.2-20.9c-.8 6.9-1.2 13.9-1.2 20.9c0 11.4 1.1 22.5 3.1 33.3c-13.5 6.2-28 10.7-43.1 12.9l0 73.8c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-73.8C91 402.6 32 336.2 32 256l0-64-8 0c-13.3 0-24-10.7-24-24z"/></svg>
													<span class="ps-1"><?php echo esc_html__( 'Connect', 'flowmattic' ); ?></span>
												</button>
											</div>
										</div>
										<small class="form-text"><?php echo esc_html__( 'This is your MCP server URL. You can use this URL to connect your FlowMattic MCP server with any compatible MCP client.', 'flowmattic' ); ?></small><br/>
										<small class="form-text"><?php echo __( 'Note: To use the MCP Server URL with MCP Client where it requires a SSE connection, you may need to append <code>/sse</code> to the URL.', 'flowmattic' ); ?></small>
									</div>
								</div>
							</div>

							<!-- Modern Tools Section -->
							<div class="mcp-tools-section">
								<div class="tools-header">
									<div class="tools-header-left">
										<h3 class="tools-title m-0"><?php echo esc_html__( 'Available Tools', 'flowmattic' ); ?></h3>
										<span class="tools-count">
											<?php echo sprintf( esc_html__( '%d tools configured', 'flowmattic' ), count( $tools_data ) ); ?>
											<?php if ( $total_executions > 0 ) : ?>
												<span class="execution-summary">
													• <?php echo sprintf( esc_html__( '%s executions across %d tools', 'flowmattic' ), number_format( $total_executions ), $tools_with_executions ); ?>
												</span>
											<?php endif; ?>
										</span>
									</div>
									<div class="tools-header-right">
										<div class="tools-filters">
											<select class="form-select filter-select" id="executionFilter" onchange="filterTools()">
												<option value=""><?php echo esc_html__( 'All Types', 'flowmattic' ); ?></option>
												<option value="php_function"><?php echo esc_html__( 'PHP Function', 'flowmattic' ); ?></option>
												<option value="workflow_id"><?php echo esc_html__( 'Workflow', 'flowmattic' ); ?></option>
												<option value="api_endpoint"><?php echo esc_html__( 'API Endpoint', 'flowmattic' ); ?></option>
												<option value="database_query"><?php echo esc_html__( 'Database Query', 'flowmattic' ); ?></option>
											</select>
											<div class="search-input-wrapper">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" class="search-icon">
													<path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
												</svg>
												<input type="text" class="form-control search-input" id="searchTools" placeholder="Search tools..." onkeyup="filterTools()" style="padding-left:  32px;">
											</div>
										</div>
									</div>
								</div>

								<div class="tools-content">
									<?php if ( empty( $tools_data ) ) : ?>
										<!-- Modern Empty State -->
										<div class="mcp-empty-state-modern">
											<div class="empty-state-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
													<path d="M78.6 5C69.1-2.4 55.6-1.5 47 7L7 47c-8.5 8.5-9.4 22-2.1 31.6l80 104c4.5 5.9 11.6 9.4 19 9.4h54.1l109 109c-14.7 29-10 65.4 14.3 89.6l112 112c12.5 12.5 32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3l-112-112c-24.2-24.2-60.6-29-89.6-14.3l-109-109V104c0-7.5-3.5-14.5-9.4-19L78.6 5zM19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L192.1 416H224c17.7 0 32-14.3 32-32V352c0-17.7-14.3-32-32-32H192.1L19.9 396.1z"/>
												</svg>
											</div>
											<h4><?php echo esc_html__( 'No Tools Configured', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Get started by adding your first tool to this MCP server. Tools allow Claude AI to interact with your workflows and data.', 'flowmattic' ); ?></p>
											<button type="button" class="btn btn-primary mcp-btn-primary" data-toggle="modal" data-target="#addToolModal">
												<span class="btn-icon">
													<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
														<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
													</svg>
												</span>
												<?php echo esc_html__( 'Create Your First Tool', 'flowmattic' ); ?>
											</button>
										</div>
									<?php else : ?>
										<!-- Modern Tools Grid -->
										<div class="tools-grid" id="toolsGrid">
											<?php foreach ( $tools_data as $tool ) : ?>
												<?php
												// Determine execution type and display info
												$execution_method = $tool['execution_method'];
												$execution_info   = '';
												$execution_class  = 'secondary';
												$execution_label  = 'Unknown';

												switch ( $execution_method ) {
													case 'php_function':
														$execution_label = 'PHP Function';
														$execution_info  = $tool['function_name'] ?: 'No function set';
														$execution_class = 'success';
														break;
													case 'workflow_id':
														$workflow_name = 'No workflow set';
														foreach ( $all_workflows as $w_key => $workflow ) {
															if ( $workflow->workflow_id === $tool['workflow_id'] ) {
																$workflow_name = rawurldecode( $workflow->workflow_name ) . ' (' . $workflow->workflow_id . ')';
																break;
															}
														}

														$execution_label = 'Workflow';
														$execution_info  = strlen( $workflow_name ) > 36 ? substr( $workflow_name, 0, 36 ) . '...' : $workflow_name;
														$execution_class = 'info';
														break;
													case 'api_endpoint':
														$execution_label = 'API Endpoint';
														$execution_info  = $tool['endpoint'] ?: 'No endpoint set';
														$execution_class = 'primary';
														break;
													case 'database_query':
														$execution_label = 'Database Query';
														$execution_info  = $tool['mcp_tool_db_query'] ?: 'No query set';
														$execution_class = 'purple';
														break;
													default:
														$execution_label = 'Unknown';
														$execution_info  = 'Method not set';
														$execution_class = 'secondary';
												}

												$is_plugin = $tool['source'] === 'plugin';
												$tool_id = $tool['mcp_tool_id'];
												$tool_counts = isset( $execution_counts[ $tool_id ] ) ? $execution_counts[ $tool_id ] : null;
												?>
												<div class="tool-card" data-execution="<?php echo esc_attr( $execution_method ); ?>" data-name="<?php echo esc_attr( strtolower( $tool['name'] ) ); ?>" data-tool-id="<?php echo esc_attr( $tool['mcp_tool_id'] ); ?>">
													<div class="tool-card-header">
														<div class="tool-header-left d-flex align-items-center gap-2">
															<div class="tool-icon">
																<?php if ( $execution_method === 'php_function' ) : ?>
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 640 512">
																		<path d="M392.8 1.2c-17-4.9-34.7 5-39.6 22l-128 448c-4.9 17 5 34.7 22 39.6s34.7-5 39.6-22l128-448c4.9-17-5-34.7-22-39.6zm80.6 120.1c-12.5 12.5-12.5 32.8 0 45.3L562.7 256l-89.4 89.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l112-112c12.5-12.5 12.5-32.8 0-45.3l-112-112c-12.5-12.5-32.8-12.5-45.3 0zm-306.7 0c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3l112 112c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256l89.4-89.4c12.5-12.5 12.5-32.8 0-45.3z"/>
																	</svg>
																<?php elseif ( $execution_method === 'workflow_id' ) : ?>
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 640 512">
																		<path d="M400 48l0 96-160 0 0-96 160 0zM240 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l56 0 0 40L24 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l112 0 0 40-56 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l160 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-56 0 0-40 272 0 0 40-56 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l160 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-56 0 0-40 112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-272 0 0-40 56 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48L240 0zM80 368l160 0 0 96L80 464l0-96zm480 0l0 96-160 0 0-96 160 0z"/>
																	</svg>
																<?php elseif ( $execution_method === 'api_endpoint' ) : ?>
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512">
																		<path d="M0 256C0 167.6 71.6 96 160 96l72 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-72 0C98.1 144 48 194.1 48 256s50.1 112 112 112l72 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-72 0C71.6 416 0 344.4 0 256zm576 0c0 88.4-71.6 160-160 160l-72 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l72 0c61.9 0 112-50.1 112-112s-50.1-112-112-112l-72 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l72 0c88.4 0 160 71.6 160 160zM184 232l208 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-208 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
																	</svg>
																<?php elseif ( $execution_method === 'database_query' ) : ?>
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																		<path d="M448 80v48c0 44.2-100.3 80-224 80S0 172.2 0 128V80C0 35.8 100.3 0 224 0S448 35.8 448 80zM393.2 214.7c20.8-7.4 39.9-16.9 54.8-28.6V288c0 44.2-100.3 80-224 80S0 332.2 0 288V186.1c14.9 11.8 34 21.2 54.8 28.6C99.7 230.7 159.5 240 224 240s124.3-9.3 169.2-25.3zM0 346.1c14.9 11.8 34 21.2 54.8 28.6C99.7 390.7 159.5 400 224 400s124.3-9.3 169.2-25.3c20.8-7.4 39.9-16.9 54.8-28.6V432c0 44.2-100.3 80-224 80S0 476.2 0 432V346.1z"/>
																	</svg>
																<?php else : ?>
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
																		<path d="M78.6 5C69.1-2.4 55.6-1.5 47 7L7 47c-8.5 8.5-9.4 22-2.1 31.6l80 104c4.5 5.9 11.6 9.4 19 9.4h54.1l109 109c-14.7 29-10 65.4 14.3 89.6l112 112c12.5 12.5 32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3l-112-112c-24.2-24.2-60.6-29-89.6-14.3l-109-109V104c0-7.5-3.5-14.5-9.4-19L78.6 5z"/>
																	</svg>
																<?php endif; ?>
															</div>
															<div class="tool-name d-flex flex-column align-items-start">
																<?php echo esc_html(trim( $tool['name'] ) ); ?>
																<!-- Execution Statistics (if available) -->
																<?php if ( $tool_counts ) : ?>
																	<div class="tool-execution-stats">
																		<div class="execution-stat-item d-flex align-items-start align-items-center gap-2">
																			<div class="stat-label"><?php echo esc_html__( 'Last Used:', 'flowmattic' ); ?></div>
																			<div class="stat-value last-execution">
																				<?php echo esc_html( $tool_counts['last_execution_ago'] ); ?>
																			</div>
																		</div>
																	</div>
																<?php endif; ?>
															</div>
														</div>
														<div class="tool-actions d-flex align-items-center gap-1">
															<!-- Execution Count Badge -->
															<?php if ( $tool_counts ) : ?>
																<div class="tool-execution-count" title="<?php echo esc_attr( sprintf( __( 'Total: %d executions, Success rate: %s%%', 'flowmattic' ), $tool_counts['total_executions'], $tool_counts['success_rate'] ) ); ?>">
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="12" height="12">
																		<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
																	</svg>
																	<span class="count-number"><?php echo number_format( $tool_counts['total_executions'] ); ?></span>
																</div>
															<?php else : ?>
																<div class="tool-execution-count no-executions" title="<?php echo esc_attr__( 'No executions yet', 'flowmattic' ); ?>">
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="12" height="12">
																		<path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/>
																	</svg>
																	<span class="count-number">0</span>
																</div>
															<?php endif; ?>

															<div class="dropdown">
																<button class="btn btn-ghost btn-sm dropdown-toggle d-flex" type="button" data-toggle="dropdown" aria-expanded="false">
																	<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 128 512" width="16" height="16">
																		<path d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z"/>
																	</svg>
																</button>
																<ul class="dropdown-menu dropdown-menu-end pb-0 mt-1">
																	<?php if ( ! $is_plugin ) : ?>
																		<li><a class="dropdown-item" href="#" onclick="editTool('<?php echo esc_js( $tool['mcp_tool_id'] ); ?>', '<?php echo esc_js( $tool['name'] ); ?>')">
																			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
																				<path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/>
																			</svg>
																			<?php echo esc_html__( 'Edit Tool', 'flowmattic' ); ?>
																			</a></li>
																		<li><a class="dropdown-item" href="#" onclick="duplicateTool('<?php echo esc_js( $tool['mcp_tool_id'] ); ?>')">
																			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																				<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
																			</svg>
																			<?php echo esc_html__( 'Duplicate', 'flowmattic' ); ?>
																			</a></li>
																		<li><a class="dropdown-item" href="<?php echo admin_url( 'admin.php?page=flowmattic-mcp-history&server_id=' . urlencode( $server_id ) . '&tool_id=' . urlencode( $tool['mcp_tool_id'] ) ); ?>">
																			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
																				<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
																			</svg>
																			<?php echo esc_html__( 'Tool History', 'flowmattic' ); ?>
																			</a></li>
																		<li><hr class="dropdown-divider"></li>
																		<li><a class="dropdown-item text-danger" href="#" onclick="deleteTool('<?php echo esc_js( $tool['mcp_tool_id'] ); ?>', '<?php echo esc_js( $tool['name'] ); ?>')">
																			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																				<path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/>
																			</svg>
																			<?php echo esc_html__( 'Delete Tool', 'flowmattic' ); ?>
																			</a></li>
																	<?php else : ?>
																		<li><a class="dropdown-item" href="<?php echo admin_url( 'admin.php?page=flowmattic-mcp-history&server_id=' . urlencode( $server_id ) . '&tool_id=' . urlencode( $tool['mcp_tool_id'] ) ); ?>">
																			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
																				<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
																			</svg>
																			<?php echo esc_html__( 'Tool History', 'flowmattic' ); ?>
																			</a></li>
																	<?php endif; ?>
																	</ul>
																</div>
														</div>
													</div>
													
													<div class="tool-card-body">
														<div class="tool-description">
															<?php echo esc_html( $tool['description'] ); ?>
														</div>
														<div class="tool-endpoint">
															<?php echo esc_html( strlen( $execution_info ) > 45 ? substr( $execution_info, 0, 36 ) . '...' : $execution_info ); ?>
														</div>
													</div>
													
													<div class="tool-card-footer">
														<div class="tool-badge tool-badge-<?php echo esc_attr( $execution_class ); ?>">
															<?php echo esc_html( $execution_label ); ?>
														</div>
														<?php if ( ! empty( $tool['method'] ) && $execution_method === 'api_endpoint' ) : ?>
															<div class="tool-method tool-method-<?php echo esc_attr( strtolower( $tool['method'] ) ); ?>">
																<?php echo esc_html( $tool['method'] ); ?>
															</div>
														<?php endif; ?>
														<?php if ( $is_plugin ) : ?>
															<span class="plugin-badge">
																<?php echo $tool['source_name']; ?>
															</span>
														<?php endif; ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Modals remain the same but with updated button styling -->
						<!-- Add Tool Modal -->
						<div class="modal fade" id="addToolModal" tabindex="-1">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<div class="modal-content mcp-modal-content">
									<div class="modal-header mcp-modal-header">
										<h5 class="modal-title">
											<span class="modal-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
													<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
												</svg>
											</span>
											<?php echo esc_html__( 'Add New Tool', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close d-flex" data-dismiss="modal">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512"><path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4-9.4 24.6-9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
										</button>
									</div>
									<form id="addToolForm">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-8">
													<div class="mb-3">
														<label class="form-label"><?php echo esc_html__( 'Tool Name', 'flowmattic' ); ?> <small class="text-danger">(<?php echo esc_html__( 'required', 'flowmattic' ); ?>)</small></label>
														<input type="text" class="form-control" name="tool_name" required placeholder="<?php echo esc_attr__( 'e.g., Get User Info, Fetch Posts', 'flowmattic' ); ?>">
														<small class="form-text text-muted"><?php echo esc_html__( 'This name will be used to identify the tool in the MCP server and by Claude AI. Enter text in plain English. No special characters or emojis.', 'flowmattic' ); ?></small>
													</div>
												</div>
												<div class="col-md-4">
													<div class="mb-3">
														<label class="form-label"><?php echo esc_html__( 'Execution Method', 'flowmattic' ); ?></label>
														<select class="form-select" name="execution_method" id="executionMethod" required onchange="toggleExecutionFields()">
															<option value=""><?php echo esc_html__( 'Select Method', 'flowmattic' ); ?></option>
															<option value="php_function"><?php echo esc_html__( 'PHP Function', 'flowmattic' ); ?></option>
															<option value="workflow_id"><?php echo esc_html__( 'Workflow', 'flowmattic' ); ?></option>
															<option value="api_endpoint"><?php echo esc_html__( 'API Endpoint', 'flowmattic' ); ?></option>
															<option value="database_query"><?php echo esc_html__( 'Database Query', 'flowmattic' ); ?></option>
														</select>
													</div>
												</div>
											</div>
											<div class="mb-3">
												<label class="form-label"><?php echo esc_html__( 'Description', 'flowmattic' ); ?></label>
												<textarea class="form-control" name="description" rows="2" required></textarea>
											</div>

											<!-- PHP Function Fields -->
											<div id="phpFunctionFields" class="execution-fields" style="display: none;">
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'PHP Function Name', 'flowmattic' ); ?></label>
													<input type="text" class="form-control" name="function_name" placeholder="<?php echo esc_attr__( 'e.g., get_user_by, get_posts, custom_function', 'flowmattic' ); ?>">
													<small class="form-text text-muted"><?php echo esc_html__( 'Function must exist and be callable. Arguments will be passed as array.', 'flowmattic' ); ?></small>
												</div>
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'Parameters Request Type', 'flowmattic' ); ?></label>
													<select class="form-select w-100 mw-100" name="params_request_type">
														<option value="individual"><?php echo esc_html__( 'Individual Parameters', 'flowmattic' ); ?></option>
														<option value="array"><?php echo esc_html__( 'Array', 'flowmattic' ); ?></option>
													</select>
													<small class="form-text text-muted"><?php echo esc_html__( 'Select how parameters will be passed to the function. "Array" will pass all parameters as a single array.', 'flowmattic' ); ?></small>
												</div>
											</div>

											<!-- Workflow ID Fields -->
											<div id="workflowFields" class="execution-fields" style="display: none;">
												<div class="mb-1">
													<label class="form-label"><?php echo esc_html__( 'Workflow to Execute', 'flowmattic' ); ?></label>
													<select class="form-select mt-2 mb-2 w-100 mw-100" name="workflow_id" id="workflowSelect">
														<option value=""><?php echo esc_html__( 'Select Workflow', 'flowmattic' ); ?></option>
														<?php foreach ( $all_workflows as $w_key => $workflow ) : ?>
															<option value="<?php echo esc_attr( $workflow->workflow_id ); ?>"><?php echo rawurldecode( $workflow->workflow_name ); ?></option>
														<?php endforeach; ?>
													</select>
													<small class="form-text text-muted"><?php echo esc_html__( 'Select the FlowMattic workflow to execute when this tool is called. The workflow must be published and active.', 'flowmattic' ); ?></small>
												</div>
											</div>

											<!-- API Endpoint Fields -->
											<div id="apiEndpointFields" class="execution-fields" style="display: none;">
												<div class="row">
													<div class="col-md-8">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'API Endpoint', 'flowmattic' ); ?></label>
															<input type="url" class="form-control" name="endpoint" placeholder="<?php echo esc_attr__( 'https://api.example.com/endpoint', 'flowmattic' ); ?>">
														</div>
													</div>
													<div class="col-md-4">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'HTTP Method', 'flowmattic' ); ?></label>
															<select class="form-select" name="method">
																<option value="GET"><?php echo esc_html__( 'GET', 'flowmattic' ); ?></option>
																<option value="POST"><?php echo esc_html__( 'POST', 'flowmattic' ); ?></option>
																<option value="PUT"><?php echo esc_html__( 'PUT', 'flowmattic' ); ?></option>
																<option value="DELETE"><?php echo esc_html__( 'DELETE', 'flowmattic' ); ?></option>
															</select>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?></label>
															<select name="api_connect" class="api-connect w-100 mw-100 mb-2 mt-2 form-select">
																<option value="none"><?php echo esc_html__( 'None', 'flowmattic' ); ?></option>
																<?php
																foreach ( $all_connects as $connect ) {
																	echo '<option value="' . esc_attr( $connect->id ) . '">' . rawurldecode( $connect->connect_name ) . '</option>';
																}
																?>
															</select>
															<small class="form-text text-muted"><?php echo esc_html__( 'Select a FlowMattic Connect to use for this API call. This will automatically handle authentication and token management.', 'flowmattic' ); ?></small>
														</div>
													</div>
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Headers (JSON, Optional)', 'flowmattic' ); ?></label>
															<textarea class="form-control mcp-code" name="headers" rows="1" placeholder='<?php echo esc_attr__( '{"Authorization": "Bearer token"}', 'flowmattic' ); ?>'></textarea>
														</div>
													</div>
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Webhook URL (Optional)', 'flowmattic' ); ?></label>
															<input type="url" class="form-control" name="webhook_url" placeholder="<?php echo esc_attr__( 'https://flowmattic.com/webhook/capture/...', 'flowmattic' ); ?>">
														</div>
													</div>
												</div>
											</div>

											<!-- Database Query Fields -->
											<div id="databaseQueryFields" class="execution-fields" style="display: none;">
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'Database Query', 'flowmattic' ); ?></label>
													<textarea class="form-control mcp-code" name="database_query" rows="4" placeholder="<?php echo esc_attr__( "SELECT * FROM wp_posts WHERE post_status = 'publish';", 'flowmattic' ); ?>"></textarea>
													<small class="form-text text-danger"><?php echo esc_html__( 'SQL query to execute. Use with caution.', 'flowmattic' ); ?></small><br>
													<small class="form-text text-muted"><?php echo esc_html__( 'To use dynamic parameters, use placeholders like :param_name. These will be replaced with actual values at runtime.', 'flowmattic' ); ?></small><br>
													<code>
														<?php echo esc_html__( 'Example:', 'flowmattic' ); ?> <br>
														<?php echo esc_html__( "SELECT * FROM wp_posts WHERE post_content LIKE '%:search_term%';", 'flowmattic' ); ?>
													</code><br>
													<small class="text-muted"><?php echo esc_html__( "This will search for posts containing the term provided in the 'search_term' parameter.", 'flowmattic' ); ?></small>
												</div>
											</div>

											<!-- Input Schema Builder -->
											<div class="mb-3">
												<label class="form-label"><?php echo esc_html__( 'Input Parameters', 'flowmattic' ); ?></label>
												<div class="mb-2">
													<small class="text-muted">
														<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px"  viewBox="0 0 512 512"><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336c-13.3 0-24 10.7-24 24s10.7 24 24 24l80 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-8 0 0-88c0-13.3-10.7-24-24-24l-48 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l24 0 0 64-24 0zm40-144a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/></svg>
														<?php echo esc_html__( 'Parameter names should be in lowercase with underscores (e.g., user_id, first_name, search_term). The format will be automatically corrected as you type.', 'flowmattic' ); ?>
													</small>
												</div>
												<div id="parametersContainer">
													<div class="text-muted mb-2">
														<small><?php echo esc_html__( 'Define the parameters that this tool accepts. Click "Add Parameter" to get started.', 'flowmattic' ); ?></small>
													</div>
												</div>
												<button type="button" class="btn btn-outline-primary btn-sm" onclick="addParameter()">
													<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 448 512">
														<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
													</svg>
													<?php echo esc_html__( 'Add Parameter', 'flowmattic' ); ?>
												</button>
												<input type="hidden" name="input_schema" id="generatedInputSchema">
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
											<button type="submit" class="btn btn-primary d-flex align-items-center">
												<span class="btn-icon me-2 d-inline-flex">
													<svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="currentColor" viewBox="0 0 448 512">
														<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
													</svg>
												</span>
												<?php echo esc_html__( 'Save Tool', 'flowmattic' ); ?>
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<!-- Edit Tool Modal -->
						<div class="modal fade" id="editToolModal" tabindex="-1">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<div class="modal-content mcp-modal-content">
									<div class="modal-header mcp-modal-header">
										<h5 class="modal-title">
											<span class="modal-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
													<path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/>
												</svg>
											</span>
											<?php echo esc_html__( 'Edit Tool', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close d-flex" data-dismiss="modal">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512"><path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
										</button>
									</div>
									<form id="editToolForm">
										<input type="hidden" name="tool_id" id="editToolId">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-8">
													<div class="mb-3">
														<label class="form-label"><?php echo esc_html__( 'Tool Name', 'flowmattic' ); ?> <small class="text-danger">(<?php echo esc_html__( 'required', 'flowmattic' ); ?>)</small></label>
														<input type="text" class="form-control" name="tool_name" id="editToolName" required>
														<small class="form-text text-muted"><?php echo esc_html__( 'This name will be used to identify the tool in the MCP server and by Claude AI. Enter text in plain English. No special characters or emojis.', 'flowmattic' ); ?></small>
													</div>
												</div>
												<div class="col-md-4">
													<div class="mb-3">
														<label class="form-label"><?php echo esc_html__( 'Execution Method', 'flowmattic' ); ?></label>
														<select class="form-select" name="execution_method" id="editExecutionMethod" required onchange="toggleEditExecutionFields()">
															<option value="php_function"><?php echo esc_html__( 'PHP Function', 'flowmattic' ); ?></option>
															<option value="workflow_id"><?php echo esc_html__( 'Workflow', 'flowmattic' ); ?></option>
															<option value="api_endpoint"><?php echo esc_html__( 'API Endpoint', 'flowmattic' ); ?></option>
															<option value="database_query"><?php echo esc_html__( 'Database Query', 'flowmattic' ); ?></option>
														</select>
													</div>
												</div>
											</div>
											<div class="mb-3">
												<label class="form-label"><?php echo esc_html__( 'Description', 'flowmattic' ); ?></label>
												<textarea class="form-control" name="description" id="editDescription" rows="2" required></textarea>
											</div>

											<!-- Edit PHP Function Fields -->
											<div id="editPhpFunctionFields" class="execution-fields" style="display: none;">
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'PHP Function Name', 'flowmattic' ); ?></label>
													<input type="text" class="form-control" name="function_name" id="editFunctionName">
													<small class="form-text text-muted"><?php echo esc_html__( 'Function must exist and be callable.', 'flowmattic' ); ?></small>
												</div>
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'Parameters Request Type', 'flowmattic' ); ?></label>
													<select class="form-select w-100 mw-100" name="params_request_type" id="editParamsRequestType">
														<option value="individual"><?php echo esc_html__( 'Individual Parameters', 'flowmattic' ); ?></option>
														<option value="array"><?php echo esc_html__( 'Array', 'flowmattic' ); ?></option>
													</select>
													<small class="form-text text-muted"><?php echo esc_html__( 'Select how parameters will be passed to the function. "Array" will pass all parameters as a single array.', 'flowmattic' ); ?></small>
												</div>
											</div>

											<!-- Edit Workflow ID Fields -->
											<div id="editWorkflowFields" class="execution-fields" style="display: none;">
												<div class="mb-1">
													<label class="form-label"><?php echo esc_html__( 'Workflow to Execute', 'flowmattic' ); ?></label>
													<select class="form-select mt-2 mb-2 w-100 mw-100" name="workflow_id" id="editWorkflowSelect">
														<option value=""><?php echo esc_html__( 'Select Workflow', 'flowmattic' ); ?></option>
														<?php foreach ( $all_workflows as $w_key => $workflow ) : ?>
															<option value="<?php echo esc_attr( $workflow->workflow_id ); ?>"><?php echo rawurldecode( $workflow->workflow_name ); ?></option>
														<?php endforeach; ?>
													</select>
													<small class="form-text text-muted"><?php echo esc_html__( 'Select the FlowMattic workflow to execute when this tool is called. The workflow must be published and active.', 'flowmattic' ); ?></small>
												</div>
											</div>

											<!-- Edit API Endpoint Fields -->
											<div id="editApiEndpointFields" class="execution-fields" style="display: none;">
												<div class="row">
													<div class="col-md-8">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'API Endpoint', 'flowmattic' ); ?></label>
															<input type="url" class="form-control" name="endpoint" id="editEndpoint">
														</div>
													</div>
													<div class="col-md-4">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'HTTP Method', 'flowmattic' ); ?></label>
															<select class="form-select" name="method" id="editMethod">
																<option value="GET"><?php echo esc_html__( 'GET', 'flowmattic' ); ?></option>
																<option value="POST"><?php echo esc_html__( 'POST', 'flowmattic' ); ?></option>
																<option value="PUT"><?php echo esc_html__( 'PUT', 'flowmattic' ); ?></option>
																<option value="DELETE"><?php echo esc_html__( 'DELETE', 'flowmattic' ); ?></option>
															</select>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Choose Connect', 'flowmattic' ); ?></label>
															<select name="api_connect" class="api-connect w-100 mw-100 mb-2 mt-2 form-select" id="editApiConnect">
																<option value="none"><?php echo esc_html__( 'None', 'flowmattic' ); ?></option>
																<?php
																foreach ( $all_connects as $connect ) {
																	echo '<option value="' . esc_attr( $connect->id ) . '">' . rawurldecode( $connect->connect_name ) . '</option>';
																}
																?>
															</select>
															<small class="form-text text-muted"><?php echo esc_html__( 'Select a FlowMattic Connect to use for this API call. This will automatically handle authentication and token management.', 'flowmattic' ); ?></small>
														</div>
													</div>
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Headers (JSON, Optional)', 'flowmattic' ); ?></label>
															<textarea class="form-control mcp-code" name="headers" id="editHeaders" rows="1"></textarea>
														</div>
													</div>
													<div class="col-md-12">
														<div class="mb-3">
															<label class="form-label"><?php echo esc_html__( 'Webhook URL (Optional)', 'flowmattic' ); ?></label>
															<input type="url" class="form-control" name="webhook_url" id="editWebhookUrl">
														</div>
													</div>
												</div>
											</div>

											<!-- Edit Database Query Fields -->
											<div id="editDatabaseQueryFields" class="execution-fields" style="display: none;">
												<div class="mb-3">
													<label class="form-label"><?php echo esc_html__( 'Database Query', 'flowmattic' ); ?></label>
													<textarea class="form-control mcp-code" name="database_query" id="editDatabaseQuery" rows="4" placeholder="<?php echo esc_attr__( "SELECT * FROM wp_posts WHERE post_status = 'publish';", 'flowmattic' ); ?>"></textarea>
													<small class="form-text text-danger"><?php echo esc_html__( 'SQL query to execute. Use with caution.', 'flowmattic' ); ?></small>
													<small class="form-text text-muted"><?php echo esc_html__( 'To use dynamic parameters, use placeholders like :param_name. These will be replaced with actual values at runtime.', 'flowmattic' ); ?></small><br>
													<code>
														<?php echo esc_html__( 'Example:', 'flowmattic' ); ?> <br>
														<pre class="mcp-code"><?php echo esc_html__( "SELECT * FROM wp_posts WHERE post_content LIKE '%:search_term%';", 'flowmattic' ); ?></pre>
														<small class="text-muted"><?php echo esc_html__( "This will search for posts containing the term provided in the 'search_term' parameter.", 'flowmattic' ); ?></small>
													</code>
												</div>
											</div>

											<!-- Input Schema Builder -->
											<div class="mb-3">
												<label class="form-label"><?php echo esc_html__( 'Input Parameters', 'flowmattic' ); ?></label>
												<div class="mb-2">
													<small class="text-muted">
														<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px"  viewBox="0 0 512 512"><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336c-13.3 0-24 10.7-24 24s10.7 24 24 24l80 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-8 0 0-88c0-13.3-10.7-24-24-24l-48 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l24 0 0 64-24 0zm40-144a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/></svg>
														<?php echo esc_html__( 'Parameter names should be in lowercase with underscores (e.g., user_id, first_name, search_term). The format will be automatically corrected as you type.', 'flowmattic' ); ?>
													</small>
												</div>
												<div id="editParametersContainer">
													<div class="text-muted mb-2">
														<small><?php echo esc_html__( 'Define the parameters that this tool accepts. Click "Add Parameter" to get started.', 'flowmattic' ); ?></small>
													</div>
												</div>
												<button type="button" class="btn btn-outline-primary btn-sm" onclick="addEditParameter()">
													<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 448 512">
														<path d="M248 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 160L40 232c-13.3 0-24 10.7-24 24s10.7 24 24 24l160 0 0 160c0 13.3 10.7 24 24 24s24-10.7 24-24l0-160 160 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-160 0 0-160z"/>
													</svg>
													<?php echo esc_html__( 'Add Parameter', 'flowmattic' ); ?>
												</button>
												<input type="hidden" name="input_schema" id="editGeneratedInputSchema">
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
											<button type="submit" class="btn btn-primary">
												<?php echo esc_html__( 'Update Tool', 'flowmattic' ); ?>
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<!-- Server Settings Modal -->
						<div class="modal fade" id="serverSettingsModal" tabindex="-1">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<div class="modal-content mcp-modal-content">
									<div class="modal-header mcp-modal-header">
										<h5 class="modal-title">
											<span class="modal-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
													<path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/>
												</svg>
											</span>
											<?php echo esc_html__( 'Server Settings', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close d-flex" data-dismiss="modal">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512"><path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
										</button>
									</div>
									<form id="serverSettingsForm">
										<div class="modal-body">
											<div class="mb-3">
												<label class="form-label"><?php echo esc_html__( 'Server Name', 'flowmattic' ); ?></label>
												<input type="text" class="form-control" name="server_name" value="<?php echo esc_attr( $server_data['name'] ); ?>">
												<small class="form-text text-muted"><?php echo esc_html__( 'A friendly name for this server (used for display purposes).', 'flowmattic' ); ?></small>
											</div>
											<div class="form-check d-flex gap-2">
												<input id="server-active-state" class="form-check-input bg-transparent" type="checkbox" name="active" <?php checked( $server_data['active'] ); ?>>
												<label for="server-active-state" class="form-check-label"><?php echo esc_html__( 'Server Active', 'flowmattic' ); ?></label>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
											<button type="submit" class="btn btn-primary">
												<?php echo esc_html__( 'Save Settings', 'flowmattic' ); ?>
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<!-- Client Configuration Modal -->
						<div class="modal fade" id="clientConfigModal" tabindex="-1">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<div class="modal-content mcp-modal-content">
									<div class="modal-header mcp-modal-header">
										<h5 class="modal-title">
											<span class="modal-icon">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512"><path d="M288 368a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-80c-8.8 0-16 7.2-16 16l0 48-48 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 0 48c0 8.8 7.2 16 16 16s16-7.2 16-16l0-48 48 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-48 0 0-48c0-8.8-7.2-16-16-16zM104 0c13.3 0 24 10.7 24 24l0 88-48 0 0-88C80 10.7 90.7 0 104 0zM280 0c13.3 0 24 10.7 24 24l0 88-48 0 0-88c0-13.3 10.7-24 24-24zM0 168c0-13.3 10.7-24 24-24l8 0 48 0 224 0 48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 19.2c-18 9.2-34.2 21.4-48 36l0-55.2L80 192l0 64c0 61.9 50.1 112 112 112c24.3 0 46.9-7.8 65.2-20.9c-.8 6.9-1.2 13.9-1.2 20.9c0 11.4 1.1 22.5 3.1 33.3c-13.5 6.2-28 10.7-43.1 12.9l0 73.8c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-73.8C91 402.6 32 336.2 32 256l0-64-8 0c-13.3 0-24-10.7-24-24z"/></svg>
											</span>
											<?php echo esc_html__( 'Connect to MCP Client', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close d-flex" data-dismiss="modal">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512"><path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
										</button>
									</div>
									<div class="modal-body">
										<div class="row">
											<div class="col-md-12 mb-4">
												<label class="form-label fw-bold"><?php echo esc_html__( 'Select MCP Client', 'flowmattic' ); ?></label>
												<p class="text-muted mb-2">
													<?php echo esc_html__( 'Choose the MCP client you want to connect to. This will update the configuration instructions below accordingly.', 'flowmattic' ); ?>
												</p>
												<div class="client-select-container">
													<div class="client-dropdown">
														<div class="client-selected" onclick="toggleClientDropdown()">
															<span class="client-placeholder"><?php echo esc_html__( 'Select A Client', 'flowmattic' ); ?></span>
															<svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M201.4 374.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 306.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
															</svg>
														</div>
														<div class="client-dropdown-menu" id="clientDropdownMenu">
															<!-- AI Assistants -->
															<div class="client-category">
																<div class="category-header"><?php echo esc_html__( 'AI Assistants', 'flowmattic' ); ?></div>
																<div class="client-option" data-client="claude_desktop" onclick="selectClient('claude_desktop', this)">
																	<div class="client-icon claude-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/claude.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Claude Desktop Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Claude Desktop', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="claude_chat" onclick="selectClient('claude_chat', this)">
																	<div class="client-icon claude-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/claude.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Claude Assistant (Chat) Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Claude Assistant (Chat)', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="mindpal" onclick="selectClient('mindpal', this)">
																	<div class="client-icon mindpal-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/mindpal.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Mindpal Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Mindpal', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="n8n_assistant" onclick="selectClient('n8n_assistant', this)">
																	<div class="client-icon n8n-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/n8n.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="n8n Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'n8n AI Agent', 'flowmattic' ); ?></span>
																</div>
															</div>

															<!-- APIs -->
															<div class="client-category">
																<div class="category-header"><?php echo esc_html__( 'APIs', 'flowmattic' ); ?></div>
																<div class="client-option" data-client="openai_api" onclick="selectClient('openai_api', this)">
																	<div class="client-icon openai-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/openai.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="OpenAI Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'OpenAI API', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="openai_playground" onclick="selectClient('openai_playground', this)">
																	<div class="client-icon openai-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/openai.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="OpenAI Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'OpenAI API Playground', 'flowmattic' ); ?></span>
																</div>
															</div>

															<!-- Code Editors -->
															<div class="client-category">
																<div class="category-header"><?php echo esc_html__( 'Code Editors', 'flowmattic' ); ?></div>
																<div class="client-option" data-client="vscode" onclick="selectClient('vscode', this)">
																	<div class="client-icon vscode-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/vscode.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="VS Code Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'VS Code', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="warp" onclick="selectClient('warp', this)">
																	<div class="client-icon warp-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/warp.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Warp Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Warp', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="windsurf" onclick="selectClient('windsurf', this)">
																	<div class="client-icon windsurf-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/windsurf.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Windsurf Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Windsurf', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="cursor" onclick="selectClient('cursor', this)">
																	<div class="client-icon cursor-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/cursor.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Cursor Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Cursor', 'flowmattic' ); ?></span>
																</div>
															</div>

															<!-- Voice Assistants -->
															<div class="client-category">
																<div class="category-header"><?php echo esc_html__( 'Voice Assistants', 'flowmattic' ); ?></div>
																<div class="client-option" data-client="vapi" onclick="selectClient('vapi', this)">
																	<div class="client-icon vapi-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/vapi.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Vapi Voice AI Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'Vapi', 'flowmattic' ); ?></span>
																</div>
																<div class="client-option" data-client="elevenlabs" onclick="selectClient('elevenlabs', this)">
																	<div class="client-icon elevenlabs-icon">
																		<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/elevenlabs.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="ElevenLabs Icon">
																	</div>
																	<span class="client-name"><?php echo esc_html__( 'ElevenLabs', 'flowmattic' ); ?></span>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<!-- Instructions Container -->
										<div id="clientInstructions" class="client-instructions" style="display: none;">
											<!-- Instructions will be populated here via JavaScript -->
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Close', 'flowmattic' ); ?></button>
									</div>
								</div>
							</div>
						</div>

						<!-- Toast Notification -->
						<div class="toast-container position-fixed bottom-0 end-0 p-3">
							<div id="mcpToast" class="toast mcp-toast" role="alert">
								<div class="toast-header">
									<div class="toast-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
											<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/>
										</svg>
									</div>
									<strong class="me-auto"><?php echo esc_html__( 'MCP Server', 'flowmattic' ); ?></strong>
									<button type="button" class="btn-close" data-dismiss="toast"></button>
								</div>
								<div class="toast-body"></div>
							</div>
						</div>

						<script type="text/javascript">
							jQuery( document ).ready( function( $ ) {
								// If query string is present, open the modal.
								const urlParams = new URLSearchParams( window.location.search );
								var openModal = urlParams.get( 'openModal' );

								if ( 'createTool' === openModal ) {
									var executionMethod = urlParams.get( 'executionMethod' ) || 'php_function';
									// Open the modal.
									jQuery( '#addToolModal' ).modal( 'show' );

									// Set the execution method
									jQuery( '#executionMethod' ).val( executionMethod );
									toggleExecutionFields();

									// Remove openModal from the URL.
									var newURL = urlParams.toString().replace( '&openModal=createTool&executionMethod=' + executionMethod, '' );
									window.history.pushState( '', '', '?' + newURL );
								}
							} );

							// Toggle execution method fields in Add modal
							function toggleExecutionFields() {
								const executionMethod = document.getElementById('executionMethod').value;
								const allFields = document.querySelectorAll('#addToolModal .execution-fields');
								
								// Hide all execution fields
								allFields.forEach(field => field.style.display = 'none');
								
								// Show relevant fields based on selection
								switch(executionMethod) {
									case 'php_function':
										document.getElementById('phpFunctionFields').style.display = 'block';
										break;
									case 'workflow_id':
										document.getElementById('workflowFields').style.display = 'block';
										break;
									case 'api_endpoint':
										document.getElementById('apiEndpointFields').style.display = 'block';
										break;
									case 'database_query':
										document.getElementById('databaseQueryFields').style.display = 'block';
										break;
									default:
										// No fields to show
										break;
								}
							}

							// Toggle execution method fields in Edit modal
							function toggleEditExecutionFields() {
								const executionMethod = document.getElementById('editExecutionMethod').value;
								const allFields = document.querySelectorAll('#editToolModal .execution-fields');
								
								// Hide all execution fields
								allFields.forEach(field => field.style.display = 'none');
								
								// Show relevant fields based on selection
								switch(executionMethod) {
									case 'php_function':
										document.getElementById('editPhpFunctionFields').style.display = 'block';
										break;
									case 'workflow_id':
										document.getElementById('editWorkflowFields').style.display = 'block';
										break;
									case 'api_endpoint':
										document.getElementById('editApiEndpointFields').style.display = 'block';
										break;
									case 'database_query':
										document.getElementById('editDatabaseQueryFields').style.display = 'block';
										break;
									default:
										// No fields to show
										break;
								}
							}

							// Copy URL functionality
							function copyServerUrl() {
								const urlField = document.getElementById('mcpServerUrl');
								const serverUrl = urlField.dataset.value;
								navigator.clipboard.writeText(serverUrl).then(() => {
									showNotification('Server URL copied to clipboard!', 'success');
								}).catch(() => {
									document.execCommand('copy');
									showNotification('Server URL copied to clipboard!', 'success');
								});
							}

							// Filter tools functionality
							function filterTools() {
								const executionFilter = document.getElementById('executionFilter').value;
								const searchFilter = document.getElementById('searchTools').value.toLowerCase();
								const toolCards = document.querySelectorAll('.tool-card');

								toolCards.forEach(card => {
									const execution = card.getAttribute('data-execution');
									const name = card.getAttribute('data-name');
									
									const executionMatch = !executionFilter || execution === executionFilter;
									const searchMatch = !searchFilter || name.includes(searchFilter);
									
									card.style.display = (executionMatch && searchMatch) ? 'block' : 'none';
								});
							}

							// Show notification
							function showNotification(message, type = 'info') {
								const toast = document.getElementById('mcpToast');
								const toastBody = toast.querySelector('.toast-body');
								const icon = toast.querySelector('.toast-icon svg');
								
								toastBody.textContent = message;
								
								// Update icon and color based on type
								if (type === 'success') {
									icon.innerHTML = '<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/>';
									toast.className = 'toast mcp-toast toast-success';
								} else if (type === 'error') {
									icon.innerHTML = '<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>';
									toast.className = 'toast mcp-toast toast-error';
								} else if (type === 'warning') {
									icon.innerHTML = '<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>';
									toast.className = 'toast mcp-toast toast-warning';
								} else {
									icon.innerHTML = '<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>';
									toast.className = 'toast mcp-toast';
								}
								
								const bsToast = new bootstrap.Toast(toast, {
									autohide: true,
									delay: 4000
								});
								bsToast.show();
							}

							// Add Tool Form Handler
							document.getElementById('addToolForm').addEventListener('submit', function(e) {
								e.preventDefault();
								
								const formData = new FormData(this);
								const toolName = formData.get('tool_name');
								const executionMethod = formData.get('execution_method');
								const metadata = {};
								
								if (!toolName.trim()) {
									showNotification('Tool name is required', 'error');
									return;
								}
								
								if (!executionMethod) {
									showNotification('Please select an execution method', 'error');
									return;
								}
								
								// Validate based on execution method
								if (executionMethod === 'php_function' && !formData.get('function_name').trim()) {
									showNotification('Function name is required for PHP Function method', 'error');
									return;
								}
								if (executionMethod === 'php_function') {
									if(formData.get('params_request_type').trim()) {
										metadata.params_request_type = formData.get('params_request_type');
									}
								}
								
								if (executionMethod === 'workflow_id' && !formData.get('workflow_id').trim()) {
									showNotification('Workflow ID is required for Workflow method', 'error');
									return;
								}
								
								if (executionMethod === 'api_endpoint' && !formData.get('endpoint').trim()) {
									showNotification('API Endpoint is required for API method', 'error');
									return;
								}

								if (executionMethod === 'database_query' && !formData.get('database_query').trim()) {
									showNotification('Database Query is required for Database method', 'error');
									return;
								}

								// Generate metadata.
								if ( executionMethod === 'api_endpoint' ) {
									if (formData.get('api_connect').trim()) {
										metadata.api_connect = formData.get('api_connect');
									}
								}

								// Show notification for tool creation								
								showNotification(`Creating tool "${toolName}"...`, 'info');
								
								// Prepare AJAX data based on execution method
								const ajaxData = {
									action: 'flowmattic_mcp_create_tool',
									workflow_nonce: FMConfig.workflow_nonce,
									server_id: '<?php echo esc_js( $server_id ); ?>',
									tool_name: formData.get('tool_name'),
									description: formData.get('description'),
									execution_method: executionMethod,
									input_schema: formData.get('input_schema')
								};

								// Add method-specific data
								switch(executionMethod) {
									case 'php_function':
										ajaxData.function_name = formData.get('function_name');

										if ( 'undefined' !== typeof metadata.params_request_type ) {
											ajaxData.metadata = metadata;
										}
										break;
									case 'workflow_id':
										ajaxData.workflow_id = formData.get('workflow_id');
										break;
									case 'api_endpoint':
										ajaxData.endpoint = formData.get('endpoint');
										ajaxData.method = formData.get('method');
										ajaxData.headers = formData.get('headers');
										ajaxData.webhook_url = formData.get('webhook_url');

										if ( 'undefined' !== typeof metadata.api_connect ) {
											ajaxData.metadata = metadata;
										}
										
										break;
									case 'database_query':
										ajaxData.database_query = formData.get('database_query');
										break;
									default:
										showNotification('Invalid execution method selected', 'error');
										return;
								}

								// Send AJAX request
								jQuery.post(ajaxurl, ajaxData)
									.done(function(response) {
										if (response.success) {
											showNotification(response.data.message, 'success');
											jQuery('#addToolModal').modal('hide');
											document.getElementById('addToolForm').reset();
											toggleExecutionFields(); // Reset field visibility
											
											// Refresh the tools table
											setTimeout(() => {
												location.reload();
											}, 1500);
										} else {
											showNotification(response.data || 'Failed to create tool', 'error');
										}
									})
									.fail(function(response) {
										showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
									});
							});

							// Edit Tool Function
							function editTool(toolId, toolName) {
								showNotification(`Loading "${toolName}" for editing...`, 'info');

								// Get tool data via AJAX
								jQuery.post(ajaxurl, {
									action: 'flowmattic_mcp_get_tool',
									workflow_nonce: FMConfig.workflow_nonce,
									tool_id: toolId
								})
								.done(function(response) {
									if (response.success) {
										populateEditForm(response.data.tool);
										jQuery('#editToolModal').modal('show');
									} else {
										showNotification('Failed to load tool data', 'error');
									}
								})
								.fail(function(response) {
									showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
								});
							}

							// Populate Edit Form
							function populateEditForm(tool) {
								// Populate basic tool information
								document.getElementById('editToolId').value = tool.mcp_tool_id || tool.id;
								document.getElementById('editToolName').value = tool.mcp_tool_name || tool.name;
								document.getElementById('editDescription').value = tool.mcp_tool_description || tool.description;
								document.getElementById('editExecutionMethod').value = tool.mcp_tool_execution_method || 'api_endpoint';
								
								// Set method-specific fields based on execution method
								if (tool.mcp_tool_function_name) {
									document.getElementById('editFunctionName').value = tool.mcp_tool_function_name;

									const paramsRequestType = tool.mcp_tool_metadata && tool.mcp_tool_metadata.params_request_type ? tool.mcp_tool_metadata.params_request_type : 'individual';
									document.getElementById('editParamsRequestType').value = paramsRequestType;
								}

								if (tool.mcp_tool_workflow_id) {
									document.getElementById('editWorkflowSelect').value = tool.mcp_tool_workflow_id;
								}
								
								if (tool.mcp_tool_api_endpoint) {
									document.getElementById('editEndpoint').value = tool.mcp_tool_api_endpoint;
									document.getElementById('editMethod').value = tool.mcp_tool_http_method || 'GET';
									document.getElementById('editHeaders').value = tool.mcp_tool_request_headers ? JSON.stringify(tool.mcp_tool_request_headers, null, 2) : '';
									document.getElementById('editWebhookUrl').value = tool.mcp_tool_webhook_url || '';
								}

								if (tool.mcp_tool_db_query) {
									document.getElementById('editDatabaseQuery').value = tool.mcp_tool_db_query;
								}

								// Set API Connect if available
								const apiConnectSelect = document.getElementById('editApiConnect');
								if (tool.mcp_tool_metadata && tool.mcp_tool_metadata.api_connect) {
									apiConnectSelect.value = tool.mcp_tool_metadata.api_connect;
								} else {
									apiConnectSelect.value = 'none';
								}
								
								// Parse and populate input schema parameters
								let inputSchema = tool.mcp_tool_input_schema;
								
								// Handle different input schema formats
								if (typeof inputSchema === 'string') {
									try {
										inputSchema = JSON.parse(inputSchema);
									} catch (e) {
										console.warn('Error parsing input schema:', e);
										inputSchema = {};
									}
								}
								
								// If inputSchema is null or undefined, set to empty object
								if (!inputSchema) {
									inputSchema = {};
								}
								
								// Populate parameters from the parsed schema
								populateParametersFromSchema(inputSchema, true);
								
								// Show appropriate execution method fields
								toggleEditExecutionFields();
							}

							// Edit Tool Form Handler
							document.getElementById('editToolForm').addEventListener('submit', function(e) {
								e.preventDefault();
								
								const formData = new FormData(this);
								const toolName = formData.get('tool_name');
								const metadata = {};
								
								showNotification(`Updating tool "${toolName}"...`, 'info');

								// Prepare AJAX data
								const ajaxData = {
									action: 'flowmattic_mcp_update_tool',
									workflow_nonce: FMConfig.workflow_nonce,
									tool_id: formData.get('tool_id'),
									tool_name: formData.get('tool_name'),
									description: formData.get('description'),
									execution_method: formData.get('execution_method'),
									input_schema: formData.get('input_schema'),
									function_name: formData.get('function_name'),
									database_query: formData.get('database_query'),
									workflow_id: formData.get('workflow_id'),
									endpoint: formData.get('endpoint'),
									method: formData.get('method'),
									headers: formData.get('headers'),
									webhook_url: formData.get('webhook_url')
								};

								// Set metadata for PHP Function if available
								if (formData.get('execution_method') === 'php_function') {
									if (formData.get('params_request_type').trim()) {
										metadata.params_request_type = formData.get('params_request_type');
									}
									// Add metadata to AJAX data
									ajaxData.metadata = metadata;
								}

								// Set metadata for API Connect if available
								if (formData.get('execution_method') === 'api_endpoint' && formData.get('api_connect').trim()) {
									metadata.api_connect = formData.get('api_connect');

									// Add metadata to AJAX data
									ajaxData.metadata = metadata;
								}

								jQuery.post(ajaxurl, ajaxData)
									.done(function(response) {
										if (response.success) {
											showNotification(response.data.message, 'success');
											jQuery('#editToolModal').modal('hide');
											// setTimeout(() => location.reload(), 1500);
										} else {
											showNotification(response.data || 'Failed to update tool', 'error');
										}
									})
									.fail(function(response) {
										showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
									});
							});

							// Duplicate Tool Function
							function duplicateTool(toolId) {
								showNotification('Creating duplicate tool...', 'info');
								
								jQuery.post(ajaxurl, {
									action: 'flowmattic_mcp_duplicate_tool',
									workflow_nonce: FMConfig.workflow_nonce,
									tool_id: toolId
								})
								.done(function(response) {
									if (response.success) {
										showNotification(response.data.message, 'success');
										setTimeout(() => location.reload(), 1500);
									} else {
										showNotification(response.data || 'Failed to duplicate tool', 'error');
									}
								})
								.fail(function(response) {
									showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
								});
							}

							// Delete Tool Function
							function deleteTool(toolId, toolName) {
								const confirmDelete = confirm(`Are you sure you want to delete "${toolName}"?\n\nThis action cannot be undone.`);
								
								if (confirmDelete) {
									showNotification(`Deleting "${toolName}"...`, 'warning');
									
									jQuery.post(ajaxurl, {
										action: 'flowmattic_mcp_delete_tool',
										workflow_nonce: FMConfig.workflow_nonce,
										tool_id: toolId
									})
									.done(function(response) {
										if (response.success) {
											showNotification(response.data.message, 'success');
											// Remove the card from grid
											const card = document.querySelector(`[data-tool-id="${toolId}"]`);
											if (card) {
												card.remove();
											}
										} else {
											showNotification(response.data || 'Failed to delete tool', 'error');
										}
									})
									.fail(function(response) {
										showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
									});
								}
							}

							// Server Settings Form Handler
							document.getElementById('serverSettingsForm').addEventListener('submit', function(e) {
								e.preventDefault();
								
								showNotification('Saving server settings...', 'info');
								
								const formData = new FormData(this);
								const ajaxData = {
									action: 'flowmattic_mcp_update_server_settings',
									workflow_nonce: FMConfig.workflow_nonce,
									server_id: '<?php echo esc_js( $server_id ); ?>',
									server_name: formData.get('server_name'),
									refresh_interval: formData.get('refresh_interval'),
									active: formData.get('active') ? 1 : 0
								};

								jQuery.post(ajaxurl, ajaxData)
									.done(function(response) {
										if (response.success) {
											showNotification('Server settings saved successfully!', 'success');
											jQuery('#serverSettingsModal').modal('hide');
											setTimeout(() => location.reload(), 1000);
										} else {
											showNotification(response.data || 'Failed to save settings', 'error');
										}
									})
									.fail(function(response) {
										showNotification(response.responseJSON?.data || 'Network error. Please try again.', 'error');
									});
							});

							// Initialize dropdowns and event listeners
							document.addEventListener('DOMContentLoaded', function() {
								// Enable Bootstrap dropdowns
								const dropdowns = document.querySelectorAll('.dropdown-toggle');
								dropdowns.forEach(dropdown => {
									new bootstrap.Dropdown(dropdown);
								});
								
								// Fix auto-close functionality
								setupDropdownAutoClose();
							});

							// Enhanced dropdown auto-close functionality
							function setupDropdownAutoClose() {
								// Close dropdowns when clicking outside
								document.addEventListener('click', function(event) {
									const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
									
									openDropdowns.forEach(dropdown => {
										const dropdownContainer = dropdown.closest('.dropdown');
										
										if (!dropdownContainer.contains(event.target)) {
											const dropdownToggle = dropdownContainer.querySelector('.dropdown-toggle');
											dropdown.classList.remove('show');
											if (dropdownToggle) {
												dropdownToggle.setAttribute('aria-expanded', 'false');
											}
										}
									});
								});

								// Close dropdowns on Escape key
								document.addEventListener('keydown', function(event) {
									if (event.key === 'Escape') {
										const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
										
										openDropdowns.forEach(dropdown => {
											const dropdownContainer = dropdown.closest('.dropdown');
											const dropdownToggle = dropdownContainer.querySelector('.dropdown-toggle');
											dropdown.classList.remove('show');
											if (dropdownToggle) {
												dropdownToggle.setAttribute('aria-expanded', 'false');
											}
										});
									}
								});

								// Prevent dropdown from closing when clicking inside dropdown menu
								document.addEventListener('click', function(event) {
									if (event.target.closest('.dropdown-menu')) {
										event.stopPropagation();
									}
								});
							}

							// Parameter builder functionality
							let parameterCount = 0;
							let editParameterCount = 0;

							// Function to format parameter name to lowercase_underscore format
							function formatParameterName(input, preserveTrailingUnderscore = false) {
								let formatted = input
									.toLowerCase()                   // Convert to lowercase
									.replace(/[^a-z0-9\s_-]/g, '')   // Remove special characters except spaces, underscores, and hyphens
									.replace(/\s+/g, '_')            // Replace spaces with underscores
									.replace(/-+/g, '_')             // Replace hyphens with underscores
									.replace(/_+/g, '_')             // Replace multiple consecutive underscores with single underscore
									.replace(/^_+/g, '');            // Remove leading underscores
								
								// Only remove trailing underscores if not preserving them
								if (!preserveTrailingUnderscore) {
									formatted = formatted.replace(/_+$/g, '');
								}
								
								return formatted;
							}

							// Function to handle parameter name input with real-time formatting
							function handleParameterNameInput(element) {
								const cursorPosition = element.selectionStart;
								const originalValue = element.value;
								const lastChar = originalValue.charAt(originalValue.length - 1);
								
								// Check if user just typed a space (which will become underscore)
								const preserveTrailing = lastChar === ' ';
								
								// Format while preserving trailing underscore if user just typed space
								let formattedValue = formatParameterName(originalValue, preserveTrailing);
								
								if (originalValue !== formattedValue) {
									element.value = formattedValue;
									// Try to maintain cursor position
									const newCursorPosition = Math.min(cursorPosition, formattedValue.length);
									element.setSelectionRange(newCursorPosition, newCursorPosition);
								}
								
								// Determine if this is edit mode based on the element's container
								const isEdit = element.closest('#editParametersContainer') !== null;
								generateSchema(isEdit);
							}

							function addParameter(containerId = 'parametersContainer', isEdit = false) {
								const container = document.getElementById(containerId);
								const count = isEdit ? ++editParameterCount : ++parameterCount;
								const prefix = isEdit ? 'edit' : '';

								const parameterHtml = `
									<div class="parameter-row" id="${prefix}parameter_${count}">
										<button type="button" class="btn btn-sm btn-outline-danger btn-remove" onclick="removeParameter('${prefix}parameter_${count}', ${isEdit})">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512"><path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
										</button>
										
										<div class="row w-100">
											<div class="col-md-12">
												<label class="form-label">Parameter Name</label>
												<input type="text" 
													class="form-control parameter-key" 
													placeholder="e.g., user_id, search_term, file_name" 
													oninput="handleParameterNameInput(this)"
													onblur="handleParameterNameInput(this)">
												<small class="form-text text-muted">Lowercase letters, numbers, and underscores only</small>
											</div>
										</div>
									</div>
								`;

								container.insertAdjacentHTML('beforeend', parameterHtml);
								generateSchema(isEdit);
							}

							function addEditParameter() {
								addParameter('editParametersContainer', true);
							}

							function removeParameter(parameterId, isEdit = false) {
								const element = document.getElementById(parameterId);
								if (element) {
									element.remove();
									generateSchema(isEdit);
								}
							}

							function generateSchema(isEdit = false) {
								const containerId = isEdit ? 'editParametersContainer' : 'parametersContainer';
								const outputId = isEdit ? 'editGeneratedInputSchema' : 'generatedInputSchema';
								const container = document.getElementById(containerId);
								
								const parameters = container.querySelectorAll('.parameter-row');
								const schema = {
									type: "object",
									properties: {}
								};
								
								parameters.forEach(param => {
									const key = param.querySelector('.parameter-key').value.trim();
									
									if (key) {
										// Always set type to "string" - numbers will be handled in PHP
										schema.properties[key] = {
											type: "string"
										};
									}
								});
								
								// Only include properties if there are any
								if (Object.keys(schema.properties).length === 0) {
									document.getElementById(outputId).value = '';
								} else {
									document.getElementById(outputId).value = JSON.stringify(schema, null, 2);
								}
							}

							// Function to populate parameters from existing schema (for edit modal)
							function populateParametersFromSchema(schema, isEdit = false) {
								const containerId = isEdit ? 'editParametersContainer' : 'parametersContainer';
								const container = document.getElementById(containerId);
								
								// Clear existing parameters
								container.querySelectorAll('.parameter-row').forEach(row => row.remove());
								
								if (!schema || typeof schema !== 'object' || !schema.properties) {
									return;
								}
								
								const properties = schema.properties;
								
								Object.keys(properties).forEach(key => {
									// Add parameter row
									if (isEdit) {
										addEditParameter();
									} else {
										addParameter();
									}
									
									// Get the last added parameter row
									const rows = container.querySelectorAll('.parameter-row');
									const lastRow = rows[rows.length - 1];
									
									if (lastRow) {
										lastRow.querySelector('.parameter-key').value = key;
									}
								});
								
								generateSchema(isEdit);
							}

							// Update form reset functions to clear parameters
							function resetAddForm() {
								document.getElementById('addToolForm').reset();
								document.getElementById('parametersContainer').querySelectorAll('.parameter-row').forEach(row => row.remove());
								document.getElementById('generatedInputSchema').value = '';
								parameterCount = 0;
								toggleExecutionFields();
							}

							function resetEditForm() {
								document.getElementById('editToolForm').reset();
								document.getElementById('editParametersContainer').querySelectorAll('.parameter-row').forEach(row => row.remove());
								document.getElementById('editGeneratedInputSchema').value = '';
								editParameterCount = 0;
								toggleEditExecutionFields();
							}

							// Client configuration data
							const clientConfigurations = {
								claude_desktop: {
									name: 'Claude Desktop',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon claude-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/claude.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Claude Desktop Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Claude Desktop', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Connect Claude Desktop to your FlowMattic MCP server to enable AI-powered workflow automation.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/78/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="alert alert-info flex-column">
											<strong><?php echo esc_html__( 'Prerequisites', 'flowmattic' ); ?></strong>
											<span>1. <?php echo esc_html__( 'Make sure Node.js is installed on your machine.', 'flowmattic' ); ?> <a href="https://support.flowmattic.com/kb/article/86/installing-nodejs-for-claude-desktop-mcp-usage/" target="_blank"><?php echo esc_html__( 'Installation Guide', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></span>
											2. <?php echo esc_html__( 'Download and install the Claude Desktop application', 'flowmattic' ); ?>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Open Claude Desktop application', 'flowmattic' ); ?></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access Settings', 'flowmattic' ); ?></h5>
													<p><strong><?php echo esc_html__( 'For Mac:', 'flowmattic' ); ?></strong> <?php echo esc_html__( 'From the top menu bar, go to Claude > Settings > Developer, then click on the Edit Config button.', 'flowmattic' ); ?></p>
													<p><strong><?php echo esc_html__( 'For Windows:', 'flowmattic' ); ?></strong> <?php echo esc_html__( 'Click on the breadcrumb menu in the top bar, then navigate to File > Settings > Developer, and click on Edit Config.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add MCP Server Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Open the claude_desktop_config.json file in any text editor and paste the following code into the file:', 'flowmattic' ); ?></p>
													<div class="code-block">
<pre><code>{
"mcpServers": {
	"FlowMattic": {
		"command": "npx",
		"args": [
			"-y",
			"mcp-remote",
			"{{server_url}}"
		]
	}
}}</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Restart Claude Desktop', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Save the configuration file and restart Claude Desktop application to apply the changes.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Test the Connection', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Open a new conversation in Claude Desktop and try using your FlowMattic tools. Claude should now have access to your configured MCP tools.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},
								claude_chat: {
									name: 'Claude Assistant (Chat)',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon claude-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/claude.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Claude Assistant (Chat) Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Claude Assistant', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Connect Claude AI to your FlowMattic MCP server, enabling it to perform real-world tasks through a simple, secure connection without leaving your conversation.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/81/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="alert alert-info flex-column">
											<strong><?php echo esc_html__( 'Note: These steps must be done by a Claude organization owner.', 'flowmattic' ); ?></strong>
											<?php echo esc_html__( 'The added connector will be available to all users in the Claude organization, but only you will be able to see actions you add to this MCP server.', 'flowmattic' ); ?>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Go to', 'flowmattic' ); ?> <a href="https://claude.ai/settings/connectors" target="_blank"><?php echo esc_html__( 'claude.ai/settings/connectors', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Click the "Add custom connector" button', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( '- This will open a form to add a new connector.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Fill out the form with your connector details', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( '- Give the connector a name', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( '- Copy the MCP Server URL from below and paste it into the "Remote MCP server URL" field.', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add Connector', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( '- Click the "Add" button to save your new connector.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( '- Your FlowMattic MCP server is now connected to Claude Assistant!', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Verify Connection', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( '- Click on the three dots next to your newly created connector and select "Tools and Settings".', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( '- You should see your FlowMattic tools listed here. If you see an error, please check the URL and try again.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Test the Connection', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( '- Open a new conversation in Claude Assistant and try using your FlowMattic tools. Claude should now have access to your configured MCP tools.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},
								openai_api: {
									name: 'OpenAI API',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon openai-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/openai.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="OpenAI Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to OpenAI API', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Integrate your FlowMattic MCP server with OpenAI API for programmatic access to your tools. Using OpenAI\'s Responses API can be used to call FlowMattic MCP from anywhere.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/88/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Code Example', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Use the OpenAI Responses API with your FlowMattic MCP server:', 'flowmattic' ); ?></p>
													<div class="code-block">
<pre><code>curl --location 'https://api.openai.com/v1/responses' \\
--header 'Content-Type: application/json' \\
--header 'Authorization: Bearer $OPENAI_API_KEY' \\
--data '{
	"model": "gpt-4.1",
	"tools": [{
		"type": "mcp",
		"server_label": "flowmattic",
		"server_url": "{{server_url}}",
		"require_approval": "never",
		"headers": {
			"Authorization": "Bearer YOUR_TOKEN_HERE"
		}
	}],
	"input": "Run the tool to get my recent posts",
	"tool_choice": "required"
}'</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Python SDK Example', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Install the OpenAI Python SDK and use it in your code:', 'flowmattic' ); ?></p>
													<div class="code-block">
														<pre><code>pip install openai</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
													<div class="code-block">
<pre><code>import openai

client = openai.OpenAI(api_key="your-openai-api-key")

response = client.responses.create(
	model="gpt-4.1",
	tools=[{
		"type": "mcp",
		"server_label": "flowmattic",
		"server_url": "{{server_url}}",
		"require_approval": "never",
		"headers": {
			"Authorization": "Bearer YOUR_TOKEN_HERE"
		}
	}],
	input="Use FlowMattic to get my recent posts",
	tool_choice="required"
)</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>
										</div>
									`,
								},
								openai_playground: {
									name: 'OpenAI API Playground',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon openai-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/openai.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="OpenAI Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to OpenAI Playground', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Test your FlowMattic MCP tools directly in the OpenAI Playground interface.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/80/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access OpenAI Playground', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Open the platform at', 'flowmattic' ); ?> <a href="https://platform.openai.com/chat" target="_blank"><?php echo esc_html__( 'platform.openai.com/chat', 'flowmattic' ); ?></a></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Create a New Prompt', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Create and save a new prompt with a descriptive name (e.g., "FlowMattic MCP Integration"), then publish it to make it active.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add MCP Server Tool', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Locate the tools section, click the (+ Add) button, select \'MCP Server\' from the dropdown menu, and proceed to add it by clicking "Add New" in the popup.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Configure MCP Server Connection', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Enter the FlowMattic MCP Server URL:', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
													<p><?php echo esc_html__( 'Provide a descriptive label and a brief description of the server\'s purpose. Set authentication to \'None\', and click \'Connect\' to establish the connection. Double-check the accuracy of the details provided.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add Available Tools', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Review the list of available tools from the FlowMattic MCP Server and add them to the playground environment.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Test Integration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Verify that the FlowMattic MCP Server tools are available in the OpenAI Playground and test the integration by creating prompts utilizing the FlowMattic tool functionalities.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},
								vscode: {
									name: 'VS Code',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon vscode-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/vscode.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Visual Studio Code Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Visual Studio Code', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Use tools directly inside of Visual Studio Code with FlowMattic MCP. Enable your AI assistant to perform real-world tasks through a simple, secure connection without leaving your coding environment.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/77/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="alert alert-warning flex-column">
											<div class="alert-icon d-flex align-items-center">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: top;">
													<path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.5 7.3 27.7 0 40.2S486.3 480 472 480H40c-14.3 0-27.4-7.8-34.5-20.2s-7.2-27.7 0-40.2l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24v112c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
												</svg>
												<strong><?php echo esc_html__( 'Important:', 'flowmattic' ); ?></strong>
											</div>
											<?php echo esc_html__( 'You must have GitHub Copilot enabled and set to \'Agent\' mode in Visual Studio Code for FlowMattic MCP to work properly.', 'flowmattic' ); ?>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Open the Visual Studio Code command palette', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Use ⌘+⇧+P on Mac, Ctrl+Shift+P on Windows', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Type "MCP: Add Server..." and press Enter', 'flowmattic' ); ?></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Choose "HTTP (HTTP or Server-Sent Events)" and press Enter', 'flowmattic' ); ?></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Paste the MCP Server URL', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Paste the FlowMattic MCP Server URL from below into the "Server URL" field and press Enter.', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512" style="width: 16px; height: 16px;">
																	<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
																</svg>
																<?php echo esc_html__( 'Copy URL', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Give the server a name and press Enter', 'flowmattic' ); ?></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Make sure that GitHub Copilot is set to "Agent" mode', 'flowmattic' ); ?></h5>
												</div>
											</div>

											<div class="step">
												<div class="step-number">7</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Ask GitHub Copilot to use the tools from your server!', 'flowmattic' ); ?></h5>
												</div>
											</div>
										</div>

										<div class="alert alert-warning flex-column">
											<div class="alert-icon d-flex align-items-center">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: top;">
													<path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.5 7.3 27.7 0 40.2S486.3 480 472 480H40c-14.3 0-27.4-7.8-34.5-20.2s-7.2-27.7 0-40.2l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24v112c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
												</svg>
												<strong><?php echo esc_html__( 'Caution:', 'flowmattic' ); ?></strong>
											</div>
											<?php echo esc_html__( 'Treat your MCP server URL like a password! It can be used to run tools attached to this server and access your data.', 'flowmattic' ); ?>
										</div>
									`,
								},
								warp: {
									name: 'Warp',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon warp-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/warp.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Warp Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Warp Terminal', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Use FlowMattic MCP tools directly within Warp terminal for enhanced command-line workflows.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/73/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access Warp Drive', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Locate and click the \'Warp Drive\' option in the top left corner of the Warp interface.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Navigate MCP Server Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Go to the \'Personal\' section and open MCP Server settings.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add New Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Click the \'Add Server\' button to begin the configuration process.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Enter JSON Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Replace the placeholder URL with the actual FlowMattic MCP Server URL in the provided JSON:', 'flowmattic' ); ?></p>
													<div class="code-block">
<pre><code>{
 "flowmattic": {
   "command": "npx",
   "args": [
	 "mcp-remote",
	 "{{server_url}}",
	 "--transport",
	 "http-only"
   ]
 }
}</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Save and Start Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Click \'Save\' to store the settings and then \'Start\' to activate the server.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								windsurf: {
									name: 'Windsurf',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon windsurf-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/windsurf.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Windsurf IDE Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Windsurf IDE', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Integrate FlowMattic MCP tools with Windsurf IDE for enhanced development workflows and AI assistance.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/76/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Open Windsurf Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Navigate to Preferences > Windsurf Settings in the application menu.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access Plugin Management', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Go to the Cascade section and click on Manage Plugins to access the plugin configuration area.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Edit Raw Configuration File', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'In the Manage Plugins view, click \'View Raw Config\'.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Copy and paste the following MCP server configuration file, replacing the placeholder URL with the actual FlowMattic MCP Server URL:', 'flowmattic' ); ?></p>
													<div class="code-block">
<pre><code>{
	"mcpServers": {
		"FlowMattic": {
			"command": "npx",
			"args": [
				"mcp-remote",
				"{{server_url}}"
			]
		}
	}
}</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Save Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Press CMD+S (Mac) or CTRL+S (Windows) to save the configuration file. The FlowMattic MCP server will be added and enabled automatically.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								cursor: {
									name: 'Cursor',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon cursor-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/cursor.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Cursor IDE Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Cursor IDE', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Use FlowMattic MCP tools within Cursor IDE for AI-powered development and workflow automation.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/75/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="cursor-install-config mb-3 pb-3 border-bottom border-gray-300">
											<h5 class="fw-bold fs-5"><?php echo esc_html__( 'Configuring FlowMattic MCP in Cursor', 'flowmattic' ); ?><h5>
											<p><?php echo esc_html__( 'First try and click on the "Add to Cursor" button below to automatically have it configured! If that doesn\'t work or you need to modify the installation follow the directions below.', 'flowmattic' ); ?></p>
											<div class="add-to-cursor-button">
												<button class="btn btn-dark btn-sm" onclick="window.open('cursor://anysphere.cursor-deeplink/mcp/install?name=FlowMattic&config=<?php echo base64_encode( wp_json_encode( array( 'url' => esc_js( $server_data['url'] ) ) ) ); ?>', '_blank')">
													<?php echo esc_html__( 'Add to Cursor', 'flowmattic' ); ?>
												</button>
											</div>
										</div>

										<div class="setup-steps mt-2">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Open Cursor Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Navigate to Preferences > Cursor Settings.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Go to the \'Tools & Integrations\' section and click \'Add Custom MCP\'.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add JSON Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Copy and paste the provided JSON configuration, replacing the placeholder URL with the actual FlowMattic MCP Server URL:', 'flowmattic' ); ?></p>
													<div class="code-block">
<pre><code>{
  "mcpServers": {
	"FlowMattic": {
	  "command": "npx",
	  "args": [
		"mcp-remote",
		"{{server_url}}"
	  ]
	}
  }
}</code></pre>
														<button class="btn btn-sm btn-outline-primary copy-code-btn" onclick="copyCodeBlock(this)">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
																<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
															</svg>
															<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
														</button>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Save Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Press CMD+S (Mac) or CTRL+S (Windows) to save the file. The FlowMattic MCP Server will be automatically enabled.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								vapi: {
									name: 'Vapi',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon vapi-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/vapi.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Vapi Voice AI Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Vapi Voice AI', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Integrate FlowMattic MCP tools with Vapi for voice-activated workflow automation and AI assistance.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/82/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Log in to Vapi', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Visit', 'flowmattic' ); ?> <a href="https://vapi.ai" target="_blank"><?php echo esc_html__( 'https://vapi.ai', 'flowmattic' ); ?></a> <?php echo esc_html__( 'and log in to your Vapi account.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access Tools Section', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Navigate to the left sidebar and click the \'Tools\' option.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Click the \'Create Tool\' button and select \'MCP\' as the tool type from the dropdown menu.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Provide Tool Details', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Enter a Tool Name (e.g., FlowMattic_MCP).', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Add a description (e.g., \'FlowMattic MCP Server executes real-time automated actions, tasks, and workflows via Vapi voice agents\').', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Configure Server Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Paste the FlowMattic MCP Server URL into the \'Server URL\' field:', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<?php echo esc_html__( 'Copy URL', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Set MCP Protocol', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Under \'MCP Settings\', select the protocol as \'Streamable HTTP\'.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Save MCP Tool', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Click the \'Save\' button located at the top-right corner of the interface.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">7</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Update Assistant Tool Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Navigate to the Assistant settings.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Go to the \'Tools\' section within the assistant configuration.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Enable the FlowMattic_MCP tool for active use in conversations.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">8</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Test the MCP Tool', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Test the tool by prompting the Vapi agent with tool-based commands.', 'flowmattic' ); ?></p>
													<p><?php echo esc_html__( 'Ensure the tool is registered and exposes the FlowMattic MCP Server for Vapi to access it.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								elevenlabs: {
									name: 'ElevenLabs',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon elevenlabs-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/elevenlabs.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="ElevenLabs Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to ElevenLabs', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Integrate FlowMattic MCP tools with ElevenLabs for voice synthesis and AI-powered audio workflows.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/74/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access ElevenLabs Studio', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Log into your ElevenLabs account at', 'flowmattic' ); ?> <a href="https://elevenlabs.io/app/sign-in" target="_blank"><?php echo esc_html__( 'elevenlabs.io/app/sign-in', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Access Integration Tab', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Navigate to the Integration tab in ElevenLabs and click \'New Integration\' to start setting up the server.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Set Custom MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Provide a recognizable name, an optional description, and configure the server settings.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Server Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Select \'Streamable HTTP\' as the protocol, set the server type, and enter the MCP Server URL:', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<?php echo esc_html__( 'Copy URL', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Tool Approval Mode', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Set the tool approval mode to \'No Approval\' to avoid manual approvals.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Trust Confirmation', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Accept the trust confirmation prompt to verify the MCP server.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">7</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Finalize Configuration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Click \'Add Server\' to complete the setup. Your ElevenLabs AI agents can now use FlowMattic tools for voice-driven automation and content generation.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								mindpal: {
									name: 'Mindpal',
									instructions: `
										<div class="instructions-header">
											<div class="client-icon mindpal-icon large">
												<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/mindpal.png', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="Mindpal Icon">
											</div>
											<h4><?php echo esc_html__( 'Connect to Mindpal', 'flowmattic' ); ?></h4>
											<p><?php echo esc_html__( 'Integrate FlowMattic MCP tools with Mindpal for AI-powered productivity and workflow management.', 'flowmattic' ); ?></p>
											<p><a href="https://support.flowmattic.com/kb/article/79/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
										</div>

										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Open AI Agent Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Go to the settings page of your AI agent, either by creating a new agent or selecting an existing one.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Navigate to MCP Integration Settings', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Access the \'Integrations\' section within the agent\'s settings and manage remote MCP server connections.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add New Remote MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Click on \'Add New Remote MCP Server\' to open a form for entering server details.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Provide Server Details', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Enter a server name (e.g., "FlowMattic MCP") and the FlowMattic MCP Server URL, which can be found in your FlowMattic account:', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>')">
																<?php echo esc_html__( 'Copy URL', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">5</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Validate MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Test the connection by clicking \'Validate MCP Server\'. Ensure the server URL is valid and reachable for successful integration.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">6</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Complete the setup by clicking \'Add Server\'. The MCP server will then be ready for use with MindPal.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">7</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Test the Integration', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Go to the AI Workforce and test using tool-based commands to ensure proper functionality.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								},

								n8n_assistant: {
									name: 'n8n AI Agent',
									instructions: `<div class="instructions-header">
										<div class="client-icon n8n-icon large">
											<img src="<?php echo esc_url( plugins_url( 'assets/admin/img/n8n.svg', FLOWMATTIC_PLUGIN_FILE ) ); ?>" alt="n8n Icon">
										</div>
										<h4><?php echo esc_html__( 'Connect to n8n AI Agent', 'flowmattic' ); ?></h4>
										<p><?php echo esc_html__( 'Integrate FlowMattic MCP tools with n8n for advanced workflow automation and AI assistance.', 'flowmattic' ); ?></p>
										<p><a href="https://support.flowmattic.com/kb/article/87/" target="_blank"><?php echo esc_html__( 'Learn More', 'flowmattic' ); ?> <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.31909 5H11.3191V10H10.3191V6.70704L5.70711 11.3191L5 10.612L9.61192 6H6.31909V5Z" fill="currentColor"></path></svg></a></p>
									</div>
										<div class="setup-steps">
											<div class="step">
												<div class="step-number">1</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Add AI Agent Node', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'In your n8n workflow, add the AI Agent node to connect with FlowMattic MCP tools.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">2</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Configure MCP Server', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'In the AI Agent tools node, click to add MCP Server node and configure it with your FlowMattic MCP server URL:', 'flowmattic' ); ?></p>
													<div class="url-copy-section">
														<label><?php echo esc_html__( 'MCP Server URL:', 'flowmattic' ); ?></label>
														<div class="input-group">
															<input type="text" class="form-control" value="<?php echo esc_attr( $server_placeholder ); ?>" readonly>
															<button class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo esc_js( $server_data['url'] ); ?>/sse')">
																<?php echo esc_html__( 'Copy URL', 'flowmattic' ); ?>
															</button>
														</div>
													</div>
												</div>
											</div>

											<div class="step">
												<div class="step-number">3</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Configure Tools', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'MCP Server node, n8n will fetch the available tools from your FlowMattic MCP server and display them in the node settings. You can choose to use all or select specific tools for your workflow.', 'flowmattic' ); ?></p>
												</div>
											</div>

											<div class="step">
												<div class="step-number">4</div>
												<div class="step-content">
													<h5><?php echo esc_html__( 'Start Automating Workflows', 'flowmattic' ); ?></h5>
													<p><?php echo esc_html__( 'Your n8n AI Agent can now use FlowMattic tools for advanced workflow automation and AI-driven tasks.', 'flowmattic' ); ?></p>
												</div>
											</div>
										</div>
									`,
								}
							};

							let selectedClient = 'claude_desktop'; // Default client
							let defaultClientDiv = document.querySelector('div[data-client="claude_desktop"]');

							document.addEventListener('DOMContentLoaded', () => {
								// Set default client instructions
								if (defaultClientDiv) {
									selectClient('claude_desktop', defaultClientDiv);
								}
							});

							function toggleClientDropdown() {
								const dropdown = document.getElementById('clientDropdownMenu');
								dropdown.classList.toggle('show');
							}

							function selectClient(clientKey, element) {
								const clientName = clientConfigurations[clientKey]?.name || 'Unknown Client';
								const placeholder = document.querySelector('.client-placeholder');
								const instructionsContainer = document.getElementById('clientInstructions');

								// Update selected display
								placeholder.textContent = clientName;
								placeholder.classList.add('selected');

								// Store selected client
								selectedClient = clientKey;

								// Hide dropdown
								document.getElementById('clientDropdownMenu').classList.remove('show');

								// Show instructions
								if (clientConfigurations[clientKey]) {
									instructionsContainer.innerHTML = clientConfigurations[clientKey].instructions;
									instructionsContainer.style.display = 'block';
								}

								// Update visual selection
								document.querySelectorAll('.client-option').forEach(opt => opt.classList.remove('selected'));
								element.classList.add('selected');
							}

							function copyCodeBlock(button) {
								const codeBlock = button.previousElementSibling;
								let code = codeBlock.textContent;

								// Replace placeholders with actual server URL
								code = code.replace(/{{server_url}}/g, '<?php echo esc_js( $server_data['url'] ); ?>');

								navigator.clipboard.writeText(code).then(() => {
									const originalText = button.innerHTML;
									button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/></svg> <?php echo esc_html__( 'Copied!', 'flowmattic' ); ?>';

									setTimeout(() => {
										button.innerHTML = originalText;
									}, 2000);
								}).catch(() => {
									showNotification('Failed to copy code', 'error');
								});
							}

							function copyToClipboard(text) {
								navigator.clipboard.writeText(text).then(() => {
									showNotification('URL copied to clipboard!', 'success');
								}).catch(() => {
									showNotification('Failed to copy URL', 'error');
								});
							}

							// Close dropdown when clicking outside
							document.addEventListener('click', function(event) {
								const dropdown = document.getElementById('clientDropdownMenu');
								const container = document.querySelector('.client-select-container');

								if (container && !container.contains(event.target)) {
									dropdown.classList.remove('show');
								}
							});
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
function flowmattic_get_method_color( $method ) {
	$colors = array(
		'GET'    => 'success',
		'POST'   => 'primary',
		'PUT'    => 'warning',
		'DELETE' => 'danger',
	);

	return $colors[ $method ] ?? 'secondary';
}
