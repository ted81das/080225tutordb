<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ProfileGrid\ProfileGridController;

Hooks::add('ProfileGrid_after_create_group', [ProfileGridController::class, 'handleGroupCreated'], 10, 1);
Hooks::add('profilegrid_group_delete', [ProfileGridController::class, 'handleGroupDeleted'], 10, 1);
Hooks::add('profilegrid_group_manager_resets_password', [ProfileGridController::class, 'handleGroupManagerResetsPassword'], 10, 2);
Hooks::add('pm_user_membership_request_approve', [ProfileGridController::class, 'handleMembershipRequestApproved'], 10, 2);
Hooks::add('pm_user_membership_request_denied', [ProfileGridController::class, 'handleMembershipRequestDenied'], 10, 2);
Hooks::add('profilegrid_join_group_request', [ProfileGridController::class, 'handleNewMembershipRequest'], 10, 2);
Hooks::add('profilegrid_payment_complete', [ProfileGridController::class, 'handlePaymentComplete'], 10, 2);
Hooks::add('profilegrid_payment_failed', [ProfileGridController::class, 'handlePaymentFailed'], 10, 2);
Hooks::add('profile_magic_join_group_additional_process', [ProfileGridController::class, 'handleUserAddedToGroup'], 10, 2);
Hooks::add('pm_assign_group_manager_privilege', [ProfileGridController::class, 'handleUserAssignedGroupManager'], 10, 2);
Hooks::add('pg_user_leave_group', [ProfileGridController::class, 'handleUserRemovedFromGroup'], 10, 2);
Hooks::add('pm_unassign_group_manager_privilege', [ProfileGridController::class, 'handleUserUnAssignedGroupManager'], 10, 2);
