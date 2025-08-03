<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WpAllImport\WpAllImportController;

Route::get('wp_all_import/get', [WpAllImportController::class, 'getAllTasks']);
Route::post('wp_all_import/test', [WpAllImportController::class, 'getTestData']);
Route::post('wp_all_import/test/remove', [WpAllImportController::class, 'removeTestData']);
