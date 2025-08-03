<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-delay-data-template">
	<div class="flowmattic-delay-form-data">
		<div class="form-group w-100 fm-delay-trigger-delay-unit">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Delay Unit', 'flowmattic' ); ?></h4>
			<select id="fm-select-trigger-delay-unit" class="fm-select-box form-control w-100" name="delayUnit">
				<#
				var delayUnits = {
					'seconds': 'Seconds',
					'minutes': 'Minutes',
					'hours': 'Hours',
					'days': 'Days',
					'weeks': 'Weeks',
				};

				_.each( delayUnits, function( title, value ) {
					#>
					<option <# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.delayUnit === value ) { #>selected<# } #> value="{{{value}}}">{{{title}}}</option>
					<#
				} )
				#>
			</select>
		</div>
		<div class="form-group w-100 fm-delay-trigger-delay-value">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Delay Value', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" id="fm-select-trigger-delay-value" class="form-control dynamic-field-input w-100" name="delayValue" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.delayValue }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-2">
				<p><?php echo __( 'Value should be integer number only.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-delay-until-template">
	<div class="flowmattic-delay-form-data">
		<div class="form-group w-100 fm-delay-trigger-delay-time">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Delay Date & Time', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" id="fm-select-trigger-delay-time" class="form-control dynamic-field-input w-100" name="delayTime" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.delayTime }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p><?php echo sprintf( __( 'Value should be in (YYYY-MM-DD HH:mm:ss) format like 2022-12-10 14:15:30 and it should be in UTC+0 Time Zone only. To convert any time in a UTC format, <a href="%s" target="_blank">click here</a>.', 'flowmattic' ), 'https://www.utctime.net/utc-time-zone-converter' ); ?></p>
			</div>
		</div>
	</div>
</script>
