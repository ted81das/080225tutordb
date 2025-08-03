<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Mailster\MailsterController;

Route::get('mailster/get', [MailsterController::class, 'getAll']);
Route::post('mailster/get/form', [MailsterController::class, 'get_a_form']);
