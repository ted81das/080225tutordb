<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\GutenaForms\GutenaFormsController;

Hooks::add('gutena_forms_submitted_data', [GutenaFormsController::class, 'handleGutenaFormsSubmit'], 10, PHP_INT_MAX);
