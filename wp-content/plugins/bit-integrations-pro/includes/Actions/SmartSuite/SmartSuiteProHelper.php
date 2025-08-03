<?php

namespace BitApps\BTCBI_PRO\Actions\SmartSuite;

use BitCode\FI\Core\Util\HttpHelper;

class SmartSuiteProHelper
{
    public static function createTable($isGetResponse, $requestParams, $workspaceId, $apiToken, $selectedSolution)
    {
        if (empty($requestParams) || empty($workspaceId) || empty($apiToken)) {
            return (object) ['error' => 'Required params are empty!'];
        }
        $apiEndPoint = 'https://app.smartsuite.com/api/v1/applications/';
        if (isset($selectedSolution) && !empty($selectedSolution)) {
            $requestParams['solution'] = $selectedSolution;
        }
        $fieldStructure = [['slug' => 'name',
            'label'                => 'Name',
            'field_type'           => 'textfield']];
        $requestParams['structure'] = $fieldStructure;
        $response = HttpHelper::post($apiEndPoint, wp_json_encode($requestParams), static::setHeaders($workspaceId, $apiToken));

        return $response;
    }

    public static function createRecord($isGetResponse, $requestParams, $integrationDetails, $workspaceId, $apiToken)
    {
        if (empty($requestParams) || empty($integrationDetails) || empty($workspaceId) || empty($apiToken)) {
            return (object) ['error' => 'Required params are empty!'];
        }
        $tableId = $integrationDetails->selectedTable;

        if (!empty($integrationDetails->assigned_to)) {
            $requestParams['assigned_to'] = $integrationDetails->assigned_to;
        }
        if (!empty($integrationDetails->status)) {
            $requestParams['status'] = $integrationDetails->status;
        }
        if (!empty($integrationDetails->priority)) {
            $requestParams['priority'] = $integrationDetails->priority;
        }
        $apiEndPoint = "https://app.smartsuite.com/api/v1/applications/{$tableId}/records";
        $response = HttpHelper::post($apiEndPoint, wp_json_encode($requestParams), static::setHeaders($workspaceId, $apiToken));

        return $response;
    }

    private static function setHeaders($workspaceId, $apiToken)
    {
        return
            [
                'ACCOUNT-ID'    => $workspaceId,
                'Authorization' => 'Token ' . $apiToken,
                'Content-Type'  => 'application/json'
            ];
    }
}
