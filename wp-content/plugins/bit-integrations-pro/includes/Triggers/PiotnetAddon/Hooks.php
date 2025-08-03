<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PiotnetAddon\PiotnetAddonController;

// for piotnet addon field
// Hooks::add('pafe/form_builder/new_record', [PiotnetAddonController::class, 'handle_piotnet_submit']);
Hooks::add('pafe/form_builder/new_record_v2', [PiotnetAddonController::class, 'handle_piotnet_submit']);
