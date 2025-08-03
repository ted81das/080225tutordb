<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FF\FFController;

Hooks::add('fluentform_submission_inserted', [FFController::class, 'handle_ff_submit'], 10, 3);
