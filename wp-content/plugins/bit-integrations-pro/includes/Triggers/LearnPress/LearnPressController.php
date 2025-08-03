<?php

namespace BitApps\BTCBI_PRO\Triggers\LearnPress;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class LearnPressController
{
    public static function info()
    {
        return [
            'name'              => 'LearnPress LMS',
            'title'             => __('Easily Create And Sell Online Courses On Your WP Site With LearnPress.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => LearnPressHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'learn_press/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'learn_press/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'learn_press/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!LearnPressHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'LearnPress LMS'));
        }

        wp_send_json_success([
            ['form_name' => __('User Completes Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'learn-press/user-course-finished', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Lesson', 'bit-integrations-pro'), 'triggered_entity_id' => 'learn-press/user-completed-lesson', 'skipPrimaryKey' => true],
            ['form_name' => __('User Enrolled In Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'learnpress/user/course-enrolled', 'skipPrimaryKey' => true],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleUserCompletesCourse($course_id, $user_id, $result)
    {
        if (empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = LearnPressHelper::formatTriggerData($user_id, $course_id);

        return static::flowExecute('learn-press/user-course-finished', $formData);
    }

    public static function handleUserCompletesLesson($lesson_id, $course_id, $user_id)
    {
        if (empty($lesson_id) || empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = LearnPressHelper::formatTriggerData($user_id, $course_id, $lesson_id);

        return static::flowExecute('learn-press/user-completed-lesson', $formData);
    }

    public static function handleUserEnrolledInCourse($order_id, $course_id, $user_id)
    {
        if (empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = LearnPressHelper::formatTriggerData($user_id, $course_id);

        return static::flowExecute('learnpress/user/course-enrolled', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('LearnPress', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        Flow::execute('LearnPress', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
