<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\NF\NFController;

Hooks::add('ninja_forms_after_submission', [NFController::class, 'ninja_forms_after_submission'], 10, 1);
