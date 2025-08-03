<?php

namespace BitApps\BTCBI_PRO\Triggers\WCMemberships;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WCMembershipsController
{
    public static function info()
    {
        return [
            'name'              => 'WooCommerce Memberships',
            'title'             => __('Woocommerce memberships plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => self::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'woocommerce_memberships/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'woocommerce_memberships/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'woocommerce_memberships/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!self::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WooCommerce Memberships'));
        }

        wp_send_json_success(WCMembershipsHelper::forms());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleMembershipPlanAdded($membershipPlan, $data)
    {
        return WCMembershipsHelper::handleMembershipPlanSaved($membershipPlan, $data, 'wc_memberships_user_membership_saved');
    }

    public static function handleMembershipPlanUpdated($membershipPlan, $data)
    {
        if (empty($data['is_update'])) {
            return;
        }

        return WCMembershipsHelper::handleMembershipPlanSaved($membershipPlan, $data, 'wc_memberships_user_membership_updated');
    }

    public static function handleUserMembershipDeleted($userMembership)
    {
        if (empty($userMembership->user_id)) {
            return;
        }

        $data = WCMembershipsHelper::formatMemberShipData($userMembership->plan_id ?? '', $userMembership->user_id, $userMembership);

        return self::flowExecute('wc_memberships_user_membership_deleted', $data);
    }

    public static function handleMembershipUserRoleUpdated($user, $toRole, $fromRole)
    {
        if (empty($user->ID)) {
            return;
        }

        $data = Helper::prepareFetchFormatFields(array_merge(User::get($user->ID), ['to_role' => $toRole, 'from_role' => $fromRole]));

        return self::flowExecute('wc_memberships_member_user_role_updated', $data);
    }

    public static function handleMembershipNoteAdded($args)
    {
        if (empty($args) || empty($args['user_membership_id']) || !\function_exists('wc_memberships_get_user_membership')) {
            return;
        }

        $userMembership = wc_memberships_get_user_membership($args['user_membership_id']);
        $membershipPlan = $userMembership->get_plan();

        $extra = ['membership_note' => $args['membership_note'], 'notify' => $args['notify']];
        $data = WCMembershipsHelper::formatMemberShipData($membershipPlan->id ?? '', $userMembership->user_id, $membershipPlan, null, null, $extra);

        return self::flowExecute('wc_memberships_new_user_membership_note', $data);
    }

    public static function handleUserMembershipActivation($userMembership, $wasPaused, $previousStatus)
    {
        if (empty($userMembership) || empty($userMembership->user_id)) {
            return;
        }

        $membershipPlan = $userMembership->get_plan();
        $extra = ['was_paused' => $wasPaused, 'previous_status' => $previousStatus];
        $data = WCMembershipsHelper::formatMemberShipData($membershipPlan->id ?? '', $userMembership->user_id, $membershipPlan, null, null, $extra);

        return self::flowExecute('wc_memberships_user_membership_activated', $data);
    }

    public static function handleUserMembershipPaused($userMembership)
    {
        if (empty($userMembership) || empty($userMembership->user_id)) {
            return;
        }

        $membershipPlan = $userMembership->get_plan();
        $data = WCMembershipsHelper::formatMemberShipData($membershipPlan->id ?? '', $userMembership->user_id, $membershipPlan);

        return self::flowExecute('wc_memberships_user_membership_paused', $data);
    }

    public static function handleUserMembershipTransferred($userMembership, $newOwner, $previousOwner)
    {
        if (empty($userMembership) || empty($userMembership->user_id) || empty($newOwner) || empty($previousOwner)) {
            return;
        }

        $membershipPlan = $userMembership->get_plan();
        $extra = ['new_owner' => User::get($newOwner->ID), 'previous_owner' => User::get($previousOwner->ID)];
        $data = WCMembershipsHelper::formatMemberShipData($membershipPlan->id ?? '', $userMembership->user_id, $membershipPlan, null, null, $extra);

        return self::flowExecute('wc_memberships_user_membership_transferred', $data);
    }

    public static function handleMembershipPlanStatusCancelled($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'cancelled') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_cancelled'
        );
    }

    public static function handleMembershipPlanStatusExpires($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'expired') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_expires'
        );
    }

    public static function handleMembershipPlanStatusDelayed($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'delayed') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_delayed'
        );
    }

    public static function handleMembershipPlanStatusComplimentary($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'complimentary') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_complimentary'
        );
    }

    public static function handleMembershipPlanStatusPaused($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'paused') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_paused'
        );
    }

    public static function handleMembershipPlanStatusPendingCancellation($userMembership, $oldStatus, $newStatus)
    {
        if ($newStatus !== 'pending') {
            return;
        }

        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_pending'
        );
    }

    public static function handleUsersMembershipStatusIsChanged($userMembership, $oldStatus, $newStatus)
    {
        return WCMembershipsHelper::handleMembershipStatusChanged(
            $userMembership,
            $oldStatus,
            $newStatus,
            'wc_memberships_user_membership_status_changed'
        );
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

    private static function isPluginInstalled()
    {
        return class_exists('WooCommerce') && class_exists('WC_Memberships_Loader');
    }
}
