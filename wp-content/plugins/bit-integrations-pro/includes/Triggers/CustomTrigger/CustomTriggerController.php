<?php

namespace BitApps\BTCBI_PRO\Triggers\CustomTrigger;

use BitApps\BTCBI_PRO\Triggers\ActionHook\ActionHookHelper;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;
use WP_Error;

class CustomTriggerController
{
    public static function info()
    {
        return [
            'name'      => 'Custom Trigger',
            'title'     => __('You can connect bit integrations with any other plugin or theme using this trigger(custom hook)', 'bit-integrations-pro'),
            'type'      => 'custom_trigger',
            'is_active' => true,
            'isPro'     => true
        ];
    }

    public function getNewHook()
    {
        $hook_id = wp_generate_uuid4();

        if (!$hook_id) {
            wp_send_json_error(__('Failed to generate new hook id', 'bit-integrations-pro'));
        }
        add_option('btcbi_custom_trigger_' . $hook_id, [], '', 'no');
        wp_send_json_success(['hook_id' => $hook_id]);
    }

    public function getTestData($data)
    {
        $missing_field = null;
        if (!property_exists($data, 'hook_id') || (property_exists($data, 'hook_id') && !wp_is_uuid($data->hook_id))) {
            $missing_field = \is_null($missing_field) ? 'Custom trigger ID' : $missing_field . ', Webhook ID';
        }
        if (!\is_null($missing_field)) {
            wp_send_json_error(wp_sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), $missing_field));
        }

        $testData = get_option('btcbi_custom_trigger_' . $data->hook_id);
        if ($testData === false) {
            update_option('btcbi_custom_trigger_' . $data->hook_id, []);
        }
        if (!$testData || empty($testData)) {
            wp_send_json_error(new WP_Error('custom_trigger_test', __('Custom trigger data is empty', 'bit-integrations-pro')));
        }
        wp_send_json_success(['custom_trigger' => $testData]);
    }

    public static function handleCustomTrigger($hook_id, $args)
    {
        $optionKey = 'btcbi_custom_trigger_' . $hook_id;
        $args = ActionHookHelper::convertToSimpleArray($args);

        if (get_option($optionKey) !== false) {
            update_option($optionKey, $args);
        }

        if ($flows = Flow::exists('CustomTrigger', $hook_id)) {
            foreach ($flows as $flow) {
                $fieldKeys = [];
                $formatedData = [];
                $flowDetails = json_decode($flow->flow_details);

                if (!empty($flowDetails->body->data) && \is_array($flowDetails->body->data)) {
                    $fieldKeys = array_column($flowDetails->body->data, 'key');
                } elseif (!empty($flowDetails->field_map) && \is_array($flowDetails->field_map)) {
                    $fieldKeys = array_column($flowDetails->field_map, 'formField');
                }

                foreach ($fieldKeys as $key) {
                    $formatedData[$key] = Helper::extractValueFromPath($args, $key, 'CustomTrigger');
                }

                Flow::execute('CustomTrigger', $hook_id, $formatedData, [$flow]);
            }
        }

        return rest_ensure_response(['status' => 'success']);
    }

    public function removeTestData($data)
    {
        $missing_field = null;

        if (!property_exists($data, 'hook_id') || (property_exists($data, 'hook_id') && !wp_is_uuid($data->hook_id))) {
            $missing_field = \is_null($missing_field) ? 'Custom trigger ID' : $missing_field . ', Custom trigger ID';
        }
        if (!\is_null($missing_field)) {
            wp_send_json_error(wp_sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), $missing_field));
        }

        if (property_exists($data, 'reset') && $data->reset) {
            $testData = update_option('btcbi_custom_trigger_' . $data->hook_id, []);
        } else {
            $testData = delete_option('btcbi_custom_trigger_' . $data->hook_id);
        }
        if (!$testData) {
            wp_send_json_error(new WP_Error('webhook_test', __('Failed to remove test data', 'bit-integrations-pro')));
        }
        wp_send_json_success(__('Webhook test data removed successfully', 'bit-integrations-pro'));
    }
}
