<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\EDD\EDDController;

Hooks::add('edd_complete_purchase', [EDDController::class, 'handlePurchaseProduct'], 10, 1);
Hooks::add('edd_complete_purchase', [EDDController::class, 'handlePurchaseProductDiscountCode'], 10, 3);
Hooks::add('edds_payment_refunded', [EDDController::class, 'handleOrderRefunded'], 10, 1);
