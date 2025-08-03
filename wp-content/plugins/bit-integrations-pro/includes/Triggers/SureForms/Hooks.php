<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SureForms\SureFormsController;

Hooks::add('srfm_form_submit', [SureFormsController::class, 'handleSureFormsSubmit'], 10, 1);
