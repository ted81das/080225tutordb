<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Kadence\KadenceController;

Route::get('kadence_blocks/get', [KadenceController::class, 'getAllTasks']);
Route::post('kadence_blocks/test', [KadenceController::class, 'getTestData']);
Route::post('kadence_blocks/test/remove', [KadenceController::class, 'removeTestData']);
