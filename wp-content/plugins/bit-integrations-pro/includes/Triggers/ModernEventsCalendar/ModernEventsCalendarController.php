<?php

namespace BitApps\BTCBI_PRO\Triggers\ModernEventsCalendar;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class ModernEventsCalendarController
{
    public static function info()
    {
        return [
            'name'              => 'Modern Events Calendar',
            'title'             => __('Best WordPress Event Calendar Plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => ModernEventsCalendarHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'modern_events_calendar/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'modern_events_calendar/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'modern_events_calendar/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!ModernEventsCalendarHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Modern Events Calendar'));
        }

        wp_send_json_success([
            ['form_name' => __('Event Booking Confirmed', 'bit-integrations-pro'), 'triggered_entity_id' => 'mec_booking_confirmed', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking Pending', 'bit-integrations-pro'), 'triggered_entity_id' => 'mec_booking_pended', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking Cancelled', 'bit-integrations-pro'), 'triggered_entity_id' => 'mec_booking_canceled', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Booking Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'mec_booking_completed', 'skipPrimaryKey' => true],
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

    public static function handleEventBookingCancelled($booking_id)
    {
        return static::flowExecute('mec_booking_canceled', $booking_id);
    }

    public static function handleEventBookingCompleted($booking_id)
    {
        return static::flowExecute('mec_booking_completed', $booking_id);
    }

    public static function handleEventBookingConfirmed($booking_id)
    {
        return static::flowExecute('mec_booking_confirmed', $booking_id);
    }

    public static function handleEventBookingPending($booking_id)
    {
        return static::flowExecute('mec_booking_pended', $booking_id);
    }

    private static function flowExecute($triggered_entity_id, $booking_id)
    {
        if (!$booking_id) {
            return;
        }

        $formData = ModernEventsCalendarHelper::formatEventData($booking_id);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('ModernEventsCalendar', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        Flow::execute('ModernEventsCalendar', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
