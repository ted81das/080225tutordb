<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WpSimplePay\WpSimplePayController;

Route::get('wp_simple_pay/get', [WpSimplePayController::class, 'getAllTasks']);
Route::post('wp_simple_pay/test', [WpSimplePayController::class, 'getTestData']);
Route::post('wp_simple_pay/test/remove', [WpSimplePayController::class, 'removeTestData']);
