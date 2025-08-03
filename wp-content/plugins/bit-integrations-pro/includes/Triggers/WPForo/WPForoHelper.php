<?php

namespace BitApps\BTCBI_PRO\Triggers\WPForo;

use wpforo\classes\Forums;
use wpforo\classes\Topics;

class WPForoHelper
{
    private static $topicFields = [
        [
            'name'  => 'topic_topicid',
            'type'  => 'text',
            'label' => 'Topic ID',
        ],
        [
            'name'  => 'topic_title',
            'type'  => 'text',
            'label' => 'Topic Title',
        ],
        [
            'name'  => 'topic_body',
            'type'  => 'text',
            'label' => 'Topic Content (Body)',
        ],
        [
            'name'  => 'topic_tags',
            'type'  => 'text',
            'label' => 'Topic Tags',
        ],
        [
            'name'  => 'topic_postmetas',
            'type'  => 'text',
            'label' => 'Topic Post Metas',
        ],
        [
            'name'  => 'topic_name',
            'type'  => 'text',
            'label' => 'Topic Name',
        ],
        [
            'name'  => 'topic_email',
            'type'  => 'text',
            'label' => 'Topic Email',
        ],
        [
            'name'  => 'topic_slug',
            'type'  => 'text',
            'label' => 'Topic Slug',
        ],
        [
            'name'  => 'topic_created',
            'type'  => 'text',
            'label' => 'Topic Created',
        ],
        [
            'name'  => 'topic_userid',
            'type'  => 'text',
            'label' => 'Topic User Id',
        ],
        [
            'name'  => 'topic_has_attach',
            'type'  => 'text',
            'label' => 'Topic Has Attach',
        ],
        [
            'name'  => 'topic_first_postid',
            'type'  => 'text',
            'label' => 'Topic First Post ID',
        ],
        [
            'name'  => 'topic_type',
            'type'  => 'text',
            'label' => 'Topic Type',
        ],
        [
            'name'  => 'topic_status',
            'type'  => 'text',
            'label' => 'Topic Status',
        ],
        [
            'name'  => 'topic_private',
            'type'  => 'text',
            'label' => 'Topic Private',
        ],
        [
            'name'  => 'topic_url',
            'type'  => 'text',
            'label' => 'Topic URL',
        ],
    ];

    private static $forumFields = [
        [
            'name'  => 'forum_forumid',
            'type'  => 'text',
            'label' => 'Forum ID',
        ],
        [
            'name'  => 'forum_title',
            'type'  => 'text',
            'label' => 'Forum Title',
        ],
        [
            'name'  => 'forum_slug',
            'type'  => 'text',
            'label' => 'Forum Slug',
        ],
        [
            'name'  => 'forum_description',
            'type'  => 'text',
            'label' => 'Forum Description',
        ],
        [
            'name'  => 'forum_parentid',
            'type'  => 'text',
            'label' => 'Forum Parent ID',
        ],
        [
            'name'  => 'forum_icon',
            'type'  => 'text',
            'label' => 'Forum Icon',
        ],
        [
            'name'  => 'forum_cover',
            'type'  => 'text',
            'label' => 'Forum Cover',
        ],
        [
            'name'  => 'forum_cover_height',
            'type'  => 'text',
            'label' => 'Forum Cover Height',
        ],
        [
            'name'  => 'forum_last_topicid',
            'type'  => 'text',
            'label' => 'Forum Last Topic ID',
        ],
        [
            'name'  => 'forum_last_postid',
            'type'  => 'text',
            'label' => 'Forum last Post ID',
        ],
        [
            'name'  => 'forum_last_userid',
            'type'  => 'text',
            'label' => 'Forum last User ID',
        ],
        [
            'name'  => 'forum_last_post_date',
            'type'  => 'text',
            'label' => 'Forum Last Post Date',
        ],
        [
            'name'  => 'forum_topics',
            'type'  => 'text',
            'label' => 'Forum Topics',
        ],
        [
            'name'  => 'forum_posts',
            'type'  => 'text',
            'label' => 'Forum Posts',
        ],
        [
            'name'  => 'forum_permissions',
            'type'  => 'text',
            'label' => 'Forum Permissions',
        ],
        [
            'name'  => 'forum_meta_key',
            'type'  => 'text',
            'label' => 'Forum Meta Key',
        ],
        [
            'name'  => 'forum_meta_desc',
            'type'  => 'text',
            'label' => 'Forum Meta Desc',
        ],
        [
            'name'  => 'forum_status',
            'type'  => 'text',
            'label' => 'Forum Status',
        ],
        [
            'name'  => 'forum_layout',
            'type'  => 'text',
            'label' => 'Forum Layout',
        ],
        [
            'name'  => 'forum_order',
            'type'  => 'text',
            'label' => 'Forum Order',
        ],
        [
            'name'  => 'forum_color',
            'type'  => 'text',
            'label' => 'Forum Color',
        ],
        [
            'name'  => 'forum_url',
            'type'  => 'text',
            'label' => 'Forum URL',
        ],
        [
            'name'  => 'forum_cover_url',
            'type'  => 'text',
            'label' => 'Forum Cover URL',
        ],
    ];

