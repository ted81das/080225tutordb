<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PieForms\PieFormsController;

Route::get('pie_forms/get', [PieFormsController::class, 'getAllTasks']);
Route::post('pie_forms/test', [PieFormsController::class, 'getTestData']);
Route::post('pie_forms/test/remove', [PieFormsController::class, 'removeTestData']);
