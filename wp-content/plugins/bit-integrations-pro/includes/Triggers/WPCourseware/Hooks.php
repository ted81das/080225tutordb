<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPCourseware\WPCoursewareController;

Hooks::add('wpcw_enroll_user', [WPCoursewareController::class, 'userEnrolledCourse'], 10, 2);
Hooks::add('wpcw_user_completed_course', [WPCoursewareController::class, 'courseCompleted'], 10, 3);
Hooks::add('wpcw_user_completed_module', [WPCoursewareController::class, 'moduleCompleted'], 10, 3);
Hooks::add('wpcw_user_completed_unit', [WPCoursewareController::class, 'unitCompleted'], 10, 3);
