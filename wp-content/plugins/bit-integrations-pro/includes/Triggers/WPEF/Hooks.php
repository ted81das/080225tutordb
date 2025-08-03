<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPEF\WPEFController;

// Hooks::add('ipt_fsqm_hook_save_insert', [WPEFController::class, 'handleSubmission'], 10, 1);
Hooks::add('ipt_fsqm_hook_save_success', [WPEFController::class, 'handleSubmission'], 10, 1);
