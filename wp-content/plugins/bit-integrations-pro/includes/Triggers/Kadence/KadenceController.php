<?php

namespace BitApps\BTCBI_PRO\Triggers\Kadence;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class KadenceController
{
    public static function info()
    {
        return [
            'name'                => 'Kadence Blocks Form',
            'title'               => __('Kadence Blocks Form - Flexible and Design-Friendly Contact Form builder plugin for WordPress', 'bit-integrations-pro'),
            'type'                => 'custom_form_submission',
            'is_active'           => self::pluginActive(),
            'documentation_url'   => 'https://bitapps.pro/docs/bit-integrations/trigger/kadence-blocks-form-integrations/',
            'tutorial_url'        => 'https://youtube.com/playlist?list=PL7c6CDwwm-AIYYb6OSx05xgfrQ0v2Eatm&si=g3PLBIvm8b3Kg9Mr',
            'triggered_entity_id' => 'kadence_blocks_form',
            'tasks'               => [
                'action' => 'kadence_blocks/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'kadence_blocks/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'kadence_blocks/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Kadence Blocks Form'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'kadence_blocks_form', 'skipPrimaryKey' => true],
        ]);
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('kadence-blocks-pro/kadence-blocks-pro.php')) {
            return $option === 'get_name' ? 'kadence-blocks-pro/kadence-blocks-pro.php' : true;
        } elseif (is_plugin_active('kadence-blocks/kadence-blocks.php')) {
            return $option === 'get_name' ? 'kadence-blocks/kadence-blocks.php' : true;
        }

        return false;
    }

    public function getTestData()
    {
        return TriggerController::getTestData('kadence');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'kadence');
    }

    public static function handle_kadence_form_submit($form_args, $fields, $form_id, $post_id = null)
    {
        $recordData = KadenceHelper::extractRecordData($form_id, $post_id, $form_args['fields'], $fields);
        $formData = KadenceHelper::setFields($recordData);
        $reOrganizeId = $post_id . '_' . $form_id;

        if (get_option('btcbi_kadence_test') !== false) {
            update_option('btcbi_kadence_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $form_id]]
            ]);
        }

        $flows = KadenceHelper::fetchFlows($form_id, $reOrganizeId);
        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = static::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) && ($flow->triggered_entity_id == $form_id || $flow->triggered_entity_id == $reOrganizeId)) {
                $data = KadenceHelper::prepareDataForFlow($fields);
                Flow::execute('Kadence', $flow->triggered_entity_id, $data, [$flow]);

                continue;
            }

            if (\is_array($flowDetails->primaryKey) && KadenceHelper::isPrimaryKeysMatch($recordData, $flowDetails)) {
                $data = array_column($formData, 'value', 'name');
                Flow::execute('Kadence', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }

    private static function parseFlowDetails($flowDetails)
    {
        return \is_string($flowDetails) ? json_decode($flowDetails) : $flowDetails;
    }
}
