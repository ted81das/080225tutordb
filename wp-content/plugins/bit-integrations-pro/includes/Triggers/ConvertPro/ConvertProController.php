<?php

namespace BitApps\BTCBI_PRO\Triggers\ConvertPro;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class ConvertProController
{
    public static function info()
    {
        return [
            'name'              => 'Convert Pro',
            'title'             => __('A WordPress plugin to convert visitors into leads, subscribers and customers. ', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/convert-pro-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'convert_pro/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'convert_pro/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'convert_pro/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Convert Pro'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'cpro_form_submit', 'skipPrimaryKey' => false],
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

    public static function handleFormSubmitted($response, $postData)
    {
        if (empty($response) || empty($postData['style_id'])) {
            return;
        }

        $params = $postData['param'] ?? [];
        $data = ['form_id' => (int) sanitize_text_field($postData['style_id'])];

        if (!empty($params) && \is_array($params)) {
            $data += array_combine(array_map('ucfirst', array_keys($params)), $params);
        }

        $formData = Helper::prepareFetchFormatFields($data);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_cpro_form_submit_test', array_values($formData), 'form_id.value', $formData['form_id']['value']);

        $flows = Flow::exists('ConvertPro', 'cpro_form_submit');
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
                Flow::execute('ConvertPro', 'cpro_form_submit', $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('\Cp_V2_Loader');
    }
}
