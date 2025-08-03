<?php

namespace BitApps\BTCBI_PRO\Triggers\GravityKit;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class GravityKitController
{
    public static function info()
    {
        return [
            'name'              => 'GravityKit',
            'title'             => __('GravityKit is a WordPress Plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => GravityKitHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/gravitykit-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'gravity_kit/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'gravity_kit/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'gravity_kit/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!GravityKitHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'GravityKit'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Entry Approved For A Specific Form', 'bit-integrations-pro'), 'triggered_entity_id' => 'gravityview/approve_entries/approved', 'skipPrimaryKey' => false],
            ['form_name' => __('Form Entry Rejected For A Specific Form', 'bit-integrations-pro'), 'triggered_entity_id' => 'gravityview/approve_entries/disapproved', 'skipPrimaryKey' => false],
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

    public static function handleFormEntryApproved($entity_id)
    {
        return static::flowExecute('gravityview/approve_entries/approved', $entity_id);
    }

    public static function handleFormEntryRejected($entity_id)
    {
        return static::flowExecute('gravityview/approve_entries/disapproved', $entity_id);
    }

    private static function flowExecute($triggered_entity_id, $entity_id)
    {
        if (empty($entity_id) || !class_exists('GFFormsModel') || !class_exists('GFCommon')) {
            return;
        }

        $formData = GravityKitHelper::formatFormEntryData($entity_id);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        $formId = $formData['form_id']['value'] ?? null;
        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData), 'form_id.value', $formId);

        $flows = Flow::exists('GravityKit', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey)) {
                continue;
            }

            if (Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                $data = array_column($formData, 'value', 'name');
                Flow::execute('GravityKit', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
