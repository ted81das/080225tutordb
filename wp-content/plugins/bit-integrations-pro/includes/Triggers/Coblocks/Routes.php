<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Coblocks\CoblocksController;

Route::post('coblocks/get', [CoblocksController::class, 'getTestData']);
Route::post('coblocks/test/remove', [CoblocksController::class, 'removeTestData']);
