<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WCMemberships\WCMembershipsController;

Hooks::add('wc_memberships_user_membership_saved', [WCMembershipsController::class, 'handleMembershipPlanAdded'], 99, 2);
Hooks::add('wc_memberships_user_membership_saved', [WCMembershipsController::class, 'handleMembershipPlanUpdated'], 99, 2);
Hooks::add('wc_memberships_user_membership_deleted', [WCMembershipsController::class, 'handleUserMembershipDeleted'], 99, 1);
Hooks::add('wc_memberships_member_user_role_updated', [WCMembershipsController::class, 'handleMembershipUserRoleUpdated'], 99, 3);
Hooks::add('wc_memberships_new_user_membership_note', [WCMembershipsController::class, 'handleMembershipNoteAdded'], 99, 1);
Hooks::add('wc_memberships_user_membership_activated', [WCMembershipsController::class, 'handleUserMembershipActivation'], 99, 3);
Hooks::add('wc_memberships_user_membership_paused', [WCMembershipsController::class, 'handleUserMembershipPaused'], 99, 1);
Hooks::add('wc_memberships_user_membership_transferred', [WCMembershipsController::class, 'handleUserMembershipTransferred'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusCancelled'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusDelayed'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusComplimentary'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusPendingCancellation'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusPaused'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleMembershipPlanStatusExpires'], 99, 3);
Hooks::add('wc_memberships_user_membership_status_changed', [WCMembershipsController::class, 'handleUsersMembershipStatusIsChanged'], 99, 3);
