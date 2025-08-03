<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ProfileBuilder\ProfileBuilderController;

Hooks::add('wppb_register_success', [ProfileBuilderController::class, 'handleUserRegistration'], 20, 3);
Hooks::add('wppb_edit_profile_success', [ProfileBuilderController::class, 'handleUserUpdate'], 20, 3);
Hooks::add('wppb_activate_user', [ProfileBuilderController::class, 'handleUserEmailConfirmed'], 20, 3);
Hooks::add('wppb_after_sending_email', [ProfileBuilderController::class, 'handleSendEmail'], 20, 6);
Hooks::add('wppb_after_user_approval', [ProfileBuilderController::class, 'handleAdminApproval'], 20, 1);
Hooks::add('wppb_after_user_unapproval', [ProfileBuilderController::class, 'handleAdminUnApproval'], 20, 1);
