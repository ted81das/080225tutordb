<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\UserFeedback\UserFeedbackController;

Route::get('userfeedback/get', [UserFeedbackController::class, 'getAllTasks']);
Route::post('userfeedback/test', [UserFeedbackController::class, 'getTestData']);
Route::post('userfeedback/test/remove', [UserFeedbackController::class, 'removeTestData']);
