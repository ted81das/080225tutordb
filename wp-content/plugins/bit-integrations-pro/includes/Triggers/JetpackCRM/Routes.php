<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\JetpackCRM\JetpackCRMController;

Route::get('jetpack_crm/get', [JetpackCRMController::class, 'getAllTasks']);
Route::post('jetpack_crm/test', [JetpackCRMController::class, 'getTestData']);
Route::post('jetpack_crm/test/remove', [JetpackCRMController::class, 'removeTestData']);
