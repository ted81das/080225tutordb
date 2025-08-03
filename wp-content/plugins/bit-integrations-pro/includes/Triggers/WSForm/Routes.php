<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WSForm\WSFormController;

Route::get('wsform/get', [WSFormController::class, 'getAll']);
Route::post('wsform/get/form', [WSFormController::class, 'get_a_form']);
