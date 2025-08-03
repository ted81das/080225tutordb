<?php
if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FluentBooking\FluentBookingController;

Hooks::add('fluent_booking/after_booking_scheduled', [FluentBookingController::class, 'handleFluentBookingScheduledSubmit'], 10, 2);
Hooks::add('fluent_booking/booking_schedule_completed', [FluentBookingController::class, 'handleFluentBookingCompletedSubmit'], 10, 2);
Hooks::add('fluent_booking/booking_schedule_cancelled', [FluentBookingController::class, 'handleFluentBookingCancelledSubmit'], 10, 2);
