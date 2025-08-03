<?php
// Select docs page
$args = array(
	'post_type'      => 'topic',
	'posts_per_page' => 1,
	'orderby'        => 'menu_order',
	'order'          => 'desc'
);

$topic_permalink    = '';
foreach ( get_posts( $args ) as $post ) {
	$topic_permalink = get_permalink( $post->ID );
}

$forum_url 	= admin_url('customize.php?url=') . site_url( '/' ) . get_option( '_bbp_root_slug' ) . '?autofocus[panel]=bbp-core-settings&autofocus[section]=forum-archive-page';
$topic_url  = admin_url( 'customize.php?url=' ) . $topic_permalink . '?autofocus[panel]=bbp-core-settings&autofocus[section]=topics_fields';

// Create a section.
CSF::createSection(
	$prefix,
	[
		'title'  => __( 'General', 'bbp-core' ),
		'icon'   => 'dashicons dashicons-admin-generic',
		'fields' => [
			[
				'id'          => 'bbpc_brand_color',
				'type'        => 'color',
				'title'       => esc_html__( 'Frontend Brand Color', 'bbp-core' ),
				'output'      => ':root',
				'output_mode' => '--bbpc_brand_color_opt',
			],

			[
				'id'         => 'customizer_visibility',
				'type'       => 'switcher',
				'title'      => esc_html__( 'Options Visibility on Customizer', 'bbp-core' ),
				'text_on'    => esc_html__( 'Enabled', 'bbp-core' ),
				'text_off'   => esc_html__( 'Disabled', 'bbp-core' ),
				'text_width' => 100
			],

			[
				'type'       => 'content',
				'title'      => esc_html__( 'Customizer Options', 'bbp-core' ),
				'content'    => '<a href="' . esc_url( $forum_url ) . '" target="_blank" id="bbpc_forum_option_link">' . esc_html__( 'Forum', 'bbp-core' )
				                . '</a> ' . '<a href="' . esc_url( $topic_url ) . '" target="_blank" id="bbpc_topic_option_link">' . esc_html__( 'Topic',
						'bbp-core' ) . '</a>',
				'dependency' => [
					[ 'customizer_visibility', '==', true ],
				],
			]
		]
	]
);
