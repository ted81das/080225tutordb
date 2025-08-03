<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Coblocks\CoblocksController;

Hooks::add('coblocks_form_submit', [CoblocksController::class, 'coblocksHandler'], 10, PHP_INT_MAX);
