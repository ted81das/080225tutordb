<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Memberpress\MemberpressController;

Route::get('memberpress/get', [MemberpressController::class, 'getAll']);
Route::post('memberpress/get/form', [MemberpressController::class, 'get_a_form']);
Route::get('get_all_membership', [MemberpressController::class, 'getAllMembership']);
Route::get('get_all_onetime_membership', [MemberpressController::class, 'getAllOnetimeMembership']);
Route::get('get_all_recurring_membership', [MemberpressController::class, 'getAllRecurringMembership']);
