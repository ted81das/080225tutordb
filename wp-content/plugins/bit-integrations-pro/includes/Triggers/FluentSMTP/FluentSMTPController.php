<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentSMTP;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class FluentSMTPController
{
    public static function info()
    {
        return [
            'name'              => 'FluentSMTP',
            'title'             => __('FluentSMTP is the ultimate WP Mail Plugin that connects with your Email Service Provider natively and makes sure your emails are delivered.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/fluentsmtp-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'fluent_smtp/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'fluent_smtp/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'fluent_smtp/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'FluentSMTP'));
        }

        wp_send_json_success([
            ['form_name' => __('Email was sent successfully', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluentmail_email_sending_succeeded', 'skipPrimaryKey' => true],
            ['form_name' => __('Failed to send email', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluentmail_email_sending_failed_no_fallback', 'skipPrimaryKey' => true]
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

    public static function handleErrorInEmailDelivery($log_id, $handler, $data)
    {
        if (empty($data) || !static::isPluginInstalled()) {
            return;
        }

        return static::flowExecute('fluentmail_email_sending_failed_no_fallback', static::setMailData((array) $data));
    }

    public static function handleEmailSucceeded($mail)
    {
        if (empty($mail) || !static::isPluginInstalled()) {
            return;
        }

        return static::flowExecute('fluentmail_email_sending_succeeded', static::setMailData((array) $mail));
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $formData = Helper::prepareFetchFormatFields($formData);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('FluentSMTP', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('FluentSMTP', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }

    private static function setMailData($mail)
    {
        return [
            'to'          => \is_string($mail['to']) ? unserialize($mail['to']) : $mail['to'],
            'from'        => $mail['from'] ?? null,
            'subject'     => $mail['subject'] ?? null,
            'body'        => $mail['body'] ?? null,
            'status'      => $mail['status'] ?? null,
            'message'     => $mail['message'] ?? null,
            'attachments' => isset($mail['attachments']) && \is_string($mail['attachments']) ? unserialize($mail['attachments']) : $mail['attachments'] ?? null,
            'headers'     => isset($mail['headers']) && \is_string($mail['headers']) ? unserialize($mail['headers']) : $mail['headers'] ?? null,
            'response'    => isset($mail['response']) && \is_string($mail['response']) ? unserialize($mail['response']) : $mail['response'] ?? null,
            'extra'       => isset($mail['extra']) && \is_string($mail['extra']) ? unserialize($mail['extra']) : $mail['extra'] ?? null,
        ];
    }

    private static function isPluginInstalled()
    {
        return \function_exists('fluentSmtpInit');
    }
}
