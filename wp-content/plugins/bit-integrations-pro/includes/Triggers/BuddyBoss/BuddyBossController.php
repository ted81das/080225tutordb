<?php

namespace BitApps\BTCBI_PRO\Triggers\BuddyBoss;

use BitCode\FI\Flow\Flow;

final class BuddyBossController
{
    private const USER_ACCEPT_FRIEND_REQ = 1;

    private const USER_SEND_FRIEND_REQ = 2;

    private const USER_CREATE_TOPIC = 3;

    private const USER_REPLIED_TOPIC = 4;

    private const USER_SEND_INVITATION = 5;

    private const USER_UPDATE_AVATAR = 6;

    private const USER_UPDATE_PROFILE = 7;

    private const USER_ACTIVATE_ACCOUNT = 8;

    private const USER_JOIN_PUBLIC_GRP_PRO = 9;

    private const USER_JOIN_PRIVATE_GRP_PRO = 10;

    private const USER_REMOVED_FROM_GRP_PRO = 11;

    private const USER_MAKE_A_POST_GRP_PRO = 12;

    private const USER_ACCESS_PRIVATE_GRP_PRO = 13;

    private const NEW_MEMBER_ACTIVATION_PRO = 14;

    private const NEW_MEMBER_REGISTRATION_PRO = 15;

