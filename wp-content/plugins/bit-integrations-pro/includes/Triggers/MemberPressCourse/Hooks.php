<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MemberPressCourse\MemberPressCourseController;

Hooks::add('mpcs_completed_course', [MemberPressCourseController::class, 'handleCourseCompleted'], 10, 1);
Hooks::add('mpcs_completed_lesson', [MemberPressCourseController::class, 'handleLessonCompleted'], 10, 1);
