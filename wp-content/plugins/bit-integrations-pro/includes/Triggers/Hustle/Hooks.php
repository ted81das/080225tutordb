<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Hustle\HustleController;

Hooks::add('hustle_form_submit_before_set_fields', [HustleController::class, 'handleHustleSubmit'], 10, 3);
