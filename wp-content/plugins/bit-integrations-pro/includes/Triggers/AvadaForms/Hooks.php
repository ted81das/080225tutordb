<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AvadaForms\AvadaFormsController;

Hooks::add('fusion_form_submission_data', [AvadaFormsController::class, 'handleFormSubmission'], 10, 2);
