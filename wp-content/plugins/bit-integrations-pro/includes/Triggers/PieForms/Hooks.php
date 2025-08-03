<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PieForms\PieFormsController;

Hooks::add('pie_forms_complete_entry_save', [PieFormsController::class, 'handleFormSubmitted'], 10, 5);
