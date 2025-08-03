<?php

namespace BitApps\BTCBI_PRO\Triggers\SenseiLMS;

class StaticData
{
    public static function forms()
    {
        return [
            ['form_name' => __('User Attempts Quiz', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_quiz_attempts', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_course_end', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Lesson', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_lesson_end', 'skipPrimaryKey' => true],
            ['form_name' => __('User Completes Quiz Percentage', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_quiz_percentage', 'skipPrimaryKey' => true],
            ['form_name' => __('User Enrolled Course', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_course_start', 'skipPrimaryKey' => true],
            ['form_name' => __('User Fails Quiz', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_quiz_fails', 'skipPrimaryKey' => true],
            ['form_name' => __('User Passes Quiz', 'bit-integrations-pro'), 'triggered_entity_id' => 'sensei_user_quiz_passes', 'skipPrimaryKey' => true],
        ];
    }
}
