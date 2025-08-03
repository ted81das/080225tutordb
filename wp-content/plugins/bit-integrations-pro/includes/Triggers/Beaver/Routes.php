<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Beaver\BeaverController;

Route::get('beaver/get', [BeaverController::class, 'getAllForms']);
Route::post('beaver/get/form', [BeaverController::class, 'getFormFields']);
