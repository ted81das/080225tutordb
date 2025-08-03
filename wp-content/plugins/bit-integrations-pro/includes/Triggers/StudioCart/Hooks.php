<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\StudioCart\StudioCartController;

Hooks::add('sc_order_complete', [StudioCartController::class, 'newOrderCreated'], 10, 3);
