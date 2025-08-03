<?php

namespace BitApps\BTCBI_PRO\Triggers\NewUserApprove;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class NewUserApproveController
{
    public static function info()
    {
        return [
            'name'              => 'New User Approve',
            'title'             => __('Manage Website Access With the Best WordPress User Management Plugin', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'new-user-approve/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'new-user-approve/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'new-user-approve/test/remove',
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
            ['form_name' => __('User Approved', 'bit-integrations-pro'), 'triggered_entity_id' => 'new_user_approve_user_approved', 'skipPrimaryKey' => true, 'note' => 'When a user successfully approved through New User Approve Plugin.'],
            ['form_name' => __('User Denied', 'bit-integrations-pro'), 'triggered_entity_id' => 'new_user_approve_user_denied', 'skipPrimaryKey' => true, 'note' => 'When a user successfully denied through New User Approve Plugin.'],
            ['form_name' => __('User Status Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'new_user_approve_user_status_update', 'skipPrimaryKey' => true, 'note' => 'When successfully changed the access status through New User Approve Plugin.'],
            ['form_name' => __('User Status Set To Approve', 'bit-integrations-pro'), 'triggered_entity_id' => 'new_user_approve_approve_user', 'skipPrimaryKey' => true, 'note' => 'When successfully changed the access status to approve through New User Approve Plugin.'],
            ['form_name' => __('User Status Set To Deny', 'bit-integrations-pro'), 'triggered_entity_id' => 'new_user_approve_deny_user', 'skipPrimaryKey' => true, 'note' => 'When successfully changed the access status to deny through New User Approve Plugin.'],
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

    public static function handleUserApproved($user)
    {
        return self::prepareAndExecute('new_user_approve_user_approved', $user);
    }

    public static function handleUserDenied($user)
    {
        return self::prepareAndExecute('new_user_approve_user_denied', $user);
    }

    public static function handleUserStatusUpdated($user_id, $status)
    {
        return self::prepareAndExecute('new_user_approve_user_status_update', null, $user_id, $status);
    }

    public static function handleUserStatusApprove($user_id)
    {
        return self::prepareAndExecute('new_user_approve_approve_user', null, $user_id);
    }

    public static function handleUserStatusDeny($user_id)
    {
        return self::prepareAndExecute('new_user_approve_deny_user', null, $user_id);
    }

    private static function prepareAndExecute($triggerId, $user = null, $userId = null, $status = null)
    {
        $userData = self::setUserData($user, $userId, $status);
        $formData = Helper::prepareFetchFormatFields($userData);

        return self::flowExecute($triggerId, $formData);
    }

    private static function setUserData($user = null, $userId = null, $status = null)
    {
        $userData = [];

        if ($user) {
            $userData = (array) $user->data;
            $userData['roles'] = $user->roles ?? [];
        } elseif ($userId) {
            $userData = User::get($userId) ?? [];
        }

        if ($status !== null) {
            $userData['wp_user_status'] = $status;
        }

        return $userData;
    }

    private static function flowExecute($triggeredEntityId, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggeredEntityId}_test", array_values($formData));

        $flows = Flow::exists('NewUserApprove', $triggeredEntityId);
        if (empty($flows)) {
            return;
        }

        Flow::execute('NewUserApprove', $triggeredEntityId, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('PW_New_User_Approve');
    }
}
