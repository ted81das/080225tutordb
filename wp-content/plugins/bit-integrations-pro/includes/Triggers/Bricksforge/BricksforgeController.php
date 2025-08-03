<?php

namespace BitApps\BTCBI_PRO\Triggers\Bricksforge;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BricksforgeController
{
    public static function info()
    {
        return [
            'name'              => 'Bricksforge',
            'title'             => __('Bricksforge', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => true,
            'documentation_url' => 'https://bitapps.pro/docs/bit-integrations/trigger/bricksforge-integration/',
            'tutorial_url'      => 'https://youtube.com/playlist?list=PL7c6CDwwm-AJvSYtsYiyH7O0CuV661H0s&si=F356gvJYMyckZrW_',
            'tasks'             => [
                'action' => 'bricksforge/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'bricksforge/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'bricksforge/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        // if (!static::is_bricks_active()) {
        //     wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Bricksforge'));
        // }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'bricksforge/pro_forms/after_submit', 'skipPrimaryKey' => false],
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('bricksforge');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'bricksforge');
    }

    public static function handleBricksforgeSubmit(...$record)
    {
        $formData = BricksforgeHelper::setFields($record[0]);

        if (get_option('btcbi_bricksforge_test') !== false) {
            update_option('btcbi_bricksforge_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $record[0]['formId']]]
            ]);
        }

        if ($flows = Flow::exists('Bricksforge', current_action())) {
            foreach ($flows as $flow) {
                $flowDetails = static::parseFlowDetails($flow->flow_details);

                if (!isset($flowDetails->primaryKey)) {
                    continue;
                }

                if (BricksforgeHelper::isPrimaryKeysMatch($record[0], $flowDetails)) {
                    $data = BricksforgeHelper::prepareDataForFlow($record[0]);
                    Flow::execute('Bricksforge', current_action(), $data, [$flow]);
                }
            }
        }

        return ['type' => 'success'];
    }

    private static function parseFlowDetails($flowDetails)
    {
        return \is_string($flowDetails) ? json_decode($flowDetails) : $flowDetails;
    }
}
