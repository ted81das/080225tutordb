<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\PiotnetAddonForm\PiotnetAddonFormController;

Route::get('piotnetaddonform/get', [PiotnetAddonFormController::class, 'getAllForms']);
Route::post('piotnetaddonform/get/form', [PiotnetAddonFormController::class, 'getFormFields']);
