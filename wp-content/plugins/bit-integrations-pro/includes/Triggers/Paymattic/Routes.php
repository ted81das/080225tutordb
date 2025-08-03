<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Paymattic\PaymatticController;

Route::get('paymattic/get', [PaymatticController::class, 'getAllTasks']);
Route::post('paymattic/test', [PaymatticController::class, 'getTestData']);
Route::post('paymattic/test/remove', [PaymatticController::class, 'removeTestData']);
