<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Dokan\DokanController;

Route::get('dokan/get', [DokanController::class, 'getAll']);
Route::post('dokan/get/form', [DokanController::class, 'get_a_form']);