    private static $postFields = [
        [
            'name'  => 'post_postid',
            'type'  => 'text',
            'label' => 'Post Id',
        ],
        [
            'name'  => 'post_title',
            'type'  => 'text',
            'label' => 'Post Title',
        ],
        [
            'name'  => 'post_parentid',
            'type'  => 'text',
            'label' => 'Post Parent Id',
        ],
        [
            'name'  => 'post_body',
            'type'  => 'text',
            'label' => 'Post Content (Body)',
        ],
        [
            'name'  => 'post_postmetas',
            'type'  => 'text',
            'label' => 'Post Metas',
        ],
        [
            'name'  => 'post_name',
            'type'  => 'text',
            'label' => 'Post Name',
        ],
        [
            'name'  => 'post_email',
            'type'  => 'text',
            'label' => 'Post Email',
        ],
        [
            'name'  => 'post_created',
            'type'  => 'text',
            'label' => 'Post Created',
        ],
        [
            'name'  => 'post_userid',
            'type'  => 'text',
            'label' => 'Post User ID',
        ],
        [
            'name'  => 'post_root',
            'type'  => 'text',
            'label' => 'Post Root',
        ],
        [
            'name'  => 'post_status',
            'type'  => 'text',
            'label' => 'Post Status',
        ],
        [
            'name'  => 'post_private',
            'type'  => 'text',
            'label' => 'Post Private',
        ],
        [
            'name'  => 'post_posturl',
            'type'  => 'text',
            'label' => 'Post URL',
        ],
    ];

    private static $topicFieldsForPost = [
        [
            'name'  => 'topic_modified',
            'type'  => 'text',
            'label' => 'Topic Modified',
        ],
        [
            'name'  => 'topic_posts',
            'type'  => 'text',
            'label' => 'Topic Posts',
        ],
        [
            'name'  => 'topic_votes',
            'type'  => 'text',
            'label' => 'Topic Votes',
        ],
        [
            'name'  => 'topic_answers',
            'type'  => 'text',
            'label' => 'Topic Answers',
        ],
        [
            'name'  => 'topic_views',
            'type'  => 'text',
            'label' => 'Topic Views',
        ],
        [
            'name'  => 'topic_meta_key',
            'type'  => 'text',
            'label' => 'Topic Meta Key',
        ],
        [
            'name'  => 'topic_meta_desc',
            'type'  => 'text',
            'label' => 'Topic Meta Desc',
        ],
        [
            'name'  => 'topic_solved',
            'type'  => 'text',
            'label' => 'Topic Solved',
        ],
        [
            'name'  => 'topic_closed',
            'type'  => 'text',
            'label' => 'Topic Closed',
        ],
        [
            'name'  => 'topic_has_attach',
            'type'  => 'text',
            'label' => 'Topic Has Attach',
        ],
        [
            'name'  => 'topic_prefix',
            'type'  => 'text',
            'label' => 'Topic Prefix',
        ],
        [
            'name'  => 'topic_short_url',
            'type'  => 'text',
            'label' => 'Topic Short URL',
        ],
    ];

