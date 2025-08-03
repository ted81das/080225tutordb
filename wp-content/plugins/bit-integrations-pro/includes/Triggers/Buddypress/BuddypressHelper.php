<?php

namespace BitApps\BTCBI_PRO\Triggers\Buddypress;

use BP_Activity_Activity;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class BuddypressHelper
{
    public static function FormatMemberTypeData($user_id, $member_type)
    {
        $data = User::get($user_id);

        $data['member_type'] = $member_type;

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatGroupPostActivityData($content, $user_id, $group_id, $activity_id)
    {
        if (!static::isPluginInstalled()) {
            return false;
        }

        $data = User::get($user_id);
        $data = array_merge($data, static::getGroupData($group_id), ['activity_id' => $activity_id, 'activity_content' => $content]);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatActivityPostedUpdateData($content, $user_id, $activity_id)
    {
        $data = User::get($user_id);
        $activityData = [];

        if (class_exists('BP_Activity_Activity')) {
            $activity = new BP_Activity_Activity($activity_id);

            $activityData = [
                'activity_date'         => $activity->date_recorded,
                'activity_user_id'      => $activity->user_id,
                'activity_action'       => $activity->type,
                'activity_item_id'      => $activity->item_id,
                'activity_primary_link' => $activity->primary_link,
                'activity_is_spam'      => $activity->is_spam,
            ];
        }

        $data = array_merge($data, $activityData, ['activity_id' => $activity_id, 'activity_content' => $content]);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatGroupData($group_id, $user_id)
    {
        if (!static::isPluginInstalled()) {
            return false;
        }

        $data = User::get($user_id);
        $data = array_merge($data, static::getGroupData($group_id));

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatFriendShipTriggersData($friendship_id, $user_id, $friend_user_id)
    {
        $data = User::get($user_id);
        $friendData = User::get($friend_user_id);

        $data = array_merge(
            $data,
            [
                'friend_user_id'      => $friendData['wp_user_id'] ?? '',
                'friend_email'        => $friendData['user_email'] ?? '',
                'friend_first_name'   => $friendData['user_firstname'] ?? '',
                'friend_last_name'    => $friendData['user_lastname'] ?? '',
                'friend_display_name' => $friendData['display_name'] ?? '',
                'friend_avatar'       => get_avatar_url($friend_user_id) ?? '',
                '$friendship_id'      => $friendship_id
            ]
        );

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('BuddyPress');
    }

    private static function getGroupData($group_id)
    {
        $data = groups_get_group(['group_id' => $group_id]);

        return [
            'group_id'           => $data->id ?? null,
            'group_name'         => $data->name ?? '',
            'group_description'  => $data->description ?? '',
            'group_avatar'       => $data->avatar ?? '',
            'group_creator_id'   => $data->creator_id ?? null,
            'group_status'       => $data->status ?? '',
            'group_date_created' => $data->date_created ?? '',
            'group_slug'         => $data->slug ?? '',
            'group_permalink'    => \function_exists('bp_get_group_url') ? bp_get_group_url($data) : '',
        ];
    }
}
