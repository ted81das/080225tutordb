<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AcademyLms\AcademyLmsController;

Route::get('academylms/get', [AcademyLmsController::class, 'getAll']);
Route::post('academylms/get/form', [AcademyLmsController::class, 'get_a_form']);
