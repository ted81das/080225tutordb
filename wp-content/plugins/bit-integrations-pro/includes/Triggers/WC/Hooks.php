<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WC\WCControllerPro;
use BitApps\BTCBI_PRO\Triggers\WC\WCHelperPro;

Hooks::filter('btcbi_woocommerce_flexible_checkout_fields', [WCHelperPro::class, 'getFlexibleCheckoutFields'], 10, 1);
Hooks::filter('btcbi_woocommerce_flexible_checkout_fields_value', [WCHelperPro::class, 'getFlexibleCheckoutFieldsValue'], 10, 1);

// WooCommerce Coupon & Cart
Hooks::add('woocommerce_update_coupon', [WCControllerPro::class, 'handleCouponCreated'], 10, 2);
Hooks::add('woocommerce_add_to_cart', [WCControllerPro::class, 'handleAddToCart'], 10, 6);
Hooks::add('woocommerce_cart_item_removed', [WCControllerPro::class, 'handleRemovedFromCart'], 10, 2);

// WooCommerce Order Status Changed
Hooks::add('woocommerce_order_status_pending', [WCControllerPro::class, 'handleOrderStatusPending'], 10, 1);
Hooks::add('woocommerce_order_status_failed', [WCControllerPro::class, 'handleOrderStatusFailed'], 10, 1);
Hooks::add('woocommerce_order_status_on-hold', [WCControllerPro::class, 'handleOrderStatusOnHold'], 10, 1);
Hooks::add('woocommerce_order_status_processing', [WCControllerPro::class, 'handleOrderStatusProcessing'], 10, 1);
Hooks::add('woocommerce_order_status_completed', [WCControllerPro::class, 'handleOrderStatusCompleted'], 10, 1);
Hooks::add('woocommerce_order_status_refunded', [WCControllerPro::class, 'handleOrderStatusRefunded'], 10, 1);
Hooks::add('woocommerce_order_status_cancelled', [WCControllerPro::class, 'handleOrderStatusCancelled'], 10, 1);
Hooks::add('woocommerce_order_status_changed', [WCControllerPro::class, 'handleRestoreOrder'], 10, 4);

// WooCommerce Product Status Changed
Hooks::add('transition_post_status', [WCControllerPro::class, 'handleProductStatusChanged'], 10, 3);
Hooks::add('transition_post_status', [WCControllerPro::class, 'handleRestoreProduct'], 10, 3);
