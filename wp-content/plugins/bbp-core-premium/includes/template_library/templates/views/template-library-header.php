<script type="text/template" id="bbpc-liteTemplateLibrary_header-logo">
	<span class="liteTemplateLibrary_logo-wrap">
		<img src="<?php echo BBPC_TEMPLATE_LOGO_SRC ?>" alt=" Template libery Logo" width="25">
	</span>
    <span class="liteTemplateLibrary_logo-title">{{{ title }}}</span>
</script>

<script type="text/template" id="bbpc-liteTemplateLibrary_header-back">
	<i class="eicon-" aria-hidden="true"></i>
	<span><?php echo __( 'Back to Library', 'bbp-core-pro' ); ?></span>
</script>

<script type="text/template" id="bbpc-TemplateLibrary_header-menu">
	<# _.each( tabs, function( args, tab ) { var activeClass = args.active ? 'elementor-active' : ''; #>
		<div class="elementor-component-tab elementor-template-library-menu-item {{activeClass}}" data-tab="{{{ tab }}}">{{{ args.title }}}</div>
	<# } ); #>
</script>

<script type="text/template" id="bbpc-TemplateLibrary_header-menu-responsive">
	
</script>

<script type="text/template" id="bbpc-TemplateLibrary_header-actions">
	<div id="liteTemplateLibrary_header-sync" class="elementor-templates-modal__header__item">
		<i class="eicon-sync" aria-hidden="true" title="<?php esc_attr_e( 'Sync Library', 'bbp-core-pro' ); ?>"></i>
		<span class="elementor-screen-only"><?php esc_html_e( 'Sync Library', 'bbp-core-pro' ); ?></span>
	</div>
</script>