<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

Route::get('webhook/new', [WebhookController::class, 'getNewHook']);
Route::post('webhook/test', [WebhookController::class, 'getTestData']);
Route::post('webhook/test/remove', [WebhookController::class, 'removeTestData']);
