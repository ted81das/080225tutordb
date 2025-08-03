<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Asgaros\AsgarosController;

Route::get('asgaros/get', [AsgarosController::class, 'getAllTasks']);
Route::post('asgaros/test', [AsgarosController::class, 'getTestData']);
Route::post('asgaros/test/remove', [AsgarosController::class, 'removeTestData']);
