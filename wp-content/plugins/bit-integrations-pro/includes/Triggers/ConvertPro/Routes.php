<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\ConvertPro\ConvertProController;

Route::get('convert_pro/get', [ConvertProController::class, 'getAllTasks']);
Route::post('convert_pro/test', [ConvertProController::class, 'getTestData']);
Route::post('convert_pro/test/remove', [ConvertProController::class, 'removeTestData']);
