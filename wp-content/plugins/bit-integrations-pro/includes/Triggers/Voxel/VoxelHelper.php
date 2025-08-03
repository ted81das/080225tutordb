<?php

namespace BitApps\BTCBI_PRO\Triggers\Voxel;

class VoxelHelper
{
    public static $userCommonFieldsWithWPKeys = ['display_name' => 'display_name', 'user_email' => 'email', 'ID' => 'user_id'];

    public static $userAllFieldsWithWPkeys = [
        'ID'              => 'id',
        'user_login'      => 'login',
        'display_name'    => 'display_name',
        'user_firstname'  => 'first_name',
        'user_lastname'   => 'last_name',
        'user_email'      => 'email',
        'user_registered' => 'registered',
        'roles'           => 'role'
    ];

    private static $userCommonFieldsKey = ['display_name', 'email', 'user_id'];

    private static $userAllFieldsKey = ['id', 'login', 'display_name', 'first_name', 'last_name', 'email', 'registered', 'role'];

    public static function getAllPostTypes()
    {
        $postTypeList = $voxelPostTypes = [];

        if (class_exists('\Voxel\Post_Type')) {
            $voxelPostTypes = \Voxel\Post_Type::get_voxel_types();
        }

        if (!empty($voxelPostTypes)) {
            foreach ($voxelPostTypes as $key => $voxelPostType) {
                $postType = get_post_type_object($key);

                if ($postType) {
                    if (\in_array($postType->name, ['collection', 'profile'], true)) {
                        continue;
                    }

                    $postTypeList[] = (object) [
                        'value' => $postType->name,
                        'label' => $postType->labels->singular_name,
                    ];
                }
            }
        }

        return $postTypeList;
    }

    public static function getPostTypeRaw()
    {
        $postTypes = json_decode(get_option('voxel:post_types', []), ARRAY_A);
        unset($postTypes['collection'], $postTypes['profile']);

        return array_keys($postTypes);
    }

    public static function getFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        $fields = [];

        if (empty($id)) {
            return $fields;
        }

        switch ($id) {
            case VoxelTasks::COLLECTION_NEW_POST_CREATED:
            case VoxelTasks::COLLECTIONS_POST_UPDATED:
                $fields = self::getPostFields('collection');

                break;
            case VoxelTasks::NEW_PROFILE_CREATED:
            case VoxelTasks::PROFILE_UPDATED:
                $fields = self::getPostFields('profile');

                break;
            case VoxelTasks::PROFILE_APPROVED:
            case VoxelTasks::PROFILE_REJECTED:
                $postFields = self::getPostFields('profile');
                $userFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'profile');
                $fields = array_merge($postFields, $userFields);

