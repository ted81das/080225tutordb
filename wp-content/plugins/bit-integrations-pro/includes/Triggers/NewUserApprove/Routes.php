<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\NewUserApprove\NewUserApproveController;

Route::get('new-user-approve/get', [NewUserApproveController::class, 'getAllTasks']);
Route::post('new-user-approve/test', [NewUserApproveController::class, 'getTestData']);
Route::post('new-user-approve/test/remove', [NewUserApproveController::class, 'removeTestData']);
