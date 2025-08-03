<?php

namespace BitApps\BTCBI_PRO\Triggers\Divi;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class DiviHelper
{
    public static function extractRecordData($record, $et_pb_contact_form_submit)
    {
        return [
            'id'      => $record['contact_form_unique_id'],
            'post_id' => $record['post_id'],
            'fields'  => $et_pb_contact_form_submit
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
                'Divi',
                'et_pb_contact_form_submit',
                $formId,
                $reOrganizeId
            )
        );
    }

    public static function isPrimaryKeysMatch($recordData, $flowDetails)
    {
        foreach ($flowDetails->primaryKey as $primaryKey) {
            if ($primaryKey->value != Helper::extractValueFromPath($recordData, $primaryKey->key, 'Divi')) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($fields)
    {
        $data = [];
        foreach ($fields as $key => $field) {
            $data[$key] = $field['value'];
        }

        return $data;
    }

    public static function setFields($formData)
    {
        $id = \is_string($formData['id']) && \strlen($formData['id']) > 20 ? substr($formData['id'], 0, 20) . '...' : $formData['id'];
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => \sprintf(__('Form Id (%s)', 'bit-integrations-pro'), $id), 'value' => $formData['id']],
            ['name' => 'post_id', 'type' => 'text', 'label' => \sprintf(__('Post Id (%s)', 'bit-integrations-pro'), $formData['post_id']), 'value' => $formData['post_id']],
        ];

        // Process fields data
        foreach ($formData['fields'] as $key => $field) {
            $labelValue = \is_string($field['value']) && \strlen($field['value']) > 20 ? substr($field['value'], 0, 20) . '...' : $field['value'];
            $labelValue = \is_array($labelValue) ? 'array' : $labelValue;

            $allFields[] = [
                'name'  => "fields.{$key}.value",
                'type'  => 'text',
                'label' => $field['label'] . ' (' . $labelValue . ')',
                'value' => $field['value']
            ];
        }

        return $allFields;
    }
}
