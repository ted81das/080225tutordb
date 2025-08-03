<?php

namespace BitApps\BTCBI_PRO\Triggers\WCSubscriptions;

use WC_Customer;

final class WCSubscriptionsHelper
{
    public static function mapSubscriptionData($subscription)
    {
        $customer = new WC_Customer($subscription->get_user_id());
        $lineItems = static::getLineItems($subscription->get_items());
        $dates = [
            'trial_end'    => (bool) $subscription->get_date('trial_end') == 0 ? 'N/A' : $subscription->get_date('trial_end'),
            'next_payment' => (bool) $subscription->get_date('next_payment') == 0 ? 'N/A' : $subscription->get_date('next_payment'),
            'end_date'     => (bool) $subscription->get_date('end_date') == 0 ? 'N/A' : $subscription->get_date('end_date'),
        ];

        return [
            'id'                   => $subscription->get_id(),
            'name'                 => 'Subscription #' . $subscription->get_order_number(),
            'status'               => $subscription->get_status(),
            'order_number'         => $subscription->get_order_number(),
            'order_key'            => $subscription->get_order_key(),
            'parent_id'            => $subscription->get_parent_id(),
            'currency'             => $subscription->get_currency(),
            'prices_include_tax'   => $subscription->get_prices_include_tax(),
            'discount_total'       => $subscription->get_discount_total(),
            'discount_tax'         => $subscription->get_discount_tax(),
            'shipping_total'       => $subscription->get_shipping_total(),
            'shipping_tax'         => $subscription->get_shipping_tax(),
            'cart_tax'             => $subscription->get_cart_tax(),
            'total_tax'            => $subscription->get_total_tax(),
            'total'                => $subscription->get_total(),
            'payment_method'       => $subscription->get_payment_method(),
            'payment_method_title' => $subscription->get_payment_method_title(),
            'transaction_id'       => $subscription->get_transaction_id(),
            'customer_note'        => $subscription->get_customer_note(),
            'billing_period'       => $subscription->get_billing_period(),
            'billing_interval'     => $subscription->get_billing_interval(),
            'suspension_count'     => $subscription->get_suspension_count(),
            'date_created'         => gmdate('Y-m-d H:i:s', $subscription->get_date_created('edit')->getTimestamp()) ?? null,
            'date_modified'        => gmdate('Y-m-d H:i:s', $subscription->get_date_modified('edit')->getTimestamp()) ?? null,
            'trial_end_date'       => $dates['trial_end'],
            'next_payment_date'    => $dates['next_payment'],
            'end_date'             => $dates['end_date'],
            'customer_id'          => $customer->get_id(),
            'first_name'           => $customer->get_first_name(),
            'last_name'            => $customer->get_last_name(),
            'email'                => $customer->get_email(),
            'username'             => $customer->get_username(),
            'display_name'         => $customer->get_display_name(),
            'is_paying_customer'   => $customer->get_is_paying_customer(),
            'billing_first_name'   => $customer->get_billing_first_name(),
            'billing_last_name'    => $customer->get_billing_last_name(),
            'billing_company'      => $customer->get_billing_company(),
            'billing_address_1'    => $customer->get_billing_address_1(),
            'billing_address_2'    => $customer->get_billing_address_2(),
            'billing_city'         => $customer->get_billing_city(),
            'billing_postcode'     => $customer->get_billing_postcode(),
            'billing_country'      => $customer->get_billing_country(),
            'billing_state'        => $customer->get_billing_state(),
            'billing_email'        => $customer->get_billing_email(),
            'billing_phone'        => $customer->get_billing_phone(),
            'shipping_first_name'  => $customer->get_shipping_first_name(),
            'shipping_last_name'   => $customer->get_shipping_last_name(),
            'shipping_company'     => $customer->get_shipping_company(),
            'shipping_address_1'   => $customer->get_shipping_address_1(),
            'shipping_address_2'   => $customer->get_shipping_address_2(),
            'shipping_city'        => $customer->get_shipping_city(),
            'shipping_postcode'    => $customer->get_shipping_postcode(),
            'shipping_country'     => $customer->get_shipping_country(),
            'shipping_state'       => $customer->get_shipping_state(),
            'line_items'           => wp_json_encode($lineItems)
        ];
    }

