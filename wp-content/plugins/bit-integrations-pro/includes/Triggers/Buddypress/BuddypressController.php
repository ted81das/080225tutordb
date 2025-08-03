<?php

namespace BitApps\BTCBI_PRO\Triggers\Buddypress;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BuddypressController
{
    public static function info()
    {
        return [
            'name'              => 'BuddyPress',
            'title'             => __('Copy and enter the above webhook URL to your Buddypress webhook setting', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => BuddypressHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/buddypress-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'buddypress/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'buddypress/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'buddypress/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!BuddypressHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'BuddyPress'));
        }

        wp_send_json_success(StaticData::forms());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleSetMemberType($user_id, $member_type)
    {
        if (empty($user_id) || !\is_array($member_type)) {
            return;
        }

        $formData = BuddypressHelper::FormatMemberTypeData($user_id, $member_type);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_bp_set_member_type_test', array_values($formData), 'primaryKey.value', $formData['primaryKey']['value']);

        return static::flowExecute('bp_set_member_type', $formData);
    }

    public static function handleUserPostToGroupActivity($content, $user_id, $group_id, $activity_id)
    {
        if (empty($activity_id)) {
            return;
        }

        $formData = BuddypressHelper::formatGroupPostActivityData($content, $user_id, $group_id, $activity_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_bp_groups_posted_update_test', array_values($formData));

        return static::flowExecute('bp_groups_posted_update', $formData);
    }

    public static function handleActivityPostedUpdate($content, $user_id, $activity_id)
    {
        if (empty($activity_id)) {
            return;
        }

        $formData = BuddypressHelper::formatActivityPostedUpdateData($content, $user_id, $activity_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_bp_activity_posted_update_test', array_values($formData));

        return static::flowExecute('bp_activity_posted_update', $formData);
    }

    public static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('Buddypress', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('Buddypress', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
