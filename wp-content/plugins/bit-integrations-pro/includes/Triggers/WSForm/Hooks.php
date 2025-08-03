<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WSForm\WSFormController;

Hooks::add('ws_form_action_for_bi', [WSFormController::class, 'handle_ws_form_submit'], 9999, 4);
