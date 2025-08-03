<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\UltimateMember\UltimateMemberController;

Hooks::add('um_user_login', [UltimateMemberController::class, 'handleUserLogViaForm'], 9, 1);
Hooks::add('um_registration_complete', [UltimateMemberController::class, 'handleUserRegisViaForm'], 10, 2);
Hooks::add('set_user_role', [UltimateMemberController::class, 'handleUserRoleChange'], 10, 3);
Hooks::add('set_user_role', [UltimateMemberController::class, 'handleUserSpecificRoleChange'], 10, 3);
