<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WCSubscriptions\WCSubscriptionsController;

Route::get('wcsubscriptions/get', [WCSubscriptionsController::class, 'getAll']);
Route::post('wcsubscriptions/get/form', [WCSubscriptionsController::class, 'get_a_form']);
Route::get('wcsubscriptions/get/subscriptions', [WCSubscriptionsController::class, 'getAllSubscriptions']);
Route::post('wcsubscriptions/get/subscription-products', [WCSubscriptionsController::class, 'getAllSubscriptionsProducts']);
