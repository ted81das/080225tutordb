<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Met\MetController;

Hooks::add('metform_pro_form_data_for_pro_integrations', [MetController::class, 'handle_metform_pro_submit'], 10, 4);
Hooks::add('metform_after_store_form_data', [MetController::class, 'handle_metform_submit'], 10, 4);
