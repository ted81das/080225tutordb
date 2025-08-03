<?php

namespace BitApps\BTCBI_PRO\Triggers\Memberpress;

final class MemberpressHelper
{
    public static function getOneTimeField()
    {
        return [
            'ID' => (object) [
                'fieldKey'  => 'ID',
                'fieldName' => __('Membership ID', 'bit-integrations-pro')
            ],
            'post_title' => (object) [
                'fieldKey'  => 'post_title',
                'fieldName' => __('Membership Name', 'bit-integrations-pro')
            ],
            'post_content' => (object) [
                'fieldKey'  => 'post_content',
                'fieldName' => __('Membership Description', 'bit-integrations-pro')
            ],
        ];
    }

    public static function getMembershipCancelField()
    {
        return [
            'id' => (object) [
                'fieldKey'  => 'id',
                'fieldName' => __('Subscription ID', 'bit-integrations-pro')
            ],
            'subscr_id' => (object) [
                'fieldKey'  => 'subscr_id',
                'fieldName' => __('Subscription ID', 'bit-integrations-pro')
            ],
            'gateway' => (object) [
                'fieldKey'  => 'gateway',
                'fieldName' => __('Subscription Gateway', 'bit-integrations-pro')
            ],
            'user_id' => (object) [
                'fieldKey'  => 'user_id',
                'fieldName' => __('User Id', 'bit-integrations-pro')
            ],
            'product_id' => (object) [
                'fieldKey'  => 'product_id',
                'fieldName' => __('Product Id', 'bit-integrations-pro')
            ],
            'price' => (object) [
                'fieldKey'  => 'price',
                'fieldName' => __('Price', 'bit-integrations-pro')
            ],
            'period_type' => (object) [
                'fieldKey'  => 'period_type',
                'fieldName' => __('Period Type', 'bit-integrations-pro')
            ],
            'trial_amount' => (object) [
                'fieldKey'  => 'trial_amount',
                'fieldName' => __('Trial Amount', 'bit-integrations-pro')
            ],
        ];
    }

    public static function getRecurringField()
    {
        return [
            'affiliate_id' => (object) [
                'fieldKey'  => 'affiliate_id',
                'fieldName' => __('Affiliate ID', 'bit-integrations-pro')
            ],
            'order_amount' => (object) [
                'fieldKey'  => 'order_amount',
                'fieldName' => __('Order Amount', 'bit-integrations-pro')
            ],
            'commission_amount' => (object) [
                'fieldKey'  => 'commission_amount',
                'fieldName' => __('Commission Amount', 'bit-integrations-pro')
            ],
            'referral_source' => (object) [
                'fieldKey'  => 'referral_source',
                'fieldName' => __('Referral Source', 'bit-integrations-pro')
            ],
            'visit_id' => (object) [
                'fieldKey'  => 'visit_id',
                'fieldName' => __('Visit ID', 'bit-integrations-pro')
            ],
            'coupon_id' => (object) [
                'fieldKey'  => 'coupon_id',
                'fieldName' => __('Coupon ID', 'bit-integrations-pro')
            ],
            'customer_id' => (object) [
                'fieldKey'  => 'customer_id',
                'fieldName' => __('Customer Id', 'bit-integrations-pro')
            ],
            'referral_type' => (object) [
                'fieldKey'  => 'referral_type',
                'fieldName' => __('Referral Type', 'bit-integrations-pro')
            ],
            'description' => (object) [
                'fieldKey'  => 'description',
                'fieldName' => __('Description', 'bit-integrations-pro')
            ],
            'order_source' => (object) [
                'fieldKey'  => 'order_source',
                'fieldName' => __('Order Source', 'bit-integrations-pro')
            ],
            'order_id' => (object) [
                'fieldKey'  => 'order_id',
                'fieldName' => __('Order ID', 'bit-integrations-pro')
            ],
            'payout_id' => (object) [
                'fieldKey'  => 'payout_id',
                'fieldName' => __('Payout ID', 'bit-integrations-pro')
            ],
            'status' => (object) [
                'fieldKey'  => 'status',
                'fieldName' => __('Status', 'bit-integrations-pro')
            ],
            'created_at' => (object) [
                'fieldKey'  => 'created_at',
                'fieldName' => __('Created At', 'bit-integrations-pro')
            ],
            'updated_at' => (object) [
                'fieldKey'  => 'updated_at',
                'fieldName' => __('Updated At', 'bit-integrations-pro')
            ]
        ];
    }

    public static function getUserField()
    {
        return [
            'User ID' => (object) [
                'fieldKey'  => 'user_id',
                'fieldName' => __('User Id', 'bit-integrations-pro')
            ],
            'First Name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro')
            ],
            'Last Name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro')
            ],
            'Nick Name' => (object) [
                'fieldKey'  => 'nickname',
                'fieldName' => __('Nick Name', 'bit-integrations-pro')
            ],
            'Avatar URL' => (object) [
                'fieldKey'  => 'avatar_url',
                'fieldName' => __('Avatar URL', 'bit-integrations-pro')
            ],
            'Email' => (object) [
                'fieldKey'  => 'user_email',
                'fieldName' => __('Email', 'bit-integrations-pro')
            ],
        ];
    }
}
