<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\EventsCalendar\EventsCalendarController;

// Hooks for task one - User Attends Event
Hooks::add('event_tickets_checkin', [EventsCalendarController::class, 'handleAttendsEvent'], 10, 2);
Hooks::add('eddtickets_checkin', [EventsCalendarController::class, 'handleAttendsEvent'], 10, 2);
Hooks::add('rsvp_checkin', [EventsCalendarController::class, 'handleAttendsEvent'], 10, 2);
Hooks::add('wootickets_checkin', [EventsCalendarController::class, 'handleAttendsEvent'], 10, 2);
// Hooks for task one - User Attends Event

// Hooks for task two - Attendee Registered for Event
Hooks::add('event_tickets_rsvp_attendee_created', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
Hooks::add('event_ticket_woo_attendee_created', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
Hooks::add('event_ticket_edd_attendee_created', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
Hooks::add('event_tickets_tpp_attendee_created', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
Hooks::add('event_tickets_tpp_attendee_updated', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
Hooks::add('tec_tickets_commerce_attendee_after_create', [EventsCalendarController::class, 'handleAttendeeRegistered'], 10, 5);
// Hooks for task two - Attendee Registered for Event

// Hooks for task three - New Attendee
Hooks::add('event_tickets_rsvp_tickets_generated_for_product', [EventsCalendarController::class, 'handleNewAttendee'], 10, 2);
Hooks::add('event_tickets_woocommerce_tickets_generated_for_product', [EventsCalendarController::class, 'handleNewAttendee'], 10, 2);
Hooks::add('event_tickets_tpp_tickets_generated_for_product', [EventsCalendarController::class, 'handleNewAttendee'], 10, 2);
// Hooks for task three - New Attendee

// Hooks for task four - Attendee Registered with WooCommerce
Hooks::add('tribe_tickets_attendee_repository_create_attendee_for_ticket_after_create', [EventsCalendarController::class, 'handleAttendeeRegisteredWC'], 10, 4);
// Hooks for task four - Attendee Registered with WooCommerce
