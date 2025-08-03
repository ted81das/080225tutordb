<?php

/**
 * FreshSales    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\FreshSales;

use BitCode\FI\Core\Util\HttpHelper;

/**
 * Provide functionality for Record insert, upsert
 */
class FreshSalesRecordApiHelper
{
    public static function upsertRecord($module, $finalData, $integrationDetails, $defaultHeader, $baseUrl)
    {
        if ($module === 'contact') {
            $finalData['sales_accounts'] = [(object) ['id' => $integrationDetails->moduleData->account_id, 'is_primary' => true]];
            $identifier = (object) ['emails' => $finalData['emails']];
        }

        if ($module === 'deal') {
            $finalData['contacts_added_list'] = [$integrationDetails->moduleData->contact_id];
            $identifier = (object) ['emails' => $finalData['emails']];
        }

        if ($module === 'account') {
            $module = 'sales_account';
            $identifier = (object) ['name' => $finalData['name']];
        }

        $body = wp_json_encode(['unique_identifier' => $identifier, $module => $finalData]);
        $apiEndpoints = 'https://' . $baseUrl . '/api/' . $module . 's/upsert';

        return HttpHelper::post($apiEndpoints, $body, $defaultHeader);
    }
}
