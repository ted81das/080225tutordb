<?php

namespace BitApps\BTCBI_PRO\Triggers\AmeliaBooking;

class StaticData
{
    public static function forms()
    {
        return [
            ['form_name' => __('New Appointment Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_appointment_added', 'skipPrimaryKey' => true],
            ['form_name' => __('Appointment Status Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_appointment_status_updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Appointment Cancelled', 'bit-integrations-pro'), 'triggered_entity_id' => 'appointment_cancelled', 'skipPrimaryKey' => true],
            ['form_name' => __('New Booking Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_booking_added', 'skipPrimaryKey' => true],
            ['form_name' => __('Booking Cancelled', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_after_booking_canceled', 'skipPrimaryKey' => true],
            ['form_name' => __('Booking Rescheduled', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_booking_rescheduled', 'skipPrimaryKey' => true],
            ['form_name' => __('New Event Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_event_added', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_after_event_updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_event_booking_saved', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_after_event_booking_updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'amelia_before_event_booking_deleted', 'skipPrimaryKey' => true],
        ];
    }
}
