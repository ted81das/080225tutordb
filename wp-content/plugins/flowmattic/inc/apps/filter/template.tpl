<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-filter-data-template">
	<div class="flowmattic-filter-form-data">
		<div class="form-group dynamic-inputs flowmattic-filter w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Filter Conditions', 'flowmattic' ); ?></h4>
			<div class="fm-filter-conditions-body data-dynamic-fields" data-option-name="filter_conditions">
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-filter-conditions-template">
	<div class="fm-dynamic-input-wrap fm-filter-conditions p-2 border rounded d-flex w-100">
		<div class="condition-wrap p-1 d-flex gap-3 w-100">
			<div class="condition-block w-100 d-flex flex-column gap-2">
				<div class="fm-dynamic-input-field">
					<input class="fm-dynamic-inputs dynamic-field-input w-100 filter-condition-input" name="filter-field-key" type="search" autocomplete="off" placeholder="Choose field" value="<# if ( 'undefined' !== typeof key ) { #>{{{ key }}}<# } #>"/>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-condition-input-field">
					<select class="fm-filter-condition form-control w-100 filter-condition-input" name="filter-field-condition" title="Choose condition" data-live-search="true">
						<#
						var conditions = {
							'contain_text': 'Contains (Text)',
							'does_not_contain_text': 'Does not contain (Text)',
							'exactly_match': 'Exactly matches (Text)',
							'does_not_exactly_match': 'Does not exactly match (Text)',
							'is_in': 'Is in (Text)',
							'is_not_in': 'Is not in (Text)',
							'starts_with': 'Starts with (Text)',
							'does_not_start_with': 'Does not start with (Text)',
							'ends_with': 'Ends with (Text)',
							'does_not_end_with': 'Does not end with (Text)',
							'greater_than': 'Greater than (Number)',
							'less_than': 'Less than (Number)',
							'equal_to': 'Equal to (Number)',
							'after_date': 'After (Date/time)',
							'before_date': 'Before (Date/time)',
							'equal_date': 'Equals (Date/time)',
							'is_true': 'Is true (Boolean)',
							'is_false': 'Is false (Boolean)',
							'exists': 'Exists',
							'does_not_exists': 'Does not exist',
							'is_empty': 'Is empty',
							'is_not_empty': 'Is not empty',
							'is_blank': 'Is blank',
							'is_not_blank': 'Is not blank',
							'is_email_valid': 'Is Valid Email Format',
							'is_valid_json': 'Is Valid JSON',
						};

						_.each ( conditions, function( conditionTitle, conditionValue ) {
							#>
							<option <# if ( ( 'undefined' !== typeof condition ) && conditionValue === condition ) { #>selected<# } #> value="{{{ conditionValue }}}">{{{ conditionTitle }}}</option>
							<#
						} );
						#>
					</select>
				</div>
				<div class="fm-dynamic-input-field filter-condition-value" <# if ( 'undefined' !== typeof condition && -1 !== jQuery.inArray( condition, ['exists','is_true','is_false','does_not_exists','is_empty', 'is_not_empty', 'is_blank', 'is_not_blank', 'is_email_valid', 'is_valid_json'] ) ) { #>style="opacity: 0;"<# } #>>
					<textarea class="fm-dynamic-inputs dynamic-field-input w-100 filter-condition-input" name="filter-field-value" rows="3" placeholder="Enter or select value"><# if ( 'undefined' !== typeof value ) { #>{{{ decodeURIComponent( value ) }}}<# } #></textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-condition" style="width: 40px;" title="Remove condition">
				<svg width="24" height="24" viewbox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
					<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
					<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
					<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
				</svg>
			</a>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-filter-and-or-template">
<div class="dynamic-input-add-conditions fm-flowmattic-filter-add-more d-flex gap-2 align-items-center">
	<a href="javascript:void(0);" class="btn btn-sm btn-outline-success btn-add-and-condition">
		<svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 4.5C10.5 3.66772 11.1677 3 12 3C12.8323 3 13.5 3.66772 13.5 4.5V19.5C13.5 20.3323 12.8323 21 12 21C11.1677 21 10.5 20.3323 10.5 19.5V4.5Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M3 12C3 11.1677 3.66772 10.5 4.5 10.5H19.5C20.3323 10.5 21 11.1677 21 12C21 12.8323 20.3323 13.5 19.5 13.5H4.5C3.66772 13.5 3 12.8323 3 12Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
		<?php echo esc_attr__( 'AND', 'flowmattic' ); ?>
	</a>
	<a href="javascript:void(0);" class="btn btn-sm btn-outline-success btn-add-or-condition">
		<svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 4.5C10.5 3.66772 11.1677 3 12 3C12.8323 3 13.5 3.66772 13.5 4.5V19.5C13.5 20.3323 12.8323 21 12 21C11.1677 21 10.5 20.3323 10.5 19.5V4.5Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M3 12C3 11.1677 3.66772 10.5 4.5 10.5H19.5C20.3323 10.5 21 11.1677 21 12C21 12.8323 20.3323 13.5 19.5 13.5H4.5C3.66772 13.5 3 12.8323 3 12Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
		<?php echo esc_attr__( 'OR', 'flowmattic' ); ?>
	</a>
</div>
</script>
<script type="text/html" id="flowmattic-filter-and-template">
<div class="dynamic-input-add-conditions fm-flowmattic-filter-add-more">
	<a href="javascript:void(0);" class="btn btn-sm btn-outline-success btn-add-and-condition">
		<svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 4.5C10.5 3.66772 11.1677 3 12 3C12.8323 3 13.5 3.66772 13.5 4.5V19.5C13.5 20.3323 12.8323 21 12 21C11.1677 21 10.5 20.3323 10.5 19.5V4.5Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M3 12C3 11.1677 3.66772 10.5 4.5 10.5H19.5C20.3323 10.5 21 11.1677 21 12C21 12.8323 20.3323 13.5 19.5 13.5H4.5C3.66772 13.5 3 12.8323 3 12Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
		<?php echo esc_attr__( 'AND', 'flowmattic' ); ?>
	</a>
</div>
</script>
