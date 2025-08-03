<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class Feed
{
    public static function handleNewFeedCreated($feed)
    {
        if (empty($feed)) {
            return;
        }

        $formData = FluentCommunityHelper::formatFeedCreationData($feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/feed/created_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed/created', $formData);
    }

    public static function handleNewSpaceFeedCreated($feed)
    {
        if (empty($feed)) {
            return;
        }

        $formData = FluentCommunityHelper::formatSpaceFeedCreationData($feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space_feed/created_test', array_values($formData), 'space_id.value', $formData['space_id']['value']);

        $flows = Flow::exists('FluentCommunity', 'fluent_community/space_feed/created');

        if (empty($flows)) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (empty($flowDetails->primaryKey)) {
                continue;
            }

            if (!Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            $data = array_column($formData, 'value', 'name');
            Flow::execute('FluentCommunity', 'fluent_community/space_feed/created', $data, [$flow]);
        }

        return ['type' => 'success'];
    }

    public static function handleFeedUpdated($feed, $args)
    {
        if (empty($feed)) {
            return;
        }

        $formData = FluentCommunityHelper::formatFeedUpdatedData($feed, $args);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/feed/updated_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed/updated', $formData);
    }

    public static function handleFeedMentionsUser($feed, $users)
    {
        if (empty($feed) || empty($users)) {
            return;
        }

        $formData = FluentCommunityHelper::formatFeedMentionUserData($feed, $users);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/feed_mentioned_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed_mentioned', $formData);
    }

    public static function handleBeforeFeedDeleted($feed)
    {
        if (empty($feed)) {
            return;
        }

        $formData = FluentCommunityHelper::formatBeforeFeedDeletedData($feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/feed/before_deleted_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed/before_deleted', $formData);
    }

    public static function handleAfterFeedDeleted($feed_id)
    {
        if (empty($feed_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(['feed_id' => $feed_id]);

        Helper::setTestData('btcbi_fluent_community/feed/deleted_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed/deleted', $formData);
    }

    public static function handleFeedReactionAdded($reaction, $feed)
    {
        if (empty($feed) || empty($reaction)) {
            return;
        }

        $formData = FluentCommunityHelper::formatFeedReactionData($reaction, $feed);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/feed/react_added_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/feed/react_added', $formData);
    }
}
