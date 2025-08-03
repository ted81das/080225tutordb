<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Happy\HappyController;

Hooks::add('happyforms_submission_success', [HappyController::class, 'handle_happy_submit'], 10, 3);
