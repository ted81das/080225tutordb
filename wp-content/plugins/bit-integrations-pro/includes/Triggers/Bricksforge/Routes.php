<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Bricksforge\BricksforgeController;

Route::get('bricksforge/get', [BricksforgeController::class, 'getAllTasks']);
Route::post('bricksforge/test', [BricksforgeController::class, 'getTestData']);
Route::post('bricksforge/test/remove', [BricksforgeController::class, 'removeTestData']);
