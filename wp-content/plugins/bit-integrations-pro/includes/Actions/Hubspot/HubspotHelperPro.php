<?php

/**
 * Hubspot    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\Hubspot;

use BitCode\FI\Core\Util\HttpHelper;

/**
 * Provide functionality for Record insert, upsert
 */
class HubspotHelperPro
{
    public static function updateEntity($id, $finalData, $actionName, $defaultHeader)
    {
        $apiEndpoint = "https://api.hubapi.com/crm/v3/objects/{$actionName}/{$id}";

        return HttpHelper::request($apiEndpoint, 'PATCH', wp_json_encode($finalData), $defaultHeader);
    }
}
