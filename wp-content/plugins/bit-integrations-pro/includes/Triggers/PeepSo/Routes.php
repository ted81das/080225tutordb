<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PeepSo\PeepSoController;

Route::get('peep_so/get', [PeepSoController::class, 'getAllTasks']);
Route::post('peep_so/test', [PeepSoController::class, 'getTestData']);
Route::post('peep_so/test/remove', [PeepSoController::class, 'removeTestData']);
