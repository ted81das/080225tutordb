<?php

namespace BitApps\BTCBI_PRO\Triggers\Buddypress;

class StaticData
{
    public static function forms()
    {
        return [
            ['form_name' => __('User Sends Friendship Request', 'bit-integrations-pro'), 'triggered_entity_id' => 'friends_friendship_requested', 'skipPrimaryKey' => true],
            ['form_name' => __('User Accepts Friendship Request', 'bit-integrations-pro'), 'triggered_entity_id' => 'friends_friendship_accepted', 'skipPrimaryKey' => true],
            ['form_name' => __('User Rejects Friendship Request', 'bit-integrations-pro'), 'triggered_entity_id' => 'friends_friendship_rejected', 'skipPrimaryKey' => true],
            ['form_name' => __('User Deletes Friendship', 'bit-integrations-pro'), 'triggered_entity_id' => 'friends_friendship_deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('User Withdraws Friendship Request', 'bit-integrations-pro'), 'triggered_entity_id' => 'friends_friendship_withdrawn', 'skipPrimaryKey' => true],
            ['form_name' => __('User Creates Group', 'bit-integrations-pro'), 'triggered_entity_id' => 'groups_create_group', 'skipPrimaryKey' => true],
            ['form_name' => __('User Joins Group', 'bit-integrations-pro'), 'triggered_entity_id' => 'groups_join_group', 'skipPrimaryKey' => true],
            ['form_name' => __('User Leaves Group', 'bit-integrations-pro'), 'triggered_entity_id' => 'groups_leave_group', 'skipPrimaryKey' => true],
            ['form_name' => __('User Profile Type is Changed', 'bit-integrations-pro'), 'triggered_entity_id' => 'bp_set_member_type', 'skipPrimaryKey' => true],
            ['form_name' => __('User post to the group activity stream', 'bit-integrations-pro'), 'triggered_entity_id' => 'bp_groups_posted_update', 'skipPrimaryKey' => true],
            ['form_name' => __('User Posts Activity to Stream', 'bit-integrations-pro'), 'triggered_entity_id' => 'bp_activity_posted_update', 'skipPrimaryKey' => true],
        ];
    }
}
