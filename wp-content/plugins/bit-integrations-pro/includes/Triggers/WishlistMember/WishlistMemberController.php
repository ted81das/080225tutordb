<?php

namespace BitApps\BTCBI_PRO\Triggers\WishlistMember;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WishlistMemberController
{
    public static function info()
    {
        return [
            'name'              => 'Wishlist Member',
            'title'             => __('Connect with your fans, faster your community.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/wishlist-member-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wishlist_member/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wishlist_member/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wishlist_member/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Wishlist Member'));
        }

        wp_send_json_success([
            ['form_name' => __('User Added to Membership Level', 'bit-integrations-pro'), 'triggered_entity_id' => 'wishlistmember_add_user_levels', 'skipPrimaryKey' => true],
            ['form_name' => __('User Removed from Membership Level', 'bit-integrations-pro'), 'triggered_entity_id' => 'wishlistmember_remove_user_levels', 'skipPrimaryKey' => true],
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

    public static function handleUserAddedToMembershipLevel($user_id, $level_id)
    {
        return static::flowExecute('wishlistmember_add_user_levels', $user_id, $level_id);
    }

    public static function handleUserRemovedFromMembershipLevel($user_id, $level_id)
    {
        return static::flowExecute('wishlistmember_remove_user_levels', $user_id, $level_id);
    }

    private static function flowExecute($triggered_entity_id, $user_id, $level_id)
    {
        $formData = static::formatMembershipData($user_id, $level_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('WishlistMember', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('WishlistMember', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('WLMAPI') || class_exists('WishListMember');
    }

    private static function formatMembershipData($user_id, $level_id)
    {
        $user_id = !empty($user_id) ? $user_id : get_current_user_id();
        $level_id = !\is_int($level_id) && \is_array($level_id) ? reset($level_id) : $level_id;

        if (empty($user_id) || empty($level_id)) {
            return;
        }

        $data = User::get($user_id);

        if (!\function_exists('wlmapi_get_level')) {
            return $data;
        }

        $wm_membership_detail = wlmapi_get_level($level_id);

        if (!\is_array($wm_membership_detail)) {
            return $data;
        }

        $level = $wm_membership_detail['level'] ?? [];
        $data['membership_level_id'] = $level['id'] ?? '';
        $data['membership_level_name'] = $level['name'] ?? '';

        if (!\function_exists('wlmapi_get_member')) {
            return $data;
        }

        $member = wlmapi_get_member($user_id)['member'][0] ?? null;
        if (empty($member)) {
            return $data;
        }

        $memberinfo = $member['UserInfo'] ?? [];
        $member_levels = $member['Levels'] ?? [];

        $data['user_registered_date'] = $memberinfo['user_registered'] ?? '';
        $data['user'] = $memberinfo['wpm_useraddress'] ?? [];

        if (!empty($member_levels[$level_id])) {
            $data['user_registered_date_in_level'] = $member_levels[$level_id]->Timestamp ?? '';
        }

        return Helper::prepareFetchFormatFields($data);
    }
}
