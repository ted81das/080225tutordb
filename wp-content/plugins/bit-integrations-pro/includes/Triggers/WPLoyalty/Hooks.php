<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPLoyalty\WPLoyaltyController;

Hooks::add('wlr_after_add_earn_point', [WPLoyaltyController::class, 'handlePointsAwardedCustomer'], 10, 4);
