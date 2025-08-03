<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\EVF\EVFController;

// Hooks::add('ipt_fsqm_hook_save_insert', [EVFController::class, 'handleSubmission'], 10, 1);
Hooks::add('everest_forms_complete_entry_save', [EVFController::class, 'handleSubmission'], 10, 5);
