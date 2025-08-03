<?php

namespace BitApps\BTCBI_PRO\Actions\PipeDrive;

use BitCode\FI\Core\Util\Common;
use BitCode\FI\Core\Util\HttpHelper;
use BitCode\FI\Log\LogHandler;

class PipeDriveHelperPro
{
    public static function addRelatedList($pipeDriveApiResponse, $integrationDetails, $fieldValues, $parentModule, $apiKey, $integrationID)
    {
        $parentId = $pipeDriveApiResponse->data->id;

        foreach ($integrationDetails->relatedlists as $item) {
            $module = strtolower($item->module);
            $finalData = static::generateReqDataFromFieldMap($fieldValues, $item->field_map);

            $mapFields = [
                'activities_type' => 'type',
                'lead_label'      => 'label_ids',
                'deal_stage'      => 'stage_id',
                'deal_status'     => 'status',
                'currency'        => 'currency',
                'visible_to'      => 'visible_to',
                'busy_flag'       => 'busy_flag',
                'active_flag'     => 'active_flag'
            ];

            foreach ($mapFields as $moduleKey => $finalDataKey) {
                if (!empty($item->moduleData->{$moduleKey}) && $moduleKey === 'lead_label') {
                    $finalData[$finalDataKey] = explode(',', $item->moduleData->{$moduleKey});
                } elseif (!empty($item->moduleData->{$moduleKey})) {
                    $finalData[$finalDataKey] = $item->moduleData->{$moduleKey};
                }
            }

            if (!empty($item->actions->activities_participants)) {
                $finalData['participants'] = array_map(function ($participant) {
                    return (object) [
                        'person_id'    => (int) $participant,
                        'primary_flag' => false
                    ];
                }, explode(',', $item->moduleData->activities_participants));
            }

            switch ($parentModule) {
                case 'leads':
                    $finalData['lead_id'] = $parentId;

                    break;
                case 'deals':
                    $finalData['deal_id'] = (int) $parentId;

                    break;
                case 'organizations':
                    $finalData[$module === 'leads' ? 'organization_id' : 'org_id'] = (int) $parentId;

                    break;
                case 'persons':
                    $finalData['person_id'] = (int) $parentId;

                    break;
            }

            $apiEndpoints = 'https://api.pipedrive.com/v1/' . $module . '?api_token=' . $apiKey;
            $apiResponse = HttpHelper::post($apiEndpoints, wp_json_encode($finalData), ['content-type' => 'application/json']);

            $logType = isset($apiResponse->error) ? 'error' : 'success';
            LogHandler::save($integrationID, wp_json_encode([
                'type'      => $parentModule,
                'type_name' => 'add-related-list-' . $module
            ]), $logType, wp_json_encode($apiResponse));
        }
    }

    private static function generateReqDataFromFieldMap($data, $fieldMap)
    {
        $dataFinal = [];
        foreach ($fieldMap as $key => $value) {
            $triggerValue = $value->formField;
            $actionValue = $value->pipeDriveFormField;
            if ($triggerValue === 'custom') {
                $dataFinal[$actionValue] = Common::replaceFieldWithValue($value->customValue, $data);
            } elseif (!\is_null($data[$triggerValue])) {
                $dataFinal[$actionValue] = \is_array($data[$triggerValue]) ? implode(',', $data[$triggerValue]) : $data[$triggerValue];
            }
        }

        return $dataFinal;
    }
}
