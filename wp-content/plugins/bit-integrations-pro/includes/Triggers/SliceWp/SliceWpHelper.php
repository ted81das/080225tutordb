<?php

namespace BitApps\BTCBI_PRO\Triggers\SliceWp;

final class SliceWpHelper
{
    public static function getSliceWpNewAffiliateField()
    {
        return [
            'affiliate_id' => (object) [
                'fieldKey'  => 'affiliate_id',
                'fieldName' => __('Affiliate ID', 'bit-integrations-pro')
            ],
            'user_id' => (object) [
                'fieldKey'  => 'user_id',
                'fieldName' => __('User Id', 'bit-integrations-pro')
            ],
            'payment_email' => (object) [
                'fieldKey'  => 'payment_email',
                'fieldName' => __('Payment Email', 'bit-integrations-pro')
            ],
            'website' => (object) [
                'fieldKey'  => 'website',
                'fieldName' => __('Website URL', 'bit-integrations-pro')
            ],
            'date_created' => (object) [
                'fieldKey'  => 'date_created',
                'fieldName' => __('Date Created', 'bit-integrations-pro')
            ],
            'status' => (object) [
                'fieldKey'  => 'status',
                'fieldName' => __('Status', 'bit-integrations-pro')
            ],
        ];
    }

    public static function getCommissionField()
    {
        return [
            'commission_id' => (object) [
                'fieldKey'  => 'commission_id',
                'fieldName' => __('Commission ID', 'bit-integrations-pro')
            ],
            'affiliate_id' => (object) [
                'fieldKey'  => 'affiliate_id',
                'fieldName' => __('Affiliate ID', 'bit-integrations-pro')
            ],
            'date_created' => (object) [
                'fieldKey'  => 'date_created',
                'fieldName' => __('Date Created', 'bit-integrations-pro')
            ],
            'amount' => (object) [
                'fieldKey'  => 'amount',
                'fieldName' => __('Amount', 'bit-integrations-pro')
            ],
            'reference' => (object) [
                'fieldKey'  => 'reference',
                'fieldName' => __('Reference', 'bit-integrations-pro')
            ],
            'origin' => (object) [
                'fieldKey'  => 'origin',
                'fieldName' => __('Origin', 'bit-integrations-pro')
            ],
            'type' => (object) [
                'fieldKey'  => 'type',
                'fieldName' => __('Type', 'bit-integrations-pro')
            ],
            'status' => (object) [
                'fieldKey'  => 'status',
                'fieldName' => __('Status', 'bit-integrations-pro')
            ],
            'currency' => (object) [
                'fieldKey'  => 'currency',
                'fieldName' => __('Currency', 'bit-integrations-pro')
            ],
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
