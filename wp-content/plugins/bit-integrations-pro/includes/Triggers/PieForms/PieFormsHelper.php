<?php

namespace BitApps\BTCBI_PRO\Triggers\PieForms;

class PieFormsHelper
{
    public static function formatFields($form_id, $form_data)
    {
        if (empty($form_data)) {
            return;
        }

        $data = [
            'form_id' => [
                'name'  => 'form_id.value',
                'type'  => 'text',
                'label' => __('Form Id', 'bit-integrations-pro') . '(' . $form_id . ')',
                'value' => $form_id
            ]
        ];

        foreach ($form_data as $key => $value) {
            $data[$key] = [
                'name'  => "{$key}.value",
                'type'  => 'text',
                'label' => "{$value['name']} ({$value['value']})",
                'value' => $value['value']
            ];
        }

        return $data;
    }

    public static function isPluginInstalled()
    {
        return \defined('PF_PLUGIN_FILE');
    }
}
