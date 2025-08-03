<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Memberpress\MemberpressController;

Hooks::add('mepr-event-transaction-completed', [MemberpressController::class, 'oneTimeMembershipSubscribe'], 10, 1);
Hooks::add('mepr-event-transaction-completed', [MemberpressController::class, 'recurringMembershipSubscribe'], 10, 1);
Hooks::add('mepr_subscription_transition_status', [MemberpressController::class, 'membershipSubscribeCancel'], 10, 3);
Hooks::add('mepr-event-transaction-expired', [MemberpressController::class, 'membershipSubscribeExpire'], 10, 1);
Hooks::add('mepr_subscription_transition_status', [MemberpressController::class, 'membershipSubscribePaused'], 10, 3);
