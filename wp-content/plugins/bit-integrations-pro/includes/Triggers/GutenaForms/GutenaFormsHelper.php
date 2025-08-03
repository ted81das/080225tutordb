<?php

namespace BitApps\BTCBI_PRO\Triggers\GutenaForms;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class GutenaFormsHelper
{
    public static function isPrimaryKeysMatch($recordData, $flowDetails, $formIdField)
    {
        $recordData = array_merge($recordData, $formIdField);

        foreach ($flowDetails->primaryKey as $primaryKey) {
            $valueFromPath = Helper::extractValueFromPath($recordData, $primaryKey->key, 'GutenaForms');

            if (\is_array($valueFromPath)) {
                $valueFromPath = $valueFromPath['value'];
            }

            if ($primaryKey->value != $valueFromPath) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($formData, $formIdField)
    {
        $finalData = array_merge($formData, $formIdField);
        $data = [];

        foreach ($finalData as $key => $value) {
            $data[$key] = $value['value'];
        }

        return $data;
    }

    public static function setFields($formData, $formId)
    {
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => __('Form Id', 'bit-integrations-pro'), 'value' => $formId]
        ];

        foreach ($formData as $key => $data) {
            $allFields[] = [
                'name'  => $key,
                'type'  => $data['fieldType'],
                'label' => $data['label'],
                'value' => $data['value']
            ];
        }

        return $allFields;
    }
}
