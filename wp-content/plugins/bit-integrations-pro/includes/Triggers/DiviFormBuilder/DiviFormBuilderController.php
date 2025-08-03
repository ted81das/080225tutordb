<?php

namespace BitApps\BTCBI_PRO\Triggers\DiviFormBuilder;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class DiviFormBuilderController
{
    public static function info()
    {
        return [
            'name'              => 'Divi Form by Divi Engine',
            'title'             => __('Divi Form Builder by Divi Engine', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => DiviFormBuilderHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/divi-form-builder-by-divi-engine-integrations/',
            'tutorial_url'      => 'https://youtube.com/playlist?list=PL7c6CDwwm-AJpOfpcioYpZOYbhcY_qTLN&si=apGHh18cxh98fVXd',
            'tasks'             => [
                'action' => 'diviformbuilder/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'diviformbuilder/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'diviformbuilder/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!DiviFormBuilderHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Divi Form by Divi Engine'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'df_after_process', 'skipPrimaryKey' => false],
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('divi_form_builder');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'divi_form_builder');
    }

    public static function handleDiviFormBuilderSubmit(...$record)
    {
        $formData = DiviFormBuilderHelper::setFields($record[0], $record[1]);

        if (get_option('btcbi_divi_form_builder_test') !== false) {
            update_option('btcbi_divi_form_builder_test', [
                'formData'   => $formData,
                'primaryKey' => !empty($record[0]) ? [(object) ['key' => 'id', 'value' => $record[0]]] : ''
            ]);
        }

        if ($flows = Flow::exists('DiviFormBuilder', current_action())) {
            $formIdField = ['id' => $record[0]];

            foreach ($flows as $flow) {
                $flowDetails = static::parseFlowDetails($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                if (DiviFormBuilderHelper::isPrimaryKeysMatch($record[1], $flowDetails, $formIdField)) {
                    $data = DiviFormBuilderHelper::prepareDataForFlow($record[1], $formIdField);
                    Flow::execute('DiviFormBuilder', current_action(), $data, [$flow]);
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
