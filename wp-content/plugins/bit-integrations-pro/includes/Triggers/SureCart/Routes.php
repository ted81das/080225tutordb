<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SureCart\SureCartController;

Route::get('surecart/get', [SureCartController::class, 'getAll']);
Route::post('surecart/get/form', [SureCartController::class, 'get_a_form']);

Route::get('get_sureCart_all_product', [SureCartController::class, 'get_sureCart_all_product']);
