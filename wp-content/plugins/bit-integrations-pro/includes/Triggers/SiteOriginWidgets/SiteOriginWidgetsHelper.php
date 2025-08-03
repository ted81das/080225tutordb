<?php

namespace BitApps\BTCBI_PRO\Triggers\SiteOriginWidgets;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class SiteOriginWidgetsHelper
{
    public static function isPrimaryKeysMatch($recordData, $flowDetails, $formIdField)
    {
        $finalData = self::prepareDataForFlow($recordData, $formIdField);

        foreach ($flowDetails->primaryKey as $primaryKey) {
            $valueFromPath = Helper::extractValueFromPath($finalData, $primaryKey->key, 'SiteOriginWidgets');

            if ($primaryKey->value != $valueFromPath) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($formData, $formIdField)
    {
        $mergedData = array_merge($formData, $formIdField);
        $finalData = [];

        foreach ($mergedData as $key => $item) {
            if ($key === 'message') {
                foreach ($item as $msgItem) {
                    if (!empty($msgItem['label'])) {
                        $finalDataKey = str_replace(' ', '_', $msgItem['label']);
                        $finalData[$finalDataKey] = $msgItem['value'];
                    }
                }
            } else {
                $finalData[$key] = $item;
            }
        }

        return $finalData;
    }

    public static function setFields($formFields, $formId, $fieldsValue)
    {
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => __('Form Id', 'bit-integrations-pro'), 'value' => $formId]
        ];

        $defaultfields = ['name', 'email', 'subject', 'number'];

        foreach ($formFields as $formField) {
            if (\in_array($formField['type'], $defaultfields)) {
                $name = $formField['type'];
                $fldvalue = $fieldsValue[$name];
            } else {
                $name = $formField['label'];
                if (isset($fieldsValue['message']) && \is_array($fieldsValue['message'])) {
                    foreach ($fieldsValue['message'] as $msgValue) {
                        if ($msgValue['label'] === $name) {
                            $fldvalue = $msgValue['value'];
                        }
                    }
                }
            }

            if (empty($name) && !\in_array($formField['type'], $defaultfields)) {
                continue;
            }

            $allFields[$name] = [
                'name'  => str_replace(' ', '_', $name),
                'type'  => $formField['type'],
                'label' => !empty($formField['label']) ? $formField['label'] : $name,
                'value' => !empty($fldvalue) ? $fldvalue : ''
            ];
        }

        return array_values($allFields);
    }
}
