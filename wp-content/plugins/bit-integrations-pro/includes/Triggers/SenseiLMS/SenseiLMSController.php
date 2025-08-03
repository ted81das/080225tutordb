<?php

namespace BitApps\BTCBI_PRO\Triggers\SenseiLMS;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class SenseiLMSController
{
    public static function info()
    {
        return [
            'name'              => 'Sensei LMS',
            'title'             => __('Learning Management System', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => SenseiLMSHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/sensei-lms-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'sensei_lms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'sensei_lms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'sensei_lms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!SenseiLMSHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Sensei LMS'));
        }

        wp_send_json_success(StaticData::forms());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleUserAttemptsQuiz($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type)
    {
        if (empty($user_id) || empty($quiz_id)) {
            return;
        }

        $formData = SenseiLMSHelper::getQuizData($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type);

        return static::flowExecute('sensei_user_quiz_attempts', $formData);
    }

    public static function handleUserCompletesCourse($user_id, $course_id)
    {
        if (empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = SenseiLMSHelper::formatUserCourseData($user_id, $course_id);

        return static::flowExecute('sensei_user_course_end', $formData);
    }

    public static function handleUserCompletesLesson($user_id, $lesson_id)
    {
        if (empty($user_id) || empty($lesson_id)) {
            return;
        }

        $formData = SenseiLMSHelper::UserCompletesLessonFormatFields($user_id, $lesson_id);

        return static::flowExecute('sensei_user_lesson_end', $formData);
    }

    public static function handleUserCompletesQuizPercentage($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type)
    {
        if (empty($user_id) || empty($quiz_id)) {
            return;
        }

        $formData = SenseiLMSHelper::getQuizData($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type);

        return static::flowExecute('sensei_user_quiz_percentage', $formData);
    }

    public static function handleUserEnrolledCourse($user_id, $course_id)
    {
        if (empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = SenseiLMSHelper::formatUserCourseData($user_id, $course_id);

        return static::flowExecute('sensei_user_course_start', $formData);
    }

    public static function handleUserFailsQuiz($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type)
    {
        if (empty($user_id) || empty($quiz_id) || empty($grade) || empty($quiz_passmark) || $grade > $quiz_passmark) {
            return;
        }

        $formData = SenseiLMSHelper::getQuizData($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type);

        return static::flowExecute('sensei_user_quiz_fails', $formData);
    }

    public static function handleUserPassesQuiz($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type)
    {
        if (empty($user_id) || empty($quiz_id) || empty($grade) || empty($quiz_passmark) || $grade < $quiz_passmark) {
            return;
        }

        $formData = SenseiLMSHelper::getQuizData($user_id, $quiz_id, $grade, $quiz_passmark, $quiz_grade_type);

        return static::flowExecute('sensei_user_quiz_passes', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('SenseiLMS', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');

        Flow::execute('SenseiLMS', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
