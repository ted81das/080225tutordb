<?php

namespace BitApps\BTCBI_PRO\Triggers\SureFeedback;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class SureFeedbackController
{
    public static function info()
    {
        return [
            'name'              => 'SureFeedback',
            'title'             => __('A WordPress plugin for Website & Design feedback.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => SureFeedbackHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'surefeedback/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'surefeedback/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'surefeedback/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!SureFeedbackHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureFeedback'));
        }

        wp_send_json_success([
            ['form_name' => __('Comment Marked As Resolved', 'bit-integrations-pro'), 'triggered_entity_id' => 'ph_website_pre_rest_update_thread_attribute', 'skipPrimaryKey' => true],
            ['form_name' => __('New Comment On Site', 'bit-integrations-pro'), 'triggered_entity_id' => 'rest_insert_comment', 'skipPrimaryKey' => true],
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

    public static function handleCommentMarkedAsResolved($attr, $value, $comment)
    {
        if ('resolved' !== $attr || empty($value) || !class_exists('PH\Models\Post') || !\function_exists('ph_get_the_title')) {
            return;
        }

        if (\is_object($comment)) {
            $comment = get_object_vars($comment);
        }

        $formData = SureFeedbackHelper::formatResolvedCommentData($comment);

        return static::flowExecute('ph_website_pre_rest_update_thread_attribute', $formData);
    }

    public static function handleNewCommentOnSite($comment, $request, $creating)
    {
        if (empty($creating) || !class_exists('PH\Models\Post') || !\function_exists('ph_get_the_title')) {
            return;
        }

        if (\is_object($comment)) {
            $comment = get_object_vars($comment);
        }

        $formData = SureFeedbackHelper::formatNewCommentData($comment);

        return static::flowExecute('rest_insert_comment', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('SureFeedback', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('SureFeedback', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
