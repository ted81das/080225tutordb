<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Bricks\BricksController;

Route::get('bricks/get', [BricksController::class, 'getAllTasks']);
Route::post('bricks/test', [BricksController::class, 'getTestData']);
Route::post('bricks/test/remove', [BricksController::class, 'removeTestData']);
