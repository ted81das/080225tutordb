<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\DiviFormBuilder\DiviFormBuilderController;

Hooks::add('df_after_process', [DiviFormBuilderController::class, 'handleDiviFormBuilderSubmit'], 10, PHP_INT_MAX);
