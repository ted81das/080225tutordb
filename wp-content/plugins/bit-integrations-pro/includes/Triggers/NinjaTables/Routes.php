<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\NinjaTables\NinjaTablesController;

Route::get('ninja_tables/get', [NinjaTablesController::class, 'getAllTasks']);
Route::post('ninja_tables/test', [NinjaTablesController::class, 'getTestData']);
Route::post('ninja_tables/test/remove', [NinjaTablesController::class, 'removeTestData']);
