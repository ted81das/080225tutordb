<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.2
 */
?>
<script type="text/html" id="flowmattic-application-router-data-template">
	<div class="flowmattic-routers-data">
		<h4 class="fm-input-title mb-3"><strong><?php esc_attr_e( 'Routers', 'flowmattic' ); ?></strong></h4>
	</div>
	<div class="fm-router-add-button mt-4">
		<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-add-route-button">
			<?php echo esc_attr__( 'Add New Route', 'flowmattic' ); ?>
		</a>
	</div>
</script>
<script type="text/html" id="flowmattic-router-blank-route-data-template">
	<div class="form-group d-flex w-100 flowmattic-router-unit justify-content-between align-items-center mb-2" data-route="{{ routeLetter }}">
		<div class="route-info d-flex">
			<div class="router-icon border border-light bg-secondary text-white p-2 me-2 text-align-center">
				<span class="router-icon-text">{{ routeLetter }}</span>
			</div>
			<div class="router-content">
				<h4 class="router-unit-title m-0">{{ routeTitle }}</h4>
				<span class="router-unit-step-counter description">Contain {{ stepsCount }} steps</span>
			</div>
		</div>
		<div class="router-actions">
			<a href="javascript:void(0);" class="btn btn-md btn-primary me-2 flowmattic-edit-route" data-toggle="tooltip" title="<?php echo esc_attr__( 'Edit route steps', 'flowmattic' ); ?>">
				<span><?php echo esc_attr__( 'Edit', 'flowmattic' ); ?></span>
			</a>
			<a href="javascript:void(0);" class="btn btn-md btn-secondary me-2 flowmattic-rename-route" data-toggle="tooltip" title="<?php echo esc_attr__( 'Rename route title', 'flowmattic' ); ?>">
				<span class="rename-route-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M16.59 8.49998L6.09 19H4V16.91L15.92 4.99998L19.41 8.49998L20.83 7.07998L15.92 2.16998L2 16.09V21H6.91L18 9.90998L16.59 8.49998Z" fill="#fff"></path>
						<path d="M21.4298 19.43C20.7798 19.89 20.3098 20.01 19.9998 20.01C19.5998 20.01 19.4598 19.89 19.0598 18.75C18.7098 17.75 18.2298 16.38 16.6398 16.06C14.1298 15.56 12.1298 17.65 9.00977 21H11.7498C13.5398 19.11 15.0098 17.77 16.2398 18.02C16.6698 18.11 16.8498 18.51 17.1698 19.41C17.5298 20.45 18.0798 22.01 19.9998 22.01C20.9098 22.01 21.8498 21.62 22.8598 20.85L21.4298 19.43Z" fill="#fff"></path>
					</svg>
				</span>
				<span class="hidden"><?php echo esc_attr__( 'Rename', 'flowmattic' ); ?></span>
			</a>
			<a href="javascript:void(0);" data-routeLetter="{{ routeLetter }}" class="btn btn-md btn-success me-2 flowmattic-clone-route" data-toggle="tooltip" title="<?php echo esc_attr__( 'Clone route', 'flowmattic' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
				<span class="hidden"><?php echo esc_attr__( 'Delete', 'flowmattic' ); ?></span>
			</a>
			<a href="javascript:void(0);" class="btn btn-md btn-danger me-2 flowmattic-delete-route" data-toggle="tooltip" title="<?php echo esc_attr__( 'Delete route', 'flowmattic' ); ?>">
				<span class="dashicons dashicons-trash"></span>
				<span class="hidden"><?php echo esc_attr__( 'Delete', 'flowmattic' ); ?></span>
			</a>
		</div>
	</div>
</script>
