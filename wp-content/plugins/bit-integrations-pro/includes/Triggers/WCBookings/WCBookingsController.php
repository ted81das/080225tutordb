<?php

namespace BitApps\BTCBI_PRO\Triggers\WCBookings;

use BitCode\FI\Flow\Flow;

final class WCBookingsController
{
    public static function info()
    {
        return [
            'name'      => 'WooCommerce Bookings',
            'title'     => __('Allow customers to book appointments, make reservations or rent equipment without leaving your site.', 'bit-integrations-pro'),
            'type'      => 'form',
            'is_active' => static::isActivate(),
            'list'      => [
                'action' => 'wcbookings/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wcbookings/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        static::isPluginActivated();

        $all_forms = [
            ['id' => 'booking_created', 'title' => __('A booking is created', 'bit-integrations-pro')],
            ['id' => 'booking_confirmed', 'title' => __('A booking is confirmed', 'bit-integrations-pro')],
            ['id' => 'booking_unpaid_to_paid', 'title' => __('A booking is unpaid to paid', 'bit-integrations-pro')],
            ['id' => 'booking_updated', 'title' => __('A booking is updated', 'bit-integrations-pro')],
            ['id' => 'booking_status_changed', 'title' => __('A booking status is changed', 'bit-integrations-pro')]
        ];

        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        static::isPluginActivated();

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id === 'booking_status_changed') {
            $responseData['fields'] = array_merge(WCBookingsHelper::getBookingFields(), [
                [
                    'name'  => 'new_status',
                    'type'  => 'text',
                    'label' => __('New Status', 'bit-integrations-pro')
                ],
                [
                    'name'  => 'old_status',
                    'type'  => 'text',
                    'label' => __('Old Status', 'bit-integrations-pro')
                ]
            ]);
        } else {
            $responseData['fields'] = WCBookingsHelper::getBookingFields();
        }

        if (empty($responseData['fields'])) {
            wp_send_json_error(__('Task doesn\'t exists any field', 'bit-integrations-pro'));
        }

        wp_send_json_success($responseData);
    }

    public static function handleNewBooking($booking_id)
    {
        self::processBookingEvent($booking_id, 'booking_created');
    }

    public static function handleConfirmBooking($booking_id, $booking)
    {
        self::processBookingEvent($booking_id, 'booking_confirmed');
    }

    public static function handleUnpaidToPaidBooking($booking_id, $booking)
    {
        self::processBookingEvent($booking_id, 'booking_unpaid_to_paid');
    }

    public static function handleBookingUpdated($booking_id)
    {
        self::processBookingEvent($booking_id, 'booking_updated');
    }

    public static function handleBookingStatusChanged($old_status, $new_status, $booking_id, $booking)
    {
        $was_in_cart = 'was-in-cart';

        if (
            !self::isValidBookingRequest($booking_id)
            || $new_status === $was_in_cart
            || $old_status === $was_in_cart
            || empty($flows = Flow::exists('WCBookings', 'booking_status_changed'))
        ) {
            return;
        }

        $flows = WCBookingsHelper::getFilteredFlows($flows, 'booking_status_changed', $new_status);
        if (empty($flows)) {
            return;
        }

        $data = array_merge(
            WCBookingsHelper::mapBookingData($booking, $booking_id),
            ['new_status' => $new_status, 'old_status' => $old_status]
        );

        Flow::execute('WCBookings', 'booking_status_changed', $data, $flows);
    }

    private static function processBookingEvent($booking_id, $event_type)
    {
        if (!static::isValidBookingRequest($booking_id) || empty($flows = Flow::exists('WCBookings', $event_type))) {
            return;
        }

        $data = WCBookingsHelper::mapBookingData(get_wc_booking($booking_id), $booking_id);
        Flow::execute('WCBookings', $event_type, $data, $flows);
    }

    private static function isValidBookingRequest($booking_id)
    {
        return $booking_id !== ''
            && class_exists('WooCommerce')
            && static::isActivate();
    }

    private static function isPluginActivated()
    {
        if (!static::isActivate()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WooCommerce Bookings'));
        }
    }

    private static function isActivate()
    {
        return class_exists('\WC_Booking');
    }
}
