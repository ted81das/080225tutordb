<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\GutenaForms\GutenaFormsController;

Route::get('gutenaforms/get', [GutenaFormsController::class, 'getAllTasks']);
Route::post('gutenaforms/test', [GutenaFormsController::class, 'getTestData']);
Route::post('gutenaforms/test/remove', [GutenaFormsController::class, 'removeTestData']);
