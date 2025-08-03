<?php

namespace BitApps\BTCBI_PRO\Triggers\CalculatedFieldsForm;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class CalculatedFieldsFormController
{
    public static function info()
    {
        return [
            'name'              => 'Calculated Fields Form Pro',
            'title'             => __('Calculated Fields Form is a WordPress plugin for creating forms with dynamically calculated fields. ', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => CalculatedFieldsFormHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/calculated-fields-form-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'cpcff/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'cpcff/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'cpcff/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!CalculatedFieldsFormHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Calculated Fields Form Pro'));
        }

        wp_send_json_success([
            ['form_name' => __('New Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'cpcff_process_data_before_insert', 'skipPrimaryKey' => false]
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

    public static function handleFormSubmitted($params, $buffer_A, $fields)
    {
        if (empty($params) || empty($params['formid'] || empty($fields))) {
            return;
        }

        $formData = CalculatedFieldsFormHelper::formatFormData($params, $fields);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_cpcff_process_data_before_insert_test', array_values($formData), 'form_id.value', $formData['form_id']['value']);

        $flows = Flow::exists('CalculatedFieldsForm', 'cpcff_process_data_before_insert');
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
                Flow::execute('CalculatedFieldsForm', 'cpcff_process_data_before_insert', $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
