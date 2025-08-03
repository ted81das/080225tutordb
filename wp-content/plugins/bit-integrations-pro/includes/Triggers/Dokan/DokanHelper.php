<?php

namespace BitApps\BTCBI_PRO\Triggers\Dokan;

use WeDevs\DokanPro\Modules\Germanized\Helper;

class DokanHelper
{
    private static $vendorFields = [
        [
            'name'  => 'vendor_id',
            'type'  => 'text',
            'label' => 'Vendor ID',
        ],
        [
            'name'  => 'store_name',
            'type'  => 'text',
            'label' => 'Store Name',
        ],
        [
            'name'  => 'store_url',
            'type'  => 'text',
            'label' => 'Store URL',
        ],
        [
            'name'  => 'user_login',
            'type'  => 'text',
            'label' => 'User Login',
        ],
        [
            'name'  => 'user_nicename',
            'type'  => 'text',
            'label' => 'User Nicename',
        ],
        [
            'name'  => 'phone',
            'type'  => 'text',
            'label' => 'Phone',
        ],
        [
            'name'  => 'first_name',
            'type'  => 'text',
            'label' => 'First Name',
        ],
        [
            'name'  => 'last_name',
            'type'  => 'text',
            'label' => 'Last Name',
        ],
        [
            'name'  => 'user_email',
            'type'  => 'email',
            'label' => 'User Email',
        ],
        [
            'name'  => 'banner',
            'type'  => 'text',
            'label' => 'Banner',
        ],
        [
            'name'  => 'banner_id',
            'type'  => 'text',
            'label' => 'Banner ID',
        ],
        [
            'name'  => 'gravatar',
            'type'  => 'text',
            'label' => 'Gravatar',
        ],
        [
            'name'  => 'gravatar_id',
            'type'  => 'text',
            'label' => 'Gravatar ID',
        ],
        [
            'name'  => 'enabled',
            'type'  => 'text',
            'label' => 'Enabled',
        ],
        [
            'name'  => 'trusted',
            'type'  => 'text',
            'label' => 'Trusted',
        ],
        [
            'name'  => 'featured',
            'type'  => 'text',
            'label' => 'Featured',
        ],
        [
            'name'  => 'ac_name',
            'type'  => 'text',
            'label' => 'Account Name',
        ],
        [
            'name'  => 'ac_type',
            'type'  => 'text',
            'label' => 'Account Type',
        ],
        [
            'name'  => 'ac_number',
            'type'  => 'text',
            'label' => 'Account Number',
        ],
        [
            'name'  => 'bank_name',
            'type'  => 'text',
            'label' => 'Bank Name',
        ],
        [
            'name'  => 'bank_addr',
            'type'  => 'text',
            'label' => 'Bank Address',
        ],
        [
            'name'  => 'routing_number',
            'type'  => 'text',
            'label' => 'Routing Number',
        ],
        [
            'name'  => 'iban',
            'type'  => 'text',
            'label' => 'IBAN',
        ],
        [
            'name'  => 'swift',
            'type'  => 'text',
            'label' => 'Swift',
        ],
        [
            'name'  => 'paypal_email',
            'type'  => 'text',
            'label' => 'PayPal Email',
        ],
        [
            'name'  => 'street_1',
            'type'  => 'text',
            'label' => 'Street 1',
        ],
        [
            'name'  => 'street_2',
            'type'  => 'text',
            'label' => 'Street 2',
        ],
        [
            'name'  => 'city',
            'type'  => 'text',
            'label' => 'City',
        ],
        [
            'name'  => 'zip',
            'type'  => 'text',
            'label' => 'Zip',
        ],
        [
            'name'  => 'state',
            'type'  => 'text',
            'label' => 'State',
        ],
        [
            'name'  => 'country',
            'type'  => 'text',
            'label' => 'Country',
        ],
    ];

    private static $vendorEUFields = [
        [
            'name'  => 'company_name',
            'type'  => 'text',
            'label' => 'Company Name',
        ],
        [
            'name'  => 'company_id_number',
            'type'  => 'text',
            'label' => 'Company ID/EUID Number',
        ],
        [
            'name'  => 'vat_number',
            'type'  => 'text',
            'label' => 'VAT/TAX Number',
        ],
        [
            'name'  => 'eu_bank_name',
            'type'  => 'text',
            'label' => 'Name of Bank',
        ],
        [
            'name'  => 'bank_iban',
            'type'  => 'text',
            'label' => 'Bank IBAN',
        ],
    ];