    public static function getFilteredFlows($flows, $entityId, $subscription, $status = null)
    {
        return array_filter($flows, function ($flow) use ($entityId, $subscription, $status) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
            }

            $flowDetails = $flow->flow_details;
            $matchProduct = false;
            $matchesSubscription = empty($flowDetails->selectedSubscription)
                || $flowDetails->selectedSubscription === 'any'
                || $flowDetails->selectedSubscription == $subscription->get_id();

            foreach ($subscription->get_items() as $item) {
                if (empty($flowDetails->selectedProduct)
                    || $flowDetails->selectedProduct === 'any'
                    || $flowDetails->selectedProduct == $item->get_product_id()) {
                    $matchProduct = true;

                    break;
                }
            }

            if (\in_array($entityId, [
                'user_cancels_subscription',
                'user_subscription_expires',
                'user_subscription_trial_end',
                'user_renews_subscription',
                'user_renewal_subscription_payment_failed'
            ])) {
                return $matchesSubscription && $matchProduct;
            }

            if ($entityId === 'user_subscribes_to_product' || $entityId === 'user_purchases_variable_subscription') {
                return $matchProduct;
            }

            if ($entityId === 'user_subscription_status_updated') {
                $matchesStatus = empty($flowDetails->selectedStatus)
                    || $flowDetails->selectedStatus === 'any'
                    || $flowDetails->selectedStatus == $status;

                return $matchesSubscription && $matchesStatus && $matchProduct;
            }

            return false;
        });
    }

    public static function getAllSubscriptions($status = null)
    {
        $subscriptions = wcs_get_subscriptions(['subscription_status' => ['active', 'on-hold', 'pending', 'cancelled', 'pending-cancel', 'trash']]);

        return array_merge([['value' => 'any', 'label' => 'Any Subscriptions']], array_map(function ($subscription) {
            return [
                'value' => (string) $subscription->get_id(),
                'label' => 'Subscription #' . $subscription->get_order_number(),
            ];
        }, $subscriptions));
    }

    public static function getAllSubscriptionsProducts($triggerEntityId)
    {
        if (!class_exists('WooCommerce')) {
            return [['value' => 'any', 'label' => 'Any subscription Product']];
        }

        $types = $triggerEntityId === 'user_purchases_variable_subscription' ? ['variable-subscription', 'subscription_variation'] : ['subscription', 'variable-subscription', 'subscription_variation'];
        $products = wc_get_products(['type' => $types]);

        return array_merge([['value' => 'any', 'label' => 'Any subscription Product']], array_map(function ($product) {
            return [
                'value' => (string) $product->get_id(),
                'label' => get_the_title($product->get_id())
            ];
        }, $products));
    }

    public static function getSubscriptionFields()
    {
        $fields = [
            [
                'name'  => 'id',
                'type'  => 'number',
                'label' => __('Subscription Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'name',
                'type'  => 'text',
                'label' => __('Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'status',
                'type'  => 'text',
                'label' => __('Subscription status', 'bit-integrations-pro')
            ],
            [
                'name'  => 'order_number',
                'type'  => 'text',
                'label' => __('Subscription Order Number', 'bit-integrations-pro')
            ],
            [
                'name'  => 'order_key',
                'type'  => 'text',
                'label' => __('Subscription Order Key', 'bit-integrations-pro')
            ],
            [
                'name'  => 'parent_id',
                'type'  => 'number',
                'label' => __('Parent Order Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'line_items',
                'type'  => 'array',
                'label' => __('Line Items', 'bit-integrations-pro')
            ],
            [
                'name'  => 'currency',
                'type'  => 'text',
                'label' => __('Currency', 'bit-integrations-pro')
            ],
            [
                'name'  => 'prices_include_tax',
                'type'  => 'number',
                'label' => __('Prices Include Tax', 'bit-integrations-pro')
            ],
            [
                'name'  => 'discount_total',
                'type'  => 'number',
                'label' => __('Discount Total Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'discount_tax',
                'type'  => 'number',
                'label' => __('Discount Tax Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_total',
                'type'  => 'number',
                'label' => __('Shipping Total Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_tax',
                'type'  => 'number',
                'label' => __('Shipping Tax Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'cart_tax',
                'type'  => 'number',
                'label' => __('Cart Tax Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'total_tax ',
                'type'  => 'number',
                'label' => __('Recurring Total Tax Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'total',
                'type'  => 'number',
                'label' => __('Total Amount', 'bit-integrations-pro')
            ],
            [
                'name'  => 'payment_method',
                'type'  => 'text',
                'label' => __('Payment Method', 'bit-integrations-pro')
            ],
            [
                'name'  => 'payment_method_title',
                'type'  => 'text',
                'label' => __('Payment Method Title', 'bit-integrations-pro')
            ],
            [
                'name'  => 'transaction_id',
                'type'  => 'number',
                'label' => __('Transaction Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'customer_note',
                'type'  => 'text',
                'label' => __('Customer Note', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_period',
                'type'  => 'text',
                'label' => __('Billing Period', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_interval',
                'type'  => 'text',
                'label' => __('Billing Interval', 'bit-integrations-pro')
            ],
            [
                'name'  => 'suspension_count',
                'type'  => 'text',
                'label' => __('Suspension Count', 'bit-integrations-pro')
            ],
            [
                'name'  => 'date_created',
                'type'  => 'date',
                'label' => __('Start Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'date_modified',
                'type'  => 'date',
                'label' => __('Modified Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'trial_end_date',
                'type'  => 'date',
                'label' => __('Trial End Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'next_payment_date',
                'type'  => 'date',
                'label' => __('Next Payment Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'end_date',
                'type'  => 'date',
                'label' => __('End Date', 'bit-integrations-pro')
            ],
        ];

        return array_merge($fields, static::customer_fields(), static::billing_address(), static::shipping_address());
    }

    private static function getLineItems($items)
    {
        $lineItems = [];
        foreach ($items as $item) {
            $product = $item->get_product();

            $lineItems[] = [
                'product_id'         => $item->get_product_id(),
                'variation_id'       => $item->get_variation_id(),
                'product_name'       => $item->get_name(),
                'quantity'           => $item->get_quantity(),
                'subtotal'           => $item->get_subtotal(),
                'total'              => $item->get_total(),
                'subtotal_tax'       => $item->get_subtotal_tax(),
                'tax_class'          => $item->get_tax_class(),
                'tax_status'         => $item->get_tax_status(),
                'product_sku'        => $product->get_sku(),
                'product_unit_price' => $product->get_price(),
                'product_urls'       => get_permalink(wp_get_post_parent_id($product->get_id()))
            ];
        }

        return $lineItems;
    }

    private static function customer_fields()
    {
        return [
            [
                'name'  => 'customer_id',
                'type'  => 'number',
                'label' => __('Customer Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'first_name',
                'type'  => 'text',
                'label' => __('First Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'last_name',
                'type'  => 'text',
                'label' => __('Last Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'email',
                'type'  => 'text',
                'label' => __('Email', 'bit-integrations-pro')
            ],
            [
                'name'  => 'username',
                'type'  => 'text',
                'label' => __('Username', 'bit-integrations-pro')
            ],
            [
                'name'  => 'display_name',
                'type'  => 'text',
                'label' => __('Display Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'is_paying_customer',
                'type'  => 'text',
                'label' => __('Is Paying Customer', 'bit-integrations-pro')
            ],
        ];
    }

    private static function billing_address()
    {
        return [
            [
                'name'  => 'billing_first_name',
                'type'  => 'text',
                'label' => __('Billing First Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_last_name',
                'type'  => 'text',
                'label' => __('Billing Last Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_company',
                'type'  => 'text',
                'label' => __('Billing Company', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_address_1',
                'type'  => 'text',
                'label' => __('Billing Address 1', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_address_2',
                'type'  => 'text',
                'label' => __('Billing Address 2', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_city',
                'type'  => 'text',
                'label' => __('Billing City', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_postcode',
                'type'  => 'number',
                'label' => __('Billing Post Code', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_country',
                'type'  => 'text',
                'label' => __('Billing Country', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_state',
                'type'  => 'text',
                'label' => __('Billing State', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_email',
                'type'  => 'text',
                'label' => __('Billing Email', 'bit-integrations-pro')
            ],
            [
                'name'  => 'billing_phone',
                'type'  => 'text',
                'label' => __('Billing Phone', 'bit-integrations-pro')
            ]
        ];
    }

    private static function shipping_address()
    {
        return [
            [
                'name'  => 'shipping_first_name',
                'type'  => 'text',
                'label' => __('Shipping First Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_last_name',
                'type'  => 'text',
                'label' => __('Shipping Last Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_company',
                'type'  => 'text',
                'label' => __('Shipping Company', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_address_1',
                'type'  => 'text',
                'label' => __('Shipping Address 1', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_address_2',
                'type'  => 'text',
                'label' => __('Shipping Address 2', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_city',
                'type'  => 'text',
                'label' => __('Shipping City', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_postcode',
                'type'  => 'number',
                'label' => __('Shipping Post Code', 'bit-integrations-pro')
            ],
            [
                'name'  => 'shipping_country',
                'type'  => 'text',
                'label' => __('Shipping Country', 'bit-integrations-pro')
            ]
        ];
    }
}
