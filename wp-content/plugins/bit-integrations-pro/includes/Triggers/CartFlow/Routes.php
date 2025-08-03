<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\CartFlow\CartFlowController;

Route::get('cartflow/get', [CartFlowController::class, 'getAllForms']);
Route::post('cartflow/get/form', [CartFlowController::class, 'getFormFields']);
