<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\LifterLms\LifterLmsController;

Route::get('lifterlms/get', [LifterLmsController::class, 'getAll']);
Route::post('lifterlms/get/form', [LifterLmsController::class, 'get_a_form']);

Route::get('get_lifterLms_all_quiz', [LifterLmsController::class, 'getLifterLmsAllQuiz']);
Route::get('get_lifterLms_all_lesson', [LifterLmsController::class, 'getLifterLmsAllLesson']);
Route::get('get_lifterLms_all_course', [LifterLmsController::class, 'getLifterLmsAllCourse']);
Route::get('get_lifterLms_all_membership', [LifterLmsController::class, 'getLifterLmsAllMembership']);
