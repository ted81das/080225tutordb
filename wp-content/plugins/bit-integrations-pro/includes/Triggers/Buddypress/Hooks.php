<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Buddypress\BuddypressController;
use BitApps\BTCBI_PRO\Triggers\Buddypress\Friendship;
use BitApps\BTCBI_PRO\Triggers\Buddypress\Group;

Hooks::add('friends_friendship_requested', [Friendship::class, 'handleFriendshipRequested'], 10, 3);
Hooks::add('friends_friendship_accepted', [Friendship::class, 'handleFriendshipAccepted'], 10, 3);
Hooks::add('friends_friendship_rejected', [Friendship::class, 'handleFriendshipRejected'], 10, 2);
Hooks::add('friends_friendship_deleted', [Friendship::class, 'handleFriendshipDeleted'], 10, 3);
Hooks::add('friends_friendship_withdrawn', [Friendship::class, 'handleFriendshipWithdrawn'], 10, 2);
Hooks::add('groups_create_group', [Group::class, 'handleCreateGroup'], 10, 2);
Hooks::add('groups_join_group', [Group::class, 'handleJoinGroup'], 10, 2);
Hooks::add('groups_leave_group', [Group::class, 'handleLeaveGroup'], 10, 2);
Hooks::add('bp_set_member_type', [BuddypressController::class, 'handleSetMemberType'], 10, 2);
Hooks::add('bp_groups_posted_update', [BuddypressController::class, 'handleUserPostToGroupActivity'], 10, 4);
Hooks::add('bp_activity_posted_update', [BuddypressController::class, 'handleActivityPostedUpdate'], 10, 4);
