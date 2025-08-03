<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SolidAffiliate\SolidAffiliateController;

Route::get('solidaffiliate/get', [SolidAffiliateController::class, 'getAll']);
Route::post('solidaffiliate/get/form', [SolidAffiliateController::class, 'get_a_form']);
