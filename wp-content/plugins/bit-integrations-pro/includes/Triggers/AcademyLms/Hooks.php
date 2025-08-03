<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AcademyLms\AcademyLmsController;

Hooks::add('academy/course/after_enroll', [AcademyLmsController::class, 'handle_course_enroll'], 10, 2);
Hooks::add('academy_quizzes/api/after_quiz_attempt_finished', [AcademyLmsController::class, 'handleQuizAttempt'], 10, 1);
Hooks::add('academy/frontend/after_mark_topic_complete', [AcademyLmsController::class, 'handleLessonComplete'], 10, 4);
Hooks::add('academy/admin/course_complete_after', [AcademyLmsController::class, 'handleCourseComplete'], 10, 1);
Hooks::add('academy_quizzes/api/after_quiz_attempt_finished', [AcademyLmsController::class, 'handleQuizTarget'], 10, 1);
