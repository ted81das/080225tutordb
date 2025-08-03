<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\EventsCalendar\EventsCalendarController;
use BitApps\BTCBI_PRO\Triggers\EventsCalendar\EventsCalendarHelper;

Route::get('eventscalendar/get', [EventsCalendarController::class, 'getAll']);
Route::post('eventscalendar/get/form', [EventsCalendarController::class, 'get_a_form']);
Route::get('eventscalendar/get/events', [EventsCalendarHelper::class, 'getAllEvents']);
