<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\ConvertPro\ConvertProController;

Hooks::add('cpro_form_submit', [ConvertProController::class, 'handleFormSubmitted'], 10, 2);
