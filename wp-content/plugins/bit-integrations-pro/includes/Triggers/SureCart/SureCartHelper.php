<?php

namespace BitApps\BTCBI_PRO\Triggers\SureCart;

use SureCart\Models\Account;
use SureCart\Models\Customer;
use SureCart\Models\Product;

final class SureCartHelper
{
    public static function mapFields($id)
    {
        if ($id == 1) {
            $fields = array_merge([
                'Store Name' => (object) [
                    'fieldKey'  => 'store_name',
                    'fieldName' => __('Store Name', 'bit-integrations-pro'),
                ],
                'Store Url' => (object) [
                    'fieldKey'  => 'store_url',
                    'fieldName' => __('Store Url', 'bit-integrations-pro'),
                ],
                'Product Id' => (object) [
                    'fieldKey'  => 'product_id',
                    'fieldName' => __('Product Id', 'bit-integrations-pro'),
                ],
                'Product Name' => (object) [
                    'fieldKey'  => 'product_name',
                    'fieldName' => __('Product Name', 'bit-integrations-pro'),
                ],
                'Product Description' => (object) [
                    'fieldKey'  => 'product_description',
                    'fieldName' => __('Product Description', 'bit-integrations-pro'),
                ],
                'Product Thumb' => (object) [
                    'fieldKey'  => 'product_thumb',
                    'fieldName' => __('Product Thumb', 'bit-integrations-pro'),
                ],
                'Product Thumb Id' => (object) [
                    'fieldKey'  => 'product_thumb_id',
                    'fieldName' => __('Product Thumb Id', 'bit-integrations-pro'),
                ],
                'Product Price Id' => (object) [
                    'fieldKey'  => 'product_price_id',
                    'fieldName' => __('Product Price Id', 'bit-integrations-pro'),
                ],
                'Order Number' => (object) [
                    'fieldKey'  => 'order_number',
                    'fieldName' => __('Order Number', 'bit-integrations-pro'),
                ],
                'Product Price' => (object) [
                    'fieldKey'  => 'product_price',
                    'fieldName' => __('Product Price', 'bit-integrations-pro'),
                ],
                'Product Quantity' => (object) [
                    'fieldKey'  => 'product_quantity',
                    'fieldName' => __('Product Quantity', 'bit-integrations-pro'),
                ],
                'Max Price amount' => (object) [
                    'fieldKey'  => 'max_price_amount',
                    'fieldName' => __('Max Price amount', 'bit-integrations-pro'),
                ],
                'Min Price amount' => (object) [
                    'fieldKey'  => 'min_price_amount',
                    'fieldName' => __('Min Price amount', 'bit-integrations-pro'),
                ],
                'Order Id' => (object) [
                    'fieldKey'  => 'order_id',
                    'fieldName' => __('Order ID', 'bit-integrations-pro'),
                ],
                'Order Date' => (object) [
                    'fieldKey'  => 'order_date',
                    'fieldName' => __('Order Date', 'bit-integrations-pro'),
                ],
                'Order Status' => (object) [
                    'fieldKey'  => 'order_status',
                    'fieldName' => __('Order Status', 'bit-integrations-pro'),
                ],
                'Order Paid Amount' => (object) [
                    'fieldKey'  => 'order_paid_amount',
                    'fieldName' => __('Order Paid Amount', 'bit-integrations-pro'),
                ],
                'Payment Currency' => (object) [
                    'fieldKey'  => 'payment_currency',
                    'fieldName' => __('Payment Currency', 'bit-integrations-pro'),
                ],
                'Payment Method' => (object) [
                    'fieldKey'  => 'payment_method',
                    'fieldName' => __('Payment Method', 'bit-integrations-pro'),
                ],
                'Subscriptions Id' => (object) [
                    'fieldKey'  => 'subscriptions_id',
                    'fieldName' => __('Subscriptions Id', 'bit-integrations-pro'),
                ],
                'customer_id' => (object) [
                    'fieldKey'  => 'customer_id',
                    'fieldName' => __('Customer Id', 'bit-integrations-pro'),
                ],
                'customer_name' => (object) [
                    'fieldKey'  => 'customer_name',
                    'fieldName' => __('Customer Name', 'bit-integrations-pro'),
                ],
                'customer_first_name' => (object) [
                    'fieldKey'  => 'customer_first_name',
                    'fieldName' => __('Customer First Name', 'bit-integrations-pro'),
                ],
                'customer_last_name' => (object) [
                    'fieldKey'  => 'customer_last_name',
                    'fieldName' => __('Customer Last Name', 'bit-integrations-pro'),
                ],
                'customer_email' => (object) [
                    'fieldKey'  => 'customer_email',
                    'fieldName' => __('Customer Email', 'bit-integrations-pro'),
                ],
                'customer_phone' => (object) [
                    'fieldKey'  => 'customer_phone',
                    'fieldName' => __('Customer Phone Number', 'bit-integrations-pro'),
                ],
            ], static::getCheckoutCustomFields());
        } elseif ($id == 2 || $id == 3) {
            $fields = [
                'Store Name' => (object) [
                    'fieldKey'  => 'store_name',
                    'fieldName' => __('Store Name', 'bit-integrations-pro'),
                ],
                'Store Url' => (object) [
                    'fieldKey'  => 'store_url',
                    'fieldName' => __('Store Url', 'bit-integrations-pro'),
                ],
                'Purchase Id' => (object) [
                    'fieldKey'  => 'purchase_id',
                    'fieldName' => __('Purchase Id', 'bit-integrations-pro'),
                ],
                'Revoke Date' => (object) [
                    'fieldKey'  => 'revoke_date',
                    'fieldName' => __('Revoke Date', 'bit-integrations-pro'),
                ],
                'Customer Id' => (object) [
                    'fieldKey'  => 'customer_id',
                    'fieldName' => __('Customer Id', 'bit-integrations-pro'),
                ],
                'Product Id' => (object) [
                    'fieldKey'  => 'product_id',
                    'fieldName' => __('Product Id', 'bit-integrations-pro'),
                ],
                'Product Description' => (object) [
                    'fieldKey'  => 'product_description',
                    'fieldName' => __('Product Description', 'bit-integrations-pro'),
                ],
                'Product_name' => (object) [
                    'fieldKey'  => 'product_name',
                    'fieldName' => __('Product Name', 'bit-integrations-pro'),
                ],
                'Product Image_id' => (object) [
                    'fieldKey'  => 'product_image_id',
                    'fieldName' => __('Product Image Id', 'bit-integrations-pro'),
                ],
                'Product Price' => (object) [
                    'fieldKey'  => 'product_price',
                    'fieldName' => __('Product Price', 'bit-integrations-pro'),
                ],
                'Product Currency' => (object) [
                    'fieldKey'  => 'product_currency',
                    'fieldName' => __('Product Currency', 'bit-integrations-pro'),
                ],
            ];
        }

        return $fields;
    }

