<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AmeliaBooking\AmeliaBookingController;

Hooks::add('amelia_before_appointment_added', [AmeliaBookingController::class, 'handleNewAppointmentAdded'], 10, 3);
Hooks::add('amelia_before_appointment_status_updated', [AmeliaBookingController::class, 'handleAppointmentStatusUpdated'], 10, 2);
Hooks::add('amelia_before_appointment_status_updated', [AmeliaBookingController::class, 'handleAppointmentCancelled'], 10, 2);
Hooks::add('amelia_before_booking_added', [AmeliaBookingController::class, 'handleBookingAdded'], 10, 1);
Hooks::add('amelia_after_booking_canceled', [AmeliaBookingController::class, 'handleBookingCancelled'], 10, 1);
Hooks::add('amelia_before_booking_rescheduled', [AmeliaBookingController::class, 'handleBookingRescheduled'], 10, 3);
Hooks::add('amelia_before_event_added', [AmeliaBookingController::class, 'handleEventAdded'], 10, 1);
Hooks::add('amelia_after_event_updated', [AmeliaBookingController::class, 'handleEventUpdated'], 10, 1);
Hooks::add('amelia_before_event_booking_saved', [AmeliaBookingController::class, 'handleEventBookingAdded'], 10, 2);
Hooks::add('amelia_before_event_booking_deleted', [AmeliaBookingController::class, 'handleEventBookingDeleted'], 10, 2);
Hooks::add('amelia_after_event_booking_updated', [AmeliaBookingController::class, 'handleEventBookingUpdated'], 10, 2);
