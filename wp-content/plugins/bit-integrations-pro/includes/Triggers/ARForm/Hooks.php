<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ARForm\ARFormController;

Hooks::add('arfliteentryexecute', [ARFormController::class, 'handleArFormSubmit'], 10, 4);
Hooks::add('arfentryexecute', [ARFormController::class, 'handleArFormSubmit'], 10, 4);
