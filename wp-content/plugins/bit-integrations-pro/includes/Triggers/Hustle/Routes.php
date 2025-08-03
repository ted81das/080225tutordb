<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Hustle\HustleController;

Route::get('hustle/get', [HustleController::class, 'getAll']);
Route::post('hustle/get/form', [HustleController::class, 'get_a_form']);
