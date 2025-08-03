<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AdvancedCoupons\AdvancedCouponsController;

Route::get('advanced_coupons/get', [AdvancedCouponsController::class, 'getAllTasks']);
Route::post('advanced_coupons/test', [AdvancedCouponsController::class, 'getTestData']);
Route::post('advanced_coupons/test/remove', [AdvancedCouponsController::class, 'removeTestData']);
