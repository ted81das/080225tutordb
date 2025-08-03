<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Brizy\BrizyController;

Hooks::filter('brizy_form_submit_data', [BrizyController::class, 'handle_brizy_submit'], 10, 2);
