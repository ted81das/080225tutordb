<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SureFeedback\SureFeedbackController;

Route::get('surefeedback/get', [SureFeedbackController::class, 'getAllTasks']);
Route::post('surefeedback/test', [SureFeedbackController::class, 'getTestData']);
Route::post('surefeedback/test/remove', [SureFeedbackController::class, 'removeTestData']);
