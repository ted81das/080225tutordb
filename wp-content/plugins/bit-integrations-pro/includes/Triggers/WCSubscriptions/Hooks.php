<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WCSubscriptions\WCSubscriptionsController;

Hooks::add('woocommerce_subscription_status_cancelled', [WCSubscriptionsController::class, 'handleSubscriptionCancelled'], 10, 1);
Hooks::add('woocommerce_subscription_payment_complete', [WCSubscriptionsController::class, 'handleVariableSubscriptionPurchases'], 10, 1);
Hooks::add('woocommerce_subscription_renewal_payment_failed', [WCSubscriptionsController::class, 'handleSubscriptionRenewalPaymentFailed'], 10, 2);
Hooks::add('woocommerce_subscription_renewal_payment_complete', [WCSubscriptionsController::class, 'handleSubscriptionRenews'], 10, 2);
Hooks::add('woocommerce_subscription_payment_complete', [WCSubscriptionsController::class, 'handleSubscribeToProduct'], 10, 1);
Hooks::add('woocommerce_subscription_status_expired', [WCSubscriptionsController::class, 'handleSubscriptionExpired'], 10, 1);
Hooks::add('woocommerce_scheduled_subscription_trial_end', [WCSubscriptionsController::class, 'handleSubscriptionTrialEnd'], 10, 1);
Hooks::add('woocommerce_subscription_status_updated', [WCSubscriptionsController::class, 'handleSubscriptionStatusUpdated'], 10, 3);
