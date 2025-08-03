<?php

namespace BitApps\BTCBI_PRO\Triggers\PopupMaker;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class PopupMakerHelper
{
    private static $fieldskey = ['name', 'fname', 'lname', 'email', 'consent'];

    public static function isPrimaryKeysMatch($recordData, $flowDetails)
    {
        $finalData = self::prepareDataForFlow($recordData);

        foreach ($flowDetails->primaryKey as $primaryKey) {
            $valueFromPath = Helper::extractValueFromPath($finalData, $primaryKey->key, 'PopupMaker');

            if ($primaryKey->value != $valueFromPath) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($formData)
    {
        $finalData = ['id' => (string) $formData['popup_id']];

        foreach ($formData as $key => $item) {
            if (\in_array($key, self::$fieldskey)) {
                $finalData[$key] = $item;
            }
        }

        return $finalData;
    }

    public static function setFields($formData)
    {
        if (empty($formData)) {
            return [];
        }

        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => __('Popup Id', 'bit-integrations-pro'), 'value' => (string) $formData['popup_id']]
        ];

        foreach ($formData as $key => $item) {
            if (\in_array($key, self::$fieldskey)) {
                $allFields[] = [
                    'name'  => $key,
                    'type'  => is_email($item) ? 'email' : 'text',
                    'label' => self::getLavelByKey($key),
                    'value' => $item,
                ];
            }
        }

        return $allFields;
    }

    public static function getLavelByKey($key)
    {
        if ($key === 'fname') {
            return 'First Name';
        } elseif ($key === 'lname') {
            return 'Last Name';
        }

        return ucfirst($key);
    }
}
