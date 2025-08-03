<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Bricks\BricksController;

Hooks::add('bricks/form/custom_action', [BricksController::class, 'handle_bricks_submit']);
