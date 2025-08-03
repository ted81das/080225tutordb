<?php

namespace BitApps\BTCBI_PRO\Triggers\CalculatedFieldsForm;

class CalculatedFieldsFormHelper
{
    public static function formatFormData($params, $fields)
    {
        $data = [
            'form_id' => [
                'name'  => 'form_id.value',
                'type'  => 'number',
                'label' => 'Form Id (' . $params['formid'] . ')',
                'value' => $params['formid'],
            ]
        ];

        foreach ($fields as $key => $field) {
            if ($field->ftype === 'fSectionBreak') {
                continue;
            }

            $value = $params[$key] ?? null;
            $value = \is_string($value) && \strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
            $labelValue = \is_array($value) ? 'array' : $value;

            $data[$key] = [
                'name'  => $key . '.value',
                'type'  => static::setFieldType($field->ftype),
                'label' => $field->title . (empty($labelValue) ? '' : ' (' . $labelValue . ')'),
                'value' => $value,
            ];
        }

        return $data;
    }

    public static function isPluginInstalled()
    {
        return is_plugin_active('calculated-fields-form/cp_calculatedfieldsf_pro.php');
    }

    private static function setFieldType($type)
    {
        switch ($type) {
            case 'fCalculated':
                return 'number';
            case 'fcheck':
                return 'array';
            default:
                return substr($type, 1);
        }
    }
}
