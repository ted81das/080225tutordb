<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-data-transformer-action-data-template">
	<div class="flowmattic-data-transformer-action-data">
	</div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-html_encode-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-html_decode-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-base64_encode-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-base64_decode-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-sha256_hash-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-sha1_hash-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-sha512_hash-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-hmac_sha256-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Key', 'flowmattic' ); ?></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" name="key" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.key ) { #>{{{ actionAppArgs.key }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your key here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-md5_hash-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="flowmattic-data-transformer-action-strip_tags-data-template">
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
    <div class="form-group w-100">
        <h4 class="fm-input-title"><?php esc_attr_e( 'Allowed Tags', 'flowmattic' ); ?></h4>
        <div class="fm-form-control">
            <div class="fm-dynamic-input-field">
                <textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" name="allowed_tags" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.allowed_tags ) { #>{{{ actionAppArgs.allowed_tags }}}<# } #></textarea>
                <span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
            </div>
            <div class="fm-application-instructions pt-1">
                <p class="description m-t-0"><?php echo esc_html__( 'Enter your allowed tags here.Self-closing XHTML tags are ignored and only non-self-closing tags should be used in allowed_tags. For example, to allow both <br> and <br/>', 'flowmattic' ); ?></p>
            </div>
        </div>
    </div>
</script>