    private static $postFieldsForVote = [
        [
            'name'  => 'post_forumid',
            'type'  => 'text',
            'label' => 'Post Forum ID',
        ],
        [
            'name'  => 'post_topicid',
            'type'  => 'text',
            'label' => 'Post Topic ID',
        ],
        [
            'name'  => 'post_modified',
            'type'  => 'text',
            'label' => 'Post Modified',
        ],
        [
            'name'  => 'post_likes',
            'type'  => 'text',
            'label' => 'Post Likes',
        ],
        [
            'name'  => 'post_votes',
            'type'  => 'text',
            'label' => 'Post Votes',
        ],
        [
            'name'  => 'post_is_answer',
            'type'  => 'text',
            'label' => 'Post Is Answer',
        ],
        [
            'name'  => 'post_is_first_post',
            'type'  => 'text',
            'label' => 'Is First Post',
        ],
        [
            'name'  => 'post_url',
            'type'  => 'text',
            'label' => 'Post URL',
        ],
        [
            'name'  => 'post_short_url',
            'type'  => 'text',
            'label' => 'Post Short URL',
        ],
    ];

    private static $wpUserFileds = [
        [
            'name'  => 'wp_user_id',
            'type'  => 'text',
            'label' => 'WP User ID',
        ],
        [
            'name'  => 'user_login',
            'type'  => 'text',
            'label' => 'User Login',
        ],
        [
            'name'  => 'display_name',
            'type'  => 'text',
            'label' => 'Display Name',
        ],
        [
            'name'  => 'user_firstname',
            'type'  => 'text',
            'label' => 'User First Name',
        ],
        [
            'name'  => 'user_lastname',
            'type'  => 'text',
            'label' => 'User Lastname',
        ],
        [
            'name'  => 'user_email',
            'type'  => 'text',
            'label' => 'User Email',
        ],
        [
            'name'  => 'user_role',
            'type'  => 'text',
            'label' => 'User Role',
        ]
    ];

    private static $postFieldsForReact = [
        [
            'name'  => 'post_likes_count',
            'type'  => 'text',
            'label' => 'Post Likes Count',
        ],
        [
            'name'  => 'post_is_answered',
            'type'  => 'text',
            'label' => 'Post is Answered',
        ],
    ];

    private static $answerFields = [
        [
            'name'  => 'answer_status',
            'type'  => 'text',
            'label' => 'Answer Status',
        ]
    ];

    private static $userFieldsKey = [
        ['field_key' => 'id', 'field_label' => 'ID', 'wp_user_key' => 'ID'],
        ['field_key' => 'login', 'field_label' => 'Login', 'wp_user_key' => 'user_login'],
        ['field_key' => 'display_name', 'field_label' => 'Display Name', 'wp_user_key' => 'display_name'],
        ['field_key' => 'firstname', 'field_label' => 'First Name', 'wp_user_key' => 'user_firstname'],
        ['field_key' => 'lastname', 'field_label' => 'Last Name', 'wp_user_key' => 'user_lastname'],
        ['field_key' => 'email', 'field_label' => 'Email', 'wp_user_key' => 'user_email'],
        ['field_key' => 'role', 'field_label' => 'Role', 'wp_user_key' => 'roles'],
    ];

