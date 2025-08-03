<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\CalculatedFieldsForm\CalculatedFieldsFormController;

Route::get('cpcff/get', [CalculatedFieldsFormController::class, 'getAllTasks']);
Route::post('cpcff/test', [CalculatedFieldsFormController::class, 'getTestData']);
Route::post('cpcff/test/remove', [CalculatedFieldsFormController::class, 'removeTestData']);
