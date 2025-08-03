<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FluentSMTP\FluentSMTPController;

Route::get('fluent_smtp/get', [FluentSMTPController::class, 'getAllTasks']);
Route::post('fluent_smtp/test', [FluentSMTPController::class, 'getTestData']);
Route::post('fluent_smtp/test/remove', [FluentSMTPController::class, 'removeTestData']);