    private static $vendorUpdateFields = [
        [
            'name'  => 'email',
            'type'  => 'email',
            'label' => 'Email',
        ],
        [
            'name'  => 'shop_url',
            'type'  => 'text',
            'label' => 'Shop URL',
        ],
        [
            'name'  => 'registered',
            'type'  => 'text',
            'label' => 'Registered On',
        ],
    ];

    private static $refundFields = [
        [
            'name'  => 'refund_id',
            'type'  => 'text',
            'label' => 'Refund ID',
        ],
        [
            'name'  => 'refund_amount',
            'type'  => 'text',
            'label' => 'Refund Amount',
        ],
        [
            'name'  => 'refund_reason',
            'type'  => 'text',
            'label' => 'Refund Reason',
        ],
        [
            'name'  => 'refund_date',
            'type'  => 'text',
            'label' => 'Refund Date',
        ],
    ];

    private static $OrderFieldsForRefunds = [
        [
            'name'  => 'order_id',
            'type'  => 'text',
            'label' => 'Order ID',
        ],
        [
            'name'  => 'order_status',
            'type'  => 'text',
            'label' => 'Order Status',
        ],
        [
            'name'  => 'order_currency',
            'type'  => 'text',
            'label' => 'Currency',
        ],
        [
            'name'  => 'order_subtotal',
            'type'  => 'text',
            'label' => 'Order Subtotal',
        ],
        [
            'name'  => 'order_total',
            'type'  => 'text',
            'label' => 'Order Total',
        ],
        [
            'name'  => 'order_total_tax',
            'type'  => 'text',
            'label' => 'Order Total Tax',
        ],
        [
            'name'  => 'order_payment_method_title',
            'type'  => 'text',
            'label' => 'Order Payment Method Title',
        ],
        [
            'name'  => 'order_transaction_id',
            'type'  => 'text',
            'label' => 'Order transaction ID',
        ],
        [
            'name'  => 'order_total_refunded',
            'type'  => 'text',
            'label' => 'Amount Already Refunded',
        ],
    ];

    private static $vendorFieldsForRefund = [
        [
            'name'  => 'vendor_id',
            'type'  => 'text',
            'label' => 'Vendor ID',
        ],
        [
            'name'  => 'vendor_store_name',
            'type'  => 'text',
            'label' => 'Store Name',
        ],
        [
            'name'  => 'vendor_shop_url',
            'type'  => 'text',
            'label' => 'Shop URL',
        ],
        [
            'name'  => 'vendor_first_name',
            'type'  => 'text',
            'label' => 'Vendor First Name',
        ],
        [
            'name'  => 'vendor_last_name',
            'type'  => 'text',
            'label' => 'Vendor Last Name',
        ],
        [
            'name'  => 'vendor_email',
            'type'  => 'email',
            'label' => 'Vendor Email',
        ],
        [
            'name'  => 'vendor_phone',
            'type'  => 'text',
            'label' => 'Vendor Phone',
        ],
    ];

    private static $userToVendorFields = [
        [
            'name'  => 'vendor_id',
            'type'  => 'text',
            'label' => 'Vendor ID',
        ],
        [
            'name'  => 'vendor_first_name',
            'type'  => 'text',
            'label' => 'Vendor First Name',
        ],
        [
            'name'  => 'vendor_last_name',
            'type'  => 'text',
            'label' => 'Vendor Last Name',
        ],
        [
            'name'  => 'store_name',
            'type'  => 'text',
            'label' => 'Shop Name',
        ],
        [
            'name'  => 'shop_url',
            'type'  => 'text',
            'label' => 'Shop URL',
        ],
        [
            'name'  => 'vendor_phone',
            'type'  => 'text',
            'label' => 'Vendor Phone',
        ],
    ];

    private static $withdrawFields = [
        [
            'name'  => 'withdraw_amount',
            'type'  => 'text',
            'label' => 'Withdraw Amount',
        ],
        [
            'name'  => 'withdraw_method',
            'type'  => 'text',
            'label' => 'Withdraw Method',
        ],
    ];

    public static function getFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        if (empty($id)) {
            return;
        }

        $fields = [];

