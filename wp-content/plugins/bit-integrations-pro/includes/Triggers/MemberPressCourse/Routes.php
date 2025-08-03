<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\MemberPressCourse\MemberPressCourseController;

Route::get('member_press_course/get', [MemberPressCourseController::class, 'getAllTasks']);
Route::post('member_press_course/test', [MemberPressCourseController::class, 'getTestData']);
Route::post('member_press_course/test/remove', [MemberPressCourseController::class, 'removeTestData']);
