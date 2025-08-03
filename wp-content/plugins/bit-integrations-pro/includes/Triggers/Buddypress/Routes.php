<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Buddypress\BuddypressController;

Route::get('buddypress/get', [BuddypressController::class, 'getAllTasks']);
Route::post('buddypress/test', [BuddypressController::class, 'getTestData']);
Route::post('buddypress/test/remove', [BuddypressController::class, 'removeTestData']);
