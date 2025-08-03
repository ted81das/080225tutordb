<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Formidable\FormidableController;

Route::get('formidable/get', [FormidableController::class, 'getAll']);
Route::post('formidable/get/form', [FormidableController::class, 'get_a_form']);
