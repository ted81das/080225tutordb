<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SureMembers\SureMembersController;

Route::get('suremembers/get', [SureMembersController::class, 'getAll']);
Route::post('suremembers/get/form', [SureMembersController::class, 'get_a_form']);
Route::post('suremembers/get/groups', [SureMembersController::class, 'getSureMembersGroups']);
