<?php

namespace BitApps\BTCBI_PRO\Triggers\Buddypress;

use BitCode\FI\Core\Util\Helper;

final class Friendship
{
    public static function handleFriendshipRequested($friendship_id, $initiator_user_id, $friend_user_id)
    {
        return static::handleFriendshipTrigger('friends_friendship_requested', $friendship_id, $initiator_user_id, $friend_user_id);
    }

    public static function handleFriendshipAccepted($friendship_id, $initiator_user_id, $friend_user_id)
    {
        return static::handleFriendshipTrigger('friends_friendship_accepted', $friendship_id, $initiator_user_id, $friend_user_id);
    }

    public static function handleFriendshipRejected($friendship_id, $friendship)
    {
        return static::handleFriendshipTrigger('friends_friendship_rejected', $friendship_id, $friendship->initiator_user_id ?? '', $friendship->friend_user_id ?? '');
    }

    public static function handleFriendshipDeleted($friendship_id, $initiator_user_id, $friend_user_id)
    {
        return static::handleFriendshipTrigger('friends_friendship_deleted', $friendship_id, $initiator_user_id, $friend_user_id);
    }

    public static function handleFriendshipWithdrawn($friendship_id, $friendship)
    {
        return static::handleFriendshipTrigger('friends_friendship_withdrawn', $friendship_id, $friendship->initiator_user_id ?? '', $friendship->friend_user_id ?? '');
    }

    private static function handleFriendshipTrigger($triggerEntityId, $friendship_id, $initiator_user_id, $friend_user_id)
    {
        if (empty($friendship_id) || empty($initiator_user_id) || empty($friend_user_id)) {
            return;
        }

        $formData = BuddypressHelper::formatFriendShipTriggersData($friendship_id, $initiator_user_id, $friend_user_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggerEntityId}_test", array_values($formData));

        return BuddypressController::flowExecute($triggerEntityId, $formData);
    }
}
