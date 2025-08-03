<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPForo\WPForoController;
use BitApps\BTCBI_PRO\Triggers\WPForo\WPForoHelper;

Route::get('wpforo/get', [WPForoController::class, 'getAll']);
Route::post('wpforo/get/form', [WPForoController::class, 'get_a_form']);
Route::get('wpforo/get/forums', [WPForoHelper::class, 'getAllForums']);
Route::get('wpforo/get/topics', [WPForoHelper::class, 'getAllTopics']);
Route::get('wpforo/get/users', [WPForoHelper::class, 'getAllUsers']);
