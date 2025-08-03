<?php

namespace BitApps\BTCBI_PRO\Triggers\WCMemberships;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

final class WCMembershipsHelper
{
    public static function handleMembershipPlanSaved($membershipPlan, $data, $triggeredEntityId)
    {
        if (empty($data['user_id']) || empty($data['user_membership_id']) || !\function_exists('wc_memberships_get_user_membership')) {
            return;
        }

        if (empty($membershipPlan)) {
            $userMembership = wc_memberships_get_user_membership($data['user_membership_id']);
            $membershipPlan = $userMembership->get_plan();
        }
        $data = self::formatMemberShipData($membershipPlan->id ?? '', $data['user_id'], $membershipPlan);

        return WCMembershipsController::flowExecute($triggeredEntityId, $data);
    }

    public static function handleMembershipStatusChanged($userMembership, $oldStatus, $newStatus, $triggeredEntityId)
    {
        if (empty($userMembership) || !\function_exists('wc_memberships_get_user_membership')) {
            return;
        }

        $membershipPlan = $userMembership->get_plan() ?? wc_memberships_get_user_membership($userMembership);
        $userId = $userMembership->user_id ?? $membershipPlan->user_id ?? '';
        $planId = $userMembership->plan_id ?? $membershipPlan->plan_id ?? '';

        if (empty($userId)) {
            return;
        }

        $data = self::formatMemberShipData($planId, $userId, $membershipPlan, $oldStatus, $newStatus);

        return WCMembershipsController::flowExecute($triggeredEntityId, $data);
    }

    public static function flowExecute($triggeredEntityId, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggeredEntityId}_test", array_values($formData));

        $flows = Flow::exists('WCMemberships', $triggeredEntityId);
        if (!$flows) {
            return;
        }

        Flow::execute('WCMemberships', $triggeredEntityId, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    public static function formatMemberShipData($planId, $userId, $membershipPlan, $oldStatus = null, $newStatus = null, $extra = [])
    {
        $data = [
            'membership_plan_id'   => $planId,
            'membership_plan_name' => $membershipPlan->name ?? $membershipPlan->plan->name ?? '',
            'membership_plan_slug' => $membershipPlan->slug ?? $membershipPlan->plan->slug ?? '',
            'membership_plan_type' => !empty($planId) ? get_post_meta($planId, '_access_method', true) : '',
        ];

        if (!empty($oldStatus)) {
            $data['membership_plan_old_status'] = $oldStatus ?? '';
        }
        if (!empty($newStatus)) {
            $data['membership_plan_new_status'] = $newStatus ?? '';
        }

        return Helper::prepareFetchFormatFields(array_merge($data, User::get($userId), $extra));
    }

    public static function forms()
    {
        return [
            [
                'form_name'           => __('User Added to Membership Plan', 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_saved',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __('User Update to Membership Plan', 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_updated',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Deleted", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_deleted',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __('Membership user Role Updated', 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_member_user_role_updated',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __('New Membership Note Added', 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_new_user_membership_note',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Activation or Re-Activation", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_activated',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Paused", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_paused',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Transferred", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_transferred',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Delayed", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_delayed',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Cancelled", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_cancelled',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Expires", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_expires',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Complimentary", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_complimentary',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Pending Cancellation", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_pending',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's Membership Status Set to Paused", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_paused',
                'skipPrimaryKey'      => true
            ],
            [
                'form_name'           => __("User's membership status is changed", 'bit-integrations-pro'),
                'triggered_entity_id' => 'wc_memberships_user_membership_status_changed',
                'skipPrimaryKey'      => true
            ],
        ];
    }
}
