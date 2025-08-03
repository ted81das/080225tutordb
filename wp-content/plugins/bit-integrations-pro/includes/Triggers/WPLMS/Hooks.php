<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPLMS\WPLMSController;

Hooks::add('wplms_submit_course', [WPLMSController::class, 'handleUserCompleteCourse'], 10, 2);
