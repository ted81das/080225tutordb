<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ModernEventsCalendar\ModernEventsCalendarController;

Hooks::add('mec_booking_canceled', [ModernEventsCalendarController::class, 'handleEventBookingCancelled'], 10, 1);
Hooks::add('mec_booking_completed', [ModernEventsCalendarController::class, 'handleEventBookingCompleted'], 10, 1);
Hooks::add('mec_booking_confirmed', [ModernEventsCalendarController::class, 'handleEventBookingConfirmed'], 10, 1);
Hooks::add('mec_booking_pended', [ModernEventsCalendarController::class, 'handleEventBookingPending'], 10, 1);
