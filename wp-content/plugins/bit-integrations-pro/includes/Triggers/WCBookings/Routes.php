<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WCBookings\WCBookingsController;

Route::get('wcbookings/get', [WCBookingsController::class, 'getAll']);
Route::post('wcbookings/get/form', [WCBookingsController::class, 'get_a_form']);
