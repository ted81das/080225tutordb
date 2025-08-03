<?php

/**
 * LMFWC    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\LMFWC;

use BitCode\FI\Core\Util\HttpHelper;

/**
 * Provide functionality for Record insert, upsert
 */
class LMFWCProHelper
{
    public static function updateLicense($state, $finalData, $apiUrl, $integrationDetails, $defaultHeader)
    {
        if (isset($integrationDetails->selectedStatus) || !empty($integrationDetails->selectedStatus)) {
            $finalData['status'] = $integrationDetails->selectedStatus;
        }
        if (isset($integrationDetails->selectedCustomer) || !empty($integrationDetails->selectedCustomer)) {
            $finalData['user_id'] = $integrationDetails->selectedCustomer;
        }
        if (isset($integrationDetails->selectedOrder) || !empty($integrationDetails->selectedOrder)) {
            $finalData['order_id'] = $integrationDetails->selectedOrder;
        }
        if (isset($integrationDetails->selectedProduct) || !empty($integrationDetails->selectedProduct)) {
            $finalData['product_id'] = $integrationDetails->selectedProduct;
        }

        $apiEndpoint = $apiUrl . '/licenses/' . $integrationDetails->selectedLicense;

        return HttpHelper::put($apiEndpoint, wp_json_encode($finalData), $defaultHeader, ['sslverify' => false]);
    }

    public static function activateLicense($state, $apiUrl, $licenseKey, $defaultHeader)
    {
        $apiEndpoint = $apiUrl . '/licenses/activate/' . $licenseKey;

        return HttpHelper::get($apiEndpoint, null, $defaultHeader, ['sslverify' => false]);
    }

    public static function createGenerator($state, $apiUrl, $finalData, $defaultHeader)
    {
        $apiEndpoint = $apiUrl . '/generators/';

        return HttpHelper::post($apiEndpoint, wp_json_encode($finalData), $defaultHeader, ['sslverify' => false]);
    }

    public static function UpdateGenerator($state, $apiUrl, $finalData, $defaultHeader, $id)
    {
        $apiEndpoint = $apiUrl . '/generators/' . $id;

        return HttpHelper::put($apiEndpoint, wp_json_encode($finalData), $defaultHeader, ['sslverify' => false]);
    }

    public static function deactivateLicense($state, $apiUrl, $licenseKey, $defaultHeader, $token = null)
    {
        if (!empty($token)) {
            $apiEndpoint = $apiUrl . '/licenses/deactivate/' . $licenseKey . '?token=' . $token;
        } else {
            $apiEndpoint = $apiUrl . '/licenses/deactivate/' . $licenseKey;
        }

        return HttpHelper::get($apiEndpoint, null, $defaultHeader, ['sslverify' => false]);
    }

    public static function reactivateLicense($state, $apiUrl, $licenseKey, $defaultHeader, $token = null)
    {
        $apiEndpoint = $apiUrl . '/licenses/activate/' . $licenseKey;
        if (!empty($token)) {
            $apiEndpoint .= '?token=' . $token;
        }

        return HttpHelper::get($apiEndpoint, null, $defaultHeader, ['sslverify' => false]);
    }

    public static function deleteLicense($state, $apiUrl, $licenseKey, $defaultHeader)
    {
        $apiEndpoint = $apiUrl . '/licenses/' . $licenseKey;

        return HttpHelper::request($apiEndpoint, 'DELETE', null, $defaultHeader, ['sslverify' => false]);
    }
}
