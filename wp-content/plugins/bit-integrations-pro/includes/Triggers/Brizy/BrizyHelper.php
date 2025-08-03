<?php

namespace BitApps\BTCBI_PRO\Triggers\Brizy;

use BitApps\BTCBI_PRO\Core\Util\Common;
use BitApps\BTCBI_PRO\Core\Util\Helper;

class BrizyHelper
{
    public static function extractRecordData($fields, $formId)
    {
        return [
            'id'     => $formId,
            'fields' => $fields
        ];
    }

    public static function setFields($formData)
    {
        // Create a mapping for quick access
        $id = \is_string($formData['id']) && \strlen($formData['id']) > 20 ? substr($formData['id'], 0, 20) . '...' : $formData['id'];
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => \sprintf(__('Form Id (%s)', 'bit-integrations-pro'), $id), 'value' => $formData['id']],
        ];

        // Process fields fields
        foreach ($formData['fields'] as $key => $field) {
            if ($field->type != 'FileUpload' && $field->type != 'checkbox') {
                if (!\is_string($field->value)) {
                    $labelValue = $field->type;
                } else {
                    $labelValue = \strlen($field->value) > 20 ? substr($field->value, 0, 20) . '...' : $field->value;
                }

                $allFields[] = [
                    'name'  => "fields.{$key}.value",
                    'type'  => $field->type,
                    'label' => $field->label . ' (' . $labelValue . ')',
                    'value' => $field->value
                ];
            } elseif ($field->type == 'checkbox') {
                $allFields[] = [
                    'name'  => "fields.{$key}.value",
                    'type'  => $field->type,
                    'label' => $field->label . ' (checkbox)',
                    'value' => explode(',', $field->value)
                ];
            } elseif ($field->type == 'FileUpload') {
                $allFields[] = [
                    'name'  => "fields.{$key}.value",
                    'type'  => $field->type,
                    'label' => $field->label . ' (File)',
                    'value' => Common::filePath($field->value)
                ];
            }
        }

        return $allFields;
    }

    public static function parseOldIntegrationsData($fields)
    {
        $data = [];

        foreach ($fields as $element) {
            if ($element->type == 'FileUpload' && !empty($element->value)) {
                $upDir = wp_upload_dir();
                $files = $element->value;
                $value = [];
                $newFileLink = Common::filePath($files);
                $data[$element->name] = $newFileLink;
            } elseif ($element->type == 'checkbox') {
                $value = explode(',', $element->value);
                $data[$element->name] = $value;
            } else {
                $data[$element->name] = $element->value;
            }
        }

        return $data;
    }

    public static function fetchFlows($formId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}btcbi_flow
                WHERE status = true 
                AND triggered_entity = %s 
                AND (triggered_entity_id = %s
                OR triggered_entity_id = %s)",
                'Brizy',
                'brizy_form_submit_data',
                $formId
            )
        );
    }

    public static function isPrimaryKeysMatch($recordData, $flowDetails)
    {
        foreach ($flowDetails->primaryKey as $primaryKey) {
            if ($primaryKey->value != Helper::extractValueFromPath($recordData, $primaryKey->key, 'Brizy')) {
                return false;
            }
        }

        return true;
    }

    public static function isPluginInstalled()
    {
        return is_plugin_active('brizy/brizy.php');
    }
}
