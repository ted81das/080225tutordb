<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-maths-action-data-template">
	<div class="flowmattic-maths-action-data">
		<div class="form-group flowmattic-math-equation w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Math Equation', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<div class="fm-dynamic-input-field">
					<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input" name="math_equation" rows="2"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.math_equation ) { #>{{{ actionAppArgs.math_equation }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Enter your mathematical equation here. You can use the brackets to form advanced equations along with math symbols such as +, -, x and /. You can use the dynamic tags where only number is available in response.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</script>
