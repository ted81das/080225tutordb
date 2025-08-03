<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FluentBooking\FluentBookingController;

Route::get('fluentbooking/get', [FluentBookingController::class, 'getAll']);
Route::post('fluentbooking/get/form', [FluentBookingController::class, 'get_a_form']);
Route::post('fluentbooking/get/fields', [FluentBookingController::class, 'fields']);
