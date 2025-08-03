<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-date-time-formatter-action-data-template">
	<div class="date-time-formatter-action-data"></div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-add_subtract_time-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Date', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="input_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.input_date ) { #>{{{ actionAppArgs.input_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Date you would like to manipulate. To use current date, use input as', 'flowmattic' ); ?> <code>[current_date]</code>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Expression', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="expression" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.expression ) { #>{{{ actionAppArgs.expression }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the amount of time you would like to add or subtract to the date (negative values subtract time). Examples: +8 hours 1 minute, +1 month -2 days, -1 day +8 hours.', 'flowmattic' ); ?>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'To Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="to_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.to_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format that the date should be converted to.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'From Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="from_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.from_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'If FlowMattic incorrectly interpret the incoming (input) date, set this to explicitly tell us the format. Otherwise, FlowMattic will figure it out for you.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-modify_current_date-data-template">
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Date Output Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="to_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.to_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format that the date should be converted to.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Time Zone', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="timezone" class="widget-select timezone-dropdown form-control w-100" title="Select time zone" data-live-search="true" required data-value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.timezone ) { #>{{{ actionAppArgs.timezone }}}<# } #>">
				<?php echo wp_timezone_choice( '' ); ?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Select the timezone for current date time.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Operation', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="operation" class="widget-select form-control w-100" title="Select time operation" data-live-search="true">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.operation && 'add' === actionAppArgs.operation ) { #>selected<# } #> value="add"><?php echo esc_attr__( 'Add', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.operation && 'subtract' === actionAppArgs.operation ) { #>selected<# } #> value="subtract"><?php echo esc_attr__( 'Subtract', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Select the operation. How would you like to modify the date.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Time Unit', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="time_unit" class="widget-select form-control w-100" title="Select time unit" data-live-search="true">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.time_unit && 'days' === actionAppArgs.time_unit ) { #>selected<# } #> value="days"><?php echo esc_attr__( 'Days', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.time_unit && 'hours' === actionAppArgs.time_unit ) { #>selected<# } #> value="hours"><?php echo esc_attr__( 'Hours', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.time_unit && 'minutes' === actionAppArgs.time_unit ) { #>selected<# } #> value="minutes"><?php echo esc_attr__( 'Minutes', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Select the time unit.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Time Unit Value', 'flowmattic' ); ?></h4>
		<div class="fm-form-control fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="time_unit_value" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.time_unit_value ) { #>{{{ actionAppArgs.time_unit_value }}}<# } #></textarea>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Enter the time unit value.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-compare_dates-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Start Date', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="start_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.start_date ) { #>{{{ actionAppArgs.start_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter start date. If the start date is after the end date, these dates will be swapped. For current date, use code - ', 'flowmattic' ); ?> <code>[current_date]</code>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter End Date', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="end_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.end_date ) { #>{{{ actionAppArgs.end_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter end date.Timezone is assumed the same for both dates.', 'flowmattic' ); ?>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Start Date Format', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="start_date_format" class="widget-select form-control w-100" title="Select time format">
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.start_date_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format of the start date.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'End Date Format', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="end_date_format" class="widget-select form-control w-100" title="Select time format">
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.end_date_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format of the end date.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-modify_date_format-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Date', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="input_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.input_date ) { #>{{{ actionAppArgs.input_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Date you would like to manipulate. To use current date, use input as', 'flowmattic' ); ?> <code>[current_date]</code>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'From Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="from_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.from_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'If FlowMattic incorrectly interpret the incoming (input) date, set this to explicitly tell us the format. Otherwise, FlowMattic will figure it out for you.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'To Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="to_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.to_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format that the date should be converted to.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-calculate_age-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Date of Birth', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="birth_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.birth_date ) { #>{{{ actionAppArgs.birth_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter date of birth and select the format.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Date Format', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="birth_date_format" class="widget-select form-control w-100" title="Select time format">
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.birth_date_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format of the date of birth.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-date-time-formatter-action-convert_timezone-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Enter Date', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="form-control fm-textarea dynamic-field-input w-100" name="input_date" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.input_date ) { #>{{{ actionAppArgs.input_date }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Date you would like to manipulate. To use current date, use input as', 'flowmattic' ); ?> <code>[current_date]</code>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Input Date Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="from_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.from_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format of the input date.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Date Output Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="to_format" class="widget-select form-control w-100" title="Select time format" required>
				<?php
				$time_formats = flowmattic_get_time_formats();

				foreach ( $time_formats as $format => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $format; ?>' === actionAppArgs.to_format ) { #>selected<# } #>
						value="<?php echo $format; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Provide the format that the date should be converted to.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'To Time Zone', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="timezone" class="widget-select timezone-dropdown form-control w-100" title="Select time zone" data-live-search="true" required data-value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.timezone ) { #>{{{ actionAppArgs.timezone }}}<# } #>">
				<?php echo wp_timezone_choice( '' ); ?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Select the timezone you would like to convert the date to.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'From Time Zone', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="from_timezone" class="widget-select timezone-dropdown form-control w-100" title="Select time zone" data-live-search="true" required data-value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.from_timezone ) { #>{{{ actionAppArgs.from_timezone }}}<# } #>">
				<?php echo wp_timezone_choice( '' ); ?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Select the timezone of the input date.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>