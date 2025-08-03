<?php

namespace BitApps\BTCBI_PRO\Triggers\PeepSo;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class PeepSoController
{
    public static function info()
    {
        return [
            'name'              => 'PeepSo',
            'title'             => __('PeepSo is a social network plugin for WordPress that allows you to quickly add a social network.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => PeepSoHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'peep_so/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'peep_so/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'peep_so/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!PeepSoHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'PeepSo'));
        }

        wp_send_json_success([
            ['form_name' => __('User Follows PeepSo Member', 'bit-integrations-pro'), 'triggered_entity_id' => 'user_follows_peepso_memeber', 'skipPrimaryKey' => true],
            ['form_name' => __('User Gains Follower', 'bit-integrations-pro'), 'triggered_entity_id' => 'user_gains_followers', 'skipPrimaryKey' => true],
            ['form_name' => __('User Loses Follower', 'bit-integrations-pro'), 'triggered_entity_id' => 'user_losses_followers', 'skipPrimaryKey' => true],
            ['form_name' => __('User Unfollows PeepSo Member', 'bit-integrations-pro'), 'triggered_entity_id' => 'user_unfollows_peepso_memeber', 'skipPrimaryKey' => true],
            ['form_name' => __('User Updates Avatar', 'bit-integrations-pro'), 'triggered_entity_id' => 'peepso_user_after_change_avatar', 'skipPrimaryKey' => true],
            ['form_name' => __('User Updates Profile Field', 'bit-integrations-pro'), 'triggered_entity_id' => 'user_updates_field', 'skipPrimaryKey' => true],
            ['form_name' => __('New Activity Post', 'bit-integrations-pro'), 'triggered_entity_id' => 'peepso_activity_after_add_post', 'skipPrimaryKey' => true],
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

    public static function handleUserFollowsPeppSoMember($data)
    {
        $post_data = $_POST; // @codingStandardsIgnoreLine
        if ('followerajax.set_follow_status' !== $data || empty($post_data) || empty($post_data['follow']) || $post_data['follow'] != 1) {
            return;
        }

        $formData = PeepSoHelper::formatUserFollowData($post_data);

        return static::flowExecute('user_follows_peepso_memeber', $formData);
    }

    public static function handleUserGainsFollower($data)
    {
        $post_data = $_POST; // @codingStandardsIgnoreLine
        if ('followerajax.set_follow_status' !== $data || empty($post_data) || empty($post_data['follow']) || $post_data['follow'] != 1) {
            return;
        }

        $formData = PeepSoHelper::formatUserFollowData($post_data);

        return static::flowExecute('user_gains_followers', $formData);
    }

    public static function handleUserLosesFollower($data)
    {
        $post_data = $_POST; // @codingStandardsIgnoreLine
        if ('followerajax.set_follow_status' !== $data || empty($post_data) || $post_data['follow'] == 1) {
            return;
        }

        $formData = PeepSoHelper::formatUserFollowData($post_data);

        return static::flowExecute('user_losses_followers', $formData);
    }

    public static function handleUserUnfollowsPeppSoMember($data)
    {
        $post_data = $_POST; // @codingStandardsIgnoreLine
        if ('followerajax.set_follow_status' !== $data || empty($post_data) || $post_data['follow'] == 1) {
            return;
        }

        $formData = PeepSoHelper::formatUserFollowData($post_data);

        return static::flowExecute('user_unfollows_peepso_memeber', $formData);
    }

    public static function handleUserUpdatesAvatar($user_id, $dest_thumb, $dest_full, $dest_orig)
    {
        if (empty($user_id) || !class_exists('PeepSoUser')) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(User::get($user_id));

        return static::flowExecute('peepso_user_after_change_avatar', $formData);
    }

    public static function handleUserProfileFieldUpdate($data)
    {
        $post_data = $_POST; /** @codingStandardsIgnoreLine */
        $ajax_actions = [
            'profilefieldsajax.savefield',
            'profilefieldsajax.save_acc',
            'profilepreferencesajax.savepreference',
        ];

        if (!\in_array($data, $ajax_actions) || !class_exists('PeepSoUser') || !isset($post_data['id']) || !isset($post_data['value'])) {
            return;
        }

        $formData = PeepSoHelper::formatUserProfileFieldUpdate($data, $post_data);

        return static::flowExecute('user_updates_field', $formData);
    }

    public static function handleNewActivityPost($post_id, $activity_id)
    {
        if (empty($post_id) || empty($activity_id)) {
            return;
        }

        $formData = PeepSoHelper::formatNewActivityData($post_id, $activity_id);

        return static::flowExecute('peepso_activity_after_add_post', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('PeepSo', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        Flow::execute('PeepSo', $triggered_entity_id, array_column($formData, 'value', 'name'), [$flow]);

        return ['type' => 'success'];
    }
}