        if ($id === 'dokan-1') {
            $fields = array_merge(self::$vendorFields, self::getEnabledVendorEUFields());
        } elseif ($id === 'dokan-2') {
            unset(self::$vendorFields[3], self::$vendorFields[8]);

            $fields = array_merge(self::$vendorFields, self::$vendorUpdateFields, self::getEnabledVendorEUFields());
        } elseif ($id === 'dokan-3') {
            unset(self::$vendorFields[2], self::$vendorFields[3], self::$vendorFields[4], self::$vendorFields[8]);

            $fields = array_merge(self::$vendorFields, self::$vendorUpdateFields, self::getEnabledVendorEUFields());
        } elseif ($id === 'dokan-4' || $id === 'dokan-5' || $id === 'dokan-6') {
            $fields = array_merge(self::$refundFields, self::$OrderFieldsForRefunds, self::$vendorFieldsForRefund);
        } elseif ($id === 'dokan-7') {
            $fields = array_merge(self::$userToVendorFields, self::getEnabledVendorEUFields('user-to-vendor'));
        } elseif ($id === 'dokan-8') {
            $fields = array_merge(self::$withdrawFields, self::$vendorFieldsForRefund);
        }

        return $fields;
    }

    public static function formatVendorData($vendorId, $data)
    {
        if (empty($vendorId) || empty($data)) {
            return false;
        }

        foreach ($data as $key => $item) {
            if ($key === 'payment') {
                if (!empty($item['bank'])) {
                    foreach ($item['bank'] as $bankKey => $bankItem) {
                        $vendorData[$bankKey] = $bankItem;
                    }
                }

                if (!empty($item['paypal'])) {
                    foreach ($item['paypal'] as $paypalKey => $paypalItem) {
                        $vendorData['paypal_' . $paypalKey] = $paypalItem;
                    }
                }
            } elseif ($key === 'address') {
                foreach ($item as $addrKey => $addrItem) {
                    $vendorData[$addrKey] = $addrItem;
                }
            } elseif ($key === 'social' || $key === '_links' || $key === 'store_open_close') {
                continue;
            } else {
                $vendorData[$key] = \is_array($item) ? implode(',', $item) : $item;
            }
        }

        $enabledEUFields = self::getEnabledVendorEUFields();

        if (!empty($enabledEUFields)) {
            foreach ($enabledEUFields as $euFiled) {
                if ($euFiled['name'] === 'eu_bank_name') {
                    $vendorData[$euFiled['name']] = isset($data['bank_name']) ? $data['bank_name'] : '';
                } else {
                    $vendorData[$euFiled['name']] = isset($data[$euFiled['name']]) ? $data[$euFiled['name']] : '';
                }
            }
        }

        $vendorData['enabled'] = isset($data['enabled']) ? $data['enabled'] : false;
        $vendorData['trusted'] = isset($data['trusted']) ? $data['trusted'] : false;
        $vendorData['featured'] = isset($data['featured']) ? $data['featured'] : false;
        $vendorData['vendor_id'] = $vendorId;

        return $vendorData;
    }

    public static function formatRefundData($refund)
    {
        if (!$refund) {
            return false;
        }

        $orderId = $refund->get_order_id();
        $vendorId = $refund->get_seller_id();
        $order = dokan()->order->get($orderId);
        $vendor = dokan()->vendor->get($vendorId)->to_array();

        if (!$order || empty($vendor)) {
            return false;
        }

        $refundData = [];

        $refundData['refund_id'] = $refund->get_id();
        $refundData['refund_amount'] = $refund->get_refund_amount();
        $refundData['refund_reason'] = $refund->get_refund_reason();
        $refundData['refund_date'] = $refund->get_date();

        $refundData['order_id'] = $order->get_id();
        $refundData['order_status'] = $order->get_status();
        $refundData['order_currency'] = $order->get_currency();
        $refundData['order_subtotal'] = $order->get_subtotal();
        $refundData['order_total'] = $order->get_total();
        $refundData['order_total_tax'] = $order->get_total_tax();
        $refundData['order_payment_method_title'] = $order->get_payment_method_title();
        $refundData['order_transaction_id'] = $order->get_transaction_id();
        $refundData['order_total_refunded'] = $order->get_total_refunded();

        $refundData['vendor_id'] = $vendor['id'];
        $refundData['vendor_store_name'] = $vendor['store_name'];
        $refundData['vendor_shop_url'] = $vendor['shop_url'];
        $refundData['vendor_first_name'] = $vendor['first_name'];
        $refundData['vendor_last_name'] = $vendor['last_name'];
        $refundData['vendor_email'] = $vendor['email'];
        $refundData['vendor_phone'] = $vendor['phone'];

        return $refundData;
    }

    public static function formatUserToVendorData($userId)
    {
        if (empty($userId)) {
            return false;
        }

        $vendor = dokan()->vendor->get($userId)->to_array();

        if (empty($vendor)) {
            return false;
        }

        $userToVendorData['vendor_id'] = $vendor['id'];
        $userToVendorData['vendor_first_name'] = $vendor['first_name'];
        $userToVendorData['vendor_last_name'] = $vendor['last_name'];
        $userToVendorData['store_name'] = $vendor['store_name'];
        $userToVendorData['shop_url'] = $vendor['shop_url'];
        $userToVendorData['vendor_phone'] = $vendor['phone'];

        $enabledEUFields = self::getEnabledVendorEUFields('user-to-vendor');

        if (!empty($enabledEUFields)) {
            foreach ($enabledEUFields as $euFiled) {
                if ($euFiled['name'] === 'eu_bank_name') {
                    $userToVendorData[$euFiled['name']] = isset($vendor['bank_name']) ? $vendor['bank_name'] : '';
                } else {
                    $userToVendorData[$euFiled['name']] = isset($vendor[$euFiled['name']]) ? $vendor[$euFiled['name']] : '';
                }
            }
        }

        return $userToVendorData;
    }

    public static function formatWithdrawRequestData($userId, $amount, $method)
    {
        if (empty($userId) || empty($amount) || empty($method)) {
            return false;
        }

        $vendor = dokan()->vendor->get($userId)->to_array();

        if (empty($vendor)) {
            return false;
        }

        $withdrawRequestData = [];

        $withdrawRequestData['withdraw_amount'] = $amount;
        $withdrawRequestData['withdraw_method'] = $method;
        $withdrawRequestData['vendor_id'] = $vendor['id'];
        $withdrawRequestData['vendor_store_name'] = $vendor['store_name'];
        $withdrawRequestData['vendor_shop_url'] = $vendor['shop_url'];
        $withdrawRequestData['vendor_first_name'] = $vendor['first_name'];
        $withdrawRequestData['vendor_last_name'] = $vendor['last_name'];
        $withdrawRequestData['vendor_email'] = $vendor['email'];
        $withdrawRequestData['vendor_phone'] = $vendor['phone'];

        return $withdrawRequestData;
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    public static function createUserFieldsByTypes($type = null)
    {
        $userFields = [];

        foreach (self::$userFieldsKey as $item) {
            $userFields[] = [
                'name'  => (!empty($type) ? $type . '_' : '') . $item['field_key'],
                'type'  => 'text',
                'label' => (!empty($type) ? ucwords(str_replace('_', ' ', $type)) . ' ' : '') . $item['field_label'],
            ];
        }

        return $userFields;
    }

    public static function userDataByType($id, $type = null)
    {
        $userData = get_userdata(\intval($id));

        $formattedUserData = [];

        foreach (self::$userFieldsKey as $item) {
            $key = $item['wp_user_key'];

            if (\is_array($userData->{$key})) {
                $data = implode(',', $userData->{$key});
            } else {
                $data = $userData->{$key};
            }

            $formattedUserData[(!empty($type) ? $type . '_' : '') . $item['field_key']] = $data;
        }

        return $formattedUserData;
    }

    private static function getEnabledVendorEUFields($type = null)
    {
        $fields = [];

        if (is_plugin_active('dokan-pro/dokan-pro.php') && dokan_pro()->module->is_active('germanized')) {
            if ($type === 'user-to-vendor' && !Helper::is_enabled_on_registration_form()) {
                return $fields;
            }

            $enabledEUFields = Helper::is_fields_enabled_for_seller();

            foreach ($enabledEUFields as $key => $item) {
                if ($item) {
                    $formatKey = str_replace('dokan_', '', $key);

                    if ($formatKey === 'bank_name') {
                        $formatKey = 'eu_bank_name';
                    }

                    $vendorEnabledEUFieldsKey[] = $formatKey;
                }
            }

            if (!empty($vendorEnabledEUFieldsKey)) {
                foreach (self::$vendorEUFields as $vendorEUField) {
                    if (\in_array($vendorEUField['name'], $vendorEnabledEUFieldsKey)) {
                        $fields[] = $vendorEUField;
                    }
                }
            }
        }

        return $fields;
    }
}
