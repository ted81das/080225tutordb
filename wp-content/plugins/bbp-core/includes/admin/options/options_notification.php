<?php


CSF::createSection(
	$prefix,
	[
		'id'     => 'bbpc_notification_opt',
		'title'  => __( 'Notification', 'bbp-core' ),
		'icon'   => 'dashicons dashicons-megaphone',
		'fields' => [
			[
				'type'    => 'subheading',
				'content' => __( 'Notification', 'bbp-core' ),
			],

			[
				'id'      => 'bbpc_notification',
				'type'    => 'switcher',
				'default' => false,
				'title'   => __( 'Show / Hide', 'bbp-core' ),
                'class'   => 'st-pro-notice'
			],

			[
				'type'       => 'subheading',
				'content'    => __( 'Avatar', 'bbp-core' ),
				'dependency' => [ 'bbpc_notification', '==', true, ],
			],

			[
				'id'         => 'bbpc_notification_location',
				'type'       => 'fieldset',
				'title'      => __( 'Select Location', 'bbp-core' ),
				'subtitle'   => __( 'Select the menu location to display the notification.', 'bbp-core' ),
				'dependency' => [ 'bbpc_notification', '==', true, ],
                'class'      => 'st-pro-notice',
				'inline'	=> true,
				'fields'     => [
					[
						'id' 		 => 'location_option',
						'type'       => 'select',
						'title'		 => __( 'Menu Location', 'bbp-core' ),
						'options' 	 => 'bbpc_get_registered_nav_menus',
						'default'    => 'main_menu',
						'after'		 => __( 'To insert the notification into this location.', 'bbp-core' )						 
					],

					[
						'id' 		 => 'location_selector',
						'type'       => 'text',
						'title'		 => __( '<b>Or</b> Selector', 'bbp-core' ),
						'after'		 => __( 'To insert end of this selector. Ex: <code>.parent_selector</code>', 'bbp-core' )
					]
				]
			],

			[
				'id'               => 'bbpc_notification_user_pos',
				'type'             => 'slider',
				'title'            => __( 'Gap', 'bbp-core' ),
				'dependency'       => [ 'bbpc_notification', '==', true, ],
				'subtitle'         => __( 'Set the gap between the menu and the Notification\'s avatar image. The gap will be applied to Left side of the Avatar image.', 'bbp-core' ),
				'unit'             => 'px',
				'output'           => '.bbpc-nav-notification',
				'output_mode'      => 'margin-left',
				'output_important' => true,
				'max'              => 200,
                'class'            => 'st-pro-notice'
			],
			
			[
				'id'				=> 'bbpc_notification_avatar',
				'type'				=> 'media',
				'title'            	=> __( 'Icon', 'bbp-core' ),
				'dependency'       	=> [ 'bbpc_notification', '==', true, ],
                'class'            	=> 'st-pro-notice',
				'url'				=> false
			],
			
			[
				'id'      			=> 'bbpc_notification_unread_counter',
				'type'    			=> 'switcher',
				'default' 			=> false,
				'title'   			=> __( 'Unread Counter', 'bbp-core' ),
                'class'   			=> 'st-pro-notice',
				'default'  			=> false,
				'dependency' 		=> [ 'bbpc_notification', '==', true, ],
			],

			[
				'type'       	   => 'subheading',
				'content'    	   => __( 'Dropdown notification Box', 'bbp-core' ),
				'dependency' 	   => [ 'bbpc_notification', '==', true, ],
			],

			[
				'id'               => 'bbpc-mini-notification-width',
				'type'             => 'dimensions',
				'title'            => __( 'Width', 'bbp-core'),
				'height'           => false,
				'units'            => array( 'px' ),
				'output'           => '.bbpc-notification-wrap',
				'output_mode'      => 'min-width',
				'output_important' => true,
				'dependency'       => [ 'bbpc_notification', '==', true, ],
                'class'            => 'st-pro-notice'
			],

			[
				'id'               => 'bbpc_notification_data_pos',
				'type'             => 'slider',
				'title'            => __( 'Gap', 'bbp-core' ),
				'subtitle'         => __( 'Set the gap between the Avatar image and the Notification\'s dropdwon box. This option is helpful to adjust the menu height with the Notification\'s dropdwon box', 'bbp-core' ),
				'dependency'       => [ 'bbpc_notification', '==', true, ],
				'unit'             => 'px',
				'output'           => '.bbpc-notification-wrap',
				'output_mode'      => 'top',
				'output_important' => true,
				'min'              => 20,
				'max'              => 200,
                'class'            => 'st-pro-notice'
			],

			[
				'id'               => 'bbpc-mini-notification-border',
				'type'             => 'border',
				'title'            => 'Border',
				'output'           => '.bbpc-notification-wrap',
				'output_mode'      => 'border',
				'output_important' => true,
				'dependency'       => [ 'bbpc_notification', '==', true, ],
                'class'            => 'st-pro-notice'
			],

			[
				'id'               => 'bbpc-mini-notification-border-radius',
				'type'             => 'spacing',
				'title'            => 'Border Radius',
				'output'           => '.bbpc-notification-wrap',
				'output_mode'      => 'border-radius',
				'units'            => array( 'px' ),
				'output_important' => true,
				'dependency'       => [ 'bbpc_notification', '==', true, ],
                'class'            => 'st-pro-notice'
			],

			[
				'type'       => 'subheading',
				'content'    => __( 'Head', 'bbp-core' ),
				'dependency' => [ 'bbpc_notification', '==', true, ],
			],

			[
				'id'      => 'bbpc_notification_head_opt',
				'type'    => 'switcher',
				'default' => false,
				'title'   => __( 'Enable / Disable', 'bbp-core' ),
                'class'   => 'st-pro-notice',
				'dependency' => [ 'bbpc_notification', '==', true, ],
			],

			[
				'id'      => 'bbpc_notification_head_text',
				'type'    => 'text',
				'default' => __( 'Alerts', 'bbp-core' ),
				'title'   => __( 'Heading Text', 'bbp-core' ),
				'dependency' => [ 
					['bbpc_notification', '==', true ], 
					['bbpc_notification_head_opt', '==', true], 
				],
                'class'   => 'st-pro-notice'
			],

			[
				'id'               => 'bbpc_notification_head_text_color',
				'type'             => 'color',
				'title'            => 'Color',
				'output'           => '.bbpc-notification-header',
				'output_mode'      => 'color',
				'output_important' => true,
				'dependency' => [ 
					['bbpc_notification', '==', true ], 
					['bbpc_notification_head_opt', '==', true], 
				],
				'class'            => 'st-pro-notice'
			],

			[
				'id'               => 'bbpc_notification_head_text_bg',
				'type'             => 'color',
				'title'            => 'Background',
				'output'           => '.bbpc-notification-header',
				'output_mode'      => 'Background',
				'output_important' => true,
				'dependency' => [ 
					['bbpc_notification', '==', true ], 
					['bbpc_notification_head_opt', '==', true], 
				],
				'class'            => 'st-pro-notice'
			],

			[
				'id'      => 'bbpc_notification_head_sticky',
				'type'    => 'switcher',
				'default' => false,
				'title'   => __( 'Head Sticky', 'bbp-core' ),
				'dependency' => [ 
					['bbpc_notification', '==', true ], 
					['bbpc_notification_head_opt', '==', true], 
				],
                'class'   => 'st-pro-notice'
			],

			[
				'type'       => 'subheading',
				'content'    => __( 'Color Management', 'bbp-core' ),
				'dependency' => [ 'bbpc_notification', '==', true, ],
			],

			[
				'id'         => 'bbpc-mini-notification-top',
				'type'       => 'fieldset',
				'dependency' => [ 'bbpc_notification', '==', true, ],
				'title'      => __( 'Content', 'bbp-core' ),
				'subtitle'   => __( 'Change the color of the information at the top of the Notification.', 'bbp-core' ),
				'fields'     => array(
					[
						'id'               => 'bbpc-mini-notification-author',
						'type'             => 'link_color',
						'title'            => 'Title',
						'default'          => array(
							'color' => '#000000',
							'hover' => '#000000',
						),
						'output'           => '.bbpc-notification-item:hover h5, .bbpc-notification-item h5',
						'output_mode'      => 'color',
						'output_important' => true,
                        'class'            => 'st-pro-notice'
					],
					
					[
						'id'               => 'bbpc-mini-notification-link',
						'type'             => 'link_color',
						'title'            => 'Links',
						'default'          => array(
							'color'        => '#000000',
							'hover'        => '#000000',
						),
						'output'           => '.bbpc-notification-item:hover h5 a,.bbpc-notification-item h5 a',
						'output_mode'      => 'color',
						'output_important' => true,
                        'class'            => 'st-pro-notice'
					],	

					[
						'id'               => 'bbpc-mini-notification-meta',
						'type'             => 'link_color',
						'title'            => 'Date',
						'output'           => '.bbpc-notification-item span',
						'output_mode'      => 'color',
						'output_important' => true,
                        'class'            => 'st-pro-notice'
					],
					
					[
						'id'               => 'bbpc-mini-notification-btm-bg',
						'type'             => 'color',
						'title'            => 'Background',
						'output'           => '.bbpc-notification-item:not(.bbpc-notify-unread)',
						'output_mode'      => 'background-color',
						'output_important' => true,
                        'class'            => 'st-pro-notice'
					],
					
					[
						'id'               => 'bbpc-mini-notification-link-bg',
						'type'             => 'color',
						'title'            => 'Hover Background',
						'output'           => '.bbpc-notification-item.bbpc-notify-unread, .bbpc-notification-item:hover',
						'output_mode'      => 'background-color',
						'output_important' => true,
                        'class'            => 'st-pro-notice'
					]
				)
			]

		]
	]
);