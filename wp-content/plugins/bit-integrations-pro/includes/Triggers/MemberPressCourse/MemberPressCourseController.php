<?php

namespace BitApps\BTCBI_PRO\Triggers\MemberPressCourse;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class MemberPressCourseController
{
    public static function info()
    {
        return [
            'name'              => 'MemberPress Courses',
            'title'             => __('Easily Create And Sell Online Courses On Your WP Site With MemberPressCourse.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => MemberPressCourseHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'member_press_course/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'member_press_course/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'member_press_course/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!MemberPressCourseHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'MemberPress Courses'));
        }

        wp_send_json_success([
            ['form_name' => __('Course Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'mpcs_completed_course', 'skipPrimaryKey' => true],
            ['form_name' => __('Lesson Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'mpcs_completed_lesson', 'skipPrimaryKey' => true],
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

    public static function handleCourseCompleted($data)
    {
        if (empty($data) || empty($data->course_id) || empty($data->user_id)) {
            return;
        }

        $formData = MemberPressCourseHelper::formatData($data->user_id, $data->course_id, $data->progress, $data->created_at, $data->completed_at);

        return static::flowExecute('mpcs_completed_course', $formData);
    }

    public static function handleLessonCompleted($data)
    {
        if (empty($data) || empty($data->course_id) || empty($data->user_id) || empty($data->lesson_id)) {
            return;
        }

        $formData = $formData = MemberPressCourseHelper::formatData($data->user_id, $data->course_id, $data->progress, $data->created_at, $data->completed_at, $data->lesson_id);

        return static::flowExecute('mpcs_completed_lesson', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('MemberPressCourse', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('MemberPressCourse', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
