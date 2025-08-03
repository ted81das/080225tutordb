<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PopupMaker\PopupMakerController;

Route::get('popupmaker/get', [PopupMakerController::class, 'getAllTasks']);
Route::post('popupmaker/test', [PopupMakerController::class, 'getTestData']);
Route::post('popupmaker/test/remove', [PopupMakerController::class, 'removeTestData']);
