<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\RestrictContent\RestrictContentController;

Hooks::add('rcp_membership_post_activate', [RestrictContentController::class, 'purchasesMembershipLevel'], 10, 2);
Hooks::add('rcp_transition_membership_status_cancelled', [RestrictContentController::class, 'membershipStatusCancelled'], 10, 2);
Hooks::add('rcp_transition_membership_status_expired', [RestrictContentController::class, 'membershipStatusExpired'], 10, 2);
