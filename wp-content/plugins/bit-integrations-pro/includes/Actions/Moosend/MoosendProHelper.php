<?php

/**
 * Moosend    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\Moosend;

use BitCode\FI\Actions\Moosend\MoosendHelper;
use BitCode\FI\Core\Util\Common;

/**
 * Provide functionality for Record insert, upsert
 */
class MoosendProHelper
{
    public static function mapCustomFields($dataFinal, $data, $field_map)
    {
        $dataFinal['CustomFields'] = [];

        foreach ($field_map as $key => $value) {
            $triggerValue = $value->formFields;
            $actionValue = $value->moosendFormFields;

            $formattedValue = isset($data[$triggerValue]) ? MoosendHelper::formatPhoneNumber($data[$triggerValue]) : null;

            if (strpos($actionValue, 'custom_field_') === 0) {
                $actionValue = str_replace('custom_field_', '', $actionValue);
                $dataFinal['CustomFields'][] = "{$actionValue}=" . ($triggerValue === 'custom'
                    ? Common::replaceFieldWithValue($value->customValue, $data)
                    : $formattedValue);
            }
        }

        if (empty($dataFinal['CustomFields'])) {
            unset($dataFinal['CustomFields']);
        }

        return $dataFinal;
    }
}
