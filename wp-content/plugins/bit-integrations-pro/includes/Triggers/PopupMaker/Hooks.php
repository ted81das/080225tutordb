<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PopupMaker\PopupMakerController;

Hooks::add('pum_sub_form_success', [PopupMakerController::class, 'handlePopupMakerSubmit'], 10, PHP_INT_MAX);
