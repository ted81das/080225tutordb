<?php

namespace BitApps\BTCBI_PRO\Triggers\EventsCalendar;

class EventsCalendarHelper
{
    public static function getAllEvents()
    {
        $events = get_posts(
            [
                'post_type'      => 'tribe_events',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ]
        );

        $eventList[] = (object) [
            'value' => 'any',
            'label' => __('Any Event', 'bit-integrations-pro')
        ];

        if (!empty($events)) {
            foreach ($events as $event) {
                $eventList[] = (object) ['value' => (string) $event->ID, 'label' => $event->post_title];
            }
        }

        return $eventList;
    }

    public static function getFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        if (empty($id)) {
            return;
        }

        $fields = [];

        if ($id === 'events_calendar-1' || $id === 'events_calendar-2') {
            $fields = self::attendsEventFields();
        } elseif ($id === 'events_calendar-3' || $id === 'events_calendar-4') {
            $fields = self::newAttendeeFields();
        }

        return $fields;
    }

    public static function formatAttendsEventData($attendee)
    {
        if (empty($attendee)) {
            return false;
        }

        $data['purchaser_name'] = $attendee['purchaser_name'] ?? '';
        $data['purchaser_email'] = $attendee['purchaser_email'] ?? '';
        $data['holder_name'] = $attendee['holder_name'] ?? '';
        $data['holder_email'] = $attendee['holder_email'] ?? '';
        $data['attendee_id'] = $attendee['attendee_id'] ?? '';
        $data['ticket_id'] = $attendee['ticket_id'] ?? '';
        $data['ticket_name'] = $attendee['ticket_name'] ?? '';
        $data['qr_ticket_id'] = $attendee['qr_ticket_id'] ?? '';
        $data['price_paid'] = $attendee['price_paid'] ?? '';
        $data['currency'] = $attendee['currency'] ?? '';
        $data['attendee_date'] = $attendee['post_date'] ?? '';
        $data['attendee_date_gmt'] = $attendee['post_date_gmt'] ?? '';
        $data['attendee_modified'] = $attendee['post_modified'] ?? '';
        $data['attendee_modified_gmt'] = $attendee['post_modified_gmt'] ?? '';
        $data['order_id'] = $attendee['order_id'] ?? '';
        $data['order_status'] = $attendee['order_status'] ?? '';
        $data['check_in'] = $attendee['check_in'] ?? '';
        $data['ticket_sent'] = $attendee['ticket_sent'] ?? '';
        $data['is_subscribed'] = $attendee['is_subscribed'] ?? '';
        $data['is_purchaser'] = $attendee['is_purchaser'] ?? '';
        $data['ticket_exists'] = $attendee['ticket_exists'] ?? '';
        $data['event_id'] = $attendee['event_id'] ?? '';
        $data['security_code'] = $attendee['security_code'] ?? '';
        $data['purchase_time'] = $attendee['purchase_time'] ?? '';

        $event = [];

        if (\function_exists('tribe_events_get_ticket_event')) {
            $event = (array) tribe_events_get_ticket_event($attendee['ticket_id']);
        }

        if (!isset($event['post_title'])) {
            $event = get_post($attendee['event_id'], ARRAY_A);
        }

        $data['event_name'] = $event['post_title'] ?? '';
        $data['event_date'] = $event['post_date'] ?? '';
        $data['event_date_gmt'] = $event['post_date_gmt'] ?? '';
        $data['event_modified'] = $event['post_modified'] ?? '';
        $data['event_modified_gmt'] = $event['post_modified_gmt'] ?? '';
        $data['event_guid'] = $event['guid'] ?? '';

        return $data;
    }

    public static function formatNewAttendeeData($event, $orderId)
    {
        if (empty($event) || empty($orderId)) {
            return false;
        }

        if (!class_exists('Tribe__Tickets__Main')) {
            return false;
        }

        $event = (array) $event;
        $data = ['purchaser_name' => '', 'purchaser_email' => '', 'holder_names' => '', 'holder_emails' => '', 'attendee_ids' => '', 'ticket_ids' => '', 'qr_ticket_ids' => '', 'ticket_name' => '', 'order_ids' => '', 'order_status' => '', 'purchase_time' => ''];

        $attendees = tribe_tickets_get_attendees($orderId);

        if (empty($attendees)) {
            return false;
        }

        foreach ($attendees as $attendee) {
            $purchaserName = $attendee['purchaser_name'];
            $purchaserEmail = $attendee['purchaser_email'];
            $holderNames[$attendee['holder_name']] = $attendee['holder_name'];
            $holderEmails[$attendee['holder_email']] = $attendee['holder_email'];
            $attendeeIds[] = $attendee['attendee_id'];
            $ticketIds[] = $attendee['ticket_id'];
            $qrTicketIds[] = $attendee['qr_ticket_id'];
            $ticketName = $attendee['ticket_name'];
            $orderIds[] = $attendee['order_id'];
            $orderStatus = $attendee['order_status'];
            $purchaseTime = $attendee['purchase_time'];
        }

        $data['purchaser_name'] = $purchaserName;
        $data['purchaser_email'] = $purchaserEmail;

        if (!empty($holderNames)) {
            $data['holder_names'] = implode(',', $holderNames);
        }

        if (!empty($holderEmails)) {
            $data['holder_emails'] = implode(',', $holderEmails);
        }

        if (!empty($attendeeIds)) {
            $data['attendee_ids'] = implode(',', $attendeeIds);
        }

        if (!empty($ticketIds)) {
            $data['ticket_ids'] = implode(',', $ticketIds);
        }

        if (!empty($qrTicketIds)) {
            $data['qr_ticket_ids'] = implode(',', $qrTicketIds);
        }

        if (!empty($orderIds)) {
            $data['order_ids'] = implode(',', $orderIds);
        }

        $data['ticket_name'] = $ticketName;
        $data['order_status'] = $orderStatus;
        $data['purchase_time'] = $purchaseTime;

        $data['event_name'] = $event['post_title'] ?? '';
        $data['event_date'] = $event['post_date'] ?? '';
        $data['event_date_gmt'] = $event['post_date_gmt'] ?? '';
        $data['event_modified'] = $event['post_modified'] ?? '';
        $data['event_modified_gmt'] = $event['post_modified_gmt'] ?? '';
        $data['event_guid'] = $event['guid'] ?? '';

        return $data;
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '' || $value === 'empty_terms') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    private static function attendsEventFields($type = null, array $unsetFields = [])
    {
        $fields = [
            [
                'name'  => 'purchaser_name',
                'type'  => 'text',
                'label' => __('Purchaser Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'purchaser_email',
                'type'  => 'text',
                'label' => __('Purchaser Email', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'holder_name',
                'type'  => 'text',
                'label' => __('Holder Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'holder_email',
                'type'  => 'text',
                'label' => __('Holder Email', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_id',
                'type'  => 'text',
                'label' => __('Attendee ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_id',
                'type'  => 'text',
                'label' => __('Ticket ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_name',
                'type'  => 'text',
                'label' => __('Ticket Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'qr_ticket_id',
                'type'  => 'text',
                'label' => __('QR Ticket ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'price_paid',
                'type'  => 'text',
                'label' => __('Price Paid', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'currency',
                'type'  => 'text',
                'label' => __('Currency', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_date',
                'type'  => 'text',
                'label' => __('Attendee Date', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_date_gmt',
                'type'  => 'text',
                'label' => __('Attendee Date GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_modified',
                'type'  => 'text',
                'label' => __('Attendee Modified', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_modified_gmt',
                'type'  => 'text',
                'label' => __('Attendee Modified GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'order_id',
                'type'  => 'text',
                'label' => __('Order ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'order_status',
                'type'  => 'text',
                'label' => __('Order Status', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_id',
                'type'  => 'text',
                'label' => __('Event ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_name',
                'type'  => 'text',
                'label' => __('Event Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_date',
                'type'  => 'text',
                'label' => __('Event Date', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_date_gmt',
                'type'  => 'text',
                'label' => __('Event Date GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_modified',
                'type'  => 'text',
                'label' => __('Event Modified', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_modified_gmt',
                'type'  => 'text',
                'label' => __('Event Modified GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_guid',
                'type'  => 'text',
                'label' => __('Event GUID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'check_in',
                'type'  => 'text',
                'label' => __('Check In', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_sent',
                'type'  => 'text',
                'label' => __('Ticket Sent', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'is_subscribed',
                'type'  => 'text',
                'label' => __('Is Subscribed', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'is_purchaser',
                'type'  => 'text',
                'label' => __('Is Purchaser', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_exists',
                'type'  => 'text',
                'label' => __('Ticket Exists', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'security_code',
                'type'  => 'text',
                'label' => __('Attendee Security Code', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'purchase_time',
                'type'  => 'text',
                'label' => __('Purchase Time', 'bit-integrations-pro'),
            ],
        ];

        if (!empty($unsetFields)) {
            foreach ($unsetFields as $fieldKey) {
                unset($fields[$fieldKey]);
            }
        }

        return $fields;
    }

    private static function newAttendeeFields($type = null, array $unsetFields = [])
    {
        $fields = [
            [
                'name'  => 'purchaser_name',
                'type'  => 'text',
                'label' => __('Purchaser Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'purchaser_email',
                'type'  => 'text',
                'label' => __('Purchaser Email', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'holder_names',
                'type'  => 'text',
                'label' => __('Holder Name(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'holder_emails',
                'type'  => 'text',
                'label' => __('Holder Email(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'attendee_ids',
                'type'  => 'text',
                'label' => __('Attendee ID(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_ids',
                'type'  => 'text',
                'label' => __('Ticket ID(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'qr_ticket_ids',
                'type'  => 'text',
                'label' => __('QR Ticket ID(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ticket_name',
                'type'  => 'text',
                'label' => __('Ticket Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'order_ids',
                'type'  => 'text',
                'label' => __('Order ID(s)', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'order_status',
                'type'  => 'text',
                'label' => __('Order Status', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'purchase_time',
                'type'  => 'text',
                'label' => __('Purchase Time', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_id',
                'type'  => 'text',
                'label' => __('Event ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_name',
                'type'  => 'text',
                'label' => __('Event Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_date',
                'type'  => 'text',
                'label' => __('Event Date', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_date_gmt',
                'type'  => 'text',
                'label' => __('Event Date GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_modified',
                'type'  => 'text',
                'label' => __('Event Modified', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_modified_gmt',
                'type'  => 'text',
                'label' => __('Event Modified GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'event_guid',
                'type'  => 'text',
                'label' => __('Event GUID', 'bit-integrations-pro'),
            ],
        ];

        if (!empty($unsetFields)) {
            foreach ($unsetFields as $fieldKey) {
                unset($fields[$fieldKey]);
            }
        }

        return $fields;
    }
}
