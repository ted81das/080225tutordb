<?php
// Create a section.
CSF::createSection(
    $prefix,
    [
        'title'  => __( 'Voting', 'bbp-core' ),
		'icon'   => 'dashicons dashicons-thumbs-up',
        'fields' => [
            [
                'id'      => 'is_votes',
                'type'    => 'switcher',
                'default' => 1,
                'title'   => __( 'Enable BBP Voting Features?', 'bbp-core' ),
            ],

            [
                'type'    => 'subheading',
                'content' => __( 'Voting Labels', 'bbp-core' ),
            ],

            [
                'id'          => 'voting_position',
                'type'        => 'select',
                'title'       => __( 'Voting Option Position', 'bbp-core' ),
                'chosen'      => true,
                'placeholder' => __( 'Select an option', 'bbp-core' ),
                'options'     => [
                    'before_content' => __( 'Before Content', 'bbp-core' ),
                    'below_user'     => __( 'Below User', 'bbp-core' ),
                ],
                'default'     => 'below_user',
            ],

            [
                'id'       => 'is_label',
                'type'     => 'switcher',
                'default'  => false,
                'title'    => __( 'Show Labels', 'bbp-core' ),
                'subtitle' => __( 'Show the labels that describe what up and down mean?', 'bbp-core' ),
            ],

            [
                'id'       => 'upvote_label',
                'type'     => 'text',
                'title'    => __( 'Upvote Label.', 'bbp-core' ),
                'default'  => __( 'Upvote', 'bbp-core' ),
                'subtitle' => __( 'Change the upvote label from "Upvote" to something else.', 'bbp-core' ),
            ],

            [
                'id'       => 'downvote_label',
                'type'     => 'text',
                'title'    => __( 'Downvote Label.', 'bbp-core' ),
                'default'  => __( 'Downvote', 'bbp-core' ),
                'subtitle' => __( 'Change the downvote label from "Downvote" to something else.', 'bbp-core' ),
            ],

            [
                'id'          => 'vote_numbers_display',
                'type'        => 'select',
                'title'       => __( 'Display Vote Numbers', 'bbp-core' ),
                'sub'         => __( 'Choose how to display the number of up votes and down votes', 'bbp-core' ),
                'chosen'      => true,
                'placeholder' => __( 'Select an option', 'bbp-core' ),
                'options'     => [
                    'hover'       => __( 'Hover', 'bbp-core' ),
                    'always-show' => __( 'Always Show', 'bbp-core' ),
                    'hide'        => __( 'Hide', 'bbp-core' ),
                ],
                'default'     => 'hover',
            ],

            [
                'type'    => 'subheading',
                'content' => __( 'Voting Buttons', 'bbp-core' ),
            ],

            [
                'id'       => 'is_voting_disabled_topics',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Voting on Topics', 'bbp-core' ),
                'subtitle' => __( 'You can override this at the forum level', 'bbp-core' ),
            ],

            [
                'id'       => 'is_voting_disabled_replies',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Voting on Replies', 'bbp-core' ),
                'subtitle' => __( 'You can override this at the forum level', 'bbp-core' ),
            ],

            [
                'id'       => 'is_down_votes_disabled',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Down Votes', 'bbp-core' ),
                'subtitle' => __( 'Only Allow Up Votes', 'bbp-core' ),
            ],

            [
                'type'    => 'subheading',
                'content' => __( 'View-Only Scores', 'bbp-core' ),
            ],

            [
                'id'       => 'is_disabled_voting_for_non_logged_users',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Disable voting for visitors who are not logged in', 'bbp-core' ),
                'subtitle' => __( 'Scores will display (if configured to), but voting will be disabled if not logged in', 'bbp-core' ),
                'class'    => 'st-pro-notice',
            ],

            [
                'id'       => 'is_disabled_voting_closed_topics',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Disable adding new votes after a topic is closed', 'bbp-core' ),
                'subtitle' => __( 'Scores will display (if configured to), but new votes for the topic or the topic\'s replies will be disabled', 'bbp-core' ),
                'class'   => 'st-pro-notice',
            ],

            [
                'id'       => 'is_disabled_voting_own_topic_reply',
                'type'     => 'switcher',
                'default'  => 0,
                'title'    => __( 'Don\'t allow authors to vote on their own topic/reply', 'bbp-core' ),
                'subtitle' => __( 'They can still vote on other people\'s topics/replies', 'bbp-core' ),
                'class'   => 'st-pro-notice',
            ],

            [
                'type'    => 'subheading',
                'content' => __( 'Admin Voting', 'bbp-core' ),
            ],

            [
                'id'      => 'is_admin_can_vote_unlimited',
                'type'    => 'switcher',
                'default' => 0,
                'title'   => __( 'Allow any administrator user to vote as much as they want?', 'bbp-core' ),
                'class'   => 'st-pro-notice',
            ],

            //
            [
                'type'    => 'subheading',
                'content' => __( 'Sort by Voting Scores', 'bbp-core' ),
            ],

            [
                'id'       => 'is_sort_topic_by_votes',
                'type'     => 'switcher',
                'default'  => 1,
                'title'    => __( 'Sort topics in a forum using their voting scores?', 'bbp-core' ),
                'subtitle' => __( 'Highest voted topics on top', 'bbp-core' ),
                'class'    => 'st-pro-notice',
            ],

            [
                'id'       => 'is_sort_reply_by_votes',
                'type'     => 'switcher',
                'default'  => 1,
                'title'    => __( 'Sort replies on a topic using their voting scores?', 'bbp-core' ),
                'subtitle' => __( 'Highest voted replies on top', 'bbp-core' ),
                'class'   => 'st-pro-notice',
            ],

            [
                'id'       => 'is_lead_topic_broken',
                'type'     => 'switcher',
                'default'  => 1,
                'title'    => __( ' Break out the lead topic to separate it from the replies', 'bbp-core' ),
                'subtitle' => __( 'Simply enabled the built-in bbPress hook, bbp_show_lead_topic. This is useful to resolve a bug in bbPress when sort order is messed up when Threaded Replies are enabled in bbPress.', 'bbp-core' ),
            ],
        ],
    ]
);
