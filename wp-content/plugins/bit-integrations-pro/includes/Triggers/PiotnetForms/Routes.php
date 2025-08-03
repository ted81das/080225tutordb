<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PiotnetForms\PiotnetFormsController;

Route::get('piotnetforms/get', [PiotnetFormsController::class, 'getAll']);
Route::post('piotnetforms/get/form', [PiotnetFormsController::class, 'get_a_form']);
