<?php

namespace BitApps\BTCBI_PRO\Triggers\NexForms;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class NexFormsController
{
    public static function info()
    {
        return [
            'name'              => 'NEX-Forms',
            'title'             => __('Ultimate Drag and Drop WordPress Forms Builder.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'nex-forms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'nex-forms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'nex-forms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'NEX-Forms'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'NEXForms_submit_form_data', 'skipPrimaryKey' => false]
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

    public static function handleFormSubmitted()
    {
        $data = static::sanitizePostData($_POST);
        $formId = $data['nex_forms_Id'] ?? null;

        if (empty($formId)) {
            return;
        }

        $formData = static::formatFormData($formId, $data);

        Helper::setTestData('btcbi_NEXForms_submit_form_data_test', array_values($formData), 'form_id.value', $formId);

        return static::flowExecute($formData);
    }

    private static function sanitizePostData($data)
    {
        if (!is_iterable($data)) {
            return sanitize_text_field($data);
        }

        foreach ((array) $data as $key => $value) {
            $data[$key] = static::sanitizePostData($value);
        }

        return $data;
    }

    private static function formatFormData($formId, $data)
    {
        $excludedKeys = [
            'nex_forms_Id',
            'page',
            'ip',
            'nf_page_id',
            'nf_page_title',
            'company_url',
            'ms_current_step',
            'action',
            'paypal_return_url'
        ];

        $formData = array_diff_key($data, array_flip($excludedKeys));
        $formData['form_id'] = $formId;

        return Helper::prepareFetchFormatFields($formData);
    }

    private static function flowExecute($formData)
    {
        $flows = Flow::exists('NexForms', 'NEXForms_submit_form_data');
        if (empty($flows)) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('NexForms', 'NEXForms_submit_form_data', array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('NEXForms5_Config');
    }
}
