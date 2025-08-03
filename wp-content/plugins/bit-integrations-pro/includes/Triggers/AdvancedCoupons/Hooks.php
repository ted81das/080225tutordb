<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AdvancedCoupons\AdvancedCouponsController;

Hooks::add('acfw_after_save_coupon', [AdvancedCouponsController::class, 'handleUserSaveCoupon'], 10, 2);
Hooks::add('acfw_create_store_credit_entry', [AdvancedCouponsController::class, 'handleUserStoreCreditExceeds'], 10, 1);
Hooks::add('acfw_create_store_credit_entry', [AdvancedCouponsController::class, 'handleUserLifetimeCreditExceeds'], 10, 1);
Hooks::add('acfw_create_store_credit_entry', [AdvancedCouponsController::class, 'handleUserReceivesStoreCredit'], 10, 1);
Hooks::add('acfw_create_store_credit_entry', [AdvancedCouponsController::class, 'handleUserAdjustStoreCredit'], 10, 1);
