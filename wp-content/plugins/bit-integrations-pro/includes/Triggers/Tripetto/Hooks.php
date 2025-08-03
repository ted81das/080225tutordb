<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Tripetto\TripettoController;

Hooks::add('tripetto_submit', [TripettoController::class, 'handleTripettoSubmit'], 10, 2);
