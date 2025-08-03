<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\LearnPress\LearnPressController;

Route::get('learn_press/get', [LearnPressController::class, 'getAllTasks']);
Route::post('learn_press/test', [LearnPressController::class, 'getTestData']);
Route::post('learn_press/test/remove', [LearnPressController::class, 'removeTestData']);
