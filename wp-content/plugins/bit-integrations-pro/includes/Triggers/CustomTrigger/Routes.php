<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\CustomTrigger\CustomTriggerController;

Route::get('custom_trigger/new', [CustomTriggerController::class, 'getNewHook']);
Route::post('custom_trigger/test', [CustomTriggerController::class, 'getTestData']);
Route::post('custom_trigger/test/remove', [CustomTriggerController::class, 'removeTestData']);
