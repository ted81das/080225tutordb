<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AdvancedAds\AdvancedAdsController;

Route::get('advanced_ads/get', [AdvancedAdsController::class, 'getAllTasks']);
Route::post('advanced_ads/test', [AdvancedAdsController::class, 'getTestData']);
Route::post('advanced_ads/test/remove', [AdvancedAdsController::class, 'removeTestData']);
