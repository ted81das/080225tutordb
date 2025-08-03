<?php
	// Create a section.
	CSF::createSection(
		$prefix,
		[
			'title'  => __( 'Topic Replies', 'bbp-core' ),
			'icon'   => 'dashicons dashicons-format-chat',
			'fields' => [
				[
					'id'       => 'is_private_replies',
					'type'     => 'switcher',
					'default'  => 1,
					'title'    => __( 'Private Replies', 'bbp-core' ),
					'subtitle' => __( 'Enable/ Disable Private Replies feature.', 'bbp-core' ),
				],
				[
					'id'       	 => 'anonymous_reply',
					'type'     	 => 'switcher',
					'title'    	 => __( 'Anonymous Reply', 'bbp-core' ),
					'subtitle' 	 => __( 'Allow anonymous to reply a topic.', 'bbp-core' ),			
					'class'   	 => 'st-pro-notice'
				],
				[
					'id'       	 => 'anonymous_reply_label',
					'type'     	 => 'text',
					'title'    	 => __( 'Anonymous Label', 'bbp-core' ),
					'default' 	 => __( 'Post Anonymously', 'bbp-core' ),
					'dependency' => [ 'anonymous_reply', '==', 'true', ],	
					'class'   	 => 'st-pro-notice'
				]
			],
		]
	);