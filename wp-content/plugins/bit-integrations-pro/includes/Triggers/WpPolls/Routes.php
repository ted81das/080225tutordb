<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WpPolls\WpPollsController;

Route::get('wp_polls/get', [WpPollsController::class, 'getAllTasks']);
Route::post('wp_polls/test', [WpPollsController::class, 'getTestData']);
Route::post('wp_polls/test/remove', [WpPollsController::class, 'removeTestData']);
