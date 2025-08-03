<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ThriveApprentice\ThriveApprenticeController;

Hooks::add('thrive_apprentice_course_finish', [ThriveApprenticeController::class, 'handleCourseComplete'], 10, 2);
Hooks::add('thrive_apprentice_lesson_complete', [ThriveApprenticeController::class, 'handleLessonComplete'], 10, 2);
Hooks::add('thrive_apprentice_module_finish', [ThriveApprenticeController::class, 'handleModuleComplete'], 10, 2);
