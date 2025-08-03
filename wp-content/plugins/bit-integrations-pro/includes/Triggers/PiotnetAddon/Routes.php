<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PiotnetAddon\PiotnetAddonController;

Route::get('piotnetaddon/get', [PiotnetAddonController::class, 'getAllForms']);
Route::post('piotnetaddon/get/form', [PiotnetAddonController::class, 'getFormFields']);
