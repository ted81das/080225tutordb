<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Bricksforge\BricksforgeController;

Hooks::add('bricksforge/pro_forms/after_submit', [BricksforgeController::class, 'handleBricksforgeSubmit'], 10, PHP_INT_MAX);
