<?php

namespace BitApps\BTCBI_PRO\Triggers\Bricksforge;

use Bricksforge\Api\FormsHelper;
use BitApps\BTCBI_PRO\Core\Util\Helper;

class BricksforgeHelper
{
    public static function isPrimaryKeysMatch($recordData, $flowDetails)
    {
        $finalData = self::prepareDataForFlow($recordData);

        foreach ($flowDetails->primaryKey as $primaryKey) {
            $valueFromPath = Helper::extractValueFromPath($finalData, $primaryKey->key, 'Bricksforge');

            if ($primaryKey->value != $valueFromPath) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($formData)
    {
        $finalData = ['id' => $formData['formId']];

        $fieldIds = json_decode($formData['fieldIds'], true);

        $bricksforgeFH = new FormsHelper();

        foreach ($fieldIds as $key => $fieldId) {
            if (isset($formData['form-field-' . $key]) && \is_array($formData['form-field-' . $key])
            && isset($formData['form-field-' . $key]['file'])) {
                $finalData[$key] = $formData['form-field-' . $key]['url'];
            } else {
                $fieldValue = $bricksforgeFH->get_form_field_by_id($key, $formData);

                $finalData[$key] = $fieldValue;
            }
        }

        return $finalData;
    }

    public static function setFields($formData)
    {
        $fieldLabels = json_decode($formData['fieldLabels'], true);

        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => \sprintf(__('Form Id (%s)', 'bit-integrations-pro'), $formData['formId']), 'value' => $formData['formId']]
        ];

        $bricksforgeFH = new FormsHelper();

        if (!empty($fieldLabels)) {
            foreach ($fieldLabels as $key => $fieldLabel) {
                if (isset($formData['form-field-' . $key]) && \is_array($formData['form-field-' . $key])
                && isset($formData['form-field-' . $key]['file'])) {
                    $allFields[] = [
                        'name'  => $key,
                        'type'  => 'file',
                        'label' => preg_replace('/\s+/', ' ', trim($fieldLabel)),
                        'value' => $formData['form-field-' . $key]['url']
                    ];
                } else {
                    $fieldValue = $bricksforgeFH->get_form_field_by_id($key, $formData);

                    $allFields[] = [
                        'name'  => $key,
                        'type'  => is_email($fieldValue) ? 'email' : 'text',
                        'label' => preg_replace('/\s+/', ' ', trim($fieldLabel)),
                        'value' => $fieldValue
                    ];
                }
            }
        }

        return $allFields;
    }
}
