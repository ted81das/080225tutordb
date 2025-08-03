<?php

namespace BitApps\BTCBI_PRO\Triggers\WC;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Triggers\WC\WCHelper;

class WCControllerPro
{
    public const RESTORE_ORDER = 21;

    public const RESTORE_PRODUCT = 22;

    public const NEW_COUPON_CREATED = 23;

    public const PRODUCT_STATUS_CHANGED = 24;

    public const PRODUCT_ADD_TO_CART = 25;

    public const PRODUCT_REMOVE_FROM_CART = 26;

    public const ORDER_STATUS_SET_TO_PENDING = 27;

    public const ORDER_STATUS_SET_TO_FAILED = 28;

    public const ORDER_STATUS_SET_TO_ON_HOLD = 29;

    public const ORDER_STATUS_SET_TO_PROCESSING = 30;

    public const ORDER_STATUS_SET_TO_COMPLETED = 31;

    public const ORDER_STATUS_SET_TO_REFUNDED = 32;

    public const ORDER_STATUS_SET_TO_CANCELLED = 33;

    public static function handleRestoreOrder($orderId, $oldStatus, $newStatus, $order)
    {
        if (!static::isActivate() || empty($orderId) || $oldStatus != 'trash') {
            return false;
        }

        return self::executeOrderTrigger($orderId, static::RESTORE_ORDER);
    }

    public static function handleCouponCreated($couponId, $coupon)
    {
        if (empty($couponId)) {
            return;
        }

        return self::execute(static::NEW_COUPON_CREATED, self::getCouponData($couponId, $coupon));
    }

    public static function handleAddToCart($cartItemKey, $productId, $quantity, $variationId, $variation, $cartItemData)
    {
        if (empty($cartItemKey) || empty($productId)) {
            return;
        }

        $cart = WC()->cart;
        $cartData = [
            'cart_item_key'   => $cartItemKey,
            'product_id'      => $productId,
            'quantity'        => $quantity,
            'variation_id'    => $variationId,
            'variation'       => $variation,
            'cart_item_data'  => $cartItemData,
            'cart_total'      => $cart->get_cart_contents_total(),
            'cart_line_items' => self::getCartLineItems(),
        ];

        return self::execute(static::PRODUCT_ADD_TO_CART, $cartData);
    }

    public static function handleRemovedFromCart($cartItemKey, $cart)
    {
        if (empty($cartItemKey) || empty($cart)) {
            return;
        }

        $cartData = [
            'cart_item_key'         => $cartItemKey,
            'applied_coupons'       => $cart->applied_coupons,
            'cart_session_data'     => $cart->cart_session_data,
            'removed_cart_contents' => array_values($cart->removed_cart_contents)
        ];

        return self::execute(static::PRODUCT_ADD_TO_CART, $cartData);
    }

    public static function handleOrderStatusPending($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_PENDING);
    }

    public static function handleOrderStatusFailed($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_FAILED);
    }

    public static function handleOrderStatusOnHold($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_ON_HOLD);
    }

    public static function handleOrderStatusProcessing($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_PROCESSING);
    }

    public static function handleOrderStatusCompleted($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_COMPLETED);
    }

    public static function handleOrderStatusRefunded($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_REFUNDED);
    }

    public static function handleOrderStatusCancelled($orderId)
    {
        return self::executeOrderTrigger($orderId, static::ORDER_STATUS_SET_TO_CANCELLED);
    }

    public static function handleProductStatusChanged($postId, $newStatus, $oldStatus)
    {
        if (!static::isActivate() || $oldStatus === 'new' || empty($post) || $post->post_type != 'product') {
            return false;
        }

        return self::executeProductTriggers($postId, static::PRODUCT_STATUS_CHANGED, ['old_status' => $oldStatus, 'new_status' => $newStatus]);
    }

    public static function handleRestoreProduct($postId, $newStatus, $oldStatus)
    {
        if (!static::isActivate() || $oldStatus === 'new' || empty($post) || $post->post_type != 'product') {
            return false;
        }

        return self::executeProductTriggers($postId, static::RESTORE_PRODUCT);
    }

    private static function getCouponData($couponId, $coupon)
    {
        $couponData = $coupon->get_data();

        return [
            'coupon_id'              => $couponId,
            'coupon_code'            => $couponData['code'],
            'coupon_amount'          => $couponData['amount'],
            'coupon_status'          => $couponData['status'],
            'discount_type'          => $couponData['discount_type'],
            'description'            => $couponData['description'],
            'date_created'           => \is_null($coupon->get_date_created()) ? $coupon->get_date_created() : $coupon->get_date_created()->format('Y-m-d H:i:s'),
            'date_modified'          => \is_null($coupon->get_date_modified()) ? $coupon->get_date_modified() : $coupon->get_date_modified()->format('Y-m-d H:i:s'),
            'date_expires'           => \is_null($coupon->get_date_expires()) ? $coupon->get_date_expires() : $coupon->get_date_expires()->format('Y-m-d H:i:s'),
            'usage_count'            => $couponData['usage_count'],
            'usage_limit'            => $couponData['usage_limit'],
            'usage_limit_per_user'   => $couponData['usage_limit_per_user'],
            'limit_usage_to_x_items' => $couponData['limit_usage_to_x_items'],
            'free_shipping'          => $couponData['free_shipping'],
            'exclude_sale_items'     => $couponData['exclude_sale_items'],
            'minimum_amount'         => $couponData['minimum_amount'],
            'maximum_amount'         => $couponData['maximum_amount'],
            'virtual'                => $couponData['virtual'],
        ];
    }

    private static function getCartLineItems()
    {
        $cart = WC()->cart;
        $cartLineItems = array_map(
            function ($cartItem) {
                return [
                    'product'            => $cartItem['data'],
                    'product_id'         => $cartItem['product_id'],
                    'variation_id'       => $cartItem['variation_id'],
                    'quantity'           => $cartItem['quantity'],
                    'product_name'       => $cartItem['data']->get_name(),
                    'tax_class'          => $cartItem['data']->get_tax_class(),
                    'tax_status'         => $cartItem['data']->get_tax_status(),
                    'product_sku'        => $cartItem['data']->get_sku(),
                    'product_unit_price' => $cartItem['data']->get_price(),
                ];
            },
            $cart->get_cart()
        );

        return wp_json_encode(array_values($cartLineItems));
    }

    private static function executeProductTriggers($postId, $triggeredEntityId, $extra = [])
    {
        if (empty($postId) || empty($triggeredEntityId)) {
            return;
        }

        $productData = WCHelper::processProductData($postId, $extra);

        return self::execute($triggeredEntityId, $productData);
    }

    private static function executeOrderTrigger($orderId, $triggeredEntityId)
    {
        if (!static::isActivate() || empty($orderId)) {
            return false;
        }

        $orderData = WCHelper::processOrderData($orderId);

        return self::execute($triggeredEntityId, $orderData);
    }

    private static function execute($triggeredEntityId, $data)
    {
        $flows = Flow::exists('WC', $triggeredEntityId);

        if (empty($flows)) {
            return false;
        }

        Flow::execute('WC', $triggeredEntityId, $data, $flows);
    }

    private static function isActivate()
    {
        return class_exists('WooCommerce');
    }
}
