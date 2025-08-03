<?php

namespace BitApps\BTCBI_PRO\Triggers\Kadence;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class KadenceHelper
{
    public static function extractRecordData($formId, $postId, $fields, $formData)
    {
        return [
            'id'           => $formId,
            'form_post_id' => $postId,
            'fields'       => $fields,
            'formData'     => $formData
        ];
    }

    public static function fetchFlows($formId, $reOrganizeId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}btcbi_flow
                WHERE status = true 
                AND triggered_entity = %s 
                AND (triggered_entity_id = %s
                OR triggered_entity_id = %s
                OR triggered_entity_id = %s)",
                'Kadence',
                'kadence_blocks_form',
                $formId,
                $reOrganizeId
            )
        );
    }

    public static function isPrimaryKeysMatch($recordData, $flowDetails)
    {
        foreach ($flowDetails->primaryKey as $primaryKey) {
            if ($primaryKey->value != Helper::extractValueFromPath($recordData, $primaryKey->key, 'Kadence Block')) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($fields)
    {
        $data = [];
        foreach ($fields as $key => $field) {
            $data['kb_field_' . $key] = $field['value'];
        }

        return $data;
    }

    public static function setFields($formData)
    {
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => \sprintf(__('Form Id (%s)', 'bit-integrations-pro'), $formData['id']), 'value' => $formData['id']],
        ];

        if (!empty($formData['form_post_id'])) {
            $allFields[] = ['name' => 'form_post_id', 'type' => 'text', 'label' => \sprintf(__('Form Post Id (%s)', 'bit-integrations-pro'), $formData['form_post_id']), 'value' => $formData['form_post_id']];
        }

        $mapData = self::formDataMapByLabel($formData['formData']);

        // Process fields data
        foreach ($formData['fields'] as $key => $field) {
            $value = $mapData[strtolower($field['label'])];

            if ($field['type'] == 'checkbox' && \is_string($value)) {
                $value = explode(',', $value);
            }

            $labelValue = \is_string($value) && \strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
            $labelValue = \is_array($labelValue) ? 'array' : (!empty($labelValue) ? $labelValue : 'null');

            $allFields[] = [
                'name'  => "fields.{$key}.value",
                'type'  => $field['type'] == 'checkbox' ? 'array' : $field['type'],
                'label' => $field['label'] . ' (' . $labelValue . ')',
                'value' => $value
            ];
        }

        return $allFields;
    }

    private static function formDataMapByLabel($formData)
    {
        $map = [];
        foreach ($formData as $item) {
            if (isset($item['label'])) {
                $map[strtolower($item['label'])] = $item['value'] ?? null;
            }
        }

        return $map;
    }
}
