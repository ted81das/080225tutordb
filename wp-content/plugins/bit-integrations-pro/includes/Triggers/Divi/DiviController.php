<?php

namespace BitApps\BTCBI_PRO\Triggers\Divi;

use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class DiviController
{
    public static function info()
    {
        return [
            'name'              => 'Divi',
            'title'             => __('Divi isn\'t just a WordPress theme, it\'s a complete design framework that allows you to design and customize every part of your website from the ground up', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => self::is_divi_active(),
            'documentation_url' => 'https://bitapps.pro/docs/bit-integrations/trigger/divi-integrations/',
            'tutorial_url'      => 'https://youtube.com/playlist?list=PL7c6CDwwm-AJpOfpcioYpZOYbhcY_qTLN&si=PnquORcc8830jEg3',
            'note'              => '<p>' . \sprintf(__('The <b>Divi Email Optin Form Module</b> does not work with <b>%s</b>. Only the <b>Divi Contact Form Module</b> is compatible and works well with <b>%s</b>', 'bit-integrations-pro'), __('Bit Integrations', 'bit-integrations-pro'), __('Bit Integrations', 'bit-integrations-pro')) . '</p>',
            'tasks'             => [
                'action' => 'divi/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'divi/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'divi/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!self::is_divi_active()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Divi'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'et_pb_contact_form_submit', 'skipPrimaryKey' => false],
        ]);
    }

    public static function is_divi_active()
    {
        global $themename;
        if (empty($themename)) {
            return false;
        }

        $diviThemes = [
            'divi',
            'extra',
            'bloom',
            'monarch',
        ];

        return \in_array(strtolower($themename), $diviThemes);
    }

    public function getTestData()
    {
        return TriggerController::getTestData('divi');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'divi');
    }

    public static function handle_divi_submit($et_pb_contact_form_submit, $et_contact_error, $contact_form_info)
    {
        $recordData = DiviHelper::extractRecordData($contact_form_info, $et_pb_contact_form_submit);
        $formData = DiviHelper::setFields($recordData);
        $reOrganizeId = $contact_form_info['contact_form_unique_id'] . '_' . $contact_form_info['contact_form_number'];

        if (get_option('btcbi_divi_test') !== false) {
            update_option('btcbi_divi_test', [
                'formData'   => $formData,
                'primaryKey' => [(object) ['key' => 'id', 'value' => $recordData['id']]]
            ]);
        }

        $flows = DiviHelper::fetchFlows($recordData['id'], $reOrganizeId);
        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) && ($flow->triggered_entity_id == $recordData['id'] || $flow->triggered_entity_id == $reOrganizeId)) {
                $data = DiviHelper::prepareDataForFlow($et_pb_contact_form_submit);
                Flow::execute('Divi', $flow->triggered_entity_id, $data, [$flow]);

                continue;
            }

            if (\is_array($flowDetails->primaryKey) && DiviHelper::isPrimaryKeysMatch($recordData, $flowDetails)) {
                $data = array_column($formData, 'value', 'name');
                Flow::execute('Divi', $flow->triggered_entity_id, $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }
}
