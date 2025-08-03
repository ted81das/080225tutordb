<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Dokan\DokanController;

Hooks::add('dokan_before_create_vendor', [DokanController::class, 'handleVendorAdd'], 10, 2);
Hooks::add('dokan_before_update_vendor', [DokanController::class, 'handleVendorUpdate'], 10, 2);
Hooks::add('delete_user', [DokanController::class, 'handleVendorDelete'], 10, 1);
Hooks::add('dokan_refund_request_created', [DokanController::class, 'dokanRefundRequest'], 10, 1);
Hooks::add('dokan_pro_refund_approved', [DokanController::class, 'dokanRefundApproved'], 10, 3);
Hooks::add('dokan_pro_refund_cancelled', [DokanController::class, 'dokanRefundCancelled'], 10, 1);
Hooks::add('dokan_new_seller_created', [DokanController::class, 'dokanUserToVendor'], 10, 2);
Hooks::add('dokan_after_withdraw_request', [DokanController::class, 'dokanWithdrawRequest'], 10, 3);
