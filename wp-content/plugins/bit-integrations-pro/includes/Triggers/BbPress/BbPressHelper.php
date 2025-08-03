<?php

namespace BitApps\BTCBI_PRO\Triggers\BbPress;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class BbPressHelper
{
    public static function formatReplyTopicData($reply_id, $topic_id, $forum_id)
    {
        $data = array_merge(
            [
                'reply_id'          => $reply_id,
                'reply_title'       => get_the_title($reply_id) ?? null,
                'reply_link'        => get_the_permalink($reply_id) ?? null,
                'reply_description' => get_the_content(null, false, $reply_id) ?? null,
                'reply_status'      => get_post_status($reply_id) ?? null,
            ],
            static::formatForumTopicData($forum_id, $topic_id, $reply_id)
        );

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatTopicData($topic_id, $forum_id, $anonymous_data)
    {
        $data = static::formatForumTopicData($forum_id, $topic_id, null, $anonymous_data);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('bbPress');
    }

    private static function formatForumTopicData($forum_id, $topic_id, $reply_id = null, $anonymous_data = [])
    {
        $userData = User::get(get_current_user_id());

        if (empty($anonymous_data) && !empty($reply_id)) {
            $anonymous_data = [
                'bbp_anonymous_name'    => get_post_meta($reply_id, '_bbp_anonymous_name', true) ?? null,
                'bbp_anonymous_email'   => get_post_meta($reply_id, '_bbp_anonymous_email', true) ?? null,
                'bbp_anonymous_website' => get_post_meta($reply_id, '_bbp_anonymous_website', true) ?? null,
            ];
        } elseif (empty($anonymous_data)) {
            $anonymous_data = [
                'bbp_anonymous_name'    => null,
                'bbp_anonymous_email'   => null,
                'bbp_anonymous_website' => null,
            ];
        }

        return array_merge(
            [
                'topic_id'          => $topic_id,
                'topic_title'       => get_the_title($topic_id) ?? null,
                'topic_link'        => get_the_permalink($topic_id) ?? null,
                'topic_description' => get_the_content(null, false, $topic_id) ?? null,
                'topic_status'      => get_post_status($topic_id) ?? null,
                'forum_id'          => $forum_id,
                'forum_title'       => get_the_title($forum_id) ?? null,
                'forum_link'        => get_the_permalink($forum_id) ?? null,
                'forum_description' => get_the_content(null, false, $forum_id) ?? null,
                'forum_status'      => get_post_status($forum_id) ?? null,
            ],
            $userData,
            $anonymous_data
        );
    }
}
