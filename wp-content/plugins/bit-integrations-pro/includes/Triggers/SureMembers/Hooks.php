<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SureMembers\SureMembersController;

Hooks::add('suremembers_after_access_grant', [SureMembersController::class, 'handleSureMembersAccessGrant'], 10, 2);
Hooks::add('suremembers_after_access_revoke', [SureMembersController::class, 'handleSureMembersAccessRevoke'], 10, 2);
Hooks::add('suremembers_after_submit_form', [SureMembersController::class, 'handleSureMembersGroupUpdated'], 10, 1);
