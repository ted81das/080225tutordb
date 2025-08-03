<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\GiveWp\GiveWpController;

Hooks::add('give_update_payment_status', [GiveWpController::class, 'handleUserDonation'], 10, 3);
Hooks::add('give_subscription_cancelled', [GiveWpController::class, 'handleSubscriptionDonationCancel'], 10, 2);
Hooks::add('give_subscription_updated', [GiveWpController::class, 'handleRecurringDonation'], 10, 4);
