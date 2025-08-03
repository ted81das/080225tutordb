<?php

/**
 * OmniSend Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\OmniSend;

class OmniSendHelperPro
{
    public static function setCustomProperties($requestParams, $customProperties, $fieldValues)
    {
        $properties = [];

        foreach ($customProperties as $property) {
            $triggerValue = $property->formField;
            $actionValue = $property->omniSendFormField;

            $properties[$actionValue] = ($triggerValue === 'custom' && !empty($property->customValue))
                ? \BitCode\FI\Core\Util\Common::replaceFieldWithValue($property->customValue, $fieldValues)
                : $fieldValues[$triggerValue] ?? null;
        }

        return (object) $properties;
    }
}
