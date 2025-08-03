<?php

namespace BitApps\BTCBI_PRO\Triggers\PieForms;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class PieFormsController
{
    public static function info()
    {
        return [
            'name'              => 'Pie Forms',
            'title'             => __('Pie Forms is a WordPress Form Builder.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => PieFormsHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/pie-forms-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'pie_forms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'pie_forms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'pie_forms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!PieFormsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Pie Forms'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'pie_forms_complete_entry_save', 'skipPrimaryKey' => false]
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('pie_forms');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'pie_forms');
    }

    public static function handleFormSubmitted($entry_id, $fields, $entry, $form_id, $form_data)
    {
        $formData = PieFormsHelper::formatFields($form_id, $form_data);

        Helper::setTestData('btcbi_pie_forms_test', array_values($formData), 'form_id.value', $form_id);
        $flows = Flow::exists('PieForms', 'pie_forms_complete_entry_save');

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
                Flow::execute('PieForms', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
