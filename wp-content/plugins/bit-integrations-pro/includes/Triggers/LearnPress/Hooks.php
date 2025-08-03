<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\LearnPress\LearnPressController;

Hooks::add('learn-press/user-course-finished', [LearnPressController::class, 'handleUserCompletesCourse'], 10, 3);
Hooks::add('learn-press/user-completed-lesson', [LearnPressController::class, 'handleUserCompletesLesson'], 10, 3);
Hooks::add('learnpress/user/course-enrolled', [LearnPressController::class, 'handleUserEnrolledInCourse'], 10, 3);
