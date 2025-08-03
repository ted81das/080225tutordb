<?php

namespace BitApps\BTCBI_PRO\Triggers\FunnelKitAutomations;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class FunnelKitAutomationsController
{
    public static function info()
    {
        return [
            'name'              => 'FunnelKit Automations',
            'title'             => __('FunnelKit Automations is a WordPress Customer Support plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => FunnelKitAutomationsHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/funnelkit-automation-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'funnel_kit_automations/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'funnel_kit_automations/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'funnel_kit_automations/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!FunnelKitAutomationsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'FunnelKit Automations'));
        }

        wp_send_json_success([
            ['form_name' => __('Contact Added to List', 'bit-integrations-pro'), 'triggered_entity_id' => 'bwfan_contact_added_to_lists', 'skipPrimaryKey' => true],
            ['form_name' => __('Contact Removed from List', 'bit-integrations-pro'), 'triggered_entity_id' => 'bwfan_contact_removed_from_lists', 'skipPrimaryKey' => true],
            ['form_name' => __('Tag Added to Contact', 'bit-integrations-pro'), 'triggered_entity_id' => 'bwfan_tags_added_to_contact', 'skipPrimaryKey' => true],
            ['form_name' => __('Tag Removed from Contact', 'bit-integrations-pro'), 'triggered_entity_id' => 'bwfan_tags_removed_from_contact', 'skipPrimaryKey' => true],
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

    public static function handleContactAddedToList($lists, $bwfcm_contact)
    {
        if (!isset($bwfcm_contact->contact)) {
            return;
        }

        $formData = FunnelKitAutomationsHelper::getContactData($bwfcm_contact->contact);
        $formData['lists'] = FunnelKitAutomationsHelper::getListData($lists);

        return static::flowExecute('bwfan_contact_added_to_lists', $formData);
    }

    public static function handleContactRemovedFromList($lists, $bwfcm_contact)
    {
        if (!isset($bwfcm_contact->contact)) {
            return;
        }

        $formData = FunnelKitAutomationsHelper::getContactData($bwfcm_contact->contact);
        $formData['lists'] = FunnelKitAutomationsHelper::getListData($lists);

        return static::flowExecute('bwfan_contact_removed_from_lists', $formData);
    }

    public static function handleTagAddedToContact($tags, $bwfcm_contact)
    {
        if (!isset($bwfcm_contact->contact)) {
            return;
        }

        $formData = FunnelKitAutomationsHelper::getContactData($bwfcm_contact->contact);
        $formData['tags'] = FunnelKitAutomationsHelper::getTagData($tags);

        return static::flowExecute('bwfan_tags_added_to_contact', $formData);
    }

    public static function handleTagRemovedFromContact($tags, $bwfcm_contact)
    {
        if (!isset($bwfcm_contact->contact)) {
            return;
        }

        $formData = FunnelKitAutomationsHelper::getContactData($bwfcm_contact->contact);
        $formData['tags'] = FunnelKitAutomationsHelper::getTagData($tags);

        return static::flowExecute('bwfan_tags_removed_from_contact', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $formData = Helper::prepareFetchFormatFields($formData);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('FunnelKitAutomations', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('FunnelKitAutomations', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
