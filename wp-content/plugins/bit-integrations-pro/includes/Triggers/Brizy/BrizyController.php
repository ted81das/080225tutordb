<?php

namespace BitApps\BTCBI_PRO\Triggers\Brizy;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BrizyController
{
    public static function info()
    {
        return [
            'name'              => 'Brizy',
            'title'             => __('Brizy is the platform web creators choose to build professional WordPress websites, grow their skills, and build their business. Start for free today!', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => BrizyHelper::isPluginInstalled(),
            'documentation_url' => 'https://bitapps.pro/docs/bit-integrations/trigger/brizy-integrations/',
            'tutorial_url'      => 'https://www.youtube.com/playlist?list=PL7c6CDwwm-AJYTO5YvgqLeNdbWzGUmC_h',
            'tasks'             => [
                'action' => 'brizy/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'brizy/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'brizy/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!BrizyHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Brizy'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'brizy_form_submit_data', 'skipPrimaryKey' => false],
        ]);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('brizy');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'brizy');
    }

    public static function handle_brizy_submit($fields, $form)
    {
        if (!method_exists($form, 'getId')) {
            return $fields;
        }

        $recordData = BrizyHelper::extractRecordData($fields, $form->getId());
        $formData = BrizyHelper::setFields($recordData);

        if (get_option('btcbi_brizy_test') !== false) {
            update_option('btcbi_brizy_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $form->getId()]]
            ]);
        }

        $formId = $form->getId();
        $flows = BrizyHelper::fetchFlows($formId);

        if (!$flows) {
            return $fields;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) && $flow->triggered_entity_id == $formId) {
                $data = BrizyHelper::parseOldIntegrationsData($fields);
                Flow::execute('Brizy', $formId, $data, $flows);

                continue;
            }

            if (\is_array($flowDetails->primaryKey) && BrizyHelper::isPrimaryKeysMatch($recordData, $flowDetails)) {
                $data = array_column($formData, 'value', 'name');
                Flow::execute('Brizy', $flow->triggered_entity_id, $data, [$flow]);

                continue;
            }
        }

        return $fields;
    }
}
