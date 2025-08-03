<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\NF\NFController;

Route::get('nf/get', [NFController::class, 'getAll']);
Route::post('nf/get/form', [NFController::class, 'getAForm']);
