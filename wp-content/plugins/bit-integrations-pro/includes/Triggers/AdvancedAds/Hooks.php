<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AdvancedAds\AdvancedAdsController;

Hooks::add('transition_post_status', [AdvancedAdsController::class, 'handleAdStatusChanged'], 10, 3);
Hooks::add('advanced-ads-ad-status-draft-to-pending', [AdvancedAdsController::class, 'handleAdStatusDraftToPending'], 10, 1);
Hooks::add('advanced-ads-ad-status-draft-to-publish', [AdvancedAdsController::class, 'handleAdStatusDraftToPublish'], 10, 1);
Hooks::add('advanced-ads-ad-status-draft-to-advanced_ads_expired', [AdvancedAdsController::class, 'handleAdStatusDraftToExpired'], 10, 1);
Hooks::add('advanced-ads-ad-status-pending-to-draft', [AdvancedAdsController::class, 'handleAdStatusPendingToDraft'], 10, 1);
Hooks::add('advanced-ads-ad-status-pending-to-publish', [AdvancedAdsController::class, 'handleAdStatusPendingToPublish'], 10, 1);
Hooks::add('advanced-ads-ad-status-pending-to-advanced_ads_expired', [AdvancedAdsController::class, 'handleAdStatusPendingToExpired'], 10, 1);
Hooks::add('advanced-ads-ad-status-publish-to-draft', [AdvancedAdsController::class, 'handleAdStatusPublishToDraft'], 10, 1);
Hooks::add('advanced-ads-ad-status-publish-to-pending', [AdvancedAdsController::class, 'handleAdStatusPublishToPending'], 10, 1);
Hooks::add('advanced-ads-ad-status-publish-to-advanced_ads_expired', [AdvancedAdsController::class, 'handleAdStatusPublishToExpired'], 10, 1);
Hooks::add('advanced-ads-ad-status-advanced_ads_expired-to-publish', [AdvancedAdsController::class, 'handleAdStatusExpiredToPublish'], 10, 1);
Hooks::add('advanced-ads-ad-status-advanced_ads_expired-to-pending', [AdvancedAdsController::class, 'handleAdStatusExpiredToPending'], 10, 1);
Hooks::add('advanced-ads-ad-status-advanced_ads_expired-to-draft', [AdvancedAdsController::class, 'handleAdStatusExpiredToDraft'], 10, 1);
