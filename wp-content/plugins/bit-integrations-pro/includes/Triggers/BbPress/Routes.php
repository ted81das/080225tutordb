<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\BbPress\BbPressController;

Route::get('bb_press/get', [BbPressController::class, 'getAllTasks']);
Route::post('bb_press/test', [BbPressController::class, 'getTestData']);
Route::post('bb_press/test/remove', [BbPressController::class, 'removeTestData']);
