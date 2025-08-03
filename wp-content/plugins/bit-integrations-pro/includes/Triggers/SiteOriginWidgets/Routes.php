<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\SiteOriginWidgets\SiteOriginWidgetsController;

Route::get('siteoriginwidgets/get', [SiteOriginWidgetsController::class, 'getAllTasks']);
Route::post('siteoriginwidgets/test', [SiteOriginWidgetsController::class, 'getTestData']);
Route::post('siteoriginwidgets/test/remove', [SiteOriginWidgetsController::class, 'removeTestData']);
