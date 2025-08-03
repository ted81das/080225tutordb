<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SliceWp\SliceWpController;

Hooks::add('slicewp_insert_affiliate', [SliceWpController::class, 'newAffiliateCreated'], 10, 2);
Hooks::add('slicewp_insert_commission', [SliceWpController::class, 'userEarnCommission'], 10, 2);
