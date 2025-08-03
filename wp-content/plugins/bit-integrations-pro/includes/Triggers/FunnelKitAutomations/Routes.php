<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\FunnelKitAutomations\FunnelKitAutomationsController;

Route::get('funnel_kit_automations/get', [FunnelKitAutomationsController::class, 'getAllTasks']);
Route::post('funnel_kit_automations/test', [FunnelKitAutomationsController::class, 'getTestData']);
Route::post('funnel_kit_automations/test/remove', [FunnelKitAutomationsController::class, 'removeTestData']);
