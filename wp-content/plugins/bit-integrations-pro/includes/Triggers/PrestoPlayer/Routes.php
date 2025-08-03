<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PrestoPlayer\PrestoPlayerController;

Route::get('presto_player/get', [PrestoPlayerController::class, 'getAllTasks']);
Route::post('presto_player/test', [PrestoPlayerController::class, 'getTestData']);
Route::post('presto_player/test/remove', [PrestoPlayerController::class, 'removeTestData']);
