<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PiotnetAddonForm\PiotnetAddonFormController;

// for piotnet addon field
// Hooks::add('pafe/form_builder/new_record', [PiotnetAddonFormController::class, 'handle_piotnet_submit']);
Hooks::add('pafe/form_builder/new_record_v2', [PiotnetAddonFormController::class, 'handle_piotnet_submit']);
