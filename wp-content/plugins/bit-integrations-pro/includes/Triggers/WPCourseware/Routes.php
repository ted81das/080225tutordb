<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPCourseware\WPCoursewareController;

Route::get('wpcourseware/get', [WPCoursewareController::class, 'getAll']);
Route::post('wpcourseware/get/form', [WPCoursewareController::class, 'get_a_form']);