    public static function SureCartDataProcess($data)
    {
        $accountDetails = Account::find();
        $product = Product::find($data['product_id']);
        $customer = Customer::find($data['customer_id']);
        $purchaseFinalData = self::purchase_data_process($data['id']);

        return array_merge([
            'store_name'          => $accountDetails['name'],
            'store_url'           => $accountDetails['url'],
            'product_name'        => $product['name'],
            'product_id'          => $product['id'],
            'product_description' => $product['description'],
            'product_thumb_id'    => $purchaseFinalData['product_thumb_id'],
            'product_thumb'       => $purchaseFinalData['product_thumb'],
            'product_price'       => $product->price,
            'product_price_id'    => $purchaseFinalData['product_price_id'],
            'product_quantity'    => $data['quantity'],
            'max_price_amount'    => $product['metrics']->max_price_amount,
            'min_price_amount'    => $product['metrics']->min_price_amount,
            'order_id'            => $purchaseFinalData['order_id'],
            'order_paid_amount'   => $data['order_paid_amount'],
            'payment_currency'    => $accountDetails['currency'],
            'payment_method'      => $purchaseFinalData['payment_method'],
            'subscriptions_id'    => $purchaseFinalData['subscriptions_id'],
            'order_number'        => $purchaseFinalData['order_number'],
            'order_date'          => $purchaseFinalData['order_date'],
            'order_status'        => $purchaseFinalData['order_status'],
            'order_paid_amount'   => $purchaseFinalData['order_paid_amount'],
            'order_subtotal'      => $purchaseFinalData['order_subtotal'],
            'order_total'         => $purchaseFinalData['order_total'],
            'customer_id'         => $customer['id'],
            'customer_name'       => $customer['name'],
            'customer_first_name' => $customer['first_name'],
            'customer_last_name'  => $customer['last_name'],
            'customer_email'      => $customer['email'],
            'customer_phone'      => $customer['phone'],
        ], $purchaseFinalData['metadata']);
    }

