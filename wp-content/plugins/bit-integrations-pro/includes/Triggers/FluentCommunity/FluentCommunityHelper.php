<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;

class FluentCommunityHelper
{
    public static function formatUserJoinSpaceData($space, $user_id, $by = null, $byPrefix = 'joined_by')
    {
        $data = static::setUserData($user_id);

        if ($by) {
            $data[$byPrefix] = $by;
        }

        $attributes = static::sanitizeAttributes($space->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');

        $data = static::processAttributes('space', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatSpaceCreationData($space, $additionalData = [])
    {
        $data = ['space_url' => $space->getPermalink()];
        $attributes = static::sanitizeAttributes($space->getAttributes());

        $data = static::processAttributes('space', $data, $attributes);
        $data = static::processAdditionalData('data', $data, $additionalData);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatBeforeSpaceDeletedData($space)
    {
        $data = ['space_url' => $space->getPermalink()];
        $attributes = static::sanitizeAttributes($space->getAttributes());

        $data = static::processAttributes('space', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatSpaceUpdatedData($space, $args)
    {
        $attributes = static::sanitizeAttributes($space->getAttributes());
        $data = static::processAttributes('space', [], $attributes);
        $data = static::processAttributes('updated', $data, $args, true);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatFeedCreationData($feed)
    {
        $attributes = $feed->getAttributes();
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('space', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatSpaceFeedCreationData($feed)
    {
        $attributes = $feed->getAttributes();
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $space = $feed->space;
        $spaceAttributes = static::sanitizeAttributes($space->getAttributes());

        $data = ['space_id' => $space->id, 'feed_url' => $feed->getPermalink(), 'space_url' => $space->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data = static::processAttributes('space', $data, $spaceAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatFeedUpdatedData($feed, $args)
    {
        $attributes = $feed->getAttributes();
        $args['meta'] = wp_json_encode(maybe_unserialize($args['meta']));
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data = static::processAttributes('updated', $data, $args, true);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatFeedMentionUserData($feed, $users)
    {
        $attributes = $feed->getAttributes();
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data['mentioned_users'] = static::processUserData($users);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatBeforeFeedDeletedData($feed)
    {
        $attributes = $feed->getAttributes();
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatFeedReactionData($reaction, $feed)
    {
        $attributes = $feed->getAttributes();
        $reactionAttributes = $reaction->getAttributes();

        if (isset($attributes['meta'])) {
            $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));
        }

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data = static::processAttributes('reaction', $data, $reactionAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCommentData($comment, $feed, $users)
    {
        $attributes = $feed->getAttributes();
        $commentAttributes = $comment->getAttributes();

        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));
        $commentAttributes['meta'] = wp_json_encode(maybe_unserialize($commentAttributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data = static::processAttributes('comment', $data, $commentAttributes);

        $data['mentioned_users'] = empty($users) ? '' : static::processUserData($users);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCommentUpdatedData($comment, $feed)
    {
        $attributes = $feed->getAttributes();
        $commentAttributes = $comment->getAttributes();

        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));
        $commentAttributes['meta'] = wp_json_encode(maybe_unserialize($commentAttributes['meta']));

        $data = ['feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);
        $data = static::processAttributes('comment', $data, $commentAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCommentDeletedData($comment_id, $feed)
    {
        $attributes = $feed->getAttributes();
        $attributes['meta'] = wp_json_encode(maybe_unserialize($attributes['meta']));

        $data = ['comment_id' => $comment_id, 'feed_url' => $feed->getPermalink()];
        $data = static::processAttributes('feed', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCourseEnrollData($course, $user_id, $by = null, $byPrefix = 'enrolled_by')
    {
        $data = static::setUserData($user_id, $course, 'course');

        if (!empty($by)) {
            $data[$byPrefix] = $by;
        }

        $attributes = static::sanitizeAttributes($course->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');

        $data = static::processAttributes('course', $data, $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatLessonCompletedData($lesson, $user_id)
    {
        $data = static::setUserData($user_id, $lesson, 'lesson');
        $lessonAttributes = static::sanitizeAttributes($lesson->getAttributes());
        $lessonAttributes['meta'] = static::encodeMeta($lessonAttributes['meta'] ?? '');

        $data = static::processAttributes('lesson', $data, $lessonAttributes);
        $course = $lesson->course ?? null;

        if (empty($course)) {
            return Helper::prepareFetchFormatFields($data);
        }

        $courseAttributes = static::sanitizeAttributes($course->getAttributes());
        $courseAttributes['meta'] = static::encodeMeta($courseAttributes['meta'] ?? '');
        $data = static::processAttributes('course', $data, $courseAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCourseCreationData($course)
    {
        $attributes = static::sanitizeAttributes($course->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');
        $data = static::processAttributes('course', [], $attributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatCourseUpdatedData($course, $args)
    {
        $attributes = static::sanitizeAttributes($course->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');

        $data = static::processAttributes('course', [], $attributes);
        $data = static::processAttributes('updated', $data, $args, true);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatLessonUpdatedData($lesson, $updated_data, $is_newly_published)
    {
        $attributes = static::sanitizeAttributes($lesson->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');

        $data = ['is_newly_published' => $is_newly_published];
        $data = static::processAttributes('lesson', $data, $attributes);
        $data = static::processAttributes('updated', $data, $updated_data, true);
        $course = $lesson->course ?? null;

        if (empty($course)) {
            return Helper::prepareFetchFormatFields($data);
        }

        $courseAttributes = static::sanitizeAttributes($course->getAttributes());
        $courseAttributes['meta'] = static::encodeMeta($courseAttributes['meta'] ?? '');
        $data = static::processAttributes('course', $data, $courseAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatUserLaveledUpData($xprofile, $new_level, $old_level)
    {
        $attributes = static::sanitizeAttributes($xprofile->getAttributes());
        $attributes['meta'] = static::encodeMeta($attributes['meta'] ?? '');

        $data = static::processAttributes('xprofile', [], $attributes);
        $data = static::processAttributes('new_level', $data, $new_level);
        $data = static::processAttributes('old_level', $data, $new_level);

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatQuizSubmissionData($quizResult, $user, $quiz)
    {
        $resultAttributes = static::sanitizeAttributes($quizResult->getAttributes());
        $resultAttributes['meta'] = static::encodeMeta($resultAttributes['meta'] ?? '');
        $resultAttributes['message'] = static::encodeMeta($resultAttributes['message'] ?? '');

        $userAttributes = static::sanitizeAttributes($user->getAttributes());
        $userAttributes['meta'] = static::encodeMeta($userAttributes['meta'] ?? '');

        $quizAttributes = static::sanitizeAttributes($quiz->getAttributes());
        $quizAttributes['meta'] = static::encodeMeta($quizAttributes['meta'] ?? '');

        $data = static::setUserData($user->ID);
        $data = static::processAttributes('quiz_result', $data, $resultAttributes);
        $data = static::processAttributes('quiz', $data, $quizAttributes);
        $course = $quiz->course ?? null;

        if (empty($course)) {
            return Helper::prepareFetchFormatFields($data);
        }

        $courseAttributes = static::sanitizeAttributes($course->getAttributes());
        $courseAttributes['meta'] = static::encodeMeta($courseAttributes['meta'] ?? '');
        $data = static::processAttributes('course', $data, $courseAttributes);

        return Helper::prepareFetchFormatFields($data);
    }

    private static function processAttributes($prefix, $data, $attributes, $isArg = false)
    {
        foreach ($attributes as $key => $value) {
            $formattedKey = "{$prefix}_{$key}";
            $data[$formattedKey] = static::formatAttribute($formattedKey, $value, $data, $isArg);
        }

        return $data;
    }

    private static function processUserData($users = [])
    {
        $mentionedUsers = [];
        foreach ($users as $user) {
            $mentionedUsers[] = [
                'id'           => $user->ID,
                'email'        => $user->user_email,
                'display_name' => $user->display_name,
            ];
        }

        return wp_json_encode($mentionedUsers);
    }

    private static function formatAttribute($key, $value, &$data, $isArg = false)
    {
        if (\is_object($value) || \is_array($value)) {
            $flattenedValue = Helper::flattenNestedData($data, $key, $value);

            return \is_array($flattenedValue) ? wp_json_encode($flattenedValue) : $flattenedValue;
        }

        return $isArg ? wp_json_encode(maybe_unserialize($value)) : $value;
    }

    private static function processAdditionalData($prefix, $data, $additionalData)
    {
        foreach ($additionalData as $key => $value) {
            $data["{$prefix}_{$key}"] = (\is_object($value) || \is_array($value)) ? wp_json_encode($value) : $value;
        }

        return $data;
    }

    private static function sanitizeAttributes($attributes)
    {
        unset($attributes['settings']);

        return $attributes;
    }

    private static function encodeMeta($meta)
    {
        return wp_json_encode(maybe_unserialize($meta));
    }

    private static function setUserData($user_id, $extra = null, $prefix = null)
    {
        $user = get_userdata($user_id);
        $data = [
            'user_id'           => $user_id,
            'user_email'        => $user->user_email,
            'user_first_name'   => $user->first_name,
            'user_last_name'    => $user->last_name,
            'user_display_name' => $user->display_name,
        ];

        if ($extra) {
            $data = array_merge($data, [
                "{$prefix}_id"    => $extra->ID,
                "{$prefix}_title" => $extra->post_title
            ]);
        }

        return $data;
    }
}
