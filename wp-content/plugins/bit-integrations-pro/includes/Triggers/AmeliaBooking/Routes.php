<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AmeliaBooking\AmeliaBookingController;

Route::get('amelia/get', [AmeliaBookingController::class, 'getAllTasks']);
Route::post('amelia/test', [AmeliaBookingController::class, 'getTestData']);
Route::post('amelia/test/remove', [AmeliaBookingController::class, 'removeTestData']);
