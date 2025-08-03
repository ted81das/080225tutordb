<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedCoupons;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AdvancedCouponsController
{
    public static function info()
    {
        return [
            'name'              => 'Advanced coupons for WooCommerce',
            'title'             => __('Advanced coupons for WooCommerce', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AdvancedCouponsHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/advanced-coupons-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'advanced_coupons/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'advanced_coupons/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'advanced_coupons/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AdvancedCouponsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Advanced coupons for WooCommerce'));
        }

        wp_send_json_success([
            ['form_name' => __('Create or Update Coupon', 'bit-integrations-pro'), 'triggered_entity_id' => 'acfw_after_save_coupon', 'skipPrimaryKey' => true],
            ['form_name' => __('Store Credit Exceeds Limit', 'bit-integrations-pro'), 'triggered_entity_id' => 'acfw_store_credit_exceeds_specific_amount', 'skipPrimaryKey' => true],
            ['form_name' => __('Lifetime Credit Exceeds Limit', 'bit-integrations-pro'), 'triggered_entity_id' => 'acfw_lifetime_credit_exceeds_specific_amount', 'skipPrimaryKey' => true],
            ['form_name' => __('Receive Store Credit', 'bit-integrations-pro'), 'triggered_entity_id' => 'acfw_user_receives_store_credit', 'skipPrimaryKey' => true],
            ['form_name' => __('Adjust Store Credit', 'bit-integrations-pro'), 'triggered_entity_id' => 'acfw_user_adjust_store_credit', 'skipPrimaryKey' => true],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleUserSaveCoupon($coupon_id, $coupon)
    {
        if (empty($coupon_id) || empty($coupon)) {
            return;
        }

        $formData = AdvancedCouponsHelper::FormatCouponData($coupon);

        return static::flowExecute('acfw_after_save_coupon', $formData);
    }

    public static function handleUserAdjustStoreCredit($data)
    {
        if (empty($data['type'])) {
            return;
        }

        $formData = AdvancedCouponsHelper::FormatStoreCreditData($data, 'adjust_store_credit');

        return static::flowExecute('acfw_user_adjust_store_credit', $formData);
    }

    public static function handleUserStoreCreditExceeds($data)
    {
        if (isset($data['type']) && 'decrease' === $data['type']) {
            return;
        }

        $formData = AdvancedCouponsHelper::FormatStoreCreditData($data, 'store_credit');

        return static::flowExecute('acfw_store_credit_exceeds_specific_amount', $formData);
    }

    public static function handleUserLifetimeCreditExceeds($data)
    {
        if (isset($data['type']) && 'increase' !== $data['type']) {
            return;
        }

        $formData = AdvancedCouponsHelper::FormatStoreCreditData($data, 'lifetime_credit');

        return static::flowExecute('acfw_lifetime_credit_exceeds_specific_amount', $formData);
    }

    public static function handleUserReceivesStoreCredit($data)
    {
        if (!isset($data['type']) || 'decrease' === $data['type']) {
            return;
        }

        $formData = AdvancedCouponsHelper::FormatStoreCreditData($data, 'receives_credit');

        return static::flowExecute('acfw_user_receives_store_credit', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('AdvancedCoupons', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('AdvancedCoupons', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
