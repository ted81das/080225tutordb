<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\GF\GFController;

Route::get('gf/get', [GFController::class, 'getAll']);
Route::post('gf/get/form', [GFController::class, 'get_a_form']);
