<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class FluentCommunityController
{
    public static function info()
    {
        return [
            'name'              => 'Fluent Community',
            'title'             => __('Fluent Community is a open source community plugin for WordPress. This application allows you to trigger workflows when a new form submission or payment submission is received.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/fluentcommunity-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'fluent_community/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'fluent_community/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'fluent_community/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Fluent Community'));
        }

        wp_send_json_success(StaticData::formTasks());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('FluentCommunity', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('FluentCommunity', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return is_plugin_active('fluent-community/fluent-community.php');
    }
}
