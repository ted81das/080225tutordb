<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

class StaticData
{
    public static function formTasks()
    {
        return [
            ['form_name' => __('User Joins Space', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/joined', 'skipPrimaryKey' => true],
            ['form_name' => __('User Requests Space Join', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/join_requested', 'skipPrimaryKey' => true],
            ['form_name' => __('User Leaves Space', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/user_left', 'skipPrimaryKey' => true],
            ['form_name' => __('New Space Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/created', 'skipPrimaryKey' => true],
            ['form_name' => __('Before Space Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/before_delete', 'skipPrimaryKey' => true],
            ['form_name' => __('After Space Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('After Space Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space/updated', 'skipPrimaryKey' => true],
            ['form_name' => __('New Feed Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed/created', 'skipPrimaryKey' => true],
            ['form_name' => __('New Space Feed Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/space_feed/created', 'skipPrimaryKey' => false],
            ['form_name' => __('Feed Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed/updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Feed Mentions User', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed_mentioned', 'skipPrimaryKey' => true],
            ['form_name' => __('Before Feed Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed/before_deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('After Feed Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed/deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('Feed Reaction Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/feed/react_added', 'skipPrimaryKey' => true],
            ['form_name' => __('New Comment Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/comment_added', 'skipPrimaryKey' => true],
            ['form_name' => __('Comment Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/comment_updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Comment Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/comment_deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('User Enrolls In Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/enrolled', 'skipPrimaryKey' => true],
            ['form_name' => __('User Unenrolls From Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/student_left', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/completed', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Lesson', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/lesson_completed', 'skipPrimaryKey' => true],
            ['form_name' => __('Course Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/created', 'skipPrimaryKey' => true],
            ['form_name' => __('Course Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/updated', 'skipPrimaryKey' => true],
            ['form_name' => __('Course Published', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/published', 'skipPrimaryKey' => true],
            ['form_name' => __('Course Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/course/deleted', 'skipPrimaryKey' => true],
            ['form_name' => __('Lesson Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/lesson/updated', 'skipPrimaryKey' => true],
            ['form_name' => __('User Leveled Up', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/user_level_upgraded', 'skipPrimaryKey' => true],
            ['form_name' => __('Quiz Submitted', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/quiz/submitted', 'skipPrimaryKey' => true],
            ['form_name' => __('Quiz Passed', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/quiz/passed', 'skipPrimaryKey' => true],
            ['form_name' => __('Quiz Failed', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_community/quiz/failed', 'skipPrimaryKey' => true],
        ];
    }
}
