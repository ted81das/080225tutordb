<?php

namespace BitApps\BTCBI_PRO\Triggers\MemberPressCourse;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class MemberPressCourseHelper
{
    public static function getPostData($post_id, $type = 'course')
    {
        return [
            "{$type}_id"    => $post_id,
            "{$type}_title" => get_the_title($post_id),
            "{$type}_url"   => get_permalink($post_id),
            "{$type}_image" => get_the_post_thumbnail_url($post_id),
        ];
    }

    public static function formatData($user_id, $course_id, $progress = null, $created_at = null, $completed_at = null, $lesson_id = false)
    {
        $data = array_merge(
            User::get($user_id),
            static::getPostData($course_id, 'course'),
            [
                'course_progress' => $progress,
                'created_at'      => $created_at,
                'completed_at'    => $completed_at
            ]
        );

        if ($lesson_id) {
            $data = array_merge($data, static::getPostData($lesson_id, 'lesson'));
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return (bool) (\in_array('memberpress-courses/main.php', apply_filters('active_plugins', get_option('active_plugins'))) && class_exists('MeprCtrlFactory'));
    }
}
