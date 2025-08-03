<?php

namespace BitApps\BTCBI_PRO\Triggers\SolidAffiliate;

final class SolidAffiliateHelper
{
    public static function getAffiliateField()
    {
        return [
            'user_id' => (object) [
                'fieldKey'  => 'user_id',
                'fieldName' => __('User Id', 'bit-integrations-pro'),
            ],
            'first_name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro'),
            ],

            'last_name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro'),
            ],
            'commission_type' => (object) [
                'fieldKey'  => 'commission_type',
                'fieldName' => __('Commission Type', 'bit-integrations-pro'),
            ],
            'commission_rate' => (object) [
                'fieldKey'  => 'commission_rate',
                'fieldName' => __('Commission Rate', 'bit-integrations-pro'),
            ],
            'payment_email' => (object) [
                'fieldKey'  => 'payment_email',
                'fieldName' => __('Payment Email', 'bit-integrations-pro'),
            ],
            'mailchimp_user_id' => (object) [
                'fieldKey'  => 'mailchimp_user_id',
                'fieldName' => __('Mailchimp User ID', 'bit-integrations-pro'),
            ],
            'affiliate_group_id' => (object) [
                'fieldKey'  => 'affiliate_group_id',
                'fieldName' => __('Affiliate Group ID', 'bit-integrations-pro'),
            ],
            'registration_notes' => (object) [
                'fieldKey'  => 'registration_notes',
                'fieldName' => __('Registration Notes', 'bit-integrations-pro'),
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
