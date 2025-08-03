<?php

/**
 * MailChimp Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\MailChimp;

use BitApps\BTCBI_PRO\Core\Util\HttpHelper;
use BitCode\FI\Log\LogHandler;

/**
 * Provide functionality for Record insert,upsert
 */
class MailChimpRecordHelper
{
    public static function addRemoveTag($module, $data, $endpoint, $defaultHeader)
    {
        $tags = [];
        $isActive = $module == 'add_tag_to_a_member';

        foreach ($data['tags'] as $value) {
            $tags['tags'][] = ['name' => $value, 'status' => $isActive ? 'active' : 'inactive'];
        }

        return HttpHelper::post($endpoint, wp_json_encode($tags), $defaultHeader);
    }

    public static function mapLanguageField($fieldData, $integrationDetails)
    {
        if (isset($integrationDetails->selectedLanguage)) {
            $fieldData['language'] = $integrationDetails->selectedLanguage;
        }

        return $fieldData;
    }

    public static function updateGDPRPermissions($subscriber, $gdprPermissions, $listId, $endPoint, $defaultHeader, $integrationID)
    {
        if (empty($subscriber) || empty($subscriber->id) || empty($gdprPermissions)) {
            return;
        }

        $data = [];
        $gdprPermissions = array_map('trim', explode(',', $gdprPermissions));

        foreach ($gdprPermissions as $value) {
            foreach ($subscriber->marketing_permissions as $permission) {
                if (strtolower($permission->text) === strtolower($value)) {
                    $data[] = [
                        'marketing_permission_id' => $permission->marketing_permission_id,
                        'enabled'                 => true
                    ];
                }
            }
        }

        if (!empty($data)) {
            $updateRecordEndpoint = $endPoint . "/lists/{$listId}/members/{$subscriber->id}";
            $data = ['marketing_permissions' => $data];

            $response = HttpHelper::request($updateRecordEndpoint, 'PUT', wp_json_encode($data), $defaultHeader);

            $logType = isset($response->status) && ($response->status === 400 || $response->status === 404) ? 'error' : 'success';

            LogHandler::save($integrationID, ['type' => 'GDPR', 'type_name' => 'Add GDPR'], $logType, wp_json_encode($response));
        }
    }
}
