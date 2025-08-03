<?php

namespace BitApps\BTCBI_PRO\Triggers\ProfileGrid;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class ProfileGridController
{
    public static function info()
    {
        return [
            'name'              => 'ProfileGrid',
            'title'             => __('Create WordPress user profiles, groups, communities, paid memberships, directories, WooCommerce profiles, bbPress profiles, content restriction, sign-up pages, blog submissions, notifications, social activity and private messaging, beautiful threaded interface and a lot more!', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => ProfileGridHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'profile_grid/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'profile_grid/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'profile_grid/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!ProfileGridHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'ProfileGrid'));
        }

        wp_send_json_success(ProfileGridHelper::tasks());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleGroupCreated($gid)
    {
        if (empty($gid)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(ProfileGridHelper::formatGroupData($gid));

        return static::flowExecute('ProfileGrid_after_create_group', $formData);
    }

    public static function handleGroupDeleted($gid)
    {
        if (empty($gid)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(ProfileGridHelper::formatGroupData($gid));

        return static::flowExecute('profilegrid_group_delete', $formData);
    }

    public static function handleGroupManagerResetsPassword($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(User::get($user_id));

        return static::flowExecute('profilegrid_group_manager_resets_password', $formData);
    }

    public static function handleMembershipRequestApproved($gid, $user_id)
    {
        return static::flowExecute('pm_user_membership_request_approve', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleMembershipRequestDenied($gid, $user_id)
    {
        return static::flowExecute('pm_user_membership_request_denied', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleNewMembershipRequest($gid, $user_id)
    {
        return static::flowExecute('profilegrid_join_group_request', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handlePaymentComplete($gid, $user_id)
    {
        return static::flowExecute('profilegrid_payment_complete', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handlePaymentFailed($gid, $user_id)
    {
        return static::flowExecute('profilegrid_payment_failed', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleUserAddedToGroup($gid, $user_id)
    {
        return static::flowExecute('profile_magic_join_group_additional_process', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleUserAssignedGroupManager($gid, $user_id)
    {
        return static::flowExecute('pm_assign_group_manager_privilege', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleUserRemovedFromGroup($user_id, $gid)
    {
        return static::flowExecute('pg_user_leave_group', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    public static function handleUserUnAssignedGroupManager($gid, $user_id)
    {
        return static::flowExecute('pm_unassign_group_manager_privilege', ProfileGridHelper::FormatGroupUserData($gid, $user_id));
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('ProfileGrid', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('ProfileGrid', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
