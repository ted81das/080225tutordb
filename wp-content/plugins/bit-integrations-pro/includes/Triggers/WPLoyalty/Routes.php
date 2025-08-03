<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPLoyalty\WPLoyaltyController;

Route::get('wployalty/get', [WPLoyaltyController::class, 'getAllTasks']);
Route::post('wployalty/test', [WPLoyaltyController::class, 'getTestData']);
Route::post('wployalty/test/remove', [WPLoyaltyController::class, 'removeTestData']);
