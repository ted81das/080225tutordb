<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedAds;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AdvancedAdsController
{
    public static function info()
    {
        return [
            'name'              => 'Advanced Ads',
            'title'             => __('A Powerful WordPress Ad Management Plugin. Advanced Ads is a great plugin that makes it easier to manage your ads.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AdvancedAdsHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/advanced-ads-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'advanced_ads/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'advanced_ads/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'advanced_ads/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AdvancedAdsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Advanced Ads'));
        }

        wp_send_json_success(StaticData::forms());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleAdStatusChanged($ad_new_status, $ad_old_status, $ad)
    {
        if (empty($ad->ID)) {
            return;
        }

        return static::flowExecute('transition_post_status', $ad->ID, $ad, $ad_new_status, $ad_old_status);
    }

    public static function handleAdStatusDraftToPending($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-draft-to-pending', $ad->id, $ad);
    }

    public static function handleAdStatusDraftToPublish($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-draft-to-publish', $ad->id, $ad);
    }

    public static function handleAdStatusDraftToExpired($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-draft-to-advanced_ads_expired', $ad->id, $ad);
    }

    public static function handleAdStatusPendingToDraft($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-pending-to-draft', $ad->id, $ad);
    }

    public static function handleAdStatusPendingToPublish($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-pending-to-publish', $ad->id, $ad);
    }

    public static function handleAdStatusPendingToExpired($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-pending-to-advanced_ads_expired', $ad->id, $ad);
    }

    public static function handleAdStatusPublishToDraft($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-publish-to-draft', $ad->id, $ad);
    }

    public static function handleAdStatusPublishToPending($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-publish-to-pending', $ad->id, $ad);
    }

    public static function handleAdStatusPublishToExpired($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-publish-to-advanced_ads_expired', $ad->id, $ad);
    }

    public static function handleAdStatusExpiredToPublish($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-advanced_ads_expired-to-publish', $ad->id, $ad);
    }

    public static function handleAdStatusExpiredToPending($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-advanced_ads_expired-to-pending', $ad->id, $ad);
    }

    public static function handleAdStatusExpiredToDraft($ad)
    {
        if (empty($ad->id)) {
            return;
        }

        return static::flowExecute('advanced-ads-ad-status-advanced_ads_expired-to-draft', $ad->id, $ad);
    }

    private static function flowExecute($triggered_entity_id, $postId, $ad, $newStatus = null, $oldStatus = null)
    {
        $formData = AdvancedAdsHelper::formatAdsData($postId, $ad, $newStatus, $oldStatus);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('AdvancedAds', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('AdvancedAds', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
