<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-counter-action-data-template">
	<div class="counter-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Initial Value', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="initial_value" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.initial_value ) { #>{{{ actionAppArgs.initial_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter initial value from where the counting will begin.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Change Value By', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="change_value" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.change_value ) { #>{{{ actionAppArgs.change_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter change by value like 1.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Reset on Execution', 'flowmattic' ); ?></h4>
			<div class="fm-condition-field">
				<select name="reset_execution" title="Choose option..." class="form-control autonami-trigger-select w-100">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.reset_execution && 'yes' === actionAppArgs.reset_execution ) { #>selected<# } #> value="yes"><?php echo esc_attr__( 'Yes', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.reset_execution && 'no' === actionAppArgs.reset_execution ) { #>selected<# } #> value="no"><?php echo esc_attr__( 'No', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Select Yes, if you want to reset the final value to the initial value on each execution of step, else select No.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group reset-counter-value w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Reset Counter After', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="reset_value" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.reset_value ) { #>{{{ actionAppArgs.reset_value }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter number at which you want to reset counter back to the initial value. This reset option is useful if you want to run something in round-robin fashion.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
