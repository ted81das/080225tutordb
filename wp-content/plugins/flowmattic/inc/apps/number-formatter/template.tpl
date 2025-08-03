<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-number-formatter-action-data-template">
	<div class="flowmattic-number-formatter-action-data">
	</div>
</script>
<script type="text/html" id="flowmattic-number-formatter-action-format_number-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Number', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" required name="number" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.number ) { #>{{{ actionAppArgs.number }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the number you want to format. e.g. 1234567890', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Grouping', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-condition-field">
			<select name="grouping" title="Choose value..." class="form-control w-100" required>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.grouping && 'comma' === actionAppArgs.grouping ) { #>selected<# } #> value="comma"><?php echo esc_attr__( 'Comma', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.grouping && 'space' === actionAppArgs.grouping ) { #>selected<# } #> value="space"><?php echo esc_attr__( 'Space', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Choose the grouping you want to use for the number. e.g. with comma - 1,234,567,890 or with space - 1 234 567 890', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-number-formatter-action-format_phone_number-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Phone Number', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" required name="phone_number" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.phone_number ) { #>{{{ actionAppArgs.phone_number }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the phone number you want to format. e.g. 09822012345', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'New Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-condition-field">
			<select name="format" title="Choose value..." class="form-control w-100" required>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.format && 'international' === actionAppArgs.format ) { #>selected<# } #> value="international"><?php echo esc_attr__( 'International ( +91 98220 12345 )', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.format && 'national' === actionAppArgs.format ) { #>selected<# } #> value="national"><?php echo esc_attr__( 'National ( 098220 12345 )', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.format && 'e164' === actionAppArgs.format ) { #>selected<# } #> value="e164"><?php echo esc_attr__( 'E.164 ( +919822012345 )', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Choose the format you want to use for the phone number.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Country Code', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input" required name="country_code" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.country_code ) { #>{{{ actionAppArgs.country_code }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter the ISO 3166-1 alpha-2 country code. e.g. IN for India. US for United States.', 'flowmattic' ); ?><a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank"> Click here</a> to see the list of country codes.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-number-formatter-action-format_currency-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Amount', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" required name="amount" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.amount ) { #>{{{ actionAppArgs.amount }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the amount you want to format. e.g. 1234567890', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Currency', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input" required name="currency" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency ) { #>{{{ actionAppArgs.currency }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter the currency code you want to use. e.g. USD, INR, EUR', 'flowmattic' ); ?> <a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes_(list_one)" target="_blank">Click here</a> to see the list of currency codes.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Currency Format', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-condition-field">
			<select name="currency_format" title="Choose value..." class="form-control w-100" required>
				<option value="¤#,##0.##" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤#,##0.##' === actionAppArgs.currency_format ) { #>selected<# } #>>¤#,##0.##</option>
				<option value="¤#,##0.00" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤#,##0.00' === actionAppArgs.currency_format ) { #>selected<# } #>>¤#,##0.00</option>
				<option value="#,##0.00" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '#,##0.00' === actionAppArgs.currency_format ) { #>selected<# } #>>#,##0.00</option>
				<option value="###0.00" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '###0.00' === actionAppArgs.currency_format ) { #>selected<# } #>>###0.00</option>
				<option value="¤###0.00" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤###0.00' === actionAppArgs.currency_format ) { #>selected<# } #>>¤###0.00</option>
				<option value="¤#,##0.###" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤#,##0.###' === actionAppArgs.currency_format ) { #>selected<# } #>>¤#,##0.###</option>
				<option value="¤###0.#####" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤###0.#####' === actionAppArgs.currency_format ) { #>selected<# } #>>¤###0.#####</option>
				<option value="¤###0.0000#" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤###0.0000#' === actionAppArgs.currency_format ) { #>selected<# } #>>¤###0.0000#</option>
				<option value="¤00000.0000" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '¤00000.0000' === actionAppArgs.currency_format ) { #>selected<# } #>>¤00000.0000</option>
				<option value="#,##0.00¤" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_format && '#,##0.00¤' === actionAppArgs.currency_format ) { #>selected<# } #>>#,##0.00¤</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>
				<ul style="list-style: disc; padding-left: 20px;">
					<li>¤: The currency sign will go here.</li>
					<li>0: Indicates zero padding: if the number is too short, a zero (in the locale's numeric set) will go there.</li>
					<li>#: Indicates no padding: if the number is too short, nothing goes there.</li>
					<li>.: The decimal point will go here.</li>
					<li>,: The thousands separator will go here.</li>
				</ul>
				<a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/NumberFormat" target="_blank">Click here</a> to see the list of currency formats.
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Currency Locale', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-condition-field">
			<select name="currency_locale" title="Choose value..." class="form-control w-100" required>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'en_US' === actionAppArgs.currency_locale ) { #>selected<# } #> value="en_US"><?php echo esc_attr__( 'English (United States)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'en_IN' === actionAppArgs.currency_locale ) { #>selected<# } #> value="en_IN"><?php echo esc_attr__( 'English (India)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'fr_FR' === actionAppArgs.currency_locale ) { #>selected<# } #> value="fr_FR"><?php echo esc_attr__( 'French (France)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'de_DE' === actionAppArgs.currency_locale ) { #>selected<# } #> value="de_DE"><?php echo esc_attr__( 'German (Germany)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'es_ES' === actionAppArgs.currency_locale ) { #>selected<# } #> value="es_ES"><?php echo esc_attr__( 'Spanish (Spain)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'it_IT' === actionAppArgs.currency_locale ) { #>selected<# } #> value="it_IT"><?php echo esc_attr__( 'Italian (Italy)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'ja_JP' === actionAppArgs.currency_locale ) { #>selected<# } #> value="ja_JP"><?php echo esc_attr__( 'Japanese (Japan)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'ko_KR' === actionAppArgs.currency_locale ) { #>selected<# } #> value="ko_KR"><?php echo esc_attr__( 'Korean (Korea)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'pt_PT' === actionAppArgs.currency_locale ) { #>selected<# } #> value="pt_PT"><?php echo esc_attr__( 'Portuguese (Portugal)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'ru_RU' === actionAppArgs.currency_locale ) { #>selected<# } #> value="ru_RU"><?php echo esc_attr__( 'Russian (Russia)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'zh_CN' === actionAppArgs.currency_locale ) { #>selected<# } #> value="zh_CN"><?php echo esc_attr__( 'Chinese (China)', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_locale && 'ar_SA' === actionAppArgs.currency_locale ) { #>selected<# } #> value="ar_SA"><?php echo esc_attr__( 'Arabic (Saudi Arabia)', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter the currency locale you want to use. e.g. en_US, en_IN, fr_FR', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-number-formatter-action-decimal_converter-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Number', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" required name="number" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.number ) { #>{{{ actionAppArgs.number }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the number for decimal conversion. e.g. 1234567890', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Decimal Places', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="search" class="form-control dynamic-field-input" required name="decimal_places" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.decimal_places ) { #>{{{ actionAppArgs.decimal_places }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter the number of decimal places you want to convert. e.g. 2, 3, 4', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-number-formatter-action-minor_unit_conversion-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Amount', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control fm-textarea dynamic-field-input" required name="amount" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.amount ) { #>{{{ actionAppArgs.amount }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the amount you want to convert. e.g. 1234567890', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Currency Code', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="search" class="form-control dynamic-field-input" required name="currency_code" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.currency_code ) { #>{{{ actionAppArgs.currency_code }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_html__( 'Enter the currency code to be used for conversion. e.g. USD, INR, EUR', 'flowmattic' ); ?> <a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes_(list_one)" target="_blank">Click here</a> to see the list of currency codes.</p>
		</div>
	</div>
</script>