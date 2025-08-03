<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\MailPoet\MailPoetController;

Route::get('mailPoet/get', [MailPoetController::class, 'getAll']);
Route::post('mailPoet/get/form', [MailPoetController::class, 'get_a_form']);
