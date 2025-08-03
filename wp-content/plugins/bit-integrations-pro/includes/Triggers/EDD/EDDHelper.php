<?php

namespace BitApps\BTCBI_PRO\Triggers\EDD;

class EDDHelper
{
    public static function fields($id)
    {
        if (empty($id)) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'bit-integrations-pro'
                ),
                400
            );
        }

        $userFields = [
            'First Name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro'),
            ],
            'Last Name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro'),
            ],
            'Nick Name' => (object) [
                'fieldKey'  => 'nickname',
                'fieldName' => __('Nick Name', 'bit-integrations-pro'),
            ],
            'Avatar URL' => (object) [
                'fieldKey'  => 'avatar_url',
                'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
            ],
            'Email' => (object) [
                'fieldKey'  => 'user_email',
                'fieldName' => __('Email', 'bit-integrations-pro'),
            ],
        ];

        if ($id == 1 || $id == 2) {
            $fields = [
                'User Id' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro'),
                ],
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
                ],
                'Last Name' => (object) [
                    'fieldKey'  => 'last_name',
                    'fieldName' => __('Last Name', 'bit-integrations-pro'),
                ],
                'Email' => (object) [
                    'fieldKey'  => 'user_email',
                    'fieldName' => __('Email', 'bit-integrations-pro'),
                ],
                'Product Id' => (object) [
                    'fieldKey'  => 'product_id',
                    'fieldName' => __('Product Id', 'bit-integrations-pro'),
                ],
                'Product Name' => (object) [
                    'fieldKey'  => 'product_name',
                    'fieldName' => __('Product Name', 'bit-integrations-pro'),
                ],
                'Order Item Id' => (object) [
                    'fieldKey'  => 'order_item_id',
                    'fieldName' => __('Order Item Id', 'bit-integrations-pro'),
                ],
                'discount_codes' => (object) [
                    'fieldKey'  => 'discount_codes',
                    'fieldName' => __('Discount Codes', 'bit-integrations-pro'),
                ],
                'order_discounts' => (object) [
                    'fieldKey'  => 'order_discounts',
                    'fieldName' => __('Order Discounts', 'bit-integrations-pro'),
                ],
                'order_subtotal' => (object) [
                    'fieldKey'  => 'order_subtotal',
                    'fieldName' => __('Order Subtotal', 'bit-integrations-pro'),
                ],
                'order_total' => (object) [
                    'fieldKey'  => 'order_total',
                    'fieldName' => __('Order Total', 'bit-integrations-pro'),
                ],
                'order_tax' => (object) [
                    'fieldKey'  => 'order_tax',
                    'fieldName' => __('Order Tax', 'bit-integrations-pro'),
                ],
                'payment_method' => (object) [
                    'fieldKey'  => 'payment_method',
                    'fieldName' => __('Payment Method', 'bit-integrations-pro'),
                ],
                'Status' => (object) [
                    'fieldKey'  => 'status',
                    'fieldName' => __('Status', 'bit-integrations-pro'),
                ],
            ];
        } elseif ($id == 3) {
            $refundField = [
                'Refund Id' => (object) [
                    'fieldKey'  => 'refund_id',
                    'fieldName' => __('Refund Id', 'bit-integrations-pro'),
                ],
                'Discount Codes' => (object) [
                    'fieldKey'  => 'discount_codes',
                    'fieldName' => __('Discount Codes', 'bit-integrations-pro'),
                ],
                'Order Discounts' => (object) [
                    'fieldKey'  => 'order_discounts',
                    'fieldName' => __('Order Discounts', 'bit-integrations-pro'),
                ],
                'Order Subtotal' => (object) [
                    'fieldKey'  => 'order_subtotal',
                    'fieldName' => __('Order Subtotal', 'bit-integrations-pro'),
                ],
                'Order Total' => (object) [
                    'fieldKey'  => 'order_total',
                    'fieldName' => __('Order Total', 'bit-integrations-pro'),
                ],
                'Order Tax' => (object) [
                    'fieldKey'  => 'order_tax',
                    'fieldName' => __('Order Tax', 'bit-integrations-pro'),
                ],
                'Payment Method' => (object) [
                    'fieldKey'  => 'payment_method',
                    'fieldName' => __('Payment Method', 'bit-integrations-pro'),
                ],
            ];

            $fields = array_merge($userFields, $refundField);
        }

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function allProducts()
    {
        $args = [
            'post_type'      => 'download',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];

        $products = get_posts($args);
        $productsArray[] = [
            'id'    => 'any',
            'title' => __('Any Product', 'bit-integrations-pro')
        ];
        foreach ($products as $product) {
            $productsArray[] = (object) [
                'id'    => $product->ID,
                'title' => $product->post_title,
            ];
        }

        return $productsArray;
    }

    public static function allDiscount()
    {
        $allDiscountCode[] = [
            'id'    => 'any',
            'title' => __('Any Discount Code', 'bit-integrations-pro')
        ];
        $discountCodes = edd_get_discounts();
        foreach ($discountCodes as $discount) {
            $allDiscountCode[] = (object) [
                'id'    => $discount->code,
                'title' => $discount->name,
            ];
        }

        return $allDiscountCode;
    }

    public static function getUserInfo($user_id)
    {
        $userInfo = get_userdata($user_id);
        $user = [];
        if ($userInfo) {
            $userData = $userInfo->data;
            $user_meta = get_user_meta($user_id);
            $user = [
                'first_name' => $user_meta['first_name'][0],
                'last_name'  => $user_meta['last_name'][0],
                'user_email' => $userData->user_email,
                'nickname'   => $userData->user_nicename,
                'avatar_url' => get_avatar_url($user_id),
            ];
        }

        return $user;
    }
}
