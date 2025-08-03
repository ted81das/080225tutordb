<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\JetEngine\JetEngineController;

Hooks::add('updated_post_meta', [JetEngineController::class, 'post_meta_data'], 10, 4);
Hooks::add('updated_post_meta', [JetEngineController::class, 'post_meta_value_check'], 10, 4);
