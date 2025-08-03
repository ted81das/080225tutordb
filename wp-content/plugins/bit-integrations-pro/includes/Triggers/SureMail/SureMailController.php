<?php

namespace BitApps\BTCBI_PRO\Triggers\SureMail;

use SureMails\Loader;
use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class SureMailController
{
    public static function info()
    {
        return [
            'name'              => 'SureMail',
            'title'             => __('A simple yet powerful way to create modern forms for your website.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'sure_emails/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'sure_emails/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'sure_emails/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureMail'));
        }

        wp_send_json_success([
            ['form_name' => __('Email was sent successfully', 'bit-integrations-pro'), 'triggered_entity_id' => 'sure_emails_mail_succeeded', 'skipPrimaryKey' => true],
            ['form_name' => __('Failed to send email', 'bit-integrations-pro'), 'triggered_entity_id' => 'sure_emails_mail_failed', 'skipPrimaryKey' => true],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleMailFailedToSend($mail_data)
    {
        if (isset($mail_data->error_data) && !empty($mail_data->error_data['wp_mail_failed'])) {
            $mail_data = $mail_data->error_data['wp_mail_failed'];
            $mail_data['from'] = $mail_data['headers'][0] ?? '';
        }

        return static::flowExecute('sure_emails_mail_failed', $mail_data);
    }

    public static function handleMailSent($mail_data)
    {
        return static::flowExecute('sure_emails_mail_succeeded', $mail_data);
    }

    private static function isPluginInstalled()
    {
        return class_exists(Loader::class);
    }

    private static function flowExecute($triggered_entity_id, $mail_data)
    {
        if (!static::isPluginInstalled()) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields((array) $mail_data);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('SureMail', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        Flow::execute('SureMail', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
