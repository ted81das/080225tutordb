<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;

final class Comment
{
    public static function handleNewCommentAdded($comment, $feed, $users = [])
    {
        if (empty($feed) || empty($comment)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCommentData($comment, $feed, $users);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/comment_added_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/comment_added', $formData);
    }

    public static function handleCommentUpdated($comment, $feed)
    {
        if (empty($feed) || empty($comment)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCommentUpdatedData($comment, $feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/comment_updated_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/comment_updated', $formData);
    }

    public static function handleCommentDeleted($comment_id, $feed)
    {
        if (empty($feed) || empty($comment_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCommentDeletedData($comment_id, $feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/comment_deleted_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/comment_deleted', $formData);
    }
}
