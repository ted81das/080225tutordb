<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MasteriyoLMS\MasteriyoLMSController;

Hooks::add('masteriyo_course_progress_status_changed', [MasteriyoLMSController::class, 'handleCourseCompleted'], 10, 4);
Hooks::add('masteriyo_new_course_progress_item', [MasteriyoLMSController::class, 'handleLessonCompleted'], 10, 2);
Hooks::add('masteriyo_quiz_attempt_status_changed', [MasteriyoLMSController::class, 'handleQuizFailed'], 10, 3);
Hooks::add('masteriyo_quiz_attempt_status_changed', [MasteriyoLMSController::class, 'handleQuizPassed'], 10, 3);
