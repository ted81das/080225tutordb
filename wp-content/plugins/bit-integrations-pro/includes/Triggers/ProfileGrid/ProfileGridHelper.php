<?php

namespace BitApps\BTCBI_PRO\Triggers\ProfileGrid;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class ProfileGridHelper
{
    public static function isPluginInstalled()
    {
        return class_exists('Profile_Magic');
    }

    public static function formatGroupData($group_id)
    {
        global $wpdb;
        $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}promag_groups WHERE id = %d", $group_id), ARRAY_A);

        return [
            'group_id'             => $group_id,
            'group_name'           => $group['group_name'],
            'group_description'    => $group['group_desc'],
            'group_limit'          => $group['group_limit'],
            'group_limit_message'  => $group['group_limit_message'],
            'group_associate_role' => $group['associate_role'],
        ];
    }

    public static function FormatGroupUserData($gid, $user_id)
    {
        if (empty($gid) || empty($user_id)) {
            return;
        }

        $user = User::get($user_id);
        $group = ProfileGridHelper::formatGroupData($gid);

        return Helper::prepareFetchFormatFields(array_merge($user, $group));
    }

    public static function tasks()
    {
        return [
            ['form_name' => __('Group Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'ProfileGrid_after_create_group', 'skipPrimaryKey' => true],
            ['form_name' => __('Group Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'profilegrid_group_delete', 'skipPrimaryKey' => true],
            ['form_name' => __('Group Manager Resets User Password', 'bit-integrations-pro'), 'triggered_entity_id' => 'profilegrid_group_manager_resets_password', 'skipPrimaryKey' => true],
            ['form_name' => __('Membership Request Approved', 'bit-integrations-pro'), 'triggered_entity_id' => 'pm_user_membership_request_approve', 'skipPrimaryKey' => true],
            ['form_name' => __('Membership Request Denied', 'bit-integrations-pro'), 'triggered_entity_id' => 'pm_user_membership_request_denied', 'skipPrimaryKey' => true],
            ['form_name' => __('New Membership Request', 'bit-integrations-pro'), 'triggered_entity_id' => 'profilegrid_join_group_request', 'skipPrimaryKey' => true],
            ['form_name' => __('Payment Complete', 'bit-integrations-pro'), 'triggered_entity_id' => 'profilegrid_payment_complete', 'skipPrimaryKey' => true],
            ['form_name' => __('Payment Failed', 'bit-integrations-pro'), 'triggered_entity_id' => 'profilegrid_payment_failed', 'skipPrimaryKey' => true],
            ['form_name' => __('User Added to Group', 'bit-integrations-pro'), 'triggered_entity_id' => 'profile_magic_join_group_additional_process', 'skipPrimaryKey' => true],
            ['form_name' => __('User Assigned Group Manager', 'bit-integrations-pro'), 'triggered_entity_id' => 'pm_assign_group_manager_privilege', 'skipPrimaryKey' => true],
            ['form_name' => __('User Removed from Group', 'bit-integrations-pro'), 'triggered_entity_id' => 'pg_user_leave_group', 'skipPrimaryKey' => true],
            ['form_name' => __('User UnAssigned Group Manager', 'bit-integrations-pro'), 'triggered_entity_id' => 'pm_unassign_group_manager_privilege', 'skipPrimaryKey' => true],
        ];
    }
}
