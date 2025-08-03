<?php

/**
 * Klaviyo    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\Klaviyo;

use BitCode\FI\Core\Util\HttpHelper;

/**
 * Provide functionality for Record insert, upsert
 */
class KlaviyoHelperPro
{
    public static function setCustomProperties($requestParams, $customProperties, $fieldValues)
    {
        $properties = [];

        foreach ($customProperties as $property) {
            $triggerValue = $property->formField;
            $actionValue = $property->klaviyoFormField;

            if ($triggerValue === 'custom') {
                $properties[$actionValue] = \BitCode\FI\Core\Util\Common::replaceFieldWithValue($property->customValue, $fieldValues);
            } elseif (isset($fieldValues[$triggerValue])) {
                $properties[$actionValue] = $fieldValues[$triggerValue];
            }
        }

        if (!empty($properties)) {
            $requestParams['data']['attributes']['properties'] = $properties;
        }

        return $requestParams;
    }

    public static function updateProfile($value, $id, $authKey, $data)
    {
        $headers = [
            'Authorization' => "Klaviyo-API-Key {$authKey}",
            'Content-Type'  => 'application/json',
            'accept'        => 'application/json',
            'revision'      => '2024-02-15'
        ];

        $apiEndpoints = "https://a.klaviyo.com/api/profiles/{$id}";
        $data['data']['id'] = $id;

        return HttpHelper::request($apiEndpoints, 'PATCH', wp_json_encode($data), $headers);
    }
}
