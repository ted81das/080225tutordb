<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Kadence\KadenceController;

Hooks::add('kadence_blocks_form_submission', [KadenceController::class, 'handle_kadence_form_submit'], 10, 4);
Hooks::add('kadence_blocks_advanced_form_submission', [KadenceController::class, 'handle_kadence_form_submit'], 10, 4);
