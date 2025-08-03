<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.1
 */
?>
<script type="text/html" id="flowmattic-application-phpfunction-data-template">
	<#
	var time = Date.now();
	#>
	<div class="flowmattic-phpfunction-action-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Function Name', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input class="form-control dynamic-field-input w-100" required name="php_function" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.php_function ) { #>{{{ actionAppArgs.php_function }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Enter the PHP function name. Make sure it is a public function and not inside any class. If the function is in class and static, you can call that function as well. Eg. my_function OR MyClass::my_fuction', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs function-parameter-type w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Function Parameters Type', 'flowmattic' ); ?></h4>
			<div class="fm-param-type-field">
				<select name="parameter_type" class="form-control" title="Choose parameter type" class="w-100">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.parameter_type && 'array' === actionAppArgs.parameter_type ) { #>selected<# } #> value="array"><?php echo esc_attr__( 'Single Array', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.parameter_type && 'variable' === actionAppArgs.parameter_type ) { #>selected<# } #> value="variable"><?php echo esc_attr__( 'Individual Variable', 'flowmattic' ); ?></option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.parameter_type && 'none' === actionAppArgs.parameter_type ) { #>selected<# } #> value="none"><?php echo esc_attr__( 'No Parameters', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Choose how you want to pass the parameters to function. Single array will combine all the parameters into single array and individual variables will pass each parameter as variable with the value set in the value field. ', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs function-parameters w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Function Parameters', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="phpfunction_parameters">
				<#
				if ( 'undefined' !== typeof phpfunction_parameters && ! _.isEmpty( phpfunction_parameters ) ) {
					_.each( phpfunction_parameters, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-parameters">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
								</svg>
							</a>
						</div>
						<#
					} );
				} else {
					#>
					<div class="fm-dynamic-input-wrap fm-api-request-parameters">
						<div class="fm-dynamic-input-field">
							<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="off" name="dynamic-field-value[]" placeholder="value"></textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
						<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
							</svg>
						</a>
					</div>
					<#
				}
				#>
				<div class="dynamic-input-add-more fm-api-parameters-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-sm btn-success btn-add-more-parameters"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
			<div class="fm-application-instructions instructions-array">
				<p>
					<?php echo esc_attr__( 'Function parateters will be passed to the function as a single array as key=value pair. Eg. array( "key" => "value" )', 'flowmattic' ); ?>
				</p>
			</div>
			<div class="fm-application-instructions instructions-variable hidden">
				<p>
					<?php echo esc_attr__( 'Function parateters will be passed to the function as a individual variables. Only the value will be passed as variable to the function. Key is for your identification purpose only.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs function-parameter-value-decode w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'JSON Value Decode', 'flowmattic' ); ?></h4>
			<div class="fm-param-type-field">
			<#
			var parameter_value_decode = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.parameter_value_decode ) ? actionAppArgs.parameter_value_decode : 'default';
			#>
				<select name="parameter_value_decode" class="form-control" title="Choose parameter value decode type" class="w-100">
					<option <# if ( 'default' === parameter_value_decode ) { #>selected<# } #> value="default"><?php echo esc_attr__( 'Pass as it is', 'flowmattic' ); ?></option>
					<option <# if ( 'array' === parameter_value_decode ) { #>selected<# } #> value="array"><?php echo esc_attr__( 'Convert JSON to Array', 'flowmattic' ); ?></option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Choose how you want to pass the parameter value to function, if the value is JSON.', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
	</div>
</script>
