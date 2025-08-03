<?php

namespace BitApps\BTCBI_PRO\Triggers\Asgaros;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AsgarosController
{
    public static function info()
    {
        return [
            'name'              => 'Asgaros Forum',
            'title'             => __('The best WordPress forum plugin, full-fledged yet easy and light forum solution for your WordPress website. The only forum software with multiple forum layouts.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AsgarosHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/asgaros-forum-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'asgaros/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'asgaros/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'asgaros/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AsgarosHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Asgaros Forum'));
        }

        wp_send_json_success([
            ['form_name' => __('User Creates New Topic in a Forum', 'bit-integrations-pro'), 'triggered_entity_id' => 'asgarosforum_after_add_topic_submit', 'skipPrimaryKey' => false],
            ['form_name' => __('User Replies to Topic in a Forum', 'bit-integrations-pro'), 'triggered_entity_id' => 'asgarosforum_after_add_post_submit', 'skipPrimaryKey' => false],
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

    public static function handleUserCreatesNewTopicInForum($post_id, $topic_id, $subject, $content, $link, $author_id)
    {
        $formData = AsgarosHelper::UserCreatesNewTopicInForumFormatFields($post_id, $topic_id, $author_id);

        Helper::setTestData(
            'btcbi_asgarosforum_after_add_topic_submit_test',
            array_values($formData),
            'forum_id.value',
            $formData['forum_id']['value']
        );

        return static::flowExecute('asgarosforum_after_add_topic_submit', $formData);
    }

    public static function handleUserRepliesToTopicInForum($post_id, $topic_id, $subject, $content, $link, $author_id)
    {
        $formData = AsgarosHelper::UserRepliesToTopicInForumFormatFields($post_id, $topic_id, $author_id);

        Helper::setTestData(
            'btcbi_asgarosforum_after_add_post_submit_test',
            array_values($formData),
            'topic_id.value',
            $formData['topic_id']['value']
        );

        return static::flowExecute('asgarosforum_after_add_post_submit', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('Asgaros', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey)) {
                continue;
            }

            if (Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                $data = array_column($formData, 'value', 'name');
                Flow::execute('Asgaros', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
