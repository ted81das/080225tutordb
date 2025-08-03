<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WeForms\WeFormsController;

Hooks::add('weforms_entry_submission', [WeFormsController::class, 'handle_weforms_submit'], 10, 4);
