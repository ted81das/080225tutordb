<?php

namespace BitApps\BTCBI_PRO\Triggers\ProfileBuilder;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class ProfileBuilderController
{
    public static function info()
    {
        return [
            'name'              => 'Profile Builder',
            'title'             => __('Conversational Forms Builder for WordPress', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'profile-builder/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'profile-builder/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'profile-builder/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Profile Builder'));
        }

        wp_send_json_success([
            ['form_name' => __('User Registration', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_register_success', 'skipPrimaryKey' => true, 'note' => 'When a user successfully registers through any Profile Builder registration form.'],
            ['form_name' => __('User Profile Update', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_edit_profile_success', 'skipPrimaryKey' => true, 'note' => 'When a user successfully edits his profile through any Profile Builder edit-profile form.'],
            ['form_name' => __('User Email Confirmation', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_activate_user', 'skipPrimaryKey' => true, 'note' => 'When the user successfully confirms his email, if Email Confirmation is active.'],
            ['form_name' => __('Email Send By Profile Builder', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_after_sending_email', 'skipPrimaryKey' => true, 'note' => 'Filter triggered for every email sent by Profile Builder, that allows you to prevent certain types of mails from being sent.'],
            ['form_name' => __('User Approved By Admin', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_after_user_approval', 'skipPrimaryKey' => true, 'note' => 'After an user is approved from the Users -> Admin Approval panel.'],
            ['form_name' => __('User UnApproved By Admin', 'bit-integrations-pro'), 'triggered_entity_id' => 'wppb_after_user_unapproval', 'skipPrimaryKey' => true, 'note' => 'After an user is unapproved from the Users -> Admin Approval panel.'],
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

    public static function handleUserRegistration($request, $form_name, $user_id)
    {
        if (empty($request)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields($request);

        return static::flowExecute('wppb_register_success', $formData);
    }

    public static function handleUserUpdate($request, $form_name, $user_id)
    {
        if (empty($request)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields($request);

        return static::flowExecute('wppb_edit_profile_success', $formData);
    }

    public static function handleUserEmailConfirmed($user_id, $password, $meta)
    {
        if (empty($user_id) || empty($meta)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(array_merge(['user_id' => $user_id], $meta));

        return static::flowExecute('wppb_activate_user', $formData);
    }

    public static function handleSendEmail($sent, $to, $subject, $message, $send_email, $context)
    {
        if (empty($to)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields([
            'to'      => $to,
            'subject' => $subject,
            'message' => $message,
            'context' => $context
        ]);

        return static::flowExecute('wppb_after_sending_email', $formData);
    }

    public static function handleAdminApproval($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(User::get($user_id));

        return static::flowExecute('wppb_after_user_approval', $formData);
    }

    public static function handleAdminUnApproval($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(User::get($user_id));

        return static::flowExecute('wppb_after_user_unapproval', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('ProfileBuilder', $triggered_entity_id);
        if (empty($flows)) {
            return;
        }

        Flow::execute('ProfileBuilder', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return \defined('WPPB_PLUGIN_DIR');
    }
}
