<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Eventin\EventinController;

Route::get('eventin/get', [EventinController::class, 'getAll']);
Route::post('eventin/get/form', [EventinController::class, 'get_a_form']);
