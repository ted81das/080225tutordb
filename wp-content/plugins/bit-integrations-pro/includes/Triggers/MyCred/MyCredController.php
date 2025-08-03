<?php

namespace BitApps\BTCBI_PRO\Triggers\MyCred;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class MyCredController
{
    public static function info()
    {
        return [
            'name'              => 'myCred',
            'title'             => __('Connect mycred with your favourite apps.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => MyCredHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'mycred/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'mycred/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'mycred/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!MyCredHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'myCred'));
        }

        wp_send_json_success([
            ['form_name' => __('User Earns Points', 'bit-integrations-pro'), 'triggered_entity_id' => 'mycred_user_earns_points', 'skipPrimaryKey' => true],
            ['form_name' => __('User Loses Points', 'bit-integrations-pro'), 'triggered_entity_id' => 'mycred_user_loses_points', 'skipPrimaryKey' => true],
            ['form_name' => __('User Earns Badge', 'bit-integrations-pro'), 'triggered_entity_id' => 'mycred_user_earns_badge', 'skipPrimaryKey' => true],
            ['form_name' => __('User Earns Rank', 'bit-integrations-pro'), 'triggered_entity_id' => 'mycred_user_earns_rank', 'skipPrimaryKey' => true],
            ['form_name' => __('User Loses Rank', 'bit-integrations-pro'), 'triggered_entity_id' => 'mycred_user_loses_rank', 'skipPrimaryKey' => true],
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

    public static function handleUserEarnsPoints($execute, $data)
    {
        if ($data['amount'] < 0) {
            return true;
        }

        $formData = MyCredHelper::formatPointEarningsData($data, 'points_earned');

        return static::flowExecute('mycred_user_earns_points', $formData);
    }

    public static function handleUserLosesPoints($execute, $data)
    {
        if ($data['amount'] >= 0) {
            return true;
        }

        $formData = MyCredHelper::formatPointEarningsData($data, 'points_loses');

        return static::flowExecute('mycred_user_loses_points', $formData);
    }

    public static function handleCaptureBadgeEarned($user_id, $badge, $new_level)
    {
        if (empty($user_id) || empty($badge)) {
            return;
        }

        $formData = MyCredHelper::formatBadgeEarnsData($user_id, $badge, $new_level);

        return static::flowExecute('mycred_user_earns_badge', $formData);
    }

    public static function handleCaptureRankEarned($user_id, $rank_id, $results, $point_type)
    {
        if (empty($user_id) || empty($rank_id)) {
            return;
        }

        $formData = MyCredHelper::formatRankEarnsData($user_id, $rank_id, $point_type);

        return static::flowExecute('mycred_user_earns_rank', $formData);
    }

    public static function handleCaptureRankLost($user_id, $rank_id, $results, $point_type)
    {
        if (empty($user_id) || empty($rank_id)) {
            return;
        }

        $formData = MyCredHelper::formatRankEarnsData($user_id, $rank_id, $point_type);

        return static::flowExecute('mycred_user_loses_rank', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return true;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('MyCred', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('MyCred', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
