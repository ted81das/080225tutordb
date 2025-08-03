<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedAds;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\Helper;

class AdvancedAdsHelper
{
    public static function formatAdsData($postId, $ad, $newStatus = null, $oldStatus = null)
    {
        if (!static::isPluginInstalled()) {
            return;
        }

        $data = Post::get($postId);
        $data['ad_id'] = $postId;

        if (!empty($newStatus) && !empty($oldStatus)) {
            $data['ad_old_status'] = $newStatus;
            $data['ad_new_status'] = $oldStatus;
        } elseif (property_exists($ad, 'status')) {
            $data['ad_status'] = $ad->status;
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('Advanced_Ads');
    }
}
