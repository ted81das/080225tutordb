<?php

namespace BitApps\BTCBI_PRO\Triggers\SenseiLMS;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class SenseiLMSHelper
{
    public static function UserCompletesLessonFormatFields($user_id, $lesson_id)
    {
        $lesson = Post::get($lesson_id);
        $data = User::get($user_id);

        $data['lesson_id'] = $lesson_id;
        $data['lesson_title'] = $lesson['post_title'];

        if (\function_exists('Sensei')) {
            $course_id = Sensei()->lesson->get_course_id($lesson_id);

            if (!empty($course_id)) {
                $data = array_merge($data, static::getCourseData($course_id));
            }
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatUserCourseData($user_id, $course_id)
    {
        $data = array_merge(User::get($user_id), static::getCourseData($course_id));

        return Helper::prepareFetchFormatFields($data);
    }

    public static function getQuizData($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type)
    {
        if (! \function_exists('Sensei')) {
            return;
        }

        $quiz = Post::get($quiz_id);
        $user = User::get($user_id);
        $quizSubmit = Sensei()->quiz_submission_repository->get($quiz_id, $user_id);

        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_type = %s AND comment_ID = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, 'sensei_lesson_status', $quizSubmit->get_id()), ARRAY_A); // @phpcs:ignore

        return Helper::prepareFetchFormatFields(array_merge([
            'quiz_id'     => $quiz_id,
            'quiz_title'  => $quiz['post_title'] ?? '',
            'quiz_status' => $results[0]['comment_approved'] ?? '',
            'quiz'        => $results[0]['comment_post_ID'] ?? '',
            'percentage'  => $grade ?? '',
            'final_grade' => $quizSubmit->get_final_grade() ?? '',
            'pass_mark'   => $quiz_passmark ?? '',
            'grade_type'  => $quiz_grade_type ?? '',
            'created_at'  => $quizSubmit->get_created_at() ?? '',
        ], $user));
    }

    public static function isPluginInstalled()
    {
        return class_exists('Sensei_Main');
    }

    private static function getCourseData($course_id)
    {
        $course = Post::get($course_id);

        return [
            'course_id'          => $course_id,
            'course_title'       => $course['post_title'] ?? '',
            'course_description' => $course['post_excerpt'] ?? '',
            'course_status'      => $course['post_status'] ?? '',
        ];
    }
}
