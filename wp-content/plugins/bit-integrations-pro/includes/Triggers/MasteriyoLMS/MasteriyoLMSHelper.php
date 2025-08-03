<?php

namespace BitApps\BTCBI_PRO\Triggers\MasteriyoLMS;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class MasteriyoLMSHelper
{
    public static function formatCourseData($new_status, $course_progress)
    {
        $course_id = $course_progress->get_course_id();
        $course = masteriyo_get_course($course_id);

        $course_id = $course_progress->get_course_id();
        $user_data = User::get($course_progress->get_user_id());

        if (!$course_id || !$user_data) {
            return;
        }

        $course = masteriyo_get_course($course_id);
        if (!$course || !\is_object($course) || !method_exists($course, 'get_data')) {
            return;
        }

        return Helper::prepareFetchFormatFields(array_merge(
            ['course_id' => $course_id, 'course_progress_status' => $new_status],
            $course->get_data(),
            $user_data
        ));
    }

    public static function formatLessonData($object)
    {
        $lesson = masteriyo_get_lesson($object->get_item_id());
        $user_data = User::get($object->get_user_id());

        if (!$lesson || !\is_object($lesson) || !method_exists($lesson, 'get_data') || !$user_data) {
            return;
        }

        return Helper::prepareFetchFormatFields(array_merge(
            $lesson->get_data(),
            $user_data
        ));
    }

    public static function formatQuizData($attempt, $status)
    {
        if (empty($attempt) || !\is_object($attempt) || !method_exists($attempt, 'get_quiz_id')
        || !method_exists($attempt, 'get_course_id') || !method_exists($attempt, 'get_data')
        || !method_exists($attempt, 'get_user_id') || !method_exists($attempt, 'get_earned_marks')
        || !\function_exists('masteriyo_get_quiz')) {
            return;
        }

        $quiz_id = $attempt->get_quiz_id();
        $quiz = masteriyo_get_quiz($quiz_id);

        if (!$quiz || !\is_object($quiz) || !method_exists($quiz, 'get_pass_mark') || !method_exists($quiz, 'get_data')) {
            return;
        }

        $is_passed = $attempt->get_earned_marks() >= $quiz->get_pass_mark();
        if ($is_passed !== $status) {
            return;
        }

        $user_data = User::get($attempt->get_user_id());

        return Helper::prepareFetchFormatFields(array_merge(
            $attempt->get_data(),
            $quiz->get_data(),
            $user_data
        ));
    }

    public static function isPluginInstalled()
    {
        return \function_exists('masteriyo');
    }
}
