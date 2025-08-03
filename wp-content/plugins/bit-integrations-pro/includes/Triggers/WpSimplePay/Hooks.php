<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WpSimplePay\WpSimplePayController;

Hooks::add('simpay_webhook_payment_intent_succeeded', [WpSimplePayController::class, 'handlePaymentForFormCompleted'], 20, 2);
Hooks::add('simpay_webhook_subscription_created', [WpSimplePayController::class, 'handleSubscriptionForFormCreated'], 20, 2);
Hooks::add('simpay_webhook_invoice_payment_succeeded', [WpSimplePayController::class, 'handleSubscriptionForFormRenewed'], 20, 2);
