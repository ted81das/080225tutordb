<?php

namespace BitApps\BTCBI_PRO\Triggers\WPLMS;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WPLMSController
{
    public static function info()
    {
        return [
            'name'              => 'WPLMS',
            'title'             => __('WPLMS is a social network plugin for WordPress that allows you to quickly add a social network.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wplms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wplms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wplms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WPLMS'));
        }

        wp_send_json_success([['form_name' => __('User complete course', 'bit-integrations-pro'), 'triggered_entity_id' => 'wplms_submit_course', 'skipPrimaryKey' => true]]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleUserCompleteCourse($course_id, $user_id)
    {
        $user_id = empty($user_id) ? get_current_user_id() : null;

        if (empty($user_id) || empty($course_id)) {
            return;
        }

        $formData = static::formatCourseData($course_id, $user_id);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_wplms_submit_course_test', array_values($formData));

        $flows = Flow::exists('WPLMS', 'wplms_submit_course');
        if (!$flows) {
            return;
        }

        Flow::execute('WPLMS', 'wplms_submit_course', array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function formatCourseData($course_id, $user_id)
    {
        $courses = get_post($course_id);
        error_log(print_r([get_post($course_id), Post::get($course_id)], true));

        return Helper::prepareFetchFormatFields(array_merge(
            User::get($user_id),
            [
                'course_id'    => $courses->ID,
                'course_name'  => $courses->post_name,
                'course_title' => $courses->post_title,
            ]
        ));
    }

    private static function isPluginInstalled()
    {
        return class_exists('WPLMS_Init');
    }
}
