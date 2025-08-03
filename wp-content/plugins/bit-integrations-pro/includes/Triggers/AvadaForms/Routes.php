<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AvadaForms\AvadaFormsController;

Route::get('avada-forms/get', [AvadaFormsController::class, 'getAllTasks']);
Route::post('avada-forms/test', [AvadaFormsController::class, 'getTestData']);
Route::post('avada-forms/test/remove', [AvadaFormsController::class, 'removeTestData']);
