<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\AdvancedCustomFields\AdvancedCustomFieldsController;

Route::get('acf/get', [AdvancedCustomFieldsController::class, 'getAllTasks']);
Route::post('acf/test', [AdvancedCustomFieldsController::class, 'getTestData']);
Route::post('acf/test/remove', [AdvancedCustomFieldsController::class, 'removeTestData']);
