<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FormCraft\FormCraftController;

Route::get('formcraft/get', [FormCraftController::class, 'getAll']);
Route::post('formcraft/get/form', [FormCraftController::class, 'get_a_form']);
