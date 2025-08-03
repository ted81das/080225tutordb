<?php

namespace BitApps\BTCBI_PRO\Triggers\AvadaForms;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AvadaFormsController
{
    public static function info()
    {
        return [
            'name'              => 'Avada Forms',
            'title'             => __('Capture Avada Forms form submission to trigger the workflow.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AvadaFormsHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/avada-forms-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'avada-forms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'avada-forms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'avada-forms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AvadaFormsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Avada Forms'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'fusion_form_submission_data', 'skipPrimaryKey' => false],
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

    public static function handleFormSubmission($formSubmission, $formId)
    {
        $formData = AvadaFormsHelper::formatFields($formSubmission, $formId);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fusion_form_submission_data_test', array_values($formData), 'form_id.value', $formData['form_id']['value']);

        return static::flowExecute('fusion_form_submission_data', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('AvadaForms', $triggered_entity_id);

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
                Flow::execute('AvadaForms', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
