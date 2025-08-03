<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SenseiLMS\SenseiLMSController;

Hooks::add('sensei_user_quiz_grade', [SenseiLMSController::class, 'handleUserAttemptsQuiz'], 10, 5);
Hooks::add('sensei_user_course_end', [SenseiLMSController::class, 'handleUserCompletesCourse'], 10, 2);
Hooks::add('sensei_user_lesson_end', [SenseiLMSController::class, 'handleUserCompletesLesson'], 10, 2);
Hooks::add('sensei_user_quiz_grade', [SenseiLMSController::class, 'handleUserCompletesQuizPercentage'], 10, 5);
Hooks::add('sensei_user_course_start', [SenseiLMSController::class, 'handleUserEnrolledCourse'], 10, 2);
Hooks::add('sensei_user_quiz_grade', [SenseiLMSController::class, 'handleUserFailsQuiz'], 10, 5);
Hooks::add('sensei_user_quiz_grade', [SenseiLMSController::class, 'handleUserPassesQuiz'], 10, 5);
