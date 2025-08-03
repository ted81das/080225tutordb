<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FluentCommunity\FluentCommunityController;

Route::get('fluent_community/get', [FluentCommunityController::class, 'getAllTasks']);
Route::post('fluent_community/test', [FluentCommunityController::class, 'getTestData']);
Route::post('fluent_community/test/remove', [FluentCommunityController::class, 'removeTestData']);
