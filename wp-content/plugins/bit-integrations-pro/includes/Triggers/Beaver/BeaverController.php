<?php

namespace BitApps\BTCBI_PRO\Triggers\Beaver;

use FLBuilderModel;
use BitCode\FI\Flow\Flow;

final class BeaverController
{
    public static function info()
    {
        $plugin_path = 'bb-plugin/fl-builder.php';

        return [
            'name'           => 'Beaver Builder',
            'title'          => __('WordPress Page Builder', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'beaver/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'beaver/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function beaver_contact_form_submitted($mailto, $subject, $template, $headers, $settings, $result)
    {
        $form_id = 'bb_contact_form';
        $flows = Flow::exists('Beaver', $form_id);
        if (!$flows) {
            return;
        }

        $template = str_replace('Name', '|Name', $template);
        $template = str_replace('Email', '|Email', $template);
        $template = str_replace('Phone', '|Phone', $template);
        $template = str_replace('Message', '|Message', $template);

        $filterData = explode('|', $template);
        $filterData = array_map('trim', $filterData);
        $filterData = array_filter($filterData, function ($value) {
            return $value !== '';
        });

        $data = ['subject' => isset($subject) ? $subject : ''];
        foreach ($filterData as $value) {
            $item = explode(':', $value);
            $data[strtolower($item[0])] = trim($item[1]);
        }
        Flow::execute('Beaver', $form_id, $data, $flows);
    }

    public static function beaver_subscribe_form_submitted($response, $settings, $email, $name, $template_id, $post_id)
    {
        $form_id = 'bb_subscription_form';
        $flows = Flow::exists('Beaver', $form_id);
        if (!$flows) {
            return;
        }

        $data = [
            'name'  => isset($name) ? $name : '',
            'email' => isset($email) ? $email : '',
        ];
        Flow::execute('Beaver', $form_id, $data, $flows);
    }

    public static function beaver_login_form_submitted($settings, $password, $name, $template_id, $post_id)
    {
        $form_id = 'bb_login_form';
        $flows = Flow::exists('Beaver', $form_id);
        if (!$flows) {
            return;
        }

        $data = [
            'name'     => isset($name) ? $name : '',
            'password' => isset($password) ? $password : '',
        ];
        Flow::execute('Beaver', $form_id, $data, $flows);
    }

    public function getAllForms()
    {
        if (!is_plugin_active('bb-plugin/fl-builder.php')) {
            wp_send_json_error(__('Beaver Builder is not installed or activated', 'bit-integrations-pro'));
        }

        $forms = [
            [
                'id'    => 'bb_contact_form',
                'title' => __('Contact Form', 'bit-integrations-pro'),
            ], [
                'id'    => 'bb_subscription_form',
                'title' => __('Subscription Form', 'bit-integrations-pro'),
            ], [
                'id'    => 'bb_login_form',
                'title' => __('Login Form', 'bit-integrations-pro'),
            ]
        ];

        $all_forms = [];
        foreach ($forms as $form) {
            $all_forms[] = (object) [
                'id'    => $form['id'],
                'title' => $form['title'],
            ];
        }
        wp_send_json_success($all_forms);
    }

    public function getFormFields($data)
    {
        if (!is_plugin_active('bb-plugin/fl-builder.php')) {
            wp_send_json_error(__('Beaver Builder is not installed or activated', 'bit-integrations-pro'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }

        $fields = self::fields($data->id);
        if (empty($fields)) {
            wp_send_json_error(__('Form doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        $form_fields = self::get_form_fields($data);
        $fields = [];
        foreach ($form_fields as $field) {
            $fields[] = [
                'name'  => $field['id'],
                'type'  => $field['field_type'],
                'label' => $field['field_label'],
            ];
        }

        return $fields;
    }

    public static function get_form_fields($form_id)
    {
        $loginForm = FLBuilderModel::get_settings_form_defaults('login-form');

        $form_fields = [
            'bb_contact_form' => [
                ['id' => 'name', 'field_label' => __('Name', 'bit-integrations-pro'), 'field_type' => 'text'],
                ['id' => 'subject', 'field_label' => __('Subject', 'bit-integrations-pro'), 'field_type' => 'text'],
                ['id' => 'email', 'field_label' => __('Email', 'bit-integrations-pro'), 'field_type' => 'email'],
                ['id' => 'phone', 'field_label' => __('Phone', 'bit-integrations-pro'), 'field_type' => 'text'],
                ['id' => 'message', 'field_label' => __('Message', 'bit-integrations-pro'), 'field_type' => 'textarea']
            ],
            'bb_subscription_form' => [
                ['id' => 'name', 'field_label' => __('Name', 'bit-integrations-pro'), 'field_type' => 'text'],
                ['id' => 'email', 'field_label' => __('Email', 'bit-integrations-pro'), 'field_type' => 'email'],
            ],
            'bb_login_form' => [
                ['id' => 'name', 'field_label' => isset($loginForm->name_field_text) ? $loginForm->name_field_text : __('Username', 'bit-integrations-pro'), 'field_type' => 'text'],
                ['id' => 'password', 'field_label' => isset($loginForm->password_field_text) ? $loginForm->password_field_text : __('Password', 'bit-integrations-pro'), 'field_type' => 'password'],
            ],
        ];

        return isset($form_fields[$form_id]) ? $form_fields[$form_id] : [];
    }
}
