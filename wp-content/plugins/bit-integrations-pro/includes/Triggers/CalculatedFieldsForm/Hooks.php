<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\CalculatedFieldsForm\CalculatedFieldsFormController;

Hooks::add('cpcff_process_data_before_insert', [CalculatedFieldsFormController::class, 'handleFormSubmitted'], 10, 3);
