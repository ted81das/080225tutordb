<?php
/**
 * Admin page template for MCP Server Execution History.
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
				<?php echo esc_html__( 'License key not registered. Please register your license first to access history.', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}

$license = wp_flowmattic()->check_license();

// Get parameters
$server_id = sanitize_text_field( $_GET['server_id'] ?? '' );
$tool_id   = sanitize_text_field( $_GET['tool_id'] ?? '' );

if ( empty( $server_id ) ) {
	?>
	<div class="mw-100 card border-light mw-100">
		<div class="card-body text-center">
			<div class="alert alert-danger" role="alert">
				<?php echo esc_html__( 'Server ID is required to view execution history.', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}

// Get server data
$server_data = flowmattic_get_mcp_server_data( $server_id );

// Get tool data if tool_id is provided
$tool_data = null;
if ( ! empty( $tool_id ) ) {
	$tool_data = wp_flowmattic()->mcp_server_db->get( array( 'mcp_tool_id' => $tool_id ) );
}

$mcp_server     = FlowMattic_MCP_Server::get_instance();
$plugin_tools   = $mcp_server->get_all_plugin_tools( $server_id );
$external_tools = array();

if ( ! empty( $plugin_tools ) ) {
	foreach ( $plugin_tools as $tool_slug => $tool ) {
		$mcp_tool_id = str_replace( array( ' ', '-' ), '_', strtolower( $tool_slug ) );
		$external_tools[ $mcp_tool_id ] = array(
			'mcp_tool_id'                => $mcp_tool_id,
			'name'                       => $tool['tool']['name'],
			'mcp_tool_name'              => $tool['tool']['name'],
			'description'                => $tool['tool']['description'],
			'mcp_tool_execution_method'  => 'php_function',
			'function_name'              => $tool['tool']['function_name'],
			'source'                     => 'plugin',
			'source_name'                => $tool['plugin'],
		);
	}
}

if ( isset( $external_tools[ $tool_id ] ) ) {
	$tool_data = (object) $external_tools[ $tool_id ];
}

// Initialize executions database
if ( ! isset( wp_flowmattic()->mcp_executions_db ) ) {
	wp_flowmattic()->mcp_executions_db = new FlowMattic_Database_MCP_Tool_Executions();
}
?>

<div class="wrap flowmattic-wrap about-wrap">
	<div class="flowmattic-wrapper d-flex">
		<?php FlowMattic_Admin::header(); ?>
		<div class="flowmattic-container flowmattic-mcp-history-container w-100">
			<div class="flowmattic-dashboard-content container m-0 ps-3">
				<div class="row">
					<div id="flowmattic-mcp-history">
						<div class="wrap mcp-history-wrap">
							<!-- Modern Header Section -->
							<div class="mcp-header-modern">
								<div class="mcp-header-content d-flex justify-content-between align-items-center">
									<div class="mcp-header-left d-flex align-items-center gap-3">
										<div class="mcp-header-icon">
											<div class="mcp-icon-container d-flex align-items-center justify-content-center">
												<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 512 512">
													<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
												</svg>
											</div>
										</div>
										<div class="mcp-header-text">
											<?php if ( $tool_data ) : ?>
												<h1 class="mcp-title fs-4"><?php echo esc_html( $tool_data->mcp_tool_name ); ?> - <?php echo esc_html__( 'Execution History', 'flowmattic' ); ?></h1>
												<p class="mcp-subtitle m-0"><?php echo esc_html__( 'View execution history for this specific tool', 'flowmattic' ); ?></p>
											<?php else : ?>
												<h1 class="mcp-title fs-4"><?php echo esc_html__( 'MCP Server Execution History', 'flowmattic' ); ?></h1>
												<p class="mcp-subtitle m-0"><?php echo esc_html__( 'View execution history for all tools on this server', 'flowmattic' ); ?></p>
											<?php endif; ?>
										</div>
									</div>
									<div class="mcp-header-right d-flex align-items-center gap-2">
										<button type="button" class="btn btn-secondary-light btn-outline-success btn-sm d-flex align-items-center gap-1 me-2" onclick="showExportModal()">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="16" height="16">
												<path d="M288 109.3V352c0 17.7-14.3 32-32 32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352H192c0 35.3 28.7 64 64 64s64-28.7 64-64H448c35.3 0 64 28.7 64 64v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V416c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/>
											</svg>
											<span class="ps-1"><?php echo esc_html__( 'Export', 'flowmattic' ); ?></span>
										</button>
										<button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" onclick="window.location.href='<?php echo admin_url( 'admin.php?page=flowmattic-mcp-server' ); ?>'">
											<svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="currentColor" viewBox="0 0 448 512">
												<path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
											</svg>
											<span class="ps-1"><?php echo esc_html__( 'Back to Server', 'flowmattic' ); ?></span>
										</button>
									</div>
								</div>
							</div>

							<!-- Server Info Card -->
							<div class="mcp-server-card-modern mb-4 mt-4">
								<div class="server-card-header">
									<div class="server-header-content">
										<div class="server-info d-flex align-items-center justify-content-between gap-3">
											<h2 class="server-name fs-5 m-0"><?php echo esc_html( $server_data['name'] ?? 'FlowMattic MCP Server' ); ?></h2>
											<?php if ( $tool_data ) : ?>
												<div class="tool-info">
													<span class="badge bg-primary"><?php echo esc_html( $tool_data->mcp_tool_name ); ?></span>
													<span class="badge bg-secondary"><?php echo esc_html( ucwords( str_replace( '_', ' ', $tool_data->mcp_tool_execution_method ) ) ); ?></span>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>

							<!-- Filters and Search -->
							<div class="history-filters-section mb-4">
								<div class="row">
									<div class="col-md-4">
										<label class="form-label"><?php echo esc_html__( 'Items per page', 'flowmattic' ); ?></label>
										<select class="form-select py-1" id="itemsPerPage" onchange="loadHistory()">
											<option value="10">10</option>
											<option value="25">25</option>
											<option value="50">50</option>
											<option value="100">100</option>
										</select>
									</div>
									<div class="col-md-4">
										<label class="form-label"><?php echo esc_html__( 'Date Range', 'flowmattic' ); ?></label>
										<select class="form-select py-1" id="dateRange" onchange="loadHistory()">
											<option value=""><?php echo esc_html__( 'All Time', 'flowmattic' ); ?></option>
											<option value="today"><?php echo esc_html__( 'Today', 'flowmattic' ); ?></option>
											<option value="week"><?php echo esc_html__( 'This Week', 'flowmattic' ); ?></option>
											<option value="month"><?php echo esc_html__( 'This Month', 'flowmattic' ); ?></option>
										</select>
									</div>
									<div class="col-md-4">
										<label class="form-label"><?php echo esc_html__( 'Refresh History', 'flowmattic' ); ?></label>
										<div>
											<button type="button" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100" onclick="loadHistory()">
												<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="16" height="16" class="me-1">
													<path d="M105.1 202.6c7.7-21.8 20.2-42.3 37.8-59.8c62.5-62.5 163.8-62.5 226.3 0L386.3 160H336c-17.7 0-32 14.3-32 32s14.3 32 32 32H463.5c0 0 0 0 0 0h.4c17.7 0 32-14.3 32-32V64c0-17.7-14.3-32-32-32s-32 14.3-32 32v51.2L414.4 97.6c-87.5-87.5-229.3-87.5-316.8 0C73.2 122 55.6 150.7 44.8 181.4c-5.9 16.7 2.9 34.9 19.5 40.8s34.9-2.9 40.8-19.5zM39 289.3c-5 1.5-9.8 4.2-13.7 8.2c-4 4-6.7 8.8-8.1 14c-.3 1.2-.6 2.5-.8 3.8c-.3 1.7-.4 3.4-.4 5.1V448c0 17.7 14.3 32 32 32s32-14.3 32-32V396.9l17.6 17.5 0 0c87.5 87.4 229.3 87.4 316.7 0c24.4-24.4 42.1-53.1 52.9-83.7c5.9-16.7-2.9-34.9-19.5-40.8s-34.9 2.9-40.8 19.5c-7.7 21.8-20.2 42.3-37.8 59.8c-62.5 62.5-163.8 62.5-226.3 0l-17.6-17.5H176c17.7 0 32-14.3 32-32s-14.3-32-32-32H48.4c-2.2 0-4.2 .9-5.7 2.4c-.2 .2-.4 .4-.6 .6c-.6 .6-1.1 1.3-1.5 2c-.2 .4-.4 .7-.5 1.1c-.2 .5-.3 1-.4 1.5c0 .2 0 .4-.1 .6c0 .2 0 .4-.1 .7c-.1 .5-.1 1-.1 1.5z"/>
												</svg>
												<?php echo esc_html__( 'Refresh', 'flowmattic' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>

							<!-- History Content -->
							<div class="history-content">
								<div id="historyTableContainer">
									<div class="d-flex justify-content-center p-4">
										<div class="spinner-border text-primary" role="status">
											<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'flowmattic' ); ?></span>
										</div>
									</div>
								</div>

								<!-- Pagination Container -->
								<div id="paginationContainer" class="mt-0" style="display: none;">
									<!-- Pagination will be injected here -->
								</div>
							</div>
						</div>

						<!-- Execution Details Modal -->
						<div class="modal fade" id="executionDetailsModal" tabindex="-1" aria-labelledby="executionDetailsModalLabel" aria-hidden="true">
							<div class="modal-dialog modal-xl modal-dialog-centered">
								<div class="modal-content mcp-modal-content">
									<div class="modal-header mcp-modal-header">
										<h5 class="modal-title d-flex align-items-center gap-2 m-0 fs-5" id="executionDetailsModalLabel">
											<span class="modal-icon d-inline-flex">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 512 512">
													<path d="M40 48C26.7 48 16 58.7 16 72v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V72c0-13.3-10.7-24-24-24H40zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM16 232v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V232c0-13.3-10.7-24-24-24H40c-13.3 0-24 10.7-24 24zM40 368c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V392c0-13.3-10.7-24-24-24H40z"/>
												</svg>
											</span>
											<?php echo esc_html__( 'Execution Details', 'flowmattic' ); ?>
										</h5>
										<button type="button" class="btn-close d-flex" data-dismiss="modal" aria-label="Close">
											<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512">
												<path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/>
											</svg>
										</button>
									</div>
									<div class="modal-body" id="executionDetailsContent">
										<!-- Content will be loaded here via AJAX -->
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
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
.mcp-history-wrap {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 24px;
	margin-bottom: 24px;
}

.history-table {
	background: white;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.history-table table {
	margin-bottom: 0;
}

.history-table th {
	background: #f8f9fa;
	border-bottom: 2px solid #dee2e6;
	font-weight: 600;
	color: #495057;
	padding: 16px 12px;
}

.history-table td {
	padding: 16px 12px;
	vertical-align: middle;
	border-bottom: 1px solid #f1f3f4;
}

.history-table tr:last-child td {
	border-bottom: none;
}

.history-table tr:hover {
	background-color: #f8f9fa;
}

.execution-status {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 4px 8px;
	border-radius: 6px;
	font-size: 12px;
	font-weight: 500;
}

.execution-status.status-success {
	background: #d4edda;
	color: #155724;
}

.execution-status.status-error {
	background: #f8d7da;
	color: #721c24;
}

.execution-time {
	font-family: monospace;
	font-size: 11px;
	color: #6c757d;
}

.tool-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 11px;
	font-weight: 500;
	background: #e9ecef;
	color: #495057;
}

.pagination-wrapper {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 16px;
	border-top: 1px solid #dee2e6;
	background: white;
	border-radius: 0 0 8px 8px;
}
#paginationContainer {
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.history-table.paginated {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

.pagination-info {
	color: #6c757d;
	font-size: 14px;
}

.pagination {
	margin: 0;
}

.pagination .page-link {
	color: #495057;
	border-color: #dee2e6;
	padding: 8px 12px;
}

.pagination .page-item {
	margin: 0;
}
.pagination .page-item.active .page-link {
	background-color: #007bff;
	border-color: #007bff;
}

.json-viewer {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: 6px;
	padding: 16px;
	margin: 12px 0;
	position: relative;
}

.json-viewer pre {
	margin: 0;
	white-space: pre-wrap;
	word-wrap: break-word;
	font-size: 13px;
	line-height: 1.4;
	max-height: 400px;
	overflow-y: auto;
}

.copy-json-btn {
	position: absolute;
	top: 8px;
	right: 8px;
	padding: 4px 8px;
	font-size: 12px;
}

.history-filters-section {
	background: white;
	padding: 20px;
	border-radius: 8px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-state {
	text-align: center;
	padding: 60px 20px;
	background: white;
	border-radius: 8px;
}

.empty-state-icon {
	font-size: 48px;
	color: #dee2e6;
	margin-bottom: 16px;
}

.empty-state h4 {
	color: #495057;
	margin-bottom: 8px;
}

.empty-state p {
	color: #6c757d;
	margin-bottom: 0;
}
.pagination .page-link {
    padding: 5px 12px;
    font-size: 14px;
}
.fw-semi-bold {
	font-weight: 600;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Load initial history
	loadHistory();
});

let currentPage = 1;
const serverId = '<?php echo esc_js( $server_id ); ?>';
const toolId = '<?php echo esc_js( $tool_id ); ?>';

function loadHistory(page = 1) {
	currentPage = page;
	const itemsPerPage = document.getElementById('itemsPerPage').value;
	const dateRange = document.getElementById('dateRange').value;

	// Show loading
	document.getElementById('historyTableContainer').innerHTML = `
		<div class="d-flex justify-content-center p-4">
			<div class="spinner-border text-primary" role="status">
				<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'flowmattic' ); ?></span>
			</div>
		</div>
	`;

	// Hide pagination
	document.getElementById('paginationContainer').style.display = 'none';

	// AJAX call to get history
	jQuery.post(ajaxurl, {
		action: 'flowmattic_mcp_get_execution_history',
		workflow_nonce: FMConfig.workflow_nonce,
		server_id: serverId,
		tool_id: toolId,
		page: page,
		per_page: itemsPerPage,
		date_range: dateRange
	}).done(function(response) {
		if (response.success) {
			displayHistory(response.data);
			displayPagination(response.data);
		} else {
			showError(response.data || 'Failed to load execution history');
		}
	}).fail(function() {
		showError('Network error. Please try again.');
	});
}

function displayHistory(data) {
	const { executions, tool_names } = data;
	const container = document.getElementById('historyTableContainer');

	if (!executions || executions.length === 0) {
		container.innerHTML = `
			<div class="empty-state">
				<div class="empty-state-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 512 512">
						<path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9V168c0 13.3 10.7 24 24 24H134.1c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24V256c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65V152c0-13.3-10.7-24-24-24z"/>
					</svg>
				</div>
				<h4><?php echo esc_html__( 'No Execution History', 'flowmattic' ); ?></h4>
				<p><?php echo esc_html__( 'No tool executions have been recorded yet. Executions will appear here once tools are called through the MCP client.', 'flowmattic' ); ?></p>
			</div>
		`;
		return;
	}

	let tableHTML = `
		<div class="history-table">
			<table class="table table-hover mb-0">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Tool', 'flowmattic' ); ?></th>
						<th><?php echo esc_html__( 'Status', 'flowmattic' ); ?></th>
						<th><?php echo esc_html__( 'Executed At', 'flowmattic' ); ?></th>
						<th><?php echo esc_html__( 'Execution Time', 'flowmattic' ); ?></th>
						<th><?php echo esc_html__( 'Client', 'flowmattic' ); ?></th>
						<th><?php echo esc_html__( 'Actions', 'flowmattic' ); ?></th>
					</tr>
				</thead>
				<tbody>
	`;

	executions.forEach(execution => {
		const toolName = tool_names[execution.mcp_tool_id] || execution.mcp_tool_id;
		const isSuccess = !execution.execution_result.error;
		const executionTime = execution.execution_result.execution_time || execution.execution_metadata.execution_time || 0;
		let clientType = execution.execution_metadata.user_agent || 'Unknown';
		const executedAt = new Date(execution.execution_created).toLocaleString();

		// Format client type
		if (clientType.includes('Claude')) {
			clientType = 'Claude';
		} else if (clientType.includes('openai')) {
			clientType = 'OpenAI';
		} else if (clientType.includes('node')) {
			clientType = 'Node MCP Client';
		}

		tableHTML += `
			<tr>
				<td>
					<div class="fw-semi-bold">${escapeHtml(toolName)}</div>
					<small class="text-muted">${execution.mcp_tool_id}</small>
				</td>
				<td>
					<span class="execution-status status-${isSuccess ? 'success' : 'error'}">
						${isSuccess ? 
							'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="12" height="12"><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/></svg> Success' : 
							'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="12" height="12"><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/></svg> Error'
						}
					</span>
				</td>
				<td>
					<div>${executedAt}</div>
				</td>
				<td>
					<span class="execution-time">${parseFloat(executionTime).toFixed(3)}s</span>
				</td>
				<td>
					<span class="tool-badge">${escapeHtml(clientType)}</span>
				</td>
				<td>
					<button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2" onclick="viewExecutionDetails('${execution.mcp_tool_execution_id}')">
						<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512" width="14" height="14">
							<path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-92.7-69.4z"/>
						</svg>
						<?php echo esc_html__( 'View Details', 'flowmattic' ); ?>
					</button>
				</td>
			</tr>
		`;
	});

	tableHTML += `
			</tbody>
		</table>
	</div>
	`;

	container.innerHTML = tableHTML;
}

function displayPagination(data) {
	const { page, total_pages, total, per_page } = data;
	const container = document.getElementById('paginationContainer');

	if (total_pages <= 1) {
		container.style.display = 'none';
		return;
	}

	const startItem = ((page - 1) * per_page) + 1;
	const endItem = Math.min(page * per_page, total);

	let paginationHTML = `
		<div class="pagination-wrapper">
			<div class="pagination-info">
				<?php echo esc_html__( 'Showing', 'flowmattic' ); ?> ${startItem} <?php echo esc_html__( 'to', 'flowmattic' ); ?> ${endItem} <?php echo esc_html__( 'of', 'flowmattic' ); ?> ${total} <?php echo esc_html__( 'items', 'flowmattic' ); ?>
			</div>
			<nav>
				<ul class="pagination">
	`;

	// Previous button
	if (page > 1) {
		paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadHistory(${page - 1}); return false;"><?php echo esc_html__( 'Previous', 'flowmattic' ); ?></a></li>`;
	} else {
		paginationHTML += `<li class="page-item disabled"><span class="page-link"><?php echo esc_html__( 'Previous', 'flowmattic' ); ?></span></li>`;
	}

	// Page numbers
	const startPage = Math.max(1, page - 2);
	const endPage = Math.min(total_pages, page + 2);

	if (startPage > 1) {
		paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadHistory(1); return false;">1</a></li>`;
		if (startPage > 2) {
			paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
		}
	}

	for (let i = startPage; i <= endPage; i++) {
		if (i === page) {
			paginationHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
		} else {
			paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadHistory(${i}); return false;">${i}</a></li>`;
		}
	}

	if (endPage < total_pages) {
		if (endPage < total_pages - 1) {
			paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
		}
		paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadHistory(${total_pages}); return false;">${total_pages}</a></li>`;
	}

	// Next button
	if (page < total_pages) {
		paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadHistory(${page + 1}); return false;"><?php echo esc_html__( 'Next', 'flowmattic' ); ?></a></li>`;
	} else {
		paginationHTML += `<li class="page-item disabled"><span class="page-link"><?php echo esc_html__( 'Next', 'flowmattic' ); ?></span></li>`;
	}

	paginationHTML += `
				</ul>
			</nav>
		</div>
	`;

	container.innerHTML = paginationHTML;
	container.style.display = 'block';

	// Add class to ".history-table" as paginated.
	document.querySelector('.history-table').classList.add('paginated');
}

function viewExecutionDetails(executionId) {
	// Show loading in modal
	const modalContent = document.getElementById('executionDetailsContent');
	modalContent.innerHTML = `
		<div class="d-flex justify-content-center p-4">
			<div class="spinner-border text-primary" role="status">
				<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'flowmattic' ); ?></span>
			</div>
		</div>
	`;

	// Show modal
	const modal = new bootstrap.Modal(document.getElementById('executionDetailsModal'));
	modal.show();

	// Load execution details
	jQuery.post(ajaxurl, {
		action: 'flowmattic_mcp_get_execution_details',
		workflow_nonce: FMConfig.workflow_nonce,
		execution_id: executionId
	}).done(function(response) {
		if (response.success) {
			displayExecutionDetails(response.data);
		} else {
			modalContent.innerHTML = `<div class="alert alert-danger">${response.data || 'Failed to load execution details'}</div>`;
		}
	}).fail(function() {
		modalContent.innerHTML = `<div class="alert alert-danger">Network error. Please try again.</div>`;
	});
}

function displayExecutionDetails(data) {
	const { execution, tool } = data;
	const modalContent = document.getElementById('executionDetailsContent');

	const isSuccess = !execution.execution_result.error;
	const executedAt = new Date(execution.execution_created).toLocaleString();
	let clientType = execution.execution_metadata.user_agent || 'Unknown';

	// Format client type
	if (clientType.includes('Claude')) {
		clientType = 'Claude';
	} else if (clientType.includes('openai')) {
		clientType = 'OpenAI';
	} else if (clientType.includes('node')) {
		clientType = 'Node MCP Client';
	}

	let detailsHTML = `
		<div class="execution-details">
			<div class="row mb-4">
				<div class="col-md-6">
					<h6><?php echo esc_html__( 'Execution Information', 'flowmattic' ); ?></h6>
					<table class="table table-sm table-borderless">
						<tr><td><strong><?php echo esc_html__( 'Tool Name:', 'flowmattic' ); ?></strong></td><td>${escapeHtml(tool ? tool.mcp_tool_name : execution.mcp_tool_id)}</td></tr>
						<tr><td><strong><?php echo esc_html__( 'Execution ID:', 'flowmattic' ); ?></strong></td><td><code>${execution.mcp_tool_execution_id}</code></td></tr>
						<tr><td><strong><?php echo esc_html__( 'Status:', 'flowmattic' ); ?></strong></td><td><span class="execution-status status-${isSuccess ? 'success' : 'error'}">${isSuccess ? 'Success' : 'Error'}</span></td></tr>
						<tr><td><strong><?php echo esc_html__( 'Executed At:', 'flowmattic' ); ?></strong></td><td>${executedAt}</td></tr>
					</table>
				</div>
				<div class="col-md-6">
					<h6><?php echo esc_html__( 'Client Information', 'flowmattic' ); ?></h6>
					<table class="table table-sm table-borderless">
						<tr><td><strong><?php echo esc_html__( 'Client Type:', 'flowmattic' ); ?></strong></td><td>${escapeHtml(clientType || 'Unknown')}</td></tr>
						<tr><td><strong><?php echo esc_html__( 'User Agent:', 'flowmattic' ); ?></strong></td><td><small>${escapeHtml(execution.execution_metadata.user_agent || 'N/A')}</small></td></tr>
						<tr><td><strong><?php echo esc_html__( 'IP Address:', 'flowmattic' ); ?></strong></td><td>${escapeHtml(execution.execution_metadata.ip_address || 'N/A')}</td></tr>
						<tr><td><strong><?php echo esc_html__( 'Execution Time:', 'flowmattic' ); ?></strong></td><td>${parseFloat(execution.execution_metadata.execution_time || 0).toFixed(3)}s</td></tr>
					</table>
				</div>
			</div>

			<h6><?php echo esc_html__( 'Arguments Passed by MCP Client', 'flowmattic' ); ?></h6>
			<div class="json-viewer">
				<button type="button" class="btn btn-sm btn-outline-secondary copy-json-btn" onclick="copyToClipboard('arguments-${execution.mcp_tool_execution_id}')">
					<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512" width="14" height="14">
						<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
					</svg>
					<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
				</button>
				<pre id="arguments-${execution.mcp_tool_execution_id}">${JSON.stringify(execution.execution_arguments, null, 2)}</pre>
			</div>

			<h6><?php echo esc_html__( 'Final Output Sent to Client', 'flowmattic' ); ?></h6>
			<div class="json-viewer">
				<button type="button" class="btn btn-sm btn-outline-secondary copy-json-btn" onclick="copyToClipboard('result-${execution.mcp_tool_execution_id}')">
					<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512" width="14" height="14">
						<path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/>
					</svg>
					<?php echo esc_html__( 'Copy', 'flowmattic' ); ?>
				</button>
				<pre id="result-${execution.mcp_tool_execution_id}">${JSON.stringify(execution.execution_result, null, 2)}</pre>
			</div>
		</div>
	`;

	modalContent.innerHTML = detailsHTML;
}

function copyToClipboard(elementId) {
	const element = document.getElementById(elementId);
	const text = element.textContent;
	
	navigator.clipboard.writeText(text).then(() => {
		// Show temporary success message
		const btn = element.parentNode.querySelector('.copy-json-btn');
		const originalText = btn.innerHTML;
		btn.innerHTML = `
			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="14" height="14">
				<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/>
			</svg>
			<?php echo esc_html__( 'Copied!', 'flowmattic' ); ?>
		`;
		
		setTimeout(() => {
			btn.innerHTML = originalText;
		}, 2000);
	}).catch(() => {
		alert('<?php echo esc_html__( 'Failed to copy to clipboard', 'flowmattic' ); ?>');
	});
}

function showError(message) {
	document.getElementById('historyTableContainer').innerHTML = `
		<div class="alert alert-danger" role="alert">
			<strong><?php echo esc_html__( 'Error:', 'flowmattic' ); ?></strong> ${message}
		</div>
	`;
}

function escapeHtml(text) {
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

// Add export functionality to history page
function showExportModal() {
	const exportModalHTML = `
		<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content mcp-modal-content">
					<div class="modal-header mcp-modal-header">
						<h5 class="modal-title d-flex align-items-center gap-2" id="exportModalLabel">
							<span class="modal-icon d-inline-flex">
								<svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="currentColor" viewBox="0 0 512 512">
									<path d="M288 109.3V352c0 17.7-14.3 32-32 32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352H192c0 35.3 28.7 64 64 64s64-28.7 64-64H448c35.3 0 64 28.7 64 64v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V416c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/>
								</svg>
							</span>
							<?php echo esc_html__( 'Export Execution History', 'flowmattic' ); ?>
						</h5>
						<button type="button" class="btn-close d-flex" data-dismiss="modal" aria-label="Close">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16px" height="16px" viewBox="0 0 384 512">
								<path d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/>
							</svg>
						</button>
					</div>
					<div class="modal-body">
						<form id="exportForm">
							<div class="mb-3">
								<label class="form-label"><?php echo esc_html__( 'Export Format', 'flowmattic' ); ?></label>
								<select class="form-select" name="format" required>
									<option value="csv"><?php echo esc_html__( 'CSV (Comma-separated values)', 'flowmattic' ); ?></option>
									<option value="json"><?php echo esc_html__( 'JSON (JavaScript Object Notation)', 'flowmattic' ); ?></option>
								</select>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="mb-3">
										<label class="form-label"><?php echo esc_html__( 'Start Date', 'flowmattic' ); ?></label>
										<input type="date" class="form-control" name="start_date">
									</div>
								</div>
								<div class="col-md-6">
									<div class="mb-3">
										<label class="form-label"><?php echo esc_html__( 'End Date', 'flowmattic' ); ?></label>
										<input type="date" class="form-control" name="end_date">
									</div>
								</div>
							</div>
							<div class="alert alert-info">
								<small><?php echo esc_html__( 'Export is limited to 10,000 most recent executions. Leave dates empty to export all available data.', 'flowmattic' ); ?></small>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html__( 'Cancel', 'flowmattic' ); ?></button>
						<button type="button" class="btn btn-primary" onclick="executeExport()">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512" width="16" height="16" class="me-1">
								<path d="M288 109.3V352c0 17.7-14.3 32-32 32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352H192c0 35.3 28.7 64 64 64s64-28.7 64-64H448c35.3 0 64 28.7 64 64v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V416c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/>
							</svg>
							<?php echo esc_html__( 'Export', 'flowmattic' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	`;

	// Add modal to page if it doesn't exist
	if (!document.getElementById('exportModal')) {
		document.body.insertAdjacentHTML('beforeend', exportModalHTML);
	}

	// Show modal
	const modal = new bootstrap.Modal(document.getElementById('exportModal'));
	modal.show();
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

function executeExport() {
	const form = document.getElementById('exportForm');
	const formData = new FormData(form);
	
	const exportBtn = event.target;
	const originalText = exportBtn.innerHTML;
	
	// Show loading state
	exportBtn.disabled = true;
	exportBtn.innerHTML = `
		<div class="spinner-border spinner-border-sm me-1" role="status">
			<span class="visually-hidden"><?php echo esc_html__( 'Loading...', 'flowmattic' ); ?></span>
		</div>
		<?php echo esc_html__( 'Exporting...', 'flowmattic' ); ?>
	`;

	// Prepare export data
	const exportData = {
		action: 'flowmattic_mcp_export_history',
		workflow_nonce: FMConfig.workflow_nonce,
		server_id: serverId,
		tool_id: toolId,
		format: formData.get('format'),
		start_date: formData.get('start_date'),
		end_date: formData.get('end_date')
	};

	jQuery.post(ajaxurl, exportData)
		.done(function(response) {
			if (response.success) {
				// Create download link
				const link = document.createElement('a');
				link.href = response.data.download_url;
				link.download = '';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);

				// Reset button
				exportBtn.disabled = false;
				exportBtn.innerHTML = originalText;

				// Close modal
				const exportModal = document.getElementById('exportModal');
				exportModal.querySelector('.btn-close').click();
				
				// Show success message
				showNotification('Export completed successfully!', 'success');
			} else {
				showNotification(response.data || 'Export failed', 'error');
			}
		})
		.fail(function() {
			showNotification('Network error during export', 'error');

			// Reset button
			exportBtn.disabled = false;
			exportBtn.innerHTML = originalText;
		})
		.always(function() {
			// Reset button
			exportBtn.disabled = false;
			exportBtn.innerHTML = originalText;
		});
}
</script>