    public static function getAllForums()
    {
        $wpForoForums = new Forums();
        $getForums = $wpForoForums->get_forums();

        if (empty($getForums)) {
            return false;
        }

        $forums[] = (object) [
            'value' => 'any',
            'label' => __('Any Forum', 'bit-integrations-pro')
        ];

        foreach ($getForums as $item) {
            if (!$item['is_cat']) {
                $forums[] = (object) ['value' => (string) $item['forumid'], 'label' => $item['title']];
            }
        }

        return $forums;
    }

    public static function getAllTopics()
    {
        $wpForoTopics = new Topics();
        $getTopics = $wpForoTopics->get_topics();

        if (empty($getTopics)) {
            return false;
        }

        $topics[] = (object) [
            'value' => 'any',
            'label' => __('Any Topic', 'bit-integrations-pro')
        ];

        foreach ($getTopics as $item) {
            $topics[] = (object) ['value' => (string) $item['topicid'], 'label' => $item['title']];
        }

        return $topics;
    }

    public static function getAllUsers()
    {
        $getUsers = get_users(['fields' => ['ID', 'display_name']]);

        if (empty($getUsers)) {
            return false;
        }

        $users[] = (object) [
            'value' => 'any',
            'label' => __('Any User', 'bit-integrations-pro')
        ];

        foreach ($getUsers as $item) {
            $users[] = (object) ['value' => (string) $item->ID, 'label' => $item->display_name];
        }

        return $users;
    }

    public static function getFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        if (empty($id)) {
            return;
        }

        $fields = [];

        if ($id === 'wpforo-1') {
            $fields = array_merge(self::$topicFields, self::$forumFields);
        } elseif ($id === 'wpforo-2') {
            unset(self::$topicFields[2], self::$topicFields[4]);
            $topicFields = array_merge(self::$topicFields, self::$topicFieldsForPost);
            $fields = array_merge(self::$postFields, $topicFields, self::$forumFields);
        } elseif ($id === 'wpforo-3' || $id === 'wpforo-4' || $id === 'wpforo-5' || $id === 'wpforo-6') {
            unset(self::$postFields[4], self::$postFields[12]);
            $postFields = array_merge(self::$postFields, self::$postFieldsForVote);

            if ($id === 'wpforo-5' || $id === 'wpforo-6') {
                $postFields = array_merge($postFields, self::$postFieldsForReact);
            }

            $fields = array_merge($postFields, self::$wpUserFileds);
        } elseif ($id === 'wpforo-7' || $id === 'wpforo-8') {
            unset(self::$postFields[4], self::$postFields[12]);

            $postFields = array_merge(self::$postFields, self::$postFieldsForVote);
            $voterFields = self::createUserFieldsByTypes('voter');
            $authorFields = self::createUserFieldsByTypes('author');
            $fields = array_merge($authorFields, $postFields, $voterFields);
        } elseif ($id === 'wpforo-9' || $id === 'wpforo-10') {
            unset(self::$postFields[4], self::$postFields[12]);

            $postFields = array_merge(self::$postFields, self::$postFieldsForVote, self::$postFieldsForReact);
            $reactorsFields = self::createUserFieldsByTypes($id === 'wpforo-9' ? 'liker' : 'disliker');
            $authorFields = self::createUserFieldsByTypes('author');
            $fields = array_merge($authorFields, $postFields, $reactorsFields);
        } elseif ($id === 'wpforo-11') {
            unset(self::$postFields[4], self::$postFields[12]);

            $fields = array_merge(self::$postFields, self::$postFieldsForVote, self::$answerFields);
        } elseif ($id === 'wpforo-12') {
            unset(self::$postFields[4], self::$postFields[12]);

            $authorFields = self::createUserFieldsByTypes('question_author');
            $fields = array_merge($authorFields, self::$postFields, self::$postFieldsForVote, self::$answerFields);
        }

