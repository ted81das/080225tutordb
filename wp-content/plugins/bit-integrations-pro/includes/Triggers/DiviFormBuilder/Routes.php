<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\DiviFormBuilder\DiviFormBuilderController;

Route::get('diviformbuilder/get', [DiviFormBuilderController::class, 'getAllTasks']);
Route::post('diviformbuilder/test', [DiviFormBuilderController::class, 'getTestData']);
Route::post('diviformbuilder/test/remove', [DiviFormBuilderController::class, 'removeTestData']);
