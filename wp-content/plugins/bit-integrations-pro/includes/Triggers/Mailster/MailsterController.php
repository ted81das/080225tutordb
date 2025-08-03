<?php

namespace BitApps\BTCBI_PRO\Triggers\Mailster;

use BitCode\FI\Flow\Flow;
use MailsterBlockForms;
use MailsterSubscribers;

final class MailsterController
{
    public static function info()
    {
        $plugin_path = 'mailster/mailster.php';

        return [
            'name'           => 'Mailster',
            'title'          => __('Mailster', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'mailster/mailster.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('mailster/mailster.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'mailster/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'mailster/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('mailster/mailster.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Mailster'));
        }

        $all_forms = [
            (object) [
                'id'    => 'mailster-1',
                'title' => __('Subscriber Subscribed', 'bit-integrations-pro'),
                'note'  => __('Run after the users confirms the subscription', 'bit-integrations-pro')
            ]
        ];

        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('mailster/mailster.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Mailster'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }

        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Fields fetching failed!', 'bit-integrations-pro'), 400);
        }

        $responseData['fields'] = $fields;

        wp_send_json_success($responseData);
    }

    public static function fields($form_id)
    {
        $mailsterBlockForms = new MailsterBlockForms();
        $mailsterFields = $mailsterBlockForms->get_fields();

        if (empty($mailsterFields) || is_wp_error($mailsterFields)) {
            return false;
        }

        $fields = [];

        foreach ($mailsterFields as $item) {
            if ($item['id'] !== 'submit') {
                $fields[] = [
                    'name'  => $item['id'],
                    'type'  => $item['type'],
                    'label' => $item['name'],
                ];
            }
        }

        $otherFields = [
            ['name' => 'ID', 'type' => 'text', 'label' => __('ID', 'bit-integrations-pro')],
            ['name' => 'fullname', 'type' => 'text', 'label' => __('Full Name', 'bit-integrations-pro')],
            ['name' => 'hash', 'type' => 'text', 'label' => __('Hash', 'bit-integrations-pro')],
            ['name' => 'wp_id', 'type' => 'text', 'label' => __('WordPress User ID', 'bit-integrations-pro')],
            ['name' => 'status', 'type' => 'text', 'label' => __('Status', 'bit-integrations-pro')],
            ['name' => 'added', 'type' => 'text', 'label' => __('Added (timestamp)', 'bit-integrations-pro')],
            ['name' => 'updated', 'type' => 'text', 'label' => __('Updated (timestamp)', 'bit-integrations-pro')],
            ['name' => 'signup', 'type' => 'text', 'label' => __('Signup (timestamp)', 'bit-integrations-pro')],
            ['name' => 'confirm', 'type' => 'text', 'label' => __('Confirm (timestamp)', 'bit-integrations-pro')],
            ['name' => 'ip_signup', 'type' => 'text', 'label' => __('IP Signup (timestamp)', 'bit-integrations-pro')],
            ['name' => 'ip_confirm', 'type' => 'text', 'label' => __('IP Confirm (timestamp)', 'bit-integrations-pro')],
            ['name' => 'rating', 'type' => 'text', 'label' => __('Rating', 'bit-integrations-pro')]
        ];

        return array_merge($fields, $otherFields);
    }

    public static function handleMailsterSubmit($userId)
    {
        if (empty($userId)) {
            return;
        }

        $mailsterSubscribers = new MailsterSubscribers();
        $getSubscribers = (array) $mailsterSubscribers->get($userId, true);

        if (!empty($getSubscribers) && $flows = Flow::exists('Mailster', 'mailster-1')) {
            Flow::execute('Mailster', 'mailster-1', $getSubscribers, $flows);
        }
    }
}
