<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Brizy\BrizyController;

Route::get('brizy/get', [BrizyController::class, 'getAllTasks']);
Route::post('brizy/test', [BrizyController::class, 'getTestData']);
Route::post('brizy/test/remove', [BrizyController::class, 'removeTestData']);
