<?php

namespace BitApps\BTCBI_PRO\Triggers\PaidMembershipPro;

final class PaidMembershipProHelper
{
    public static function getPaidMembershipProField()
    {
        return [
            'id' => (object) [
                'fieldKey'  => 'id',
                'fieldName' => __('Membership ID', 'bit-integrations-pro')
            ],
            'name' => (object) [
                'fieldKey'  => 'name',
                'fieldName' => __('Name', 'bit-integrations-pro')
            ],
            'description' => (object) [
                'fieldKey'  => 'description',
                'fieldName' => __('Description', 'bit-integrations-pro')
            ],
            'confirmation' => (object) [
                'fieldKey'  => 'confirmation',
                'fieldName' => __('Confirmation', 'bit-integrations-pro')
            ],
            'initial_payment' => (object) [
                'fieldKey'  => 'initial_payment',
                'fieldName' => __('Initial Payment', 'bit-integrations-pro')
            ],
            'billing_amount' => (object) [
                'fieldKey'  => 'billing_amount',
                'fieldName' => __('Billing Amount', 'bit-integrations-pro')
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
