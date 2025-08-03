<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\TutorLms\TutorLmsController;

Route::get('tutorlms/get', [TutorLmsController::class, 'getAll']);
Route::post('tutorlms/get/form', [TutorLmsController::class, 'get_a_form']);
