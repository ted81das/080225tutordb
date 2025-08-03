<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;

final class Course
{
    public static function handleUserEnrollsInCourse($course, $user_id, $by)
    {
        if (empty($course) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseEnrollData($course, $user_id, $by, 'enrolled_by');

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/enrolled_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/enrolled', $formData);
    }

    public static function handleUserUnenrollsFromCourse($course, $user_id, $by)
    {
        if (empty($course) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseEnrollData($course, $user_id, $by, 'unenrolled_by');

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/student_left_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/student_left', $formData);
    }

    public static function handleUserCompletesCourse($course, $user_id)
    {
        if (empty($course) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseEnrollData($course, $user_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/completed_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/completed', $formData);
    }

    public static function handleUserCompletesLesson($lesson, $user_id)
    {
        if (empty($lesson) || empty($user_id)) {
            return;
        }

        $formData = FluentCommunityHelper::formatLessonCompletedData($lesson, $user_id);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/lesson_completed_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/lesson_completed', $formData);
    }

    public static function handleCourseCreated($course)
    {
        if (empty($course)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseCreationData($course);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/created_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/created', $formData);
    }

    public static function handleCourseUpdated($course, $args)
    {
        if (empty($course) || empty($args)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseUpdatedData($course, $args);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/updated_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/updated', $formData);
    }

    public static function handleCoursePublished($course)
    {
        if (empty($course)) {
            return;
        }

        $formData = FluentCommunityHelper::formatCourseCreationData($course);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/course/published_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/published', $formData);
    }

    public static function handleCourseDeleted($course_id)
    {
        if (empty($course_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields(['course_id' => $course_id]);

        Helper::setTestData('btcbi_fluent_community/course/deleted_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/course/deleted', $formData);
    }

    public static function handleLessonUpdated($lesson, $updated_data, $is_newly_published)
    {
        if (empty($lesson) || empty($updated_data)) {
            return;
        }

        $formData = FluentCommunityHelper::formatLessonUpdatedData($lesson, $updated_data, $is_newly_published);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/lesson/updated_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/lesson/updated', $formData);
    }

    public static function handleQuizSubmitted($quizResult, $user, $quiz)
    {
        return self::quizExecute($quizResult, $user, $quiz, 'fluent_community/quiz/submitted');
    }

    public static function handleQuizPassed($quizResult, $user, $quiz)
    {
        if (empty($quizResult) || $quizResult->status !== 'passed') {
            return;
        }

        return self::quizExecute($quizResult, $user, $quiz, 'fluent_community/quiz/passed');
    }

    public static function handleQuizFailed($quizResult, $user, $quiz)
    {
        if (empty($quizResult) || $quizResult->status !== 'failed') {
            return;
        }

        return self::quizExecute($quizResult, $user, $quiz, 'fluent_community/quiz/failed');
    }

    private static function quizExecute($quizResult, $user, $quiz, $triggeredEntityId)
    {
        if (empty($quizResult) || empty($user) || empty($quiz)) {
            return;
        }

        $formData = FluentCommunityHelper::formatQuizSubmissionData($quizResult, $user, $quiz);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggeredEntityId}_test", array_values($formData));

        return FluentCommunityController::flowExecute($triggeredEntityId, $formData);
    }
}
