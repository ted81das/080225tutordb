<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\ThriveApprentice\ThriveApprenticeController;

Route::get('thriveapprentice/get', [ThriveApprenticeController::class, 'getAll']);
Route::post('thriveapprentice/get/form', [ThriveApprenticeController::class, 'get_a_form']);

Route::get('get_thriveapprentice_all_course', [ThriveApprenticeController::class, 'getAllCourseEdit']);
Route::get('get_thriveapprentice_all_lesson', [ThriveApprenticeController::class, 'getAllLessonEdit']);
Route::get('get_thriveapprentice_all_module', [ThriveApprenticeController::class, 'getAllModuleEdit']);
