<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\GF\GFController;

Hooks::add('gform_after_submission', [GFController::class, 'gform_after_submission'], 10, 2);
