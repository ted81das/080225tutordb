<?php

namespace BitApps\BTCBI_PRO\Triggers\Asgaros;

use AsgarosForum;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Core\Util\User;

class AsgarosHelper
{
    public static function UserCreatesNewTopicInForumFormatFields($post_id, $topic_id, $author_id)
    {
        if (! class_exists('AsgarosForum')) {
            return;
        }

        $forum = new AsgarosForum();

        if (! isset($post_id)) {
            return;
        }

        $data = static::setForumFields($forum, $topic_id, $post_id, $author_id);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function UserRepliesToTopicInForumFormatFields($post_id, $topic_id, $author_id)
    {
        if (! class_exists('AsgarosForum')) {
            return;
        }

        $forum = new AsgarosForum();

        if (! isset($post_id)) {
            return;
        }

        $data = static::setForumFields($forum, $topic_id, $post_id, $author_id);
        error_log(print_r($data, true));

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('AsgarosForum');
    }

    private static function setForumFields($forum, $topic_id, $post_id, $author_id)
    {
        $topic = $forum->content->get_topic($topic_id);
        $forum_id = $topic->parent_id;

        return [
            'topic_id' => $topic_id,
            'post_id'  => $post_id,
            'forum_id' => $forum_id,
            'forum'    => $forum->content->get_forum($forum_id),
            'topic'    => $forum->content->get_topic($topic_id),
            'post'     => $forum->content->get_post($post_id),
            'author'   => User::get($author_id),
        ];
    }
}