    public static function info()
    {
        $plugin_path = static::pluginActive('get_name');

        return [
            'name'           => 'BuddyBoss',
            'title'          => __('BuddyBoss - most powerful & customizable open-source community platform, built on WordPress', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'buddyboss/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'buddyboss/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive($option = null)
    {
        return class_exists('BuddyPress');
    }

    public function getAll()
    {
        if (!static::pluginActive()) {
            wp_send_json_error(__('BuddyBoss is not installed or activated', 'bit-integrations-pro'));
        }

        $actions = [
            (object) ['id' => static::USER_ACCEPT_FRIEND_REQ, 'title' => __('A user accepts a friend request', 'bit-integrations-pro')],
            (object) ['id' => static::USER_SEND_FRIEND_REQ, 'title' => __('A user sends a friend request', 'bit-integrations-pro')],
            (object) ['id' => static::USER_CREATE_TOPIC, 'title' => __('A user creates a topic in a forum', 'bit-integrations-pro')],
            (object) ['id' => static::USER_REPLIED_TOPIC, 'title' => __('A user replies to a topic in a forum', 'bit-integrations-pro')],
            (object) ['id' => static::USER_SEND_INVITATION, 'title' => __('A user send an email invitation', 'bit-integrations-pro')],
            (object) ['id' => static::USER_UPDATE_AVATAR, 'title' => __('A user updates their avatar', 'bit-integrations-pro')],
            (object) ['id' => static::USER_UPDATE_PROFILE, 'title' => __('A user updates his/her profile', 'bit-integrations-pro')],
            (object) ['id' => static::USER_ACTIVATE_ACCOUNT, 'title' => __('A user account is activated', 'bit-integrations-pro')],
        ];

        if (is_plugin_active('buddyboss-platform-pro/buddyboss-platform-pro.php')) {
            $actions = array_merge($actions, [
                (object) ['id' => static::USER_JOIN_PUBLIC_GRP_PRO, 'title' => __('A user joins in a public group Pro', 'bit-integrations-pro')],
                (object) ['id' => static::USER_JOIN_PRIVATE_GRP_PRO, 'title' => __('A user joins in a private group Pro', 'bit-integrations-pro')],
                (object) ['id' => static::USER_REMOVED_FROM_GRP_PRO, 'title' => __('A user leaves/removed from a group Pro', 'bit-integrations-pro')],
                (object) ['id' => static::USER_MAKE_A_POST_GRP_PRO, 'title' => __('A user makes a post to the ativity stream of a group Pro', 'bit-integrations-pro')],
                (object) ['id' => static::USER_ACCESS_PRIVATE_GRP_PRO, 'title' => __('A user request to access a private group Pro', 'bit-integrations-pro')],
                (object) ['id' => static::NEW_MEMBER_ACTIVATION_PRO, 'title' => __('A user\'s email invitation results in a new member activation Pro', 'bit-integrations-pro')],
                (object) ['id' => static::NEW_MEMBER_REGISTRATION_PRO, 'title' => __('A user\'s email invitation results in a new member registration Pro', 'bit-integrations-pro')],
            ]);
        }

        wp_send_json_success($actions);
    }

    public function get_a_form($data)
    {
        if (!static::pluginActive()) {
            wp_send_json_error(__('BuddyBoss is not installed or activated', 'bit-integrations-pro'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = static::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;

        $id = $data->id;
        if (\in_array($id, [static::USER_CREATE_TOPIC, static::USER_REPLIED_TOPIC])) {
            $forums = static::getAllForums();
            $responseData['forums'] = $forums;
        } elseif (\in_array($id, [static::USER_JOIN_PUBLIC_GRP_PRO, static::USER_ACCESS_PRIVATE_GRP_PRO])) {
            $groups = static::getAllGroups('public');
            $responseData['groups'] = $groups;
        } elseif ($id == static::USER_JOIN_PRIVATE_GRP_PRO) {
            $groups = static::getAllGroups('private');
            $responseData['groups'] = $groups;
        } elseif (\in_array($id, [static::USER_REMOVED_FROM_GRP_PRO, static::USER_MAKE_A_POST_GRP_PRO])) {
            $groups = static::getAllGroups('');
            $responseData['groups'] = $groups;
        }

        wp_send_json_success($responseData);
    }

    public static function getAllForums()
    {
        $forum_args = [
            'post_type'      => bbp_get_forum_post_type(),
            'posts_per_page' => 999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => ['publish', 'private'],
        ];

        $forumList = get_posts($forum_args);

        $forums[] = [
            'forum_id'    => 'any',
            'forum_title' => 'Any Forums',
        ];

        foreach ($forumList as $key => $val) {
            $forums[] = [
                'forum_id'    => $val->ID,
                'forum_title' => $val->post_title,
            ];
        }

        return $forums;
    }

    public static function getAllGroups($status)
    {
        $public_groups = groups_get_groups(
            [
                'status'   => $status,
                'per_page' => -1,
            ]
        );

        if (!empty($public_groups['groups'])) {
            $public_groups = $public_groups['groups'];
        } else {
            $public_groups = [];
        }
        $groups[] = [
            'group_id'    => 'any',
            'group_title' => __('Any Group', 'bit-integrations-pro'),
        ];
        foreach ($public_groups as $k => $group) {
            $groups[] = [
                'group_id'    => $group->id,
                'group_title' => $group->name
            ];
        }

        return $groups;
    }

    public static function fields($id)
    {
        if (empty($id)) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'bit-integrations-pro'
                ),
                400
            );
        }
        if (\in_array($id, [static::USER_ACCEPT_FRIEND_REQ, static::USER_SEND_FRIEND_REQ])) {
            $fields = [
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Nick Name' => (object) [
                    'fieldKey'  => 'nickname',
                    'fieldName' => __('Nick Name', 'bit-integrations-pro'),
                ],
                'Avatar URL' => (object) [
                    'fieldKey'  => 'avatar_url',
                    'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ],
                'Friend ID' => (object) [
                    'fieldKey'  => 'friend_id',
                    'fieldName' => __('Friend ID', 'bit-integrations-pro'),
                ],
                'Friend First Name' => (object) [
                    'fieldKey'  => 'friend_first_name',
                    'fieldName' => __('Friend First Name', 'bit-integrations-pro'),
                ],
                'Friend Last Name' => (object) [
                    'fieldKey'  => 'friend_last_name',
                    'fieldName' => __('Friend Last Name', 'bit-integrations-pro'),
                ],
                'Fiend Nick Name' => (object) [
                    'fieldKey'  => 'friend_nickname',
                    'fieldName' => __('Fiend Nick Name', 'bit-integrations-pro'),
                ],
                'Friend Email' => (object) [
                    'fieldKey'  => 'friend_email',
                    'fieldName' => __('Friend Email', 'bit-integrations-pro'),
                ],
                'Friend Avatar URL' => (object) [
                    'fieldKey'  => 'friend_avatar_url',
                    'fieldName' => __('Friend Avatar URL', 'bit-integrations-pro'),
                ],

            ];
        } elseif (\in_array($id, [static::USER_CREATE_TOPIC, static::USER_REPLIED_TOPIC])) {
            $fields = [
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Nick Name' => (object) [
                    'fieldKey'  => 'nickname',
                    'fieldName' => __('Nick Name', 'bit-integrations-pro'),
                ],
                'Avatar URL' => (object) [
                    'fieldKey'  => 'avatar_url',
                    'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ],
                'Topic Title' => (object) [
                    'fieldKey'  => 'topic_title',
                    'fieldName' => __('Topic Title', 'bit-integrations-pro'),
                ],
                'Topic ID' => (object) [
                    'fieldKey'  => 'topic_id',
                    'fieldName' => __('Topic ID', 'bit-integrations-pro'),
                ],
                'Topic URL' => (object) [
                    'fieldKey'  => 'topic_url',
                    'fieldName' => __('Topic URL', 'bit-integrations-pro'),
                ],
                'Topic Content' => (object) [
                    'fieldKey'  => 'topic_content',
                    'fieldName' => __('Topic Content', 'bit-integrations-pro'),
                ],
                'Forum ID' => (object) [
                    'fieldKey'  => 'forum_id',
                    'fieldName' => __('Forum ID', 'bit-integrations-pro'),
                ],
                'Forum Title' => (object) [
                    'fieldKey'  => 'forum_title',
                    'fieldName' => __('Forum Title', 'bit-integrations-pro'),
                ],
                'Forum URL' => (object) [
                    'fieldKey'  => 'forum_url',
                    'fieldName' => __('Forum URL', 'bit-integrations-pro'),
                ],
            ];

            if ($id == static::USER_REPLIED_TOPIC) {
                $fields['Reply Content'] = (object) [
                    'fieldKey'  => 'reply_content',
                    'fieldName' => __('Reply Content', 'bit-integrations-pro'),
                ];
            }
        } elseif (\in_array($id, [static::USER_JOIN_PUBLIC_GRP_PRO, static::USER_JOIN_PRIVATE_GRP_PRO, static::USER_REMOVED_FROM_GRP_PRO, static::USER_ACCESS_PRIVATE_GRP_PRO])) {
            $fields = [
                'Group Title' => (object) [
                    'fieldKey'  => 'group_title',
                    'fieldName' => __('Group Title', 'bit-integrations-pro'),
                ],
                'Group ID' => (object) [
                    'fieldKey'  => 'group_id',
                    'fieldName' => __('Group ID', 'bit-integrations-pro'),
                ],
                'Group Description' => (object) [
                    'fieldKey'  => 'group_desc',
                    'fieldName' => __('Group Description', 'bit-integrations-pro'),
                ],
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Nick Name' => (object) [
                    'fieldKey'  => 'nickname',
                    'fieldName' => __('Nick Name', 'bit-integrations-pro'),
                ],
                'Avatar URL' => (object) [
                    'fieldKey'  => 'avatar_url',
                    'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ]
            ];
            if ($id == static::USER_ACCESS_PRIVATE_GRP_PRO) {
                $fields['User Profile URL'] = (object) [
                    'fieldKey'  => 'user_profile_url',
                    'fieldName' => __('User Profile URL', 'bit-integrations-pro'),
                ];

                $fields['Manage Group Request URL'] = (object) [
                    'fieldKey'  => 'manage_group_request_url',
                    'fieldName' => __('Manage Group Request URL', 'bit-integrations-pro'),
                ];
            }
        } elseif ($id == static::USER_MAKE_A_POST_GRP_PRO) {
            $fields = [
                'Group Title' => (object) [
                    'fieldKey'  => 'group_title',
                    'fieldName' => __('Group Title', 'bit-integrations-pro'),
                ],
                'Group ID' => (object) [
                    'fieldKey'  => 'group_id',
                    'fieldName' => __('Group ID', 'bit-integrations-pro'),
                ],
                'Group Description' => (object) [
                    'fieldKey'  => 'group_desc',
                    'fieldName' => __('Group Description', 'bit-integrations-pro'),
                ],
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Nick Name' => (object) [
                    'fieldKey'  => 'nickname',
                    'fieldName' => __('Nick Name', 'bit-integrations-pro'),
                ],
                'Avatar URL' => (object) [
                    'fieldKey'  => 'avatar_url',
                    'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ],
                'Activity ID' => (object) [
                    'fieldKey'  => 'activity_id',
                    'fieldName' => __('Activity ID', 'bit-integrations-pro'),
                ],
                'Activity URL' => (object) [
                    'fieldKey'  => 'activity_url',
                    'fieldName' => __('Activity URL', 'bit-integrations-pro'),
                ],
                'Activity Content' => (object) [
                    'fieldKey'  => 'activity_content',
                    'fieldName' => __('Activity Content', 'bit-integrations-pro'),
                ],
                'Activity Stream URL' => (object) [
                    'fieldKey'  => 'activity_stream_url',
                    'fieldName' => __('Activity Stream URL', 'bit-integrations-pro'),
                ],

            ];
        } else {
            $fields = [
                'Id' => (object) [
                    'fieldKey'  => 'id',
                    'fieldName' => __('Id', 'bit-integrations-pro'),
                ],
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Nick Name' => (object) [
                    'fieldKey'  => 'nickname',
                    'fieldName' => __('Nick Name', 'bit-integrations-pro'),
                ],
                'Avatar URL' => (object) [
                    'fieldKey'  => 'avatar_url',
                    'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ],
            ];

            $buddyBossProfileFields = BuddyBossHelper::getBuddyBossProfileField();
            foreach ($buddyBossProfileFields as $key => $val) {
                if (\in_array($val->name, ['First Name', 'Last Name', 'Nickname'])) {
                    continue;
                }

                $fieldKey = "field_{$val->id}";
                $fields[$fieldKey] = (object) [
                    'fieldKey'  => $fieldKey,
                    'fieldName' => $val->name,
                ];
            }
        }

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function getUserInfo($user_id, $extra = false)
    {
        $userInfo = get_userdata($user_id);
        $user = [];

        if ($userInfo) {
            $userData = $userInfo->data;
            $user_meta = get_user_meta($user_id);
            $user = [
                'id'         => $user_id,
                'first_name' => $user_meta['first_name'][0],
                'last_name'  => $user_meta['last_name'][0],
                'user_email' => $userData->user_email,
                'nickname'   => $userData->user_nicename,
                'avatar_url' => get_avatar_url($user_id),
            ];
        }

        if ($extra == static::USER_ACCESS_PRIVATE_GRP_PRO) {
            $user['user_profile_url'] = maybe_serialize(bbp_get_user_profile_url($user_id));
        }

        return $user;
    }

    public static function getTopicInfo($topic_id)
    {
        $topicInfo = get_post($topic_id);
        $topic = [];
        if ($topicInfo) {
            $topic = [
                'topic_title'   => $topicInfo->post_title,
                'topic_id'      => $topicInfo->ID,
                'topic_url'     => get_permalink($topicInfo->ID),
                'topic_content' => $topicInfo->post_content,
            ];
        }

        return $topic;
    }

    public static function getForumInfo($forum_id)
    {
        $forumInfo = get_post($forum_id);
        $forum = [];
        if ($forumInfo) {
            $forum = [
                'forum_title' => $forumInfo->post_title,
                'forum_id'    => $forumInfo->ID,
                'forum_url'   => get_permalink($forumInfo->ID),
            ];
        }

        return $forum;
    }

    public static function getReplyInfo($reply_id)
    {
        $replyInfo = get_post($reply_id);
        $reply = [];
        if ($replyInfo) {
            $reply = [
                'reply_content' => $replyInfo->post_content,
            ];
        }

        return $reply;
    }

    public static function getGroupInfo($group_id, $status = '', $extra = false)
    {
        global $wpdb;
        if ($status == '') {
            $group = $wpdb->get_results(
                $wpdb->prepare("select id,name,description from {$wpdb->prefix}bp_groups where id = %d", $group_id)
            );
        } else {
            $group = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id,name,description FROM {$wpdb->prefix}bp_groups WHERE id = %d AND status = %s",
                    $group_id,
                    $status
                )
            );
        }

        if (\count($group)) {
            $groupInfo = [
                'group_id'    => $group[0]->id,
                'group_title' => $group[0]->name,
                'group_desc'  => $group[0]->description
            ];
        }
        if ($extra == static::USER_JOIN_PUBLIC_GRP_PRO) {
            $group_obj = groups_get_group($group_id);
            $groupInfo['manage_group_request_url'] = maybe_serialize(bp_get_group_permalink($group_obj) . 'admin/membership-requests/');
        }

        return $groupInfo;
    }

    public static function getTopicByForum($queryParams)
    {
        $forum_id = $queryParams->forum_id;
        if ($forum_id === 'any') {
            $topics[] = [
                'topic_id'    => 'any',
                'topic_title' => 'Any Topic',
            ];
        } else {
            $topic_args = [
                'post_type'      => bbp_get_topic_post_type(),
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'post_parent'    => $forum_id,
                'post_status'    => 'publish',
            ];

            $topic_list = get_posts($topic_args);
            $topics = [];

            foreach ($topic_list as $key => $val) {
                $topics[] = [
                    'topic_id'    => $val->ID,
                    'topic_title' => $val->post_title,
                ];
            }
        }
        wp_send_json_success($topics);
    }

    public static function getActivityInfo($activity_id, $group_id, $user_id)
    {
        global $wpdb;

        $activity = $wpdb->get_results("select id,content from {$wpdb->prefix}bp_activity where id = {$activity_id}");

        $group = groups_get_group($group_id);
        $activityInfo = [];
        if (\count($activity)) {
            $activityInfo = [
                'activity_id'         => $activity[0]->id,
                'activity_url'        => bp_get_group_permalink($group) . 'activity',
                'activity_content'    => $activity[0]->content,
                'activity_stream_url' => bp_core_get_user_domain($user_id) . 'activity/' . $activity_id,
            ];
        }

        return $activityInfo;
    }

    public static function handle_accept_friend_request($id, $initiator_user_id, $friend_user_id, $friendship)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_ACCEPT_FRIEND_REQ);
        if (!$flows) {
            return;
        }

        $user = static::getUserInfo($friend_user_id);
        $initUser = static::getUserInfo($initiator_user_id);

        $currentUser = [
            'id'         => $friend_user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $initiatorUser = [
            'friend_first_name' => $initUser['first_name'],
            'friend_last_name'  => $initUser['last_name'],
            'friend_email'      => $initUser['user_email'],
            'friend_nickname'   => $initUser['nickname'],
            'friend_avatar_url' => $initUser['avatar_url'],
            'friend_id'         => $initiator_user_id,
        ];

        Flow::execute('BuddyBoss', static::USER_ACCEPT_FRIEND_REQ, array_merge($currentUser, $initiatorUser), $flows);
    }

    public static function handle_sends_friend_request($id, $initiator_user_id, $friend_user_id, $friendship)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_SEND_FRIEND_REQ);

