<?php

namespace BitApps\BTCBI_PRO\Triggers\PrestoPlayer;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class PrestoPlayerController
{
    public static function info()
    {
        return [
            'name'              => 'Presto Player',
            'title'             => __('Connect with your fans, faster your community.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => PrestoPlayerHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'presto_player/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'presto_player/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'presto_player/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!PrestoPlayerHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Presto Player'));
        }

        wp_send_json_success([
            ['form_name' => __('Video Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'presto_video_completed', 'skipPrimaryKey' => true],
            ['form_name' => __('Video Watched', 'bit-integrations-pro'), 'triggered_entity_id' => 'presto_video_watched', 'skipPrimaryKey' => true],
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

    public static function handleVideoCompleted($video_id, $percent, $visit_time)
    {
        if (empty($video_id) || empty($percent) || $percent < 100) {
            return;
        }

        return static::flowExecute('presto_video_completed', $video_id, $percent);
    }

    public static function handleVideoWatched($video_id, $percent, $visit_time)
    {
        if (empty($video_id)) {
            return;
        }

        return static::flowExecute('presto_video_watched', $video_id, $percent);
    }

    private static function flowExecute($triggered_entity_id, $video_id, $percent)
    {
        $formData = PrestoPlayerHelper::formatVideoData($video_id, $percent);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('PrestoPlayer', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('PrestoPlayer', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
