<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Divi\DiviController;

Route::get('divi/get', [DiviController::class, 'getAllTasks']);
Route::post('divi/test', [DiviController::class, 'getTestData']);
Route::post('divi/test/remove', [DiviController::class, 'removeTestData']);
