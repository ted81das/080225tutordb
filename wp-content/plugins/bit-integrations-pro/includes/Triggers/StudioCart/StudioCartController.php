<?php

namespace BitApps\BTCBI_PRO\Triggers\StudioCart;

use BitCode\FI\Flow\Flow;

final class StudioCartController
{
    protected static $actions = [
        'newOrderCreated' => [
            'id'    => 2,
            'title' => 'New Order Created'
        ],
    ];

    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'StudioCart',
            'title'          => __('Build high-converting checkout pages and sales funnels on your own website. No coding required', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'studiocart-pro/studiocart.php',
            'type'           => 'form',
            'is_active'      => self::pluginActive(),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'studiocart/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'studiocart/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('studiocart-pro/studiocart.php')) {
            return $option === 'get_name' ? 'studiocart-pro/studiocart.php' : true;
        } elseif (is_plugin_active('studiocart/studiocart.php')) {
            return $option === 'get_name' ? 'studiocart/studiocart.php' : true;
        }

        return false;
    }

    public static function newOrderCreated($status, $order_data, $order_type = 'main')
    {
        $flows = Flow::exists('StudioCart', self::$actions['newOrderCreated']['id']);

        if (!$flows) {
            return;
        }

        $data = [];
        foreach ($order_data as $key => $field_value) {
            $data[$key] = $field_value;
        }

        Flow::execute('StudioCart', self::$actions['newOrderCreated']['id'], $data, $flows);
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Studiocart'));
        }

        $sc_actions = [];
        foreach (self::$actions as $action) {
            $sc_actions[] = (object) [
                'id'    => $action['id'],
                'title' => $action['title'],
            ];
        }
        wp_send_json_success($sc_actions);
    }

    public function get_a_form($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Studiocart'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);
        if (empty($fields)) {
            wp_send_json_error(__('Form doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fields($selectedAction)
    {
        $fieldDetails = [];
        if ($selectedAction == self::$actions['newOrderCreated']['id']) {
            $fieldDetails = self::getNewOrderFields();
        }

        $fields = [];

        foreach ($fieldDetails as $field) {
            $fields[] = [
                'name'  => $field['key'],
                'type'  => $field['type'],
                'label' => $field['label'],
            ];
        }

        return $fields;
    }

    protected static function getNewOrderFields()
    {
        return [
            [
                'key'   => 'id',
                'type'  => 'text',
                'label' => __('order_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'date',
                'type'  => 'text',
                'label' => __('date', 'bit-integrations-pro'),
            ], [
                'key'   => 'transaction_id',
                'type'  => 'text',
                'label' => __('transaction_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'status',
                'type'  => 'text',
                'label' => __('Status', 'bit-integrations-pro'),
            ], [
                'key'   => 'payment_status',
                'type'  => 'text',
                'label' => __('payment_status', 'bit-integrations-pro'),
            ], [
                'key'   => 'custom_fields_post_data',
                'type'  => 'text',
                'label' => __('custom_fields_post_data', 'bit-integrations-pro'),
            ], [
                'key'   => 'custom_fields',
                'type'  => 'text',
                'label' => __('custom_fields', 'bit-integrations-pro'),
            ], [
                'key'   => 'custom_prices',
                'type'  => 'text',
                'label' => __('custom_prices', 'bit-integrations-pro'),
            ], [
                'key'   => 'product_id',
                'type'  => 'text',
                'label' => __('product_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'product_name',
                'type'  => 'text',
                'label' => __('product_name', 'bit-integrations-pro'),
            ], [
                'key'   => 'page_id',
                'type'  => 'text',
                'label' => __('page_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'page_url',
                'type'  => 'text',
                'label' => __('page_url', 'bit-integrations-pro'),
            ], [
                'key'   => 'item_name',
                'type'  => 'text',
                'label' => __('item_name', 'bit-integrations-pro'),
            ], [
                'key'   => 'plan',
                'type'  => 'text',
                'label' => __('plan', 'bit-integrations-pro'),
            ], [
                'key'   => 'plan_id',
                'type'  => 'text',
                'label' => __('plan_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'option_id',
                'type'  => 'text',
                'label' => __('option_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'invoice_total',
                'type'  => 'text',
                'label' => __('invoice_total', 'bit-integrations-pro'),
            ], [
                'key'   => 'invoice_subtotal',
                'type'  => 'text',
                'label' => __('invoice_subtotal', 'bit-integrations-pro'),
            ], [
                'key'   => 'amount',
                'type'  => 'text',
                'label' => __('Amount', 'bit-integrations-pro'),
            ], [
                'key'   => 'main_offer_amt',
                'type'  => 'text',
                'label' => __('main_offer_amt', 'bit-integrations-pro'),
            ], [
                'key'   => 'pre_tax_amount',
                'type'  => 'text',
                'label' => __('pre_tax_amount', 'bit-integrations-pro'),
            ], [
                'key'   => 'tax_amount',
                'type'  => 'text',
                'label' => __('tax_amount', 'bit-integrations-pro'),
            ], [
                'key'   => 'auto_login',
                'type'  => 'text',
                'label' => __('auto_login', 'bit-integrations-pro'),
            ], [
                'key'   => 'coupon',
                'type'  => 'text',
                'label' => __('coupon', 'bit-integrations-pro'),
            ], [
                'key'   => 'coupon_id',
                'type'  => 'text',
                'label' => __('Coupon ID', 'bit-integrations-pro'),
            ], [
                'key'   => 'on_sale',
                'type'  => 'text',
                'label' => __('on_sale', 'bit-integrations-pro'),
            ], [
                'key'   => 'accept_terms',
                'type'  => 'text',
                'label' => __('accept_terms', 'bit-integrations-pro'),
            ], [
                'key'   => 'accept_privacy',
                'type'  => 'text',
                'label' => __('accept_privacy', 'bit-integrations-pro'),
            ], [
                'key'   => 'consent',
                'type'  => 'text',
                'label' => __('consent', 'bit-integrations-pro'),
            ], [
                'key'   => 'order_log',
                'type'  => 'text',
                'label' => __('order_log', 'bit-integrations-pro'),
            ], [
                'key'   => 'order_bumps',
                'type'  => 'text',
                'label' => __('order_bumps', 'bit-integrations-pro'),
            ], [
                'key'   => 'us_parent',
                'type'  => 'text',
                'label' => __('us_parent', 'bit-integrations-pro'),
            ], [
                'key'   => 'ds_parent',
                'type'  => 'text',
                'label' => __('ds_parent', 'bit-integrations-pro'),
            ], [
                'key'   => 'order_parent',
                'type'  => 'text',
                'label' => __('order_parent', 'bit-integrations-pro'),
            ], [
                'key'   => 'order_type',
                'type'  => 'text',
                'label' => __('order_type', 'bit-integrations-pro'),
            ], [
                'key'   => 'subscription_id',
                'type'  => 'text',
                'label' => __('subscription Id', 'bit-integrations-pro'),
            ], [
                'key'   => 'firstname',
                'type'  => 'text',
                'label' => __('firstname', 'bit-integrations-pro'),
            ], [
                'key'   => 'lastname',
                'type'  => 'text',
                'label' => __('lastname', 'bit-integrations-pro'),
            ], [
                'key'   => 'first_name',
                'type'  => 'text',
                'label' => __('first_name', 'bit-integrations-pro'),
            ], [
                'key'   => 'last_name',
                'type'  => 'text',
                'label' => __('last_name', 'bit-integrations-pro'),
            ], [
                'key'   => 'customer_name',
                'type'  => 'text',
                'label' => __('customer_name', 'bit-integrations-pro'),
            ], [
                'key'   => 'customer_id',
                'type'  => 'text',
                'label' => __('customer_id', 'bit-integrations-pro'),
            ], [
                'key'   => 'email',
                'type'  => 'text',
                'label' => __('Email', 'bit-integrations-pro'),
            ], [
                'key'   => 'phone',
                'type'  => 'text',
                'label' => __('Phone', 'bit-integrations-pro'),
            ], [
                'key'   => 'country',
                'type'  => 'text',
                'label' => __('country', 'bit-integrations-pro'),
            ], [
                'key'   => 'address1',
                'type'  => 'text',
                'label' => __('address1', 'bit-integrations-pro'),
            ], [
                'key'   => 'address2',
                'type'  => 'text',
                'label' => __('address2', 'bit-integrations-pro'),
            ], [
                'key'   => 'city',
                'type'  => 'text',
                'label' => __('city', 'bit-integrations-pro'),
            ], [
                'key'   => 'state',
                'type'  => 'text',
                'label' => __('state', 'bit-integrations-pro'),
            ], [
                'key'   => 'zip',
                'type'  => 'text',
                'label' => __('zip', 'bit-integrations-pro'),
            ], [
                'key'   => 'ip_address',
                'type'  => 'text',
                'label' => __('ip_address', 'bit-integrations-pro'),
            ], [
                'key'   => 'user_account',
                'type'  => 'text',
                'label' => __('user_account', 'bit-integrations-pro'),
            ], [
                'key'   => 'pay_method',
                'type'  => 'text',
                'label' => __('pay_method', 'bit-integrations-pro'),
            ], [
                'key'   => 'gateway_mode',
                'type'  => 'text',
                'label' => __('gateway_mode', 'bit-integrations-pro'),
            ], [
                'key'   => 'currency',
                'type'  => 'text',
                'label' => __('Currency', 'bit-integrations-pro'),
            ], [
                'key'   => 'tax_rate',
                'type'  => 'text',
                'label' => __('tax_rate', 'bit-integrations-pro'),
            ], [
                'key'   => 'tax_desc',
                'type'  => 'text',
                'label' => __('tax_desc', 'bit-integrations-pro'),
            ], [
                'key'   => 'tax_data',
                'type'  => 'text',
                'label' => __('tax_data', 'bit-integrations-pro'),
            ], [
                'key'   => 'tax_type',
                'type'  => 'text',
                'label' => __('tax_type', 'bit-integrations-pro'),
            ], [
                'key'   => 'vat_number',
                'type'  => 'text',
                'label' => __('vat_number', 'bit-integrations-pro'),
            ], [
                'key'   => 'stripe_tax_id',
                'type'  => 'text',
                'label' => __('stripe_tax_id', 'bit-integrations-pro'),
            ]
        ];
    }
}
