<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedCoupons;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class AdvancedCouponsHelper
{
    public static function FormatCouponData($coupon)
    {
        $data = $coupon->get_data();

        unset(
            $data['excluded_product_ids'],
            $data['limit_usage_to_x_items'],
            $data['product_categories'],
            $data['excluded_product_categories'],
            $data['exclude_sale_items'],
            $data['email_restrictions'],
            $data['used_by'],
            $data['meta_data'],
        );

        return Helper::prepareFetchFormatFields($data);
    }

    public static function FormatStoreCreditData($data, $type)
    {
        if (empty($data['user_id'])) {
            return;
        }

        $userId = \intval($data['user_id']);
        $creditData = User::get($userId);

        $creditData['amount'] = \floatval($data['amount']);
        $creditData['type'] = $data['type'];
        $creditData['note'] = $data['note'] ?? '';

        $creditData = static::getCreditAmount($creditData, $userId, $type);

        return Helper::prepareFetchFormatFields($creditData);
    }

    public static function isPluginInstalled()
    {
        return class_exists('ACFWF');
    }

    private static function getCreditAmount($creditData, $userId, $type)
    {
        if (!\function_exists('ACFWF')) {
            return 0;
        }

        $storeCredits = ACFWF()->Store_Credits_Calculate;

        switch ($type) {
            case 'store_credit':
                $creditData['credit_amount'] = apply_filters('acfw_filter_amount', $storeCredits->get_customer_balance($userId));

                break;

            case 'lifetime_credit':
                global $wpdb;

                $couponData = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT entry_type,entry_action,CONVERT(entry_amount, DECIMAL(%d,%d)) AS amount FROM {$wpdb->prefix}acfw_store_credits WHERE user_id = %d",
                        $storeCredits->get_decimal_precision(),
                        wc_get_price_decimals(),
                        $userId
                    ),
                    ARRAY_A
                );

                $totalAmount = array_reduce($couponData, function ($sum, $entry) {
                    if ((!empty($entry['entry_type']) && $entry['entry_type'] === 'increase')) {
                        return $sum + \floatval($entry['amount']);
                    }

                    return $sum;
                }, 0);

                $creditData['credit_amount'] = apply_filters('acfw_filter_amount', $totalAmount);

                break;

            case 'receives_credit':
                $creditData['credit_amount'] = \floatval($creditData['amount']);
                unset($creditData['amount']);

                break;

            case 'adjust_store_credit':
                $creditData['new_balance'] = apply_filters('acfw_filter_amount', $storeCredits->get_customer_balance($userId));
                $creditData['previous_balance'] = $creditData['new_balance'] - $creditData['amount'];

                break;
            default:
                $creditData['credit_amount'] = 0;

                break;
        }

        return $creditData;
    }
}
