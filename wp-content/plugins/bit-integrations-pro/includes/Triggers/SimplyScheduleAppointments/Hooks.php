<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SimplyScheduleAppointments\SimplyScheduleAppointmentsController;

Hooks::add('ssa/appointment/booked', [SimplyScheduleAppointmentsController::class, 'handleNewAppointmentBooked'], 10, 3);
Hooks::add('ssa/appointment/rescheduled', [SimplyScheduleAppointmentsController::class, 'handleAppointmentRescheduled'], 10, 3);
Hooks::add('ssa/appointment/canceled', [SimplyScheduleAppointmentsController::class, 'handleNewAppointmentCanceled'], 10, 4);
