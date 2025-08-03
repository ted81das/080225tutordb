<?php

namespace BitApps\BTCBI_PRO\Triggers\MasteriyoLMS;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class MasteriyoLMSController
{
    public static function info()
    {
        return [
            'name'              => 'Masteriyo LMS',
            'title'             => __('A WordPress LMS Plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => MasteriyoLMSHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'masteriyo/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'masteriyo/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'masteriyo/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!MasteriyoLMSHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'MasteriyoLMS'));
        }

        wp_send_json_success([
            ['form_name' => __('Course Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'masteriyo_course_progress_status_changed', 'skipPrimaryKey' => true],
            ['form_name' => __('Lesson Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'masteriyo_new_course_progress_item', 'skipPrimaryKey' => true],
            ['form_name' => __('Quiz Failed', 'bit-integrations-pro'), 'triggered_entity_id' => 'masteriyo_quiz_attempt_failed', 'skipPrimaryKey' => true],
            ['form_name' => __('Quiz Passed', 'bit-integrations-pro'), 'triggered_entity_id' => 'masteriyo_quiz_attempt_passed', 'skipPrimaryKey' => true],
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

    public static function handleCourseCompleted($course_id, $old_status, $new_status, $course_progress)
    {
        if (empty($new_status) || empty($course_progress) || 'completed' != $new_status && !\function_exists('masteriyo_get_course') || !method_exists($course_progress, 'get_course_id') || !method_exists($course_progress, 'get_user_id')) {
            return;
        }

        $formData = MasteriyoLMSHelper::formatCourseData($new_status, $course_progress);

        return static::flowExecute('masteriyo_course_progress_status_changed', $formData);
    }

    public static function handleLessonCompleted($item_id, $object)
    {
        if (empty($object) || !method_exists($object, 'get_item_type') || 'lesson' !== $object->get_item_type() || !\function_exists('masteriyo_get_lesson') || !method_exists($object, 'get_item_id') || !method_exists($object, 'get_user_id')) {
            return;
        }

        $formData = MasteriyoLMSHelper::formatLessonData($object);

        return static::flowExecute('masteriyo_new_course_progress_item', $formData);
    }

    public static function handleQuizFailed($attempt, $old_status, $new_status)
    {
        return static::flowExecute('masteriyo_quiz_attempt_failed', MasteriyoLMSHelper::formatQuizData($attempt, false));
    }

    public static function handleQuizPassed($attempt, $old_status, $new_status)
    {
        return static::flowExecute('masteriyo_quiz_attempt_passed', MasteriyoLMSHelper::formatQuizData($attempt, true));
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('MasteriyoLMS', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('MasteriyoLMS', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
