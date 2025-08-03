<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\BetterMessages\BetterMessagesController;

Route::get('better_messages/get', [BetterMessagesController::class, 'getAllTasks']);
Route::post('better_messages/test', [BetterMessagesController::class, 'getTestData']);
Route::post('better_messages/test/remove', [BetterMessagesController::class, 'removeTestData']);
