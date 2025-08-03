<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;

final class Space
{
    public static function handleUserJoinsSpace($space, $user_id, $by)
    {
        if (empty($space) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatUserJoinSpaceData($space, $user_id, $by, 'joined_by');

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/joined_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/joined', $formData);
    }

    public static function handleUserRequestsSpaceJoin($space, $user_id)
    {
        if (empty($space) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatUserJoinSpaceData($space, $user_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/join_requested_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/join_requested', $formData);
    }

    public static function handleUserLeavesSpace($space, $user_id, $by)
    {
        if (empty($space) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatUserJoinSpaceData($space, $user_id, $by, 'left_by');

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/user_left_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/user_left', $formData);
    }

    public static function handleNewSpaceCreated($space, $data)
    {
        if (empty($space)) {
            return;
        }

        $formData = FluentCommunityHelper::formatSpaceCreationData($space, $data);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/created_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/created', $formData);
    }

    public static function handleBeforeSpaceDeleted($space)
    {
        if (empty($space)) {
            return;
        }

        $formData = FluentCommunityHelper::formatBeforeSpaceDeletedData($space);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/before_delete_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/before_delete', $formData);
    }

    public static function handleAfterSpaceDeleted($space_id)
    {
        if (empty($space_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(['space_id' => $space_id]);

        Helper::setTestData('btcbi_fluent_community/space/deleted_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/deleted', $formData);
    }

    public static function handleAfterSpaceUpdated($space, $args)
    {
        if (empty($space) || !isset($args['settings'])) {
            return;
        }

        $formData = FluentCommunityHelper::formatSpaceUpdatedData($space, $args);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/space/updated_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/space/updated', $formData);
    }
}
