<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\QuillForms\QuillFormsController;

Hooks::add('quillforms_after_entry_processed', [QuillFormsController::class, 'handleFormSubmitted'], 99, 2);
