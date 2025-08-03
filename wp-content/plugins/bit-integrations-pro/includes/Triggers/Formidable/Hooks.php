<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Formidable\FormidableController;

Hooks::add('frm_success_action', [FormidableController::class, 'handle_formidable_submit'], 10, 5);
