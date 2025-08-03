<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\NexForms\NexFormsController;

Hooks::add('NEXForms_submit_form_data', [NexFormsController::class, 'handleFormSubmitted']);
