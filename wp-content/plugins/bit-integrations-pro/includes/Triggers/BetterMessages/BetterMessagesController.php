<?php

namespace BitApps\BTCBI_PRO\Triggers\BetterMessages;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BetterMessagesController
{
    public static function info()
    {
        return [
            'name'              => 'Better Messages',
            'title'             => __('Better Messages – is realtime private messaging system for WordPress, BuddyPress, BuddyBoss Platform, Ultimate Member, PeepSo and any other WordPress powered websites.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => BetterMessagesHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/better-messages-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'better_messages/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'better_messages/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'better_messages/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!BetterMessagesHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Better Messages'));
        }

        wp_send_json_success([
            ['form_name' => __('New Message Received', 'bit-integrations-pro'), 'triggered_entity_id' => 'better_messages_message_sent', 'skipPrimaryKey' => true]
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

    public static function handleNewMessageReceived($message)
    {
        if (!property_exists($message, 'id')) {
            return;
        }

        $formData = BetterMessagesHelper::NewMessageReceivedFormatFields($message);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_better_messages_message_sent_test', array_values($formData));

        $flows = Flow::exists('BetterMessages', 'better_messages_message_sent');

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('BetterMessages', 'better_messages_message_sent', $data, $flows);

        return ['type' => 'success'];
    }
}
