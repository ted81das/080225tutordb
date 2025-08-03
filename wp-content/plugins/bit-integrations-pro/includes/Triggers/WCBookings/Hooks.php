<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WCBookings\WCBookingsController;

Hooks::add('woocommerce_new_booking', [WCBookingsController::class, 'handleNewBooking'], 10, 1);
Hooks::add('woocommerce_booking_confirmed', [WCBookingsController::class, 'handleConfirmBooking'], 10, 2);
Hooks::add('woocommerce_booking_unpaid_to_paid', [WCBookingsController::class, 'handleUnpaidToPaidBooking'], 10, 2);
Hooks::add('woocommerce_booking_process_meta', [WCBookingsController::class, 'handleBookingUpdated'], 10, 1);
Hooks::add('woocommerce_booking_status_changed', [WCBookingsController::class, 'handleBookingStatusChanged'], 10, 4);
