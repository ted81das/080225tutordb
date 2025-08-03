<?php

namespace BitApps\BTCBI_PRO\Triggers\WCSubscriptions;

use WC_Order;
use WC_Subscription;
use BitCode\FI\Flow\Flow;

final class WCSubscriptionsController
{
    public static function info()
    {
        $plugin_path = 'woocommerce-subscriptions/woocommerce-subscriptions.php';

        return [
            'name'           => 'WooCommerce Subscriptions',
            'title'          => \sprintf(__('%s - Sell products and services with recurring payments in your WooCommerce Store.', 'bit-integrations-pro'), 'WooCommerce Subscriptions'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => static::isActivate(),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'wcsubscriptions/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wcsubscriptions/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        static::isPluginActivated();

        $all_forms = [
            ['id' => 'user_subscribes_to_product', 'title' => __('User Subscribes to Product', 'bit-integrations-pro')],
            ['id' => 'user_purchases_variable_subscription', 'title' => __('User Purchases Variable Subscription', 'bit-integrations-pro')],
            ['id' => 'user_renews_subscription', 'title' => __('User Renews Subscription', 'bit-integrations-pro')],
            ['id' => 'user_cancels_subscription', 'title' => __('User Cancels Subscription', 'bit-integrations-pro')],
            ['id' => 'user_subscription_status_updated', 'title' => __('User Subscription Status Updated', 'bit-integrations-pro')],
            ['id' => 'user_subscription_expires', 'title' => __('User Subscription Expires', 'bit-integrations-pro')],
            ['id' => 'user_subscription_trial_end', 'title' => __('User Subscription Trial End', 'bit-integrations-pro')],
            ['id' => 'user_renewal_subscription_payment_failed', 'title' => __('User Renewal Subscription Payment Failed', 'bit-integrations-pro')],
        ];

        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        static::isPluginActivated();

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id === 'user_cancels_subscription'
        || $data->id === 'user_subscription_trial_end'
        || $data->id === 'user_subscription_expires'
        ) {
            $responseData['fields'] = WCSubscriptionsHelper::getSubscriptionFields();
            $responseData['allSubscriptions'] = WCSubscriptionsHelper::getAllSubscriptions();
        } elseif ($data->id === 'user_subscribes_to_product' || $data->id === 'user_purchases_variable_subscription') {
            $responseData['fields'] = WCSubscriptionsHelper::getSubscriptionFields();
        } elseif ($data->id === 'user_subscription_status_updated') {
            $responseData['allSubscriptions'] = WCSubscriptionsHelper::getAllSubscriptions();
            $responseData['fields'] = array_merge(WCSubscriptionsHelper::getSubscriptionFields(), [
                [
                    'name'  => 'new_status',
                    'type'  => 'text',
                    'label' => __('New Status', 'bit-integrations-pro')
                ],
                [
                    'name'  => 'old_status',
                    'type'  => 'text',
                    'label' => __('Old Status', 'bit-integrations-pro')
                ]
            ]);
        } elseif ($data->id === 'user_renews_subscription' || $data->id === 'user_renewal_subscription_payment_failed') {
            $responseData['allSubscriptions'] = WCSubscriptionsHelper::getAllSubscriptions();
            $responseData['fields'] = array_merge(WCSubscriptionsHelper::getSubscriptionFields(), [
                [
                    'name'  => 'last_order_id',
                    'type'  => 'number',
                    'label' => __('Last Order Id', 'bit-integrations-pro')
                ]
            ]);
        }

        $responseData['allSubscriptionProducts'] = WCSubscriptionsHelper::getAllSubscriptionsProducts($data->id);

        if (empty($responseData['fields'])) {
            wp_send_json_error(__('Task doesn\'t exists any field', 'bit-integrations-pro'));
        }

        wp_send_json_success($responseData);
    }

    public function getAllSubscriptions()
    {
        static::isPluginActivated();

        return WCSubscriptionsHelper::getAllSubscriptions();
    }

    public function getAllSubscriptionsProducts($data)
    {
        static::isPluginActivated();

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        return WCSubscriptionsHelper::getAllSubscriptionsProducts($data->id);
    }

    public static function handleSubscriptionCancelled($subscription)
    {
        if (!static::isActivate() || empty($flows = Flow::exists('WCSubscriptions', 'user_cancels_subscription'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_cancels_subscription', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = WCSubscriptionsHelper::mapSubscriptionData($subscription);

        Flow::execute('WCSubscriptions', 'user_cancels_subscription', $data, $flows);
    }

    public static function handleVariableSubscriptionPurchases($subscription)
    {
        if (!static::isActivate()) {
            return;
        }

        $last_order_id = $subscription->get_last_order();

        if (! empty($last_order_id) && $last_order_id !== $subscription->get_parent_id()) {
            return;
        }
        if (empty($flows = Flow::exists('WCSubscriptions', 'user_purchases_variable_subscription'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_purchases_variable_subscription', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = WCSubscriptionsHelper::mapSubscriptionData($subscription);

        Flow::execute('WCSubscriptions', 'user_purchases_variable_subscription', $data, $flows);
    }

    public static function handleSubscriptionRenewalPaymentFailed($subscription, $last_order)
    {
        if (!static::isActivate() || !class_exists('\WC_Order')) {
            return;
        }
        if (! $subscription instanceof WC_Subscription || ! $last_order instanceof WC_Order) {
            return;
        }
        if (empty($flows = Flow::exists('WCSubscriptions', 'user_renewal_subscription_payment_failed'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_renewal_subscription_payment_failed', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = array_merge(WCSubscriptionsHelper::mapSubscriptionData($subscription), ['last_order_id' => $last_order->get_id()]);

        Flow::execute('WCSubscriptions', 'user_renewal_subscription_payment_failed', $data, $flows);
    }

    public static function handleSubscriptionRenews($subscription, $last_order)
    {
        if (!static::isActivate() || !class_exists('\WC_Order')) {
            return;
        }
        if (! $subscription instanceof WC_Subscription || ! $last_order instanceof WC_Order) {
            return;
        }
        if (empty($flows = Flow::exists('WCSubscriptions', 'user_renews_subscription'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_renews_subscription', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = array_merge(WCSubscriptionsHelper::mapSubscriptionData($subscription), ['last_order_id' => $last_order->get_id()]);

        Flow::execute('WCSubscriptions', 'user_renews_subscription', $data, $flows);
    }

    public static function handleSubscribeToProduct($subscription)
    {
        if (!static::isActivate()) {
            return;
        }

        $last_order_id = $subscription->get_last_order();

        if (! empty($last_order_id) && $last_order_id !== $subscription->get_parent_id()) {
            return;
        }
        if (empty($flows = Flow::exists('WCSubscriptions', 'user_subscribes_to_product'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_subscribes_to_product', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = WCSubscriptionsHelper::mapSubscriptionData($subscription);

        Flow::execute('WCSubscriptions', 'user_subscribes_to_product', $data, $flows);
    }

    public static function handleSubscriptionExpired($subscription)
    {
        if (!static::isActivate() || empty($flows = Flow::exists('WCSubscriptions', 'user_subscription_expires'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_subscription_expires', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = WCSubscriptionsHelper::mapSubscriptionData($subscription);

        Flow::execute('WCSubscriptions', 'user_subscription_expires', $data, $flows);
    }

    public static function handleSubscriptionTrialEnd($subscription_id)
    {
        if (!static::isActivate() || empty($flows = Flow::exists('WCSubscriptions', 'user_subscription_trial_end'))) {
            return;
        }

        $subscription = wcs_get_subscription($subscription_id);
        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_subscription_trial_end', $subscription);

        if (empty($flows)) {
            return;
        }

        $data = WCSubscriptionsHelper::mapSubscriptionData($subscription);

        Flow::execute('WCSubscriptions', 'user_subscription_trial_end', $data, $flows);
    }

    public static function handleSubscriptionStatusUpdated($subscription, $new_status, $old_status)
    {
        if (!static::isActivate() || empty($flows = Flow::exists('WCSubscriptions', 'user_subscription_status_updated'))) {
            return;
        }

        $flows = WCSubscriptionsHelper::getFilteredFlows($flows, 'user_subscription_status_updated', $subscription, $new_status);

        if (empty($flows)) {
            return;
        }

        $data = array_merge(WCSubscriptionsHelper::mapSubscriptionData($subscription), ['new_status' => $new_status, 'old_status' => $old_status]);

        Flow::execute('WCSubscriptions', 'user_subscription_status_updated', $data, $flows);
    }

    private static function isPluginActivated()
    {
        if (!static::isActivate()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WooCommerce Subscriptions'));
        }
    }

    private static function isActivate()
    {
        return class_exists('\WC_Subscription');
    }
}
