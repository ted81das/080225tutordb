<?php

if (!defined('ABSPATH')) {
    exit;
}
use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Tripetto\TripettoController;

Route::get('tripetto/get', [TripettoController::class, 'getAll']);
Route::post('tripetto/get/form', [TripettoController::class, 'get_a_form']);
