<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\ModernEventsCalendar\ModernEventsCalendarController;

Route::get('modern_events_calendar/get', [ModernEventsCalendarController::class, 'getAllTasks']);
Route::post('modern_events_calendar/test', [ModernEventsCalendarController::class, 'getTestData']);
Route::post('modern_events_calendar/test/remove', [ModernEventsCalendarController::class, 'removeTestData']);
