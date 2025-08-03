<?php

namespace BitApps\BTCBI_PRO\Triggers\AmeliaBooking;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AmeliaBookingController
{
    public static function info()
    {
        return [
            'name'              => 'Amelia Booking',
            'title'             => __('A WordPress plugin that allows you to easily schedule and manage appointments and bookings on your website.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AmeliaBookingHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/amelia-booking-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'amelia/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'amelia/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'amelia/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AmeliaBookingHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Amelia Booking'));
        }

        wp_send_json_success(StaticData::forms());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleNewAppointmentAdded($appointment, $service, $paymentData)
    {
        if (empty($appointment) || empty($appointment['bookings'])) {
            return;
        }

        $formData = AmeliaBookingHelper::formatNewAppointmentData($appointment, $service, $paymentData);

        return static::flowExecute('amelia_before_appointment_added', $formData);
    }

    public static function handleBookingAdded($appointment)
    {
        if (empty($appointment) || empty($appointment['bookings'])) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(AmeliaBookingHelper::formatAppointmentData($appointment));

        return static::flowExecute('amelia_before_booking_added', $formData);
    }

    public static function handleBookingCancelled($booking)
    {
        if (empty($booking) || empty($booking['appointment'])) {
            return;
        }

        $formData = AmeliaBookingHelper::formatBookingCancelledData($booking);

        return static::flowExecute('amelia_after_booking_canceled', $formData);
    }

    public static function handleBookingRescheduled($oldAppointment, $booking, $bookingStart)
    {
        if (empty($oldAppointment) || empty($oldAppointment['bookings'])) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(AmeliaBookingHelper::formatAppointmentData($oldAppointment));

        return static::flowExecute('amelia_before_booking_rescheduled', $formData);
    }

    public static function handleAppointmentStatusUpdated($appointment, $requestedStatus)
    {
        if (empty($appointment) || empty($appointment['id']) || empty($requestedStatus)) {
            return;
        }

        $formData = AmeliaBookingHelper::formatAppointmentStatusUpdateData($appointment, $requestedStatus);

        return static::flowExecute('amelia_before_appointment_status_updated', $formData);
    }

    public static function handleAppointmentCancelled($appointment, $requestedStatus)
    {
        if (empty($appointment) || empty($appointment['id']) || empty($requestedStatus) || $requestedStatus !== 'canceled') {
            return;
        }

        $formData = AmeliaBookingHelper::formatAppointmentStatusUpdateData($appointment, $requestedStatus);

        return static::flowExecute('appointment_cancelled', $formData);
    }

    public static function handleEventAdded($event)
    {
        if (empty($event)) {
            return;
        }

        $formData = AmeliaBookingHelper::formatEventData($event);

        return static::flowExecute('amelia_before_event_added', $formData);
    }

    public static function handleEventUpdated($event)
    {
        if (empty($event)) {
            return;
        }

        $formData = AmeliaBookingHelper::formatEventData($event);

        return static::flowExecute('amelia_after_event_updated', $formData);
    }

    public static function handleEventBookingAdded($booking, $reservation)
    {
        if (empty($booking) || empty($reservation)) {
            return;
        }

        $formData = AmeliaBookingHelper::formatEventBookingData($booking, $reservation);

        return static::flowExecute('amelia_before_event_booking_saved', $formData);
    }

    public static function handleEventBookingDeleted($booking, $event)
    {
        if (empty($booking) || empty($event)) {
            return;
        }

        $formData = AmeliaBookingHelper::formatEventBookingData($booking, $event);

        return static::flowExecute('amelia_before_event_booking_deleted', $formData);
    }

    public static function handleEventBookingUpdated($booking, $oldBooking)
    {
        if (empty($booking)) {
            return;
        }

        $bookingData = ['bookings' => [$booking]];
        $formData = Helper::prepareFetchFormatFields(AmeliaBookingHelper::formatAppointmentData($bookingData));

        return static::flowExecute('amelia_after_event_booking_updated', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('AmeliaBooking', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('AmeliaBooking', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
