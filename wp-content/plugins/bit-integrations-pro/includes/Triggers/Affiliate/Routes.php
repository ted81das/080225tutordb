<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Affiliate\AffiliateController;

Route::get('affiliate/get', [AffiliateController::class, 'getAll']);
Route::post('affiliate/get/form', [AffiliateController::class, 'get_a_form']);
Route::get('affiliate_get_all_type', [AffiliateController::class, 'affiliateGetAllType']);
