<?php

namespace BitApps\BTCBI_PRO\Triggers\SimplyScheduleAppointments;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class SimplyScheduleAppointmentsController
{
    public static function info()
    {
        return [
            'name'              => 'Simply Schedule Appointments',
            'title'             => __('Simply Schedule Appointments Booking Plugin is for Consultants and Small Businesses using WordPress.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'ssa/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'ssa/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'ssa/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Simply Schedule Appointments'));
        }

        wp_send_json_success([
            ['form_name' => __('New Appointment Booked', 'bit-integrations-pro'), 'triggered_entity_id' => 'ssa/appointment/booked', 'skipPrimaryKey' => true],
            ['form_name' => __('Appointment Rescheduled', 'bit-integrations-pro'), 'triggered_entity_id' => 'ssa/appointment/rescheduled', 'skipPrimaryKey' => true],
            ['form_name' => __('New Appointment canceled', 'bit-integrations-pro'), 'triggered_entity_id' => 'ssa/appointment/canceled', 'skipPrimaryKey' => true],
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

    public static function handleNewAppointmentBooked($appointment_id, $data, $data_before)
    {
        return static::flowExecute('ssa/appointment/booked', $data);
    }

    public static function handleAppointmentRescheduled($appointment_id, $data, $data_before)
    {
        if (empty($data) || ($data_before['start_date'] === $data['start_date'])) {
            return;
        }

        return static::flowExecute('ssa/appointment/rescheduled', $data);
    }

    public static function handleNewAppointmentCanceled($appointment_id, $data_after, $data_before, $response)
    {
        if (empty($data_after) || empty($data_before)) {
            return;
        }

        $data_before['status'] = $data_after['status'];

        return static::flowExecute('ssa/appointment/canceled', $data_before);
    }

    private static function flowExecute($triggered_entity_id, $data)
    {
        if (empty($data)) {
            return;
        }

        $data['customer'] = $data['customer_information'];
        unset($data['customer_information']);

        $formData = Helper::prepareFetchFormatFields($data);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('SimplyScheduleAppointments', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('SimplyScheduleAppointments', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('Simply_Schedule_Appointments');
    }
}
