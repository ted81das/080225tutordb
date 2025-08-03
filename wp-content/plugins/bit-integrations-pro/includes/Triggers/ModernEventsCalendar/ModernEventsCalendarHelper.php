<?php

namespace BitApps\BTCBI_PRO\Triggers\ModernEventsCalendar;

use BitCode\FI\Core\Util\Helper;

class ModernEventsCalendarHelper
{
    public static function isPluginInstalled()
    {
        return \defined('MEC_ABSPATH');
    }

    public static function formatEventData($booking_id)
    {
        $event_id_meta = get_post_meta($booking_id, 'mec_event_id', true);
        $event_id = is_numeric($event_id_meta) ? (int) $event_id_meta : null;

        if (empty($event_id)) {
            return;
        }

        $event = get_post($event_id);
        if (empty($event)) {
            return;
        }

        $start_datetime = get_post_meta($event_id, 'mec_start_datetime', true);
        $end_datetime = get_post_meta($event_id, 'mec_end_datetime', true);

        $eventData = Helper::prepareFetchFormatFields(array_merge([
            'event_id'           => $event_id,
            'title'              => get_the_title($event_id),
            'description'        => $event->post_content ?? null,
            'categories'         => static::getEventCategories($event_id),
            'start_date'         => static::formatEventDate($start_datetime),
            'start_time'         => static::formatEventTime($start_datetime),
            'end_date'           => static::formatEventDate($end_datetime),
            'end_time'           => static::formatEventTime($end_datetime),
            'location'           => static::getEventLocationOrOrganizer($event_id, 'mec_location_id', 'mec_location'),
            'organizer'          => static::getEventLocationOrOrganizer($event_id, 'mec_organizer_id', 'mec_organizer'),
            'cost'               => get_post_meta($event_id, 'mec_cost', true),
            'featured_image_id'  => get_post_thumbnail_id($event_id),
            'featured_image_url' => get_the_post_thumbnail_url($event_id),
        ], static::getEventBooking($booking_id)));

        return array_merge($eventData, [
            'tickets' => [
                'name'  => 'tickets.value',
                'type'  => 'array',
                'label' => 'Tickets (array)',
                'value' => static::getEventTickets($event_id),
            ],
            'attendees' => [
                'name'  => 'attendees.value',
                'type'  => 'array',
                'label' => 'Attendees (array)',
                'value' => static::getEventAttendees($booking_id),
            ]
        ]);
    }

    public static function getEventLocationOrOrganizer($event_id, $post_key, $term_key)
    {
        $location_id = get_post_meta($event_id, $post_key, true);
        if (!$location_id || !is_numeric($location_id)) {
            return;
        }

        $term = get_term((int) $location_id, $term_key);

        return (!is_wp_error($term) && $term && $term->name !== '') ? $term->name : null;
    }

    private static function getEventBooking($booking_id)
    {
        $attendees = get_post_meta($booking_id, 'mec_attendees', true) ?? [];

        return [
            'booking_id'                  => $booking_id,
            'booking_title'               => get_the_title($booking_id),
            'booking_transaction_id'      => get_post_meta($booking_id, 'mec_transaction_id', true),
            'booking_amount_payable'      => get_post_meta($booking_id, 'mec_payable', true),
            'booking_price'               => get_post_meta($booking_id, 'mec_price', true),
            'booking_time'                => get_post_meta($booking_id, 'mec_booking_time', true),
            'booking_payment_gateway'     => get_post_meta($booking_id, 'mec_gateway_label', true),
            'booking_confirmation_status' => static::getConfirmationStatus($booking_id),
            'booking_verification_status' => static::getVerificationStatus($booking_id),
            'booking_attendees_count'     => \is_array($attendees) ? \count($attendees) : null
        ];
    }

    private static function getConfirmationStatus($booking_id)
    {
        $isConfirmed = get_post_meta($booking_id, 'mec_confirmed', true);

        if (1 == $isConfirmed) {
            return 'Confirmed';
        } elseif (-1 == $isConfirmed) {
            return 'Rejected';
        }

        return 'Pending';
    }

    private static function getVerificationStatus($booking_id)
    {
        $isVerified = get_post_meta($booking_id, 'mec_confirmed', true);

        if (1 == $isVerified) {
            return 'Verified';
        } elseif (-1 == $isVerified) {
            return 'Canceled';
        }

        return 'Waiting';
    }

    private static function getEventAttendees($booking_id)
    {
        $attendees = get_post_meta($booking_id, 'mec_attendees', true);

        if (!\is_array($attendees) || empty($attendees)) {
            return [];
        }

        return array_map(function ($attendee) {
            return [
                'id'    => $attendee['id'] ?? null,
                'email' => $attendee['email'] ?? null,
                'name'  => $attendee['name'] ?? null,
                'count' => $attendee['count'] ?? null,
            ];
        }, $attendees);
    }

    private static function getEventTickets($event_id)
    {
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        if (!\is_array($tickets) || empty($tickets)) {
            return [];
        }

        return array_map(function ($ticket) {
            return [
                'id'             => $ticket['id'] ?? null,
                'name'           => $ticket['name'] ?? null,
                'description'    => $ticket['description'] ?? null,
                'price'          => $ticket['price'] ?? null,
                'price_label'    => $ticket['price_label'] ?? null,
                'limit'          => $ticket['limit'] ?? null,
                'unlimited'      => $ticket['unlimited'] ?? null,
                'seats'          => $ticket['seats'] ?? null,
                'minimum_ticket' => $ticket['minimum_ticket'] ?? null,
                'maximum_ticket' => $ticket['maximum_ticket'] ?? null,
            ];
        }, $tickets);
    }

    private static function getEventCategories($event_id)
    {
        $terms = get_the_terms($event_id, 'mec_category');
        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        return implode(', ', array_map(function ($term) {
            return $term->name;
        }, $terms));
    }

    private static function formatEventDate($date)
    {
        return \is_string($date) ? gmdate('F j, Y', (int) strtotime($date)) : $date;
    }

    private static function formatEventTime($date)
    {
        return \is_string($date) ? gmdate('g:i A', (int) strtotime($date)) : $date;
    }
}
