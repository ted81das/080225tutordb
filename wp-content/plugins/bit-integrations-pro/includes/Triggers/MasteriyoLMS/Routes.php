<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\MasteriyoLMS\MasteriyoLMSController;

Route::get('masteriyo/get', [MasteriyoLMSController::class, 'getAllTasks']);
Route::post('masteriyo/test', [MasteriyoLMSController::class, 'getTestData']);
Route::post('masteriyo/test/remove', [MasteriyoLMSController::class, 'removeTestData']);
