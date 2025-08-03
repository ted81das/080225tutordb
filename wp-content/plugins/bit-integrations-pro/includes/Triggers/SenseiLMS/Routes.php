<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SenseiLMS\SenseiLMSController;

Route::get('sensei_lms/get', [SenseiLMSController::class, 'getAllTasks']);
Route::post('sensei_lms/test', [SenseiLMSController::class, 'getTestData']);
Route::post('sensei_lms/test/remove', [SenseiLMSController::class, 'removeTestData']);
