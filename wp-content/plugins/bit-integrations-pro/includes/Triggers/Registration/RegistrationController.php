<?php

namespace BitApps\BTCBI_PRO\Triggers\Registration;

use BitCode\FI\Flow\Flow;

final class RegistrationController
{
    public static function info()
    {
        return [
            'name'      => 'WP User Registration',
            'title'     => __('WP User Registration', 'bit-integrations-pro'),
            'type'      => 'form',
            'is_active' => true,
            'list'      => [
                'action' => 'registration/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'registration/get/form',
                'method' => 'post',
                'data'   => ['id'],
            ],
            'isPro' => true

        ];
    }

    public function getAll()
    {
        $forms = [
            ['id' => 1, 'title' => __('Create New User', 'bit-integrations-pro')],
            ['id' => 2, 'title' => __('User Profile Update', 'bit-integrations-pro')],
            ['id' => 3, 'title' => __('User Login', 'bit-integrations-pro')],
            ['id' => 4, 'title' => __('User reset password', 'bit-integrations-pro')],
            ['id' => 5, 'title' => __('User delete account', 'bit-integrations-pro')],
        ];

        wp_send_json_success($forms);
    }

    public static function fields($triggerId)
    {
        $fields = [
            [
                'name'  => 'user_email',
                'label' => __('Email', 'bit-integrations-pro'),
                'type'  => 'email',
            ],
            [
                'name'  => 'user_login',
                'label' => __('Username', 'bit-integrations-pro'),
                'type'  => 'text',
            ],

            [
                'name'  => 'nickname',
                'label' => __('Nickname', 'bit-integrations-pro'),
                'type'  => 'text',
            ],
            [
                'name'  => 'display_name',
                'label' => __('Display Name', 'bit-integrations-pro'),
                'type'  => 'text',
            ],
            [
                'name'  => 'first_name',
                'label' => __('First Name', 'bit-integrations-pro'),
                'type'  => 'text',
            ],
            [
                'name'  => 'last_name',
                'label' => __('Last Name', 'bit-integrations-pro'),
                'type'  => 'text',
            ],
            [
                'name'  => 'user_url',
                'label' => __('Website', 'bit-integrations-pro'),
                'type'  => 'url',
            ],
            [
                'name'  => 'description',
                'label' => __('Biographical Info', 'bit-integrations-pro'),
                'type'  => 'text',
            ],
        ];

        if (\in_array($triggerId, [3, 4, 5])) {
            unset($fields[4], $fields[5], $fields[7]);

            array_unshift($fields, [
                'name'  => 'user_id',
                'label' => 'User Id',
                'type'  => 'text',
            ]);

            $fields = array_values($fields);
        }

        return $fields;
    }

    public function get_a_form($data)
    {
        $responseData['fields'] = self::fields($data->id);
        $responseData['fields'][] = [
            'name'  => 'user_pass',
            'label' => 'Password',
            'type'  => 'password',
        ];

        wp_send_json_success($responseData);
    }

    public static function userCreate()
    {
        $newUserData = \func_get_args()[1];

        $userCreateFlow = Flow::exists('Registration', 1);

        if ($userCreateFlow) {
            Flow::execute('Registration', 1, $newUserData, $userCreateFlow);
        }
    }

    public static function profileUpdate()
    {
        $userdata = \func_get_args()[2];

        $userUpdateFlow = Flow::exists('Registration', 2);

        if ($userUpdateFlow) {
            Flow::execute('Registration', 2, $userdata, $userUpdateFlow);
        }
    }

    public static function wpLogin($userId, $data)
    {
        $userLoginFlow = Flow::exists('Registration', 3);

        if ($userLoginFlow) {
            $user = [];

            if (isset($data->data)) {
                $user['user_id'] = $userId;
                $user['user_login'] = $data->data->user_login;
                $user['user_email'] = $data->data->user_email;
                $user['user_url'] = $data->data->user_url;
                $user['nickname'] = $data->data->user_nicename;
                $user['display_name'] = $data->data->display_name;
            }
            Flow::execute('Registration', 3, $user, $userLoginFlow);
        }
    }

    public static function wpResetPassword($data)
    {
        $userResetPassFlow = Flow::exists('Registration', 4);

        if ($userResetPassFlow) {
            $user = [];
            if (isset($data->data)) {
                $user['user_id'] = $data->data->ID;
                $user['user_login'] = $data->data->user_login;
                $user['user_email'] = $data->data->user_email;
                $user['user_url'] = $data->data->user_url;
                $user['nickname'] = $data->data->user_nicename;
                $user['display_name'] = $data->data->display_name;
            }

            Flow::execute('Registration', 4, $user, $userResetPassFlow);
        }
    }

    public static function wpUserDeleted()
    {
        $data = \func_get_args()[2];

        $userDeleteFlow = Flow::exists('Registration', 5);

        if ($userDeleteFlow) {
            $user = [];
            if (isset($data->data)) {
                $user['user_id'] = $data->data->ID;
                $user['user_login'] = $data->data->user_login;
                $user['user_email'] = $data->data->user_email;
                $user['user_url'] = $data->data->user_url;
                $user['nickname'] = $data->data->user_nicename;
                $user['display_name'] = $data->data->display_name;
            }

            Flow::execute('Registration', 5, $user, $userDeleteFlow);
        }
    }
}
