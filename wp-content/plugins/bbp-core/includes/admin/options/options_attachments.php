<?php

CSF::createSection(
	$prefix,
	[
		'title'  => __( 'Attachments', 'bbp-core' ),
		'icon'   => 'dashicons dashicons-media-default',
		'fields' => [
			[
				'id'      => 'is_attachment',
				'type'    => 'switcher',
				'default' => true,
				'title'   => __( 'Show attachments on topic replies.', 'bbp-core' ),
			],

			[
				'id'       => 'max_file_size',
				'type'     => 'number',
				'title'    => _x( 'Maximum File Size', 'bbp core maximum file size upload', 'bbp-core' ),
				'subtitle' => __( 'Input the values in Kilo Bytes (KB)', 'bbp-core' ),
				'default'  => 512,
                'unit'     => 'KB',
                'class'   => 'st-pro-notice',
			],

			[
				'id'      => 'max_file_uploads',
				'type'    => 'number',
				'title'   => _x( 'Maximum number of file to upload at once', 'bbp core maximum file size upload', 'bbp-core' ),
				'default' => 4,
				'class'   => 'st-pro-notice',
			],

			[
				'id'       => 'is_hide_attachment',
				'type'     => 'switcher',
				'default'  => true,
				'title'    => __( 'Hide Attachment from visitors', 'bbp-core' ),
				'text_on'  => __( 'Hide', 'bbp-core' ),
				'text_off' => __( 'Show', 'bbp-core' ),
				'text_width' => 85
			],

			[
				'type'    => 'subheading',
				'content' => __( 'Who can upload files ', 'bbp-core' ),
			],

			[
				'id'      => 'users_can_upload',
				'type'    => 'checkbox',
				'title'   => __( 'Users who can upload attachments.', 'bbp-core' ),
				'options' => [
					'keymaster'   => 'Keymaster',
					'moderator'   => 'Moderator',
					'participant' => 'Participant',
					'spectator'   => 'Spectator',
					'blocked'     => 'Blocked',
				],
				'default' => [ 'keymaster', 'moderator', 'participant', 'spectator', 'blocked' ],
			],

			[
				'type'    => 'subheading',
				'content' => __( 'When an associated Topic or Reply has been deleted', 'bbp-core' ),
			],

			[
				'id'      => 'is_attachment_deletion',
				'type'    => 'switcher',
				'default' => false,
				'title'   => __( 'Delete the attachment along with forum/topic.', 'bbp-core' ),
				'class'   => 'st-pro-notice',
			],

			[
				'type'    => 'subheading',
				'content' => __( 'Forums integration', 'bbp-core' ),
			],

			[
				'type'    => 'content',
				'content' => __( 'With these options you can modify the forums to include attachment elements.', 'bbp-core' ),
			],

			[
				'id'      => 'is_attachment_icon',
				'type'    => 'switcher',
				'title'   => __( 'Attachment Icon', 'bbp-core' ),
				'default' => true,
			],

			[
				'id'      => 'is_file_type_icon',
				'type'    => 'switcher',
				'title'   => __( 'File type Icons', 'bbp-core' ),
				'default' => true,
			],

			[
				'type'    => 'subheading',
				'content' => __( 'Display of image attachments', 'bbp-core' ),
			],

			[
				'type'    => 'content',
				'content' => __( 'Attached images can be displayed as thumbnails, and from here you can control this.', 'bbp-core' ),
			],

			[
				'id'      => 'image_thumbnail_caption',
				'type'    => 'switcher',
				'title'   => __( 'With Caption', 'bbp-core' ),
				'default' => true,
			],
			
			[
				'id'      => 'image_link_type',
				'type'    => 'select',
				'title'   => __( 'Image Link Type', 'bbp-core' ),
				'options' => [
					'download'   => __( 'Download', 'bbp-core' ),
					'lightbox'   => __( 'Lightbox', 'bbp-core' ),
				],
				'default' => 'download',
				'class'   => 'st-pro-notice',
				'chosen'  => true
			],
			
			[
				'type'    => 'subheading',
				'content' => __( 'Image thumbnails size', 'bbp-core' ),
			],

			[
				'type'    => 'content',
				'content' => __( 'Changing thumbnails size affects only new image attachments. To use new size for old attachments, resize them using Regenerate Thumbnails plugin.', 'bbp-core' ),
			],

			[
				'id'        => 'attachment_image_x',
				'type'      => 'number',
				'title'     => __( 'Thumbnail width', 'bbp-core' ),
                'unit'      => 'px',
			],

			[
				'id'    => 'attachment_image_y',
				'type'  => 'number',
				'title' => __( 'Thumbnail height', 'bbp-core' ),
                'unit'     => 'px',
			],
		],
	]
);