        return $fields;
    }

    public static function formatTopicAddData($topic, $forum)
    {
        if (empty($topic) || empty($forum)) {
            return false;
        }

        foreach ($topic as $key => $item) {
            $topicData['topic_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        foreach ($forum as $key => $item) {
            $forumData['forum_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $mergedData = array_merge($topicData, $forumData);

        unset($mergedData['topic_topicurl'], $mergedData['forum_is_cat']);

        return $mergedData;
    }

    public static function formatPostAddData($post, $topic, $forum)
    {
        if (empty($post) || empty($topic) || empty($forum)) {
            return false;
        }

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        foreach ($topic as $key => $item) {
            $topicData['topic_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        foreach ($forum as $key => $item) {
            $forumData['forum_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        return array_merge($postData, $topicData, $forumData);
    }

    public static function formatVoteData($post, $userid)
    {
        if (empty($post) || empty($userid)) {
            return false;
        }

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $userData = get_userdata(\intval($userid));

        $formattedUserData = [
            'wp_user_id'     => $userData->ID,
            'user_login'     => $userData->user_login,
            'display_name'   => $userData->display_name,
            'user_firstname' => $userData->user_firstname,
            'user_lastname'  => $userData->user_lastname,
            'user_email'     => $userData->user_email,
            'user_role'      => \is_array($userData->roles)
                                    ? implode(',', $userData->roles) : $userData->roles,
        ];

        return array_merge($postData, $formattedUserData);
    }

    public static function formatReactionData($post, $userid)
    {
        unset($post['likers_usernames']);

        return self::formatVoteData($post, $userid);
    }

    public static function formatGetsVoteData($authorId, $post, $voterId)
    {
        if (empty($authorId) || empty($post) || empty($voterId)) {
            return false;
        }

        $authorData = self::userDataByType($authorId, 'author');

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $voterData = self::userDataByType($voterId, 'voter');

        return array_merge($authorData, $postData, $voterData);
    }

    public static function formatGetsReactionData($post, $authorId, $reactorsId, $reactType)
    {
        if (empty($post) || empty($authorId) || empty($reactorsId)) {
            return false;
        }

        $authorData = self::userDataByType($authorId, 'author');

        unset($post['likers_usernames']);

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $reactorsData = self::userDataByType($reactorsId, $reactType);

        return array_merge($authorData, $postData, $reactorsData);
    }

    public static function formatAnswerData($answerStatus, $post)
    {
        if (empty($post)) {
            return false;
        }

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $answerData = ['answer_status' => $answerStatus];

        return array_merge($postData, $answerData);
    }

    public static function formatAnswerGetsData($answerStatus, $post, $topicAuthorId)
    {
        if (empty($post) || empty($topicAuthorId)) {
            return false;
        }

        $authorData = self::userDataByType($topicAuthorId, 'question_author');

        foreach ($post as $key => $item) {
            $postData['post_' . $key] = \is_array($item) ? implode(',', $item) : $item;
        }

        $answerData = ['answer_status' => $answerStatus];

        return array_merge($authorData, $postData, $answerData);
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    public static function createUserFieldsByTypes($type = null)
    {
        $userFields = [];

        foreach (self::$userFieldsKey as $item) {
            $userFields[] = [
                'name'  => (!empty($type) ? $type . '_' : '') . $item['field_key'],
                'type'  => 'text',
                'label' => (!empty($type) ? ucwords(str_replace('_', ' ', $type)) . ' ' : '') . $item['field_label'],
            ];
        }

        return $userFields;
    }

    public static function userDataByType($id, $type = null)
    {
        $userData = get_userdata(\intval($id));

        $formattedUserData = [];

        foreach (self::$userFieldsKey as $item) {
            $key = $item['wp_user_key'];

            if (\is_array($userData->{$key})) {
                $data = implode(',', $userData->{$key});
            } else {
                $data = $userData->{$key};
            }

            $formattedUserData[(!empty($type) ? $type . '_' : '') . $item['field_key']] = $data;
        }

        return $formattedUserData;
    }
}
