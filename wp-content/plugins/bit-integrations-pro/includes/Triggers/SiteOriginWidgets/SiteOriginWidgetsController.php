<?php

namespace BitApps\BTCBI_PRO\Triggers\SiteOriginWidgets;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class SiteOriginWidgetsController
{
    public static function info()
    {
        return [
            'name'              => 'SiteOrigin Widgets',
            'title'             => __('SiteOrigin Widgets', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => true,
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/siteorigin-widgets-bundle-integrations/',
            'tutorial_url'      => 'https://youtube.com/playlist?list=PL7c6CDwwm-AIkjyDJvcaTv4270D8yuYKE&si=4b7Z0vPgvkJtLdsT',
            'tasks'             => [
                'action' => 'siteoriginwidgets/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'siteoriginwidgets/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'siteoriginwidgets/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        // if (!static::isPluginInstalled()) {
        //     wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SiteOrigin Widgets'));
        // }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'siteorigin_widgets_contact_sent', 'skipPrimaryKey' => false]
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('site_origin_widgets');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'site_origin_widgets');
    }

    public static function handleSiteOriginWidgetsSubmit(...$record)
    {
        $formData = SiteOriginWidgetsHelper::setFields($record[0]['fields'], $record[0]['_sow_form_id'], $record[1]);

        if (get_option('btcbi_site_origin_widgets_test') !== false) {
            update_option('btcbi_site_origin_widgets_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $record[0]['_sow_form_id']]]
            ]);
        }

        if ($flows = Flow::exists('SiteOriginWidgets', current_action())) {
            $formIdField = ['id' => $record[0]['_sow_form_id']];

            foreach ($flows as $flow) {
                $flowDetails = static::parseFlowDetails($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                if (SiteOriginWidgetsHelper::isPrimaryKeysMatch($record[1], $flowDetails, $formIdField)) {
                    $data = SiteOriginWidgetsHelper::prepareDataForFlow($record[1], $formIdField);
                    Flow::execute('SiteOriginWidgets', current_action(), $data, [$flow]);
                }
            }
        }

        return ['type' => 'success'];
    }

    private static function parseFlowDetails($flowDetails)
    {
        return \is_string($flowDetails) ? json_decode($flowDetails) : $flowDetails;
    }
}
