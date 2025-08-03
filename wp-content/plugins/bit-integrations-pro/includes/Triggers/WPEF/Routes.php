<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPEF\WPEFController;

Route::get('wpef/get', [WPEFController::class, 'getAll']);
Route::post('wpef/get/form', [WPEFController::class, 'getAForm']);
