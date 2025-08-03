<?php

namespace BitApps\BTCBI_PRO\Triggers\Rafflepress;

final class RafflepressHelper
{
    public static function getRafflepressField()
    {
        return [
            'giveaway_id' => (object) [
                'fieldKey'  => 'giveaway_id',
                'fieldName' => __('Giveaway ID', 'bit-integrations-pro'),
            ],
            'giveaway_name' => (object) [
                'fieldKey'  => 'giveaway_name',
                'fieldName' => __('Giveaway Name', 'bit-integrations-pro'),
            ],

            'starts' => (object) [
                'fieldKey'  => 'starts',
                'fieldName' => __('Starts', 'bit-integrations-pro'),
            ],
            'ends' => (object) [
                'fieldKey'  => 'ends',
                'fieldName' => __('Ends', 'bit-integrations-pro'),
            ],
            'active' => (object) [
                'fieldKey'  => 'active',
                'fieldName' => __('Active', 'bit-integrations-pro'),
            ],
            'name' => (object) [
                'fieldKey'  => 'name',
                'fieldName' => __('Name', 'bit-integrations-pro'),
            ],
            'first_name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro'),
            ],
            'last_name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro'),
            ],
            'email' => (object) [
                'fieldKey'  => 'email',
                'fieldName' => __('Email', 'bit-integrations-pro'),
            ],
            'prize_name' => (object) [
                'fieldKey'  => 'prize_name',
                'fieldName' => __('Prize Name', 'bit-integrations-pro'),
            ],
            'prize_description' => (object) [
                'fieldKey'  => 'prize_description',
                'fieldName' => __('Prize Description', 'bit-integrations-pro'),
            ],
            'prize_image' => (object) [
                'fieldKey'  => 'prize_image',
                'fieldName' => __('Prize Image', 'bit-integrations-pro'),
            ],

        ];
    }

    public static function getReferralAffiliateField()
    {
        return [
            'affiliate_id' => (object) [
                'fieldKey'  => 'affiliate_id',
                'fieldName' => __('Affiliate ID', 'bit-integrations-pro'),
            ],
            'order_amount' => (object) [
                'fieldKey'  => 'order_amount',
                'fieldName' => __('Order Amount', 'bit-integrations-pro'),
            ],
            'commission_amount' => (object) [
                'fieldKey'  => 'commission_amount',
                'fieldName' => __('Commission Amount', 'bit-integrations-pro'),
            ],
            'referral_source' => (object) [
                'fieldKey'  => 'referral_source',
                'fieldName' => __('Referral Source', 'bit-integrations-pro'),
            ],
            'visit_id' => (object) [
                'fieldKey'  => 'visit_id',
                'fieldName' => __('Visit ID', 'bit-integrations-pro'),
            ],
            'coupon_id' => (object) [
                'fieldKey'  => 'coupon_id',
                'fieldName' => __('Coupon ID', 'bit-integrations-pro'),
            ],
            'customer_id' => (object) [
                'fieldKey'  => 'customer_id',
                'fieldName' => __('Customer Id', 'bit-integrations-pro'),
            ],
            'referral_type' => (object) [
                'fieldKey'  => 'referral_type',
                'fieldName' => __('Referral Type', 'bit-integrations-pro'),
            ],
            'description' => (object) [
                'fieldKey'  => 'description',
                'fieldName' => __('Description', 'bit-integrations-pro'),
            ],
            'order_source' => (object) [
                'fieldKey'  => 'order_source',
                'fieldName' => __('Order Source', 'bit-integrations-pro'),
            ],
            'order_id' => (object) [
                'fieldKey'  => 'order_id',
                'fieldName' => __('Order ID', 'bit-integrations-pro'),
            ],
            'payout_id' => (object) [
                'fieldKey'  => 'payout_id',
                'fieldName' => __('Payout ID', 'bit-integrations-pro'),
            ],
            'status' => (object) [
                'fieldKey'  => 'status',
                'fieldName' => __('Status', 'bit-integrations-pro'),
            ],
            'created_at' => (object) [
                'fieldKey'  => 'created_at',
                'fieldName' => __('Created At', 'bit-integrations-pro'),
            ],
            'updated_at' => (object) [
                'fieldKey'  => 'updated_at',
                'fieldName' => __('Updated At', 'bit-integrations-pro'),
            ]
        ];
    }
}
