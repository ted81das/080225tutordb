<?php

namespace BitApps\BTCBI_PRO\Triggers\EventsCalendar;

use BitCode\FI\Flow\Flow;

final class EventsCalendarController
{
    private static $pluginPath = 'the-events-calendar/the-events-calendar.php';

    public static function info()
    {
        return [
            'name'           => 'The Events Calendar',
            'title'          => __('The Events Calendar', 'bit-integrations-pro'),
            'slug'           => self::$pluginPath,
            'pro'            => self::$pluginPath,
            'type'           => 'form',
            'is_active'      => is_plugin_active(self::$pluginPath),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . self::$pluginPath . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . self::$pluginPath),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . self::$pluginPath), 'install-plugin_' . self::$pluginPath),
            'list'           => [
                'action' => 'eventscalendar/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'eventscalendar/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'EventsCalendar'));
        }

        $tasks = [
            ['id' => 'events_calendar-1', 'title' => __('User Attends Event', 'bit-integrations-pro'), 'note' => __('Runs after a user attends an event.', 'bit-integrations-pro')],
            ['id' => 'events_calendar-2', 'title' => __('Attendee Registered for Event', 'bit-integrations-pro'), 'note' => __('Runs after an attendee is registered for an event.', 'bit-integrations-pro')],
            ['id' => 'events_calendar-3', 'title' => __('New Attendee', 'bit-integrations-pro'), 'note' => __('Runs after a user registers for an event.', 'bit-integrations-pro')],
            ['id' => 'events_calendar-4', 'title' => __('Attendee Registered with WooCommerce', 'bit-integrations-pro'), 'note' => __('Runs after a user registers for an event with WooCommerce (use "Ticket" here and not "RSVP").', 'bit-integrations-pro')],
        ];

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'EventsCalendar'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id === 'events_calendar-1' || $data->id === 'events_calendar-2' || $data->id === 'events_calendar-3' || $data->id === 'events_calendar-4') {
            $events = EventsCalendarHelper::getAllEvents();

            if (!empty($events)) {
                $responseData['events'] = $events;
            }
        }

        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        return EventsCalendarHelper::getFields($data);
    }

    public static function handleAttendsEvent($attendeeId, $qr)
    {
        if (empty($attendeeId)) {
            return;
        }

        if (!\function_exists('tribe_tickets_get_attendees')) {
            return;
        }

        $attendees = tribe_tickets_get_attendees($attendeeId, 'rsvp_order');
        $attendee = [];

        if (isset($attendees[0])) {
            $attendee = $attendees[0];
        }

        if (empty($attendee)) {
            return;
        }

        $eventId = $attendee['event_id'];

        $flows = Flow::exists('EventsCalendar', 'events_calendar-1');
        $flows = EventsCalendarHelper::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventsCalendarHelper::formatAttendsEventData($attendee);

        if (!empty($data)) {
            Flow::execute('EventsCalendar', 'events_calendar-1', $data, $flows);
        }
    }

    public static function handleAttendeeRegistered($attendeeId, $postId, $order, $attendeeProductId, $attendeeOrderStatus = null)
    {
        if (empty($attendeeId)) {
            return;
        }

        if (\is_object($attendeeId)) {
            $attendeeId = $attendeeId->ID;
        }

        if (!\function_exists('tribe_tickets_get_attendees')) {
            return;
        }

        $attendees = tribe_tickets_get_attendees($attendeeId);
        $attendee = [];

        if (isset($attendees[0])) {
            $attendee = $attendees[0];
        }

        if (empty($attendee)) {
            return;
        }

        $eventId = $attendee['event_id'];

        $flows = Flow::exists('EventsCalendar', 'events_calendar-2');
        $flows = EventsCalendarHelper::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventsCalendarHelper::formatAttendsEventData($attendee);

        if (!empty($data)) {
            Flow::execute('EventsCalendar', 'events_calendar-2', $data, $flows);
        }
    }

    public static function handleNewAttendee($productId, $orderId)
    {
        if (empty($productId) || empty($orderId)) {
            return;
        }

        if (!class_exists('Tribe__Tickets__Main')) {
            return;
        }

        $event = tribe_events_get_ticket_event($productId);
        $eventId = $event->ID;

        if (empty($eventId)) {
            return;
        }

        $flows = Flow::exists('EventsCalendar', 'events_calendar-3');
        $flows = EventsCalendarHelper::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventsCalendarHelper::formatNewAttendeeData($event, $orderId);

        if (!empty($data)) {
            Flow::execute('EventsCalendar', 'events_calendar-3', $data, $flows);
        }
    }

    public static function handleAttendeeRegisteredWC($attendee, $attendeeData, $ticket, $repository)
    {
        if (empty($attendeeData) || empty($ticket)) {
            return;
        }

        if (!class_exists('Tribe__Tickets__Main')) {
            return;
        }

        $orderId = $attendeeData['order_id'];
        $productId = $ticket->ID;
        $event = tribe_events_get_ticket_event($productId);
        $eventId = $event->ID;

        if (empty($eventId)) {
            return;
        }

        $flows = Flow::exists('EventsCalendar', 'events_calendar-4');
        $flows = EventsCalendarHelper::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventsCalendarHelper::formatNewAttendeeData($event, $orderId);

        if (!empty($data)) {
            Flow::execute('EventsCalendar', 'events_calendar-4', $data, $flows);
        }
    }
}
