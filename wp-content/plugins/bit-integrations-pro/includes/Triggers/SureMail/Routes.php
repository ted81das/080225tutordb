<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SureMail\SureMailController;

Route::get('sure_emails/get', [SureMailController::class, 'getAllTasks']);
Route::post('sure_emails/test', [SureMailController::class, 'getTestData']);
Route::post('sure_emails/test/remove', [SureMailController::class, 'removeTestData']);
