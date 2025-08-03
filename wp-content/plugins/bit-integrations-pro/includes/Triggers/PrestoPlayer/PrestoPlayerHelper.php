<?php

namespace BitApps\BTCBI_PRO\Triggers\PrestoPlayer;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\User;
use PrestoPlayer\Models\Video;
use BitCode\FI\Core\Util\Helper;

class PrestoPlayerHelper
{
    public static function formatVideoData($video_id, $percent)
    {
        $data = [
            'video_id'         => $video_id,
            'video_percentage' => $percent
        ];

        if (!class_exists(Video::class)) {
            return Helper::prepareFetchFormatFields($data);
        }

        $video = new Video($video_id);
        $video_data = $video->toArray();
        $data['video'] = $video_data;

        if (empty($video_data['post_id'])) {
            return Helper::prepareFetchFormatFields($data);
        }

        $tags = get_the_terms($video_data['post_id'], 'pp_video_tag');
        if (!empty($tags) && \is_array($tags)) {
            $data['media']['tag'] = array_map(function ($tag) {
                return $tag->name;
            }, $tags);
        }

        $data = array_merge(
            $data,
            User::currentUser(),
            Post::get($video_data['post_id'])
        );

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return \function_exists('presto_player_plugin');
    }
}
