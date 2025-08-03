<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FormCraft\FormCraftController;

Hooks::add('formcraft_after_save', [FormCraftController::class, 'handle_formcraft_submit'], 10, 4);
