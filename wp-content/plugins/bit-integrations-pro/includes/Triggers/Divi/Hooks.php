<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Divi\DiviController;

Hooks::add('et_pb_contact_form_submit', [DiviController::class, 'handle_divi_submit'], 10, 3);
