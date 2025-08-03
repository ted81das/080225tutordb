<?php

namespace BitApps\BTCBI_PRO\Triggers\Coblocks;

use BitCode\FI\Flow\Flow;
use WP_Error;

class CoblocksController
{
    public static function info()
    {
        return [
            'name'      => 'CoBlocks Form',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'coblocks',
            'is_active' => true,
            'isPro'     => true
        ];
    }

    public function getTestData()
    {
        $testData = get_option('btcbi_test_coblocks_form_submit');

        if ($testData === false) {
            update_option('btcbi_test_coblocks_form_submit', []);
        }
        if (!$testData || empty($testData)) {
            wp_send_json_error(new WP_Error('coblocks_test', __('Coblocks data is empty', 'bit-integrations-pro')));
        }
        wp_send_json_success(['coblocks' => $testData]);
    }

    public function removeTestData($data)
    {
        $testData = delete_option('btcbi_test_coblocks_form_submit');

        if (!$testData) {
            wp_send_json_error(new WP_Error('coblocks_test', __('Failed to remove test data', 'bit-integrations-pro')));
        }
        wp_send_json_success(__('coblocks test data removed successfully', 'bit-integrations-pro'));
    }

    public static function coblocksHandler(...$args)
    {
        if (get_option('btcbi_test_coblocks_form_submit') !== false) {
            update_option('btcbi_test_coblocks_form_submit', $args);
        }

        if ($flows = Flow::exists('Coblocks', current_action())) {
            foreach ($flows as $flow) {
                $flowDetails = json_decode($flow->flow_details);
                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                $primaryKeyValue = self::extractValueFromPath($args, $flowDetails->primaryKey->key);

                if ($flowDetails->primaryKey->value->value === $primaryKeyValue['value']) {
                    $fieldKeys = [];
                    $formatedData = [];

                    if (isset($flowDetails->body->data) && $flowDetails->body->data && \is_array($flowDetails->body->data)) {
                        $fieldKeys = array_map(function ($field) use ($args) {
                            return $field->key;
                        }, $flowDetails->body->data);
                    } elseif (isset($flowDetails->field_map) && \is_array($flowDetails->field_map)) {
                        $fieldKeys = array_map(function ($field) use ($args) {
                            return $field->formField;
                        }, $flowDetails->field_map);
                    }
                    // var_dump($args);
                    // die;

                    foreach ($fieldKeys as $key) {
                        $formatedData[$key] = \is_array(self::extractValueFromPath($args, $key)) ? self::extractValueFromPath($args, $key)['value'] : self::extractValueFromPath($args, $key);
                    }

                    Flow::execute('Coblocks', current_action(), $formatedData, [$flow]);
                }
            }
        }

        return rest_ensure_response(['status' => 'success']);
    }

    private static function extractValueFromPath($data, $path)
    {
        $parts = \is_array($path) ? $path : explode('.', $path);
        if (\count($parts) === 0) {
            return $data;
        }

        $currentPart = array_shift($parts);

        if (\is_array($data)) {
            if (!isset($data[$currentPart])) {
                wp_send_json_error(new WP_Error('Coblocks', __('Index out of bounds or invalid', 'bit-integrations-pro')));
            }

            return self::extractValueFromPath($data[$currentPart], $parts);
        }

        if (\is_object($data)) {
            if (!property_exists($data, $currentPart)) {
                wp_send_json_error(new WP_Error('Coblocks', __('Invalid path', 'bit-integrations-pro')));
            }

            return self::extractValueFromPath($data->{$currentPart}, $parts);
        }

        wp_send_json_error(new WP_Error('Coblocks', __('Invalid path', 'bit-integrations-pro')));
    }
}
