<?php

namespace BitApps\BTCBI_PRO\Triggers\Bricks;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BricksController
{
    public static function info()
    {
        return [
            'name'              => 'Bricks',
            'title'             => __('Build With Confidence', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => self::is_bricks_active(),
            'documentation_url' => 'https://bitapps.pro/docs/bit-integrations/trigger/bricks-integrations/',
            'tutorial_url'      => 'https://www.youtube.com/playlist?list=PL7c6CDwwm-AKyyZhh4_n-MqdeaFv6x4iK',
            'note'              => '<p>' . __('Select <b>"Custom"</b> as a form submit actions from your Bricks Builder sidebar', 'bit-integrations-pro') . '</p>',
            'tasks'             => [
                'action' => 'bricks/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'bricks/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'bricks/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::is_bricks_active()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Bricks'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'bricks/form/custom_action', 'skipPrimaryKey' => false],
        ]);
    }

    public static function is_bricks_active()
    {
        return wp_get_theme()->get_template() === 'bricks';
    }

    public function getTestData()
    {
        return TriggerController::getTestData('bricks');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'bricks');
    }

    public static function handle_bricks_submit($form)
    {
        $fields = $form->get_fields();
        $settings = $form->get_settings();
        $files = $form->get_uploaded_files();

        $recordData = BricksHelper::extractRecordData($fields, $settings, $files);
        $formData = BricksHelper::setFields($recordData);

        if (get_option('btcbi_bricks_test') !== false) {
            update_option('btcbi_bricks_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $recordData['id']]]
            ]);
        }

        $flows = BricksHelper::fetchFlows($recordData['id']);

        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) && ($flow->triggered_entity_id == $recordData['id'])) {
                $data = BricksHelper::prepareDataForFlow($fields, $files);

                Flow::execute('Bricks', $flow->triggered_entity_id, $data, [$flow]);

                continue;
            }

            if (\is_array($flowDetails->primaryKey) && BricksHelper::isPrimaryKeysMatch($recordData, $flowDetails)) {
                $data = array_column($formData, 'value', 'name');

                Flow::execute('Bricks', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
