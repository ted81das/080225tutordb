<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SureForms\SureFormsController;

Route::get('sureforms/get', [SureFormsController::class, 'getAll']);
Route::post('sureforms/get/form', [SureFormsController::class, 'get_a_form']);
