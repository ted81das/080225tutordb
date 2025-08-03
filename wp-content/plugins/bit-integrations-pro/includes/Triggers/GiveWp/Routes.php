<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\GiveWp\GiveWpController;

Route::get('givewp/get', [GiveWpController::class, 'getAll']);
Route::post('givewp/get/form', [GiveWpController::class, 'get_a_form']);

Route::get('get_all_donation_form', [GiveWpController::class, 'all_donation_form']);
