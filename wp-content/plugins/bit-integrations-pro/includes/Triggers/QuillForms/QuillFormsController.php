<?php

namespace BitApps\BTCBI_PRO\Triggers\QuillForms;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class QuillFormsController
{
    public static function info()
    {
        return [
            'name'              => 'Quill Forms',
            'title'             => __('Conversational Forms Builder for WordPress', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'quill-forms/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'quill-forms/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'quill-forms/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Quill Forms'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'quillforms_after_entry_processed', 'skipPrimaryKey' => false]
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

    public static function handleFormSubmitted($entry, $form_data)
    {
        $formId = $entry->form_id ?? null;
        $fields = $entry->records['fields'] ?? [];
        $blocks = $form_data['blocks'][0]['innerBlocks'] ?? [];

        if (!$formId || empty($fields) || empty($blocks)) {
            return;
        }

        $formData = ['form_id' => static::formatField('form_id', 'Form Id', $formId, 'number')];
        foreach ($blocks as $block) {
            $key = $block['id'];
            $label = $block['attributes']['label'] ?? $key;
            $type = $block['name'] ?? 'text';
            $type = str_replace(['short-', 'long-'], '', $type);
            $value = $fields[$key]['value'] ?? null;

            $formData[$key] = static::formatField($key, $label, $value, $type);
        }

        Helper::setTestData('btcbi_quillforms_after_entry_processed_test', array_values($formData), 'form_id.value', $formId);

        return static::flowExecute($formData);
    }

    private static function formatField($key, $label, $value, $type = 'text')
    {
        $labelValue = \is_string($value) && \strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;

        return [
            'name'  => "{$key}.value",
            'type'  => $type,
            'label' => "{$label} ({$labelValue})",
            'value' => $value
        ];
    }

    private static function flowExecute($formData)
    {
        $flows = Flow::exists('QuillForms', 'quillforms_after_entry_processed');
        if (empty($flows)) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('QuillForms', 'quillforms_after_entry_processed', array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return \defined('QUILLFORMS_PLUGIN_FILE');
    }
}
