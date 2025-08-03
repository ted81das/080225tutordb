<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\SmartSuite\SmartSuiteProHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_smartSuite_create_table', [SmartSuiteProHelper::class, 'createTable'], 10, 5);
Hooks::filter('btcbi_smartSuite_create_record', [SmartSuiteProHelper::class, 'createRecord'], 10, 5);
