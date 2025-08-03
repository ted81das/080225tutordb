<?php
 
	// Create a section.
	CSF::createSection(
		$prefix,
		[
			'title'  => __( 'Forum Topics', 'bbp-core' ),
			'id'     => 'topics_fields',
			'icon'   => 'dashicons dashicons-menu-alt3',
			'fields' => [
				[
					'id'       => 'is_solved_topics',
					'type'     => 'switcher',
					'default'  => true,
					'title'    => __( 'Solved Topics', 'bbp-core' ),
					'subtitle' => __( 'Enable/ Disable Solved Topics feature.', 'bbp-core' ),
				],
				[
					'id'       => 'is_auto_approval_topics',
					'type'     => 'switcher',
					'default'  => true,
					'title'    => __( 'Auto Approval', 'bbp-core' ),
					'subtitle' => __( 'Enable/ Disable Auto Approval feature.', 'bbp-core' ),
					'class'   => 'st-pro-notice',
				],
				[
					'id'       	 => 'topic_pending_notice',
					'type'     	 => 'text',
					'title'    	 => __( 'Pending Notice', 'bbp-core' ),
					'default' 	 => __( 'Your topic is awaiting for moderation.', 'bbp-core' ),					
					'dependency' => [ 'is_auto_approval_topics', '==', false ],
					'class'   => 'st-pro-notice',
				],
				[
					'id'       	 => 'anonymous_topic',
					'type'     	 => 'switcher',
					'title'    	 => __( 'Anonymous Topic', 'bbp-core' ),
					'subtitle' 	 => __( 'Allow anonymous to create topics.', 'bbp-core' ),		
					'class'   => 'st-pro-notice',
				],
				[
					'id'       	 => 'anonymous_topic_label',
					'type'     	 => 'text',
					'title'    	 => __( 'Anonymous Label', 'bbp-core' ),
					'default' 	 => __( 'Post Anonymously', 'bbp-core' ),
					'dependency' => [ 'anonymous_topic', '==', 'true', ],
					'class'   => 'st-pro-notice',
				],
				
				[
					'type'		=> 'subheading',
					'title'		=> __( 'Reaction', 'bbp-core' ),
				],

				[
					'id'       	 => 'agree_disagree_voting',
					'type'     	 => 'switcher',
					'title'    	 => __( 'Agree/Disagree', 'bbp-core' ),
					'subtitle'   => __( 'To enable or disable the agree/disagree reactions.', 'bbp-core' ),
					'class'   	 => 'st-pro-notice bbpc-geo-roles-opt',
					'default' 	 => true,
				],
				
				[
					'id'       	 => 'reaction_display_condition',
					'type'     	 => 'select',
					'title'    	 => __( 'Display Condition', 'bbp-core' ),
					'subtitle'   => __( 'Select the condition to display the reaction buttons.', 'bbp-core' ),
					'options'    => [
						'always' 		=> __( 'Always', 'bbp-core' ),
						'has_replies' 	=> __( 'Has Replies', 'bbp-core' ),
					],
					'default' 	 => 'always',
					'dependency' => [ 'agree_disagree_voting', '==', 'true', ],
					'class'   	 => 'st-pro-notice bbpc-geo-roles-opt',
				],

				[
					'id'       	 => 'reaction_display_condition_count',
					'type'     	 => 'text',
					'title'    	 => __( 'Display Condition Count', 'bbp-core' ),
					'subtitle'   => __( 'Number of replies to display the reaction buttons.', 'bbp-core' ),
					'default' 	 => 5,
					'dependency' => [ 
						['reaction_display_condition', '==', 'has_replies'], 
						['agree_disagree_voting', '==', 'true'],
					],
					'class'   	 => 'st-pro-notice bbpc-geo-roles-opt',
				],
				
				[
					'type'		=> 'subheading',
					'title'		=> __( 'Same Topic Voting', 'bbp-core' ),
				],
				[
					'id'       	 => 'same_topic_voting',
					'type'     	 => 'switcher',
					'title'    	 => __( 'Enable / Disable', 'bbp-core' ),	
					'class'   	 => 'st-pro-notice',
				],
				array(
					'id'            => 'same_topic_settings',
					'type'          => 'tabbed',
					'title'     	=> __( 'Settings', 'bbp-core' ),
					'subtitle'  	=> __( 'Customize voting button text and color for single topic.', 'bbp-core' ),
					'dependency' 	=> [ 'same_topic_voting', '==', 'true', ],
					'tabs'          => array(
					  array(
						'title'     => __( 'Button', 'bbp-core' ),
						'fields'    => array(
							[
								'id'       	 => 'same_topic_voting_label',
								'type'     	 => 'text',
								'title'    	 => __( 'Label', 'bbp-core' ),
								'default' 	 => __( 'I have the same question', 'bbp-core' ),
								'class'   	 => 'st-pro-notice'
							],
							[
								'id'               => 'same_topic_color',
								'type'             => 'link_color',
								'title'     => __( 'Color', 'bbp-core' ),
								'default'          => array(
									'color' => '#4a4a4a',
									'hover' => '#4a4a4a',
								),
								'output'           => '.bbpc-same-topic-btn',
								'output_mode'      => 'color',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							],
							[
								'id'				=> 'same_topic_bg_color_normal',
								'type'				=> 'background',
								'title'     		=> __( 'Background', 'bbp-core' ),
								'background_image'	=> false,
								'background_attachment'	=> false,
								'background_position'	=> false,
								'background_repeat'	=> false,
								'background_size'	=> false,
								'default'			=> [
									  'background-color'	=> '#f9f9f9'
								],
								'output'           => '.bbpc-same-topic-btn',
								'output_mode'      => 'background',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							],
							[
								'id'				=> 'same_topic_bg_color_hover',
								'type'				=> 'background',
								'title'     		=> __( 'Hover Background', 'bbp-core' ),
								'background_image'	=> false,
								'background_attachment'	=> false,
								'background_position'	=> false,
								'background_repeat'	=> false,
								'background_size'	=> false,
								'default'			=> [
									  'background-color'	=> '#f9f9f9'
								],
								'output'           => '.bbpc-same-topic-btn:hover',
								'output_mode'      => 'background',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							]
						)
					  ),					  
					  array(
						'title'     => __( 'Success', 'bbp-core' ),
						'fields'    => array(
							[
								'id'       	 => 'same_topic_voting_success',
								'type'     	 => 'text',
								'title'    	 => __( 'Text', 'bbp-core' ),
								'default' 	 => __( 'Updated vote successfully', 'bbp-core' ),
								'class'   	 => 'st-pro-notice'
							],
							[
								'id'               => 'same_topic_success_color',
								'type'             => 'link_color',
								'title'     	   => __( 'Color', 'bbp-core' ),
								'default'          => array(
									'color' => '#4a4a4a',
									'hover' => '#4a4a4a',
								),
								'output'           => '.same-topic-voting-notice',
								'output_mode'      => 'color',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							],
							[
								'id'				=> 'same_topic_bg_success_color',
								'type'				=> 'background',
								'title'     	    => __( 'Background', 'bbp-core' ),
								'background_image'	=> false,
								'background_attachment'	=> false,
								'background_position'	=> false,
								'background_repeat'	=> false,
								'background_size'	=> false,
								'default'			=> [
									  'background-color'	=> '#f9f9f9'
								],
								'output'           => '.same-topic-voting-notice',
								'output_mode'      => 'background',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							],
							[
								'id'				=> 'same_topic_bg_success_color_hover',
								'type'				=> 'background',
								'title'     	    => __( 'Hover Background', 'bbp-core' ),
								'background_image'	=> false,
								'background_attachment'	=> false,
								'background_position'	=> false,
								'background_repeat'	=> false,
								'background_size'	=> false,
								'default'			=> [
									  'background-color'	=> '#f9f9f9'
								],
								'output'           => '.same-topic-voting-notice:hover',
								'output_mode'      => 'background',
								'output_important' => true,
								'class'            => 'st-pro-notice'
							]
						)
					  )
					)
				)			  
			]
		]
	);