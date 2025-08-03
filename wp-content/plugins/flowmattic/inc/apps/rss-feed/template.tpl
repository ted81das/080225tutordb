<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 4.1.0
 */
?>
<script type="text/html" id="flowmattic-application-rss-feed-data-template">
	<div class="flowmattic-rss-feed-form-data">
		<div class="form-group api-endpoint">
			<h4 class="fm-input-title"><?php esc_attr_e( 'RSS Feed URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<input type="text" name="rss_feed_url" class="form-control fm-api-endpoint" value="{{{ rss_feed_url }}}">
			<div class="fm-application-instructions">
				<p><?php esc_attr_e( 'Paste your publicly accessible RSS URL here.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-api-poll-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Save & Capture response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-rss-feed-action-data-template">
	<div class="flowmattic-rss-feed-action-data">
	</div>
</script>
<script type="text/html" id="flowmattic-rss-feed-action-add_new_feed_item-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'RSS Feed URL Slug', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<#
			var slug = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.rss_feed_slug ) ? actionAppArgs.rss_feed_slug : '';
			#>
			<div class="fm-dynamic-input-field">
				<input type="search" class="w-100 form-control rss-field-slug-input" required name="rss_feed_slug" value="{{{ slug }}}">
			</div>
			<div class="fm-application-instructions pt-1">
				<code><?php echo home_url( '/rss-feed/' ); ?><span class="slug-placeholder">{{{ slug }}}</span></code>
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the URL slug for this RSS feed. Letters, numbers, and hyphens only, no spaces, no special characters.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Feed Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<#
			var feedTitle = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.feed_title ) ? actionAppArgs.feed_title : '';
			#>
			<div class="fm-dynamic-input-field">
				<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-title" required name="feed_title" value="{{{ feedTitle }}}">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the title for this feed.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100 d-none">
		<h4 class="input-title"><?php echo esc_attr__( 'Feed Description', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<#
			var feedDescription = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.feed_description ) ? actionAppArgs.feed_description : '';
			#>
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-description" name="feed_description">{{{ feedDescription }}}</textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the description for this feed.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Feed Link', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<#
			var feedLink = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.feed_link ) ? actionAppArgs.feed_link : '';
			#>
			<div class="fm-dynamic-input-field">
				<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-link" name="feed_link" value="{{{ feedLink }}}">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter the link for this feed. Default is your home page url.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Max Records', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<#
			var maxRecords = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.max_records ) ? actionAppArgs.max_records : '50';
			#>
			<div class="fm-dynamic-input-field">
				<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-link" name="max_records" value="{{{ maxRecords }}}">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( '50 is the max, but this allows you to reduce the number of items returned in the feed.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Remove Old Records on Record Limit?', 'flowmattic' ); ?></h4>
		<div class="d-flex w-100">
			<select name="remove_older_records" class="form-control custom-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.remove_older_records ) { #>selected<# } #> value="no">No</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.remove_older_records ) { #>selected<# } #> value="yes">Yes</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p><?php echo esc_attr__( 'Choose Yes, if you want to remove old records from the database, when feed items cross the max records limit. This is helpful to keep your database clean.', 'flowmattic' ); ?></p>
		</div>
	</div>
	<fieldset class="px-3 border border-1 pb-2 mb-3">
		<legend class="fs-6 text-muted" style="font-weight: 500; width: auto;padding: 0 10px;float: none;">Feed Item</legend>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Item Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<#
				var feedItemTitle = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_title ) ? actionAppArgs.item_title : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-title" required name="item_title" value="{{{ feedItemTitle }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Title of the item to publish.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Item Source URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<#
				var feedItemSource = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_source ) ? actionAppArgs.item_source : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-source" name="item_source" value="{{{ feedItemSource }}}" required>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Provide a source URL for where this item is permanently hosted. Most RSS readers turn the title into a clickable link to this URL.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Item Description', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-form-control">
				<#
				var feedItemDescription = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_description ) ? actionAppArgs.item_description : '';
				#>
				<div class="fm-dynamic-input-field">
					<textarea class="w-100 fm-dynamic-inputs fm-textarea form-control dynamic-field-input rss-feed-item-description" name="item_description" rows="1" required>{{{ feedItemDescription }}}</textarea>
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Provide the plain text or HTML content of the feed item.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Author Name', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemAuthor = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_author ) ? actionAppArgs.item_author : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-author" name="item_author" value="{{{ feedItemAuthor }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Author Email', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemAuthorEmail = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_author_email ) ? actionAppArgs.item_author_email : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-author-email" name="item_author_email" value="{{{ feedItemAuthorEmail }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Item Category', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemCategory = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_category ) ? actionAppArgs.item_category : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-category" name="item_category" value="{{{ feedItemCategory }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Category of the item to publish.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Media URL', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemMediaUrl = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_media_url ) ? actionAppArgs.item_media_url : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-media-url" name="item_media_url" value="{{{ feedItemMediaUrl }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Specify a downloadable URL for your podcast file, image, or other media file.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Media Length in Bytes', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemMediaLength = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_media_length ) ? actionAppArgs.item_media_length : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-media-length" name="item_media_length" value="{{{ feedItemMediaLength }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Some players require a valid length. Defaults to 0.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Media MIME Type', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemMediaMime = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_media_mime ) ? actionAppArgs.item_media_mime : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-media-mime" name="item_media_mime" value="{{{ feedItemMediaMime }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'MIME type of the media file. Defaults to audio/mpeg.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Item Publish Date', 'flowmattic' ); ?></h4>
			<div class="fm-form-control">
				<#
				var feedItemPubDate = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.item_pub_date ) ? actionAppArgs.item_pub_date : '';
				#>
				<div class="fm-dynamic-input-field">
					<input type="search" class="w-100 fm-dynamic-inputs form-control dynamic-field-input rss-feed-item-pub-date" name="item_pub_date" value="{{{ feedItemPubDate }}}">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-1">
					<p class="description m-t-0"><?php echo esc_html__( 'Publish date of the item. Preferred format - ', 'flowmattic' ); ?><code><?php echo date_i18n( 'c' ); ?></code></p>
					<p class="description"><?php echo esc_html__( 'Note: we do not order by pubdate, just by order inserted.', 'flowmattic' ); ?></p>
				</div>
			</div>
		</div>
	</fieldset>
</script>
<script type="text/html" id="flowmattic-rss-feed-action-retrieve_rss_feed-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'RSS Feed URL', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-dynamic-inputs fm-textarea form-control dynamic-field-input" required name="rss_feed_url" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.rss_feed_url ) { #>{{{ actionAppArgs.rss_feed_url }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions pt-1">
				<p class="description m-t-0"><?php echo esc_html__( 'Enter your publicly accessible RSS URL here.', 'flowmattic' ); ?></p>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Response Format', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<select name="response_format" class="widget-select form-control w-100" title="Choose option">
				<?php
				$response_formats = array(
					'simple'          => array(
						'title' => esc_html__( 'Simple', 'flowmattic' ),
						'description' => esc_html__( 'Simple response format to return after extracting individual items.', 'flowmattic' ),
					),
					'item_json'         => array(
						'title' => esc_html__( 'Advanced', 'flowmattic' ),
						'description' => esc_html__( 'Advanced response format includes items in JSON format.', 'flowmattic' ),
					),
				);

				foreach ( $response_formats as $response_format => $options ) {
					$title       = $options['title'];
					$description = $options['description'];
					?>
					<option
						<# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $response_format; ?>' === actionAppArgs.response_format ) { #>selected<# } #>
						value="<?php echo $response_format; ?>" data-subtext="<?php echo $description; ?>">
						<?php echo $title; ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Handles the response data received. Choose <code>Advanced</code> if you want to use the data in Iterator or want to send all the data to external service.</p>
		</div>
	</div>
</script>