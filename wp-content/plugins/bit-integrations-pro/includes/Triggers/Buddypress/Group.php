<?php

namespace BitApps\BTCBI_PRO\Triggers\Buddypress;

use BitCode\FI\Core\Util\Helper;

final class Group
{
    public static function handleCreateGroup($group_id, $user)
    {
        return static::handleGroupTrigger('groups_create_group', $group_id, $user->user_id ?? '');
    }

    public static function handleJoinGroup($group_id, $user_id)
    {
        return static::handleGroupTrigger('groups_join_group', $group_id, $user_id);
    }

    public static function handleLeaveGroup($group_id, $user_id)
    {
        return static::handleGroupTrigger('groups_leave_group', $group_id, $user_id);
    }

    private static function handleGroupTrigger($triggerEntityId, $group_id, $user_id)
    {
        if (empty($group_id) || empty($user_id)) {
            return;
        }

        $formData = BuddypressHelper::formatGroupData($group_id, $user_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggerEntityId}_test", array_values($formData));

        return BuddypressController::flowExecute($triggerEntityId, $formData);
    }
}
