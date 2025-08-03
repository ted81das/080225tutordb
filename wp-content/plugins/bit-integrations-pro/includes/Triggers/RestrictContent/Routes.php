<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\RestrictContent\RestrictContentController;

Route::get('restrictcontent/get', [RestrictContentController::class, 'getAll']);
Route::post('restrictcontent/get/form', [RestrictContentController::class, 'get_a_form']);
Route::get('restrictContent_Get_All_Levels', [RestrictContentController::class, 'get_all_membership']);
