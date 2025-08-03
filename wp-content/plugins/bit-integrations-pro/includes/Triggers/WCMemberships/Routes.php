<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WCMemberships\WCMembershipsController;

Route::get('woocommerce_memberships/get', [WCMembershipsController::class, 'getAllTasks']);
Route::post('woocommerce_memberships/test', [WCMembershipsController::class, 'getTestData']);
Route::post('woocommerce_memberships/test/remove', [WCMembershipsController::class, 'removeTestData']);
