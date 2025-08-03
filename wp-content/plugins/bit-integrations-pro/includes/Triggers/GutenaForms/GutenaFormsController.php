<?php

namespace BitApps\BTCBI_PRO\Triggers\GutenaForms;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class GutenaFormsController
{
    public static function info()
    {
        return [
            'name'              => 'Gutena Forms',
            'title'             => __('Gutena Forms is the platform web creators choose to build professional WordPress websites, grow their skills, and build their business. Start for free today!', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => true,
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/gutena-forms-integrations/',
            'tutorial_url'      => 'https://youtube.com/playlist?list=PL7c6CDwwm-AJ9xNgikf2unjXj2h0sc9u4&si=VICbK_rEqnu3morT',
            'tasks'             => [
                'action' => 'gutenaforms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'gutenaforms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'gutenaforms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        // if (!SenseiLMSHelper::isPluginInstalled()) {
        //     wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Gutena Forms'));
        // }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'gutena_forms_submitted_data', 'skipPrimaryKey' => false]
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('gutena_forms');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'gutena_forms');
    }

    public static function handleGutenaFormsSubmit(...$record)
    {
        $formData = GutenaFormsHelper::setFields($record[0], $record[1]);

        if (get_option('btcbi_gutena_forms_test') !== false) {
            update_option('btcbi_gutena_forms_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $record[1]]]
            ]);
        }

        if ($flows = Flow::exists('GutenaForms', current_action())) {
            $formIdField = ['id' => ['value' => $record[1]]];

            foreach ($flows as $flow) {
                $flowDetails = static::parseFlowDetails($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                if (GutenaFormsHelper::isPrimaryKeysMatch($record[0], $flowDetails, $formIdField)) {
                    $data = GutenaFormsHelper::prepareDataForFlow($record[0], $formIdField);
                    Flow::execute('GutenaForms', current_action(), $data, [$flow]);
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
