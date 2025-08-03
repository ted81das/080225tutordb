<?php

/**
 * SendPulse    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\SendPulse;

use BitCode\FI\Core\Util\HttpHelper;

/**
 * Provide functionality for Record insert, upsert
 */
class SendPulseHelperPro
{
    public static function refreshFields($fields, $apiEndpoint, $token)
    {
        $headers = ['Authorization' => 'Bearer ' . $token];

        $sendPulseResponse = HttpHelper::get($apiEndpoint, null, $headers);

        if (!is_wp_error($sendPulseResponse)) {
            $allFields = $sendPulseResponse;

            foreach ($allFields as $field) {
                if (!\array_key_exists(ucfirst($field->name), $fields)) {
                    $fields[$field->name] = (object) [
                        'fieldName'  => $field->name,
                        'fieldValue' => $field->name,
                        'required'   => strtolower($field->name) == 'email' ? true : false
                    ];
                }
            }
        }

        return $fields;
    }
}
