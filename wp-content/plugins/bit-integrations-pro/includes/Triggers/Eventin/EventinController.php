<?php

namespace BitApps\BTCBI_PRO\Triggers\Eventin;

use BitCode\FI\Flow\Flow;

final class EventinController
{
    private static $pluginPath = 'wp-event-solution/eventin.php';

    public static function info()
    {
        return [
            'name'           => 'Eventin',
            'title'          => __('Eventin', 'bit-integrations-pro'),
            'slug'           => self::$pluginPath,
            'pro'            => self::$pluginPath,
            'type'           => 'form',
            'is_active'      => is_plugin_active(self::$pluginPath),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . self::$pluginPath . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . self::$pluginPath),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . self::$pluginPath), 'install-plugin_' . self::$pluginPath),
            'list'           => [
                'action' => 'eventin/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'eventin/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Eventin'));
        }

        $tasks = [
            ['id' => 'eventin-1', 'title' => __('Event Created', 'bit-integrations-pro'), 'note' => __('Runs after a new event is created', 'bit-integrations-pro')],
            ['id' => 'eventin-2', 'title' => __('Event Updated', 'bit-integrations-pro'), 'note' => __('Runs after an existing event is updated', 'bit-integrations-pro')],
            ['id' => 'eventin-3', 'title' => __('Event Deleted', 'bit-integrations-pro'), 'note' => __('Runs after an event is deleted', 'bit-integrations-pro')],
            ['id' => 'eventin-4', 'title' => __('Speaker (or Organizer) Created', 'bit-integrations-pro'), 'note' => __('Runs after a speaker or organizer is created.', 'bit-integrations-pro')],
            ['id' => 'eventin-5', 'title' => __('Speaker (or Organizer) Updated', 'bit-integrations-pro'), 'note' => __('Runs after a speaker or organizer is updated.', 'bit-integrations-pro')],
            ['id' => 'eventin-6', 'title' => __('Speaker (or Organizer) Deleted', 'bit-integrations-pro'), 'note' => __('Runs after an speaker or organizer is deleted.', 'bit-integrations-pro')],
            ['id' => 'eventin-7', 'title' => __('Attendee Updated', 'bit-integrations-pro'), 'note' => __('Runs after attendee is updated.', 'bit-integrations-pro')],
            ['id' => 'eventin-8', 'title' => __('Attendee Deleted', 'bit-integrations-pro'), 'note' => __('Runs after attendee is deleted.', 'bit-integrations-pro')],
            ['id' => 'eventin-9', 'title' => __('Order Created', 'bit-integrations-pro'), 'note' => __('Runs after a new order is created (or ticket purchased).', 'bit-integrations-pro')],
            ['id' => 'eventin-10', 'title' => __('Order Deleted', 'bit-integrations-pro'), 'note' => __('Runs after an order is deleted.', 'bit-integrations-pro')],
            ['id' => 'eventin-11', 'title' => __('Schedule Deleted', 'bit-integrations-pro'), 'note' => __('Runs after a schedule is deleted.', 'bit-integrations-pro')],
        ];

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Eventin'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        return EventinHelper::getFields($data);
    }

    public static function handleEventCreated($event, $request)
    {
        if (empty($event) || empty($request)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-1');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatEventCreatedData($request);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-1', $data, $flows);
        }
    }

    public static function handleEventUpdated($event, $request)
    {
        if (empty($event) || empty($request)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-2');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatEventUpdatedData($request);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-2', $data, $flows);
        }
    }

    public static function handleEventDeleted($eventId)
    {
        if (empty($eventId)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-3');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = ['event_id' => $eventId];

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-3', $data, $flows);
        }
    }

    public static function handleSpeakerCreated($created, $request)
    {
        if (empty($created) || empty($request)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-4');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatSpeakerCreatedData($request);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-4', $data, $flows);
        }
    }

    public static function handleSpeakerUpdated($speaker, $request)
    {
        if (empty($speaker) || empty($request)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-5');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatSpeakerUpdatedData($request);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-5', $data, $flows);
        }
    }

    public static function handleSpeakerDeleted($speakerId)
    {
        if (empty($speakerId)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-6');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = ['id' => $speakerId];

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-6', $data, $flows);
        }
    }

    public static function handleAttendeeUpdated($attendee, $request)
    {
        if (empty($attendee) || empty($request)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-7');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatAttendeeUpdatedData($request);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-7', $data, $flows);
        }
    }

    public static function handleAttendeeDeleted($attendeeId)
    {
        if (empty($attendeeId)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-8');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = ['id' => $attendeeId];

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-8', $data, $flows);
        }
    }

    public static function handleOrderCreate($order)
    {
        if (empty($order)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-9');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = EventinHelper::formatOrderCreateData($order);

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-9', $data, $flows);
        }
    }

    public static function handleOrderDeleted($orderId)
    {
        if (empty($orderId)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-10');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = ['id' => $orderId];

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-10', $data, $flows);
        }
    }

    public static function handleScheduleDeleted($scheduleId)
    {
        if (empty($scheduleId)) {
            return;
        }

        $flows = Flow::exists('Eventin', 'eventin-11');

        if (empty($flows) || !$flows) {
            return;
        }

        $data = ['id' => $scheduleId];

        if (!empty($data)) {
            Flow::execute('Eventin', 'eventin-11', $data, $flows);
        }
    }
}