        if (!$flows) {
            return;
        }

        $user = static::getUserInfo($initiator_user_id);
        $friend = static::getUserInfo($friend_user_id);

        $currentUser = [
            'id'         => $initiator_user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $initiatorUser = [
            'friend_first_name' => $friend['first_name'],
            'friend_last_name'  => $friend['last_name'],
            'friend_email'      => $friend['user_email'],
            'friend_nickname'   => $friend['nickname'],
            'friend_avatar_url' => $friend['avatar_url'],
            'friend_id'         => $friend_user_id,
        ];

        Flow::execute('BuddyBoss', static::USER_SEND_FRIEND_REQ, array_merge($currentUser, $initiatorUser), $flows);
    }

    public static function handle_create_topic($topic_id, $forum_id, $anonymous_data, $topic_author)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_CREATE_TOPIC);
        $flows = static::flowFilter($flows, 'selectedForum', $forum_id);

        if (!$flows) {
            return;
        }

        $user = static::getUserInfo($topic_author);
        $currentUser = [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $topics = static::getTopicInfo($topic_id);
        $forums = static::getForumInfo($forum_id);

        Flow::execute('BuddyBoss', static::USER_CREATE_TOPIC, array_merge($currentUser, $topics, $forums), $flows);
    }

    public static function handle_join_public_group($group_id, $user_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_JOIN_PUBLIC_GRP_PRO);
        $flows = static::flowFilter($flows, 'selectedGroup', $group_id);

        if (!$flows) {
            return;
        }

        $groups = static::getGroupInfo($group_id, 'public');
        if (!\count($groups)) {
            return;
        }

        $user = static::getUserInfo($user_id);
        $currentUser = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::USER_JOIN_PUBLIC_GRP_PRO, array_merge($currentUser, $groups), $flows);
    }

    public static function handle_join_private_group($user_id, $group_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_JOIN_PRIVATE_GRP_PRO);
        $flows = static::flowFilter($flows, 'selectedGroup', $group_id);

        if (!$flows) {
            return;
        }

        $groups = static::getGroupInfo($group_id, 'private');
        if (!\count($groups)) {
            return;
        }

        $user = static::getUserInfo($user_id);
        $currentUser = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::USER_JOIN_PRIVATE_GRP_PRO, array_merge($currentUser, $groups), $flows);
    }

    public static function handle_leaves_group($group_id, $user_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_REMOVED_FROM_GRP_PRO);
        $flows = static::flowFilter($flows, 'selectedGroup', $group_id);

        if (!$flows) {
            return;
        }

        $groups = static::getGroupInfo($group_id);
        if (!\count($groups)) {
            return;
        }

        $user = static::getUserInfo($user_id);
        $currentUser = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::USER_REMOVED_FROM_GRP_PRO, array_merge($currentUser, $groups), $flows);
    }

    public static function handle_post_group_activity($content, $user_id, $group_id, $activity_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_MAKE_A_POST_GRP_PRO);
        $flows = static::flowFilter($flows, 'selectedGroup', $group_id);

        if (!$flows) {
            return;
        }

        $groups = static::getGroupInfo($group_id);
        if (!\count($groups)) {
            return;
        }

        $user = static::getUserInfo($user_id);
        $currentUser = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $data = array_merge($currentUser, $groups, static::getActivityInfo($activity_id, $group_id, $user_id));

        Flow::execute('BuddyBoss', static::USER_MAKE_A_POST_GRP_PRO, $data, $flows);
    }

    public static function handle_replies_topic($reply_id, $topic_id, $forum_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_REPLIED_TOPIC);
        $flows = static::flowFilter($flows, 'selectedTopic', $topic_id);

        if (!$flows) {
            return;
        }

        $topics = static::getTopicInfo($topic_id);
        if (!\count($topics)) {
            return;
        }

        $forums = static::getForumInfo($forum_id);
        if (!\count($forums)) {
            return;
        }

        $replies = static::getReplyInfo($reply_id);
        if (!\count($replies)) {
            return;
        }

        $user_id = get_current_user_id();
        $user = static::getUserInfo($user_id);

        $currentUser = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::USER_REPLIED_TOPIC, array_merge($currentUser, $topics, $forums, $replies), $flows);
    }

    public static function handle_request_private_group($user_id, $admins, $group_id, $request_id)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_ACCESS_PRIVATE_GRP_PRO);
        $flows = static::flowFilter($flows, 'selectedGroup', $group_id);

        if (!$flows) {
            return;
        }

        $groups = static::getGroupInfo($group_id, 'private', static::USER_ACCESS_PRIVATE_GRP_PRO);
        if (!\count($groups)) {
            return;
        }

        $user = static::getUserInfo($user_id, static::USER_ACCESS_PRIVATE_GRP_PRO);
        $currentUser = [
            'id'               => $user_id,
            'first_name'       => $user['first_name'],
            'last_name'        => $user['last_name'],
            'user_email'       => $user['user_email'],
            'nickname'         => $user['nickname'],
            'avatar_url'       => $user['avatar_url'],
            'user_profile_url' => $user['user_profile_url'],
        ];

        Flow::execute('BuddyBoss', static::USER_ACCESS_PRIVATE_GRP_PRO, array_merge($currentUser, $groups), $flows);
    }

    public static function handle_send_email_invites($user_id, $post)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_SEND_INVITATION);

        if (!$flows) {
            return;
        }

        $data = static::setUserData($user_id, static::USER_SEND_INVITATION);

        Flow::execute('BuddyBoss', static::USER_SEND_INVITATION, $data, $flows);
    }

    public static function handle_update_avatar($item_id, $type, $avatar_data)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_UPDATE_AVATAR);

        if (!$flows) {
            return;
        }

        $data = static::setUserData($avatar_data['item_id'], static::USER_UPDATE_AVATAR);

        Flow::execute('BuddyBoss', static::USER_UPDATE_AVATAR, $data, $flows);
    }

    public static function handle_update_profile($user_id, $posted_field_ids, $errors, $old_values, $new_values)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_UPDATE_PROFILE);

        if (!$flows) {
            return;
        }

        $data = static::setUserData($user_id, static::USER_ACTIVATE_ACCOUNT);

        Flow::execute('BuddyBoss', static::USER_UPDATE_PROFILE, $data, $flows);
    }

    public static function handle_account_active($user_id, $key, $user)
    {
        $flows = Flow::exists('BuddyBoss', static::USER_ACTIVATE_ACCOUNT);

        if (!$flows) {
            return;
        }
        $data = static::setUserData($user_id, static::USER_ACTIVATE_ACCOUNT);

        Flow::execute('BuddyBoss', static::USER_ACTIVATE_ACCOUNT, $data, $flows);
    }

    public static function handle_invitee_active_account($user_id, $inviter_id, $post_id)
    {
        $flows = Flow::exists('BuddyBoss', static::NEW_MEMBER_ACTIVATION_PRO);

        if (!$flows) {
            return;
        }

        $user = static::getUserInfo($inviter_id);
        $data = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::NEW_MEMBER_ACTIVATION_PRO, $data, $flows);
    }

    public static function handle_invitee_register_account($user_id, $inviter_id, $post_id)
    {
        $flows = Flow::exists('BuddyBoss', static::NEW_MEMBER_REGISTRATION_PRO);

        if (!$flows) {
            return;
        }

        $user = static::getUserInfo($inviter_id);
        $data = [
            'id'         => $user_id,
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        Flow::execute('BuddyBoss', static::NEW_MEMBER_REGISTRATION_PRO, $data, $flows);
    }

    public static function getAllGroup($queryParams)
    {
        $select_option_id = $queryParams->select_option_id;
        if (\in_array($select_option_id, [static::USER_JOIN_PUBLIC_GRP_PRO, static::USER_ACCESS_PRIVATE_GRP_PRO])) {
            $status = 'public';
        } elseif ($select_option_id == static::USER_JOIN_PRIVATE_GRP_PRO) {
            $status = 'private';
        } elseif (\in_array($select_option_id, [static::USER_REMOVED_FROM_GRP_PRO, static::USER_MAKE_A_POST_GRP_PRO])) {
            $status = '';
        }

        $public_groups = groups_get_groups(
            [
                'status'   => $status,
                'per_page' => -1,
            ]
        );

        if (!empty($public_groups['groups'])) {
            $public_groups = $public_groups['groups'];
        } else {
            $public_groups = [];
        }
        $groups[] = [
            'group_id'    => 'any',
            'group_title' => __('Any Group', 'bit-integrations-pro'),
        ];
        foreach ($public_groups as $k => $group) {
            $groups[] = [
                'group_id'    => $group->id,
                'group_title' => $group->name
            ];
        }

        return $groups;
    }

    public static function getAllTopic($queryParams)
    {
        $forum_id = $queryParams->forum_id;
        if ($forum_id === 'any') {
            $topics[] = [
                'topic_id'    => 'any',
                'topic_title' => __('Any Topic', 'bit-integrations-pro'),
            ];
        } else {
            $topic_args = [
                'post_type'      => bbp_get_topic_post_type(),
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'post_parent'    => $forum_id,
                'post_status'    => 'publish',
            ];

            $topic_list = get_posts($topic_args);
            $topics = [];

            foreach ($topic_list as $key => $val) {
                $topics[] = [
                    'topic_id'    => $val->ID,
                    'topic_title' => $val->post_title,
                ];
            }
        }
        wp_send_json_success($topics);
    }

    protected static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];
        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
            }
            if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '') {
                $filteredFlows[] = $flow;
            }
        }

        return $filteredFlows;
    }

    private static function setUserData($user_id, $module)
    {
        $data = static::getUserInfo($user_id);
        $fields = static::fields($module);
        $userProfileData = BuddyBossHelper::getProfileData($user_id);
        $userFieldValues = array_column($userProfileData, 'value', 'field_id');

        foreach ($fields as $key => $field) {
            if (strpos($field['name'], 'field_') !== false) {
                $fieldId = substr($field['name'], 6);
                $rawData = unserialize($userFieldValues[$fieldId] ?? '');
                $data[$field['name']] = $rawData ? $rawData : $userFieldValues[$fieldId];
            }
        }

        return $data;
    }
}