                break;
            case VoxelTasks::USER_RECEIVES_MESSAGE:
                $messageField = [['name' => 'content', 'type' => 'text', 'label' => __('Content', 'bit-integrations-pro')]];
                $senderFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'sender');
                $receiverFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'receiver');
                $fields = array_merge($messageField, $senderFields, $receiverFields);

                break;
            case VoxelTasks::USER_REGISTERED_FOR_MEMBERSHIP:
            case VoxelTasks::MEMBERSHIP_PLAN_ACTIVATED:
            case VoxelTasks::MEMBERSHIP_PLAN_SWITCHED:
            case VoxelTasks::MEMBERSHIP_PLAN_CANCELED:
                $fields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'user');

                break;
            case VoxelTasks::NEW_COMMENT_ON_TIMELINE:
                $commentFields = [
                    ['name' => 'comment_id', 'type' => 'text', 'label' => __('Comment Id', 'bit-integrations-pro')],
                    ['name' => 'status_id', 'type' => 'text', 'label' => __('Status Id', 'bit-integrations-pro')],
                    ['name' => 'comment_content', 'type' => 'text', 'label' => __('Comment Content', 'bit-integrations-pro')],
                ];

                $commenterFields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'commenter');
                $fields = array_merge($commentFields, $commenterFields);

                break;
            case VoxelTasks::COMMENT_NEW_REPLY:
                $replyFields = [
                    ['name' => 'reply_id', 'type' => 'text', 'label' => __('Reply Id', 'bit-integrations-pro')],
                    ['name' => 'reply', 'type' => 'text', 'label' => __('Reply', 'bit-integrations-pro')],
                    ['name' => 'comment_id', 'type' => 'text', 'label' => __('Comment Id', 'bit-integrations-pro')],
                    ['name' => 'comment', 'type' => 'text', 'label' => __('Comment', 'bit-integrations-pro')],
                ];

                $replierFields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'replier');
                $commenterFields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'commenter');
                $fields = array_merge($replyFields, $replierFields, $commenterFields);

                break;
            case VoxelTasks::PROFILE_NEW_WALL_POST:
                $profilePostFields = self::getPostFields('profile');
                $profileFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'profile');
                $wallPostField = [['name' => 'wall_post', 'type' => 'text', 'label' => __('Wall Posts', 'bit-integrations-pro')]];
                $fields = array_merge($profilePostFields, $profileFields, $wallPostField);

                break;
            case VoxelTasks::NEW_ORDER_PLACED:
            case VoxelTasks::ORDER_CANCELED_BY_CUSTOMER:
            case VoxelTasks::ORDERS_CLAIM_LISTING:
            case VoxelTasks::ORDER_PROMOTION_ACTIVATED:
            case VoxelTasks::ORDER_PROMOTION_CANCELED:
                $fields = self::getOrderFields(true);

                break;
            case VoxelTasks::ORDER_APPROVED_BY_VENDOR:
            case VoxelTasks::ORDER_DECLINED_BY_VENDOR:
                $fields = self::getOrderFields(true, true);

                break;
        }

        return $fields;
    }

    public static function getPostTypeFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        $fields = [];

        if (empty($id)) {
            return $fields;
        }

        $postTypeFields = [['name' => 'post_type', 'type' => 'text', 'label' => __('Post Type', 'bit-integrations-pro')]];

        $fields = array_merge(self::getPostFields($id), $postTypeFields);

        if (isset($data->TaskId) && $data->TaskId === VoxelTasks::POST_REVIEWED) {
            $reviewFields = [
                ['name' => 'review_content', 'type' => 'text', 'label' => __('Review Content', 'bit-integrations-pro')],
                ['name' => 'review_created_at', 'type' => 'text', 'label' => __('Review Created At', 'bit-integrations-pro')],
                ['name' => 'review_details', 'type' => 'text', 'label' => __('Review Details', 'bit-integrations-pro')],
            ];

            $reviewByFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'reviewer');

            $fields = array_merge($fields, $reviewFields, $reviewByFields);
        }

        if (isset($data->TaskId) && $data->TaskId === VoxelTasks::NEW_WALL_POST_BY_USER) {
            $userFields = self::createUserFieldsByTypes(self::$userCommonFieldsKey, 'user');
            $wallPostField = [['name' => 'wall_post', 'type' => 'text', 'label' => __('Wall Posts', 'bit-integrations-pro')]];

            $fields = array_merge($fields, $userFields, $wallPostField);
        }

        return $fields;
    }

    public static function getPostFieldsData($postId)
    {
        $data = [];

        if (!class_exists('Voxel\Post')) {
            return $data;
        }

        $post = \Voxel\Post::force_get($postId);

        if (!empty($post)) {
            $fields = $post->get_fields();
            if (\is_array($fields) && !empty($fields)) {
                foreach ($fields as $field) {
                    $fieldKey = $field->get_key();
                    $fieldType = $field->get_type();
                    $fieldValue = $field->get_value();
                    $fieldContent = null;

                    if ($fieldType === 'location') {
                        $fieldContent = $fieldValue['address'];
                    } elseif ($fieldType === 'work-hours') {
                        $hours = [];

                        if (\is_array($fieldValue) && !empty($fieldValue)) {
                            foreach ($fieldValue as $workHour) {
                                if ($workHour['status'] === 'hours') {
                                    foreach ($workHour['days'] as $day) {
                                        foreach ($workHour['hours'] as $hourKey => $hour) {
                                            $hours[$day . '_' . $hourKey] = $hour['from'] . '-' . $hour['to'];
                                        }
                                    }
                                }
                            }
                        }

                        $fieldContent = wp_json_encode($hours);
                    } elseif ($fieldType === 'file' || $fieldType === 'image' || $fieldType === 'profile-avatar' || $fieldType === 'gallery') {
                        if (\is_array($fieldValue)) {
                            foreach ($fieldValue as $fileKey => $fileId) {
                                $fieldContent[$fieldKey . '_' . $fileKey . '_url'] = wp_get_attachment_url($fileId);
                            }
                        } else {
                            $fieldContent[$fieldKey . '_url'] = wp_get_attachment_url($fieldValue);
                        }
                    } elseif ($fieldType === 'taxonomy') {
                        $fieldContent = join(
                            ', ',
                            array_map(
                                function ($term) {
                                    return $term->get_label();
                                },
                                $fieldValue
                            )
                        );
                    } else {
                        $fieldContent = $fieldValue;
                    }

                    $data[$fieldKey] = $fieldContent;
                }
                $data['id'] = $postId;
            }
        }

        return $data;
    }

    public static function userDataByType($id, array $fieldsWithKeys, $type = null)
    {
        $userData = get_userdata(\intval($id));
        $formattedUserData = [];

        foreach ($fieldsWithKeys as $key => $item) {
            if (is_numeric($key)) {
                $wpUserKey = $item;
            } else {
                $wpUserKey = $key;
            }

            if (\is_array($userData->{$wpUserKey})) {
                $data = implode(',', $userData->{$wpUserKey});
            } else {
                $data = $userData->{$wpUserKey};
            }

            $formattedUserData[(!empty($type) ? $type . '_' : '') . $item] = $data;
        }

        return $formattedUserData;
    }

    public static function formatOrderData($event, $withCustomerData = false, $withVendorData = false)
    {
        $order = $event->order;

        $data['order_id'] = $order->get_id();
        $data['vendor_id'] = $order->get_vendor_id();
        $data['order_details'] = $order->get_details();
        $data['payment_method'] = $order->get_payment_method_key();
        $data['tax_amount'] = $order->get_tax_amount();
        $data['discount_amount'] = $order->get_discount_amount();
        $data['shipping_amount'] = $order->get_shipping_amount();
        $data['order_status'] = $order->get_status();
        $data['created_at'] = $order->get_created_at();
        $data['subtotal'] = $order->get_subtotal();
        $data['total'] = $order->get_total();
        $data['order_items_count'] = $order->get_item_count();

        $orderItems = $order->get_items();

        foreach ($orderItems as $orderItem) {
            $data['order_items'][] = [
                'item_id'               => $orderItem->get_id(),
                'item_type'             => $orderItem->get_type(),
                'currency'              => $orderItem->get_currency(),
                'quantity'              => $orderItem->get_quantity(),
                'subtotal'              => $orderItem->get_subtotal(),
                'product_id'            => $orderItem->get_post()->get_id(),
                'product_label'         => $orderItem->get_product_label(),
                'product_thumbnail_url' => $orderItem->get_product_thumbnail_url(),
                'product_link'          => $orderItem->get_product_link(),
                'description'           => $orderItem->get_product_description()
            ];
        }

        $customerData = $vendorData = [];

        if ($withCustomerData) {
            $customerData = VoxelHelper::userDataByType(
                $event->customer->get_id(),
                self::$userAllFieldsWithWPkeys,
                'customer'
            );
        }

        if ($withVendorData) {
            $vendorData = VoxelHelper::userDataByType(
                $order->get_vendor_id(),
                self::$userAllFieldsWithWPkeys,
                'vendor'
            );
        }

        return array_merge($data, $customerData, $vendorData);
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '' || $value === 'empty_terms') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    private static function getPostFields(string $postType)
    {
        $fields = [];

        if (!class_exists('Voxel\Post_Type')) {
            return $fields;
        }

        $postType = \Voxel\Post_Type::get($postType);
        $postFields = $postType->get_fields();

        if (\is_array($postFields) && !empty($postFields)) {
            $fields[] = [
                'name'  => 'id',
                'type'  => 'text',
                'label' => 'ID'
            ];

            foreach ($postFields as $postField) {
                $fieldType = $postField->get_type();

                if (\in_array($fieldType, ['ui-step', 'ui-html', 'ui-heading', 'ui-image'], true)) {
                    continue;
                }

                $fields[] = [
                    'name'  => $postField->get_key(),
                    'type'  => $fieldType,
                    'label' => $postField->get_label(),
                ];
            }
        }

        return $fields;
    }

    private static function createUserFieldsByTypes(array $fields, $type = null)
    {
        $userFields = [];

        foreach ($fields as $item) {
            $userFields[] = [
                'name'  => (!empty($type) ? $type . '_' : '') . $item,
                'type'  => 'text',
                'label' => (!empty($type) ? ucwords(str_replace('_', ' ', $type)) . ' ' : '')
                . ucwords(str_replace('_', ' ', $item)),
            ];
        }

        return $userFields;
    }

    private static function getOrderFields($withCustomerFields = false, $withVendorFields = false)
    {
        $fields = [
            ['name' => 'order_id', 'type' => 'text', 'label' => __('Order Id', 'bit-integrations-pro')],
            ['name' => 'vendor_id', 'type' => 'text', 'label' => __('Vendor Id', 'bit-integrations-pro')],
            ['name' => 'order_details', 'type' => 'text', 'label' => __('Order Details', 'bit-integrations-pro')],
            ['name' => 'payment_method', 'type' => 'text', 'label' => __('Payment Method', 'bit-integrations-pro')],
            ['name' => 'tax_amount', 'type' => 'text', 'label' => __('Tax Amount', 'bit-integrations-pro')],
            ['name' => 'discount_amount', 'type' => 'text', 'label' => __('Discount Amount', 'bit-integrations-pro')],
            ['name' => 'shipping_amount', 'type' => 'text', 'label' => __('Shipping Amount', 'bit-integrations-pro')],
            ['name' => 'order_status', 'type' => 'text', 'label' => __('Order Status', 'bit-integrations-pro')],
            ['name' => 'created_at', 'type' => 'text', 'label' => __('Created At', 'bit-integrations-pro')],
            ['name' => 'subtotal', 'type' => 'text', 'label' => __('Subtotal', 'bit-integrations-pro')],
            ['name' => 'total', 'type' => 'text', 'label' => __('Total', 'bit-integrations-pro')],
            ['name' => 'order_items', 'type' => 'text', 'label' => __('Order Items', 'bit-integrations-pro')],
            ['name' => 'order_items_count', 'type' => 'text', 'label' => __('Order Items Count', 'bit-integrations-pro')],
        ];

        $customerFields = $vendorFields = [];

        if ($withCustomerFields) {
            $customerFields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'customer');
        }

        if ($withVendorFields) {
            $vendorFields = self::createUserFieldsByTypes(self::$userAllFieldsKey, 'vendor');
        }

        return array_merge($fields, $customerFields, $vendorFields);
    }
}
