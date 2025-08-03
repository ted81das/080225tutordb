<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\NexForms\NexFormsController;

Route::get('nex-forms/get', [NexFormsController::class, 'getAllTasks']);
Route::post('nex-forms/test', [NexFormsController::class, 'getTestData']);
Route::post('nex-forms/test/remove', [NexFormsController::class, 'removeTestData']);
