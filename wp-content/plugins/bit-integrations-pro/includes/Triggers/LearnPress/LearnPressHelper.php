<?php

namespace BitApps\BTCBI_PRO\Triggers\LearnPress;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class LearnPressHelper
{
    public static function formatTriggerData($user_id, $course_id, $lesson_id = null)
    {
        $data = array_merge(User::get($user_id), static::getPostData($course_id, 'course'));

        if (!empty($lesson_id)) {
            $data = array_merge($data, static::getPostData($lesson_id, 'lesson'));
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function getPostData($post_id, $key_prefix)
    {
        $post = Post::get($post_id);

        if (empty($post)) {
            return [];
        }

        return [
            "{$key_prefix}_id"           => $post['ID'],
            "{$key_prefix}_name"         => $post['post_name'],
            "{$key_prefix}_title"        => $post['post_title'],
            "{$key_prefix}_content"      => $post['post_content'],
            "{$key_prefix}_status"       => $post['post_status'],
            "{$key_prefix}_published_on" => $post['post_date'],
            "{$key_prefix}_url"          => get_permalink($post_id),
        ];
    }

    public static function isPluginInstalled()
    {
        return class_exists('LearnPress');
    }
}
