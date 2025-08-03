<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AppointmentHourBooking\AppointmentHourBookingController;

Route::get('appointment_hour_booking/get', [AppointmentHourBookingController::class, 'getAllTasks']);
Route::post('appointment_hour_booking/test', [AppointmentHourBookingController::class, 'getTestData']);
Route::post('appointment_hour_booking/test/remove', [AppointmentHourBookingController::class, 'removeTestData']);
