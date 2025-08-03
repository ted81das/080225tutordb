<?php

namespace BitApps\BTCBI_PRO\Triggers\AmeliaBooking;

use BitCode\FI\Core\Util\Helper;

class AmeliaBookingHelper
{
    public static function formatNewAppointmentData($appointment, $service, $paymentData)
    {
        $booking = static::formatAppointmentData($appointment);

        unset(
            $appointment['bookings'],
            $service['settings'],
            $service['timeBefore'],
            $service['timeAfter'],
            $service['translations'],
            $service['minSelectedExtras'],
            $service['mandatoryExtra'],
            $service['customPricing'],
            $service['maxExtraPeople'],
            $service['limitPerCustomer'],
            $booking['customerId'],
        );

        $bookingData = array_merge($booking, [
            'bookingStart'       => $appointment['bookingStart'],
            'notifyParticipants' => $appointment['notifyParticipants'],
            'internalNotes'      => $appointment['internalNotes'],
            'recurring'          => $appointment['recurring'],
            'utc'                => $appointment['utc'],
            'timeZone'           => $appointment['timeZone'],
            'payment'            => $paymentData,
            'service'            => $service,
        ]);

        return Helper::prepareFetchFormatFields($bookingData);
    }

    public static function formatAppointmentStatusUpdateData($appointment, $requestedStatus)
    {
        $bookingData = static::formatAppointmentData($appointment);
        $bookingData['requested_status'] = $requestedStatus;

        return Helper::prepareFetchFormatFields($bookingData);
    }

    public static function formatBookingCancelledData($booking)
    {
        $appointment = $booking['appointment'];
        $appointment['bookings'] = [$booking['booking']];
        $bookingData = static::formatAppointmentData($appointment);

        return Helper::prepareFetchFormatFields($bookingData);
    }

    public static function formatEventData($event)
    {
        $provider = $event['providers'][0] ?? [];
        $event['periods'] = wp_json_encode($event['periods']);
        $event['tags'] = wp_json_encode($event['tags']);

        unset(
            $event['bookingOpensRec'],
            $event['bookingClosesRec'],
            $event['gallery'],
            $event['settings'],
            $event['closeAfterMinBookings'],
            $event['providers'],
            $provider['translations'],
            $provider['appleCalendarId'],
            $provider['weekDayList'],
            $provider['serviceList'],
            $provider['dayOffList'],
            $provider['specialDayList'],
            $provider['description'],
        );

        $event['provider'] = $provider;

        return Helper::prepareFetchFormatFields($event);
    }

    public static function formatEventBookingData($booking, $reservation)
    {
        $customer = $booking['customer'] ?? [];
        $provider = $reservation['providers'][0] ?? [];
        $reservation['tags'] = wp_json_encode($reservation['tags']);
        $reservation['periods'] = $reservation['periods'] ?? [];

        unset(
            $booking['customerId'],
            $booking['info'],
            $customer['externalId'],
            $customer['translations'],
            $reservation['settings'],
            $reservation['bookings'],
            $reservation['periods'],
            $reservation['providers'],
            $reservation['bookingOpensRec'],
            $reservation['bookingClosesRec'],
            $reservation['translations']
        );

        $reservation['provider'] = $provider;
        $booking['customer'] = $customer;
        $booking['event'] = $reservation;

        return Helper::prepareFetchFormatFields($booking);
    }

    public static function formatAppointmentData($appointment)
    {
        $booking = $appointment['bookings'][0];

        if (!empty($booking['payments'])) {
            $payment = $booking['payments'][0];

            $booking['payment'] = [
                'amount'        => $payment['amount'] ?? null,
                'gateway'       => $payment['gateway'] ?? null,
                'status'        => $payment['status'] ?? null,
                'dateTime'      => $payment['dateTime'] ?? null,
                'invoiceNumber' => $payment['invoiceNumber'] ?? null
            ];
        }

        unset(
            $appointment['bookings'],
            $booking['utcOffset'],
            $booking['isChangedStatus'],
            $booking['isLastBooking'],
            $booking['packageCustomerService'],
            $booking['actionsCompleted'],
            $booking['isUpdated'],
            $booking['info'],
            $booking['payments'],
        );

        return array_merge($booking, $appointment);
    }

    public static function isPluginInstalled()
    {
        return class_exists('\AmeliaBooking\Plugin');
    }
}
