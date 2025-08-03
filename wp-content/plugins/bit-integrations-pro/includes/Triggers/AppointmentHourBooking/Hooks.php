<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AppointmentHourBooking\AppointmentHourBookingController;

Hooks::add('cpappb_update_status', [AppointmentHourBookingController::class, 'handleBookingStatusUpdated'], 10, 2);
Hooks::add('cpappb_process_data', [AppointmentHourBookingController::class, 'handleNewAppointmentBooked'], 10, 1);
