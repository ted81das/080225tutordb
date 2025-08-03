<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-text-formatter-action-data-template">
	<div class="flowmattic-text-formatter-action-data">
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-extract_pattern-data-template">
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
		<h4 class="input-title"><?php echo esc_attr__( 'Pattern', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="regex_pattern" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.regex_pattern ) { #>{{{ actionAppArgs.regex_pattern }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter your regular expression without the enclosed slashes.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Combine Results?', 'flowmattic' ); ?></h4>
		<div class="fm-condition-field">
			<select name="combine_results" title="Choose value..." class="form-control autonami-trigger-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.combine_results && 'yes' === actionAppArgs.combine_results ) { #>selected<# } #> value="yes"><?php echo esc_attr__( 'Yes', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== actionAppArgs.combine_results && 'no' === actionAppArgs.combine_results ) { #>selected<# } #> value="no"><?php echo esc_attr__( 'No', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'If "Yes", all the results will be combined into a single string and included in the "combined_result" parameter.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-find_in_text-data-template">
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
		<h4 class="input-title"><?php echo esc_attr__( 'Search String', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="search_string" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.search_string ) { #>{{{ actionAppArgs.search_string }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the value to be searched. If found, the 'result' parameter returns the position of the string from the left else, returns -1.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-replace_text-data-template">
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
		<h4 class="input-title"><?php echo esc_attr__( 'Search For', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="search_string" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.search_string ) { #>{{{ actionAppArgs.search_string }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the value to be searched for replace.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Replace With', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="replace_string" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.replace_string ) { #>{{{ actionAppArgs.replace_string }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the value to be replaced with.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-format_text-data-template">
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
		<h4 class="input-title"><?php echo esc_attr__( 'Formatting Method', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="method" class="widget-select form-control w-100" title="Select status">
				<?php
				$methods = array(
					'ucwords'           => esc_html__( 'Capitalize', 'flowmattic' ),
					'title_case'        => esc_html__( 'Title Case', 'flowmattic' ),
					'strtoupper'        => esc_html__( 'Upper Case', 'flowmattic' ),
					'strtolower'        => esc_html__( 'Lower Case', 'flowmattic' ),
					'ucfirst'           => esc_html__( 'Sentence Case', 'flowmattic' ),
					'remove_html_tags'  => esc_html__( 'Remove HTML Tags', 'flowmattic' ),
					'extract_number'    => esc_html__( 'Extract Numbers', 'flowmattic' ),
					'extract_email'     => esc_html__( 'Extract Email Address', 'flowmattic' ),
					'extract_phone'     => esc_html__( 'Extract Phone Numbers', 'flowmattic' ),
					'extract_url'       => esc_html__( 'Extract URLs', 'flowmattic' ),
					'get_string_length' => esc_html__( 'Get Length of String', 'flowmattic' ),
					'get_word_count'    => esc_html__( 'Get Word Count', 'flowmattic' ),
					'trim_whitespace'   => esc_html__( 'Trim White Space', 'flowmattic' ),
				);

				foreach ( $methods as $method => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $method; ?>' === actionAppArgs.method ) { #>selected<# } #>
						value="<?php echo $method; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select the formatting method.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-split_text-data-template">
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
		<h4 class="input-title"><?php echo esc_attr__( 'Separator', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="separator" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.separator ) { #>{{{ actionAppArgs.separator }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Character or word separator to split the text on.  For space as a separator, use [space].</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Segment Index', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<select name="segment" class="widget-select form-control w-100" title="Select status">
				<?php
				$segments = array(
					'first'          => esc_html__( 'First', 'flowmattic' ),
					'second'         => esc_html__( 'Second', 'flowmattic' ),
					'last'           => esc_html__( 'Last', 'flowmattic' ),
					'second_to_last' => esc_html__( 'Second to Last', 'flowmattic' ),
					'all'            => esc_html__( 'All', 'flowmattic' ),
				);

				foreach ( $segments as $segment => $title ) {
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $segment; ?>' === actionAppArgs.segment ) { #>selected<# } #>
						value="<?php echo $segment; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Segment of text to return after splitting.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-text_between-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="content" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Start Text', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="start_text" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.start_text ) { #>{{{ actionAppArgs.start_text }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Text to start from.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'End Text', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="end_text" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.end_text ) { #>{{{ actionAppArgs.end_text }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Text to end at.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-default_value-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Field', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="fm-textarea w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="field" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.field ) { #>{{{ actionAppArgs.field }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Field you would like to check if has an empty value.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Default Value', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="default_value" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.default_value ) { #>{{{ actionAppArgs.default_value }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Value to return if the text is empty.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-truncate-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="fm-textarea w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Max Length', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="max_length" autocomplete="off" required type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.max_length ) { #>{{{ actionAppArgs.max_length }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions pt-1">
			<p class="description m-t-0"><?php echo esc_html__( 'The max length the text should be.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Skip Characters', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="skip_characters" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.skip_characters ) { #>{{{ actionAppArgs.skip_characters }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions pt-1">
			<p class="description m-t-0"><?php echo esc_html__( 'Will skip the first N characters in the text.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Append Ellipsis?', 'flowmattic' ); ?></h4>
		<div class="fm-condition-field">
			<select name="append_ellipsis" title="Choose value..." class="form-control autonami-trigger-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.append_ellipsis && 'yes' === actionAppArgs.append_ellipsis ) { #>selected<# } #> value="yes"><?php echo esc_attr__( 'Yes', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.append_ellipsis && 'no' === actionAppArgs.append_ellipsis ) { #>selected<# } #> value="no"><?php echo esc_attr__( 'No', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'If "Yes", three dots ( ... ) will be appended at the end of truncated string.', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Custom Ellipsis Text', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="custom_ellipsis_text" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.custom_ellipsis_text ) { #>{{{ actionAppArgs.custom_ellipsis_text }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions pt-1">
			<p class="description m-t-0"><?php echo esc_html__( 'If you want to replace the three dots ( ... ) Ellipsis, provide custom text here.', 'flowmattic' ); ?></p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-url_encode-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="fm-textarea w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Convert space to plus?', 'flowmattic' ); ?></h4>
		<div class="fm-condition-field">
			<select name="convert_space" title="Choose value..." class="form-control autonami-trigger-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.convert_space && 'yes' === actionAppArgs.convert_space ) { #>selected<# } #> value="yes"><?php echo esc_attr__( 'Yes', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.convert_space && 'no' === actionAppArgs.convert_space ) { #>selected<# } #> value="no"><?php echo esc_attr__( 'No', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Will convert spaces to "+" instead of "%20" and will not convert "/".', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-text-formatter-action-url_decode-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Content', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="fm-textarea w-100 fm-dynamic-inputs form-control dynamic-field-input" required name="content" rows="4"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your content here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Convert plus to spaces?', 'flowmattic' ); ?></h4>
		<div class="fm-condition-field">
			<select name="convert_plus" title="Choose value..." class="form-control autonami-trigger-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.convert_plus && 'yes' === actionAppArgs.convert_plus ) { #>selected<# } #> value="yes"><?php echo esc_attr__( 'Yes', 'flowmattic' ); ?></option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.convert_plus && 'no' === actionAppArgs.convert_plus ) { #>selected<# } #> value="no"><?php echo esc_attr__( 'No', 'flowmattic' ); ?></option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>
				<?php echo esc_attr__( 'Will convert "+" to spaces instead of converting "%20", and will not convert "/".', 'flowmattic' ); ?>
			</p>
		</div>
	</div>
</script>
