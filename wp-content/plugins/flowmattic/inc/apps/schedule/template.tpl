<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-schedule-data-template">
	<div class="flowmattic-schedule-form-data">
		<div class="form-group w-100 fm-schedule-trigger-week-day">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Day Of The Week', 'flowmattic' ); ?></h4>
			<select id="fm-select-trigger-week-day" class="fm-select-box w-100" name="week-day">
				<#
				var weekDays = {
					'sunday': 'Sunday',
					'monday': 'Monday',
					'tuesday': 'Tuesday',
					'wednesday': 'Wednesday',
					'thursday': 'Thursday',
					'friday': 'Friday',
					'saturday': 'Saturday'
				};

				_.each( weekDays, function( title, value ) {
					#>
					<option <# if ( week_day === value ) { #>selected<# } #> value="{{{value}}}">{{{title}}}</option>
					<#
				} )
				#>
			</select>
		</div>
		<div class="form-group w-100 fm-schedule-trigger-month-day">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Day Of The Month', 'flowmattic' ); ?></h4>
			<select id="fm-select-trigger-month-day" class="fm-select-box w-100" name="month-day">
				<#
				for ( var i = 01; i <= 31; i++ ) {
					#>
					<option <# if ( parseInt( month_day ) === i ) { #>selected<# } #> value="{{{ i }}}">{{{ i }}}</option>
					<#
				}
				#>
			</select>
		</div>
		<div class="form-group w-100 fm-schedule-trigger-day fm-schedule-trigger-month fm-schedule-trigger-week">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Time Of The Day', 'flowmattic' ); ?></h4>
			<input id="fm-checkbox-trigger-day" class="fm-input-box w-100" name="day-time" type="time" value="{{{ day_time }}}" />
		</div>
		<div class="form-group w-100 fm-schedule-trigger-minutes">
			<# var minutes = ( 'undefined' === typeof minutes ) ? '' : minutes; #>
			<h4 class="fm-input-title"><?php esc_attr_e( 'Minutes', 'flowmattic' ); ?></h4>
			<input id="fm-checkbox-trigger-minutes" class="fm-input-box w-100" name="minutes" min="1" max="59" type="number" value="{{{ minutes }}}" />
		</div>
		<div class="form-group w-100 fm-schedule-trigger-hour fm-schedule-trigger-day fm-schedule-trigger-minutes">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Trigger on Weekends?', 'flowmattic' ); ?></h4>
			<input id="fm-checkbox-trigger-hour" class="form-control fm-checkbox" name="weekend_trigger" type="checkbox" value="yes" <# if ( 'undefined' !== typeof weekend_trigger && 'yes' === weekend_trigger ) { #>checked<# } #>
				onchange="jQuery( this ).val( this.checked ? 'yes' : 'no' );"
			/> <label for="fm-checkbox-trigger-hour"><?php esc_attr_e( 'Trigger on Weekends?', 'flowmattic' ); ?></label>
		</div>
		<div class="fm-schedule-save-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-schedule-save-button">
				<?php echo esc_attr__( 'Save & Continue', 'flowmattic' ); ?>
			</a>
		</div>
		<div class="fm-schedule-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
