<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FluentBoards\FluentBoardsController;

Route::get('fluent_boards/get', [FluentBoardsController::class, 'getAllTasks']);
Route::post('fluent_boards/test', [FluentBoardsController::class, 'getTestData']);
Route::post('fluent_boards/test/remove', [FluentBoardsController::class, 'removeTestData']);
