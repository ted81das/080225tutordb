<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MasterStudyLms\MasterStudyLmsController;

Hooks::add('stm_lms_progress_updated', [MasterStudyLmsController::class, 'handleCourseComplete'], 10, 3);
Hooks::add('course_enrolled', [MasterStudyLmsController::class, 'handleCourseEnroll'], 10, 2);
Hooks::add('add_user_course', [MasterStudyLmsController::class, 'handleCourseEnroll'], 10, 2);
Hooks::add('stm_lms_lesson_passed', [MasterStudyLmsController::class, 'handleLessonComplete'], 10, 2);
Hooks::add('stm_lms_quiz_passed', [MasterStudyLmsController::class, 'handleQuizComplete'], 10, 3);
Hooks::add('stm_lms_quiz_failed', [MasterStudyLmsController::class, 'handleQuizFailed'], 10, 3);

// Add points
Hooks::add('stm_lms_score_charge_user_registered', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_course_purchased', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_lesson_passed', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_quiz_passed', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_perfect_quiz', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_assignment_passed', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_certificate_received', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_group_joined', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
Hooks::add('stm_lms_score_charge_friends_friendship_accepted', [MasterStudyLmsController::class, 'handlePointScoreCharge'], 10, 3);
