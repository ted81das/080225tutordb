<?php

namespace BitApps\BTCBI_PRO\Triggers\BbPress;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BbPressController
{
    public static function info()
    {
        return [
            'name'              => 'bbPress',
            'title'             => __('Discussion forums for WordPress.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => BbPressHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/bbpress-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'bb_press/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'bb_press/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'bb_press/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!BbPressHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'bbPress'));
        }

        wp_send_json_success([
            ['form_name' => __('Reply To Topic', 'bit-integrations-pro'), 'triggered_entity_id' => 'bbp_new_reply', 'skipPrimaryKey' => true],
            ['form_name' => __('Topic Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'bbp_new_topic', 'skipPrimaryKey' => true],
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

    public static function handleReplyToTopic($reply_id, $topic_id, $forum_id)
    {
        if (empty($reply_id) || empty($topic_id)) {
            return;
        }

        $formData = BbPressHelper::formatReplyTopicData($reply_id, $topic_id, $forum_id);

        return static::flowExecute('bbp_new_reply', $formData);
    }

    public static function handleTopicCreated($topic_id, $forum_id, $anonymous_data, $topic_author)
    {
        if (empty($topic_id)) {
            return;
        }

        $formData = BbPressHelper::formatTopicData($topic_id, $forum_id, $anonymous_data);

        return static::flowExecute('bbp_new_topic', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('BbPress', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('BbPress', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
