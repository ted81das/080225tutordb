<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SimplyScheduleAppointments\SimplyScheduleAppointmentsController;

Route::get('ssa/get', [SimplyScheduleAppointmentsController::class, 'getAllTasks']);
Route::post('ssa/test', [SimplyScheduleAppointmentsController::class, 'getTestData']);
Route::post('ssa/test/remove', [SimplyScheduleAppointmentsController::class, 'removeTestData']);
