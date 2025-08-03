<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Rafflepress\RafflepressController;

Route::get('rafflepress/get', [RafflepressController::class, 'getAll']);
Route::post('rafflepress/get/form', [RafflepressController::class, 'get_a_form']);
