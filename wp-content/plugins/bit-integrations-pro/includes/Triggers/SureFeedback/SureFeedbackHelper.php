<?php

namespace BitApps\BTCBI_PRO\Triggers\SureFeedback;

use PH\Models\Post;
use BitCode\FI\Core\Util\Helper;

class SureFeedbackHelper
{
    public static function formatResolvedCommentData($comment)
    {
        global $wpdb;
        $comment_result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT  comment_ID FROM {$wpdb->prefix}comments  WHERE comment_post_ID = %d LIMIT 1",
                $comment['ID']
            ),
            ARRAY_A
        );

        $commentData = $comment;
        $comment_id = $comment_result['comment_ID'];
        $commentData['website_id'] = get_comment_meta($comment_id, 'project_id', true) ?? '';

        $itemIdMeta = get_comment_meta($comment_id, 'item_id');
        $commentItemId = \is_array($itemIdMeta) && !empty($itemIdMeta[0]) ? (int) $itemIdMeta[0] : null;

        return static::formatCommentData($commentData, $commentItemId, $comment);
    }

    public static function formatNewCommentData($comment)
    {
        $commentData = $comment;
        $commentData['website_id'] = !empty($comment['project_id']) ? (int) $comment['project_id'] : '';
        unset($commentData['comment_karma'],$commentData['comment_agent'],$commentData['comment_parent']);

        $itemIdMeta = get_comment_meta($comment['comment_ID'], 'item_id');
        $commentItemId = \is_array($itemIdMeta) && !empty($itemIdMeta[0]) ? (int) $itemIdMeta[0] : null;

        return static::formatCommentData($commentData, $commentItemId, $comment);
    }

    public static function formatCommentData($commentData, $commentItemId, $comment)
    {
        $itemData = [
            'comment_item_id'         => $commentItemId ?? '',
            'comment_item_page_title' => $commentItemId ? get_the_title($commentItemId) : '',
            'comment_item_page_url'   => $commentItemId ? get_post_meta($commentItemId, 'page_url', true) : ''
        ];

        $postId = $comment['comment_post_ID'] ?? null;

        if (!$postId) {
            return Helper::prepareFetchFormatFields(array_merge($commentData, $itemData, [
                'project_name'   => '',
                'commenter_name' => $comment['comment_author'] ?? '',
                'project_type'   => '',
                'action_status'  => '',
                'project_link'   => ''
            ]));
        }

        $postType = get_post_type($postId);
        $projectType = $postType === 'ph-website' ? __('Website', 'bit-integrations-pro') : __('Mockup', 'bit-integrations-pro');
        $actionStatus = get_post_meta($postId, 'resolved', true) ? __('Resolved', 'bit-integrations-pro') : __('Unresolved', 'bit-integrations-pro');

        $projectId = Post::get($postId)->parentsIds()['project'] ?? null;
        $projectName = $projectId ? ph_get_the_title($projectId) : '';
        $projectLink = get_the_guid($postId);

        return Helper::prepareFetchFormatFields(array_merge($commentData, $itemData, [
            'project_name'   => $projectName,
            'commenter_name' => $comment['comment_author'] ?? '',
            'project_type'   => $projectType,
            'action_status'  => $actionStatus,
            'project_link'   => $projectLink
        ]));
    }

    public static function isPluginInstalled()
    {
        return class_exists('\Project_Huddle');
    }
}
