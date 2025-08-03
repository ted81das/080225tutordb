<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\NewUserApprove\NewUserApproveController;

Hooks::add('new_user_approve_user_approved', [NewUserApproveController::class, 'handleUserApproved'], 20, 1);
Hooks::add('new_user_approve_user_denied', [NewUserApproveController::class, 'handleUserDenied'], 20, 1);
Hooks::add('new_user_approve_user_status_update', [NewUserApproveController::class, 'handleUserStatusUpdated'], 20, 2);
Hooks::add('new_user_approve_approve_user', [NewUserApproveController::class, 'handleUserStatusApprove'], 20, 1);
Hooks::add('new_user_approve_deny_user', [NewUserApproveController::class, 'handleUserStatusDeny'], 20, 1);
