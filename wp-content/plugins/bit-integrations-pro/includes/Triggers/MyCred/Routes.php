<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\MyCred\MyCredController;

Route::get('mycred/get', [MyCredController::class, 'getAllTasks']);
Route::post('mycred/test', [MyCredController::class, 'getTestData']);
Route::post('mycred/test/remove', [MyCredController::class, 'removeTestData']);