    public static function purchase_data_process($id)
    {
        $purchase_data = self::purchase_details($id);
        $price = self::get_price($purchase_data);
        $chekout = $purchase_data->initial_order->checkout;

        return [
            'product'           => $purchase_data->product->name,
            'product_id'        => $purchase_data->product->id,
            'product_thumb_id'  => isset($purchase_data->product->image) ? $purchase_data->product->image : '',
            'product_thumb'     => isset($purchase_data->product->image_url) ? $purchase_data->product->image_url : '',
            'product_price_id'  => isset($price->id) ? $price->id : '',
            'order_id'          => $purchase_data->initial_order->id,
            'subscription_id'   => isset($purchase_data->subscription->id) ? $purchase_data->subscription->id : '',
            'order_number'      => $purchase_data->initial_order->number,
            'order_date'        => date(get_option('date_format', 'F j, Y'), $purchase_data->initial_order->created_at),
            'order_status'      => $purchase_data->initial_order->status,
            'order_paid_amount' => self::format_amount($chekout->charge->amount),
            'order_subtotal'    => self::format_amount($chekout->subtotal_amount),
            'order_total'       => self::format_amount($chekout->total_amount),
            'payment_method'    => isset($chekout->payment_method->processor_type) ? $chekout->payment_method->processor_type : '',
            'metadata'          => empty($chekout->metadata) ? [] : (array) $chekout->metadata
        ];
    }

    public static function purchase_details($id)
    {
        return \SureCart\Models\Purchase::with(['initial_order', 'order.checkout', 'checkout.shipping_address', 'checkout.payment_method', 'checkout.discount', 'discount.coupon', 'checkout.charge', 'product', 'product.downloads', 'download.media', 'license.activations', 'line_items', 'line_item.price', 'subscription'])->find($id);
    }

    public static function get_price($purchase_data)
    {
        if (empty($purchase_data->line_items->data[0])) {
            return;
        }

        $line_item = $purchase_data->line_items->data[0];

        return $line_item->price;
    }

    public static function format_amount($amount)
    {
        return $amount / 100;
    }

    private static function getCheckoutCustomFields()
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT ID, post_content FROM {$wpdb->prefix}posts WHERE post_type = 'sc_form' LIMIT 1"),
            ARRAY_A
        );

        if (empty($result['ID'] || empty($result['post_content']))) {
            return [];
        }

        $blocks = parse_blocks($result['post_content']);
        error_log(print_r(['bkocks' => $blocks], true));

        return static::extractMetaDataFromBlocks($blocks);
    }

    private static function extractMetaDataFromBlocks($blocks, $metaData = [])
    {
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'surecart/input') {
                $attrs = $block['attrs'] ?? [];
                $innerHTML = $block['innerHTML'] ?? '';
                $label = $attrs['label'] ?? null;
                $name = $attrs['name'] ?? null;

                if (!$label && preg_match('/label="([^"]*)"/', $innerHTML, $m)) {
                    $label = $m[1];
                }

                if (!$name && preg_match('/name="([^"]*)"/', $innerHTML, $m)) {
                    $name = $m[1];
                }

                if ($label && $name) {
                    $metaData[$label] = (object) [
                        'fieldKey'  => $name,
                        'fieldName' => $label,
                    ];
                }
            }

            if (!empty($block['innerBlocks']) && \is_array($block['innerBlocks'])) {
                $metaData = self::extractMetaDataFromBlocks($block['innerBlocks'], $metaData);
            }
        }

        return $metaData;
    }
}
