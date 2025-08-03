<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\ProfileGrid\ProfileGridController;

Route::get('profile_grid/get', [ProfileGridController::class, 'getAllTasks']);
Route::post('profile_grid/test', [ProfileGridController::class, 'getTestData']);
Route::post('profile_grid/test/remove', [ProfileGridController::class, 'removeTestData']);
