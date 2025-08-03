<?php

namespace BitApps\BTCBI_PRO\Triggers\WC;

class WCHelperPro
{
    public static function getFlexibleCheckoutFields()
    {
        return static::getFlexibleFields();
    }

    public static function getFlexibleCheckoutFieldsValue($fields)
    {
        return static::getFlexibleFields($fields);
    }

    private static function getFlexibleFields($fields = null)
    {
        $data = [];
        $checkoutFields = WC()->checkout()->get_checkout_fields();

        foreach ($checkoutFields as $groupKey => $group) {
            foreach ($group as $fieldKey => $field) {
                if (!empty($field['custom_field']) && $field['custom_field']) {
                    $fieldKey = $field['name'] ?? $fieldKey;

                    if (empty($fields)) {
                        $data[$fieldKey] = (object) [
                            'fieldKey'  => $fieldKey,
                            'fieldName' => $field['label']
                        ];
                    } elseif ($groupKey != 'shipping') {
                        $data[$fieldKey] = $fields[$fieldKey];
                    }
                }
            }
        }

        return $data;
    }
}
