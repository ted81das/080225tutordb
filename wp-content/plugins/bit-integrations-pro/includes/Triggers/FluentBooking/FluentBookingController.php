<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentBooking;

use BitCode\FI\Flow\Flow;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingFieldService;

final class FluentBookingController
{
    public static function info()
    {
        $plugin_path = 'fluent-booking-pro/fluent-booking-pro.php';

        return [
            'name'           => 'Fluent Booking',
            'title'          => __('Fluent Booking', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'fluent-booking-pro/fluent-booking-pro.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('fluent-booking-pro/fluent-booking-pro.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'fluentbooking/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'fluentbooking/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('fluent-booking-pro/fluent-booking-pro.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Fluent Booking'));
        }

        $types = ['Booking Scheduled', 'Booking Completed', 'Booking Cancelled'];
        $tasks = [];

        foreach ($types as $key => $item) {
            $key = $key + 1;
            $tasks[] = (object) [
                'id'    => 'fluentBooking-' . $key,
                'title' => $item,
            ];
        }

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('fluent-booking-pro/fluent-booking-pro.php')) {
            wp_send_json_error(__('Fluent Booking is not installed or activated', 'bit-integrations-pro'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Event doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id) {
            $events = CalendarSlot::where('status', 'active')->get();
            $all_events = [];

            if ($events) {
                foreach ($events as $event) {
                    $all_events[] = [
                        'id'    => $event->id,
                        'title' => $event->title,
                    ];
                }
            }

            $responseData['events'] = $all_events;
        }

        wp_send_json_success($responseData);
    }

    public static function formattedParam($data)
    {
        return (object) ['id' => isset($data->flow_details->selectedEvent) ? $data->flow_details->selectedEvent : -1];
    }

    public static function fields($data)
    {
        $id = $data->id;

        if (empty($id)) {
            return;
        }

        $calendarSlot = CalendarSlot::find($id);
        $bookingFields = BookingFieldService::getBookingFields($calendarSlot);
        $fields = [];

        foreach ($bookingFields as $bookingField) {
            if ($bookingField['enabled']) {
                $fields[] = [
                    'name'  => $bookingField['name'],
                    'type'  => $bookingField['type'],
                    'label' => $bookingField['label'],
                ];
            }
        }

        $otherFileds = [
            [
                'name'  => 'id',
                'type'  => 'text',
                'label' => __('ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'calendar_id',
                'type'  => 'text',
                'label' => __('Calender Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'event_id',
                'type'  => 'text',
                'label' => __('Event Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'person_time_zone',
                'type'  => 'text',
                'label' => __('Person Time Zone', 'bit-integrations-pro')
            ],
            [
                'name'  => 'location_type',
                'type'  => 'text',
                'label' => __('Location Type', 'bit-integrations-pro')
            ],
            [
                'name'  => 'location_description',
                'type'  => 'text',
                'label' => __('Location Description', 'bit-integrations-pro')
            ],
            [
                'name'  => 'start_time',
                'type'  => 'text',
                'label' => __('Start Time', 'bit-integrations-pro')
            ],
            [
                'name'  => 'end_time',
                'type'  => 'text',
                'label' => __('End Time', 'bit-integrations-pro')
            ],
            [
                'name'  => 'slot_minutes',
                'type'  => 'text',
                'label' => __('Slot Minutes', 'bit-integrations-pro')
            ],
            [
                'name'  => 'status',
                'type'  => 'text',
                'label' => __('Status', 'bit-integrations-pro')
            ],
            [
                'name'  => 'event_type',
                'type'  => 'text',
                'label' => __('Event Type', 'bit-integrations-pro')
            ],
            [
                'name'  => 'cancelled_by',
                'type'  => 'text',
                'label' => __('Cancelled By', 'bit-integrations-pro')
            ],
        ];

        if (!empty($fields)) {
            $fields = array_merge($fields, $otherFileds);
        }

        return $fields;
    }

    public static function handleFluentBookingScheduledSubmit($booking, $calendarSlot)
    {
        $eventId = $booking['event_id'];
        $flows = Flow::exists('FluentBooking', 'fluentBooking-1');
        $flows = self::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows || empty($eventId)) {
            return;
        }

        $formData = self::handleBookingData($booking, $calendarSlot);

        Flow::execute('FluentBooking', 'fluentBooking-1', $formData, $flows);
    }

    public static function handleFluentBookingCompletedSubmit($booking, $calendarSlot)
    {
        $eventId = $booking['event_id'];
        $flows = Flow::exists('FluentBooking', 'fluentBooking-2');
        $flows = self::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows || empty($eventId)) {
            return;
        }

        $formData = self::handleBookingData($booking, $calendarSlot);

        Flow::execute('FluentBooking', 'fluentBooking-2', $formData, $flows);
    }

    public static function handleFluentBookingCancelledSubmit($booking, $calendarSlot)
    {
        $eventId = $booking['event_id'];
        $flows = Flow::exists('FluentBooking', 'fluentBooking-3');
        $flows = self::flowFilter($flows, 'selectedEvent', $eventId);

        if (empty($flows) || !$flows || empty($eventId)) {
            return;
        }

        $formData = self::handleBookingData($booking, $calendarSlot);

        Flow::execute('FluentBooking', 'fluentBooking-3', $formData, $flows);
    }

    public static function handleBookingData($booking, $calendarSlot)
    {
        $customFieldsData = $booking->getCustomFormData(false);
        $bookingArray = $booking->toArray();

        unset($bookingArray['calendar_event']);

        $formData = [];

        foreach ($bookingArray as $key => $item) {
            if ($key === 'first_name') {
                $name[] = $item;
            } elseif ($key === 'last_name') {
                if (!empty($item)) {
                    $name[] = $item;
                }
            } elseif ($key === 'location_details') {
                $locationArrayKeys = array_keys($item);
                $formData['location_type'] = $item[$locationArrayKeys[0]];
                $formData['location_description'] = $item[$locationArrayKeys[1]];
            } else {
                $formData[$key] = $item;
            }
        }

        if (!empty($name)) {
            $formData['name'] = implode(' ', $name);
        }

        $customData = [];

        if (!empty($customFieldsData)) {
            foreach ($customFieldsData as $key => $item) {
                if (\is_array($item)) {
                    $customData[$key] = implode(',', $item);
                } else {
                    $customData[$key] = $item;
                }
            }
        }

        return array_merge($formData, $customData);
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (isset($flow->flow_details->{$key}) && $flow->flow_details->{$key} == $value) {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }
}
