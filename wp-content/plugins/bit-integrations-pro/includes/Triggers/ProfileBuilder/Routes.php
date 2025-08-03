<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\ProfileBuilder\ProfileBuilderController;

Route::get('profile-builder/get', [ProfileBuilderController::class, 'getAllTasks']);
Route::post('profile-builder/test', [ProfileBuilderController::class, 'getTestData']);
Route::post('profile-builder/test/remove', [ProfileBuilderController::class, 'removeTestData']);
