<?php

namespace BitApps\BTCBI_PRO\Triggers\AppointmentHourBooking;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AppointmentHourBookingController
{
    public static function info()
    {
        return [
            'name'              => 'Appointment Hour Booking',
            'title'             => __('A WordPress Booking plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/appointment-hour-booking-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'appointment_hour_booking/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'appointment_hour_booking/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'appointment_hour_booking/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Appointment Hour Booking'));
        }

        wp_send_json_success([
            ['form_name' => __('Booking Status Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'cpappb_update_status', 'skipPrimaryKey' => true],
            ['form_name' => __('New Appointment Booked', 'bit-integrations-pro'), 'triggered_entity_id' => 'cpappb_process_data', 'skipPrimaryKey' => true],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleBookingStatusUpdated($id, $status)
    {
        if (empty($id) || empty($status)) {
            return;
        }

        global $wpdb;
        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}cpappbk_messages WHERE id=%d LIMIT 1", $id));

        if (is_wp_error($event) || empty($event) || empty($event->posted_data)) {
            return;
        }

        return static::flowExecute('cpappb_update_status', unserialize($event->posted_data));
    }

    public static function handleNewAppointmentBooked($data)
    {
        return static::flowExecute('cpappb_process_data', $data);
    }

    private static function flowExecute($triggered_entity_id, $data)
    {
        if (empty($data) || !\is_array($data)) {
            return;
        }

        unset($data['apps']);
        $formData = Helper::prepareFetchFormatFields($data);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('AppointmentHourBooking', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('AppointmentHourBooking', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('CP_AppBookingPlugin');
    }
}
