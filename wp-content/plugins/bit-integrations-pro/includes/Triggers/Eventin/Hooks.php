<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Eventin\EventinController;

Hooks::add('eventin_event_created', [EventinController::class, 'handleEventCreated'], 10, 2);
Hooks::add('eventin_event_updated', [EventinController::class, 'handleEventUpdated'], 10, 2);
Hooks::add('eventin_event_deleted', [EventinController::class, 'handleEventDeleted'], 10, 1);
Hooks::add('eventin_speaker_created', [EventinController::class, 'handleSpeakerCreated'], 10, 2);
Hooks::add('eventin_speaker_update', [EventinController::class, 'handleSpeakerUpdated'], 10, 2);
Hooks::add('eventin_speaker_deleted', [EventinController::class, 'handleSpeakerDeleted'], 10, 1);
Hooks::add('eventin_attendee_updated', [EventinController::class, 'handleAttendeeUpdated'], 10, 2);
Hooks::add('eventin_attendee_deleted', [EventinController::class, 'handleAttendeeDeleted'], 10, 1);
Hooks::add('eventin_after_order_create', [EventinController::class, 'handleOrderCreate'], 10, 1);
Hooks::add('eventin_order_deleted', [EventinController::class, 'handleOrderDeleted'], 10, 1);
Hooks::add('eventin_schedule_deleted', [EventinController::class, 'handleScheduleDeleted'], 10, 1);
