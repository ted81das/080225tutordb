<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\GravityKit\GravityKitController;

Route::get('gravity_kit/get', [GravityKitController::class, 'getAllTasks']);
Route::post('gravity_kit/test', [GravityKitController::class, 'getTestData']);
Route::post('gravity_kit/test/remove', [GravityKitController::class, 'removeTestData']);
