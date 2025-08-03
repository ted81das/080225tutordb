<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Newsletter\NewsletterController;

Route::get('newsletter/get', [NewsletterController::class, 'getAllTasks']);
Route::post('newsletter/test', [NewsletterController::class, 'getTestData']);
Route::post('newsletter/test/remove', [NewsletterController::class, 'removeTestData']);
