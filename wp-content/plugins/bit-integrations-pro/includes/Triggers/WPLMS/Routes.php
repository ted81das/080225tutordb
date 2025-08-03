<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPLMS\WPLMSController;

Route::get('wplms/get', [WPLMSController::class, 'getAllTasks']);
Route::post('wplms/test', [WPLMSController::class, 'getTestData']);
Route::post('wplms/test/remove', [WPLMSController::class, 'removeTestData']);
