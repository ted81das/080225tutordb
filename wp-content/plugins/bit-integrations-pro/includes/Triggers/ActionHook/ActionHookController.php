<?php

namespace BitApps\BTCBI_PRO\Triggers\ActionHook;

use WP_Error;
use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;

class ActionHookController
{
    public static function info()
    {
        return [
            'name'              => 'Action Hook',
            'title'             => __('Get callback data through a HOOK', 'bit-integrations-pro'),
            'type'              => 'action_hook',
            'is_active'         => true,
            'documentation_url' => 'https://bitapps.pro/docs/bit-integrations/trigger/action-hook-integrations',
            'tutorial_url'      => 'https://youtu.be/pZ-8JuZfIco?si=Xxv857hJjv6p5Tcu',
            'fetch'             => [
                'action' => 'action_hook/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'action_hook/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getTestData($data)
    {
        if (!property_exists($data, 'hook_id')) {
            wp_send_json_error(\sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), 'ActionHook ID'));
        }

        $testData = get_option('btcbi_action_hook_test_data');

        if (empty($testData) || $testData === false) {
            update_option('btcbi_action_hook_test_data', [$data->hook_id => []]);
        } elseif (empty($testData[$data->hook_id])) {
            $testData[$data->hook_id] = [];
            update_option('btcbi_action_hook_test_data', $testData);
        }

        if (empty($testData[$data->hook_id])) {
            wp_send_json_error(new WP_Error('actionHook_test', __('ActionHook data is empty', 'bit-integrations-pro')));
        }

        wp_send_json_success(['actionHook' => $testData[$data->hook_id]]);
    }

    public static function actionHookHandler(...$args)
    {
        $args = ActionHookHelper::convertToSimpleArray($args);
        $option = get_option('btcbi_action_hook_test_data');

        if (isset($option[current_action()]) && \is_array($option[current_action()])) {
            $option[current_action()] = $args;
            update_option('btcbi_action_hook_test_data', $option);
        }

        return ['type' => 'success'];
    }

    public function removeTestData($data)
    {
        if (!property_exists($data, 'hook_id') || empty($data->hook_id)) {
            wp_send_json_error(\sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), 'ActionHook ID'));
        }

        $items = get_option('btcbi_action_hook_test_data');

        if (!empty($data->reset)) {
            $items[$data->hook_id] = [];
        } else {
            unset($items[$data->hook_id]);
        }

        $result = update_option('btcbi_action_hook_test_data', $items ?? []);

        if (!$result) {
            wp_send_json_error(new WP_Error('actionHook_test', __('Failed to remove test data', 'bit-integrations-pro')));
        }

        wp_send_json_success(__('ActionHook test data removed successfully', 'bit-integrations-pro'));
    }

    public static function handle(...$args)
    {
        if ($flows = Flow::exists('ActionHook', current_action())) {
            foreach ($flows as $flow) {
                $flowDetails = json_decode($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                $args = ActionHookHelper::convertToSimpleArray($args);
                $primaryKeyValue = Helper::extractValueFromPath($args, $flowDetails->primaryKey->key, 'ActionHook');

                if ($flowDetails->primaryKey->value === $primaryKeyValue) {
                    $fieldKeys = [];
                    $formatedData = [];

                    if ($flowDetails->body->data && \is_array($flowDetails->body->data)) {
                        $fieldKeys = array_map(function ($field) use ($args) {
                            return $field->key ?? null;
                        }, $flowDetails->body->data);
                    } elseif (isset($flowDetails->field_map) && \is_array($flowDetails->field_map)) {
                        $fieldKeys = array_map(function ($field) use ($args) {
                            return $field->formField;
                        }, $flowDetails->field_map);
                    }

                    foreach ($fieldKeys as $key) {
                        $formatedData[$key] = Helper::extractValueFromPath($args, $key, 'ActionHook');
                    }

                    Flow::execute('ActionHook', current_action(), $formatedData, [$flow]);
                }
            }
        }

        return ['type' => 'success'];
    }
}
