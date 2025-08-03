<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Met\MetController;

Route::get('met/get', [MetController::class, 'getAll']);
Route::post('met/get/form', [MetController::class, 'get_a_form']);
