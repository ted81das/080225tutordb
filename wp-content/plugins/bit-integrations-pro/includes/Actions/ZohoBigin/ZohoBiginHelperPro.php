<?php

namespace BitApps\BTCBI_PRO\Actions\ZohoBigin;

use BitCode\FI\Core\Util\HttpHelper;

class ZohoBiginHelperPro
{
    public static function getTagList($tags, $accessToken, $dataCenter, $module)
    {
        $tagsMetaApiEndpoint = "http://www.zohoapis.{$dataCenter}/bigin/v1/settings/tags?module={$module}";
        $authorizationHeader['Authorization'] = "Zoho-oauthtoken {$accessToken}";
        $tagsMetaResponse = HttpHelper::get($tagsMetaApiEndpoint, null, $authorizationHeader);

        if (!is_wp_error($tagsMetaResponse)) {
            return array_column($tagsMetaResponse->tags ?? $tags, 'name');
        }

        return $tags;
    }

    public static function addTagsToRecords($recordID, $module, $tags, $apiDomain, $defaultHeader)
    {
        $tagsMetaApiEndpoint = "{$apiDomain}/bigin/v1/{$module}/actions/add_tags?ids={$recordID}&tag_names={$tags}";

        return HttpHelper::post($tagsMetaApiEndpoint, null, $defaultHeader);
    }
}
