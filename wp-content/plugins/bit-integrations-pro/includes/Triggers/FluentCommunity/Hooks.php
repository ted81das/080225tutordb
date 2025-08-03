<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\Comment;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\Course;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\Feed;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\Space;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\User;

Hooks::add('fluent_community/space/joined', [Space::class, 'handleUserJoinsSpace'], 10, 3);
Hooks::add('fluent_community/space/join_requested', [Space::class, 'handleUserRequestsSpaceJoin'], 10, 2);
Hooks::add('fluent_community/space/user_left', [Space::class, 'handleUserLeavesSpace'], 10, 3);
Hooks::add('fluent_community/space/created', [Space::class, 'handleNewSpaceCreated'], 10, 2);
Hooks::add('fluent_community/space/deleted', [Space::class, 'handleAfterSpaceDeleted'], 10, 1);
Hooks::add('fluent_community/space/before_delete', [Space::class, 'handleBeforeSpaceDeleted'], 10, 1);
Hooks::add('fluent_community/space/updated', [Space::class, 'handleAfterSpaceUpdated'], 10, 2);
Hooks::add('fluent_community/feed/created', [Feed::class, 'handleNewFeedCreated'], 10, 1);
Hooks::add('fluent_community/space_feed/created', [Feed::class, 'handleNewSpaceFeedCreated'], 10, 1);
Hooks::add('fluent_community/feed/updated', [Feed::class, 'handleFeedUpdated'], 10, 2);
Hooks::add('fluent_community/feed_mentioned', [Feed::class, 'handleFeedMentionsUser'], 10, 2);
Hooks::add('fluent_community/feed/before_deleted', [Feed::class, 'handleBeforeFeedDeleted'], 10, 1);
Hooks::add('fluent_community/feed/deleted', [Feed::class, 'handleAfterFeedDeleted'], 10, 1);
Hooks::add('fluent_community/feed/react_added', [Feed::class, 'handleFeedReactionAdded'], 10, 2);
Hooks::add('fluent_community/comment_added', [Comment::class, 'handleNewCommentAdded'], 10, 3);
Hooks::add('fluent_community/comment_updated', [Comment::class, 'handleCommentUpdated'], 10, 2);
Hooks::add('fluent_community/comment_deleted', [Comment::class, 'handleCommentDeleted'], 10, 2);
Hooks::add('fluent_community/course/enrolled', [Course::class, 'handleUserEnrollsInCourse'], 10, 3);
Hooks::add('fluent_community/course/student_left', [Course::class, 'handleUserUnenrollsFromCourse'], 10, 3);
Hooks::add('fluent_community/course/completed', [Course::class, 'handleUserCompletesCourse'], 10, 2);
Hooks::add('fluent_community/course/lesson_completed', [Course::class, 'handleUserCompletesLesson'], 10, 2);
Hooks::add('fluent_community/course/created', [Course::class, 'handleCourseCreated'], 10, 1);
Hooks::add('fluent_community/course/updated', [Course::class, 'handleCourseUpdated'], 10, 2);
Hooks::add('fluent_community/course/published', [Course::class, 'handleCoursePublished'], 10, 1);
Hooks::add('fluent_community/course/deleted', [Course::class, 'handleCourseDeleted'], 10, 1);
Hooks::add('fluent_community/lesson/updated', [Course::class, 'handleLessonUpdated'], 10, 3);
Hooks::add('fluent_community/user_level_upgraded', [User::class, 'handleUserLeveledUp'], 10, 3);
Hooks::add('fluent_community/quiz/submitted', [Course::class, 'handleQuizSubmitted'], 10, 3);
Hooks::add('fluent_community/quiz/submitted', [Course::class, 'handleQuizPassed'], 10, 3);
Hooks::add('fluent_community/quiz/submitted', [Course::class, 'handleQuizFailed'], 10, 3);
