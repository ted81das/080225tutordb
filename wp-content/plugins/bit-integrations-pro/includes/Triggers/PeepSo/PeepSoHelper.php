<?php

namespace BitApps\BTCBI_PRO\Triggers\PeepSo;

use WP_User;
use PeepSoUser;
use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class PeepSoHelper
{
    public static function formatUserFollowData(array $post_data)
    {
        if (empty($post_data['user_id']) || empty($post_data['uid'])) {
            return;
        }

        $followerUser = User::get($post_data['user_id']);
        $followingUser = User::get($post_data['uid']);

        return Helper::prepareFetchFormatFields([
            'follower'  => $followerUser,
            'following' => $followingUser,
        ]);
    }

    public static function formatUserProfileFieldUpdate($data, $post_data)
    {
        $user_id = $post_data['view_user_id'] ?? null;
        if (!$user_id) {
            return;
        }

        $user = PeepSoUser::get_instance($user_id);
        if (!$user) {
            return;
        }

        $user->profile_fields->load_fields();
        $fields = $user->profile_fields->get_fields();

        $preparedData = [];
        if ($data === 'profilefieldsajax.savefield') {
            $preparedData['field_id'] = sanitize_key($post_data['id'] ?? '');
            $preparedData['field_value'] = sanitize_text_field($post_data['value'] ?? '');
        }

        foreach ($fields as $field) {
            $preparedData[$field->title] = $field->value;
        }

        $currentUser = get_userdata($user_id);

        return Helper::prepareFetchFormatFields(array_merge(
            $preparedData,
            [
                'user_id'     => $user_id,
                'user_email'  => $user->get_email() ?? '',
                'avatar_url'  => $user->get_avatar() ?? '',
                'profile_url' => $user->get_profileurl() ?? '',
                'about_me'    => get_user_meta($user_id, 'description', true) ?? '',
                'website'     => ($currentUser instanceof WP_User) ? $currentUser->user_url : '',
                'role'        => $user->get_user_role() ?? '',
            ]
        ));
    }

    public static function formatNewActivityData($post_id, $activity_id)
    {
        $user_id = absint(get_post_field('post_author', $post_id));
        if (!$user_id) {
            return;
        }

        $user_data = User::get($user_id);
        if (!\is_array($user_data)) {
            return;
        }

        $post = Post::get($post_id);
        if (!$post) {
            return;
        }

        return Helper::prepareFetchFormatFields(array_merge(
            ['activity_id' => $activity_id],
            $user_data,
            $post
        ));
    }

    public static function isPluginInstalled()
    {
        return class_exists('PeepSo');
    }
}
