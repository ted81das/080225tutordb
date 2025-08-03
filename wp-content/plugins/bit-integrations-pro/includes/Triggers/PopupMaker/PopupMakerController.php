<?php

namespace BitApps\BTCBI_PRO\Triggers\PopupMaker;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Flow\Flow;

final class PopupMakerController
{
    public static function info()
    {
        return [
            'name'              => 'Popup Maker',
            'title'             => __('Popup Maker', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => true,
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/popup-maker-integrations/',
            'tutorial_url'      => '',
            'note'              => '<p>' . \sprintf(__('Please submit a <strong>%s</strong> of Popup Maker', 'bit-integrations-pro'), __('Subscription Form', 'bit-integrations-pro')) . '</p>',
            'tasks'             => [
                'action' => 'popupmaker/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'popupmaker/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'popupmaker/test/remove',
                'method' => 'post',
            ],
            'isPro' => true,
        ];
    }

    public function getAllTasks()
    {
        // if (!SenseiLMSHelper::isPluginInstalled()) {
        //     wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Popup Maker'));
        // }

        wp_send_json_success([
            ['form_name' => __('Subscription Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'pum_sub_form_success', 'skipPrimaryKey' => false]
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('popupmaker');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'popupmaker');
    }

    public static function handlePopupMakerSubmit(...$record)
    {
        $formData = PopupMakerHelper::setFields($record[0]);

        if (get_option('btcbi_popupmaker_test') !== false) {
            update_option('btcbi_popupmaker_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => (string) $record[0]['popup_id']]]
            ]);
        }

        if ($flows = Flow::exists('PopupMaker', current_action())) {
            foreach ($flows as $flow) {
                $flowDetails = static::parseFlowDetails($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                if (PopupMakerHelper::isPrimaryKeysMatch($record[0], $flowDetails)) {
                    $data = PopupMakerHelper::prepareDataForFlow($record[0]);
                    Flow::execute('PopupMaker', current_action(), $data, [$flow]);
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
