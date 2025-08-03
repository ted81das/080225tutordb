<?php

namespace BitApps\BTCBI_PRO\Triggers\WPLoyalty;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Triggers\WC\WCController;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WPLoyaltyController
{
    public static function info()
    {
        return [
            'name'              => 'WPLoyalty',
            'title'             => __('Points and Rewards for WooCommerce', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wployalty/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wployalty/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wployalty/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WPLoyalty'));
        }

        wp_send_json_success([['form_name' => __('Points Awarded Customer', 'bit-integrations-pro'), 'triggered_entity_id' => 'wlr_after_add_earn_point', 'skipPrimaryKey' => true]]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handlePointsAwardedCustomer($user_email, $point, $action_type, $action_data)
    {
        if (empty($user_email) || empty($point) || !static::isPluginInstalled()) {
            return;
        }

        $pointData = array_merge(
            [
                'points_earned'     => $point,
                'action_type'       => $action_type,
                'point_expiry_date' => static::getPointExpiryDate($user_email)
            ],
            static::getUserData($user_email)
        );

        if (!empty($action_data['order'])) {
            $pointData = array_merge($pointData, static::getOrderData($action_data['order']));
        }

        $formattedData = Helper::prepareFetchFormatFields($pointData);
        if (empty($formattedData) || !\is_array($formattedData)) {
            return;
        }

        Helper::setTestData('btcbi_wlr_after_add_earn_point_test', array_values($formattedData));

        $flows = Flow::exists('WPLoyalty', 'wlr_after_add_earn_point');
        if (!$flows) {
            return;
        }

        Flow::execute('WPLoyalty', 'wlr_after_add_earn_point', array_column($formattedData, 'value', 'name'), $flows);

        return ['status' => 'success'];
    }

    private static function getUserData($user_email)
    {
        if (!class_exists('Wlr\App\Helpers\Base')) {
            return [];
        }

        $baseHelper = new \Wlr\App\Helpers\Base();
        $user = (array) $baseHelper->getPointUserByEmail($user_email);

        foreach ($user as $key => $value) {
            if (substr($key, 0, 4) != 'user') {
                $user['user_' . $key] = $value;
                unset($user[$key]);
            }
        }

        return $user;
    }

    private static function getPointExpiryDate($user_email)
    {
        global $wpdb;
        $expiryResults = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wlr_expire_points 
				WHERE user_email = %s ORDER BY id DESC LIMIT 1',
                $user_email
            ),
            ARRAY_A
        );

        if (empty($expiryResults)) {
            return;
        }

        $expiryDate = $expiryResults[0]['expire_date'];
        $timestamp = is_numeric($expiryDate) ? (int) $expiryDate : null;
        $date_format = get_option('date_format');

        return \is_string($date_format) ? wp_date($date_format, $timestamp) : '';
    }

    private static function getOrderData($order)
    {
        if (empty($order) || empty($order->get_data())) {
            return [];
        }

        $orderData = $order->get_data();
        $orderId = $orderData['id'] ?? '';

        if (empty($orderId)) {
            return [];
        }

        $orderInstance = wc_get_order($orderId);
        if (empty($orderInstance)) {
            return [];
        }

        $orderDetails = WCController::accessOrderData($orderInstance);
        $orderDetails['order_id'] = $orderDetails['id'];
        $orderDetails['line_items'] = wp_json_encode($orderDetails['line_items']);
        unset($orderDetails['id']);

        return $orderDetails;
    }

    private static function isPluginInstalled()
    {
        return class_exists('WooCommerce') && class_exists('\Wlr\App\Router');
    }
}
