<?php

namespace BitApps\BTCBI_PRO\Triggers\MyCred;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class MyCredHelper
{
    public static function formatPointEarningsData($data, $statusKey = 'points_earned')
    {
        $pointData = [
            'total_points' => mycred_get_users_balance($data['user_id']),
            $statusKey     => true
        ];

        foreach ($data as $key => $value) {
            if (\is_array($value) || \is_object($value)) {
                $pointData = Helper::flattenNestedData($pointData, $key, $value);
            } else {
                $pointData[$key] = $value;
            }
        }

        return Helper::prepareFetchFormatFields($pointData);
    }

    public static function formatBadgeEarnsData($user_id, $badge, $new_level)
    {
        $data = User::get($user_id);

        foreach (mycred_get_badge($badge) as $key => $field) {
            if (\is_array($field) || \is_object($field)) {
                $data = Helper::flattenNestedData($data, $key, $field);
            } else {
                $data[$key] = $field;
            }
        }

        $data['badge_level'] = $new_level;

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatRankEarnsData($user_id, $rank_id, $point_type)
    {
        $rank = mycred_get_rank($rank_id);

        return Helper::prepareFetchFormatFields(array_merge(
            User::get($user_id),
            [
                'rank_id'      => $rank->post_id ?? '',
                'title'        => $rank->title ?? '',
                'minimum'      => $rank->minimum ?? '',
                'maximum'      => $rank->maximum ?? '',
                'users'        => $rank->count ?? '',
                'logo_id'      => $rank->logo_id ?? '',
                'logo_url'     => $rank->logo_url ?? '',
                'point_type'   => $point_type ?? '',
                'total_points' => mycred_get_users_balance($user_id) ?? ''
            ]
        ));
    }

    public static function isPluginInstalled()
    {
        return class_exists('myCRED_Core');
    }
}
